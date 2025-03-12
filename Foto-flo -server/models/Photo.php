<?php
require "PhotoSkeleton.php";
require(__DIR__ . '/../connection/connect.php');
class Photo extends PhotoSkeleton
{
    public static function all($user_id)
    {
        global $conn;
        $query = $conn->prepare("SELECT * FROM photos where user_id = ?");
        $query->bind_param("i", $user_id);
        $query->execute();
        $result = $query->get_result();
        // here we need to loop over the results
        $photos = [];
        while ($row = $result->fetch_assoc()) {
            $photos[] = $row;
        }
        return $photos;
    }
    public static function save()
    {
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

            foreach (self::$tags as $tag) {
                $insertTagQuery->reset();
                $selectTagQuery->reset(); 
                // validate tag if its empty
                $tag = trim($tag);
                if (empty($tag)) continue;

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
        } catch (Exception $e) {
            // Rollback on failure
            $conn->rollback();
            error_log("Save failed: " . $e->getMessage());
            return false;
        }
    }
    public static function update($photo_id, $user_id, $title, $description, $tags)
    {
        global $conn;
        // Start transaction (rollback if transaction is failed)
        $conn->begin_transaction();
        try {
            // 1. Update the photo
            $query = $conn->prepare("
            UPDATE photos SET  title =?, description =? WHERE photo_id =? AND user_id =?");
            $query->bind_param("ssii", $title, $description, $photo_id, $user_id);
            $query->execute();
            // 2. Delete all existing tags
            $query = $conn->prepare("DELETE FROM phototags WHERE photo_id =?");
            $query->bind_param("i", $photo_id);
            $query->execute();
            // 3. Process new tags
            $insertTagQuery = $conn->prepare("INSERT IGNORE INTO tags (tag_name) VALUES (?)");
            $selectTagQuery = $conn->prepare("SELECT tag_id FROM tags WHERE tag_name = ?");
            $insertPhotoTagQuery = $conn->prepare("INSERT INTO phototags (photo_id, tag_id) VALUES (?, ?)");
            foreach ($tags as $tag) {
                $insertTagQuery->reset(); // ← Add this
                $selectTagQuery->reset(); // ← And this
                //insert new tags
                $tag = trim($tag);
                if (empty($tag)) continue;
                $insertTagQuery->bind_param("s", $tag);
                $insertTagQuery->execute();
                // Get tag ID
                $selectTagQuery->bind_param("s", $tag);
                $selectTagQuery->execute();
                $result = $selectTagQuery->get_result();
                $tag_id = $result->fetch_assoc()['tag_id'];

                // Link to photo
                $insertPhotoTagQuery->bind_param("ii", $photo_id, $tag_id);
                $insertPhotoTagQuery->execute();
            }
            $conn->commit();
            return true;
        } catch (Exception $e) {
            $conn->rollback();
            error_log("Update failed: " . $e->getMessage());
            return false;
        }
    }
    public static function delete($photo_id, $user_id)
    {
        global $conn;
        $conn->begin_transaction();

        try {
            // 1. Delete photo-tag relationships
            $query = $conn->prepare("DELETE FROM phototags WHERE photo_id = ?");
            $query->bind_param("i", $photo_id);
            $query->execute();

            // 2. Delete the photo
            $query = $conn->prepare("DELETE FROM photos WHERE photo_id = ? AND user_id = ?");
            $query->bind_param("ii", $photo_id, $user_id);
            $query->execute();

            $conn->commit();
            return $query->affected_rows > 0;
        } catch (Exception $e) {
            $conn->rollback();
            error_log("Delete failed: " . $e->getMessage());
            return false;
        }
    }
}
