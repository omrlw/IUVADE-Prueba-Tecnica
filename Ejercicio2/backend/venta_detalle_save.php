<?php
// Inserta o actualiza un detalle de venta.
require_once __DIR__ . '/db.php';

$data = read_request_data();

$detalleId = isset($data['v_d_ide']) ? (int) $data['v_d_ide'] : null;
$ventaId = isset($data['ven_ide']) ? (int) $data['ven_ide'] : null;
$producto = trim($data['v_d_pro'] ?? '');
$unidad = $data['v_d_uni'] ?? null;
$cantidad = $data['v_d_can'] ?? null;
$estAdo = isset($data['est_ado']) ? (int) $data['est_ado'] : 1;

// Validaciones mínimas para asegurar datos coherentes.
if (!$ventaId) {
    send_json(['success' => false, 'error' => 'ven_ide es obligatorio.'], 400);
}

if ($producto === '') {
    send_json(['success' => false, 'error' => 'v_d_pro es obligatorio.'], 400);
}

if (!is_numeric($unidad) || !is_numeric($cantidad)) {
    send_json(['success' => false, 'error' => 'v_d_uni y v_d_can deben ser numéricos.'], 400);
}

try {
    if ($detalleId) {
        // Actualizamos el registro existente.
        $sql = "
            UPDATE ventas_detalle
            SET ven_ide = :ven_ide,
                v_d_pro = :v_d_pro,
                v_d_uni = :v_d_uni,
                v_d_can = :v_d_can,
                est_ado = :est_ado
            WHERE v_d_ide = :v_d_ide
            RETURNING v_d_ide, ven_ide, v_d_pro, v_d_uni, v_d_can, v_d_tot, est_ado
        ";
    } else {
        // Insertamos un nuevo detalle.
        $sql = "
            INSERT INTO ventas_detalle (ven_ide, v_d_pro, v_d_uni, v_d_can, est_ado)
            VALUES (:ven_ide, :v_d_pro, :v_d_uni, :v_d_can, :est_ado)
            RETURNING v_d_ide, ven_ide, v_d_pro, v_d_uni, v_d_can, v_d_tot, est_ado
        ";
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':v_d_ide' => $detalleId,
        ':ven_ide' => $ventaId,
        ':v_d_pro' => $producto,
        ':v_d_uni' => $unidad,
        ':v_d_can' => $cantidad,
        ':est_ado' => $estAdo,
    ]);

    // El trigger en la BD calcula v_d_tot antes de devolver el registro.
    $saved = $stmt->fetch(PDO::FETCH_ASSOC);

    send_json([
        'success' => true,
        'data' => $saved,
    ]);
} catch (PDOException $e) {
    send_json([
        'success' => false,
        'error' => $e->getMessage(),
    ], 500);
}
