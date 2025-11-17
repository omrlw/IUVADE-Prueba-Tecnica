<?php
// backend/trabajador_list.php
// Devuelve el listado de trabajadores activos para llenar el grid.
require 'db.php';

try {
    $stmt = $pdo->query("
        SELECT tra_ide, tra_cod, tra_nom, tra_pat, tra_mat, est_ado
        FROM prueba.trabajador
        WHERE est_ado = 1 -- 1 = activo, 0 = eliminado lógico
        ORDER BY tra_ide ASC
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
