<?php
// Inserta o modifica un detalle. El trigger en PostgreSQL calcula v_d_tot.
require 'db.php';

$input    = read_request_data();          // cuerpo JSON o POST
$v_d_ide  = $input['v_d_ide'] ?? null;    // ID del detalle (para editar)
$ven_ide  = $input['ven_ide'] ?? null;    // FK de la cabecera
$v_d_pro  = trim($input['v_d_pro'] ?? '');// descripción del producto
$v_d_uni  = $input['v_d_uni'] ?? null;    // precio unitario
$v_d_can  = $input['v_d_can'] ?? null;    // cantidad

if (!$ven_ide || $v_d_pro === '' || $v_d_uni === null || $v_d_can === null) {
    send_json(['success' => false, 'message' => 'Faltan datos obligatorios.'], 400);
}

try {
    if ($v_d_ide) {
        // Actualizar un detalle existente.
        $stmt = $pdo->prepare("
            UPDATE prueba.venta_detalle
            SET ven_ide = ?, v_d_pro = ?, v_d_uni = ?, v_d_can = ?
            WHERE v_d_ide = ?
        ");
        $stmt->execute([$ven_ide, $v_d_pro, $v_d_uni, $v_d_can, $v_d_ide]);
    } else {
        // Insertar un nuevo detalle.
        $stmt = $pdo->prepare("
            INSERT INTO prueba.venta_detalle (ven_ide, v_d_pro, v_d_uni, v_d_can)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$ven_ide, $v_d_pro, $v_d_uni, $v_d_can]);
    }

    // El cálculo de v_d_tot ocurre en el trigger PostgreSQL.
    send_json(['success' => true]);
} catch (Exception $e) {
    send_json([
        'success' => false,
        'message' => $e->getMessage(),
    ], 500);
}
