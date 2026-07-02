<?php
//tipo de debito
define("TARJETA_DEBITO", 'D');
define("TARJETA_CREDITO", 'C');
define("CBU", 'H');

class debitoAutomaticoLogic {

    public function obtenerEnvioDebitoPorId($idEnvioDebito) {
    $resultado = array();
    try {
        $db = Database::getConnection();
        $sql = "SELECT a.FechaEnvio, a.FechaDebito, a.Tipo, a.NombreArchivo, a.PathArchivo
            FROM enviodebito a
            WHERE a.Id = ? AND a.Borrado = 0";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idEnvioDebito]);
        $rows = $stmt->fetchAll();
        $resultado['cantidad'] = count($rows);
        if ($resultado['cantidad'] > 0) {
            $row = $rows[0];
            $datos = array(
                'fechaEnvio' => $row['FechaEnvio'],
                'fechaDebito' => $row['FechaDebito'],
                'tipo' => $row['Tipo'],
                'nombreArchivo' => $row['NombreArchivo'],
                'pathArchivo' => $row['PathArchivo']
            );
            $resultado['estado'] = true;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = false;
            $resultado['mensaje'] = "No se encontro envio debito automatico";
            $resultado['clase'] = 'alert alert-info';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando envio home banking";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function obtenerDebitoAutomaticoGenerados($anio, $mes, $tipoDebito) {
    $resultado = array();
    try {
        $db = Database::getConnection();
        if (isset($tipoDebito) && $tipoDebito <> "") {
            $conTipoDebito = " AND ed.Tipo = '".$tipoDebito."'";
        } else {
            $conTipoDebito = "";
        }
        if (isset($mes) && $mes > "0") {
            $conMes = " AND SUBSTR(ed.FechaEnvio, 6, 2) = '".$mes."'";
        } else {
            $conMes = "";
        }
        $sql = "SELECT ed.Id, ed.Tipo, ed.FechaEnvio, ed.FechaDebito, ed.NombreArchivo, ed.PathArchivo, ed.Borrado, (SELECT SUM(a.Importe) FROM enviodebitodetallecuota a INNER JOIN enviodebitodetalle b ON b.Id = a.IdEnvioDebitoDetalle WHERE b.IdEnvioDebito = ed.Id) AS TotalDebitar
                FROM enviodebito ed
                WHERE SUBSTR(ed.FechaEnvio, 1, 4) = ? ".$conMes." ".$conTipoDebito;
        $stmt = $db->prepare($sql);
        $stmt->execute([$anio]);
        $rows = $stmt->fetchAll();
        $resultado['cantidad'] = count($rows);
        if ($resultado['cantidad'] > 0) {
            $datos = array();
            foreach ($rows as $row) {
                $r = array(
                    'idDebitoAutomatico' => $row['Id'],
                    'tipoDebito' => $row['Tipo'],
                    'fechaProceso' => $row['FechaEnvio'],
                    'fechaDebito' => $row['FechaDebito'],
                    'totalDebitar' => $row['TotalDebitar'],
                    'nombreArchivo' => $row['NombreArchivo'],
                    'pathArchivo' => $row['PathArchivo'],
                    'borrado' => $row['Borrado']
                );
                array_push($datos, $r);
            }
            $resultado['estado'] = true;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = false;
            $resultado['mensaje'] = "No se encontro envio debito automático";
            $resultado['clase'] = 'alert alert-info';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando envio debito automático";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function obtenerColegiadosPorDebitoCbu($tipoDebito){
    $resultado = array();
    $resultado['estado'] = true;
    try {
        $db = Database::getConnection();
        if ($tipoDebito == "H") {
            $sql = "SELECT c.Id AS IdColegiado, d.Id AS IdDebito
                FROM debitocbu d
                INNER JOIN colegiado c ON c.Id = d.IdColegiado
                INNER JOIN tipomovimiento tm ON tm.Id = c.Estado
                WHERE tm.Estado = 'A' AND d.Estado = 'A' AND d.CBUBloque1 <> ''
                ORDER BY c.Matricula";
        } else {
            if ($tipoDebito == "C" || $tipoDebito == "D") {
                $sql = "SELECT c.Id AS IdColegiado, d.Id AS IdDebito
                    FROM debitotarjeta d
                    INNER JOIN colegiado c ON c.Id = d.IdColegiado
                    INNER JOIN tipomovimiento tm ON tm.Id = c.Estado
                    WHERE tm.Estado = 'A' AND d.Estado = 'A' AND d.Tipo = '".$tipoDebito."'
                    ORDER BY c.Matricula";
            } else {
                $resultado['estado'] = false;
                $resultado['mensaje'] = "Error buscando envio debito automático";
                $resultado['clase'] = 'alert alert-danger';
                $resultado['icono'] = 'glyphicon glyphicon-remove';
                return $resultado;
            }
        }
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll();
        $resultado['cantidad'] = count($rows);
        if ($resultado['cantidad'] > 0) {
            $datos = array();
            foreach ($rows as $row) {
                $r = array(
                    'idColegiado' => $row['IdColegiado'],
                    'idDebito' => $row['IdDebito']
                );
                array_push($datos, $r);
            }
            $resultado['estado'] = true;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = false;
            $resultado['mensaje'] = "No se encontro debito automático para generar";
            $resultado['clase'] = 'alert alert-info';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando debito automático para generar";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function obtenerColegiadoDebitoRechazado($rango, $idLugarPago){
    $resultado = array();
    try {
        $db = Database::getConnection();
        if ($idLugarPago == 28) {
            $sql = "SELECT cobranza.Id AS IdCobranza, cobranzanovedades.Id AS IdCobranzaNovedades, cobranzanovedades.IdColegiado,
                colegiado.Matricula, persona.Sexo, persona.Apellido, persona.Nombres, colegiadocontacto.CorreoElectronico,
                cobranzanovedades.Detalle, debitotarjeta.Tipo AS TipoTarjeta, '' AS TipoCuenta
                FROM cobranza
                INNER JOIN cobranzanovedades ON(cobranzanovedades.IdCobranza = cobranza.Id)
                INNER JOIN colegiado ON(colegiado.Id = cobranzanovedades.IdColegiado)
                INNER JOIN tipomovimiento ON(tipomovimiento.Id = colegiado.Estado
                    AND tipomovimiento.Estado = 'A')
                INNER JOIN persona ON(persona.Id = colegiado.IdPersona)
                INNER JOIN colegiadocontacto ON(colegiadocontacto.IdColegiado = colegiado.Id
                    AND colegiadocontacto.IdEstado = 1
                    AND colegiadocontacto.CorreoElectronico is not null
                    AND UPPER(colegiadocontacto.CorreoElectronico) <> 'NR'
                    AND colegiadocontacto.CorreoElectronico <> '')
                LEFT JOIN debitotarjeta ON(debitotarjeta.IdColegiado = cobranzanovedades.IdColegiado
                    AND debitotarjeta.Estado IN('A', 'B'))
                LEFT JOIN enviomaildiariocolegiado ON(enviomaildiariocolegiado.IdColegiado = cobranzanovedades.IdColegiado
                    AND enviomaildiariocolegiado.IdReferencia = cobranza.Id)
                WHERE cobranza.IdLugarPago = ?
                    AND cobranza.EnvioMail = 'N'
                    AND enviomaildiariocolegiado.Id IS NULL
                GROUP BY colegiado.Matricula
                ORDER BY colegiado.Matricula
                LIMIT ?";
        } else {
            $sql = "SELECT cobranza.Id AS IdCobranza, cobranzanovedades.Id AS IdCobranzaNovedades, cobranzanovedades.IdColegiado,
                colegiado.Matricula, persona.Sexo, persona.Apellido, persona.Nombres, colegiadocontacto.CorreoElectronico,
                cobranzanovedades.Detalle, ' ' AS TipoTarjeta, debitocbu.Tipo AS TipoCuenta
                FROM cobranza
                INNER JOIN cobranzanovedades ON(cobranzanovedades.IdCobranza = cobranza.Id)
                INNER JOIN colegiado ON(colegiado.Id = cobranzanovedades.IdColegiado)
                INNER JOIN tipomovimiento ON(tipomovimiento.Id = colegiado.Estado
                    AND tipomovimiento.Estado = 'A')
                INNER JOIN persona ON(persona.Id = colegiado.IdPersona)
                INNER JOIN colegiadocontacto ON(colegiadocontacto.IdColegiado = colegiado.Id
                    AND colegiadocontacto.IdEstado = 1
                    AND colegiadocontacto.CorreoElectronico is not null
                    AND UPPER(colegiadocontacto.CorreoElectronico) <> 'NR'
                    AND colegiadocontacto.CorreoElectronico <> '')
                LEFT JOIN debitocbu ON(debitocbu.IdColegiado = cobranzanovedades.IdColegiado
                    AND debitocbu.Estado IN('A', 'B'))
                LEFT JOIN enviomaildiariocolegiado ON(enviomaildiariocolegiado.IdColegiado = cobranzanovedades.IdColegiado
                    AND enviomaildiariocolegiado.IdReferencia = cobranzanovedades.Id)
                WHERE cobranza.IdLugarPago = ?
                    AND cobranza.EnvioMail = 'N'
                    AND enviomaildiariocolegiado.Id IS NULL
                GROUP BY colegiado.Matricula
                ORDER BY colegiado.Matricula
                LIMIT ?";
        }
        $stmt = $db->prepare($sql);
        $stmt->execute([$idLugarPago, $rango]);
        $rows = $stmt->fetchAll();
        $resultado['cantidad'] = count($rows);
        if ($resultado['cantidad'] > 0) {
            $datos = array();
            foreach ($rows as $row) {
                $idCobranza = $row['IdCobranza'];
                $idCobranzaNovedades = $row['IdCobranzaNovedades'];
                if ($idLugarPago == 28) {
                    $idReferencia = $idCobranza;
                } else {
                    $idReferencia = $idCobranzaNovedades;
                }
                $r = array(
                    'idCobranza' => $idCobranza,
                    'idReferencia' => $idReferencia,
                    'idColegiado' => $row['IdColegiado'],
                    'matricula' => $row['Matricula'],
                    'sexo' => $row['Sexo'],
                    'apellido' => $row['Apellido'],
                    'nombres' => $row['Nombres'],
                    'mail' => $row['CorreoElectronico'],
                    'detalle' => $row['Detalle'],
                    'tipoTarjeta' => $row['TipoTarjeta'],
                    'tipoCuenta' => $row['TipoCuenta']
                );
                array_push($datos, $r);
            }
            $resultado['estado'] = true;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = false;
            $resultado['mensaje'] = "No se encontro rechazo de debito del colegiado";
            $resultado['clase'] = 'alert alert-info';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando notificacion del colegiado";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function obtenerColegiadoStopDebitPorBono($rango){
    $resultado = array();
    try {
        $db = Database::getConnection();
        $sql = "SELECT cobranzanovedades.Id, cobranzanovedades.IdColegiado,
            colegiado.Matricula, persona.Sexo, persona.Apellido, persona.Nombres, colegiadocontacto.CorreoElectronico
            FROM cobranza
            INNER JOIN cobranzanovedades ON(cobranzanovedades.IdCobranza = cobranza.Id)
            INNER JOIN colegiado ON(colegiado.Id = cobranzanovedades.IdColegiado)
            INNER JOIN tipomovimiento ON(tipomovimiento.Id = colegiado.Estado
                AND tipomovimiento.Estado = 'A')
            INNER JOIN persona ON(persona.Id = colegiado.IdPersona)
            INNER JOIN colegiadocontacto ON(colegiadocontacto.IdColegiado = colegiado.Id
                AND colegiadocontacto.IdEstado = 1
                AND colegiadocontacto.CorreoElectronico is not null
                AND UPPER(colegiadocontacto.CorreoElectronico) <> 'NR'
                AND colegiadocontacto.CorreoElectronico <> '')
            INNER JOIN debitocbu ON(debitocbu.IdColegiado = cobranzanovedades.IdColegiado
                AND debitocbu.Estado='A' AND debitocbu.IdBanco = 1)
            LEFT JOIN enviomaildiariocolegiado ON(enviomaildiariocolegiado.IdColegiado = cobranzanovedades.IdColegiado
                AND enviomaildiariocolegiado.IdReferencia = cobranzanovedades.Id
                    AND enviomaildiariocolegiado.IdEnvioMailDiario = 14)
            WHERE cobranzanovedades.IdCobranza = (SELECT MAX(Id) FROM cobranza WHERE cobranza.IdLugarPago = 30)
                AND cobranza.EnvioMail = 'N'
                AND enviomaildiariocolegiado.Id IS NULL
            GROUP BY colegiado.Matricula
            ORDER BY colegiado.Matricula
            LIMIT ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$rango]);
        $rows = $stmt->fetchAll();
        $resultado['cantidad'] = count($rows);
        if ($resultado['cantidad'] > 0) {
            $datos = array();
            foreach ($rows as $row) {
                $idReferencia = $row['Id'];
                $r = array(
                    'idReferencia' => $idReferencia,
                    'idColegiado' => $row['IdColegiado'],
                    'matricula' => $row['Matricula'],
                    'sexo' => $row['Sexo'],
                    'apellido' => $row['Apellido'],
                    'nombres' => $row['Nombres'],
                    'mail' => $row['CorreoElectronico']
                );
                array_push($datos, $r);
            }
            $resultado['estado'] = true;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = false;
            $resultado['mensaje'] = "No se encontro rechazo de debito del colegiado";
            $resultado['clase'] = 'alert alert-info';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando notificacion del colegiado";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function agregarEnvioDebito($tipoDebito, $fechaDebito, $nombreArchivo, $path) {
    try {
        $db = Database::getConnection();
        $db->beginTransaction();
        $resultado = array();

        $sql="INSERT INTO enviodebito
                (FechaEnvio, FechaDebito, Tipo, NombreArchivo, PathArchivo)
                VALUE (NOW(), ?, ?, ?, ?)";
        $stmt = $db->prepare($sql);
        $stmt->execute([$fechaDebito, $tipoDebito, $nombreArchivo, $path]);
        $idEnvioDebito = $db->lastInsertId();
        $resultado['estado'] = TRUE;
        $resultado['mensaje'] = "OK";
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
        $resultado['idEnvioDebito'] = $idEnvioDebito;

        $db->commit();
        return $resultado;
    } catch (PDOException $e) {
        $db->rollBack();
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "ERROR AL INSERTAR enviodebito ".$e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
        return $resultado;
    }
}

    public function agregarEnvioDebitoDetalle($idEnvioDebito, $idDebitoTarjetaCbu, $arrayCuotas){
    try {
        $db = Database::getConnection();
        $db->beginTransaction();
        $resultado = array();

        $sql="INSERT INTO enviodebitodetalle (IdEnvioDebito, IdDebitoTarjeta)
                VALUE (?, ?)";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idEnvioDebito, $idDebitoTarjetaCbu]);
        $idEnvioDebitoDetalle = $db->lastInsertId();
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "";
        foreach ($arrayCuotas as $cuotaDeuda) {
            if (!isset($cuotaDeuda['idColegiadoDeudaAnualCuota'])) break;
            $idRelacion = $cuotaDeuda['idColegiadoDeudaAnualCuota'];
            $importe = $cuotaDeuda['importeActualizado'];
            $sql="INSERT INTO enviodebitodetallecuota (IdEnvioDebitoDetalle, TipoCuota, IdRelacion, Importe)
                VALUES (?, 'C', ?, ?)";
            $stmt = $db->prepare($sql);
            $stmt->execute([$idEnvioDebitoDetalle, $idRelacion, $importe]);
            $resultado['estado'] = TRUE;
        }

        if ($resultado['estado']) {
            $resultado['estado'] = TRUE;
            $resultado['mensaje'] = 'SE GENERO EL CONCEPTO';
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
            $db->commit();
            return $resultado;
        } else {
            $db->rollBack();
            return $resultado;
        }
    } catch (PDOException $e) {
        $db->rollBack();
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "ERROR AL INSERTAR CUOTAS EN enviodebitodetalle ".$e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
        return $resultado;
    }
}

    public function actualizarEnvioDebito($idEnvioDebito, $nombreArchivo, $path) {
    try {
        $db = Database::getConnection();
        $db->beginTransaction();
        $resultado = array();

        $sql="UPDATE enviodebito
                SET NombreArchivo = ?, PathArchivo = ?
                WHERE Id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$nombreArchivo, $path, $idEnvioDebito]);
        $resultado['estado'] = TRUE;
        $resultado['mensaje'] = "OK";
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';

        $db->commit();
        return $resultado;
    } catch (PDOException $e) {
        $db->rollBack();
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "ERROR AL ACTUALIZAR enviodebito ".$e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
        return $resultado;
    }
}

    public function obtenerEnvioDebitoDetallePorIdEnvio($idEnvioDebito, $tipoDebito) {
    $resultado = array();
    try {
        $db = Database::getConnection();
        if ($tipoDebito == CBU) {
            $sql = "SELECT c.Matricula, d.IdBanco, d.IdColegiado, d.Tipo, d.CBUBloque1, d.CBUBloque2, edd.Id, SUM(eddc.Importe) AS TotalCuotas, p.Apellido, p.Nombres
                FROM colegiado c
                INNER JOIN debitocbu d ON d.IdColegiado = c.Id
                INNER JOIN enviodebitodetalle edd ON d.id = edd.IdDebitoTarjeta
                INNER JOIN enviodebitodetallecuota eddc ON eddc.IdEnvioDebitoDetalle = edd.Id
                INNER JOIN persona p ON p.Id = c.IdPersona
                WHERE edd.IdEnvioDebito = ?
                GROUP BY c.Matricula";
            $stmt = $db->prepare($sql);
            $stmt->execute([$idEnvioDebito]);
            $rows = $stmt->fetchAll();
            $resultado['cantidad'] = count($rows);
            if ($resultado['cantidad'] > 0) {
                $datos = array();
                foreach ($rows as $row) {
                    $r = array(
                        'matricula' => $row['Matricula'],
                        'idBanco' => $row['IdBanco'],
                        'idColegiado' => $row['IdColegiado'],
                        'tipoCuenta' => $row['Tipo'],
                        'cbuBloque1' => $row['CBUBloque1'],
                        'cbuBloque2' => $row['CBUBloque2'],
                        'idEnvioDebitoDetalle' => $row['Id'],
                        'importeDebitar' => $row['TotalCuotas'],
                        'apellido' => $row['Apellido'],
                        'nombre' => $row['Nombres']
                    );
                    array_push($datos, $r);
                }
                $resultado['estado'] = true;
                $resultado['mensaje'] = "OK";
                $resultado['datos'] = $datos;
                $resultado['clase'] = 'alert alert-success';
                $resultado['icono'] = 'glyphicon glyphicon-ok';
            } else {
                $resultado['estado'] = false;
                $resultado['mensaje'] = "No se encontro envio debito automatico";
                $resultado['clase'] = 'alert alert-info';
                $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
            }
        } else {
            $sql = "SELECT c.Matricula, p.NumeroDocumento, dt.NumeroTarjeta, dt.PrimerProceso, c.Id AS IdColegiado, dt.IncluyePlanPagos, dt.id AS IdDebitoTarjeta, dt.PagoTotal, SUM(eddc.Importe) AS TotalCuotas, edd.Id AS IdEnvioDebitoDetalle, p.Apellido, p.Nombres
                    FROM debitotarjeta dt
                    INNER JOIN colegiado c ON c.Id = dt.IdColegiado
                    INNER JOIN enviodebitodetalle edd ON dt.id = edd.IdDebitoTarjeta
                    INNER JOIN enviodebitodetallecuota eddc ON eddc.IdEnvioDebitoDetalle = edd.Id
                    INNER JOIN persona p ON p.Id = c.IdPersona
                    WHERE edd.IdEnvioDebito = ?  AND dt.Estado = 'A' AND dt.Tipo = ?
                    GROUP BY edd.Id
                    ORDER BY c.Matricula";
            $stmt = $db->prepare($sql);
            $stmt->execute([$idEnvioDebito, $tipoDebito]);
            $rows = $stmt->fetchAll();
            $resultado['cantidad'] = count($rows);
            if ($resultado['cantidad'] > 0) {
                $datos = array();
                foreach ($rows as $row) {
                    $r = array(
                        'matricula' => $row['Matricula'],
                        'numeroDocumento' => $row['NumeroDocumento'],
                        'numeroTarjeta' => substr($row['NumeroTarjeta'], 0, 16),
                        'primerProceso' => $row['PrimerProceso'],
                        'idColegiado' => $row['IdColegiado'],
                        'incluyePlanPagos' => $row['IncluyePlanPagos'],
                        'idDebitoTarjeta' => $row['IdDebitoTarjeta'],
                        'pagoTotal' => $row['PagoTotal'],
                        'importeDebitar' => $row['TotalCuotas'],
                        'idEnvioDebitoDetalle' => $row['IdEnvioDebitoDetalle'],
                        'apellido' => $row['Apellido'],
                        'nombre' => $row['Nombres']
                    );
                    array_push($datos, $r);
                }
                $resultado['estado'] = true;
                $resultado['mensaje'] = "OK";
                $resultado['datos'] = $datos;
                $resultado['clase'] = 'alert alert-success';
                $resultado['icono'] = 'glyphicon glyphicon-ok';
            } else {
                $resultado['estado'] = false;
                $resultado['mensaje'] = "No se encontro envio debito automatico";
                $resultado['clase'] = 'alert alert-info';
                $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
            }
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando envio home banking";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function obtenerTotalEnvioDebitoPorId($idEnvioDebito) {
    $resultado = array();
    try {
        $db = Database::getConnection();
        $sql = "SELECT COUNT(DISTINCT eddc.IdEnvioDebitoDetalle) AS CantidadDebitar, SUM(eddc.Importe) AS TotalDebitar
                FROM enviodebitodetalle edd
                INNER JOIN enviodebitodetallecuota eddc ON eddc.IdEnvioDebitoDetalle = edd.Id
                WHERE edd.IdEnvioDebito = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idEnvioDebito]);
        $row = $stmt->fetch();
        $resultado['estado'] = true;
        $resultado['mensaje'] = "OK";
        $resultado['cantidadDebitar'] = $row ? $row['CantidadDebitar'] : 0;
        $resultado['totalDebitar'] = $row ? $row['TotalDebitar'] : 0;
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando envio totales enviodebito";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function obtenerEnvioDebitoDetalleCuotas($idEnvioDebitoDetalle) {
    $resultado = array();
    try {
        $db = Database::getConnection();
        $sql = "SELECT da.Periodo, dac.Cuota, eddc.Importe, eddc.IdRelacion
                FROM enviodebitodetallecuota eddc
                INNER JOIN colegiadodeudaanualcuotas dac ON dac.Id = eddc.IdRelacion
                INNER JOIN colegiadodeudaanual da ON da.Id = dac.IdColegiadoDeudaAnual
                WHERE eddc.IdEnvioDebitoDetalle = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idEnvioDebitoDetalle]);
        $rows = $stmt->fetchAll();
        $resultado['cantidad'] = count($rows);
        if ($resultado['cantidad'] > 0) {
            $datos = array();
            foreach ($rows as $row) {
                $r = array(
                    'periodo' => $row['Periodo'],
                    'cuota' => $row['Cuota'],
                    'importe' => $row['Importe'],
                    'recibo' => $row['IdRelacion']
                );
                array_push($datos, $r);
            }
            $resultado['estado'] = true;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = false;
            $resultado['mensaje'] = "No se encontro envio debito automatico";
            $resultado['clase'] = 'alert alert-info';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando envio home banking";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function borrarEnvioDebito($idEnvioDebito) {
    $resultado = array();
    try {
        $db = Database::getConnection();
        $sql="UPDATE enviodebito
                SET Borrado = 1, Estado = 'B'
                WHERE Id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idEnvioDebito]);
        $resultado['estado'] = TRUE;
        $resultado['mensaje'] = "OK";
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "ERROR AL BORRAR enviodebito ".$e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}


    public function obtenerTipoDebito($tipoDebito) {
    switch ($tipoDebito) {
        case 'D':
            $tipo = "Tarjeta Débito";
            break;

        case 'C':
            $tipo = "Tarjeta Crédito";
            break;

        case 'H':
            $tipo = "CBU";
            break;

        default:
            $tipo = "Sin dato";
            break;
    }
    return $tipo;
}
}
