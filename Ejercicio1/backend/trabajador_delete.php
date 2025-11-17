<?php
// backend/trabajador_delete.php
// Elimina lógicamente cambiando est_ado a 1.
require 'db.php';

$input   = read_request_data();
$tra_ide = $input['tra_ide'] ?? null;

if (!$tra_ide) {
    send_json(['success' => false, 'message' => 'ID requerido'], 400);
}

try {
    $stmt = $pdo->prepare("
        UPDATE prueba.trabajador
        SET est_ado = 0
        WHERE tra_ide = ?
    ");
    $stmt->execute([$tra_ide]);

    send_json(['success' => true]);
} catch (Exception $e) {
    send_json([
        'success' => false,
        'message' => $e->getMessage(),
    ], 500);
}
