<?php
class pagosNoRegistradosLogic {

    public function obtenerPagosNoRegistrados($idColegiado) {
    $db = Database::getConnection();
    $sql = "SELECT pagosnoregistrados.Id, colegiadodeudaanual.Periodo, colegiadodeudaanualcuotas.Cuota AS CuotaColegiacion,
            pagosnoregistrados.Recibo, pagosnoregistrados.FechaPago, pagosnoregistrados.FechaCarga,
            pagosnoregistrados.IdUsuario, pagosnoregistrados.Detalle, lugarpago.Detalle AS LugarDePago,
            pagosnoregistrados.TipoPago, planpagoscuotas.IdPlanPagos, planpagoscuotas.Cuota AS CuotaPP
            FROM pagosnoregistrados
            INNER JOIN lugarpago ON(lugarpago.Id = pagosnoregistrados.IdLugarDePago)
            LEFT JOIN colegiadodeudaanualcuotas ON(colegiadodeudaanualcuotas.Id = pagosnoregistrados.Recibo AND
                    pagosnoregistrados.TipoPago = 'C')
            LEFT JOIN colegiadodeudaanual ON(colegiadodeudaanual.Id = colegiadodeudaanualcuotas.IdColegiadoDeudaAnual)
            LEFT JOIN planpagoscuotas ON(planpagoscuotas.Id = pagosnoregistrados.Recibo AND
                    pagosnoregistrados.TipoPago = 'P')
            WHERE pagosnoregistrados.IdColegiado = ?";

    $resultado = array();
    try {
        $stmt = $db->prepare($sql);
        $stmt->execute([$idColegiado]);
        $dados = $stmt->fetchAll();
        if (count($dados) > 0) {
            $datos = array();
            foreach ($dados as $row) {
                $datos[] = array(
                    'idPagoNoRegistrado' => $row['Id'],
                    'periodo' => $row['Periodo'],
                    'cuota' => $row['CuotaColegiacion'],
                    'recibo' => $row['Recibo'],
                    'fechaPago' => $row['FechaPago'],
                    'fechaCarga' => $row['FechaCarga'],
                    'idUsuario' => $row['IdUsuario'],
                    'detalle' => $row['Detalle'],
                    'lugarPago' => $row['LugarDePago'],
                    'tipoPago' => $row['TipoPago'],
                    'idPlanPago' => $row['IdPlanPagos'],
                    'cuotaPlanPago' => $row['CuotaPP']
                );
            }
            $resultado['estado'] = TRUE;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "No hay Pagos No Registrados";
            $resultado['clase'] = 'alert alert-warning';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "Error buscando Pagos No Registrados";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function agregarPagosNoRegistrados($idColegiado, $idLugarPago, $fechaPago, $observaciones, $lasCuotas, $lasCuotasPP) {
    $db = Database::getConnection();
    try {
        $db->beginTransaction();
        $fechaCarga = date('Y-m-d');
        $hayCuotas = 0;
        $resultado['estado'] = TRUE;
        foreach ($lasCuotas as $value) {
            $sql = "INSERT INTO pagosnoregistrados
                    (Recibo, IdUsuario, FechaCarga, FechaPago, IdLugarDePago, Detalle, Estado, TipoPago, IdColegiado)
                    VALUES (?, ?, ?, ?, ?, ?, 'A', 'C', ?)";
            $stmt = $db->prepare($sql);
            $stmt->execute([$value, $_SESSION['user_id'], $fechaCarga, $fechaPago, $idLugarPago, $observaciones, $idColegiado]);
            if ($stmt->rowCount() == 0) {
                $resultado['estado'] = FALSE;
                $resultado['mensaje'] = "ERROR AL GENERAR EL DETALLE";
                $resultado['clase'] = 'alert alert-danger';
                $resultado['icono'] = 'glyphicon glyphicon-remove';
                break;
            }
            $hayCuotas++;
        }
        if ($resultado['estado']) {
            foreach ($lasCuotasPP as $value) {
                $sql = "INSERT INTO pagosnoregistrados
                        (Recibo, IdUsuario, FechaCarga, FechaPago, IdLugarDePago, Detalle, Estado, TipoPago, IdColegiado)
                        VALUES (?, ?, ?, ?, ?, ?, 'A', 'P', ?)";
                $stmt = $db->prepare($sql);
                $stmt->execute([$value, $_SESSION['user_id'], $fechaCarga, $fechaPago, $idLugarPago, $observaciones, $idColegiado]);
                if ($stmt->rowCount() == 0) {
                    $resultado['estado'] = FALSE;
                    $resultado['mensaje'] = "ERROR AL GENERAR EL DETALLE";
                    $resultado['clase'] = 'alert alert-danger';
                    $resultado['icono'] = 'glyphicon glyphicon-remove';
                    break;
                }
                $hayCuotas++;
            }
            if ($hayCuotas > 0) {
                $resultado['estado'] = TRUE;
            } else {
                $resultado['estado'] = FALSE;
                $resultado['mensaje'] = "NO SE REGISTRARON PAGOS";
                $resultado['clase'] = 'alert alert-danger';
                $resultado['icono'] = 'glyphicon glyphicon-remove';
            }
        } else {
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "ERROR AL CARGAR PAGOS NO REGISTRADOS";
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-remove';
        }

        if ($resultado['estado']) {
            $resultado['mensaje'] = "SE REGISTRARON PAGOS CORRECTAMENTE";
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
            $db->commit();
        } else {
            $db->rollBack();
        }
        return $resultado;

    } catch (PDOException $e) {
        $db->rollBack();
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "ERROR AL CARGAR PAGOS NO REGISTRADOS";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
        return $resultado;
    }
}

    public function obtenerPagoNoregistradoPorId($idPagoNoRegistrado) {
    $db = Database::getConnection();
    $sql = "SELECT pagosnoregistrados.Id, colegiadodeudaanual.Periodo, colegiadodeudaanualcuotas.Cuota AS CuotaColegiacion,
            pagosnoregistrados.Recibo, pagosnoregistrados.FechaPago, pagosnoregistrados.FechaCarga,
            pagosnoregistrados.IdUsuario, pagosnoregistrados.Detalle, lugarpago.Detalle AS LugarDePago,
            pagosnoregistrados.TipoPago, planpagoscuotas.IdPlanPagos, planpagoscuotas.Cuota AS CuotaPP
            FROM pagosnoregistrados
            INNER JOIN lugarpago ON(lugarpago.Id = pagosnoregistrados.IdLugarDePago)
            LEFT JOIN colegiadodeudaanualcuotas ON(colegiadodeudaanualcuotas.Id = pagosnoregistrados.Recibo AND
                    pagosnoregistrados.TipoPago = 'C')
            LEFT JOIN colegiadodeudaanual ON(colegiadodeudaanual.Id = colegiadodeudaanualcuotas.IdColegiadoDeudaAnual)
            LEFT JOIN planpagoscuotas ON(planpagoscuotas.Id = pagosnoregistrados.Recibo AND
                    pagosnoregistrados.TipoPago = 'P')
            WHERE pagosnoregistrados.Id = ?";

    $resultado = array();
    try {
        $stmt = $db->prepare($sql);
        $stmt->execute([$idPagoNoRegistrado]);
        $row = $stmt->fetch();
        if ($row) {
            $datos = array(
                'idPagoNoRegistrado' => $row['Id'],
                'periodo' => $row['Periodo'],
                'cuota' => $row['CuotaColegiacion'],
                'recibo' => $row['Recibo'],
                'fechaPago' => $row['FechaPago'],
                'fechaCarga' => $row['FechaCarga'],
                'idUsuario' => $row['IdUsuario'],
                'detalle' => $row['Detalle'],
                'lugarPago' => $row['LugarDePago'],
                'tipoPago' => $row['TipoPago'],
                'idPlanPago' => $row['IdPlanPagos'],
                'cuotaPlanPago' => $row['CuotaPP']
            );
            $resultado['estado'] = TRUE;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "No hay Pagos No Registrados";
            $resultado['clase'] = 'alert alert-warning';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "Error buscando Pagos No Registrados";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function anularPagoNoRegistrado($idPagoNoRegistrado) {
    $db = Database::getConnection();
    $sql = "DELETE FROM pagosnoregistrados WHERE pagosnoregistrados.Id = ?";

    $resultado = array();
    try {
        $stmt = $db->prepare($sql);
        $stmt->execute([$idPagoNoRegistrado]);
        $resultado['estado'] = TRUE;
        $resultado['mensaje'] = "EL PAGO NO REGISTRADO SE ELIMINO CORRECTAMENTE";
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "No se pudo eliminar el Pago No Registrado";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}
}
