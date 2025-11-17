<?php
// Lista los detalles activos para una venta en particular.
require 'db.php';

$ven_ide = $_GET['ven_ide'] ?? null; // parámetro enviado por el store

if (!$ven_ide) {
    send_json([
        'success' => true,
        'data' => [],
        'message' => 'Sin venta seleccionada'
    ]);
}

try {
    $stmt = $pdo->prepare("
        SELECT v_d_ide, ven_ide, v_d_pro, v_d_uni, v_d_can, v_d_tot, est_ado
        FROM prueba.venta_detalle
        WHERE est_ado = 1 AND ven_ide = ?
        ORDER BY v_d_ide ASC
    ");
    $stmt->execute([$ven_ide]);

    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    send_json([
        'success' => true,
        'data' => $data,
    ]);
} catch (Exception $e) {
    send_json([
        'success' => false,
        'message' => $e->getMessage(),
    ], 500);
}
