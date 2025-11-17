<?php
// Inserta o modifica una venta. Recibe JSON desde ExtJS.
require 'db.php';

$input   = read_request_data();              // lee POST o cuerpo crudo
$ven_ide = $input['ven_ide'] ?? null;        // viene solo al editar
$ven_ser = trim($input['ven_ser'] ?? '');    // serie del comprobante
$ven_num = trim($input['ven_num'] ?? '');    // número del comprobante
$ven_cli = trim($input['ven_cli'] ?? '');    // nombre de cliente
$ven_mon = $input['ven_mon'] ?? null;        // monto declarado

// Validaciones básicas para no guardar registros incompletos.
if ($ven_ser === '' || $ven_num === '' || $ven_cli === '' || $ven_mon === null) {
    send_json(['success' => false, 'message' => 'Faltan datos obligatorios.'], 400);
}

try {
    if ($ven_ide) {
        // Actualizar cabecera existente.
        $stmt = $pdo->prepare("
            UPDATE prueba.venta
            SET ven_ser = ?, ven_num = ?, ven_cli = ?, ven_mon = ?
            WHERE ven_ide = ?
        ");
        $stmt->execute([$ven_ser, $ven_num, $ven_cli, $ven_mon, $ven_ide]);
    } else {
        // Insertar nueva cabecera.
        $stmt = $pdo->prepare("
            INSERT INTO prueba.venta (ven_ser, ven_num, ven_cli, ven_mon)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$ven_ser, $ven_num, $ven_cli, $ven_mon]);
    }

    send_json(['success' => true]);
} catch (Exception $e) {
    send_json([
        'success' => false,
        'message' => $e->getMessage(),
    ], 500);
}
