<?php
// backend/test_db.php
// Endpoint simple para probar la conexión a la base.
require 'db.php';

$stmt = $pdo->query("SELECT * FROM prueba.trabajador");
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

send_json($data);
