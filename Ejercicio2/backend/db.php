<?php

$host = "localhost";
$port = "5432";
$db   = "crud_db";
$user = "sebas";
$pass = "12345";

// Creamos el objeto PDO de forma centralizada y con manejo básico de errores.
try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$db;";
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
} catch (PDOException $e) {
    // Si la conexión falla devolvemos un JSON con el error.
    send_json(['success' => false, 'error' => $e->getMessage()], 500);
}

/**
 * Atajo para responder en formato JSON y terminar la ejecución.
 */
function send_json(array $payload, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload);
    exit;
}

/**
 * Obtiene los datos enviados por POST o por el cuerpo raw (JSON).
 */
function read_request_data(): array
{
    // Primero intentamos con $_POST por compatibilidad con formularios clásicos.
    if (!empty($_POST)) {
        return $_POST;
    }

    // Si no hay POST, tratamos de decodificar el cuerpo JSON.
    $raw = file_get_contents('php://input');
    $decoded = json_decode($raw, true);

    return is_array($decoded) ? $decoded : [];
}
