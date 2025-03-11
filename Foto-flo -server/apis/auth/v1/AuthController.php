<?php
header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

require '../../../models/User.php';

class AuthController {

    public static function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        
        if ($method === 'OPTIONS') {
            http_response_code(200);
            exit();
        }

        $action = $_GET['action'] ?? '';

        switch($action) {
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
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Endpoint not found']);
        }
    }

    private static function register() {
        $data = json_decode(file_get_contents('php://input'), true);
    
        // Validate input
        if (empty($data['username']) || empty($data['email']) || empty($data['password'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'All fields are required']);
            return;
        }
    
        try {
            // Create and save user
            User::create($data['username'], $data['email'], $data['password']);
            $user_id = User::save();
    
            if ($user_id) {
                $token = User::createToken($user_id);
                echo json_encode([
                    'success' => true,
                    'user_id' => $user_id,
                    'token' => $token
                ]);
            }
        } catch (Exception $e) {
            http_response_code(500);
    
            // Detect duplicate error 
            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                echo json_encode(['success' => false, 'error' => 'Email or username already exists']);
            } else {
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            }
        }
    }
    
    
    

    private static function login() {
        $data = json_decode(file_get_contents('php://input'), true);

        if(empty($data['email']) || empty($data['password'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Email and password required']);
            return;
        }

    

        try {
            $user = User::login($data['email'], $data['password']);
            
            if($user) {
                $token = User::createToken($user['user_id']);
                echo json_encode([
                    'success' => true,
                    'user_id' => $user['user_id'],
                    'token' => $token
                ]);
            } else {
                http_response_code(401);
                echo json_encode(['success' => false, 'error' => 'Invalid credentials']);
            }
        } catch(Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Login failed']);
        }
    }

    private static function validateToken() {
        $headers = getallheaders();
        $token = isset($headers['Authorization']) 
               ? trim(str_replace('Bearer ', '', $headers['Authorization'])) 
               : null;

        if(!$token) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Authorization token required']);
            return;
        }

        try {
            $user = User::validateToken($token);
            
            if($user) {
                echo json_encode([
                    'success' => true,
                    'user' => [
                        'user_id' => $user['user_id'],
                        'username' => $user['username'],
                        'email' => $user['email']
                    ]
                ]);
            } else {
                http_response_code(401);
                echo json_encode(['success' => false, 'error' => 'Invalid token']);
            }
        } catch(Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Token validation failed']);
        }
    }
}

// Execute the controller
AuthController::handleRequest();