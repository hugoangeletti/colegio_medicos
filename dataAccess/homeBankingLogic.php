<?php
class homeBankingLogic {

    public function obtenerHomaBankingPorId($idHomeBankingArchivo) {
    $resultado = array();
    try {
        $db = Database::getConnection();
        $sql = "SELECT a.Id, a.FechaProceso, a.PeriodoProceso, a.FechaPrimerVto, a.ImportePrimerVto, a.Codigo, a.Control, a.Refresh, a.PagoMisCuentas, a.PathArchivos
            FROM home_banking_archivo a
            WHERE a.Id = ?
            AND a.Borrado = 0";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idHomeBankingArchivo]);
        $rows = $stmt->fetchAll();
        $resultado['cantidad'] = count($rows);
        if ($resultado['cantidad'] > 0) {
            $row = $rows[0];
            $datos = array(
                'idHomeBankingArchivo' => $row['Id'],
                'fechaProceso' => $row['FechaProceso'],
                'periodoProceso' => $row['PeriodoProceso'],
                'fechaPrimerVencimiento' => $row['FechaPrimerVto'],
                'importe' => $row['ImportePrimerVto'],
                'codigo' => $row['Codigo'],
                'control' => $row['Control'],
                'refresh' => $row['Refresh'],
                'pagoMisCuentas' => $row['PagoMisCuentas'],
                'pathArchivo' => $row['PathArchivos']
            );
            $resultado['estado'] = true;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = false;
            $resultado['mensaje'] = "No se encontro envio home banking";
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

    public function obtenerHomaBankingGenerados($anio) {
    $resultado = array();
    try {
        $db = Database::getConnection();
        $sql = "SELECT a.Id, a.FechaProceso, a.PeriodoProceso, a.FechaPrimerVto, a.ImportePrimerVto, a.Codigo, a.Control, a.Refresh, a.PagoMisCuentas, a.PathArchivos, a.Borrado
            FROM home_banking_archivo a
            WHERE SUBSTR(a.PeriodoProceso, 1, 4) = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$anio]);
        $rows = $stmt->fetchAll();
        $resultado['cantidad'] = count($rows);
        if ($resultado['cantidad'] > 0) {
            $datos = array();
            foreach ($rows as $row) {
                $r = array(
                    'idHomeBankingArchivo' => $row['Id'],
                    'fechaProceso' => $row['FechaProceso'],
                    'periodoProceso' => $row['PeriodoProceso'],
                    'fechaPrimerVencimiento' => $row['FechaPrimerVto'],
                    'importe' => $row['ImportePrimerVto'],
                    'codigo' => $row['Codigo'],
                    'control' => $row['Control'],
                    'refresh' => $row['Refresh'],
                    'pagoMisCuentas' => $row['PagoMisCuentas'],
                    'pathArchivo' => $row['PathArchivos'],
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
            $resultado['mensaje'] = "No se encontro envio home banking";
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

    public function obtenerNuevaLiquidacionPorPeriodo($periodo) {
    $resultado = array();
    try {
        $db = Database::getConnection();
        $sql="SELECT hba.Codigo
            FROM home_banking_archivo hba
            WHERE hba.PeriodoProceso = ? AND hba.Borrado = 0
            ORDER BY hba.Codigo DESC
            LIMIT 1";
        $stmt = $db->prepare($sql);
        $stmt->execute([$periodo]);
        $row = $stmt->fetch();
        $codigo = $row ? $row['Codigo'] : null;

        if (isset($codigo) && $codigo <> "") {
            //si el periodo ya fue procesado, entonces genero un codigo extra
            $anioProceso = substr($periodo, 0, 4);
            $codigoExtra = substr($anioProceso, 2, 2).'013';

            $sql2 = "SELECT MAX(hba.Codigo)
                    FROM home_banking_archivo hba
                    WHERE SUBSTR(hba.PeriodoProceso, 1, 4) = ? AND hba.Codigo >= ? AND hba.Borrado = 0";
            $stmt2 = $db->prepare($sql2);
            $stmt2->execute([$anioProceso, $codigoExtra]);
            $row2 = $stmt2->fetch();
            $codigo = $row2 ? $row2[0] : null;

            if (isset($codigo) && $codigo <> "") {
                $extra = intval(substr($codigo, 2, 3));
                $extra += 1;
                $subCodigoExtra = rellenarCeros($extra, 3);
            } else {
                $codigo = $codigoExtra;
                $subCodigoExtra = '013';
            }
            $codigo = substr($codigo, 0, 2).$subCodigoExtra;
        } else {
            //si el periodo aun no fue procesado, agrego el codigo del mes procesado
            $codigo = substr(date('Y'), 2, 2).rellenarCeros(date('m'), 3);
        }

        if (isset($codigo)) {
            $resultado['estado'] = true;
            $resultado['mensaje'] = "OK";
            $resultado['codigo'] = $codigo;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = false;
            $resultado['mensaje'] = "Error buscando codigo";
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando codigo";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
    }
    return $resultado;
}

    public function agregarHomeBankingArchivo($fechaPrimerVencimiento, $fechaSegundoVencimiento, $codigoLiquidacion, $control, $refresh, $pagoMisCuentas, $path, $conect) {
    try {
        $db = Database::getConnection();
        $db->beginTransaction();
        $resultado = array();

        $periodoProceso = date('Y').date('m');
        $sql="INSERT INTO home_banking_archivo
                (FechaProceso, PeriodoProceso, IdUsuario, FechaPrimerVto, ImportePrimerVto, FechaSegundoVto, ImporteSegundoVto, Codigo, Control, Refresh, PagoMisCuentas, PathArchivos)
                VALUE (NOW(), ?, ?, ?, 0, ?, 0, ?, ?, ?, ?, ?)";
        $stmt = $db->prepare($sql);
        $stmt->execute([$periodoProceso, $_SESSION['user_id'], $fechaPrimerVencimiento, $fechaSegundoVencimiento, $codigoLiquidacion, $control, $refresh, $pagoMisCuentas, $path]);
        $idHomeBankingArchivo = $db->lastInsertId();
        $resultado['estado'] = TRUE;
        $resultado['mensaje'] = "OK";
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
        $resultado['idHomeBankingArchivo'] = $idHomeBankingArchivo;

        $db->commit();
        return $resultado;
    } catch (PDOException $e) {
        $db->rollBack();
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "ERROR AL INSERTAR home_banking_archivo ".$e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
        return $resultado;
    }
}

    public function agregarLinkPagos($idHomeBankingArchivo, $concepto, $matricula, $idAsistente, $fechaPrimerVencimiento, $importe, $fechaSegundoVencimiento, $mensajeTicket, $mensajePantalla, $codigobarra, $arrayCuotas, $conect){
    try {
        $db = Database::getConnection();
        $db->beginTransaction();
        $resultado = array();

        if (isset($idAsistente) && $idAsistente > 0) {
            $matricula_idAsistente = $idAsistente;
        } else {
            $matricula_idAsistente = $matricula;
        }
        $sql="INSERT INTO linkpagos
                (IdHomeBankingArchivo, Concepto, Matricula, FechaPrimerVto, ImportePrimerVto, FechaSegundoVto, ImporteSegundoVto, MensajeTicket, MensajePantalla, CodigoBarras)
                VALUE (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idHomeBankingArchivo, $concepto, $matricula_idAsistente, $fechaPrimerVencimiento, $importe, $fechaSegundoVencimiento, $importe, $mensajeTicket, $mensajePantalla, $codigobarra]);
        $idLinkPagos = $db->lastInsertId();
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "";
        foreach ($arrayCuotas as $cuotaDeuda) {
            if (!isset($cuotaDeuda['idDeuda'])) break;
            $idDeuda = $cuotaDeuda['idDeuda'];
            $importePrimerVto = $cuotaDeuda['recargo'];
            $importeSegundoVto = $cuotaDeuda['recargo'];
            $sql="INSERT INTO linkpagosdetalle (IdLinkPagos, IdDeuda, ImportePrimerVto, ImporteSegundoVto)
                VALUES (?, ?, ?, ?)";
            $stmt = $db->prepare($sql);
            $stmt->execute([$idLinkPagos, $idDeuda, $importePrimerVto, $importeSegundoVto]);
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
        $resultado['mensaje'] = "ERROR AL INSERTAR CUOTAS EN linkpagos ".$e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
        return $resultado;
    }
}

    public function agregarEnvioHomeBanking($idHomeBankingArchivo, $concepto, $idColegiado, $idAsistente, $fechaPrimerVencimiento, $importe, $fechaSegundoVencimiento, $mensajeTicket, $mensajePantalla, $codigobarra, $arrayCuotas, $conect){
    try {
        $db = Database::getConnection();
        $db->beginTransaction();
        $resultado = array();

        $sql="INSERT INTO home_banking_archivo_concepto
                (IdHomeBankingArchivo, Concepto, IdColegiado, IdAsistente, FechaPrimerVto, ImportePrimerVto, FechaSegundoVto, ImporteSegundoVto, MensajeTicket, MensajePantalla, CodigoBarras)
                VALUE (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idHomeBankingArchivo, $concepto, $idColegiado, $idAsistente, $fechaPrimerVencimiento, $importe, $fechaSegundoVencimiento, $importe, $mensajeTicket, $mensajePantalla, $codigobarra]);
        $idHomeBankingArchivoConcepto = $db->lastInsertId();
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "";
        foreach ($arrayCuotas as $cuotaDeuda) {
            if (!isset($cuotaDeuda['idDeuda'])) break;
            $idDeuda = $cuotaDeuda['idDeuda'];
            $importePrimerVto = $cuotaDeuda['recargo'];
            $importeSegundoVto = $cuotaDeuda['recargo'];
            $sql="INSERT INTO home_banking_archivo_concepto_detalle (IdHomeBankingArchivoConcepto, IdDeuda, ImportePrimerVto, ImporteSegundoVto)
                VALUES (?, ?, ?, ?)";
            $stmt = $db->prepare($sql);
            $stmt->execute([$idHomeBankingArchivoConcepto, $idDeuda, $importePrimerVto, $importeSegundoVto]);
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
        $resultado['mensaje'] = "ERROR AL INSERTAR CUOTAS EN linkpagos ".$e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
        return $resultado;
    }
}

    public function obtenerHomeBankingConceptoPorIdArchivo($idHomeBankingArchivo) {
    $resultado = array();
    try {
        $db = Database::getConnection();
        $sql = "SELECT a.Id, a.Concepto, a.Matricula, a.FechaSegundoVto, a.ImporteSegundoVto, a.MensajeTicket, a.MensajePantalla
            FROM linkpagos a
            WHERE a.IdHomeBankingArchivo = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idHomeBankingArchivo]);
        $rows = $stmt->fetchAll();
        $resultado['cantidad'] = count($rows);
        if ($resultado['cantidad'] > 0) {
            $datos = array();
            foreach ($rows as $row) {
                $r = array(
                    'idLinkPagos' => $row['Id'],
                    'concepto' => $row['Concepto'],
                    'matricula' => $row['Matricula'],
                    'fechaVencimiento' => $row['FechaSegundoVto'],
                    'importe' => $row['ImporteSegundoVto'],
                    'mensajeTicket' => $row['MensajeTicket'],
                    'mensajePantalla' => $row['MensajePantalla']
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
            $resultado['mensaje'] = "No se encontro envio home banking";
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

    public function obtenerLinkPagosPorIdHomeBankin($idHomeBankingArchivo) {
    $resultado = array();
    try {
        $db = Database::getConnection();
        $sql = "(SELECT 'COLEGIACION' AS Origen, a.MensajeTicket, a.Matricula, CONCAT(p.Apellido, ' ', p.Nombres) AS ApellidoNombre, a.ImportePrimerVto
                FROM linkpagos a
                INNER JOIN colegiado c ON c.Matricula = a.Matricula
                INNER JOIN persona p ON p.Id = c.IdPersona
                WHERE a.IdHomeBankingArchivo = ? AND a.Concepto BETWEEN '001' AND '199'
                GROUP BY a.Matricula)
        UNION ALL
        (SELECT 'CURSOS' AS Origen, a.MensajeTicket, a.Matricula, ca.ApellidoNombre, a.ImportePrimerVto
                FROM linkpagos a
                INNER JOIN cursosasistente ca ON ca.Id = a.Matricula
                WHERE a.IdHomeBankingArchivo = ? AND a.Concepto > '199'
                GROUP BY a.Matricula)";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idHomeBankingArchivo, $idHomeBankingArchivo]);
        $rows = $stmt->fetchAll();
        $resultado['cantidad'] = count($rows);
        if ($resultado['cantidad'] > 0) {
            $datos = array();
            foreach ($rows as $row) {
                $r = array(
                    'origen' => $row['Origen'],
                    'mensajeTicket' => $row['MensajeTicket'],
                    'matricula' => $row['Matricula'],
                    'apellidoNombre' => $row['ApellidoNombre'],
                    'importe' => $row['ImportePrimerVto']
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
            $resultado['mensaje'] = "No se encontro envio home banking";
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

    public function obtenerColegiadoConDeudaPeriodosAnteriores($periodoProceso) {
    $resultado = array();
    try {
        $db = Database::getConnection();
        $periodoActual = PERIODO_ACTUAL;
        $sql = "SELECT c.Id, c.Matricula, p.Apellido, p.Nombres, p.NumeroDocumento, c.Estado
            FROM colegiado c
            INNER JOIN persona p ON p.Id = c.IdPersona
            LEFT JOIN linkpagos lp ON lp.Matricula = c.Matricula AND lp.Concepto = '001' AND (lp.IdHomeBankingArchivo IN(SELECT hba.Id FROM home_banking_archivo hba WHERE hba.Id = lp.IdHomeBankingArchivo AND hba.PeriodoProceso = ? AND hba.Borrado = 0))
            WHERE (c.Estado IN(1, 5, 10)  OR (c.Estado = 2 AND TIMESTAMPDIFF(DAY, c.FechaActualizacion, DATE(NOW())) < 45))
            AND c.Id IN(SELECT b.IdColegiado
                                        FROM colegiadodeudaanualcuotas a
                                        INNER JOIN colegiadodeudaanual b ON b.Id = a.IdColegiadoDeudaAnual AND b.Periodo < ? AND b.Estado = 'A'
                                        WHERE a.Estado = 1
                                        GROUP BY b.IdColegiado)
            AND lp.Id IS NULL
            ORDER BY c.Matricula";
        $stmt = $db->prepare($sql);
        $stmt->execute([$periodoProceso, $periodoActual]);
        $rows = $stmt->fetchAll();

        $datos = array();
        foreach ($rows as $row) {
            $r = array(
                'idColegiado' => $row['Id'],
                'matricula' => $row['Matricula'],
                'apellido' => trim($row['Apellido']),
                'nombre' => trim($row['Nombres']),
                'numeroDocumento' => $row['NumeroDocumento'],
                'estadoMatricular' => $row['Estado']
            );
            array_push($datos, $r);
        }
        $resultado['estado'] = true;
        $resultado['mensaje'] = "OK";
        $resultado['datos'] = $datos;
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando Matriculas activas";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function obtenerColegiadoConDeudaPeriodoActual($periodoProceso, $fechaVencimiento) {
    $resultado = array();
    try {
        $db = Database::getConnection();
        $periodoActual = PERIODO_ACTUAL;
        $sql = "SELECT c.Id, c.Matricula, p.Apellido, p.Nombres, p.NumeroDocumento, c.Estado
            FROM colegiado c
            INNER JOIN persona p ON p.Id = c.IdPersona
            LEFT JOIN linkpagos lp ON lp.Matricula = c.Matricula
                                    AND lp.Concepto IN(SELECT hbc.Codigo FROM home_banking_concepto hbc WHERE hbc.CuotaPeriodo IS NOT NULL)
                                    AND lp.IdHomeBankingArchivo IN(SELECT hba.Id FROM home_banking_archivo hba WHERE hba.Id = lp.IdHomeBankingArchivo AND hba.PeriodoProceso = ? AND hba.Borrado = 0)
            LEFT JOIN debitotarjeta dt ON dt.IdColegiado = c.Id AND dt.Estado = 'A'
            LEFT JOIN debitocbu dc ON dc.IdColegiado = c.Id AND dc.Estado = 'A'
            LEFT JOIN agremiacionesdebito ad ON ad.Periodo = ? AND ad.IdColegiado = c.Id AND ad.Borrado = 0
            WHERE (c.Estado IN(1, 5, 10)  OR (c.Estado = 2 AND TIMESTAMPDIFF(DAY, c.FechaActualizacion, DATE(NOW())) < 45))
            AND c.Id IN(SELECT b.IdColegiado
                                        FROM colegiadodeudaanualcuotas a
                                        INNER JOIN colegiadodeudaanual b ON b.Id = a.IdColegiadoDeudaAnual AND b.Periodo = ? AND b.Estado = 'A'
                                        WHERE a.Estado = 1 AND a.FechaVencimiento <= ?
                                        GROUP BY b.IdColegiado)
            AND lp.Id IS NULL
            AND dt.id IS NULL AND dc.Id IS NULL AND ad.Id IS NULL
            ORDER BY c.Matricula";
        $stmt = $db->prepare($sql);
        $stmt->execute([$periodoProceso, $periodoActual, $periodoActual, $fechaVencimiento]);
        $rows = $stmt->fetchAll();

        $datos = array();
        foreach ($rows as $row) {
            $r = array(
                'idColegiado' => $row['Id'],
                'matricula' => $row['Matricula'],
                'apellido' => trim($row['Apellido']),
                'nombre' => trim($row['Nombres']),
                'numeroDocumento' => $row['NumeroDocumento'],
                'estadoMatricular' => $row['Estado']
            );
            array_push($datos, $r);
        }
        $resultado['estado'] = true;
        $resultado['mensaje'] = "OK";
        $resultado['datos'] = $datos;
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando Matriculas activas";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function obtenerColegiadoPagoTotal($periodoProceso) {
    $resultado = array();
    try {
        $db = Database::getConnection();
        $periodoActual = PERIODO_ACTUAL;
        $sql = "SELECT c.Id, c.Matricula, dat.Id AS IdColegiadoDeudaAnualTotal, dat.Importe
            FROM colegiado c
            INNER JOIN colegiadodeudaanual da ON da.IdColegiado = c.Id AND da.Periodo = ?
            INNER JOIN colegiadodeudaanualtotal dat ON dat.IdColegiadoDeudaAnual = da.Id AND dat.IdEstado = 1
            LEFT JOIN debitotarjeta dt ON dt.IdColegiado = c.Id AND dt.Estado = 'A'
            LEFT JOIN debitocbu dc ON dc.IdColegiado = c.Id AND dc.Estado = 'A'
            LEFT JOIN agremiacionesdebito ad ON ad.Periodo = ? AND ad.IdColegiado = c.Id AND ad.Borrado = 0
            LEFT JOIN linkpagos lp ON lp.Matricula = c.Matricula
                                    AND lp.Concepto = '014'
                                    AND lp.IdHomeBankingArchivo IN(SELECT hba.Id FROM home_banking_archivo hba WHERE hba.Id = lp.IdHomeBankingArchivo AND hba.PeriodoProceso = ? AND hba.Borrado = 0)
            WHERE c.Estado IN(1, 5, 10)
            AND lp.Id IS NULL
            AND dt.id IS NULL AND dc.Id IS NULL AND ad.Id IS NULL
            ORDER BY c.Matricula";
        $stmt = $db->prepare($sql);
        $stmt->execute([$periodoActual, $periodoActual, $periodoProceso]);
        $rows = $stmt->fetchAll();

        $datos = array();
        foreach ($rows as $row) {
            $r = array(
                'idColegiado' => $row['Id'],
                'matricula' => $row['Matricula'],
                'idColegiadoDeudaAnualTotal' => $row['IdColegiadoDeudaAnualTotal'],
                'importe' => $row['Importe']
            );
            array_push($datos, $r);
        }
        $resultado['estado'] = true;
        $resultado['mensaje'] = "OK";
        $resultado['datos'] = $datos;
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando Matriculas activas";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function obtenerColegiadoConDeudaPlanPagos($periodoProceso) {
    $resultado = array();
    try {
        $db = Database::getConnection();
        $periodoActual = PERIODO_ACTUAL;
        $sql = "SELECT c.Id, c.Matricula, p.Apellido, p.Nombres, p.NumeroDocumento, c.Estado
            FROM colegiado c
            INNER JOIN persona p ON p.Id = c.IdPersona
            LEFT JOIN linkpagos lp ON lp.Matricula = c.Matricula
                                    AND lp.Concepto = '002'
                                    AND lp.IdHomeBankingArchivo IN(SELECT hba.Id FROM home_banking_archivo hba WHERE hba.Id = lp.IdHomeBankingArchivo AND hba.PeriodoProceso = ? AND hba.Borrado = 0)
            WHERE (c.Estado IN(1, 5, 10)  OR (c.Estado = 2 AND TIMESTAMPDIFF(DAY, c.FechaActualizacion, DATE(NOW())) < 45))
            AND c.Id IN(SELECT pp.IdColegiado
                          FROM planpagoscuotas ppc
                        INNER JOIN planpagos pp ON pp.Id = ppc.IdPlanPagos
                        LEFT JOIN pagosnoregistrados pnr ON pnr.Recibo = ppc.Id AND pnr.TipoPago='P'
                        WHERE pp.IdColegiado = c.Id AND pp.Estado = 'A'
                        AND ppc.IdTipoEstadoCuota = 1 AND ppc.Vencimiento <= DATE(NOW())
                        AND pnr.Id IS NULL)
            AND lp.Id IS NULL
            ORDER BY c.Matricula";
        $stmt = $db->prepare($sql);
        $stmt->execute([$periodoProceso]);
        $rows = $stmt->fetchAll();

        $datos = array();
        foreach ($rows as $row) {
            $r = array(
                'idColegiado' => $row['Id'],
                'matricula' => $row['Matricula'],
                'apellido' => trim($row['Apellido']),
                'nombre' => trim($row['Nombres']),
                'numeroDocumento' => $row['NumeroDocumento'],
                'estadoMatricular' => $row['Estado']
            );
            array_push($datos, $r);
        }
        $resultado['estado'] = true;
        $resultado['mensaje'] = "OK";
        $resultado['datos'] = $datos;
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando Matriculas activas";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function obtenerColegiadoCuotaVigentePlanPagos($periodoProceso) {
    $resultado = array();
    try {
        $db = Database::getConnection();
        $periodoActual = PERIODO_ACTUAL;
        $sql = "SELECT c.Id, c.Matricula, p.Apellido, p.Nombres, p.NumeroDocumento, c.Estado
            FROM colegiado c
            INNER JOIN persona p ON p.Id = c.IdPersona
            LEFT JOIN linkpagos lp ON lp.Matricula = c.Matricula
                                    AND lp.Concepto = '003'
                                    AND lp.IdHomeBankingArchivo IN(SELECT hba.Id FROM home_banking_archivo hba WHERE hba.Id = lp.IdHomeBankingArchivo AND hba.PeriodoProceso = ? AND hba.Borrado = 0)
            WHERE (c.Estado IN(1, 5, 10)  OR (c.Estado = 2 AND TIMESTAMPDIFF(DAY, c.FechaActualizacion, DATE(NOW())) < 45))
            AND c.Id IN(SELECT DISTINCT pp.IdColegiado
                          FROM planpagoscuotas ppc
                        INNER JOIN planpagos pp ON pp.Id = ppc.IdPlanPagos
                        WHERE pp.IdColegiado = c.Id AND pp.Estado = 'A'
                        AND ppc.IdTipoEstadoCuota = 1 AND ppc.Vencimiento > DATE(NOW()))
            AND lp.Id IS NULL
            ORDER BY c.Matricula";
        $stmt = $db->prepare($sql);
        $stmt->execute([$periodoProceso]);
        $rows = $stmt->fetchAll();

        $datos = array();
        foreach ($rows as $row) {
            $r = array(
                'idColegiado' => $row['Id'],
                'matricula' => $row['Matricula'],
                'apellido' => trim($row['Apellido']),
                'nombre' => trim($row['Nombres']),
                'numeroDocumento' => $row['NumeroDocumento'],
                'estadoMatricular' => $row['Estado']
            );
            array_push($datos, $r);
        }
        $resultado['estado'] = true;
        $resultado['mensaje'] = "OK";
        $resultado['datos'] = $datos;
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando Matriculas activas";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function actualizarHomeBankingArchivos($idHomeBankingArchivo, $total, $control, $refresh, $pagoMisCuentas, $path, $conect) {
    try {
        $db = Database::getConnection();
        $db->beginTransaction();
        $resultado = array();

        $sql="UPDATE home_banking_archivo
                SET ImportePrimerVto = ?, ImporteSegundoVto = ?, Control = ?, Refresh = ?, PagoMisCuentas = ?, PathArchivos = ?
                WHERE Id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$total, $total, $control, $refresh, $pagoMisCuentas, $path, $idHomeBankingArchivo]);
        $resultado['estado'] = TRUE;
        $resultado['mensaje'] = "OK";
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';

        $db->commit();
        return $resultado;
    } catch (PDOException $e) {
        $db->rollBack();
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "ERROR AL ACTUALIZAR home_banking_archivo ".$e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
        return $resultado;
    }
}

    public function borrarHomeBankingArchivo($idHomeBankingArchivo) {
    $resultado = array();
    try {
        $db = Database::getConnection();
        $sql="UPDATE home_banking_archivo
                SET Borrado = 1, FechaBorrado = NOW(), IdUsuarioBorrado = ?
                WHERE Id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$_SESSION['user_id'], $idHomeBankingArchivo]);
        $resultado['estado'] = TRUE;
        $resultado['mensaje'] = "OK";
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "ERROR AL BORRAR home_banking_archivo ".$e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function noExisteConceptoPorIdColegiado($idColegiado, $idCursosAsistente, $periodoProceso, $concepto, $conect){
    try {
        $db = Database::getConnection();

        if (isset($idColegiado) && $idColegiado > 0) {
            $sql="SELECT COUNT(a.Id) AS Cantidad
                FROM home_banking_archivo_concepto a
                INNER JOIN home_banking_archivo b ON b.Id = a.IdHomeBankingArchivo AND b.PeriodoProceso = ? AND b.Borrado = 0
                WHERE a.IdColegiado = ? AND a.Concepto = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$periodoProceso, $idColegiado, $concepto]);
        } else {
            if (isset($idCursosAsistente) && $idCursosAsistente > 0) {
                $sql="SELECT COUNT(a.Id) AS Cantidad
                    FROM home_banking_archivo_concepto a
                    INNER JOIN home_banking_archivo b ON b.Id = a.IdHomeBankingArchivo AND b.PeriodoProceso = ? AND b.Borrado = 0
                    WHERE a.IdAsistente = ? AND a.Concepto = ?";
                $stmt = $db->prepare($sql);
                $stmt->execute([$periodoProceso, $idCursosAsistente, $concepto]);
            } else {
                return FALSE;
            }
        }
        $row = $stmt->fetch();
        $cantidad = $row ? $row['Cantidad'] : 0;
        return ($cantidad == 0) ? TRUE : FALSE;
    } catch (PDOException $e) {
        return FALSE;
    }
}

    public function obtenerCuotasCursosParaHomeBanking($fechaVencimiento, $periodoProceso) {
    $resultado = array();
    try {
        $db = Database::getConnection();
        $sql="SELECT cac.Id AS IdCursosAsistenteCuota, ca.IdCursos, cac.Cuota, cac.Importe, cac.FechaVencimiento, ca.Id AS IdCursosAsistente
                FROM cursosasistentecuotas cac
                INNER JOIN cursosasistente ca ON (ca.Id = cac.IdCursosAsistente AND ca.Estado = 'S')
                INNER JOIN cursos c ON c.Id = ca.IdCursos AND c.Estado = 'A'
                LEFT JOIN linkpagos lp ON lp.Matricula = ca.Id
                                    AND lp.Concepto BETWEEN '200' AND '299'
                                    AND lp.IdHomeBankingArchivo IN(SELECT hba.Id FROM home_banking_archivo hba WHERE hba.Id = lp.IdHomeBankingArchivo AND hba.PeriodoProceso = ? AND hba.Borrado = 0)
            WHERE cac.FechaVencimiento <= ?
            AND (cac.FechaPago = '0000-00-00' OR cac.FechaPago IS NULL)
            AND lp.Id IS NULL
            ORDER BY ca.Id, cac.Id";
        $stmt = $db->prepare($sql);
        $stmt->execute([$periodoProceso, $fechaVencimiento]);
        $rows = $stmt->fetchAll();

        if (count($rows) > 0) {
            $datos = array();
            foreach ($rows as $row) {
                $r = array(
                    'idCursosAsistenteCuota' => $row['IdCursosAsistenteCuota'],
                    'idCursos' => $row['IdCursos'],
                    'cuota' => $row['Cuota'],
                    'importe' => $row['Importe'],
                    'fechaVencimiento' => $row['FechaVencimiento'],
                    'idCursosAsistente' => $row['IdCursosAsistente']
                );
                array_push($datos, $r);
            }
            $resultado['estado'] = TRUE;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "No hay cuotas a pagar";
            $resultado['clase'] = 'alert alert-info';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "Error buscando cuots a pagar";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
    }
    return $resultado;
}
}
