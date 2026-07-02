 <?php
class reconocimientoAntiguedadLogic {

    function obtenerActos() {
        try {
            $db = Database::getConnection();
            $sql="SELECT ra.Id, ra.AnioProceso, ra.FechaActo, ra.LugarActo, ra.Antiguedad
                FROM reconocimiento_antiguedad ra
                ORDER BY ra.Id";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            $rows = $stmt->fetchAll();

            $resultado = array();
            if (count($rows) > 0) {
                $datos = array();
                foreach ($rows as $r) {
                    $row = array (
                        'idReconocimientoAntiguedad' => $r['Id'],
                        'anioActo' => $r['AnioProceso'],
                        'fechaActo' => $r['FechaActo'],
                        'lugarActo' => $r['LugarActo'],
                        'antiguedad' => $r['Antiguedad'].' años'
                    );
                    array_push($datos, $row);
                }
                $resultado['estado'] = TRUE;
                $resultado['mensaje'] = "OK";
                $resultado['datos'] = $datos;
                $resultado['clase'] = 'alert alert-success';
                $resultado['icono'] = 'glyphicon glyphicon-ok';
            } else {
                $resultado['estado'] = FALSE;
                $resultado['mensaje'] = "No hay actos";
                $resultado['clase'] = 'alert alert-info';
                $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
            }
        } catch (PDOException $e) {
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "Error buscando actos";
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }

        return $resultado;
    }

    function obtenerActoPorId($idReconocimientoAntiguedad) {
        try {
            $db = Database::getConnection();
            $sql="SELECT ra.AnioProceso, ra.FechaActo, ra.LugarActo, ra.Antiguedad
                FROM reconocimiento_antiguedad ra
                WHERE ra.Id = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$idReconocimientoAntiguedad]);

