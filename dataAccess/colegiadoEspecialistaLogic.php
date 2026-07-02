<?php
class colegiadoEspecialistaLogic {

    public function obtenerCantidadEspecialidadesPorIdColegiado($idColegiado) {
    try {
        $db = Database::getConnection();
        $sql = "SELECT e.Especialidad, e.CodigoRes62707, e.Id, COUNT(ce.Id) AS Cantidad
            FROM colegiadoespecialista ce
            INNER JOIN especialidad e ON e.Id = ce.Especialidad
            WHERE ce.IdColegiado = ? AND ce.Estado = 'A'
            GROUP BY e.Especialidad, e.CodigoRes62707, e.Id";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idColegiado]);
        $datos_raw = $stmt->fetchAll();
        $resultado = array();
        if (count($datos_raw) > 0) {
            $datos = array();
            foreach ($datos_raw as $r) {
                $row = array(
                    'nombreEspecialidad' => $r['Especialidad'],
                    'codigoEspecialidad' => $r['CodigoRes62707'],
                    'idEspecialidad' => $r['Id'],
                    'cantidad' => $r['Cantidad']
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
            $resultado['datos'] = NULL;
            $resultado['mensaje'] = "El colegiado no tiene especialidades.";
            $resultado['clase'] = 'alert alert-warning';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando especialidades del colegiado";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }

    return $resultado;
}

    public function obtenerEspecialidadPorIdColegiadoIdEspecialidad($idColegiado, $idEspecialidad) {
    try {
        $db = Database::getConnection();
        $sql="SELECT ce.Id, ce.FechaCarga, ce.FechaEspecialista, ce.FechaRecertificacion, ce.Colegio, ce.FechaVencimiento,
            te.Nombre, te.Codigo, ce.IncisoArticulo8
        FROM colegiadoespecialista ce
        LEFT JOIN tipoespecialista te ON te.IdTipoEspecialista = ce.IdTipoEspecialista
        WHERE ce.IdColegiado = ? AND ce.Especialidad = ? AND ce.Estado = 'A'
        ORDER BY ce.FechaEspecialista DESC
        LIMIT 1";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idColegiado, $idEspecialidad]);
        $row = $stmt->fetch();
        $resultado = array();
        if ($row) {
            $codigoEspecialista = $row['Codigo'];
            $distritoOrigen = $row['Colegio'];
            if ($codigoEspecialista == "N") {
                $distritoOrigen = "NACIÓN";
            }
            $datos = array(
                'idColegiadoEspecialista' => $row['Id'],
                'fechaCarga' => $row['FechaCarga'],
                'fechaEspecialista' => $row['FechaEspecialista'],
                'fechaRecertificacion' => $row['FechaRecertificacion'],
                'distritoOrigen' => $distritoOrigen,
                'fechaVencimiento' => $row['FechaVencimiento'],
                'tipoespecialista' => $row['Nombre'],
                'codigoEspecialista' => $codigoEspecialista,
                'especialistaInciso' => $row['IncisoArticulo8'],
                'origen' => $row['Nombre']
            );
            $resultado['estado'] = TRUE;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = FALSE;
            $resultado['datos'] = NULL;
            $resultado['mensaje'] = "El colegiado no tiene especialidades.";
            $resultado['clase'] = 'alert alert-warning';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando especialidades del colegiado";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }

    return $resultado;
}

    public function obtenerEspecialidadesPorIdColegiado($idColegiado, $orden = NULL) {
    try {
        $db = Database::getConnection();
        if (!isset($orden)) {
            $orden = 'DESC';
        }

        $sql="SELECT ce.Id, ce.FechaCarga, ce.FechaEspecialista, ce.FechaRecertificacion, ce.Colegio, ce.FechaVencimiento, te.Nombre, e.Especialidad, e.CodigoRes62707, ce.Estado, e.Id AS IdEspecialidad, te.Codigo, ce.IncisoArticulo8, ce.FechaEspecialistaOrigen, ce.IdColegiadoEspecialistaActualizacion, (SELECT ce1.Id FROM colegiadoespecialista ce1 WHERE ce1.IdColegiadoEspecialistaActualizacion = ce.Id) AS IdColegiadoEspecialistaOrigen,
            (SELECT GROUP_CONCAT(cet.Fecha, '&', (IF (cet.IdResolucionDetalle IS NULL, 'NULL', cet.IdResolucionDetalle)), '&', te.Codigo)
            FROM colegiadoespecialistatipo cet
            LEFT JOIN tipoespecialista te ON te.IdTipoEspecialista = cet.IdTipoEspecialista
            WHERE cet.IdColegiadoEspecialista = ce.Id AND cet.Borrado = 0) AS JerarquizadoConsultor
        FROM colegiadoespecialista ce
        INNER JOIN especialidad e ON e.Id = ce.Especialidad
        LEFT JOIN tipoespecialista te ON te.IdTipoEspecialista = ce.IdTipoEspecialista
        WHERE ce.IdColegiado = ? AND ce.Estado = 'A'
        ORDER BY ce.Especialidad, ce.FechaEspecialista ".$orden;
        $stmt = $db->prepare($sql);
        $stmt->execute([$idColegiado]);
        $datos_raw = $stmt->fetchAll();
        $resultado = array();
        if (count($datos_raw) > 0) {
            $datos = array();
            foreach ($datos_raw as $r) {
                $codigoEspecialista = $r['Codigo'];
                $distritoOrigen = $r['Colegio'];
                $tipoespecialista = $r['Nombre'];
                $incisoArticulo8 = $r['IncisoArticulo8'];
                $jerarquizadoConsultor = $r['JerarquizadoConsultor'];
                if ($codigoEspecialista == "N") {
                    $distritoOrigen = "NACIÓN";
                }
                if (isset($incisoArticulo8) && $incisoArticulo8 <> "") {
                    $tipoespecialista = trim($tipoespecialista.' Inc.'.$incisoArticulo8);
                }

                $datoJerarquizadoConsultor = array();
                if (isset($jerarquizadoConsultor) && $jerarquizadoConsultor <> "") {
                    $array_JerarquizadoConsultor = explode(",", $jerarquizadoConsultor);
                    $array_Jerarquizado = array();
                    $array_Consultor = array();
                    if (isset($array_JerarquizadoConsultor[0])) {
                        $array_Jerarquizado = explode('&', $array_JerarquizadoConsultor[0]);
                        array_push($datoJerarquizadoConsultor, $array_Jerarquizado);
                    }
                    if (isset($array_JerarquizadoConsultor[1])) {
                        $array_Consultor = explode('&', $array_JerarquizadoConsultor[1]);
                        array_push($datoJerarquizadoConsultor, $array_Consultor);
                    }
                }

                $row = array(
                    'idColegiadoEspecialista' => $r['Id'],
                    'fechaCarga' => $r['FechaCarga'],
                    'fechaEspecialista' => $r['FechaEspecialista'],
                    'fechaRecertificacion' => $r['FechaRecertificacion'],
                    'distritoOrigen' => $distritoOrigen,
                    'fechaVencimiento' => $r['FechaVencimiento'],
                    'tipoespecialista' => $tipoespecialista,
                    'nombreEspecialidad' => $r['Especialidad'],
                    'codigoEspecialidad' => $r['CodigoRes62707'],
                    'estado' => $r['Estado'],
                    'idEspecialidad' => $r['IdEspecialidad'],
                    'codigoEspecialista' => $codigoEspecialista,
                    'especialistaInciso' => $incisoArticulo8,
                    'origen' => $tipoespecialista,
                    'fechaEspecialistaOrigen' => $r['FechaEspecialistaOrigen'],
                    'idColegiadoEspecialistaActualizacion' => $r['IdColegiadoEspecialistaActualizacion'],
                    'idColegiadoEspecialistaOrigen' => $r['IdColegiadoEspecialistaOrigen'],
                    'jerarquizadoConsultor' => $datoJerarquizadoConsultor
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
            $resultado['datos'] = NULL;
            $resultado['mensaje'] = "El colegiado no tiene especialidades.";
            $resultado['clase'] = 'alert alert-warning';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando especialidades del colegiado";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }

    return $resultado;
}

    public function obtenerEspecialidadesPorIdColegiadoVigentes($idColegiado) {
    try {
        $db = Database::getConnection();
        $sql="SELECT ce.Id, ce.FechaEspecialista, e.Especialidad
        FROM colegiadoespecialista ce
        INNER JOIN especialidad e ON e.Id = ce.Especialidad
        WHERE ce.IdColegiado = ? AND ce.Estado = 'A'
        ORDER BY ce.FechaEspecialista DESC";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idColegiado]);
        $datos_raw = $stmt->fetchAll();
        $resultado = array();
        if (count($datos_raw) > 0) {
            $datos = array();
            foreach ($datos_raw as $r) {
                $row = array(
                    'idColegiadoEspecialista' => $r['Id'],
                    'fechaEspecialista' => $r['FechaEspecialista'],
                    'nombreEspecialidad' => $r['Especialidad']
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
            $resultado['datos'] = NULL;
            $resultado['mensaje'] = "El colegiado no tiene especialidades.";
            $resultado['clase'] = 'alert alert-warning';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando especialidades del colegiado";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }

    return $resultado;
}

    public function especialidadesConCaducidad($idColegiado){
    try {
        $db = Database::getConnection();
        $sql="SELECT ce.FechaEspecialista, ce.FechaRecertificacion, ce.Colegio, e.Especialidad, ce.FechaVencimiento, ce.Especialidad as IdEspecilidad
            FROM colegiadoespecialista ce
            INNER JOIN especialidad e ON e.Id = ce.Especialidad
            LEFT JOIN colegiadoespecialistatipo cet ON cet.IdColegiadoEspecialista = ce.Id AND cet.TipoEspecialista = 'C' and cet.Borrado = 0
            WHERE ce.IdColegiado = ?
            AND ce.Estado = 'A' AND cet.Id IS NULL AND ce.FechaVencimiento IS NOT NULL";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idColegiado]);
        $datos_raw = $stmt->fetchAll();
        $resultado = array();
        if (count($datos_raw) > 0) {
            $datos = array();
            foreach ($datos_raw as $r) {
                $caducidad = "";
                $resBaja = $this->verBajaEspecialista($idColegiado, $r['IdEspecilidad'], $r['FechaEspecialista']);
                if (!$resBaja['estado']) {
                    $fechaCaducidad = $r['FechaVencimiento'];
                    if (isset($fechaCaducidad) && $fechaCaducidad <> "0000-00-00" && substr($fechaCaducidad, 0, 4)<=$_SESSION['periodoActual']+1) {
                        if (date('Y-m-d') > $fechaCaducidad) {
                            $caducidad = 'Venció el ';
                        } else {
                            $caducidad = 'Caduca el ';
                        }
                        $caducidad .= cambiarFechaFormatoParaMostrar($fechaCaducidad);
                        $row = array(
                            'especialidad' => $r['Especialidad'],
                            'caducidad' => $caducidad
                        );
                        array_push($datos, $row);
                    }
                }
            }
            $resultado['estado'] = true;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "No hay estados";
            $resultado['clase'] = 'alert alert-warning';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "Error buscando estados";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function verBajaEspecialista($idColegiado, $idEspecialidad, $fechaEspecialista){
    try {
        $db = Database::getConnection();
        $sql="select Numero, Fecha
            from resolucion
            inner join resoluciondetalle on(resolucion.id = resoluciondetalle.IdResolucion)
            where resoluciondetalle.IdColegiado = ? and resoluciondetalle.Especialidad = ?
            and resolucion.Fecha > ? and resolucion.TipoResolucion = 3";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idColegiado, $idEspecialidad, $fechaEspecialista]);
        $row = $stmt->fetch();
        $resultado = array();
        if ($row) {
            $datos = array(
                'numero' => $row['Numero'],
                'fecha' => $row['Fecha']
            );
            $resultado['datos'] = $datos;
            $resultado['estado'] = TRUE;
        } else {
            $resultado['estado'] = FALSE;
        }
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
    }

    return $resultado;
}

    public function obtenerFechaJerarquizadoConsultor($idColegiadoEspecialista, $tipo){
    try {
        $db = Database::getConnection();
        $sql="SELECT cet.Fecha
            FROM colegiadoespecialistatipo cet
            INNER JOIN tipoespecialista te ON te.IdTipoEspecialista = cet.IdTipoEspecialista
            WHERE cet.IdColegiadoEspecialista = ?
            AND te.Codigo = ?
            AND cet.Borrado = 0";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idColegiadoEspecialista, $tipo]);
        $row = $stmt->fetch();
        $resultado = array();
        if ($row) {
            $resultado['estado'] = TRUE;
            $resultado['fecha'] = $row['Fecha'];
        } else {
            $resultado['estado'] = FALSE;
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
    }

    return $resultado;
}

    public function obtenerEspecialistaTipoPorIdColegiadoEspecialista($idColegiadoEspecialista, $idTipoEspecialista) {
    try {
        $db = Database::getConnection();
        $sql="SELECT cet.Id, cet.Fecha, cet.Colegio, cet.FechaRecertificacion, r.Numero, r.Fecha AS FechaResolucion
            FROM colegiadoespecialistatipo cet
            LEFT JOIN resoluciondetalle rd ON rd.Id = cet.IdResolucionDetalle
            LEFT JOIN resolucion r ON r.Id = rd.IdResolucion
            WHERE cet.IdColegiadoEspecialista = ?
            AND cet.IdTipoEspecialista = ?
            AND cet.Borrado = 0
            ORDER BY cet.Id DESC
            LIMIT 1";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idColegiadoEspecialista, $idTipoEspecialista]);
        $row = $stmt->fetch();
        $resultado = array();
        if ($row) {
            $datos = array(
                    'idColegiadoEspecialistaTipo' => $row['Id'],
                    'fecha' => $row['Fecha'],
                    'distritoOtorgante' => $row['Colegio'],
                    'fechaRecertificacion' => $row['FechaRecertificacion'],
                    'numeroResolucion' => $row['Numero'],
                    'fechaResolucion' => $row['FechaResolucion']
                );
            $resultado['estado'] = TRUE;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "El colegiado no registra datos";
            $resultado['clase'] = 'alert alert-warning';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "Error buscando estados";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }

    return $resultado;
}

    public function obtenerColegiadoEspecialistaPorId($idColegiadoEspecialista) {
    try {
        $db = Database::getConnection();
        $sql="SELECT ce.Id, ce.FechaCarga, ce.FechaEspecialista, ce.FechaRecertificacion, ce.Colegio, ce.FechaVencimiento, te.Nombre, e.Especialidad, e.CodigoRes62707, ce.Estado, e.Id AS IdEspecialidad, e.IdTipoEspecialidad, te.Codigo, ce.IdResolucionDetalle, ce.IncisoArticulo8, ce.IdColegiado, ce.IdTipoEspecialista
            FROM colegiadoespecialista ce
            INNER JOIN especialidad e ON(e.Id = ce.Especialidad)
            LEFT JOIN tipoespecialista te ON(te.IdTipoEspecialista = ce.IdTipoEspecialista)
            WHERE ce.Id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idColegiadoEspecialista]);
        $row = $stmt->fetch();
        $resultado = array();
        if ($row) {
            $idTipoEspecialidad = $row['IdTipoEspecialidad'];
            if ($idTipoEspecialidad < 3) {
                $tipoEspecialidad = 'Especialista';
            } else {
                $tipoEspecialidad = 'Calificación Agregada';
            }
            $codigoTipoEspecialista = $row['Codigo'];
            $distritoOrigen = $row['Colegio'];
            if ($codigoTipoEspecialista == "N") {
                $distritoOrigen = "NACIÓN";
            }
            $datos = array(
                'idColegiado' => $row['IdColegiado'],
                'fechaCarga' => $row['FechaCarga'],
                'fechaEspecialista' => $row['FechaEspecialista'],
                'fechaRecertificacion' => $row['FechaRecertificacion'],
                'distritoOrigen' => $distritoOrigen,
                'fechaVencimiento' => $row['FechaVencimiento'],
                'tipoespecialista' => $row['Nombre'],
                'nombreEspecialidad' => $row['Especialidad'],
                'codigoEspecialidad' => $row['CodigoRes62707'],
                'estado' => $row['Estado'],
                'idEspecialidad' => $row['IdEspecialidad'],
                'tipoEspecialidad' => $tipoEspecialidad,
                'idResolucionDetalle' => $row['IdResolucionDetalle'],
                'incisoArticulo8' => $row['IncisoArticulo8'],
                'idTipoEspecialista' => $row['IdTipoEspecialista']
            );
            $resultado['estado'] = TRUE;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = FALSE;
            $resultado['datos'] = NULL;
            $resultado['mensaje'] = "El colegiado no tiene especialidades.";
            $resultado['clase'] = 'alert alert-warning';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando especialidades del colegiado";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }

    return $resultado;
}

    public function obtenerEspecialistaPorIdResolucionDetalle($idResolucionDetalle, $tipo) {
    try {
        $db = Database::getConnection();
        if ($tipo == 'R') {
            $sql = "SELECT ce.FechaVencimiento, ce.FechaEspecialista, ce.Id
                    FROM colegiadoespecialista ce
                    INNER JOIN colegiadoespecialistarecertificaciones cer ON cer.IdColegiadoEspecialista = ce.Id
                    WHERE cer.IdResolucionDetalle = ?";
        } else {
            if ($tipo == 'J' || $tipo == 'C') {
                $sql = "SELECT ce.FechaVencimiento, ce.FechaEspecialista, ce.Id
                        FROM colegiadoespecialista ce
                        INNER JOIN colegiadoespecialistatipo cet ON cet.IdColegiadoEspecialista = ce.Id
                        WHERE cet.IdResolucionDetalle = ?";
            } else {
                $sql = "SELECT ce.FechaVencimiento, ce.FechaEspecialista, ce.Id
                        FROM colegiadoespecialista ce
                        WHERE ce.IdResolucionDetalle = ?";
            }
        }
        $stmt = $db->prepare($sql);
        $stmt->execute([$idResolucionDetalle]);
        $row = $stmt->fetch();
        $resultado = array();
        if ($row) {
            $datos = array(
                'fechaEspecialista' => $row['FechaEspecialista'],
                'fechaVencimiento' => $row['FechaVencimiento'],
                'idColegiadoEspecialista' => $row['Id']
            );
            $resultado['estado'] = TRUE;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = FALSE;
            $resultado['datos'] = NULL;
            $resultado['mensaje'] = "El colegiado no tiene especialidades.";
            $resultado['clase'] = 'alert alert-warning';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando especialidades del colegiado";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }

    return $resultado;
}

    public function obtenerEspecialidadBase($idColegiado, $idEspecialidad) {
    try {
        $db = Database::getConnection();
        $sql = "SELECT e.Especialidad
                FROM especialidad_calificacion_agregada eca
                INNER JOIN especialidad e ON e.Id = eca.IdEspecialidadPadre
                WHERE eca.IdCalificacionAgregada = ?
                AND eca.IdEspecialidadPadre IN (SELECT ce.Especialidad
                        FROM colegiadoespecialista ce
                        INNER JOIN especialidad e1 ON e1.Id = ce.Especialidad
                        WHERE ce.IdColegiado = ? AND e1.IdTipoEspecialidad <> 3)";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idEspecialidad, $idColegiado]);
        $row = $stmt->fetch();
        $resultado = array();
        if ($row) {
            $resultado['estado'] = TRUE;
            $resultado['mensaje'] = "OK";
            $resultado['especialidadBaseDetalle'] = $row['Especialidad'];
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = FALSE;
            $resultado['datos'] = NULL;
            $resultado['mensaje'] = "El colegiado no tiene especialidades.";
            $resultado['clase'] = 'alert alert-warning';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando especialidades del colegiado";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }

    return $resultado;
}

    public function obtenerEspecialistasConVencimientoParaNotificar($anio, $anioDesde, $rango) {
    try {
        $db = Database::getConnection();
        $sql="SELECT colegiadoespecialista.Id, especialidad.Especialidad, colegiadoespecialista.IdColegiado,
                colegiado.Matricula, persona.Apellido, persona.Nombres, persona.Sexo, colegiadocontacto.CorreoElectronico,
                colegiadoespecialista.FechaVencimiento
            FROM colegiadoespecialista
            INNER JOIN colegiado ON(colegiado.Id = colegiadoespecialista.IdColegiado)
            INNER JOIN persona ON(persona.Id = colegiado.IdPersona)
            INNER JOIN colegiadocontacto ON(colegiadocontacto.IdColegiado = colegiado.Id
                AND colegiadocontacto.IdEstado = 1
                AND colegiadocontacto.CorreoElectronico is not null
                AND UPPER(colegiadocontacto.CorreoElectronico) <> 'NR'
                AND colegiadocontacto.CorreoElectronico <> '')
            INNER JOIN tipomovimiento ON(tipomovimiento.Id = colegiado.Estado)
            INNER JOIN especialidad ON(especialidad.Id = colegiadoespecialista.Especialidad)
            LEFT JOIN enviomaildiariocolegiado ON(enviomaildiariocolegiado.IdColegiado = colegiadoespecialista.IdColegiado
                AND enviomaildiariocolegiado.IdReferencia = colegiadoespecialista.Id)
            WHERE tipomovimiento.Estado = 'A'
                AND (year(colegiadoespecialista.FechaVencimiento) = ?
                OR (year(colegiadoespecialista.FechaVencimiento) >= ?
                AND year(colegiadoespecialista.FechaVencimiento) < ?))
            ORDER BY colegiado.Matricula
            LIMIT ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$anio, $anioDesde, $anio, $rango]);
        $datos_raw = $stmt->fetchAll();
        if (count($datos_raw) > 0) {
            $resultado['cantidad'] = count($datos_raw);
            $datos = array();
            foreach ($datos_raw as $r) {
                $row = array(
                        'idReferencia' => $r['Id'],
                        'especialidad' => $r['Especialidad'],
                        'idColegiado' => $r['IdColegiado'],
                        'matricula' => $r['Matricula'],
                        'sexo' => $r['Sexo'],
                        'apellido' => $r['Apellido'],
                        'nombres' => $r['Nombres'],
                        'mail' => $r['CorreoElectronico'],
                        'fechaVencimiento' => $r['FechaVencimiento']
                );
                array_push($datos, $row);
            }
            $resultado['estado'] = true;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = false;
            $resultado['mensaje'] = "No hay Vencimientos a Enviar";
            $resultado['clase'] = 'alert alert-info';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando Titulos con Vencimiento";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }

    return $resultado;
}

    public function agregarEspecialista($idEspecialidad, $fechaEspecialista, $colegio, $fechaVencimiento, $idColegiado, $idTipoEspecialista, $idResolucionDetalle, $incisoArticulo8) {
    $resultado = array();
    try {
        $db = Database::getConnection();
        $sql="INSERT colegiadoespecialista (Especialidad, IdUsuario, FechaCarga, FechaEspecialista, Colegio, FechaVencimiento, Estado, IdColegiado, IdTipoEspecialista, IdResolucionDetalle, IncisoArticulo8)
              VALUES (?, ?, date(now()), ?, ?, ?, 'A', ?, ?, ?, ?)";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idEspecialidad, $_SESSION['user_id'], $fechaEspecialista, $colegio, $fechaVencimiento, $idColegiado, $idTipoEspecialista, $idResolucionDetalle, $incisoArticulo8]);
        $resultado['estado'] = true;
        $resultado['mensaje'] = "OK";
        $resultado['idColegiadoEspecialista'] = $db->lastInsertId();
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando Titulos con Vencimiento";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }

    return $resultado;
}

    public function agregarEspecialistaTipo($idColegiadoEspecialista, $tipoEspecialista, $fechaAprobacion, $distrito, $idResolucionDetalle, $idTipoEspecialista) {
    $resultado = array();
    try {
        $db = Database::getConnection();
        echo 'idColegiadoEspecialista->'.$idColegiadoEspecialista.' tipoespecialista->'.$tipoEspecialista.' fechaAprobacion->'.$fechaAprobacion.' distrito->'.$distrito.' idResolucionDetalle->'.$idResolucionDetalle.' idTipoEspecialista->'.$idTipoEspecialista.'-<br>';
        $sql="INSERT colegiadoespecialistatipo
                (IdColegiadoEspecialista, TipoEspecialista, Fecha, Colegio, IdUsuario, FechaCarga, IdResolucionDetalle, IdTipoEspecialista)
              VALUES (?, ?, ?, 1, ?, date(now()), ?, ?)";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idColegiadoEspecialista, $tipoEspecialista, $fechaAprobacion, $_SESSION['user_id'], $idResolucionDetalle, $idTipoEspecialista]);
        $resultado['estado'] = true;
        $resultado['mensaje'] = "OK";
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando Titulos con Vencimiento -> ".$e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }

    return $resultado;
}

    public function agregarRecertificacion($idColegiadoEspecialista, $fechaRecertificacion, $fechaVencimiento, $idResolucionDetalle) {
    $resultado = array();
    try {
        $db = Database::getConnection();
        $sql="UPDATE colegiadoespecialista
            SET FechaRecertificacion = ?,
                FechaVencimiento = ?
            WHERE Id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$fechaRecertificacion, $fechaVencimiento, $idColegiadoEspecialista]);

        $sql="INSERT INTO colegiadoespecialistarecertificaciones
            (IdColegiadoEspecialista, IdResolucionDetalle)
            VALUE (?, ?)";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idColegiadoEspecialista, $idResolucionDetalle]);
        $resultado['estado'] = true;
        $resultado['mensaje'] = "OK";
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error al actualizar la recertificacion";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function existeEspecialista($idColegiado, $idEspecialidad, $tipoEspecialista) {
    try {
        $db = Database::getConnection();
        if ($tipoEspecialista == "Especialista") {
            $sql="SELECT COUNT(ce.Id) AS Cantidad
                FROM colegiadoespecialista ce
                WHERE ce.IdColegiado = ? AND ce.Especialidad = ? AND ce.Estado = 'A'";
            $stmt = $db->prepare($sql);
            $stmt->execute([$idColegiado, $idEspecialidad]);
            $row = $stmt->fetch();
            if ($row && $row['Cantidad'] > 0) {
                return TRUE;
            } else {
                return FALSE;
            }
        }
    } catch (PDOException $e) {
        return FALSE;
    }
    return FALSE;
}

    public function obtenerColegiadoEspecialistaPorColegiadoEspecialidad($idColegiado, $idEspecialidad) {
    try {
        $db = Database::getConnection();
        $sql="SELECT colegiadoespecialista.Id, colegiadoespecialista.FechaCarga, colegiadoespecialista.FechaEspecialista,
            colegiadoespecialista.FechaRecertificacion, colegiadoespecialista.Colegio, colegiadoespecialista.FechaVencimiento,
            tipoespecialista.Nombre, especialidad.Especialidad, especialidad.CodigoRes62707, colegiadoespecialista.Estado,
            especialidad.Id AS IdEspecialidad
        FROM colegiadoespecialista
        INNER JOIN especialidad ON(especialidad.Id = colegiadoespecialista.Especialidad)
        LEFT JOIN tipoespecialista ON(tipoespecialista.IdTipoEspecialista = colegiadoespecialista.IdTipoEspecialista)
        WHERE colegiadoespecialista.IdColegiado = ? AND colegiadoespecialista.Especialidad = ? AND colegiadoespecialista.Estado = 'A'
        ORDER BY colegiadoespecialista.FechaEspecialista DESC";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idColegiado, $idEspecialidad]);
        $row = $stmt->fetch();
        $resultado = array();
        if ($row) {
            $datos = array(
                    'idColegiadoEspecialista' => $row['Id'],
                    'fechaCarga' => $row['FechaCarga'],
                    'fechaEspecialista' => $row['FechaEspecialista'],
                    'fechaRecertificacion' => $row['FechaRecertificacion'],
                    'distritoOrigen' => $row['Colegio'],
                    'fechaVencimiento' => $row['FechaVencimiento'],
                    'tipoespecialista' => $row['Nombre'],
                    'nombreEspecialidad' => $row['Especialidad'],
                    'codigoEspecialidad' => $row['CodigoRes62707'],
                    'estado' => $row['Estado'],
                    'idEspecialidad' => $row['IdEspecialidad'],
                    'idColegiado' => $idColegiado
                );
            $resultado['estado'] = TRUE;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = FALSE;
            $resultado['datos'] = NULL;
            $resultado['mensaje'] = "El colegiado no tiene especialidades.";
            $resultado['clase'] = 'alert alert-warning';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando especialidades del colegiado";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }

    return $resultado;
}

    public function guardarQrColegiadoEspecialista($idColegiadoEspecialista, $hash_qr, $pathArchivo, $nombreArchivo) {
    $resultado = array();
    try {
        $db = Database::getConnection();
        $sql="UPDATE colegiadoespecialista
                SET HashQR = ?,
                    PathArchivo = ?,
                    NombreArchivo = ?
                WHERE Id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$hash_qr, $pathArchivo, $nombreArchivo, $idColegiadoEspecialista]);
        $resultado['estado'] = TRUE;
        $resultado['mensaje'] = "OK";
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "ERROR AL AGREGAR CODIGO QR. ".$e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function noExisteCodigoQR($idColegiadoEspecialista) {
    try {
        $db = Database::getConnection();
        $sql="SELECT HashQR FROM colegiadoespecialista WHERE Id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idColegiadoEspecialista]);
        $row = $stmt->fetch();
        $resultado = TRUE;
        if ($row && isset($row['HashQR'])) {
            $resultado = FALSE;
        }
    } catch (PDOException $e) {
        $resultado = TRUE;
    }
    return $resultado;
}

    public function obtenerCodigoQR($idColegiadoEspecialista) {
    try {
        $db = Database::getConnection();
        $sql="SELECT HashQR FROM colegiadoespecialista WHERE Id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idColegiadoEspecialista]);
        $row = $stmt->fetch();
        $resultado = array();
        $resultado['estado'] = TRUE;
        $resultado['hash_qr'] = $row ? $row['HashQR'] : null;
        $resultado['mensaje'] = "OK";
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "ERROR AL OBTENER CODIGO QR. ".$e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function agregarColegiadoEspecialista($idColegiado, $idEspecialidad, $idTipoEspecialista, $fechaEspecialista, $fechaRecertificacion, $fechaVencimiento, $inciso, $distritoOtorgante, $idResolucionDetalle) {
    $resultado = array();
    try {
        $db = Database::getConnection();
        $sql="INSERT colegiadoespecialista (Especialidad, IdUsuario, FechaCarga, FechaEspecialista, FechaRecertificacion, Colegio, FechaVencimiento, Estado, IdColegiado, IdTipoEspecialista, IdResolucionDetalle, IncisoArticulo8)
                VALUES (?, ?, DATE(NOW()), ?, ?, ?, ?, 'A', ?, ?, ?, ?)";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idEspecialidad, $_SESSION['user_id'], $fechaEspecialista, $fechaRecertificacion, $distritoOtorgante, $fechaVencimiento, $idColegiado, $idTipoEspecialista, $idResolucionDetalle, $inciso]);
        $resultado['estado'] = TRUE;
        $resultado['mensaje'] = "OK";
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "ERROR AL AGREGAR Especialista. ".$e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function editarColegiadoEspecialista($idColegiadoEspecialista, $idTipoEspecialista, $inciso, $fechaVencimiento, $fechaRecertificacion) {
    $resultado = array();
    try {
        $db = Database::getConnection();
        $sql="UPDATE colegiadoespecialista
                SET IdTipoEspecialista = ?, IncisoArticulo8 = ?, FechaVencimiento = ?, FechaRecertificacion = ?
                WHERE Id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idTipoEspecialista, $inciso, $fechaVencimiento, $fechaRecertificacion, $idColegiadoEspecialista]);
        $resultado['estado'] = TRUE;
        $resultado['mensaje'] = "OK";
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "ERROR AL AGREGAR CODIGO QR. ".$e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function borrarColegiadoEspecialista($idColegiadoEspecialista) {
    $resultado = array();
    try {
        $db = Database::getConnection();
        $sql="UPDATE colegiadoespecialista
                SET Estado = 'B', IdUsuarioBorrado = ?, FechaBorrado = NOW(), Borrado = 1
                WHERE Id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$_SESSION['user_id'], $idColegiadoEspecialista]);
        $resultado['estado'] = TRUE;
        $resultado['mensaje'] = "OK";
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "ERROR AL BORRAR Especialista. ".$e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function guardarColegiadoEspecialista($idColegiadoEspecialista, $idColegiado, $idEspecialidad, $idTipoEspecialista, $fechaEspecialista, $fechaRecertificacion, $fechaVencimiento, $inciso, $distritoOtorgante, $idResolucionDetalle, $datosAnteriores) {
    try {
        $db = Database::getConnection();
        if (isset($idColegiadoEspecialista) && $idColegiadoEspecialista <> "") {
            $sql="UPDATE colegiadoespecialista ce, resoluciondetalle rd
                SET ce.IdTipoEspecialista = ?, ce.IncisoArticulo8 = ?, ce.FechaVencimiento = ?, ce.FechaRecertificacion = ?, rd.IncisoArticulo8 = ?
                WHERE ce.Id = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$idTipoEspecialista, $inciso, $fechaVencimiento, $fechaRecertificacion, $inciso, $idColegiadoEspecialista]);
            $tipoMovimiento = 'modificacion';
        } else {
            $sql="INSERT colegiadoespecialista (Especialidad, IdUsuario, FechaCarga, FechaEspecialista, FechaRecertificacion, Colegio, FechaVencimiento, Estado, IdColegiado, IdTipoEspecialista, IdResolucionDetalle, IncisoArticulo8)
                VALUES (?, ?, DATE(NOW()), ?, ?, ?, ?, 'A', ?, ?, ?, ?)";
            $stmt = $db->prepare($sql);
            $stmt->execute([$idEspecialidad, $_SESSION['user_id'], $fechaEspecialista, $fechaRecertificacion, $distritoOtorgante, $fechaVencimiento, $idColegiado, $idTipoEspecialista, $idResolucionDetalle, $inciso]);
            $idColegiadoEspecialista = $db->lastInsertId();
            $datosAnteriores = array();
            $tipoMovimiento = 'alta';
        }
        $datos = serialize($datosAnteriores);
        $sql="INSERT INTO log_cursos (Tabla, IdTabla, Fecha, TipoMovimiento, IdUsuario, Datos)
            VALUES ('colegiadoespecialista', ?, NOW(), ?, ?, ?)";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idColegiadoEspecialista, $tipoMovimiento, $_SESSION['user_id'], $datos]);
        $resultado['estado'] = TRUE;
        $resultado['idColegiadoEspecialista'] = $idColegiadoEspecialista;
        $resultado['mensaje'] = 'EL ESPECIALISTA HA SIDO GUARDADO';
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error al guardar colegiadoEspecialista->".$e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function guardarColegiadoEspecialistaTipo($idColegiadoEspecialistaTipo, $idColegiadoEspecialista, $fecha, $idTipoEspecialista, $distritoOtorgante) {
    $resultado = array();
    try {
        $db = Database::getConnection();
        if (isset($idColegiadoEspecialistaTipo) && $idColegiadoEspecialistaTipo <> "") {
            $sql="UPDATE colegiadoespecialistatipo
                SET Fecha = ?, Colegio = ?, IdUsuario = ?, FechaCarga = DATE(NOW())
              WHERE Id = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$fecha, $distritoOtorgante, $_SESSION['user_id'], $idColegiadoEspecialistaTipo]);
        } else {
            $sql="INSERT colegiadoespecialistatipo
                (IdColegiadoEspecialista, IdTipoEspecialista, Fecha, Colegio, IdUsuario, FechaCarga)
              VALUES (?, ?, ?, ?, ?, DATE(NOW()))";
            $stmt = $db->prepare($sql);
            $stmt->execute([$idColegiadoEspecialista, $idTipoEspecialista, $fecha, $distritoOtorgante, $_SESSION['user_id']]);
        }
        $resultado['estado'] = true;
        $resultado['mensaje'] = "OK";
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error al guardar colegiadoEspecialistaTipo";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }

    return $resultado;
}

    public function borrarColegiadoEspecialistaTipo($idColegiadoEspecialistaTipo) {
    $resultado = array();
    try {
        $db = Database::getConnection();
        $sql="UPDATE colegiadoespecialistatipo
                SET Borrado = 1, IdUsuario = ?, FechaCarga = DATE(NOW())
              WHERE Id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$_SESSION['user_id'], $idColegiadoEspecialistaTipo]);
        $resultado['estado'] = true;
        $resultado['mensaje'] = "OK";
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error al borrar colegiadoEspecialistaTipo";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }

    return $resultado;
}

    public function firmantesPorEntidad($entidad, $orden) {
    try {
        $db = Database::getConnection();
        $sql="SELECT Id, Cargo, Titulo, ApellidoNombres
            FROM firmantetituloespecialista
            WHERE Entidad = ? AND Orden = ? AND Estado = 'A'";
        $stmt = $db->prepare($sql);
        $stmt->execute([$entidad, $orden]);
        $row = $stmt->fetch();
        $resultado = array();
        if ($row) {
            $datos = array(
                        'id' => $row['Id'],
                        'cargo' => $row['Cargo'],
                        'titulo' => $row['Titulo'],
                        'apellidoNombre' => $row['ApellidoNombres']
                        );
            $resultado['estado'] = true;
            $resultado['datos'] = $datos;
            $resultado['mensaje'] = "OK";
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = false;
            $resultado['mensaje'] = "No existe Firmante";
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-remove';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error al buscar firmante";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }

    return $resultado;
}

    public function obtenerTitulosParaEntregaPorIdColegiado($idColegiado) {
    try {
        $db = Database::getConnection();
        $sql = "SELECT te.IdTituloEspecialista, te.IdResolucionDetalle, e.Especialidad AS Especialista, e2.Especialidad AS Recertificacion, e3.Especialidad AS Jer_Con, if (cet.TipoEspecialista = 'J', 'Jerarquizado', if (cet.TipoEspecialista = 'C', 'Consultor', '')) AS tipo
                FROM tituloespecialista te
                INNER JOIN resoluciondetalle rd ON rd.Id = te.IdResolucionDetalle
                LEFT JOIN colegiadoespecialista ce ON ce.IdResolucionDetalle = rd.Id
                LEFT JOIN especialidad e ON e.Id = ce.Especialidad
                    LEFT JOIN colegiadoespecialistarecertificaciones cer ON cer.IdResolucionDetalle = rd.Id
                LEFT JOIN colegiadoespecialista ce2 ON ce2.Id = cer.IdColegiadoEspecialista
                LEFT JOIN especialidad e2 ON e2.Id = ce2.Especialidad
                    LEFT JOIN colegiadoespecialistatipo cet ON cet.IdResolucionDetalle = rd.Id
                LEFT JOIN colegiadoespecialista ce3 ON ce3.Id = cet.IdColegiadoEspecialista
                LEFT JOIN especialidad e3 ON e3.Id = ce3.Especialidad
                WHERE rd.IdColegiado = ?
                AND te.FechaEmision >= '2016-01-01'
                AND te.FechaEntrega IS NULL";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idColegiado]);
        $datos_raw = $stmt->fetchAll();
        $resultado = array();
        $datos = array();
        foreach ($datos_raw as $r) {
            $especialista = $r['Especialista'];
            $recertificacion = $r['Recertificacion'];
            $jer_con = $r['Jer_Con'];
            $tipo = $r['tipo'];
            if (isset($especialista) && $especialista <> "") {
                $especialidadEntregar = 'Título Especialista: '.$especialista;
            } else {
                if (isset($recertificacion) && $recertificacion <> "") {
                    $especialidadEntregar = 'Título Recertificación Especialista: '.$recertificacion;
                } else {
                    if (isset($jer_con) && $jer_con <> "") {
                        $especialidadEntregar = 'Título Especialista '.$tipo.': '.$jer_con;
                    } else {
                        continue;
                    }
                }
            }
            $row = array(
                    'idTituloEspecialista' => $r['IdTituloEspecialista'],
                    'especialidadEntregar' => $especialidadEntregar
            );
            array_push($datos, $row);
        }
        $resultado['estado'] = true;
        $resultado['mensaje'] = "OK";
        $resultado['datos'] = $datos;
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error al buscar especialidades";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }

    return $resultado;
}

    public function guradarFechaEmicionTitulo($idResolucionDetalle) {
    $resultado = array();
    try {
        $db = Database::getConnection();
        $sql="UPDATE tituloespecialista
                SET Borrado = 1
              WHERE IdResolucionDetalle = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idResolucionDetalle]);

        $fechaEmision = date('Y-m-d');
        $sql="INSERT INTO tituloespecialista (IdResolucionDetalle, FechaEmision, IdUsuarioEmision)
                VALUES (?, ?, ?)";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idResolucionDetalle, $fechaEmision, $_SESSION['user_id']]);
        $resultado['estado'] = true;
        $resultado['mensaje'] = "OK";
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error al guardar fecha de emision";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function guradarFechaEntregaTitulo($idTituloEspecialista) {
    $resultado = array();
    try {
        $db = Database::getConnection();
        $sql="UPDATE tituloespecialista
                SET FechaEntrega = NOW(), IdUsuarioEntrega = ?
              WHERE IdTituloEspecialista = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$_SESSION['user_id'], $idTituloEspecialista]);
        $resultado['estado'] = true;
        $resultado['mensaje'] = "OK";
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error al guardar fecha de emision";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function obtenerEspecialistasParaImprimir($idEspecialidad, $estadoMatricular, $colegiadoEn) {
    try {
        $db = Database::getConnection();
        $filtro = "";
        if (isset($idEspecialidad) && $idEspecialidad >= 1000) {
            $filtro .= "WHERE e.Id = ".$idEspecialidad;
        }

        if ($estadoMatricular == 'ACTIVOS') {
            if ($filtro == '') { $filtro = "WHERE"; } else { $filtro .= " AND"; }
            $filtro .= " tm.Estado = 'A'";
        } else {
            if ($estadoMatricular == 'ACTIVOS_INSCRIPTOS') {
                if ($filtro == '') { $filtro = "WHERE"; } else { $filtro .= " AND"; }
                $filtro .= " tm.Estado IN('A', 'I')";
            }
        }

        if ($colegiadoEn == 'DISTRITO_I') {
            if ($filtro == '') { $filtro = "WHERE"; } else { $filtro .= " AND"; }
            $filtro .= " ce.Colegio IN('I', '1')";
        }

        $sql="SELECT c.Id, c.Matricula, p.Apellido, p.Nombres, ce.Especialidad, e.Especialidad AS NombreEspecialidad, ce.FechaEspecialista, ce.FechaRecertificacion, ce.Colegio, ce.FechaVencimiento, tm.Estado AS EstadoMatricular,
            (SELECT MAX(cet.Fecha) FROM colegiadoespecialistatipo cet WHERE cet.IdColegiadoEspecialista = ce.Id AND cet.IdTipoEspecialista = 3 AND cet.Borrado = 0 GROUP BY cet.IdColegiadoEspecialista, cet.IdTipoEspecialista) AS FechaJerarquizado,
            (SELECT MAX(cet.Fecha) FROM colegiadoespecialistatipo cet WHERE cet.IdColegiadoEspecialista = ce.Id AND cet.IdTipoEspecialista = 4 AND cet.Borrado = 0 GROUP BY cet.IdColegiadoEspecialista, cet.IdTipoEspecialista) AS FechaConsultor
            FROM colegiadoespecialista ce
            INNER JOIN colegiado c ON ce.IdColegiado = c.Id
            INNER JOIN persona p ON p.Id = c.IdPersona
            INNER JOIN tipomovimiento tm ON tm.Id = c.Estado
            INNER JOIN especialidad e ON e.Id = ce.Especialidad
            ".$filtro."
            ORDER BY e.Especialidad, p.Apellido, p.Nombres";

        $stmt = $db->prepare($sql);
        $stmt->execute();
        $datos_raw = $stmt->fetchAll();
        $resultado = array();
        if (count($datos_raw) > 0) {
            $datos = array();
            foreach ($datos_raw as $r) {
                $fechaVencimiento = $r['FechaVencimiento'];
                $fechaConsultor = $r['FechaConsultor'];
                if (isset($fechaConsultor) && $fechaConsultor <> "" && $fechaConsultor <> "0000-00-00") {
                    $fechaVencimiento = NULL;
                }
                $row = array(
                    'idColegiado' => $r['Id'],
                    'matricula' => $r['Matricula'],
                    'apellido' => $r['Apellido'],
                    'nombre' => $r['Nombres'],
                    'idEspecialidad' => $r['Especialidad'],
                    'nombreEspecialidad' => $r['NombreEspecialidad'],
                    'fechaEspecialista' => $r['FechaEspecialista'],
                    'fechaRecertificacion' => $r['FechaRecertificacion'],
                    'colegio' => $r['Colegio'],
                    'fechaVencimiento' => $fechaVencimiento,
                    'estadoMatricular' => $r['EstadoMatricular'],
                    'fechaJerarquizado' => $r['FechaJerarquizado'],
                    'fechaConsultor' => $fechaConsultor
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
            $resultado['datos'] = NULL;
            $resultado['mensaje'] = "No se encontraron especialistas.";
            $resultado['clase'] = 'alert alert-warning';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando especialistas";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }

    return $resultado;
}

    public function obtenerResolucionesEspecialidadesPorIdColegiado($idColegiado) {
    try {
        $db = Database::getConnection();
        $sql="SELECT r.Numero, r.Fecha, e.Especialidad, te.Nombre, rd.IncisoArticulo8, te.Codigo
            FROM resoluciondetalle rd
            INNER JOIN resolucion r ON r.Id = rd.IdResolucion
            LEFT JOIN tipoespecialista te ON te.IdTipoEspecialista = rd.IdTipoEspecialista
            INNER JOIN especialidad e ON e.Id = rd.Especialidad
            WHERE rd.IdColegiado = ?
            ORDER BY r.Fecha DESC";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idColegiado]);
        $datos_raw = $stmt->fetchAll();
        $resultado = array();
        if (count($datos_raw) > 0) {
            $datos = array();
            foreach ($datos_raw as $r) {
                $codigoEspecialista = $r['Codigo'];
                $incisoArticulo8 = $r['IncisoArticulo8'];
                $nombreTipoEspecialista = $r['Nombre'];
                if ($codigoEspecialista == "N") {
                    $distritoOrigen = "NACIÓN";
                } else {
                    $distritoOrigen = "1";
                }
                if (isset($incisoArticulo8) && $incisoArticulo8 <> "") {
                    $tipoespecialista = trim($nombreTipoEspecialista.' Inc.'.$incisoArticulo8);
                } else {
                    $tipoespecialista = $nombreTipoEspecialista;
                }
                $row = array(
                    'numeroResolucion' => $r['Numero'],
                    'fechaResolucion' => $r['Fecha'],
                    'nombreEspecialidad' => $r['Especialidad'],
                    'tipoespecialista' => $tipoespecialista,
                    'incisoArticulo8' => $incisoArticulo8,
                    'distritoOrigen' => $distritoOrigen,
                    'codigoEspecialista' => $codigoEspecialista
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
            $resultado['datos'] = NULL;
            $resultado['mensaje'] = "El colegiado no tiene especialidades.";
            $resultado['clase'] = 'alert alert-warning';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando especialidades del colegiado";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }

    return $resultado;
}
}
