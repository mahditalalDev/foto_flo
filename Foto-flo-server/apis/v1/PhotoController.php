<?php
require '../../models/Photo.php';
require '../../models/User.php';
require '../../utils/utils.php';

class PhotoController
{
    public static function handleRequest()
    {
        handleOptionsRequest();
        
        $action = $_GET['action'] ?? '';
        $method = $_SERVER['REQUEST_METHOD'];

        try {
            switch ($action) {
                case 'upload':
                    self::upload();
                    break;
                case 'list':
                    self::all();
                    break;
                case 'update':
                    self::updatePhoto();
                    break;
                case 'delete':
                    self::deletePhoto();
                    break;
                default:
                    ResponseHelper::error('Endpoint not found', 404);
            }
        } catch (Exception $e) {
            $statusCode = $e->getCode() ?: 500;
            ResponseHelper::error($e->getMessage(), $statusCode);
        }
    }

    static function all()
    {
        $user_id = AuthHelper::authenticate();
        $photos = Photo::all($user_id);
        ResponseHelper::send(true, $photos,200);
    }

    static function updatePhoto()
    {
        $user_id = AuthHelper::authenticate();
        $data = getRequestData();
    
        // Add validation for required photo_id
        if (!isset($data['photo_id']) || !is_numeric($data['photo_id'])) {
            ResponseHelper::send(false, ['error' => 'Invalid photo ID'], 400);
            return;
        }
    
        $success = Photo::update(
            $data['photo_id'],
            $user_id,
            $data['title'] ?? '',
            $data['description'] ?? '',
            processTags($data['tags'] ?? '')
        );
    
        // Get updated photo data if successful
        if ($success) {
            $updatedPhoto = Photo::getById($data['photo_id'], $user_id);
            ResponseHelper::send(true, $updatedPhoto, 200);
        } else {
            ResponseHelper::send(false, ['error' => 'Update failed or no changes made'], 400);
        }
    }

    static function deletePhoto()
    {
        $user_id = AuthHelper::authenticate();
        $data = getRequestData();

        $success = Photo::delete($data['photo_id'] ?? 0, $user_id);
        ResponseHelper::send($success, null, $success ? 200 : 404);
    }

    static function upload()
    {
        $user_id = AuthHelper::authenticate();
        
        // Get JSON input
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Validate required fields
        if (!isset($data['image'])) {
            ResponseHelper::send(false, ['error' => 'Missing image data'], 400);
            return;
        }
    
        try {
            // Initialize uploader with user directory
            $uploader = new FileUploader(__DIR__ . '/uploads/' . $user_id . '/');
            
            // Handle base64 upload
            $filePath = $uploader->handleBase64Upload($data['image']);
            
            // Process tags from JSON data
            $tags = processTags($data['tags'] ?? '');
    
            // Create photo with data from JSON
            Photo::create(
                $user_id,
                $data['title'] ?? '',
                $data['description'] ?? '',
                $filePath,
                $tags
            );
            
            $photo_id = Photo::save();
    
            if ($photo_id === false) {
                throw new Exception('Failed to save photo');
            }
    
            ResponseHelper::send(true, ['photo_id' => $photo_id], 201);
            
        } catch (Exception $e) {
            ResponseHelper::send(false, [
                'error' => $e->getMessage()
            ], $e->getCode() ?: 500);
        }
    }
}

PhotoController::handleRequest();