<?php

define("TIPO_PAGO_CUOTA_COLEGIACION", 1);
define("TIPO_PAGO_CUOTA_PLAN_PAGO", 2);
define("TIPO_PAGO_CUOTA_PERIODO_ANTERIOR", 3);
define("TIPO_PAGO_NOTIFICACION", 4);
define("TIPO_PAGO_CURSO", 7);
define("TIPO_PAGO_TOTAL", 8);

class cobranzaLogic {

    public function obtenerLotes($anio, $idLugarPago) {
    try {
        $db = Database::getConnection();
        if (isset($anio) && $anio <> "0") {
            $porAnio = " AND YEAR(c.FechaApertura) = ".$anio;
        } else {
            $porAnio = "";
        }
        if (isset($idLugarPago) && $idLugarPago <> "") {
            $porLugar = " AND c.IdLugarPago = ".$idLugarPago;
        } else {
            $porLugar = "";
        }
        $sql="SELECT c.Id, c.IdLugarPago, lp.Detalle AS NombreLugarPago, c.CantidadComprobantes, c.TotalRecaudacion, c.FechaApertura, c.TipoLote, c.NumeroLoteManual, c.DiferenciaImporte, c.DiferenciaComprobantes, c.Estado, c.Archivo, c.FechaProceso, c.IdUsuarioProceso, c.EnvioMail, c.Observacion
            FROM cobranza c
            INNER JOIN lugarpago lp ON lp.Id = c.IdLugarPago
            WHERE 1 = 1".$porAnio.$porLugar." ORDER BY c.FechaApertura";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $resultado = array();
        $datos = array();
        foreach ($rows as $r) {
            $tipoLote = $r['TipoLote'];
            if ($tipoLote == 'M') {
                $tipoLote = "MANUAL";
            } else {
                $tipoLote = "ELECTRONICO";
            }
            $row = array(
                'id' => $r['Id'],
                'idLugarPago' => $r['IdLugarPago'],
                'nombreLugarPago' => $r['NombreLugarPago'],
                'cantidadComprobantes' => $r['CantidadComprobantes'],
                'totalRecaudacion' => $r['TotalRecaudacion'],
                'fechaApertura' => $r['FechaApertura'],
                'tipoLote' => $tipoLote,
                'numeroLoteManual' => $r['NumeroLoteManual'],
                'diferenciaImporte' => $r['DiferenciaImporte'],
                'diferenciaComprobantes' => $r['DiferenciaComprobantes'],
                'estado' => $r['Estado'],
                'archivo' => $r['Archivo'],
                'fechaProceso' => $r['FechaProceso'],
                'idUsuario' => $r['IdUsuarioProceso'],
                'envioMail' => $r['EnvioMail'],
                'observaciones' => $r['Observacion']
            );
            array_push($datos, $row);
        }
        $resultado['estado'] = TRUE;
        $resultado['mensaje'] = "OK";
        $resultado['datos'] = $datos;
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "Error buscando lotes";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
    }
    return $resultado;
}

