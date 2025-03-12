<?php
require "UserSkeleton.php";
require(__DIR__ . '/../connection/connect.php');
class User extends UserSkeleton
{
    public static function save()
    {
        global $conn;
        $hashedPassword = hash('sha256', self::$password);
        $query = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        if (!$query) {
            throw new Exception("Query preparation failed: " . $conn->error);
        }
        $query->bind_param("sss", self::$username, self::$email, $hashedPassword);
        if (!$query->execute()) {
            throw new Exception("Query execution failed: " . $query->error);
        }

        return $conn->insert_id;
    }

    public static function login($email, $password)
    {
        global $conn;

        $hashedPassword = hash('sha256', $password);
        $query = $conn->prepare("SELECT * FROM users WHERE email = ? AND password = ?");
        $query->bind_param("ss", $email, $hashedPassword);
        $query->execute();

        $result = $query->get_result();
        $user = $result->fetch_assoc();

        return $user;
    }

    public static function createToken($user_id)
    {
        global $conn;

        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+2 hour'));

        $query = $conn->prepare("INSERT INTO auth_tokens (user_id, token, expires_at) VALUES (?, ?, ?)");
        $query->bind_param("iss", $user_id, $token, $expires);
        $query->execute();

        return $token;
    }

    public static function validateToken($token)
    {
        global $conn;
        $query = $conn->prepare("
            SELECT * FROM users WHERE user_id = (
                SELECT user_id FROM auth_tokens WHERE token = ?
            );
        ");
        $query->bind_param("s", $token);

        $query->execute();

        return $query->get_result()->fetch_assoc();
    }
}
