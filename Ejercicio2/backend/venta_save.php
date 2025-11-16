<?php
// Inserta o actualiza una venta (cabecera).
require_once __DIR__ . '/db.php';

$data = read_request_data();

// Validaciones básicas para no insertar registros vacíos.
$venIde = isset($data['ven_ide']) ? (int) $data['ven_ide'] : null;
$venSer = trim($data['ven_ser'] ?? '');
$venNum = trim($data['ven_num'] ?? '');
$venCli = trim($data['ven_cli'] ?? '');
$venMon = $data['ven_mon'] ?? null;
$estAdo = isset($data['est_ado']) ? (int) $data['est_ado'] : 1;

if ($venSer === '' || $venNum === '' || $venCli === '') {
    send_json(['success' => false, 'error' => 'Faltan datos obligatorios.'], 400);
}

if (!is_numeric($venMon)) {
    send_json(['success' => false, 'error' => 'El monto debe ser numérico.'], 400);
}

try {
    if ($venIde) {
        // Actualizamos el registro existente.
        $sql = "
            UPDATE ventas
            SET ven_ser = :ven_ser,
                ven_num = :ven_num,
                ven_cli = :ven_cli,
                ven_mon = :ven_mon,
                est_ado = :est_ado
            WHERE ven_ide = :ven_ide
            RETURNING ven_ide, ven_ser, ven_num, ven_cli, ven_mon, est_ado
        ";
    } else {
        // Insertamos una nueva venta.
        $sql = "
            INSERT INTO ventas (ven_ser, ven_num, ven_cli, ven_mon, est_ado)
            VALUES (:ven_ser, :ven_num, :ven_cli, :ven_mon, :est_ado)
            RETURNING ven_ide, ven_ser, ven_num, ven_cli, ven_mon, est_ado
        ";
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':ven_ser' => $venSer,
        ':ven_num' => $venNum,
        ':ven_cli' => $venCli,
        ':ven_mon' => $venMon,
        ':est_ado' => $estAdo,
        ':ven_ide' => $venIde,
    ]);

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
