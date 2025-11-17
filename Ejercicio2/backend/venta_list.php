<?php
// Lista las cabeceras de ventas activas.
require 'db.php';

try {
    $stmt = $pdo->query("
        SELECT ven_ide, ven_ser, ven_num, ven_cli, ven_mon, est_ado
        FROM prueba.venta
        WHERE est_ado = 1
        ORDER BY ven_ide ASC
    ");

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
