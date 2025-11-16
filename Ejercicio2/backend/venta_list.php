<?php
// Lista todas las ventas (cabecera) activas.
require_once __DIR__ . '/db.php';

// Permitimos opcionalmente traer eliminados lógicamente con ?show_all=1.
$showAll = isset($_GET['show_all']) && $_GET['show_all'] === '1';
$statusFilter = $showAll ? '' : 'WHERE est_ado = 1';

try {
    // Obtenemos las ventas ordenadas de la más reciente a la más antigua.
    $sql = "
        SELECT ven_ide, ven_ser, ven_num, ven_cli, ven_mon, est_ado
        FROM ventas
        $statusFilter
        ORDER BY ven_ide DESC
    ";
    $stmt = $pdo->query($sql);
    $ventas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    send_json([
        'success' => true,
        'data' => $ventas,
    ]);
} catch (PDOException $e) {
    send_json([
        'success' => false,
        'error' => $e->getMessage(),
    ], 500);
}
