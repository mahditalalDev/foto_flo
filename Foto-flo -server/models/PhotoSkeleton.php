<?php 
class PhotoSkeleton{
    public static $photo_id;
    public static $user_id;
    public static $title;
    public static $description;
    public static $image_url;
    public static $tags = [];
    public static function create($photo_id , $user_id , $title , $description , $image_url , $tags ) {
        self::$photo_id = $photo_id;
        self::$user_id = $user_id;
        self::$title = $title;
        self::$description = $description;
        self::$image_url = $image_url;
        self::$tags = $tags;
    }
}