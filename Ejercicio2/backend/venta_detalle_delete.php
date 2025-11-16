<?php

require_once __DIR__ . '/db.php';

$data = read_request_data();
$detalleId = isset($data['v_d_ide']) ? (int) $data['v_d_ide'] : null;

if (!$detalleId) {
    send_json(['success' => false, 'error' => 'v_d_ide es obligatorio.'], 400);
}

try {
    $stmt = $pdo->prepare('UPDATE ventas_detalle SET est_ado = 0 WHERE v_d_ide = :v_d_ide');
    $stmt->execute([':v_d_ide' => $detalleId]);

    send_json(['success' => true]);
} catch (PDOException $e) {
    send_json([
        'success' => false,
        'error' => $e->getMessage(),
    ], 500);
}
