<?php
header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

class ResponseHelper {
    public static function send($success, $data = null, $statusCode = 200) {
        http_response_code($statusCode);
        echo json_encode(['success' => $success, 'data' => $data]);
        exit;
    }

    public static function error($message, $statusCode = 500) {
        http_response_code($statusCode);
        echo json_encode(['success' => false, 'error' => $message]);
        exit;
    }
}

class AuthHelper {
    public static function authenticate() {
        $token = self::getAuthToken();
        $user = User::validateToken($token);
        if (!$user) {
            ResponseHelper::error('Unauthorized', 401);
        }
        return $user['user_id'];
    }

    private static function getAuthToken() {
        $headers = getallheaders();
        return str_replace('Bearer ', '', $headers['Authorization'] ?? '');
    }
}

class FileUploader {
    private $uploadDir;
    private $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    private $mimeMap = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif'
    ];

    public function __construct($uploadDir = __DIR__ . '/../uploads/') {
        $this->uploadDir = rtrim($uploadDir, '/') . '/';
    }

    public function handleBase64Upload($base64Data) {
        // Parse and validate base64 data
        $parsed = $this->parseBase64($base64Data);
        $mimeType = $parsed['mime'];
        $imageData = $parsed['data'];

        // Validate file type
        if (!in_array($mimeType, $this->allowedTypes)) {
            throw new Exception('Invalid file type: ' . $mimeType, 415);
        }

        // Generate filename
        $extension = $this->mimeMap[$mimeType];
        $fileName = uniqid() . '.' . $extension;
        $targetPath = $this->uploadDir . $fileName;

        // Ensure directory exists
        $this->ensureUploadDirExists();

        // Save file
        if (!file_put_contents($targetPath, $imageData)) {
            throw new Exception('Failed to save image file');
        }

        return '/uploads/' . $fileName;
    }

    private function parseBase64($base64Data) {
        // Handle data URI scheme
        if (preg_match('/^data:(image\/\w+);base64,/', $base64Data, $matches)) {
            return [
                'mime' => $matches[1],
                'data' => base64_decode(str_replace($matches[0], '', $base64Data))
            ];
        }

        // Handle raw base64
        $imageData = base64_decode($base64Data);
        if (!$imageData) {
            throw new Exception('Invalid base64 data');
        }

        // Detect MIME type from binary
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        return [
            'mime' => $finfo->buffer($imageData),
            'data' => $imageData
        ];
    }

    private function ensureUploadDirExists() {
        if (!is_dir($this->uploadDir)) {
            if (!mkdir($this->uploadDir, 0755, true)) {
                throw new Exception('Failed to create upload directory');
            }
        }
    }
}
function processTags($tagsInput) {
    return array_filter(
        array_map('trim', explode(',', $tagsInput)),
        function($tag) { return !empty($tag); }
    );
}

function getRequestData() {
    if ($_SERVER['CONTENT_TYPE'] === 'application/json') {
        return json_decode(file_get_contents('php://input'), true) ?? [];
    }
    return $_SERVER['REQUEST_METHOD'] === 'GET' ? $_GET : $_POST;
}

function handleOptionsRequest() {
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit;
    }
}