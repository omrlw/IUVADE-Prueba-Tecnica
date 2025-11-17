<?php
// Borrado lógico de un detalle.
require 'db.php';

$input   = read_request_data();           // obtiene JSON enviado
$v_d_ide = $input['v_d_ide'] ?? null;     // ID del detalle seleccionado

if (!$v_d_ide) {
    send_json(['success' => false, 'message' => 'ID de detalle requerido'], 400);
}

try {
    $stmt = $pdo->prepare("
        UPDATE prueba.venta_detalle
        SET est_ado = 0
        WHERE v_d_ide = ?
    ");
    $stmt->execute([$v_d_ide]);

    send_json(['success' => true]);
} catch (Exception $e) {
    send_json([
        'success' => false,
        'message' => $e->getMessage(),
    ], 500);
}
