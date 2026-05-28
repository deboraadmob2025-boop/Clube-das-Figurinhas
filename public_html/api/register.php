<?php
require_once "../config/database.php";

$database = new Database();
$db = $database->getConnection();

$input = json_decode(file_get_contents("php://input"), true);
$name = isset($input['name']) ? trim($input['name']) : '';
$email = isset($input['email']) ? trim($input['email']) : '';
$password = isset($input['password']) ? trim($input['password']) : '';

if (empty($name) || empty($email) || empty($password)) {
    sendResponse(400, "Por favor, preencha todos os campos obrigatórios.");
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    sendResponse(400, "Formato de e-mail inválido.");
}

if (strlen($password) < 6) {
    sendResponse(400, "A senha deve conter no mínimo 6 caracteres.");
}

if ($db) {
    try {
        // Check if email already registered
        $check = $db->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
        $check->execute([':email' => $email]);
        if ($check->fetch()) {
            sendResponse(409, "Este endereço de e-mail já está sendo utilizado.");
        }
        
        $password_hash = password_hash($password, PASSWORD_BCRYPT);
        
        $stmt = $db->prepare("INSERT INTO users (name, email, password_hash) VALUES (:name, :email, :password_hash)");
        $stmt->execute([
            ':name' => $name,
            ':email' => $email,
            ':password_hash' => $password_hash
        ]);
        
        $newId = $db->lastInsertId();
        $token = generateJWT($newId, $email);
        
        sendResponse(201, "Usuário cadastrado com sucesso!", [
            "token" => $token,
            "user" => [
                "id" => (int)$newId,
                "name" => $name,
                "email" => $email,
                "isPremium" => false
            ]
        ]);
    } catch (PDOException $e) {
        sendResponse(500, "Ocorreu um erro ao salvar o registro.");
    }
} else {
    // Return instant success in developer simulation modes
    $token = generateJWT(102, $email);
    sendResponse(201, "Usuário cadastrado com sucesso (Simulado)!", [
        "token" => $token,
        "user" => [
            "id" => 102,
            "name" => $name,
            "email" => $email,
            "isPremium" => false
        ]
    ]);
}