            $resultado = array();
            $r = $stmt->fetch();
            if ($r) {
                $datos = array (
                            'idReconocimientoAntiguedad' => $idReconocimientoAntiguedad,
                            'anioActo' => $r['AnioProceso'],
                            'fechaActo' => $r['FechaActo'],
                            'lugarActo' => $r['LugarActo'],
                            'antiguedad' => $r['Antiguedad']
                    );
                $resultado['estado'] = TRUE;
                $resultado['mensaje'] = "OK";
                $resultado['datos'] = $datos;
                $resultado['clase'] = 'alert alert-success';
                $resultado['icono'] = 'glyphicon glyphicon-ok';
            } else {
                $resultado['estado'] = FALSE;
                $resultado['mensaje'] = "Error buscando acto";
                $resultado['clase'] = 'alert alert-danger';
                $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
            }
        } catch (PDOException $e) {
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "Error buscando acto";
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }

        return $resultado;
    }

    function obtenerActoDetallePorId($idReconocimientoAntiguedadDetalle) {
        try {
            $db = Database::getConnection();
            $sql="SELECT ra.Id, ra.AnioProceso, ra.FechaActo, ra.LugarActo, ra.Antiguedad, rad.IdColegiado, c.Matricula, p.Apellido, p.Nombres, p.Sexo
                FROM reconocimiento_antiguedad_detalle rad
                    INNER JOIN reconocimiento_antiguedad ra ON ra.Id = rad.IdReconocimientoAntiguedad
                    INNER JOIN colegiado c ON c.Id = rad.IdColegiado
                    INNER JOIN persona p ON p.Id = c.IdPersona
                WHERE rad.Id = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$idReconocimientoAntiguedadDetalle]);

            $resultado = array();
            $r = $stmt->fetch();
            if ($r) {
                $datos = array (
                            'idReconocimientoAntiguedad' => $r['Id'],
                            'anioActo' => $r['AnioProceso'],
                            'fechaActo' => $r['FechaActo'],
                            'lugarActo' => $r['LugarActo'],
                            'antiguedad' => $r['Antiguedad'],
                            'idColegiado' => $r['IdColegiado'],
                            'matricula' => $r['Matricula'],
                            'apellidoNombre' => trim($r['Apellido']).' '.trim($r['Nombres']),
                            'sexo' => $r['Sexo']
                    );
                $resultado['estado'] = TRUE;
                $resultado['mensaje'] = "OK";
                $resultado['datos'] = $datos;
                $resultado['clase'] = 'alert alert-success';
                $resultado['icono'] = 'glyphicon glyphicon-ok';
            } else {
                $resultado['estado'] = FALSE;
                $resultado['mensaje'] = "Error buscando colegiado";
                $resultado['clase'] = 'alert alert-danger';
                $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
            }
        } catch (PDOException $e) {
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "Error buscando colegiado";
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }

        return $resultado;
    }

    function obtenerActosPorIdColegiado($idColegiado) {
        try {
            $db = Database::getConnection();
            $sql="SELECT rad.Id, rad.EstadoInvitacion, rad.EstadoTesoreria, rad.EstadoMatricular
                FROM reconocimiento_antiguedad_detalle rad
                INNER JOIN reconocimiento_antiguedad ra ON ra.Id = rad.IdReconocimientoAntiguedad
                WHERE rad.IdColegiado = ? AND rad.EstadoInvitacion = 'C'";
            $stmt = $db->prepare($sql);
            $stmt->execute([$idColegiado]);
            $rows = $stmt->fetchAll();

            $resultado = array();
            if (count($rows) > 0) {
                $datos = array();
                foreach ($rows as $r) {
                    $row = array (
                        'idReconocimientoAntiguedadDetalle' => $r['Id'],
                        'estadoInvitacion' => $r['EstadoInvitacion'],
                        'estadoTesoreria' => $r['EstadoTesoreria'],
                        'estadoMatricular' => $r['EstadoMatricular']
                    );
                    array_push($datos, $row);
                }
                $resultado['estado'] = TRUE;
                $resultado['mensaje'] = "OK";
                $resultado['datos'] = $datos;
                $resultado['clase'] = 'alert alert-success';
                $resultado['icono'] = 'glyphicon glyphicon-ok';
            } else {
                $resultado['estado'] = FALSE;
                $resultado['mensaje'] = "No hay actos";
                $resultado['clase'] = 'alert alert-info';
                $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
            }
        } catch (PDOException $e) {
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "Error buscando actos";
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }

        return $resultado;
    }

    function obtenerColegiadosPorActo($idReconocimientoAntiguedad, $estado) {
        try {
            $db = Database::getConnection();
            $resultado = array();
            $periodoActual = PERIODO_ACTUAL;

            if ($estado == "ACTIVOS") {
                $filtro = " AND c.Estado IN(1, 5, 10)";
            } else {
                if ($estado == "ACTIVOS_CANCELACION_TRANSITORIA") {
                    $filtro = " AND c.Estado IN(1, 5, 10, 2)";
                } else {
                    if ($estado == "JUBILADOS") {
                        $filtro = " AND c.Estado IN(11, 14, 25, 26)";
                    } else {
                        $filtro = "";
                    }
                }
            }

            $sql="SELECT rad.Id AS IdDetalle, c.Id AS IdColegiado, rad.EstadoInvitacion, c.Matricula, p.Apellido, p.Nombres, p.Sexo, tm.Detalle,
                (SELECT COUNT(*) FROM colegiadodeudaanualcuotas a INNER JOIN colegiadodeudaanual b ON b.Id = a.IdColegiadoDeudaAnual WHERE b.IdColegiado = c.Id AND b.Periodo = ? AND a.FechaVencimiento <= DATE(NOW()) AND a.Estado = 1) AS CuotasImpagasPeriodoActual,
                (SELECT COUNT(*) FROM colegiadodeudaanualcuotas a INNER JOIN colegiadodeudaanual b ON b.Id = a.IdColegiadoDeudaAnual WHERE b.IdColegiado = c.Id AND b.Periodo < ? AND a.FechaVencimiento <= DATE(NOW()) AND a.Estado = 1) AS CuotasImpagasPeriodosAnteriores,
                (SELECT COUNT(*) FROM planpagoscuotas a INNER JOIN planpagos b ON b.Id = a.IdPlanPagos WHERE b.IdColegiado = c.Id AND a.Vencimiento <= DATE(NOW()) AND (a.FechaPago IS NULL OR a.FechaPago = '0000-00-00')) AS CuotasImpagasPlanPagos
                FROM reconocimiento_antiguedad_detalle rad
                INNER JOIN colegiado c ON c.Id = rad.IdColegiado
                INNER JOIN persona p ON p.Id = c.IdPersona
                LEFT JOIN tipomovimiento tm ON tm.Id = c.Estado
                WHERE rad.IdReconocimientoAntiguedad = ?".$filtro." ORDER BY p.Apellido, p.Nombres";
            $stmt = $db->prepare($sql);
            $stmt->execute([$periodoActual, $periodoActual, $idReconocimientoAntiguedad]);
            $rows = $stmt->fetchAll();

            if (count($rows) > 0) {
                $datos = array();
                foreach ($rows as $r) {
                    $cuotasImpagasPeriodoActual = $r['CuotasImpagasPeriodoActual'];
                    $cuotasImpagasPeriodosAnteriores = $r['CuotasImpagasPeriodosAnteriores'];
                    $cuotasImpagasPlanPagos = $r['CuotasImpagasPlanPagos'];
                    $codigoDeudor = 0;
                    if ($cuotasImpagasPeriodoActual > 0) {
                        $codigoDeudor = 1;
                    }
                    if ($cuotasImpagasPeriodosAnteriores > 0) {
                        if ($codigoDeudor == 0) {
                            $codigoDeudor = 2;
                        } else {
                            $codigoDeudor = 3;
                        }
                    }
                    if ($cuotasImpagasPlanPagos > 0) {
                        switch ($codigoDeudor) {
                            case '0':
                                $codigoDeudor = 7;
                                break;

                            case '1':
                                $codigoDeudor = 4;
                                break;

                            case '2':
                                $codigoDeudor = 6;
                                break;

                            case '3':
                                $codigoDeudor = 5;
                                break;

                            default:
                                break;
                        }
                    }
                    $row = array (
                            'idReconocimientoAntiguedadDetalle' => $r['IdDetalle'],
                            'idColegiado' => $r['IdColegiado'],
                            'estadoInvitacion' => $r['EstadoInvitacion'],
                            'codigoDeudor' => $codigoDeudor,
                            'estadoMatricular' => $r['Detalle'],
                            'matricula' => $r['Matricula'],
                            'apellidoNombre' => trim($r['Apellido']).' '.trim($r['Nombres']),
                            'sexo' => $r['Sexo']
                        );
                    array_push($datos, $row);
                }
                $resultado['estado'] = TRUE;
                $resultado['mensaje'] = "OK";
                $resultado['datos'] = $datos;
                $resultado['clase'] = 'alert alert-success';
                $resultado['icono'] = 'glyphicon glyphicon-ok';
            } else {
                $resultado['estado'] = FALSE;
                $resultado['mensaje'] = "No existe colegiado en el acto";
                $resultado['clase'] = 'alert alert-danger';
                $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
            }
        } catch (PDOException $e) {
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "Error buscando colegiado en el acto";
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }

        return $resultado;
    }

    function guardarActo($idReconocimientoAntiguedad, $anioActo, $fechaActo, $lugarActo, $antiguedad, $datosAnteriores){
        try {
            $db = Database::getConnection();
            $db->beginTransaction();
            $resultado = array();
            $sql = "UPDATE reconocimiento_antiguedad
                    SET AnioProceso = ?, FechaActo = ?, LugarActo = ?, Antiguedad = ?
                    WHERE Id = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$anioActo, $fechaActo, $lugarActo, $antiguedad, $idReconocimientoAntiguedad]);

            $tipoMovimiento = 'modificacion';
            $datos = serialize($datosAnteriores);
            $sql="INSERT INTO log_reconocimiento_antiguedad (Tabla, IdTabla, Fecha, TipoMovimiento, IdUsuario, Datos)
                VALUES ('reconocimiento_antiguedad', ?, NOW(), ?, ?, ?)";
            $stmt = $db->prepare($sql);
            $stmt->execute([$idReconocimientoAntiguedad, $tipoMovimiento, $_SESSION['user_id'], $datos]);

            $resultado['estado'] = TRUE;
            $resultado['idReconocimientoAntiguedad'] = $idReconocimientoAntiguedad;
            $resultado['mensaje'] = 'EL ACTO HA SIDO GUARDADO';
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
            $db->commit();
            return $resultado;
        } catch (PDOException $e) {
            if (isset($db) && $db->inTransaction()) $db->rollBack();
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "Error guardando acto -> ".$e->getMessage();
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
            return $resultado;
        }
    }

    function agregarActo($anioActo, $fechaActo, $lugarActo){
        try {
            $db = Database::getConnection();
            $db->beginTransaction();
            $resultado = array();
            $antiguedades = 1;
            while ($antiguedades <= 3) {
                switch ($antiguedades) {
                    case '1':
                        $antiguedad = 25;
                        break;

                    case '2':
                        $antiguedad = 50;
                        break;

                    case '3':
                        $antiguedad = 60;
                        break;
                }
                $antiguedades += 1;

                $sql="INSERT INTO reconocimiento_antiguedad (AnioProceso, FechaActo, LugarActo, Antiguedad)
                        VALUES (?, ?, ?, ?)";
                $stmt = $db->prepare($sql);
                $stmt->execute([$anioActo, $fechaActo, $lugarActo, $antiguedad]);
                $idReconocimientoAntiguedad = $db->lastInsertId();

                $datosAnteriores = array();
                $tipoMovimiento = 'alta';
                $sql="INSERT INTO log_reconocimiento_antiguedad (Tabla, IdTabla, Fecha, TipoMovimiento, IdUsuario, Datos)
                    VALUES ('reconocimiento_antiguedad', ?, NOW(), ?, ?, ?)";
                $stmt = $db->prepare($sql);
                $stmt->execute([$idReconocimientoAntiguedad, $tipoMovimiento, $_SESSION['user_id'], serialize($datosAnteriores)]);

                $resultado['estado'] = TRUE;
                $resultado['idReconocimientoAntiguedad'] = $idReconocimientoAntiguedad;
                $resultado['mensaje'] = 'EL ACTO HA SIDO GUARDADO';
                $resultado['clase'] = 'alert alert-success';
                $resultado['icono'] = 'glyphicon glyphicon-ok';

                $anioTitulo = $anioActo - $antiguedad;
                $sql_colegiados = "SELECT c.Id
                        FROM colegiadotitulo ct
                        INNER JOIN colegiado c ON c.Id = ct.IdColegiado
                        WHERE YEAR(ct.FechaTitulo) = ?
                        AND c.Estado IN(0, 1, 2, 5, 10, 11, 14, 25, 26)
                        ORDER BY c.Matricula";
                $stmt_colegiados = $db->prepare($sql_colegiados);
                $stmt_colegiados->execute([$anioTitulo]);
                $colegiados = $stmt_colegiados->fetchAll();

                if (count($colegiados) > 0) {
                    foreach ($colegiados as $col) {
                        $idColegiado = $col['Id'];
                        $sql_insert = "INSERT INTO reconocimiento_antiguedad_detalle (IdReconocimientoAntiguedad, IdColegiado, EstadoInvitacion, FechaCarga, IdUsuario)
                            VALUES (?, ?, 'A_CITAR', NOW(), ?)";
                        $stmt_insert = $db->prepare($sql_insert);
                        $stmt_insert->execute([$idReconocimientoAntiguedad, $idColegiado, $_SESSION['user_id']]);
                    }
                } else {
                    $resultado['mensaje'] .= " NO SE ENCONTRARON COLEGIADOS.";
                    $resultado['clase'] = 'alert alert-danger';
                    $resultado['icono'] = 'glyphicon glyphicon-remove';
                }
            }

            $db->commit();
            return $resultado;
        } catch (PDOException $e) {
            if (isset($db) && $db->inTransaction()) $db->rollBack();
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "Error guardando acto -> ".$e->getMessage();
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
            return $resultado;
        }
    }

    function borrarActo($idReconocimientoAntiguedad) {
        try {
            $db = Database::getConnection();
            $resultado = array();
            $sql = "UPDATE reconocimiento_antiguedad_detalle
                    SET Borrado = 1, FechaBorrado = NOW(), IdUsuarioBorrado = ?
                    WHERE Id = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$_SESSION['user_id'], $idReconocimientoAntiguedad]);
            $resultado['estado'] = TRUE;
            $resultado['mensaje'] = 'EL COLEGIADO HA SIDO BORRADO';
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } catch (PDOException $e) {
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "ERROR AL BORRAR EL COLEGIADO -> ".$e->getMessage();
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-remove';
        }
        return $resultado;
    }

}
