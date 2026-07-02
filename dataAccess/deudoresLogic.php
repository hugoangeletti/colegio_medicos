<?php
class deudoresLogic {

    public function obtenerListadoDeudoresPorPeriodo($periodo) {
        try {
            $db = Database::getConnection();
            $sql="SELECT d.Id, d.FechaProceso, d.PeriodoLimite, d.TipoFiltro, d.CuotasAdeudadas, d.IdNotificacion
                FROM deudores d
                WHERE YEAR(d.FechaProceso) = ? AND d.Borrado = 0";
            $stmt = $db->prepare($sql);
            $stmt->execute([$periodo]);
            $rows = $stmt->fetchAll();

            $resultado = array();
            $datos = array();
            foreach ($rows as $r) {
                $row = array(
                    'id' => $r['Id'],
                    'fecha_proceso' => substr($r['FechaProceso'], 0, 10),
                    'periodo_limite' => $r['PeriodoLimite'],
                    'tipo_filtro' => $r['TipoFiltro'],
                    'tipo_filtro_detalle' => $this->tipoFiltro($r['TipoFiltro']),
                    'cuotas_adeudadas' => $r['CuotasAdeudadas'],
                    'idNotificacion' => $r['IdNotificacion']
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
            $resultado['mensaje'] = "Error buscando Listado de duedores";
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }

        return $resultado;
    }

    public function guardarLitadoDeudores($periodoLimite, $tipoFiltro, $cuotasAdeudadas) {
        $resultado = array();
        try {
            $db = Database::getConnection();
            $sql = "INSERT INTO reunionconsejo (FechaProceso, PeriodoLimite, TipoFiltro, CuotasAdeudadas)
                    VALUES (NOW(), ?, ?, ?)";
            $stmt = $db->prepare($sql);
            $stmt->execute([$periodoLimite, $tipoFiltro, $cuotasAdeudadas]);
            $resultado['estado'] = TRUE;
            $resultado['mensaje'] = 'LISTADO DE DEUDA GENERADO';
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } catch (PDOException $e) {
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "Error guardando listado -> ".$e->getMessage();
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }

        return $resultado;
    }

    public function obtenerListadoDeudoresPorId($idDeudores) {
        $resultado = array();
        try {
            $db = Database::getConnection();
            $sql="SELECT d.Id, d.FechaProceso, d.PeriodoLimite, d.TipoFiltro, d.CuotasAdeudadas
                FROM deudores d
                WHERE d.Id = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$idDeudores]);
            $row = $stmt->fetch();
            if ($row) {
                $datos = array(
                    'id' => $row['Id'],
                    'fecha_proceso' => substr($row['FechaProceso'], 0, 10),
                    'periodo_limite' => $row['PeriodoLimite'],
                    'tipo_filtro' => $row['TipoFiltro'],
                    'cuotas_adeudadas' => $row['CuotasAdeudadas']
                );
                $resultado['estado'] = true;
                $resultado['mensaje'] = "OK";
                $resultado['datos'] = $datos;
                $resultado['clase'] = 'alert alert-success';
                $resultado['icono'] = 'glyphicon glyphicon-ok';
            } else {
                $resultado['estado'] = false;
                $resultado['mensaje'] = "No se encontro listado de deudores";
                $resultado['clase'] = 'alert alert-danger';
                $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
            }
        } catch (PDOException $e) {
            $resultado['estado'] = false;
            $resultado['mensaje'] = "Error buscando reunion de consejo";
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }

        return $resultado;
    }

