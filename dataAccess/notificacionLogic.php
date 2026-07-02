<?php
class notificacionLogic {

    function obtenerNotificaciones($idNotificacionNota, $anio) {
        try {
            $db = Database::getConnection();
            $sql = "SELECT n.IdNotificacion, n.IdNotificacionNota, n.FechaCreacion, n.IdUsuario, n.Estado, n.TipoEnvio, n.PeriodoDesde, n.PeriodoHasta, n.Matricula, n.CuotasAdeudadas, n.FechaVencimiento, n.FechaActualizacion, nd.Tema, p.Apellido, p.Nombres, (SELECT COUNT(*) FROM notificacioncolegiado nc WHERE nc.IdNotificacion = n.IdNotificacion) AS Cantidad_Matriculas
        		FROM notificacion n
        		INNER JOIN notificacionnota nd ON nd.IdNotificacionNota = n.IdNotificacionNota
        		LEFT JOIN colegiado c ON c.Matricula = n.Matricula
        		LEFT JOIN persona p ON p.Id = c.IdPersona
        		WHERE n.IdNotificacionNota = ? AND YEAR(n.FechaCreacion) = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$idNotificacionNota, $anio]);
            $filas = $stmt->fetchAll();
            $resultado = array();
            if (count($filas) > 0) {
                $datos = array();
                foreach ($filas as $row) {
                    $datos[] = array(
                        'idNotificacion' => $row['IdNotificacion'],
                        'idNotificacionNota' => $row['IdNotificacionNota'],
                        'fechaCreacion' => $row['FechaCreacion'],
                        'idUsuario' => $row['IdUsuario'],
                        'estado' => $row['Estado'],
                        'tipoEnvio' => $row['TipoEnvio'],
                        'periodoDesde' => $row['PeriodoDesde'],
                        'periodoHasta' => $row['PeriodoHasta'],
                        'matricula' => $row['Matricula'],
                        'cuotasAdeudadas' => $row['CuotasAdeudadas'],
                        'fechaVencimiento' => $row['FechaVencimiento'],
                        'fechaActualizacion' => $row['FechaActualizacion'],
                        'temaNotificacion' => $row['Tema'],
                        'apellidoNombre' => trim($row['Apellido']).' '.trim($row['Nombres']),
                        'cantidadMatriculasConDeuda' => $row['Cantidad_Matriculas']
                    );
                }
                $resultado['estado'] = TRUE;
                $resultado['mensaje'] = "OK";
                $resultado['datos'] = $datos;
                $resultado['clase'] = 'alert alert-success';
                $resultado['icono'] = 'glyphicon glyphicon-ok';
            } else {
                $resultado['estado'] = FALSE;
                $resultado['datos'] = NULL;
                $resultado['mensaje'] = "No existen notificaciones";
                $resultado['clase'] = 'alert alert-warning';
                $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
            }
            return $resultado;
        } catch (PDOException $e) {
            return array(
                'estado' => false,
                'mensaje' => "Error buscando notificaciones",
                'clase' => 'alert alert-danger',
                'icono' => 'glyphicon glyphicon-remove'
            );
        }
    }

    function obtenerNotificacionColegiados($idNotificacion) {
        try {
            $db = Database::getConnection();
            $sql = "SELECT nc.IdNotificacionColegiado, SUM(dac.Importe) AS TotalCuotaPura, SUM(ncd.ValorActualizado), nc.TipoEnvio, c.Matricula, p.Apellido, p.Nombres
                FROM notificacioncolegiado nc
                INNER JOIN notificacioncolegiadodeuda ncd ON ncd.IdNotificacionColegiado = nc.IdNotificacionColegiado
                INNER JOIN colegiadodeudaanualcuotas dac ON dac.Id = ncd.IdColegiadoDeudaAnualCuota
                LEFT JOIN colegiado c ON c.Id = nc.IdColegiado
                LEFT JOIN persona p ON p.Id = c.IdPersona
                WHERE nc.IdNotificacion = ?
                GROUP BY nc.IdNotificacionColegiado";
            $stmt = $db->prepare($sql);
            $stmt->execute([$idNotificacion]);
            $filas = $stmt->fetchAll();
            $resultado = array();
            if (count($filas) > 0) {
                $datos = array();
                foreach ($filas as $row) {
                    $datos[] = array(
                        'idNotificacionColegiado' => $row['IdNotificacionColegiado'],
                        'totalCuotaPura' => $row['TotalCuotaPura'],
                        'totalRecargo' => $row[2],
                        'tipoEnvio' => $row['TipoEnvio'],
                        'matricula' => $row['Matricula'],
                        'apellido' => $row['Apellido'],
                        'nombre' => $row['Nombres']
                    );
                }
                $resultado['estado'] = TRUE;
                $resultado['mensaje'] = "OK";
                $resultado['datos'] = $datos;
                $resultado['clase'] = 'alert alert-success';
                $resultado['icono'] = 'glyphicon glyphicon-ok';
            } else {
                $resultado['estado'] = FALSE;
                $resultado['datos'] = NULL;
                $resultado['mensaje'] = "No existen colegiados en la notificación";
                $resultado['clase'] = 'alert alert-warning';
                $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
            }
            return $resultado;
        } catch (PDOException $e) {
            return array(
                'estado' => false,
                'mensaje' => "Error buscando colegiados en la notificación",
                'clase' => 'alert alert-danger',
                'icono' => 'glyphicon glyphicon-remove'
            );
        }
    }

    function obtenerNotificacionColegiadoPorIdNotificacion($idNotificacion) {
        try {
            $db = Database::getConnection();
            $sql = "SELECT nc.IdNotificacionColegiado, nc.TotalCuotaPura, nc.TotalRecargo, nc.TipoEnvio, c.Matricula, p.Apellido, p.Nombres
                FROM notificacioncolegiado nc
                LEFT JOIN colegiado c ON c.Id = nc.IdColegiado
                LEFT JOIN persona p ON p.Id = c.IdPersona
                WHERE nc.IdNotificacion = ?
                ORDER BY nc.IdNotificacionColegiado DESC
                LIMIT 1";
            $stmt = $db->prepare($sql);
            $stmt->execute([$idNotificacion]);
            $row = $stmt->fetch();
            $resultado = array();
            if ($row) {
                $datos = array(
                    'idNotificacionColegiado' => $row['IdNotificacionColegiado'],
                    'totalCuotaPura' => $row['TotalCuotaPura'],
                    'totalRecargo' => $row['TotalRecargo'],
                    'tipoEnvio' => $row['TipoEnvio'],
                    'matricula' => $row['Matricula'],
                    'apellido' => $row['Apellido'],
                    'nombre' => $row['Nombres']
                );
                $resultado['estado'] = TRUE;
                $resultado['mensaje'] = "OK";
                $resultado['datos'] = $datos;
                $resultado['clase'] = 'alert alert-success';
                $resultado['icono'] = 'glyphicon glyphicon-ok';
            } else {
                $resultado['estado'] = FALSE;
                $resultado['datos'] = NULL;
                $resultado['mensaje'] = "No existen colegiados en la notificación";
                $resultado['clase'] = 'alert alert-warning';
                $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
            }
            return $resultado;
        } catch (PDOException $e) {
            return array(
                'estado' => false,
                'mensaje' => "Error buscando colegiados en la notificación",
                'clase' => 'alert alert-danger',
                'icono' => 'glyphicon glyphicon-remove'
            );
        }
    }

    function obtenerNotificacionColegiadoPorId($idNotificacionColegiado) {
        try {
            $db = Database::getConnection();
            $sql = "SELECT nc.IdNotificacion, nc.TotalCuotaPura, nc.TotalRecargo, nc.TipoEnvio, c.Matricula, p.Apellido, p.Nombres
                FROM notificacioncolegiado nc
                LEFT JOIN colegiado c ON c.Id = nc.IdColegiado
                LEFT JOIN persona p ON p.Id = c.IdPersona
                WHERE nc.IdNotificacionColegiado = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$idNotificacionColegiado]);
            $row = $stmt->fetch();
            $resultado = array();
            if ($row) {
                $datos = array(
                    'idNotificacion' => $row['IdNotificacion'],
                    'totalCuotaPura' => $row['TotalCuotaPura'],
                    'totalRecargo' => $row['TotalRecargo'],
                    'tipoEnvio' => $row['TipoEnvio'],
                    'matricula' => $row['Matricula'],
                    'apellido' => $row['Apellido'],
                    'nombre' => $row['Nombres']
                );
                $resultado['estado'] = TRUE;
                $resultado['mensaje'] = "OK";
                $resultado['datos'] = $datos;
                $resultado['clase'] = 'alert alert-success';
                $resultado['icono'] = 'glyphicon glyphicon-ok';
            } else {
                $resultado['estado'] = FALSE;
                $resultado['datos'] = NULL;
                $resultado['mensaje'] = "No existen colegiados en la notificación";
                $resultado['clase'] = 'alert alert-warning';
                $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
            }
            return $resultado;
        } catch (PDOException $e) {
            return array(
                'estado' => false,
                'mensaje' => "Error buscando colegiados en la notificación",
                'clase' => 'alert alert-danger',
                'icono' => 'glyphicon glyphicon-remove'
            );
        }
    }

    function obtenerNotificacionColegiadoDetallePorId($idNotificacionColegiado) {
        try {
            $db = Database::getConnection();
            $sql = "SELECT ncd.IdNotificacionColegiadoDeuda, ncd.IdColegiadoDeudaAnualCuota, ncd.IdPlanPagosCuota, ncd.ValorActualizado, da.Periodo, dac.Cuota, dac.Importe, dac.FechaVencimiento, ppc.Cuota, ppc.Importe, ppc.Vencimiento
                FROM notificacioncolegiadodeuda ncd
                LEFT JOIN colegiadodeudaanualcuotas dac ON dac.Id = ncd.IdColegiadoDeudaAnualCuota
                LEFT JOIN colegiadodeudaanual da ON da.Id = dac.IdColegiadoDeudaAnual
                LEFT JOIN planpagoscuotas ppc ON ppc.Id = ncd.IdPlanPagosCuota
                WHERE ncd.IdNotificacionColegiado = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$idNotificacionColegiado]);
            $filas = $stmt->fetchAll();
            $resultado = array();
            if (count($filas) > 0) {
                $datos = array();
                foreach ($filas as $row) {
                    $datos[] = array(
                        'idNotificacionColegiadoDeuda' => $row['IdNotificacionColegiadoDeuda'],
                        'idColegiadoDeudaAnualCuota' => $row['IdColegiadoDeudaAnualCuota'],
                        'idPlanPagosCuota' => $row['IdPlanPagosCuota'],
                        'valorActualizado' => $row['ValorActualizado'],
                        'periodo' => $row['Periodo'],
                        'cuota' => $row['Cuota'],
                        'importe' => $row['Importe'],
                        'fechaVencimiento' => $row['FechaVencimiento'],
                        'cuotaPP' => $row[8],
                        'importePP' => $row[9],
                        'fechaVencimientoPP' => $row['Vencimiento']
                    );
                }
                $resultado['estado'] = TRUE;
                $resultado['mensaje'] = "OK";
                $resultado['datos'] = $datos;
                $resultado['clase'] = 'alert alert-success';
                $resultado['icono'] = 'glyphicon glyphicon-ok';
            } else {
                $resultado['estado'] = FALSE;
                $resultado['datos'] = NULL;
                $resultado['mensaje'] = "No existen notificaciones";
                $resultado['clase'] = 'alert alert-warning';
                $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
            }
            return $resultado;
        } catch (PDOException $e) {
            return array(
                'estado' => false,
                'mensaje' => "Error buscando notificaciones",
                'clase' => 'alert alert-danger',
                'icono' => 'glyphicon glyphicon-remove'
            );
        }
    }

    function agregarNotificacion($idNotificacionNota, $filtroDeudores, $tipoEnvio, $fechaVencimiento, $periodoDesde, $periodoHasta) {
        $resultado = array();
        try {
            $db = Database::getConnection();
            $sql = "INSERT INTO notificacion (IdNotificacionNota, FechaCreacion, IdUsuario, Estado, FiltroDeudores, TipoEnvio, PeriodoDesde, PeriodoHasta, FechaVencimiento)
                VALUES(?, time(NOW()), ?, ?, 'A')";
            $stmt = $db->prepare($sql);
            $stmt->execute([$fechaVencimiento, $saldoInicial, $_SESSION['user_id']]);
            $idNotificacion = $db->lastInsertId();
            $resultado['estado'] = TRUE;
            $resultado['idNotificacion'] = $idNotificacion;
            $resultado['mensaje'] = 'OK';
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } catch (PDOException $e) {
            $resultado['estado'] = FALSE;
            $resultado['idCajaDiaria'] = NULL;
            $resultado['mensaje'] = 'ERROR al abrir cajadiaria';
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-remove';
        }
        return $resultado;
    }

    function generarNotificacionDeuda($idNotificacionNota, $estado, $periodoHasta, $matricula, $cuotasAdeudadas, $fechaVencimiento, $idDeudores) {
        try {
            $db = Database::getConnection();
            $resultado = array();

            $sql = "INSERT INTO notificacion (IdNotificacionNota, FechaCreacion, IdUsuario, Estado, PeriodoHasta, Matricula, CuotasAdeudadas, FechaVencimiento)
                VALUE (?, DATE(NOW()), ?, ?, ?, ?, ?, ?)";
            $stmt = $db->prepare($sql);
            $stmt->execute([$idNotificacionNota, $_SESSION['user_id'], $estado, $periodoHasta, $matricula, $cuotasAdeudadas, $fechaVencimiento]);
            $idNotificacion = $db->lastInsertId();
            $resultado['estado'] = TRUE;
            $resultado['idNotificacion'] = $idNotificacion;
            $resultado['mensaje'] = 'OK';
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';

            if (isset($idDeudores) && $idDeudores <> "") {
                $sql = "SELECT dc.IdColegiado, dc.CuotasAdeudadas,
                    if ((SELECT count(cc.IdColegiadoContacto)
                        FROM colegiadocontacto cc
                        WHERE cc.IdColegiado = c.Id AND cc.IdEstado = 1
                        AND cc.CorreoElectronico <> 'NR' AND substr(cc.CorreoElectronico, 1, 3) <> 'nr@') = 0, 'C', 'E') AS TipoEnvio
                    FROM deudores_colegiado dc
                    INNER JOIN colegiado c ON c.Id = dc.IdColegiado AND c.Estado IN(1, 5, 10)
                    WHERE dc.IdDeudores = ?";
                $stmt = $db->prepare($sql);
                $stmt->execute([$idDeudores]);
            } else {
                $sql = "SELECT cda.IdColegiado, COUNT(dac.Id) AS CuotasAdeudadas, SUM(dac.Importe) AS TotalCuotaPura,
                    (SELECT SUM(ROUND(((SELECT tv.Valor FROM tablavalores tv WHERE tv.IdValor = 20 ORDER BY tv.Fecha DESC LIMIT 1) * ((TIMESTAMPDIFF(MONTH, dac1.FechaVencimiento, ?))) * dac1.Importe / 100))) AS ImporteActualizado
                            FROM colegiadodeudaanualcuotas dac1
                            INNER JOIN colegiadodeudaanual cda1 ON cda1.Id = dac1.IdColegiadoDeudaAnual
                            WHERE cda1.IdColegiado = c.Id AND cda1.Periodo <= ?
                            AND dac1.Estado = 1
                            AND dac1.FechaVencimiento <= ?) AS TotalRecargo,
                    if ((SELECT count(cc.IdColegiadoContacto)
                        FROM colegiadocontacto cc
                        LEFT JOIN colegiadomailrechazado cmr ON cmr.IdColegiado = cc.IdColegiado
                        WHERE cc.IdColegiado = c.Id AND cc.IdEstado = 1
                        AND cc.CorreoElectronico <> 'NR' AND substr(cc.CorreoElectronico, 1, 3) <> 'nr@'
                        AND cmr.Id IS NULL) = 0, 'C', 'E') AS TipoEnvio

                    FROM colegiadodeudaanualcuotas dac
                    INNER JOIN colegiadodeudaanual cda ON cda.Id = dac.IdColegiadoDeudaAnual
                    INNER JOIN colegiado c ON c.Id = cda.IdColegiado AND c.Estado IN(1, 5, 10)
                    WHERE cda.Periodo <= ?
                    AND dac.Estado = 1 AND dac.FechaVencimiento <= ?
                    GROUP BY cda.IdColegiado
                    HAVING CuotasAdeudadas > ?";
                $stmt = $db->prepare($sql);
                $stmt->execute([$fechaVencimiento, $periodoHasta, $fechaVencimiento, $periodoHasta, $fechaVencimiento, $cuotasAdeudadas]);
            }

            $deudores = $stmt->fetchAll();
            if (count($deudores) > 0) {
                foreach ($deudores as $deudorRow) {
                    $idColegiado = $deudorRow['IdColegiado'];
                    $tipoEnvio = $deudorRow['TipoEnvio'];

                    $sql1 = "INSERT INTO notificacioncolegiado (IdNotificacion, Estado, IdColegiado, TipoEnvio)
                        VALUE (?, 'A', ?, ?)";
                    $stmt1 = $db->prepare($sql1);
                    $stmt1->execute([$idNotificacion, $idColegiado, $tipoEnvio]);
                    $idNotificacionColegiado = $db->lastInsertId();

                    $sql2 = "SELECT cda.Periodo, dac.Id , dac.Cuota, dac.Importe, dac.FechaVencimiento, ((TIMESTAMPDIFF(MONTH, dac.FechaVencimiento, DATE(NOW()))) + 1) AS Meses, ROUND(((SELECT tv.Valor FROM tablavalores tv WHERE tv.IdValor = 20 ORDER BY tv.Fecha DESC LIMIT 1) * ((TIMESTAMPDIFF(MONTH, dac.FechaVencimiento, ?))) * dac.Importe / 100)) AS ImporteActualizado
                        FROM colegiadodeudaanualcuotas dac
                        INNER JOIN colegiadodeudaanual cda ON cda.Id = dac.IdColegiadoDeudaAnual
                        WHERE cda.IdColegiado = ? AND cda.Periodo <= ?
                        AND dac.Estado = 1
                        AND dac.FechaVencimiento <= ?";
                    $stmt2 = $db->prepare($sql2);
                    $stmt2->execute([$fechaVencimiento, $idColegiado, $periodoHasta, $fechaVencimiento]);
                    $cuotas = $stmt2->fetchAll();

                    if (count($cuotas) > 0) {
                        $idPlanPagosCuota = NULL;
                        foreach ($cuotas as $cuotaRow) {
                            $valorActualizado = $cuotaRow['Importe'] + $cuotaRow['ImporteActualizado'];
                            $sql3 = "INSERT INTO notificacioncolegiadodeuda (IdNotificacionColegiado, IdColegiadoDeudaAnualCuota, IdPlanPagosCuota, ValorActualizado)
                                VALUE (?, ?, ?, ?)";
                            $stmt3 = $db->prepare($sql3);
                            $stmt3->execute([$idNotificacionColegiado, $cuotaRow['Id'], $idPlanPagosCuota, $valorActualizado]);
                        }
                        $resultado['clase'] = 'alert alert-success';
                        $resultado['icono'] = 'glyphicon glyphicon-ok';
                    } else {
                        echo 'NO HAY DEUDA->'.$matricula.'<br>';
                    }
                }

                if (isset($idDeudores)) {
                    $sql = "UPDATE deudores SET IdNotificacion = ? WHERE Id = ?";
                    $stmt = $db->prepare($sql);
                    $stmt->execute([$idNotificacion, $idDeudores]);
                }
            } else {
                $resultado['estado'] = false;
                $resultado['mensaje'] = "No existen colegiados con deuda";
                $resultado['clase'] = 'alert alert-danger';
                $resultado['icono'] = 'glyphicon glyphicon-remove';
            }

            return $resultado;
        } catch (PDOException $e) {
            return array(
                'estado' => false,
                'mensaje' => "Error: " . $e->getMessage(),
                'clase' => 'alert alert-danger',
                'icono' => 'glyphicon glyphicon-remove'
            );
        }
    }

    function actualizarNotificacionDeuda($idNotificacion, $estado) {
        $resultado = array();
        try {
            $db = Database::getConnection();
            $sql = "UPDATE notificacion
                SET Estado = ?, FechaActualizacion = NOW(), IdUsuario = ?
                WHERE IdNotificacion = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$estado, $_SESSION['user_id'], $idNotificacion]);
            $resultado['estado'] = TRUE;
            $resultado['mensaje'] = 'OK';
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } catch (PDOException $e) {
            $resultado['estado'] = FALSE;
            $resultado['idCajaDiaria'] = NULL;
            $resultado['mensaje'] = 'ERROR al actualizar notificación';
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-remove';
        }
        return $resultado;
    }
}
