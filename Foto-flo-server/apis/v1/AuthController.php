<?php
header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

require '../../models/User.php';
require '../../utils/utils.php';

class AuthController
{

    public static function handleRequest()
    {
        $method = $_SERVER['REQUEST_METHOD'];

        if ($method === 'OPTIONS') {
            http_response_code(200);
            exit();
        }

        $action = $_GET['action'] ?? '';

        switch ($action) {
            case 'register':
                self::register();
                break;
            case 'login':
                self::login();
                break;
            case 'validate-token':
                self::validateToken();
                break;
            default:
                ResponseHelper::error("not found", '404');
        }
    }

    private static function register()
    {
        $data = json_decode(file_get_contents('php://input'), true);

        // Validate input
        if (empty($data['username']) || empty($data['email']) || empty($data['password'])) {
            ResponseHelper::send(false, "All fields are required", 400);
        }

        try {
            // Create and save user
            User::create($data['username'], $data['email'], $data['password']);
            $user_id = User::save();

            if ($user_id) {
                $token = User::createToken($user_id);
                ResponseHelper::send(true, $token, 201);
            }
        } catch (Exception $e) {
            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                ResponseHelper::send(false, "Email or userbame already exists", 400);
            } else {
                ResponseHelper::send(false, "internal server error", 500);
            }
        }
    }




    private static function login()
    {
        $data = json_decode(file_get_contents('php://input'), true);

        if (empty($data['email']) || empty($data['password'])) {
            ResponseHelper::send(false, "email and password required", 200);
        }
        try {
            $user = User::login($data['email'], $data['password']);

            if ($user) {
                $token = User::createToken($user['user_id']);
                ResponseHelper::send(true, $token, 200);
            } else {
                ResponseHelper::send(false, "invalid credentials", 401);
            }
        } catch (Exception $e) {
            ResponseHelper::send(false, "internal server error", 500);
        }
    }

    private static function validateToken()
    {
        $headers = getallheaders();
        $token = isset($headers['Authorization'])
            ? trim(str_replace('Bearer ', '', $headers['Authorization']))
            : null;

        if (!$token) {
            ResponseHelper::send(false, "Authorization token required", 401); // Added semicolon
        }

        try {
            $user = User::validateToken($token);

            if ($user) {
                $data = [
                    'success' => true,
                    'user' => [
                        'user_id' => $user['user_id'],
                        'username' => $user['username'],
                        'email' => $user['email']
                    ]
                ];
                ResponseHelper::send(true, $data, 200);
            } else {
                ResponseHelper::send(false, "invalid token", 401);
            }
        } catch (Exception $e) {
            ResponseHelper::send(false, "internal server error", 500);
        }
    }
}

// Execute the controller
AuthController::handleRequest();
