<?php
require "PhotoSkeleton.php";
require(__DIR__ . '/../connection/connect.php');
class Photo extends PhotoSkeleton {
    public function all($user_id){
        global $conn;
        $query = $conn ->prepare("SELECT * FROM photos where user_id = ?");
        $query -> bind_param("i", $user_id);
        $query -> execute();
        $result = $query -> get_result();
        $photos = $result->fetch_assoc();
        return $photos;
    }
    public static function save() {
        global $conn;
        
        // Start transaction (rollback if transaction is failed) 
        $conn->begin_transaction();
    
        try {
            // 1. Save the photo
            $query = $conn->prepare("INSERT INTO photos (user_id, title, description, image_url) VALUES (?, ?, ?, ?)");
            $query->bind_param("isss", self::$user_id, self::$title, self::$description, self::$image_url);
            $query->execute();
            $photo_id = $conn->insert_id;
    
            // 2. Process tags
            $insertTagQuery = $conn->prepare("INSERT IGNORE INTO tags (tag_name) VALUES (?)");
            $selectTagQuery = $conn->prepare("SELECT tag_id FROM tags WHERE tag_name = ?");
            $insertPhotoTagQuery = $conn->prepare("INSERT INTO phototags (photo_id, tag_id) VALUES (?, ?)");
    
            foreach(self::$tags as $tag) {
                // validate tag if its empty
                $tag = trim($tag);
                if(empty($tag)) continue;
    
                // Insert tag (if new)
                $insertTagQuery->bind_param("s", $tag);
                $insertTagQuery->execute();
    
                // Get tag ID
                $selectTagQuery->bind_param("s", $tag);
                $selectTagQuery->execute();
                $result = $selectTagQuery->get_result();
                $tag_id = $result->fetch_assoc()['tag_id'];
    
                // Link tag to photo 
                $insertPhotoTagQuery->bind_param("ii", $photo_id, $tag_id);
                $insertPhotoTagQuery->execute();
            }
    
            // Commit transaction
            $conn->commit();
            return $photo_id;
    
        } catch(Exception $e) {
            // Rollback on failure
            $conn->rollback();
            error_log("Save failed: " . $e->getMessage());
            return false;
        }
    }
}
