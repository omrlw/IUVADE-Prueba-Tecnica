<?php
// backend/trabajador_save.php
// Guarda (insert/update) un trabajador. Recibe JSON desde ExtJS.
require 'db.php';

$input = read_request_data();

$tra_ide = $input['tra_ide'] ?? null;
$tra_cod = $input['tra_cod'] ?? null;
$tra_nom = trim($input['tra_nom'] ?? '');
$tra_pat = trim($input['tra_pat'] ?? '');
$tra_mat = trim($input['tra_mat'] ?? '');

// Validaciones mínimas para no guardar registros vacíos.
if ($tra_cod === null || $tra_nom === '' || $tra_pat === '' || $tra_mat === '') {
    send_json(['success' => false, 'message' => 'Faltan datos obligatorios.'], 400);
}

try {
    if ($tra_ide) {
        // Modificar registro existente.
        $stmt = $pdo->prepare("
            UPDATE prueba.trabajador
            SET tra_cod = ?, tra_nom = ?, tra_pat = ?, tra_mat = ?
            WHERE tra_ide = ?
        ");
        $stmt->execute([$tra_cod, $tra_nom, $tra_pat, $tra_mat, $tra_ide]);
    } else {
        // Nuevo registro.
        $stmt = $pdo->prepare("
            INSERT INTO prueba.trabajador (tra_cod, tra_nom, tra_pat, tra_mat)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$tra_cod, $tra_nom, $tra_pat, $tra_mat]);
    }

    send_json(['success' => true]);
} catch (Exception $e) {
    send_json([
        'success' => false,
        'message' => $e->getMessage(),
    ], 500);
}