    public function obtenerDetalleListadoDeudores($idDeudores) {
        try {
            $db = Database::getConnection();
            $sql_colegiados = "SELECT dc.Id, c.Matricula, p.Apellido, p.Nombres, dc.CuotasAdeudadas, dc.PeriodosAdeudados, cc.CorreoElectronico, cc.TelefonoFijo, cc.TelefonoMovil
                FROM deudores_colegiado dc
                INNER JOIN colegiado c ON c.Id = dc.IdColegiado
                INNER JOIN persona p ON p.Id = c.IdPersona
                LEFT JOIN colegiadocontacto cc ON cc.IdColegiado = c.Id AND cc.IdEstado = 1
                WHERE dc.IdDeudores = ?";
            $stmt_colegiados = $db->prepare($sql_colegiados);
            $stmt_colegiados->execute([$idDeudores]);
            $rows = $stmt_colegiados->fetchAll();

            $resultado = array();
            $datos = array();
            foreach ($rows as $r) {
                $row = array(
                    'idDeudoresColegiado' => $r['Id'],
                    'matricula' => $r['Matricula'],
                    'apellidoNombre' => trim($r['Apellido']).' '.trim($r['Nombres']),
                    'cuotas_adeudadas' => $r['CuotasAdeudadas'],
                    'periodos_adeudados' => $r['PeriodosAdeudados'],
                    'correoElectronico' => $r['CorreoElectronico'],
                    'telefonoFijo' => $r['TelefonoFijo'],
                    'telefonoMovil' => $r['TelefonoMovil']
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
            $resultado['mensaje'] = "Error buscando colegiados deudores";
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }

        return $resultado;
    }

    public function generarDeudores($tipo_filtro, $periodo_hasta, $cuotas_adeudadas) {
        try {
            $db = Database::getConnection();
            $db->beginTransaction();
            switch ($tipo_filtro) {
                case '1':
                    // Todos los deudores
                    $sql="SELECT c.Id, c.Matricula, p.Apellido, p.Nombres, ad.Id,
                        (SELECT COUNT(dac.Id)
                        FROM colegiadodeudaanualcuotas dac
                        INNER JOIN colegiadodeudaanual da1 ON da1.Id = dac.IdColegiadoDeudaAnual
                        WHERE da1.IdColegiado = c.Id AND dac.FechaVencimiento < NOW()
                        AND dac.Estado = 1
                        GROUP BY da.IdColegiado) as CantCuota,
                        (SELECT COUNT(DISTINCT da2.Id)
                        FROM colegiadodeudaanualcuotas dac1
                        INNER JOIN colegiadodeudaanual da2 ON da2.Id = dac1.IdColegiadoDeudaAnual
                        WHERE da2.IdColegiado = c.Id AND dac1.FechaVencimiento < NOW()
                        AND da2.Estado = 'A' AND dac1.Estado = 1
                        GROUP BY da2.IdColegiado) AS CantPeriodos
                    FROM colegiado c
                    INNER JOIN tipomovimiento tm ON tm.Id = c.Estado
                    INNER JOIN persona p ON p.Id = c.IdPersona
                    INNER JOIN colegiadodeudaanual da ON da.IdColegiado = c.Id AND da.Estado = 'A'
                    LEFT JOIN agremiacionesdebito ad ON ad.IdColegiado = c.Id AND ad.Borrado = 0
                    WHERE tm.Estado = 'A'
                    GROUP BY c.Matricula
                    HAVING CantCuota > 0
                    ORDER BY c.Matricula";
                    break;

                case '2': // Con 2 o más períodos adeudados; Incluye Período Actual
                case '3': // Con 2 o más períodos adeudados; NO Incluye Período Actual
                case '6': // Más de 2 períodos adeudados; NO Incluye Período Actual
                    if ($tipo_filtro == '3' || $tipo_filtro == '6') {
                        $con_periodo_actual_cuotas = " AND da1.Periodo < ".PERIODO_ACTUAL;
                        $con_periodo_actual_periodos = " AND da2.Periodo < ".PERIODO_ACTUAL;
                        if ($tipo_filtro == '6') {
                            $cantidadPeriodos = 3;
                        } else {
                            $cantidadPeriodos = 2;
                        }
                    } else {
                        $con_periodo_actual_cuotas = " AND dac.FechaVencimiento < NOW()";
                        $con_periodo_actual_periodos = "AND dac1.FechaVencimiento < NOW()";
                        $cantidadPeriodos = 2;
                    }

                    $sql="SELECT c.Id, c.Matricula, p.Apellido, p.Nombres, ad.Id,
                        (SELECT COUNT(dac.Id)
                        FROM colegiadodeudaanualcuotas dac
                        INNER JOIN colegiadodeudaanual da1 ON da1.Id = dac.IdColegiadoDeudaAnual
                        WHERE da1.IdColegiado = c.Id ".$con_periodo_actual_cuotas."
                        AND dac.Estado = 1
                        GROUP BY da.IdColegiado) as CantCuota,
                        (SELECT COUNT(DISTINCT da2.Id)
                        FROM colegiadodeudaanualcuotas dac1
                        INNER JOIN colegiadodeudaanual da2 ON da2.Id = dac1.IdColegiadoDeudaAnual
                        WHERE da2.IdColegiado = c.Id ".$con_periodo_actual_periodos."
                        AND da2.Estado = 'A' AND dac1.Estado = 1
                        GROUP BY da2.IdColegiado) AS CantPeriodos
                    FROM colegiado c
                    INNER JOIN tipomovimiento tm ON tm.Id = c.Estado
                    INNER JOIN persona p ON p.Id = c.IdPersona
                    INNER JOIN colegiadodeudaanual da ON da.IdColegiado = c.Id
                    INNER JOIN colegiadodomicilioreal cdr ON cdr.idColegiado = c.Id AND cdr.idEstado = 1
                    LEFT JOIN localidad l ON l.Id = cdr.idLocalidad
                    INNER JOIN colegiadocontacto cc ON cc.IdColegiado = c.Id AND cc.IdEstado = 1
                    LEFT JOIN agremiacionesdebito ad ON ad.IdColegiado = c.Id AND ad.Borrado = 0
                    WHERE tm.Estado = 'A' AND da.Estado = 'A'
                    GROUP BY c.Matricula
                    HAVING CantCuota > 0 AND CantPeriodos >= ".$cantidadPeriodos."
                    ORDER BY c.Matricula";
                    break;

                case '4': // Con 1 período adeudado y cantidad de cuotas seleccionadas
                case '5': // Con 2 o menos períodos adeudados y cantidad de cuotas seleccionadas
                    $having_cantidad_cuotas_periodos = "";
                    if ($tipo_filtro == '4') {
                        $having_cantidad_cuotas_periodos = "HAVING CantCuota >= ".$cuotas_adeudadas." AND CantPeriodos = 1";
                    } else {
                        if ($tipo_filtro == '5') {
                            $having_cantidad_cuotas_periodos = "HAVING CantCuota >= ".$cuotas_adeudadas." AND CantPeriodos <= 2";
                        }
                    }
                    $sql="SELECT c.Id, c.Matricula, p.Apellido, p.Nombres, ad.Id,
                        (SELECT COUNT(dac.Id)
                        FROM colegiadodeudaanualcuotas dac
                        INNER JOIN colegiadodeudaanual da1 ON da1.Id = dac.IdColegiadoDeudaAnual
                        WHERE da1.IdColegiado = c.Id AND dac.FechaVencimiento < NOW()
                        AND dac.Estado = 1
                        GROUP BY da.IdColegiado) as CantCuota,
                        (SELECT COUNT(DISTINCT da2.Id)
                        FROM colegiadodeudaanualcuotas dac1
                        INNER JOIN colegiadodeudaanual da2 ON da2.Id = dac1.IdColegiadoDeudaAnual
                        WHERE da2.IdColegiado = c.Id AND dac1.FechaVencimiento < NOW()
                        AND da2.Estado = 'A' AND dac1.Estado = 1
                        GROUP BY da2.IdColegiado) AS CantPeriodos
                    FROM colegiado c
                    INNER JOIN tipomovimiento tm ON tm.Id = c.Estado
                    INNER JOIN persona p ON p.Id = c.IdPersona
                    INNER JOIN colegiadodeudaanual da ON da.IdColegiado = c.Id AND da.Estado = 'A'
                    LEFT JOIN agremiacionesdebito ad ON ad.IdColegiado = c.Id AND ad.Borrado = 0
                    WHERE tm.Estado = 'A'
                    GROUP BY c.Matricula ".$having_cantidad_cuotas_periodos." ORDER BY c.Matricula";
                    break;

                default:
                    $resultado['estado'] = FALSE;
                    $resultado['mensaje'] = "Ingreso incorrecto por tipo_filtro";
                    $resultado['clase'] = 'alert alert-danger';
                    $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
                    return $resultado;
                    break;
            }
            $stmt = $db->prepare($sql);
            $stmt->execute();
            $rows = $stmt->fetchAll();

            $resultado = array();
            //agregamos deudores
            $sql_insert_deudores = "INSERT INTO deudores (FechaProceso, PeriodoLimite, TipoFiltro, CuotasAdeudadas, IdUsuario)
                    VALUES (NOW(), ?, ?, ?, ?)";
            $stmt_deudores = $db->prepare($sql_insert_deudores);
            $stmt_deudores->execute([$periodo_hasta, $tipo_filtro, $cuotas_adeudadas, $_SESSION['user_id']]);
            $idDeudores = $db->lastInsertId();
            $resultado['idDeudores'] = $idDeudores;
            $resultado['estado'] = TRUE;
            $resultado['mensaje'] = "OK";
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';

            echo 'Cantidad de deudores: '.count($rows).'<br>';
            foreach ($rows as $r) {
                if ($resultado['estado']) {
                    $debitoAgremiaciones = (isset($r['Id']) && $r['Id'] <> "") ? 1 : 0;
                    $cuotas_adeudadas_row = (isset($r['CantCuota']) && $r['CantCuota'] != "") ? $r['CantCuota'] : 0;
                    $periodos_adeudados_row = isset($r['CantPeriodos']) ? $r['CantPeriodos'] : 0;
                    $idColegiado = $r['Id'];

                    $sql_insert_colegiado = "INSERT INTO deudores_colegiado (IdDeudores, IdColegiado, CuotasAdeudadas, PeriodosAdeudados, DebitoAgremiaciones)
                        VALUES (?, ?, ?, ?, ?)";
                    $stmt_colegiados = $db->prepare($sql_insert_colegiado);
                    $stmt_colegiados->execute([$idDeudores, $idColegiado, $cuotas_adeudadas_row, $periodos_adeudados_row, $debitoAgremiaciones]);
                }
            }

            if ($resultado['estado']) {
                $db->commit();
                return $resultado;
            } else {
                $db->rollBack();
                return $resultado;
            }
        } catch (PDOException $e) {
            $db->rollBack();
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "Error: ".$e->getMessage();
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
            return $resultado;
        }
    }

    public function borrarDeudores($idDeudores) {
        $resultado = array();
        try {
            $db = Database::getConnection();
            $sql = "UPDATE deudores
                    SET Borrado = 1, IdUsuarioBorrado = ?, FechaBorrado = NOW()
                    WHERE Id = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$_SESSION['user_id'], $idDeudores]);
            $resultado['estado'] = TRUE;
            $resultado['mensaje'] = 'HA SIDO BORRADO';
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } catch (PDOException $e) {
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "Error borrando";
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
        return $resultado;
    }

    public function tipoFiltro($tipo_filtro) {
        switch ($tipo_filtro) {
            case '1':
                $respuesta = 'Todos los deudores';
                break;
            case '2':
                $respuesta = 'Con 2 o más períodos adeudados; Incluye Período Actual ('.PERIODO_ACTUAL.')';
                break;
            case '3':
                $respuesta = 'Con 2 o más períodos adeudados; NO Incluye Período Actual ('.PERIODO_ACTUAL.')';
                break;
            case '4':
                $respuesta = 'Con 1 período adeudado y cantidad de cuotas seleccionadas';
                break;
            case '5':
                $respuesta = 'Con 2 o menos períodos adeudados y cantidad de cuotas seleccionadas';
                break;
            case '6':
                $respuesta = 'Más de 2 períodos adeudados; NO Incluye Período Actual (2025)';
                break;
            default:
                $respuesta = NULL;
                break;
        }
        return $respuesta;
    }
}
