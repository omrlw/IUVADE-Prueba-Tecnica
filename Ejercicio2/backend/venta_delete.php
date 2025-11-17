<?php
// Borrado lógico de una venta (est_ado = 0).
require 'db.php'; // abre la conexión y helpers

$input   = read_request_data();        // datos enviados vía AJAX
$ven_ide = $input['ven_ide'] ?? null;  // ID de la venta a marcar como eliminada

if (!$ven_ide) {
    send_json(['success' => false, 'message' => 'ID de venta requerido'], 400);
}

try {
    $stmt = $pdo->prepare("
        UPDATE prueba.venta
        SET est_ado = 0
        WHERE ven_ide = ?
    ");
    $stmt->execute([$ven_ide]);

    send_json(['success' => true]);
} catch (Exception $e) {
    send_json([
        'success' => false,
        'message' => $e->getMessage(),
    ], 500);
}
