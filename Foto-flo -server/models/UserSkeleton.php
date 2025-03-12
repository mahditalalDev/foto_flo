<?php
class UserSkeleton {
    public static $user_id;
    public static $username;
    public static $email;
    public static $password;
    public static function create($username,$email,$password){
        self::$username = $username;
        self::$email = $email;
        self::$password = $password;
    }
}