    public function verificarArchivoExistente($idLugarPago, $archivoLote) {
    $anio = date('Y');
    try {
        $db = Database::getConnection();
        $sql="SELECT COUNT(Id) AS Cantidad
                FROM cobranza
                WHERE IdLugarPago = ? AND Archivo = ? AND Estado <> 'B' AND YEAR(FechaApertura) = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idLugarPago, $archivoLote, $anio]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row && $row['Cantidad'] > 0) {
            return TRUE;
        } else {
            return FALSE;
        }
    } catch (PDOException $e) {
        return FALSE;
    }
}

    public function obtenerLote($idLugarPago, $archivoLote) {
    try {
        $db = Database::getConnection();
        $sql="SELECT Id
                FROM cobranza
                WHERE IdLugarPago = ? AND Archivo = ? AND Estado <> 'B'";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idLugarPago, $archivoLote]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $resultado = array();
        if ($row && $row['Id'] > 0) {
            $resultado['idCobranza'] = $row['Id'];
        } else {
            $resultado['idCobranza'] = NULL;
        }
    } catch (PDOException $e) {
        $resultado = array();
        $resultado['idCobranza'] = NULL;
    }
    return $resultado;
}

    public function obtenerLotePorId($idCobranza) {
    try {
        $db = Database::getConnection();
        $sql="SELECT c.Id, c.IdLugarPago, c.FechaApertura, c.TotalRecaudacion, c.CantidadComprobantes, c.Estado, lp.Detalle, c.TipoLote, c.NumeroLoteManual, c.DiferenciaImporte, c.PeriodoContable, c.NumeroParte
            FROM cobranza c
            INNER JOIN lugarpago lp ON lp.Id = c.IdLugarPago
            WHERE c.Id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idCobranza]);
        $r = $stmt->fetch(PDO::FETCH_ASSOC);

        $resultado = array();
        if ($r) {
            $tipoLote = $r['TipoLote'];
            if ($tipoLote == 'M') {
                $tipoLote = "MANUAL";
            } else {
                $tipoLote = "ELECTRONICO";
            }
            $datos = array(
                'idCobranza' => $r['Id'],
                'idLugarPago' => $r['IdLugarPago'],
                'lugarPago' => $r['Detalle'],
                'cantidadComprobantes' => $r['CantidadComprobantes'],
                'totalRecaudacion' => $r['TotalRecaudacion'],
                'fechaApertura' => $r['FechaApertura'],
                'estado' => $r['Estado'],
                'tipoLote' => $tipoLote,
                'numeroLoteManual' => $r['NumeroLoteManual'],
                'diferenciaImporte' => $r['DiferenciaImporte'],
                'periodo' => $r['PeriodoContable'],
                'cuota' => $r['NumeroParte']
            );
            $resultado['estado'] = TRUE;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "Error buscando lote";
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado = array();
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "Error buscando lote";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
    }
    return $resultado;
}

    public function obtenerDetalleLote($idCobranza){
    try {
        $db = Database::getConnection();
        $sql="SELECT cd.Id, cd.Periodo, cd.Cuota, cd.FechaPago, cd.Importe, cd.Recibo, cd.Recargo, cd.IdColegiado, cd.IdAsistente, cd.CodigoPago, tp.Detalle, c.Matricula, p.Apellido, p.Nombres, ca.ApellidoNombre, cd.TipoPago
            FROM cobranzadetalle cd
            INNER JOIN tipopago tp ON tp.Id = cd.CodigoPago
            LEFT JOIN colegiado c ON c.Id = cd.IdColegiado
            LEFT JOIN persona p ON p.Id = c.IdPersona
            LEFT JOIN cursosasistente ca ON ca.Id = cd.IdAsistente
            WHERE cd.IdLoteCobranza = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idCobranza]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $resultado = array();
        $datos = array();
        foreach ($rows as $r) {
            $row = array(
                'idCobranzaDetalle' => $r['Id'],
                'periodo' => $r['Periodo'],
                'cuota' => $r['Cuota'],
                'fechaPago' => $r['FechaPago'],
                'importe' => $r['Importe'],
                'recibo' => $r['Recibo'],
                'recargo' => $r['Recargo'],
                'idColegiado' => $r['IdColegiado'],
                'idAsistente' => $r['IdAsistente'],
                'idTipoPago' => $r['CodigoPago'],
                'tipoPago' => $r['Detalle'],
                'matricula' => $r['Matricula'],
                'apellido' => $r['Apellido'],
                'nombre' => $r['Nombres'],
                'asistente' => $r['ApellidoNombre'],
                'detalleTipoPago' => $r['TipoPago']
            );
            array_push($datos, $row);
        }
        $resultado['estado'] = TRUE;
        $resultado['mensaje'] = "OK";
        $resultado['datos'] = $datos;
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado = array();
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "Error buscando pagos del lote ".$idCobranza;
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
    }
    return $resultado;
}

    public function obtenerCobranzaDetallePorId($idCobranzaDetalle){
    try {
        $db = Database::getConnection();
        $sql="SELECT cd.IdLoteCobranza, c.FechaApertura, c.IdLugarPago, cd.Periodo, cd.Cuota, cd.FechaPago, cd.Importe, cd.Recibo, cd.Recargo, cd.IdColegiado, cd.IdAsistente, cd.CodigoPago
            FROM cobranzadetalle cd
            INNER JOIN cobranza c ON c.Id = cd.IdLoteCobranza
            WHERE cd.Id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idCobranzaDetalle]);
        $r = $stmt->fetch(PDO::FETCH_ASSOC);

        $resultado = array();
        if ($r) {
            $datos = array(
                'idLoteCobranza' => $r['IdLoteCobranza'],
                'fechaApertura' => $r['FechaApertura'],
                'idLugarPago' => $r['IdLugarPago'],
                'periodo' => $r['Periodo'],
                'cuota' => $r['Cuota'],
                'fechaPago' => $r['FechaPago'],
                'importe' => $r['Importe'],
                'recibo' => $r['Recibo'],
                'recargo' => $r['Recargo'],
                'idColegiado' => $r['IdColegiado'],
                'idAsistente' => $r['IdAsistente'],
                'codigoPago' => $r['CodigoPago']
            );
            $resultado['estado'] = TRUE;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "Error buscando pago";
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado = array();
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "Error buscando pago";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
    }
    return $resultado;
}

    public function eliminarCobranzaDetalle($idCobranzaDetalle) {
    try {
        $db = Database::getConnection();
        $sql="DELETE FROM cobranzadetalle WHERE Id = ?";
        echo $sql.' id->'.$idCobranzaDetalle;
        $stmt = $db->prepare($sql);
        $stmt->execute([$idCobranzaDetalle]);

        $resultado = array();
        $resultado['estado'] = true;
        $resultado['mensaje'] = "Pago eliminado correctamente -> ".$idCobranzaDetalle;
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado = array();
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error eliminando idCobranzaDetalle->".$idCobranzaDetalle;
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
    }
    return $resultado;
}

    public function obtenerNovedadesLote($idCobranza, $idLugarPago){
    try {
        $db = Database::getConnection();
        if ($idLugarPago == 30) {
            $sql="SELECT cn.Id, cn.IdColegiado, c.Matricula, p.Apellido, p.Nombres, cn.Detalle,
                (SELECT COUNT(a.Id) FROM enviodebitodetallecuota a INNER JOIN enviodebitodetalle b ON b.Id = a.IdEnvioDebitoDetalle WHERE b.IdDebitoTarjeta = dc.Id AND b.IdEnvioDebito = (SELECT MAX(Id) FROM enviodebito a1 WHERE a1.Tipo = 'H' AND a1.Borrado = 0)) AS CantidadCuotas,
                (SELECT b.Id FROM enviodebitodetalle b WHERE b.IdDebitoTarjeta = dc.Id AND b.IdEnvioDebito = (SELECT MAX(Id) FROM enviodebito a1 WHERE a1.Tipo = 'H' AND a1.Borrado = 0)) AS IdEnvioDebitoDetalle
                FROM cobranzanovedades cn
                INNER JOIN colegiado c ON c.Id = cn.IdColegiado
                INNER JOIN persona p ON p.Id = c.IdPersona
                INNER JOIN debitocbu dc ON dc.IdColegiado = c.Id AND dc.Estado = 'A'
                WHERE cn.IdCobranza = ?";
        } else {
            $sql="SELECT cn.Id, cn.IdColegiado, c.Matricula, p.Apellido, p.Nombres, cn.Detalle, (SELECT COUNT(a.Id) FROM enviodebitodetallecuota a INNER JOIN enviodebitodetalle b ON b.Id = a.IdEnvioDebitoDetalle WHERE b.IdDebitoTarjeta = dt.Id AND b.IdEnvioDebito = (SELECT MAX(Id) FROM enviodebito a1 WHERE a1.Tipo = SUBSTR(cob.Archivo, 8, 1) AND a1.Borrado = 0)) AS CantidadCuotas,
                (SELECT b.Id FROM enviodebitodetalle b WHERE b.IdDebitoTarjeta = dt.Id AND b.IdEnvioDebito = (SELECT MAX(Id) FROM enviodebito a1 WHERE a1.Tipo = SUBSTR(cob.Archivo, 8, 1) AND a1.Borrado = 0)) AS IdEnvioDebitoDetalle
                FROM cobranzanovedades cn
                INNER JOIN cobranza cob ON cob.Id = cn.IdCobranza
                INNER JOIN colegiado c ON c.Id = cn.IdColegiado
                INNER JOIN persona p ON p.Id = c.IdPersona
                INNER JOIN debitotarjeta dt ON dt.IdColegiado = c.Id AND dt.Estado = 'A'
                WHERE cn.IdCobranza = ?";
        }
        $stmt = $db->prepare($sql);
        $stmt->execute([$idCobranza]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $resultado = array();
        $datos = array();
        foreach ($rows as $r) {
            $row = array(
                'idCobranzaNovedades' => $r['Id'],
                'idColegiado' => $r['IdColegiado'],
                'matricula' => $r['Matricula'],
                'apellido' => $r['Apellido'],
                'nombre' => $r['Nombres'],
                'detalle' => $r['Detalle'],
                'cantidadCuotas' => $r['CantidadCuotas'],
                'idEnvioDebitoDetalle' => $r['IdEnvioDebitoDetalle']
            );
            array_push($datos, $row);
        }
        $resultado['estado'] = TRUE;
        $resultado['mensaje'] = "OK";
        $resultado['datos'] = $datos;
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado = array();
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "Error buscando novedades del lote ".$idCobranza;
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
    }
    return $resultado;
}

    public function agregarLoteCobranza($idLugarPago, $fechaApertura, $archivo) {
    try {
        $db = Database::getConnection();
        $sql="INSERT INTO cobranza (IdLugarPago, FechaApertura, Estado, Archivo, FechaProceso, IdUsuarioProceso, TipoLote)
                VALUES (?, ?, 'A', ?, DATE(NOW()), ?, 'E')";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idLugarPago, $fechaApertura, $archivo, $_SESSION['user_id']]);

        $resultado = array();
        $resultado['idCobranza'] = $db->lastInsertId();
        $resultado['estado'] = true;
        $resultado['mensaje'] = "OK";
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado = array();
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error cargando PDF";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
    }
    return $resultado;
}

    public function modificarLoteCobranza($idCobranza, $comprobantesRendido, $importeRendido, $cantidadComprobantes, $totalRecaudacion, $observacion) {
    try {
        $db = Database::getConnection();
        $diferenciaComprobantes = $comprobantesRendido - $cantidadComprobantes;
        $diferenciaImporte = $importeRendido - $totalRecaudacion;
        $sql="UPDATE cobranza
                SET CantidadComprobantes = ?,
                    TotalRecaudacion = ?,
                    DiferenciaImporte = ?,
                    DiferenciaComprobantes = ?,
                    Estado = 'C',
                    Observacion = ?
                WHERE Id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$comprobantesRendido, $importeRendido, $diferenciaImporte, $diferenciaComprobantes, $observacion, $idCobranza]);

        $resultado = array();
        $resultado['estado'] = true;
        $resultado['mensaje'] = "OK";
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado = array();
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error cargando cobranza";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
    }
    return $resultado;
}

    public function procesarPagoDebitoTarjeta($idCobranza, $fechaPago, $importeParcial, $comprobante, $tipoTarjeta, $tipoComprobante) {
    $resultado = array();
    switch ($tipoComprobante) {
        case '1':
            // pago de plan de pagos
            try {
                $db = Database::getConnection();
                $sql = "SELECT Id, IdPlanPagos, Cuota
                    FROM planpagoscuotas
                    WHERE IdPlanPagos = ? AND FechaPago IS NULL
                    ORDER BY Cuota
                    LIMIT 1";
                $stmt = $db->prepare($sql);
                $stmt->execute([$comprobante]);
                $r = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($r) {
                    $comprobante = $r['Id'];
                    $periodo = $r['IdPlanPagos'];
                    $cuota = $r['Cuota'];
                    $idAsistente = NULL;
                    $resultado = $this->cargaPago($importeParcial, $tipoComprobante, $comprobante, $periodo, $fechaPago, $idCobranza, $idAsistente, $cuota);
                } else {
                    $resultado['estado'] = FALSE;
                    $resultado['aplicado'] = FALSE;
                    $resultado['mensaje'] = "ERROR AL BUSCAR CUOTA DEL PLAN DE PAGOS";
                }
            } catch (PDOException $e) {
                $resultado['estado'] = FALSE;
                $resultado['aplicado'] = FALSE;
                $resultado['mensaje'] = "ERROR AL BUSCAR CUOTA DEL PLAN DE PAGOS";
            }
            break;

        case '2':
            // pago de cuota de colegiacion
            $periodo = NULL;
            $cuota = NULL;
            $idAsistente = NULL;
            $resultado = $this->cargaPago($importeParcial, $tipoComprobante, $comprobante, $periodo, $fechaPago, $idCobranza, $idAsistente, $cuota);
            break;

        case '8':
            $comprobante = substr($comprobante, 2, 6);
            $periodo = $_SESSION['periodoActual'];
            $cuota = 0;
            $idAsistente = NULL;
            $resCarga = $this->cargaPago($importeParcial, $tipoComprobante, $comprobante, $periodo, $fechaPago, $idCobranza, $idAsistente, $cuota);

         default:
            // error en el tipo
            $tipoPago = 3; //cuota de colegiacion
            $periodo = 0;
            $cuota = 0;
            $recargo = 0;
            $codigoPago = 1; //periodo actual
            $idAsistente = NULL;
            $resultado = $this->agregarPagoLote($idCobranza, $idColegiado, $periodo, $cuota, $fechaPago, $importeParcial, $comprobante, $tipoPago, $recargo, $codigoPago, $idAsistente);
            $resultado['aplicado'] = FALSE;
            break;
    }
    return $resultado;
}

    public function cargarNovedades($idCobranza, $idColegiado, $numeroDocumento, $observaciones) {
    try {
        $db = Database::getConnection();
        $resultado = array();
        if (!isset($idColegiado)) {
            //no viene el idColegiado, debo buscarlo por numeroDocumento
            $sql = "SELECT c.Id
                    FROM colegiado c
                    INNER JOIN persona p ON p.Id = c.IdPersona
                    where p.NumeroDocumento = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$numeroDocumento]);
            $r = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($r) {
                $idColegiado = $r['Id'];
            } else {
                $idColegiado = NULL;
            }
        }
        if (isset($idColegiado)) {
            $sql = "INSERT INTO cobranzanovedades (IdCobranza, IdColegiado, Detalle)
                VALUES (?, ?, ?)";
            $stmt = $db->prepare($sql);
            $stmt->execute([$idCobranza, $idColegiado, $observaciones]);
            $resultado['estado'] = TRUE;
            $resultado['mensaje'] = "OK";
        } else {
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "Error al cargar observaciones";
        }
    } catch (PDOException $e) {
        $resultado = array();
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "Error al cargar observaciones";
    }
    return $resultado;
}

    public function procesarPagoHomeBanking($idCobranza, $idLinkPagos, $matricula_idAsistente, $fechaPago, $importeParcial, $comprobante, $concepto) {
    $resultado = array();
    try {
        $db = Database::getConnection();

        //obtenemos el registro en linkpagos
        if (isset($idLinkPagos)) {
            $idLinkPagos = intval($idLinkPagos);

            $sql="SELECT lp.Concepto, lp.ImportePrimerVto, lp.ImporteSegundoVto
                FROM linkpagos lp
                WHERE lp.id = ? AND lp.Matricula = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$idLinkPagos, $matricula_idAsistente]);
            $r = $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            $sql="SELECT lp.id, lp.ImportePrimerVto, lp.ImporteSegundoVto
                FROM linkpagos lp
                WHERE lp.Concepto = ? AND lp.Matricula = ?
                ORDER BY lp.id DESC
                LIMIT 1";
            $stmt = $db->prepare($sql);
            $stmt->execute([$concepto, $matricula_idAsistente]);
            $r = $stmt->fetch(PDO::FETCH_ASSOC);
        }

        if ($r) {
            if (isset($idLinkPagos)) {
                $concepto = $r['Concepto'];
                $importePrimerVto = $r['ImportePrimerVto'];
                $importeSegundoVto = $r['ImporteSegundoVto'];
            } else {
                $idLinkPagos = $r['id'];
                $importePrimerVto = $r['ImportePrimerVto'];
                $importeSegundoVto = $r['ImporteSegundoVto'];
            }

            //por error en linkpagos busco el concepto del maximo idlinkpagos por matricula, sino no encuentre el registro
            if (!isset($concepto) || $concepto == "") {
                $sql = "SELECT  lp.Concepto, lp.Id, lp.ImportePrimerVto, lp.ImporteSegundoVto
                        FROM linkpagos lp
                        WHERE lp.Matricula = ?
                        ORDER BY lp.Id DESC
                        LIMIT 1";
                $stmt = $db->prepare($sql);
                $stmt->execute([$matricula_idAsistente]);
                $r2 = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($r2) {
                    $concepto = $r2['Concepto'];
                    $idLinkPagos = $r2['Id'];
                    $importePrimerVto = $r2['ImportePrimerVto'];
                    $importeSegundoVto = $r2['ImporteSegundoVto'];
                }
            }

            //procesar los registros segun el concepto
            switch ($concepto) {
                case '001':
                    //deuda cuotas
                    $sqlDeuda = "SELECT lpd.IdDeuda, lpd.ImportePrimerVto, lpd.ImporteSegundoVto, cda.Periodo, cdac.Cuota, cdac.Importe, cdac.Recargo
                        FROM linkpagosdetalle lpd
                        LEFT JOIN colegiadodeudaanualcuotas cdac ON cdac.Id = lpd.IdDeuda
                        LEFT JOIN colegiadodeudaanual cda ON cda.Id = cdac.IdColegiadoDeudaAnual
                        WHERE lpd.IdLinkPagos = ?";
                    $stmtDeuda = $db->prepare($sqlDeuda);
                    $stmtDeuda->execute([$idLinkPagos]);
                    $rowsDeuda = $stmtDeuda->fetchAll(PDO::FETCH_ASSOC);

                    if ($rowsDeuda !== false) {
                        foreach ($rowsDeuda as $rd) {
                            $comprobante = $rd['IdDeuda'];
                            $tipoComprobante = 2; //Cuota de colegiacion para cargaPago
                            $resCarga = $this->cargaPago($rd['ImportePrimerVto'], $tipoComprobante, $comprobante, $rd['Periodo'], $fechaPago, $idCobranza, NULL, $rd['Cuota']);
                        }
                    } else {
                        $idColegiado = NULL;
                        $tipoPago = 3;
                        $periodo = 0;
                        $cuota = 0;
                        $recargo = 0;
                        $codigoPago = 3; //periodos anteriores
                        $idAsistente = NULL;
                        $resultado = $this->agregarPagoLote($idCobranza, $idColegiado, $periodo, $cuota, $fechaPago, $importeParcial, $comprobante, $tipoPago, $recargo, $codigoPago, $idAsistente);
                        $resultado['aplicado'] = FALSE;
                    }
                    break;

                case '002': //Deuda PlanPagos
                case '003': //Cuota PlanPagos
                    //obtener los registros incluidos
                    $sqlDeuda = "SELECT lpd.IdDeuda, lpd.ImportePrimerVto, ppc.IdPlanPagos, ppc.Cuota, ppc.Cuota, ppc.Importe
                        FROM linkpagosdetalle lpd
                        INNER JOIN planpagoscuotas ppc ON ppc.Id = lpd.IdDeuda
                        WHERE lpd.IdLinkPagos = ?";
                    $stmtDeuda = $db->prepare($sqlDeuda);
                    $stmtDeuda->execute([$idLinkPagos]);
                    $rowsDeuda = $stmtDeuda->fetchAll(PDO::FETCH_ASSOC);

                    if ($rowsDeuda !== false) {
                        foreach ($rowsDeuda as $rd) {
                            $comprobante = $rd['IdDeuda'];
                            $tipoComprobante = 1; //Cuota de plan de pagos para cargaPago
                            $resCarga = $this->cargaPago($importeParcial, $tipoComprobante, $comprobante, $rd['IdPlanPagos'], $fechaPago, $idCobranza, NULL, $rd['Cuota']);
                        }
                    } else {
                        $idColegiado = NULL;
                        $tipoPago = 2; //plan de pagos
                        $periodo = 0;
                        $cuota = 0;
                        $recargo = 0;
                        $codigoPago = 2; //plan de pagos
                        $idAsistente = NULL;
                        $resultado = $this->agregarPagoLote($idCobranza, $idColegiado, $periodo, $cuota, $fechaPago, $importeParcial, $comprobante, $tipoPago, $recargo, $codigoPago, $idAsistente);
                        $resultado['aplicado'] = FALSE;
                    }
                    break;

                case ($concepto >= '004' && $concepto <= '013'):
                case ($concepto >= '015' && $concepto <= '016'):
                    //obtener los registros incluidos
                    $sqlDeuda = "SELECT lpd.IdDeuda, lpd.ImportePrimerVto, cda.Periodo, cdac.Cuota, cdac.Importe
                        FROM linkpagosdetalle lpd
                        INNER JOIN colegiadodeudaanualcuotas cdac ON cdac.Id = lpd.IdDeuda
                        INNER JOIN colegiadodeudaanual cda ON cda.Id = cdac.IdColegiadoDeudaAnual
                        WHERE lpd.IdLinkPagos = ?";
                    $stmtDeuda = $db->prepare($sqlDeuda);
                    $stmtDeuda->execute([$idLinkPagos]);
                    $rowsDeuda = $stmtDeuda->fetchAll(PDO::FETCH_ASSOC);

                    if ($rowsDeuda !== false) {
                        foreach ($rowsDeuda as $rd) {
                            $comprobante = $rd['IdDeuda'];
                            $tipoComprobante = 2; //Cuota de colegiacion para cargaPago
                            $resCarga = $this->cargaPago($importeParcial, $tipoComprobante, $comprobante, $rd['Periodo'], $fechaPago, $idCobranza, NULL, $rd['Cuota']);
                        }
                    } else {
                        $colegiadoLogic = new colegiadoLogic();
                        $resColegiado = $colegiadoLogic->obtenerIdColegiado($matricula_idAsistente);
                        if ($resColegiado['estado']) {
                            $idColegiado = $resColegiado['idColegiado'];
                        } else {
                            $idColegiado = NULL;
                        }
                        $tipoPago = 3; //cuota de colegiacion
                        $periodo = 0;
                        $cuota = 0;
                        $recargo = 0;
                        $codigoPago = 1; //periodo actual
                        $idAsistente = NULL;
                        $resultado = $this->agregarPagoLote($idCobranza, $idColegiado, $periodo, $cuota, $fechaPago, $importeParcial, $comprobante, $tipoPago, $recargo, $codigoPago, $idAsistente);
                        $resultado['aplicado'] = FALSE;
                    }
                    break;

                case '014':
                    //obtener los registros incluidos
                    $sqlDeuda = "SELECT lpd.IdDeuda, lpd.ImportePrimerVto, cda.Periodo
                        FROM linkpagosdetalle lpd
                        INNER JOIN colegiadodeudaanualtotal cdat ON cdat.Id = lpd.IdDeuda
                        INNER JOIN colegiadodeudaanual cda ON cda.Id = cdat.IdColegiadoDeudaAnual
                        WHERE lpd.IdLinkPagos = ?";
                    $stmtDeuda = $db->prepare($sqlDeuda);
                    $stmtDeuda->execute([$idLinkPagos]);
                    $rowsDeuda = $stmtDeuda->fetchAll(PDO::FETCH_ASSOC);

                    if ($rowsDeuda !== false) {
                        foreach ($rowsDeuda as $rd) {
                            $comprobante = $rd['IdDeuda'];
                            $tipoComprobante = 8; //Pago Total para cargaPago
                            $cuota = 0; //para Pago Total va cero
                            $resCarga = $this->cargaPago($importeParcial, $tipoComprobante, $comprobante, $rd['Periodo'], $fechaPago, $idCobranza, NULL, $cuota);
                        }
                    } else {
                        $resColegiado = $colegiadoLogic->obtenerIdColegiado($matricula);
                        if ($resColegiado['estado']) {
                            $idColegiado = $resColegiado['idColegiado'];
                        } else {
                            $idColegiado = NULL;
                        }
                        $tipoPago = 3; //cuota de colegiacion
                        $periodo = 0;
                        $cuota = 0;
                        $recargo = 0;
                        $codigoPago = 1; //periodo actual
                        $idAsistente = NULL;
                        $resultado = $this->agregarPagoLote($idCobranza, $idColegiado, $periodo, $cuota, $fechaPago, $importeParcial, $comprobante, $tipoPago, $recargo, $codigoPago, $idAsistente);
                        $resultado['aplicado'] = FALSE;
                    }
                    break;

                case ($concepto >= '200' && $concepto <= '299'): //Cuota Curso
                    //obtener los registros incluidos
                    $idAsistente = $matricula_idAsistente;
                    $sqlDeuda = "SELECT lpd.IdDeuda, lpd.ImportePrimerVto, cac.Cuota, cac.Importe
                        FROM linkpagosdetalle lpd
                        INNER JOIN cursosasistentecuotas cac ON cac.Id = lpd.IdDeuda
                        WHERE lpd.IdLinkPagos = ?";
                    $stmtDeuda = $db->prepare($sqlDeuda);
                    $stmtDeuda->execute([$idLinkPagos]);
                    $rowsDeuda = $stmtDeuda->fetchAll(PDO::FETCH_ASSOC);

                    if ($rowsDeuda !== false) {
                        foreach ($rowsDeuda as $rd) {
                            $comprobante = $rd['IdDeuda'];
                            $periodo = 0;
                            $tipoComprobante = 6; //cuota de curso
                            $resCarga = $this->cargaPago($importeParcial, $tipoComprobante, $comprobante, 0, $fechaPago, $idCobranza, $idAsistente, $rd['Cuota']);
                        }
                    } else {
                        $idColegiado = NULL;
                        $tipoPago = 7; //cursos
                        $periodo = 0;
                        $cuota = 0;
                        $recargo = 0;
                        $codigoPago = 10; //cursos
                        $resultado = $this->agregarPagoLote($idCobranza, $idColegiado, $periodo, $cuota, $fechaPago, $importeParcial, $comprobante, $tipoPago, $recargo, $codigoPago, $idAsistente);
                        $resultado['aplicado'] = FALSE;
                    }
                    break;

                default:
                    //el concepto es erroneo, cargo el pago solo en el lote sin datos
                    $idColegiado = NULL;
                    $tipoPago = 0; //error de concepto
                    $periodo = 0;
                    $cuota = 0;
                    $recargo = 0;
                    $codigoPago = 0; //error de concepto
                    $idAsistente = NULL;
                    $resultado = $this->agregarPagoLote($idCobranza, $idColegiado, $periodo, $cuota, $fechaPago, $importeParcial, $comprobante, $tipoPago, $recargo, $codigoPago, $idAsistente);
                    $resultado['aplicado'] = FALSE;
                    $resultado['mensaje'] .= ' - Codigo erroneo -> '.$concepto;
            }
            $resultado['estado'] = TRUE;
            $resultado['mensaje'] = "OK";
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        } else {
            echo 'no encontrado'.'<br>';
            $resultado['estado'] = false;
            $resultado['mensaje'] = "Error buscando linkpagos";
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando linkpagos";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
    }
    return $resultado;
}

    public function procesarPagoCBU($idCobranza, $fechaPago, $importeParcial, $idEnvioDebitoDetalle) {
    $resultado = array();
    try {
        $db = Database::getConnection();
        $sql="SELECT 'C' AS TipoCuota, cdac.Id, cda.Periodo, cdac.Cuota, eddc.Importe, cda.IdColegiado, cdac.Importe, cdac.Estado
            FROM enviodebitodetallecuota eddc
            INNER JOIN enviodebitodetalle edd ON edd.Id = eddc.IdEnvioDebitoDetalle
            INNER JOIN colegiadodeudaanualcuotas cdac ON cdac.Id = eddc.IdRelacion
            INNER JOIN colegiadodeudaanual cda ON cda.Id = cdac.IdColegiadoDeudaAnual
            WHERE edd.Id = ? AND eddc.TipoCuota = 'C'

            UNION ALL

            SELECT 'P' AS TipoCuota, ppc.Id, ppc.IdPlanPagos, ppc.Cuota, eddc.Importe, pp.IdColegiado, ppc.Importe, ppc.IdTipoEstadoCuota
            FROM enviodebitodetallecuota eddc
            INNER JOIN enviodebitodetalle edd ON edd.Id = eddc.IdEnvioDebitoDetalle
            INNER JOIN planpagoscuotas ppc ON ppc.Id = eddc.IdRelacion
            INNER JOIN planpagos pp ON pp.Id = ppc.IdPlanPagos
            WHERE edd.Id = ? AND eddc.TipoCuota = 'P'";
        $stmtDeuda = $db->prepare($sql);
        $stmtDeuda->execute([$idEnvioDebitoDetalle, $idEnvioDebitoDetalle]);
        $rowsDeuda = $stmtDeuda->fetchAll(PDO::FETCH_ASSOC);

        $aplicado = TRUE;
        foreach ($rowsDeuda as $rd) {
            $tipoCuota = $rd['TipoCuota'];
            $idReferencia = $rd['Id'];
            $periodo_planpago = $rd['Periodo'];
            $cuota = $rd['Cuota'];
            $importeParcialRow = $rd['Importe'];
            if (isset($tipoCuota)) {
                if ($tipoCuota == 'C') {
                    $comprobante = $idReferencia;
                    $tipoComprobante = 2;
                    $resCarga = $this->cargaPago($importeParcialRow, $tipoComprobante, $comprobante, $periodo_planpago, $fechaPago, $idCobranza, NULL, $cuota);
                } else {
                    if ($tipoCuota == 'P') {
                        $comprobante = $idReferencia;
                        $tipoComprobante = 1;
                        $resCarga = $this->cargaPago($importeParcialRow, $tipoComprobante, $comprobante, $periodo_planpago, $fechaPago, $idCobranza, NULL, $cuota);
                    } else {
                        $aplicado = FALSE;
                    }
                }
            } else {
                $aplicado = FALSE;
            }
        }
        $resultado['estado'] = TRUE;
        $resultado['mensaje'] = "OK";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
    } catch (PDOException $e) {
        $idColegiado = NULL;
        $tipoPago = 3;
        $periodo = 0;
        $cuota = 0;
        $recargo = 0;
        $codigoPago = 3; //periodos anteriores
        $idAsistente = NULL;
        $resultado = $this->agregarPagoLote($idCobranza, $idColegiado, $periodo, $cuota, $fechaPago, $importeParcial, $comprobante, $tipoPago, $recargo, $codigoPago, $idAsistente);
        $resultado['aplicado'] = FALSE;
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando linkpagos";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
    }
    return $resultado;
}

    public function procesarPagoDebitoAutomatico($idCobranza, $fechaPago, $importeParcial, $idEnvioDebitoDetalle) {
    $resultado = array();
    try {
        $db = Database::getConnection();
        $sql="SELECT 'C' AS TipoCuota, cdac.Id, cda.Periodo, cdac.Cuota, eddc.Importe, cda.IdColegiado, cdac.Importe, cdac.Estado
            FROM enviodebitodetallecuota eddc
            INNER JOIN enviodebitodetalle edd ON edd.Id = eddc.IdEnvioDebitoDetalle
            INNER JOIN colegiadodeudaanualcuotas cdac ON cdac.Id = eddc.IdRelacion
            INNER JOIN colegiadodeudaanual cda ON cda.Id = cdac.IdColegiadoDeudaAnual
            WHERE edd.Id = ? AND eddc.TipoCuota = 'C'

            UNION ALL

            SELECT 'P' AS TipoCuota, ppc.Id, ppc.IdPlanPagos, ppc.Cuota, eddc.Importe, pp.IdColegiado, ppc.Importe, ppc.IdTipoEstadoCuota
            FROM enviodebitodetallecuota eddc
            INNER JOIN enviodebitodetalle edd ON edd.Id = eddc.IdEnvioDebitoDetalle
            INNER JOIN planpagoscuotas ppc ON ppc.Id = eddc.IdRelacion
            INNER JOIN planpagos pp ON pp.Id = ppc.IdPlanPagos
            WHERE edd.Id = ? AND eddc.TipoCuota = 'P'";
        $stmtDeuda = $db->prepare($sql);
        $stmtDeuda->execute([$idEnvioDebitoDetalle, $idEnvioDebitoDetalle]);
        $rowsDeuda = $stmtDeuda->fetchAll(PDO::FETCH_ASSOC);

        $aplicado = TRUE;
        foreach ($rowsDeuda as $rd) {
            $tipoCuota = $rd['TipoCuota'];
            $idReferencia = $rd['Id'];
            $periodo_planpago = $rd['Periodo'];
            $cuota = $rd['Cuota'];
            $importeParcialRow = $rd['Importe'];
            if (isset($tipoCuota)) {
                if ($tipoCuota == 'C') {
                    $comprobante = $idReferencia;
                    $tipoComprobante = 2;
                    $resCarga = $this->cargaPago($importeParcialRow, $tipoComprobante, $comprobante, $periodo_planpago, $fechaPago, $idCobranza, NULL, $cuota);
                } else {
                    if ($tipoCuota == 'P') {
                        $comprobante = $idReferencia;
                        $tipoComprobante = 1;
                        $resCarga = $this->cargaPago($importeParcialRow, $tipoComprobante, $comprobante, $periodo_planpago, $fechaPago, $idCobranza, NULL, $cuota);
                    } else {
                        $aplicado = FALSE;
                    }
                }
            } else {
                $aplicado = FALSE;
            }
        }
        $resultado['estado'] = TRUE;
        $resultado['mensaje'] = "OK";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
    } catch (PDOException $e) {
        $idColegiado = NULL;
        $tipoPago = 3;
        $periodo = 0;
        $cuota = 0;
        $recargo = 0;
        $codigoPago = 3; //periodos anteriores
        $idAsistente = NULL;
        $resultado = $this->agregarPagoLote($idCobranza, $idColegiado, $periodo, $cuota, $fechaPago, $importeParcial, $comprobante, $tipoPago, $recargo, $codigoPago, $idAsistente);
        $resultado['aplicado'] = FALSE;
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando linkpagos";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
    }
    return $resultado;
}

    public function cargaPago($importeParcial, $tipoComprobante, $comprobante, $periodo, $fechaPago, $idCobranza, $idAsistente, $cuota) {
    $resultado = array();
    try {
        $db = Database::getConnection();
        switch ($tipoComprobante) {
            case '1':
                //es cuota de plan de pagos
                $sql = "SELECT pp.IdColegiado, pp.Id, ppc.Cuota, ppc.Importe, ppc.IdTipoEstadoCuota
                    FROM planpagoscuotas ppc
                    INNER JOIN planpagos pp ON pp.Id = ppc.IdPlanPagos
                    WHERE ppc.id = ?";
                $stmt = $db->prepare($sql);
                $stmt->execute([$comprobante]);
                $r = $stmt->fetch(PDO::FETCH_ASSOC);

                $aplicado = FALSE;
                if ($r) {
                    $idColegiado = $r['IdColegiado'];
                    $periodo = $r['Id'];
                    $cuota = $r['Cuota'];
                    $importeOriginal = $r['Importe'];
                    $estado = $r['IdTipoEstadoCuota'];
                    if ($estado == 1) {
                        //si el estado del comprobante esta en 1, entonces aplicamos el pago
                        $resAplica = $this->aplicarPagoPlanPago($comprobante, $fechaPago);
                        if ($resAplica['estado']) {
                            $aplicado = TRUE;
                        }
                    }
                }

                if (!$aplicado) {
                    //busco la primer cuota impaga y se la aplico, sino genero como pago doble
                    $sql = "SELECT ppc.Cuota, ppc.Importe, ppc.Id
                        FROM planpagoscuotas ppc
                        INNER JOIN planpagos pp ON pp.Id = ppc.IdPlanPagos
                        WHERE pp.IdColegiado = ?
                            AND pp.Id = ?
                            AND ppc.IdTipoEstadoCuota IN(1, 5)
                        ORDER BY ppc.Estado, ppc.Id
                        LIMIT 1";
                    $stmt = $db->prepare($sql);
                    $stmt->execute([$idColegiado, $periodo]);
                    $r2 = $stmt->fetch(PDO::FETCH_ASSOC);

                    if ($r2) {
                        $cuota = $r2['Cuota'];
                        $importeOriginal = $r2['Importe'];
                        $idPlanPagoCuota = $r2['Id'];
                        if ($idPlanPagoCuota > 0) {
                            $resAplica = $this->aplicarPagoPlanPago($idPlanPagoCuota, $fechaPago);
                            if ($resAplica['estado']) {
                                $aplicado = TRUE;
                            }
                        }
                    }
                }

                if (!$aplicado) {
                    if (isset($cuota) && $cuota > 0) {
                        $cuota += 20;
                    } else {
                        $cuota = 20;
                    }
                }

                $tipoPago = 2; //plan de pagos
                $codigoPago = 2; //plan de pagos
                $recargo = 0;
                if ($aplicado && $importeParcial > $importeOriginal) {
                    $recargo = $importeParcial - $importeOriginal;
                }
                $idAsistente = NULL;
                $resultado = $this->agregarPagoLote($idCobranza, $idColegiado, $periodo, $cuota, $fechaPago, $importeParcial, $comprobante, $tipoPago, $recargo, $codigoPago, $idAsistente);
                $resultado['aplicado'] = $aplicado;
                break;

            case '2':
            case '0':
                //es cuota de colegiacion en base de datos
                $sql = "SELECT cda.IdColegiado, cda.Periodo, cdac.Cuota, cdac.Importe, cdac.Estado
                    FROM colegiadodeudaanualcuotas cdac
                    INNER JOIN colegiadodeudaanual cda ON cda.Id = cdac.IdColegiadoDeudaAnual
                    WHERE cdac.id = ?";
                $stmt = $db->prepare($sql);
                $stmt->execute([$comprobante]);
                $r = $stmt->fetch(PDO::FETCH_ASSOC);

                $aplicado = FALSE;
                if ($r) {
                    $idColegiado = $r['IdColegiado'];
                    $periodo = $r['Periodo'];
                    $cuota = $r['Cuota'];
                    $importeOriginal = $r['Importe'];
                    $estado = $r['Estado'];
                    if ($estado == 1) {
                        $resAplica = $this->aplicarPagoDeudaAnual($comprobante, $fechaPago);
                        if ($resAplica['estado']) {
                            $aplicado = TRUE;
                        }
                    } else {
                        $cuotaOriginal = $cuota;
                        $importeOriginalOriginal = $importeOriginal;
                    }
                }

                if (!$aplicado) {
                    $cuotaOriginal = $cuota;
                    $sql = "SELECT cdac.Cuota, cdac.Importe, cdac.Id
                        FROM colegiadodeudaanualcuotas cdac
                        INNER JOIN colegiadodeudaanual cda ON cda.Id = cdac.IdColegiadoDeudaAnual
                        WHERE cda.IdColegiado = ?
                            AND cda.Periodo = ?
                            AND cdac.Estado = 1
                        ORDER BY cdac.Estado, cdac.Id
                        LIMIT 1";
                    $stmt = $db->prepare($sql);
                    $stmt->execute([$idColegiado, $periodo]);
                    $r2 = $stmt->fetch(PDO::FETCH_ASSOC);

                    if ($r2) {
                        $cuota = $r2['Cuota'];
                        $importeOriginal = $r2['Importe'];
                        $idColegiadoDeudaAnualCuota = $r2['Id'];
                        if ($idColegiadoDeudaAnualCuota > 0) {
                            $resAplica = $this->aplicarPagoDeudaAnual($idColegiadoDeudaAnualCuota, $fechaPago);
                            if ($resAplica['estado']) {
                                $aplicado = TRUE;
                            }
                        }
                    }
                }

                if (!$aplicado) {
                    if (isset($cuotaOriginal) && $cuotaOriginal > 0) {
                        $cuota = $cuotaOriginal + 10;
                    } else {
                        $cuota = 20;
                    }
                }

                if ($importeParcial > $importeOriginal) {
                    if (isset($importeOriginal) && $importeOriginal > 0) {
                        $recargo = $importeParcial - $importeOriginal;
                    } else {
                        $recargo = $importeParcial - $importeOriginalOriginal;
                    }
                } else {
                    $recargo = 0;
                }
                if ($periodo == $_SESSION['periodoActual']) {
                    $codigoPago = 1; //periodo actual
                } else {
                    $codigoPago = 3; //periodos anteriores
                }
                $tipoPago = 3;
                $idAsistente = NULL;

                $resultado = $this->agregarPagoLote($idCobranza, $idColegiado, $periodo, $cuota, $fechaPago, $importeParcial, $comprobante, $tipoPago, $recargo, $codigoPago, $idAsistente);
                $resultado['aplicado'] = $aplicado;
                break;

            case 4: //pago por notificacion de deuda
                $resAplica = $this->aplicarPagoNotaDeuda($comprobante, $fechaPago);
                if ($resAplica['estado']) {
                    $recargo = 0;
                    $idColegiado = NULL;
                    $resCuotaPura = obtenerValorCuotaPuraNotificacionDeuda($comprobante);
                    if ($resCuotaPura['estado']) {
                        $cuotaPura = $resCuotaPura['datos'];
                        $importeCuotaPura = $cuotaPura['importe'];
                        $idColegiado = $cuotaPura['idColegiado'];
                        if ($importeCuotaPura < $importeParcial) {
                            $recargo = $importeParcial - $importeCuotaPura;
                        }
                    }
                    $codigoPago = 3; //periodos anteriores
                    $tipoPago = 4;
                    $idAsistente = NULL;
                    $periodo = 0;
                    $cuota = 0;
                    $resultado = $this->agregarPagoLote($idCobranza, $idColegiado, $periodo, $cuota, $fechaPago, $importeParcial, $comprobante, $tipoPago, $recargo, $codigoPago, $idAsistente);
                    $resultado['aplicado'] = TRUE;
                } else {
                    $resultado['aplicado'] = FALSE;
                }
                break;

            case '6':
                //es cuota de curso
                $sql = "SELECT cac.Cuota, cac.Importe, cac.FechaPago
                    FROM cursosasistentecuotas cac
                    WHERE cac.Id = ?";
                $stmt = $db->prepare($sql);
                $stmt->execute([$comprobante]);
                $r = $stmt->fetch(PDO::FETCH_ASSOC);

                $aplicado = FALSE;
                if ($r) {
                    $cuota = $r['Cuota'];
                    $importeOriginal = $r['Importe'];
                    $fechaPagoCuota = $r['FechaPago'];
                    if (!isset($fechaPagoCuota) || $fechaPagoCuota == '0000-00-00') {
                        $resAplica = $this->aplicarPagoCurso($comprobante, $fechaPago);
                        if ($resAplica['estado']) {
                            $aplicado = TRUE;
                        }
                    }
                }

                if (!$aplicado) {
                    $sql = "SELECT cac.Cuota, cac.Importe, cac.Id
                        FROM cursosasistentecuotas cac
                        WHERE cac.IdCursosAsistente = ?
                            AND (cac.FechaPago IS NULL OR cac.FechaPago = '0000-00-00')
                        ORDER BY cac.Id
                        LIMIT 1";
                    $stmt = $db->prepare($sql);
                    $stmt->execute([$idAsistente]);
                    $r2 = $stmt->fetch(PDO::FETCH_ASSOC);

                    if ($r2) {
                        $cuota = $r2['Cuota'];
                        $importeOriginal = $r2['Importe'];
                        $idCursosAsistenteCuota = $r2['Id'];
                        if ($idCursosAsistenteCuota > 0) {
                            $resAplica = $this->aplicarPagoCurso($idCursosAsistenteCuota, $fechaPago);
                            if ($resAplica['estado']) {
                                $aplicado = TRUE;
                            }
                        }
                    }
                }

                if (!$aplicado) {
                    if (isset($cuota) && $cuota > 0) {
                        $cuota += 10;
                    } else {
                        $cuota = 20;
                    }
                }

                $tipoPago = 7; //cursos
                $codigoPago = 10; //cursos
                $recargo = 0;
                $idColegiado = NULL;
                $resultado = $this->agregarPagoLote($idCobranza, $idColegiado, $periodo, $cuota, $fechaPago, $importeParcial, $comprobante, $tipoPago, $recargo, $codigoPago, $idAsistente);
                $resultado['aplicado'] = $aplicado;
                break;

            case 8: //recibo pago total
                $sql = "SELECT colegiadodeudaanual.IdColegiado, colegiadodeudaanual.Periodo, colegiadodeudaanualtotal.Importe, colegiadodeudaanual.Id, colegiadodeudaanualtotal.IdEstado
                    FROM colegiadodeudaanualtotal
                    INNER JOIN colegiadodeudaanual ON colegiadodeudaanual.Id = colegiadodeudaanualtotal.IdColegiadoDeudaAnual
                    WHERE colegiadodeudaanualtotal.Id = ?";

                $stmt = $db->prepare($sql);
                $stmt->execute([$comprobante]);
                $r = $stmt->fetch(PDO::FETCH_ASSOC);

                $aplicado = FALSE;
                if ($r) {
                    $idColegiado = $r['IdColegiado'];
                    $periodo = $r['Periodo'];
                    $importeOriginal = $r['Importe'];
                    $idColegiadoDeudaAnual = $r['Id'];
                    $estado = $r['IdEstado'];
                    if ($estado == 1) {
                        $sql = "UPDATE colegiadodeudaanual cda, colegiadodeudaanualcuotas cdac, colegiadodeudaanualtotal cdat
                            SET cda.Estado = 'C', cdac.FechaPago = ?, cdac.Estado = 8, cdac.FechaActualizacion = DATE(NOW()), cdat.FechaPago = ?, cdat.IdEstado = 2, cdat.FechaActualizacion = DATE(NOW())
                            WHERE cdat.Id = ?
                            AND cda.Id = cdat.IdColegiadoDeudaAnual AND cdac.IdColegiadoDeudaAnual = cda.Id AND cdac.Estado = 1";
                        $stmt2 = $db->prepare($sql);
                        $stmt2->execute([$fechaPago, $fechaPago, $comprobante]);
                        $aplicado = TRUE;
                    }
                }
                $tipoPago = 8;
                $idAsistente = NULL;
                $recargo = 0;
                $codigoPago = 1; //periodo actual

                $resultado = $this->agregarPagoLote($idCobranza, $idColegiado, $periodo, $cuota, $fechaPago, $importeParcial, $comprobante, $tipoPago, $recargo, $codigoPago, $idAsistente);
                $resultado['aplicado'] = $aplicado;
                break;

            default:
                $resultado['estado'] = FALSE;
                $resultado['mensaje'] = "El tipo de comprobante es invalido";
                break;
        }
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "Error en cargaPago: ".$e->getMessage();
    }
    return $resultado;
}

    public function aplicarPagoDeudaAnual($comprobante, $fechaPago) {
    try {
        $db = Database::getConnection();
        $sql = "UPDATE colegiadodeudaanualcuotas
                SET FechaPago = ?,
                Estado = 2,
                FechaActualizacion = DATE(NOW())
                WHERE Id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$fechaPago, $comprobante]);

        $resultado = array();
        $resultado['estado'] = TRUE;
        $resultado['mensaje'] = "OK";
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado = array();
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "Error actualizando cuotas";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
    }
    return $resultado;
}

    public function aplicarPagoPlanPago($comprobante, $fechaPago) {
    try {
        $db = Database::getConnection();
        $sql = "UPDATE planpagoscuotas
                SET FechaPago = ?,
                    Estado = '3',
                    IdTipoEstadoCuota = 2,
                    FechaActualizacion = DATE(NOW())
                WHERE Id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$fechaPago, $comprobante]);

        $resultado = array();
        $resultado['estado'] = TRUE;
        $resultado['mensaje'] = "OK";
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado = array();
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "Error actualizando cuotas";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
    }
    return $resultado;
}

    public function aplicarPagoCurso($comprobante, $fechaPago) {
    try {
        $db = Database::getConnection();
        $sql = "UPDATE cursosasistentecuotas
            SET FechaPago = ?,
                Recibo = ?,
                FechaActualizacion = DATE(NOW())
            WHERE id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$fechaPago, $comprobante, $comprobante]);

        $resultado = array();
        $resultado['estado'] = TRUE;
        $resultado['mensaje'] = "OK";
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado = array();
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "Error actualizando cuotas";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
    }
    return $resultado;
}

    public function aplicarPagoNotaDeuda($comprobante, $fechaPago){
    try {
        $db = Database::getConnection();
        $sql = "UPDATE colegiadodeudaanualcuotas cdac
            INNER JOIN notificacioncolegiadodeuda ncd ON ncd.IdColegiadoDeudaAnualCuota = cdac.id
            SET cdac.FechaPago = ?,
            cdac.FechaActualizacion = DATE(NOW()),
            cdac.Estado = 2
            WHERE ncd.IdNotificacionColegiado = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$fechaPago, $comprobante]);

        $resultado = array();
        $resultado['estado'] = TRUE;
        $resultado['mensaje'] = "OK";
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado = array();
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "Error actualizando cuotas";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
    }
    return $resultado;
}

    //function agregaPagoCC($idCobranza, $periodo, $cuota, $fechaPago, $importe, $recibo, $tipoPago, $recargo, $idColegiado, $fechaCarga) {
    public function agregarPagoLote($idCobranza, $idColegiado, $periodo, $cuota, $fechaPago, $importe, $comprobante, $tipoPago, $recargo, $codigoPago, $idAsistente) {
    try {
        $db = Database::getConnection();
        $sql = "INSERT INTO cobranzadetalle (IdLoteCobranza, Periodo, Cuota, FechaPago, Importe, Recibo, TipoPago, Recargo, IdColegiado, IdAsistente, FechaCarga, CodigoPago)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, DATE(NOW()), ?)";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idCobranza, $periodo, $cuota, $fechaPago, $importe, $comprobante, $tipoPago, $recargo, $idColegiado, $idAsistente, $codigoPago]);

        $resultado = array();
        $resultado['estado'] = TRUE;
        $resultado['mensaje'] = "OK";
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado = array();
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "Error al cargar comprobante -> ".$e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
    }
    return $resultado;
}

    public function obtenerDatosRecibo($tipoPago, $recibo) {
    $resultado = array();
    $continua = TRUE;
    $tipoPagoDetalle = "";
    $codigoPago = NULL;
    switch ($tipoPago) {
        case TIPO_PAGO_CUOTA_COLEGIACION:
            // por cuota de colegiacion
            $codigoPago = 1;
            $detalleTipoPago = 3;
            $sql = "SELECT da.Periodo, dac.Cuota, dac.Importe, da.IdColegiado, c.Matricula, p.Apellido, p.Nombres, dac.Estado, (SELECT tp.Detalle FROM tipopago tp WHERE tp.Id = ?) as TipoPagoDetalle
                FROM colegiadodeudaanualcuotas dac
                INNER JOIN colegiadodeudaanual da ON da.Id = dac.IdColegiadoDeudaAnual AND da.Estado = 'A'
                INNER JOIN colegiado c ON c.Id = da.IdColegiado
                INNER JOIN persona p ON p.Id = c.IdPersona
                WHERE dac.Id = ? AND da.Periodo = ".PERIODO_ACTUAL;
            break;

        case TIPO_PAGO_CUOTA_PLAN_PAGO:
            // por cuota de plan de pagos
            $codigoPago = 2;
            $detalleTipoPago = 2;
            $sql = "SELECT pp.Id AS Periodo, ppc.Cuota, ppc.Importe, pp.IdColegiado, c.Matricula, p.Apellido, p.Nombres, ppc.IdTipoEstadoCuota, (SELECT tp.Detalle FROM tipopago tp WHERE tp.Id = ?) as TipoPagoDetalle
                FROM planpagoscuotas ppc
                INNER JOIN planpagos pp ON pp.Id = ppc.IdPlanPagos
                INNER JOIN colegiado c ON c.Id = pp.IdColegiado
                INNER JOIN persona p ON p.Id = c.IdPersona
                WHERE ppc.Id = ?";
            break;

        case TIPO_PAGO_CUOTA_PERIODO_ANTERIOR:
            // por cuota de colegiacion periodo anterior
            $codigoPago = 3;
            $detalleTipoPago = 3;
            $sql = "SELECT da.Periodo, dac.Cuota, dac.Importe, da.IdColegiado, c.Matricula, p.Apellido, p.Nombres, dac.Estado, (SELECT tp.Detalle FROM tipopago tp WHERE tp.Id = ?) as TipoPagoDetalle
                FROM colegiadodeudaanualcuotas dac
                INNER JOIN colegiadodeudaanual da ON da.Id = dac.IdColegiadoDeudaAnual AND da.Estado = 'A'
                INNER JOIN colegiado c ON c.Id = da.IdColegiado
                INNER JOIN persona p ON p.Id = c.IdPersona
                WHERE dac.Id = ? AND da.Periodo < ".PERIODO_ACTUAL;
            break;

        case TIPO_PAGO_NOTIFICACION:
            // por nota de deuda
            $codigoPago = 2;
            $detalleTipoPago = 4;
            $sql = "SELECT 0 AS Periodo, 0 as Cuota, (SELECT SUM(ncd.ValorActualizado) FROM notificacioncolegiadodeuda ncd WHERE ncd.IdNotificacionColegiado = nc.IdNotificacionColegiado) AS Importe, nc.IdColegiado, c.Matricula, p.Apellido, p.Nombres, nc.Estado, (SELECT tp.Detalle FROM tipopago tp WHERE tp.Id = ?) as TipoPagoDetalle
                FROM notificacioncolegiado nc
                INNER JOIN colegiado c ON c.Id = nc.IdColegiado
                INNER JOIN persona p ON p.Id = c.IdPersona
                WHERE nc.IdNotificacionColegiado = ?";
            break;

        case TIPO_PAGO_TOTAL:
            // por pago Total de colegiacion
            $codigoPago = 1;
            $detalleTipoPago = 8;
            $sql = "SELECT da.Periodo, 0 as Cuota, dat.Importe, da.IdColegiado, c.Matricula, p.Apellido, p.Nombres, dat.IdEstado, (SELECT tp.Detalle FROM tipopago tp WHERE tp.Id = ?) as TipoPagoDetalle
                FROM colegiadodeudaanualtotal dat
                INNER JOIN colegiadodeudaanual da ON da.Id = dat.IdColegiadoDeudaAnual AND da.Estado = 'A'
                INNER JOIN colegiado c ON c.Id = da.IdColegiado
                INNER JOIN persona p ON p.Id = c.IdPersona
                WHERE dat.Id = ? AND dat.IdEstado = 1";
            break;

        case TIPO_PAGO_CURSO:
            //cuota de curso
            $codigoPago = 10;
            $detalleTipoPago = 7;
            $sql = "SELECT NULL AS Periodo, ac.Cuota as Cuota, ac.Importe, ca.Id AS IdAsistente, col.Matricula, ca.ApellidoNombre, '' AS Nombres, if (ac.FechaPago IS NULL, 1, 2) AS IdEstado, CONCAT((SELECT tp.Detalle FROM tipopago tp WHERE tp.Id = ?), ' (', c.Titulo, ')') as TipoPagoDetalle
                FROM cursosasistentecuotas ac
                INNER JOIN cursosasistente ca ON ca.Id = ac.IdCursosAsistente AND ca.Estado = 'S'
                INNER JOIN cursos c ON c.Id = ca.IdCursos
                LEFT JOIN colegiado col ON col.Id = ca.IdColegiado
                WHERE ac.Id = ?";
            break;
        default:
            // error en el tipo de pago ingresado
            $continua = FALSE;
            break;
    }
    if ($continua) {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare($sql);
            $stmt->execute([$codigoPago, $recibo]);
            $r = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($r) {
                $periodo = $r['Periodo'];
                $cuota = $r['Cuota'];
                $importe = $r['Importe'];
                $idReferencia = isset($r['IdColegiado']) ? $r['IdColegiado'] : (isset($r['IdAsistente']) ? $r['IdAsistente'] : null);
                $matricula = $r['Matricula'];
                $apellido = $r['Apellido'];
                $nombre = $r['Nombres'];
                $estadoPago = $r['Estado'] ?? $r['IdTipoEstadoCuota'] ?? $r['IdEstado'] ?? null;
                $tipoPagoDetalle = $r['TipoPagoDetalle'];

                // Determine estadoPago from the correct column
                if (isset($r['Estado'])) {
                    $estadoPago = $r['Estado'];
                } elseif (isset($r['IdTipoEstadoCuota'])) {
                    $estadoPago = $r['IdTipoEstadoCuota'];
                } elseif (isset($r['IdEstado'])) {
                    $estadoPago = $r['IdEstado'];
                } else {
                    $estadoPago = null;
                }

                if ($estadoPago == 1 || $estadoPago == 5 || $estadoPago == 'A') {
                    //esta pendiente de pago o marcado por cancelacion, devuelvo los datos
                    if ($tipoPago == TIPO_PAGO_CURSO) {
                        $idAsistente = $idReferencia;
                        $idColegiado = NULL;
                    } else {
                        $idColegiado = $idReferencia;
                        $idAsistente = NULL;
                    }

                    $datos = array(
                        'periodo' => $periodo,
                        'cuota' => $cuota,
                        'importe' => $importe,
                        'idColegiado' => $idColegiado,
                        'idAsistente' => $idAsistente,
                        'matricula' => $matricula,
                        'apellidoNombre' => trim($apellido)." ".trim($nombre),
                        'codigoPagoDetalle' => $tipoPagoDetalle,
                        'codigoPago' => $codigoPago,
                        'tipoPagoDetalle' => obtenrTipoPago($detalleTipoPago)
                    );
                    $resultado['estado'] = TRUE;
                    $resultado['mensaje'] = "OK";
                    $resultado['datos'] = $datos;
                    $resultado['clase'] = 'alert alert-success';
                    $resultado['icono'] = 'glyphicon glyphicon-ok';
                } else {
                    switch ($estadoPago) {
                        case 2:
                        case 8:
                            // pagado
                            $mensaje = "El recibo <b>".$recibo."</b> ya se encuentra abonado.";
                            break;

                        case 4:
                            // condonada
                            $mensaje = "La cuota con recibo <b>".$recibo."</b> fue condonada.";
                            break;

                        default:
                            // no se encontro el recibo
                            $resTipoPago = obtenerTipoValorPorId($tipoPago);
                            if ($resTipoPago['estado']) {
                                $tipoPagoDetalle = $resTipoPago['datos']['nombre'];
                            } else {
                                $tipoPagoDetalle = "";
                            }
                            $mensaje = "El recibo <b>".$recibo."</b> no se encontró para tipo de pago ".$tipoPagoDetalle.".";
                            break;
                    }
                    $resultado['estado'] = FALSE;
                    $resultado['mensaje'] = $mensaje;
                    $resultado['clase'] = 'alert alert-warning';
                    $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
                }
            } else {
                $resultado['estado'] = FALSE;
                $resultado['mensaje'] = "Error buscando recibo -> ".$recibo;
                $resultado['clase'] = 'alert alert-danger';
                $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
            }
        } catch (PDOException $e) {
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "Error buscando recibo -> ".$recibo;
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } else {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "Error ingreso de tipo de pago";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
    }
    return $resultado;
}

    public function guardarCobranzaManual($idCobranza, $idLugarPago, $cantidadComprobantes, $totalRecaudacion, $diferenciaImporte, $fechaApertura, $periodo, $cuota, $numeroLoteManual, $estado, $idUsuario) {
    $resultado = array();
    try {
        $db = Database::getConnection();

        //si entra para agregar viene sin idCobranza
        $ingresoPorAlta = FALSE;
        if (!isset($idCobranza)) {
            $ingresoPorAlta = TRUE;
            $sql = "INSERT INTO cobranza (IdLugarPago, CantidadComprobantes, TotalRecaudacion, DiferenciaImporte, FechaApertura, PeriodoContable, NumeroParte, TipoLote, NumeroLoteManual, Estado, FechaProceso, IdUsuarioProceso)
                VALUES (?, ?, ?, ?, ?, ?, ?, 'M', ?, 'A', DATE(NOW()), ?)";
            $stmt = $db->prepare($sql);
            $stmt->execute([$idLugarPago, $cantidadComprobantes, $totalRecaudacion, $diferenciaImporte, $fechaApertura, $periodo, $cuota, $numeroLoteManual, $idUsuario]);

            $resultado['estado'] = true;
            $resultado['mensaje'] = "OK";
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';

            $idCobranza = $db->lastInsertId();

            if ($cuota > 0) {
                //se cargan las cuotas de los matriculados que estan adheridos al debito por lugar de pago
                $sql2 = "SELECT dac.Id, dac.Importe, da.IdColegiado
                    FROM agremiacionesdebito ad
                    LEFT JOIN colegiadodeudaanual da ON da.IdColegiado = ad.IdColegiado AND da.Periodo = ad.Periodo
                    LEFT JOIN colegiadodeudaanualcuotas dac ON dac.IdColegiadoDeudaAnual = da.Id AND dac.Cuota = ?
                    WHERE ad.IdLugarPago = ? AND ad.Periodo = ? AND ad.PagoTotal = 'N' AND ad.Borrado = 0";
                $stmt2 = $db->prepare($sql2);
                $stmt2->execute([$cuota, $idLugarPago, $periodo]);
                $rows2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);

                $resultado['estado'] = TRUE;
                $resultado['idCobranza'] = $idCobranza;
                if (count($rows2) > 0) {
                    $totalPorCuotas = 0;
                    $cantidadComprobantes = 0;
                    foreach ($rows2 as $r2) {
                        $importe = $r2['Importe'];
                        $idColegiadoRow = $r2['IdColegiado'];
                        $tipoPago = 3;
                        $codigoPago = 1;
                        $comprobante = $r2['Id'];
                        $fechaPago = $fechaApertura;
                        $idAsistente = NULL;
                        $recargo = 0.00;
                        $totalPorCuotas += $importe;

                        $resCargaPago = $this->agregarPagoLote($idCobranza, $idColegiadoRow, $periodo, $cuota, $fechaPago, $importe, $comprobante, $tipoPago, $recargo, $codigoPago, $idAsistente);
                        if ($resCargaPago['estado']) {
                            $cantidadComprobantes += 1;
                        } else {
                            $resultado['estado'] = false;
                            $resultado['mensaje'] = "Error cargando colegiado en debito";
                            $resultado['clase'] = 'alert alert-danger';
                            $resultado['icono'] = 'glyphicon glyphicon-remove';
                            break;
                        }
                    }
                    $diferenciaImporte = $totalRecaudacion - $totalPorCuotas;
                    $estado = 'A';
                    //actualizarLoteCobranza($idCobranza, $estado, $cantidadComprobantes, $diferenciaImporte);
                }
            }
        }

        //si ingresa por un alta y no dio error o ingrasa por actualizacion, entonces se hace el update
        var_dump($ingresoPorAlta);
        echo '<br> '.sizeof($resultado);
        if (($ingresoPorAlta && $resultado['estado']) || !$ingresoPorAlta) {
            //actualiza cobranza tanto por entre como edicion como para actualizar los totales por el alta
            $sql = "UPDATE cobranza
                    SET CantidadComprobantes = ?, TotalRecaudacion = ?, DiferenciaImporte = ?, FechaApertura = ?, Estado = ?
                    WHERE Id = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$cantidadComprobantes, $totalRecaudacion, $diferenciaImporte, $fechaApertura, $estado, $idCobranza]);
            $resultado['estado'] = true;
            $resultado['mensaje'] = "OK";
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error lote manual -> ".$e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }

    return $resultado;
}

    public function obtenerNumeroLoteManual() {
    try {
        $db = Database::getConnection();
        $sql="SELECT MAX(NumeroLoteManual) AS NumeroLoteManual FROM cobranza";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $r = $stmt->fetch(PDO::FETCH_ASSOC);

        $resultado = array();
        $resultado['estado'] = TRUE;
        $numeroLoteManual = $r['NumeroLoteManual'];
        $numeroLoteManual++;
        $resultado['numeroLoteManual'] = $numeroLoteManual;
        $resultado['mensaje'] = "OK";
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado = array();
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando ultimo tomo y folio";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function obtenerProximaCuota($periodo, $idLugarPago) {
    try {
        $db = Database::getConnection();
        $sql="SELECT c.NumeroParte AS CuotaProcesada
            FROM cobranza c
            WHERE c.IdLugarPago = ?
            AND c.PeriodoContable = ?
            ORDER BY c.NumeroParte DESC
            LIMIT 1";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idLugarPago, $periodo]);
        $r = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($r) {
            $cuotaProcesada = $r['CuotaProcesada'];
            $cuotaProcesada += 1;
        } else {
            $cuotaProcesada = 0;
        }
    } catch (PDOException $e) {
        $cuotaProcesada = 0;
    }
    return $cuotaProcesada;
}

    public function obtenerCobranzaDetallePorIdCobranza($idCobranza) {
    try {
        $db = Database::getConnection();
        $sql = "SELECT cd.Recibo, cd.FechaPago, cd.Importe
            FROM cobranzadetalle cd
            WHERE cd.IdLoteCobranza = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idCobranza]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $resultado = array();
        if (count($rows) > 0) {
            $datos = array();
            foreach ($rows as $r) {
                $row = array(
                    'recibo' => $r['Recibo'],
                    'fechaPago' => $r['FechaPago'],
                    'importe' => $r['Importe']
                );
                array_push($datos, $row);
            }
            $resultado['estado'] = TRUE;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = false;
            $resultado['mensaje'] = "No hay pagos del lote";
            $resultado['clase'] = 'alert alert-warning';
            $resultado['icono'] = 'glyphicon glyphicon-remove';
        }
    } catch (PDOException $e) {
        $resultado = array();
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando pagos del lote";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function actualizarLoteCobranza($idCobranza, $estado, $cantidadComprobantes, $diferenciaImporte) {
    try {
        $db = Database::getConnection();
        $sql = "UPDATE cobranza
            SET Estado = ?,
            CantidadComprobantes = ?,
            DiferenciaImporte = ?
            WHERE Id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$estado, $cantidadComprobantes, $diferenciaImporte, $idCobranza]);

        $resultado = array();
        $resultado['estado'] = TRUE;
        $resultado['mensaje'] = "OK";
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado = array();
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "Error actualizando cobranza";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
    }
    return $resultado;
}

    public function cerrarCobranzaManual($idCobranza, $cantidadComprobantes) {
    try {
        $db = Database::getConnection();
        $sql = "UPDATE cobranza
            SET Estado = 'C',
            CantidadComprobantes = ?
            WHERE Id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$cantidadComprobantes, $idCobranza]);

        $resultado = array();
        $resultado['estado'] = TRUE;
        $resultado['mensaje'] = "OK";
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado = array();
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "Error actualizando cobranza";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
    }
    return $resultado;
}

}
