<?php
// Secure PDO Database Connection Class

class Database {
    private $host = "localhost";
    private $db_name = "sticker_store";
    private $username = "root";
    private $password = "";
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->exec("set names utf8mb4");
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch(PDOException $exception) {
            // Quiet fail or logging can be placed here
        }
        return $this->conn;
    }
}

// Global Response Helper for standardized API JSON outputs
function sendResponse($status, $message, $data = null) {
    header("Content-Type: application/json; charset=UTF-8");
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
    
    $response = [
        "success" => $status === 200 || $status === 201,
        "status" => $status,
        "message" => $message
    ];
    if ($data !== null) {
        $response["data"] = $data;
    }
    echo json_serialize($response);
    exit;
}

// Polyfill dynamic serialization returning strict formats
function json_serialize($data) {
    return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
}

// Simple simulation of JWT Auth validation
function validateJWT() {
    $headers = apache_request_headers();
    $authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : '';
    if (empty($authHeader) && isset($_SERVER['HTTP_AUTHORIZATION'])) {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
    }

    if (empty($authHeader)) {
        return false;
    }

    $parts = explode(" ", $authHeader);
    if (count($parts) !== 2 || strtolower($parts[0]) !== 'bearer') {
        return false;
    }

    $token = $parts[1];
    $tokenParts = explode('.', $token);
    if (count($tokenParts) !== 3) {
        return false;
    }

    $payload = json_decode(base64_decode($tokenParts[1]), true);
    if (!$payload || !isset($payload['exp']) || $payload['exp'] < time()) {
        return false;
    }

    return $payload; // Return token details
}

// Generate simple mock helper JWT
function generateJWT($userId, $email, $role = 'user') {
    $header = json_encode(['alg' => 'HS256', 'typ' => 'JWT']);
    $payload = json_encode([
        'sub' => $userId,
        'email' => $email,
        'role' => $role,
        'iat' => time(),
        'exp' => time() + (3600 * 24 * 7) // Valid for 7 days
    ]);

    $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
    $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));
    
    // In a production server, append server signature here
    $signature = hash_hmac('sha256', "$base64UrlHeader.$base64UrlPayload", "StickerStoreSuperSecretKey123!!!");
    $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

    return "$base64UrlHeader.$base64UrlPayload.$base64UrlSignature";
}
