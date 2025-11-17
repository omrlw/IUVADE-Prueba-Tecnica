<?php
// Conexión simple a PostgreSQL mediante PDO.
// Se centraliza para reutilizar $pdo y helpers en todos los endpoints.

$host = "localhost";
$port = "5432";
$db   = "crud_db";
$user = "sebas";
$pass = "12345";

try {
    // DSN de PostgreSQL. Se activa modo excepciones para capturar errores.
    $dsn = "pgsql:host=$host;port=$port;dbname=$db;";
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
} catch (PDOException $e) {
    send_json(['success' => false, 'message' => $e->getMessage()], 500);
}

/**
 * Responde en JSON y finaliza el script.
 */
function send_json(array $payload, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload);
    exit;
}

/**
 * Lee datos enviados ya sea como formulario o como JSON crudo.
 */
function read_request_data(): array
{
    if (!empty($_POST)) {
        return $_POST;
    }

    $raw = file_get_contents('php://input');
    $decoded = json_decode($raw, true);

    return is_array($decoded) ? $decoded : [];
}
