<?php
class enviosCajaMedicosLogic {

    function obtenerEnvios() {
        try {
            $db = Database::getConnection();
            $sql="SELECT a.Id, a.FechaEnvio, a.FechaDesde, a.FechaHasta, a.Mail, a.`Path`, a.NombreArchivo, a.NombrePdf, a.IdUsuario
                FROM envios_caja_medicos a
                WHERE a.Borrado = 0";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            $rows = $stmt->fetchAll();

            $resultado = array();
            $datos = array();
            foreach ($rows as $row) {
                $item = array (
                    'idEnviosCajaMedicos' => $row['Id'],
                    'fechaEnvio' => $row['FechaEnvio'],
                    'fechaDesde' => $row['FechaDesde'],
                    'fechaHasta' => $row['FechaHasta'],
                    'mail' => $row['Mail'],
                    'path' => $row['Path'],
                    'nombreArchivo' => $row['NombreArchivo'],
                    'nombrePdf' => $row['NombrePdf'],
                    'idUsuario' => $row['IdUsuario']
                );
                array_push($datos, $item);
            }
            $resultado['estado'] = TRUE;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } catch (PDOException $e) {
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "Error buscando Envios: " . $e->getMessage();
            $resultado['clase'] = 'alert alert-error';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }

        return $resultado;
    }

    function obtenerEnvioPorId($idEnviosCajaMedicos) {
        try {
            $db = Database::getConnection();
            $sql="SELECT a.Id, a.FechaEnvio, a.FechaDesde, a.FechaHasta, a.Mail, a.`Path`, a.NombreArchivo, a.NombrePdf, a.IdUsuario
                FROM envios_caja_medicos a
                WHERE a.Id = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$idEnviosCajaMedicos]);
            $row = $stmt->fetch();

