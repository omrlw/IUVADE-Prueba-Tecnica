<?php
// Lista los detalles de una venta específica.
require_once __DIR__ . '/db.php';

// Permitimos ven_ide tanto por GET como por cuerpo JSON/POST.
$data = read_request_data();
$venIde = isset($_GET['ven_ide']) ? (int) $_GET['ven_ide'] : ($data['ven_ide'] ?? null);
$venIde = $venIde ? (int) $venIde : null;

if (!$venIde) {
    send_json(['success' => false, 'error' => 'ven_ide es obligatorio.'], 400);
}

$showAll = (isset($_GET['show_all']) && $_GET['show_all'] === '1');
$statusFilter = $showAll ? '' : 'AND est_ado = 1';

try {
    $sql = "
        SELECT v_d_ide, ven_ide, v_d_pro, v_d_uni, v_d_can, v_d_tot, est_ado
        FROM ventas_detalle
        WHERE ven_ide = :ven_ide
        $statusFilter
        ORDER BY v_d_ide
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':ven_ide' => $venIde]);
    $detalles = $stmt->fetchAll(PDO::FETCH_ASSOC);

    send_json([
        'success' => true,
        'data' => $detalles,
    ]);
} catch (PDOException $e) {
    send_json([
        'success' => false,
        'error' => $e->getMessage(),
    ], 500);
}
