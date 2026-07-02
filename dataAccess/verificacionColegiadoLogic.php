<?php
class verificacionColegiadoLogic {

    public function tieneTituloEspecialistaParaRetirar($idColegiado){
    try {
        $db = Database::getConnection();
        $sql = "SELECT COUNT(tituloespecialista.IdTituloEspecialista)
            FROM tituloespecialista
            INNER JOIN resoluciondetalle ON(resoluciondetalle.Id = tituloespecialista.IdResolucionDetalle)
            WHERE resoluciondetalle.IdColegiado = ?
            AND tituloespecialista.FechaEmision >= '2016-01-01'
            AND tituloespecialista.FechaEntrega is null";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idColegiado]);
        $row = $stmt->fetch(PDO::FETCH_NUM);
        $resultado['estado'] = FALSE;
        if ($row && $row[0] > 0) {
            $resultado['estado'] = TRUE;
        }
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
    }
    return $resultado;
}

    public function tieneCostas($idColegiado){
    try {
        $db = Database::getConnection();
        $sql = "SELECT sum(colegiadosanciongasto.CantidadGalenos)
        FROM colegiadosanciongasto
        INNER JOIN colegiadosancion ON(colegiadosancion.id = colegiadosanciongasto.IdColegiadoSancion)
        WHERE colegiadosancion.IdColegiado = ?
        AND (colegiadosanciongasto.FechaPago is NULL OR colegiadosanciongasto.FechaPago = 0)";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idColegiado]);
        $row = $stmt->fetch(PDO::FETCH_NUM);
        $resultado['estado'] = FALSE;
        if ($row) {
            $cantidadGalenos = $row[0];
            if ($cantidadGalenos > 0) {
                $resultado['estado'] = TRUE;
                $resultado['costas'] = $cantidadGalenos;
            }
        }
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
    }
    return $resultado;
}

    public function tieneDocumentacionParaRetirar($idColegiado){
    try {
        $db = Database::getConnection();
        $sql = "SELECT rd.Id, tdr.Nombre
        FROM retirodocumentacion rd
        INNER JOIN tipodocumentacionretiro tdr ON tdr.Id = rd.IdTipoDocumentacionRetiro
        WHERE rd.IdColegiado = ? AND rd.Estado = 'A'";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idColegiado]);
        $datos = $stmt->fetchAll();
        $resultado['estado'] = FALSE;
        if (count($datos) > 0) {
            $rows = array();
            foreach ($datos as $row) {
                $rows[] = array(
                    'idRetiroDocumentacion' => $row['Id'],
                    'tipoDocumentacionRetiro' => $row['Nombre']
                );
            }
            $resultado['estado'] = TRUE;
            $resultado['datos'] = $rows;
            $resultado['mensaje'] = "OK";
        }
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "Error: " . $e->getMessage();
    }
    return $resultado;
}

    public function tienePagosPotTituloEspecialista($idColegiado, $fechaDesde, $fechaHasta) {
    try {
        $db = Database::getConnection();
        $sql = "SELECT cajadiariamovimientodetalle.CodigoPago, tipopago.Detalle,
            cajadiariamovimientootro.Descripcion, cajadiariamovimiento.Fecha,
            cajadiariamovimientodetalle.Monto
            FROM cajadiariamovimientodetalle
            INNER JOIN cajadiariamovimiento on(cajadiariamovimiento.Id = cajadiariamovimientodetalle.IdCajaDiariaMovimiento)
            LEFT JOIN cajadiariamovimientootro on(cajadiariamovimientootro.IdCajaDiariaMovimiento = cajadiariamovimiento.Id)
            INNER JOIN tipopago on(tipopago.Id = cajadiariamovimientodetalle.CodigoPago)
            WHERE cajadiariamovimiento.IdColegiado = ?
            AND cajadiariamovimiento.Fecha BETWEEN ? AND ?
            AND cajadiariamovimientodetalle.CodigoPago in(55, 72, 59, 38, 61, 37, 52, 56)
            AND cajadiariamovimiento.Estado <> 'A'
            ORDER BY cajadiariamovimiento.Fecha";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idColegiado, $fechaDesde, $fechaHasta]);
        $datos = $stmt->fetchAll();
        if (count($datos) > 0) {
            $rows = array();
            foreach ($datos as $row) {
                $rows[] = array(
                    'codigoPago' => $row['CodigoPago'],
                    'detalle' => $row['Detalle'],
                    'descripcion' => $row['Descripcion'],
                    'fechaPago' => $row['Fecha'],
                    'monto' => $row['Monto']
                );
            }
            $resultado['estado'] = TRUE;
            $resultado['datos'] = $rows;
            $resultado['mensaje'] = "OK";
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "NO SE ENCONTRO PAGO";
            $resultado['clase'] = 'alert alert-warning';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "Error buscando PAGO";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
    }
    return $resultado;
}

    public function obtenerColegiadoAutoprescripcion($idColegiado){
    try {
        $db = Database::getConnection();
        $sql = "SELECT mesaentrada.IdMesaEntrada, mesaentrada.FechaIngreso, usuario.Usuario,
        mesaentradaautoprescripcion.Autorizado, mesaentradaautoprescripcion.DocumentoAutorizado,
        mesaentradaautoprescripcion.Parentezco, mesaentradaautoprescripcion.Autorizado2,
        mesaentradaautoprescripcion.DocumentoAutorizado2, mesaentradaautoprescripcion.Parentezco2
    FROM mesaentrada
    INNER JOIN mesaentradaautoprescripcion ON(mesaentradaautoprescripcion.IdMesaEntrada = mesaentrada.IdMesaEntrada)
    LEFT JOIN usuario ON(usuario.Id = mesaentrada.IdUsuario)
    WHERE mesaentrada.IdColegiado = ? AND mesaentrada.Estado = 'A'";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idColegiado]);
        $datos = $stmt->fetchAll();
        $resultado['estado'] = TRUE;
        $rows = array();
        if (count($datos) > 0) {
            foreach ($datos as $row) {
                $rows[] = array(
                    'id' => $row['IdMesaEntrada'],
                    'fechaIngreso' => $row['FechaIngreso'],
                    'nombreUsuario' => $row['Usuario'],
                    'autorizado1' => $row['Autorizado'],
                    'documento1' => $row['DocumentoAutorizado'],
                    'parentezco1' => $row['Parentezco'],
                    'autorizado2' => $row['Autorizado2'],
                    'documento2' => $row['DocumentoAutorizado2'],
                    'parentezco2' => $row['Parentezco2']
                );
            }
            $resultado['mensaje'] = "OK";
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['mensaje'] = "NO SE ENCONTRO AUTOPRESCRIPCION";
            $resultado['clase'] = 'alert alert-warning';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
        $resultado['datos'] = $rows;
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "Error buscando AUTOPRESCRIPCION";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
    }
    return $resultado;
}

    public function tieneExpediente($idColegiado){
    try {
        $db = Database::getConnection();
        $sql="SELECT (COUNT(*) + (SELECT COUNT(*) FROM eticaexpedientedenunciados eed WHERE eed.IdColegiado = ?)) AS cantidad
        FROM eticaexpediente ee
        WHERE ee.IdColegiado = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idColegiado, $idColegiado]);
        $row = $stmt->fetch();
        if ($row && $row['cantidad'] > 0) {
            return true;
        } else {
            return false;
        }
    } catch (PDOException $e) {
        return false;
    }
}
}