            $resultado = array();
            if ($row) {
                $datos = array(
                    'idEnviosCajaMedicos' => $row['Id'],
                    'fechaEnvio' => $row['FechaEnvio'],
                    'fechaDesde' => $row['FechaDesde'],
                    'fechaHasta' => $row['FechaHasta'],
                    'mail' => $row['Mail'],
                    'path' => $row['Path'],
                    'nombreArchivo' => $row['NombreArchivo'],
                    'nombrePdf' => $row['NombrePdf'],
                    'idUsuario' => $row['IdUsuario']
                    );
                $resultado['estado'] = TRUE;
                $resultado['mensaje'] = "OK";
                $resultado['datos'] = $datos;
                $resultado['clase'] = 'alert alert-success';
                $resultado['icono'] = 'glyphicon glyphicon-ok';
            } else {
                $resultado['estado'] = FALSE;
                $resultado['mensaje'] = "No se encontro Envio Caja Medicos #".$idEnviosCajaMedicos;
                $resultado['clase'] = 'alert alert-error';
                $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
            }
        } catch (PDOException $e) {
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "Error buscando Envio Caja Medicos: " . $e->getMessage();
            $resultado['clase'] = 'alert alert-error';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }

        return $resultado;
    }

    function obtenerEnvioDetalle($idEnviosCajaMedicos) {
        try {
            $db = Database::getConnection();
            $sql="SELECT a.Id, a.IdTipoMovimiento, a.Fecha, a.FechaActualizacion, c.Matricula, p.Apellido, p.Nombres, a.Tipo, tm.Detalle, a.DistritoCambio, c.Id AS IdColegiado, cc.TelefonoFijo, cc.TelefonoMovil, cc.CorreoElectronico
                FROM envios_caja_medicos_detalle a
                INNER JOIN colegiado c ON c.Id = a.IdColegiado
                INNER JOIN persona p ON p.Id = c.IdPersona
                INNER JOIN tipomovimiento tm ON tm.Id = a.IdTipoMovimiento
                INNER JOIN colegiadocontacto cc ON cc.IdColegiado = c.Id AND cc.IdEstado = 1
                WHERE a.IdEnviosCajaMedicos = ?
                ORDER BY a.Fecha, p.Apellido, p.Nombres";
            $stmt = $db->prepare($sql);
            $stmt->execute([$idEnviosCajaMedicos]);
            $rows = $stmt->fetchAll();

            $resultado = array();
            $datos = array();
            foreach ($rows as $row) {
                $item = array (
                    'idEnviosCajaMedicosDetalle' => $row['Id'],
                    'idTipoMovimiento' => $row['IdTipoMovimiento'],
                    'fecha' => $row['Fecha'],
                    'fechaActualizacion' => $row['FechaActualizacion'],
                    'matricula' => $row['Matricula'],
                    'apellido' => $row['Apellido'],
                    'nombre' => $row['Nombres'],
                    'tipoNovedad' => $row['Tipo'],
                    'nombreMovimiento' => $row['Detalle'],
                    'distritoCambio' => $row['DistritoCambio'],
                    'idColegiado' => $row['IdColegiado'],
                    'telefonoFijo' => $row['TelefonoFijo'],
                    'telefonoMovil' => $row['TelefonoMovil'],
                    'correoElectronico' => $row['CorreoElectronico']
                );
                array_push($datos, $item);
            }
            $resultado['estado'] = TRUE;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } catch (PDOException $e) {
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "Error buscando Detalle del enviosCajaMedicosLogic: " . $e->getMessage();
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }

        return $resultado;
    }

    function agregarEnvio($fechaDesde, $fechaHasta, $idUsuario) {
        try {
            $db = Database::getConnection();
            $db->beginTransaction();
            $resultado = array();

            $continuar = TRUE;
            $sql_tramites = "(SELECT 'ALTA' as Tipo, c.Id AS IdColegiado, c.Matricula, p.Apellido, p.Nombres, c.FechaMatriculacion AS FechaDesde, NULL AS FechaHasta, c.FechaActualizacion, 1 AS IdTipoMovimiento, tm.Detalle, NULL AS DistritoCambio, NULL AS IdColegiadoMovimiento
                    FROM colegiado c
                    INNER JOIN persona p ON p.Id = c.IdPersona
                    INNER JOIN tipomovimiento tm ON tm.Id = c.Estado
                    WHERE FechaMatriculacion BETWEEN ? AND ?
                    AND DistritoOrigen = 1)

                UNION ALL

                (SELECT 'MOVIMIENTO' as Tipo, c.Id AS IdColegiado, c.Matricula, p.Apellido, p.Nombres, cm.FechaDesde, cm.FechaHasta, c.FechaActualizacion, cm.IdMovimiento AS IdTipoMovimiento, if (cm.FechaHasta IS NOT NULL, CONCAT('Rehabilitación de ', tm.Detalle), tm.Detalle) AS Detalle, DistritoCambio, cm.Id AS IdColegiadoMovimiento
                FROM colegiadomovimiento cm
                INNER JOIN colegiado c ON c.Id = cm.IdColegiado
                INNER JOIN persona p ON p.Id = c.IdPersona
                INNER JOIN tipomovimiento tm ON tm.Id = cm.IdMovimiento
                WHERE ((cm.fechacarga BETWEEN ? AND ?)
                OR (cm.FechaCargaRehabilitacion BETWEEN ? AND ?))
                AND cm.Estado <> 'A')";
            $stmt1 = $db->prepare($sql_tramites);
            $stmt1->execute([$fechaDesde, $fechaHasta, $fechaDesde, $fechaHasta, $fechaDesde, $fechaHasta]);
            $tramites = $stmt1->fetchAll();

            $resultado = array();
            $idEnviosCajaMedicos = NULL;
            $continua = TRUE;
            foreach ($tramites as $tramiteRow) {
                if (!$continua) break;
                $tipo = $tramiteRow['Tipo'];
                $idColegiado = $tramiteRow['IdColegiado'];
                $fechaDesdeMovimiento = $tramiteRow['FechaDesde'];
                $fechaHastaMovimiento = $tramiteRow['FechaHasta'];
                $fechaActualizacion = $tramiteRow['FechaActualizacion'];
                $idTipoMovimiento = $tramiteRow['IdTipoMovimiento'];
                $detalleMovimiento = $tramiteRow['Detalle'];
                $distritoCambio = $tramiteRow['DistritoCambio'];
                $idColegiadoMovimiento = $tramiteRow['IdColegiadoMovimiento'];

                if (!isset($idEnviosCajaMedicos)) {
                    $mail = MAIL_ENVIO_CAJA;
                    $sql_insert_envio = "INSERT INTO envios_caja_medicos
                                        (FechaEnvio, FechaDesde, FechaHasta, Mail, IdUsuario)
                                        VALUES (NOW(), ?, ?, ?, ?)";
                    $stmt_sql_insert_envio = $db->prepare($sql_insert_envio);
                    $stmt_sql_insert_envio->execute([$fechaDesde, $fechaHasta, $mail, $idUsuario]);
                    $idEnviosCajaMedicos = $db->lastInsertId();
                }

                if (isset($idEnviosCajaMedicos)) {
                    $cargar = TRUE;
                    $fecha = $fechaDesdeMovimiento;
                    if ($tipo == "MOVIMIENTO") {
                        $esRehabilitacion = FALSE;
                        $fechaVer = sumarRestarSobreFecha($fechaDesde, 30, 'day', '-');
                        if (isset($fechaHastaMovimiento)) {
                            if ($fechaHastaMovimiento > $fechaVer) {
                                $fecha = $fechaHastaMovimiento;
                                $esRehabilitacion = TRUE;
                                $tipo = 'REHABILITACION';
                                $detalleMovimiento = 'REHABILITACION';
                            } else {
                                $cargar = FALSE;
                            }
                        } else {
                            if ($fecha < $fechaVer) {
                                $cargar = FALSE;
                            }
                        }
                    }

                    if ($cargar) {
                        if (!isset($fechaActualizacion)) {
                            if (isset($fechaHastaMovimiento)) {
                                $fechaActualizacion = $fechaHastaMovimiento;
                            } else {
                                $fechaActualizacion = $fechaDesdeMovimiento;
                            }
                        }
                        $sql="INSERT INTO envios_caja_medicos_detalle
                            (IdEnviosCajaMedicos, Tipo, IdTipoMovimiento, DetalleMovimiento, Fecha, FechaActualizacion, DistritoCambio, IdColegiado, IdColegiadoMovimiento)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                        $stmt = $db->prepare($sql);
                        $stmt->execute([$idEnviosCajaMedicos, $tipo, $idTipoMovimiento, $detalleMovimiento, $fecha, $fechaActualizacion, $distritoCambio, $idColegiado, $idColegiadoMovimiento]);
                    }
                }
            }
            if (!isset($idEnviosCajaMedicos)) {
                $resultado['estado'] = FALSE;
                $resultado['mensaje'] = "NO EXISTEN MOVIMIENTOS.";
                $resultado['clase'] = 'alert alert-danger';
                $resultado['icono'] = 'glyphicon glyphicon-remove';
            } else {
                $resultado['estado'] = TRUE;
                $resultado['mensaje'] = "OK";
                $resultado['clase'] = 'alert alert-success';
                $resultado['icono'] = 'glyphicon glyphicon-ok';
            }

            if ($resultado['estado']) {
                $resultado['estado'] = TRUE;
                $resultado['idEnviosCajaMedicos'] = $idEnviosCajaMedicos;
                $resultado['mensaje'] = 'SE GENERO EL LISTADO CORRECTAMENTE';
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
            $resultado['mensaje'] = "Error: " . $e->getMessage();
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-remove';
            return $resultado;
        }
    }

    function guardarEnvioArchivo($idEnviosCajaMedicos, $path, $nombreArchivo, $tipoArchivo) {
        try {
            $db = Database::getConnection();
            $db->beginTransaction();
            $resultado = array();

            if ($tipoArchivo == 'pdf') {
                $sql="UPDATE envios_caja_medicos
                    SET Path = ?, NombrePdf = ?
                    WHERE Id = ?";
            } else {
                $sql="UPDATE envios_caja_medicos
                    SET Path = ?, NombreArchivo = ?
                    WHERE Id = ?";
            }
            $stmt = $db->prepare($sql);
            $stmt->execute([$path, $nombreArchivo, $idEnviosCajaMedicos]);
            $resultado['estado'] = TRUE;
            $resultado['mensaje'] = "EL ENVIO SE REGISTRO CORRECTAMENTE";
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';

            $db->commit();
            return $resultado;
        } catch (PDOException $e) {
            $db->rollBack();
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "ERROR AL CARGAR ENVIO: " . $e->getMessage();
            $resultado['clase'] = 'alert alert-error';
            $resultado['icono'] = 'glyphicon glyphicon-remove';
            return $resultado;
        }
    }

    function envioRealizadoEnElPeriodo($periodo) {
        try {
            $db = Database::getConnection();
            $sql="SELECT COUNT(*) AS Cantidad
                FROM envios_caja_medicos a
                WHERE SUBSTR(a.FechaDesde, 1, 7) = ? AND a.Borrado = 0";
            $stmt = $db->prepare($sql);
            $stmt->execute([$periodo]);
            $row = $stmt->fetch();

            $resultado = FALSE;
            if ($row && $row['Cantidad'] > 0) {
                $resultado = TRUE;
            }
        } catch (PDOException $e) {
            $resultado = FALSE;
        }
        return $resultado;
    }

}
