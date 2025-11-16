<?php
// Elimina lógicamente una venta (cabecera) marcando est_ado = 0.
require_once __DIR__ . '/db.php';

$data = read_request_data();
$venIde = isset($data['ven_ide']) ? (int) $data['ven_ide'] : null;

if (!$venIde) {
    send_json(['success' => false, 'error' => 'ven_ide es obligatorio.'], 400);
}

try {
    $stmt = $pdo->prepare('UPDATE ventas SET est_ado = 0 WHERE ven_ide = :ven_ide');
    $stmt->execute([':ven_ide' => $venIde]);

    send_json(['success' => true]);
} catch (PDOException $e) {
    send_json([
        'success' => false,
        'error' => $e->getMessage(),
    ], 500);
}
