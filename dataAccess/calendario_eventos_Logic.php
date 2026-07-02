<?php
class calendario_eventosLogic {
    public function obtenerCursoEntidadPorEstado($estadoCurso, $periodoSeleccionado) {
        if ($periodoSeleccionado == "TODOS") {
            $conFiltro = "";
        } else {
            $conFiltro = " AND (YEAR(c.FechaInicio) = ".$periodoSeleccionado." OR (SELECT COUNT(cat.IdCursoAulaTurno) FROM cursoaulaturno cat INNER JOIN cursoaula ca ON ca.IdCursoAula = cat.IdCursoAula WHERE ca.IdCurso = c.Id AND YEAR(cat.Fecha) = ".$periodoSeleccionado."))";
        }
        $sql = "SELECT c.Id, c.Titulo, c.Director, c.FechaInicio, c.VigenciaHasta
            FROM cursoentidad c
            WHERE c.Estado = ?".$conFiltro;

        $resultado = array();
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare($sql);
            $stmt->execute([$estadoCurso]);
            $datos = $stmt->fetchAll();
            if (count($datos) > 0) {
                $rows = array();
                foreach ($datos as $row) {
                    $rows[] = array(
                        'idCursoEntidad' => $row['Id'],
                        'titulo' => $row['Titulo'],
                        'fechaInicio' => $row['FechaInicio'],
                        'vigenciaHasta' => $row['VigenciaHasta'],
                        'director' => $row['Director']
                    );
                }
                $resultado['estado'] = TRUE;
                $resultado['mensaje'] = "OK";
                $resultado['datos'] = $rows;
                $resultado['clase'] = 'alert alert-success';
                $resultado['icono'] = 'glyphicon glyphicon-ok';
            } else {
                $resultado['estado'] = FALSE;
                $resultado['mensaje'] = "No hay eventos/cursos";
                $resultado['clase'] = 'alert alert-info';
                $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
            }
        } catch (PDOException $e) {
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "Error buscando eventos/cursos";
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }

        return $resultado;
    }

    public function obtenerCursoEntidadPorId($idCursoEntidad) {
        $resultado = array();
        try {
            $db = Database::getConnection();
            $sql = "SELECT c.Titulo, c.Director, c.FechaInicio, c.VigenciaHasta, c.Estado, c.FechaCarga, c.IdUsuario, c.Observacion
                FROM cursoentidad c
                WHERE c.Id = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$idCursoEntidad]);
            $row = $stmt->fetch();
            if ($row) {
                $datos = array(
                    'idCursoEntidad' => $idCursoEntidad,
                    'titulo' => $row['Titulo'],
                    'director' => $row['Director'],
                    'fechaInicio' => $row['FechaInicio'],
                    'vigenciaHasta' => $row['VigenciaHasta'],
                    'estado' => $row['Estado'],
                    'fechaCarga' => $row['FechaCarga'],
                    'idUsuario' => $row['IdUsuario'],
                    'observacion' => $row['Observacion']
                );
                $resultado['estado'] = TRUE;
                $resultado['mensaje'] = "OK";
                $resultado['datos'] = $datos;
                $resultado['clase'] = 'alert alert-success';
                $resultado['icono'] = 'glyphicon glyphicon-ok';
            } else {
                $resultado['estado'] = FALSE;
                $resultado['mensaje'] = "Error buscando cursoentidad";
                $resultado['clase'] = 'alert alert-danger';
                $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
            }
        } catch (PDOException $e) {
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "Error buscando cursoentidad";
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }

        return $resultado;
    }

    /*
    function obtenerAsistentesAutocompletar(){
        ...
    }
    */

    public function guardarCursoEntidad($idCursoEntidad, $titulo, $director, $fechaInicio, $vigenciaHasta, $observacion, $datosAnteriores){
        try {
            $db = Database::getConnection();
            $db->beginTransaction();
            $resultado = array();
            if (isset($idCursoEntidad) && $idCursoEntidad <> "") {
                $sql = "UPDATE cursoentidad
                        SET Titulo = ?, Director = ?, FechaInicio = ?, VigenciaHasta = ?, Observacion = ?
                        WHERE Id = ?";
                $stmt = $db->prepare($sql);
                $stmt->execute([$titulo, $director, $fechaInicio, $vigenciaHasta, $observacion, $idCursoEntidad]);
            } else {
                $sql = "INSERT INTO cursoentidad (Titulo, Director, FechaInicio, VigenciaHasta, Estado, FechaCarga, IdUsuario, Observacion)
                VALUES (?, ?, ?, ?, 'A', NOW(), ?, ?)";
                $stmt = $db->prepare($sql);
                $stmt->execute([$titulo, $director, $fechaInicio, $vigenciaHasta, $_SESSION['user_id'], $observacion]);
            }

            if ($db->errorCode() == '00000') {
                if (isset($idCursoEntidad) && $idCursoEntidad <> "") {
                    $tipoMovimiento = 'modificacion';
                } else {
                    $idCursoEntidad = $db->lastInsertId();
                    $datosAnteriores = array();
                    $tipoMovimiento = 'alta';
                }
                $datos = serialize($datosAnteriores);
                $sql = "INSERT INTO log_cursos (Tabla, IdTabla, Fecha, TipoMovimiento, IdUsuario, Datos)
                    VALUES ('cursoentidad', ?, NOW(), ?, ?, ?)";
                $stmt = $db->prepare($sql);
                $stmt->execute([$idCursoEntidad, $tipoMovimiento, $_SESSION['user_id'], $datos]);
                if ($db->errorCode() == '00000') {
                    $resultado['estado'] = TRUE;
                    $resultado['idCurso'] = $idCursoEntidad;
                    $resultado['mensaje'] = 'EL CURSO HA SIDO GUARDADO';
                    $resultado['clase'] = 'alert alert-success';
                    $resultado['icono'] = 'glyphicon glyphicon-ok';
                } else {
                    $resultado['estado'] = FALSE;
                    $resultado['mensaje'] .= "ERROR AL AGREGAR CURSO";
                    $resultado['clase'] = 'alert alert-danger';
                    $resultado['icono'] = 'glyphicon glyphicon-remove';
                }
            } else {
                $resultado['estado'] = FALSE;
                $resultado['mensaje'] = "Error guardando curso";
                $resultado['clase'] = 'alert alert-danger';
                $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
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
            $resultado['mensaje'] = "Error: " . $e->getMessage();
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-remove';
            return $resultado;
        }
    }

    public function editarCursoEntidad($idCursoEntidad, $estado, $datosAnteriores) {
        try {
            $db = Database::getConnection();
            $db->beginTransaction();
            $resultado = array();
            $sql = "UPDATE cursoentidad
                    SET Estado = ?, IdUsuario = ?, FechaCarga = NOW()
                    WHERE Id = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$estado, $_SESSION['user_id'], $idCursoEntidad]);
            if ($db->errorCode() == '00000') {
                if ($estado == 'B') {
                    $tipoMovimiento = 'borrado';
                } else {
                    if ($estado == 'F') {
                        $tipoMovimiento = 'finalizado';
                    } else {
                        $tipoMovimiento = 'sin discriminar';
                    }
                }
                $datos = serialize($datosAnteriores);
                $sql = "INSERT INTO log_cursos (Tabla, IdTabla, Fecha, TipoMovimiento, IdUsuario, Datos)
                    VALUES ('cursoentidad', ?, NOW(), ?, ?, ?)";
                $stmt = $db->prepare($sql);
                $stmt->execute([$idCursoEntidad, $tipoMovimiento, $_SESSION['user_id'], $datos]);
                if ($db->errorCode() == '00000') {
                    $resultado['estado'] = TRUE;
                    $resultado['mensaje'] = 'EL cursoentidad HA SIDO MODIFICADO';
                    $resultado['clase'] = 'alert alert-success';
                    $resultado['icono'] = 'glyphicon glyphicon-ok';
                } else {
                    $resultado['estado'] = FALSE;
                    $resultado['mensaje'] .= "ERROR AL MODIFICAR cursoentidad";
                    $resultado['clase'] = 'alert alert-danger';
                    $resultado['icono'] = 'glyphicon glyphicon-remove';
                }
            } else {
                $resultado['estado'] = FALSE;
                $resultado['mensaje'] .= "ERROR AL MODIFICAR cursoentidad";
                $resultado['clase'] = 'alert alert-danger';
                $resultado['icono'] = 'glyphicon glyphicon-remove';
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
            $resultado['mensaje'] = "Error: " . $e->getMessage();
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-remove';
            return $resultado;
        }
    }

    public function obtenerCursoAulaPorIdCursoEntidad($idCursoEntidad, $estado) {
        $resultado = array();
        try {
            $db = Database::getConnection();
            $sql = "SELECT ca.IdCursoAula, ca.IdAula, ca.IdDia, ca.FechaDesde, ca.FechaHasta, ca.HoraDesde, ca.HoraHasta, a.Nombre AS nombreAula, d.Nombre AS nombreDia, ca.Autorizado, ca.FechaCarga, u.Usuario
                FROM cursoaula ca
                INNER JOIN aula a ON (a.IdAula = ca.IdAula)
                INNER JOIN dias d ON (d.Id = ca.IdDia)
                LEFT JOIN usuario u ON (u.Id = ca.IdUsuarioCarga)
                WHERE ca.IdCurso = ? AND ca.Estado = ?
                ORDER BY ca.FechaDesde, ca.HoraDesde";
            $stmt = $db->prepare($sql);
            $stmt->execute([$idCursoEntidad, $estado]);
            $datos = $stmt->fetchAll();
            if (count($datos) > 0) {
                $rows = array();
                foreach ($datos as $row) {
                    $rows[] = array(
                        'idCursoEntidad' => $idCursoEntidad,
                        'idCursoAula' => $row['IdCursoAula'],
                        'idAula' => $row['IdAula'],
                        'idDia' => $row['IdDia'],
                        'fechaDesde' => $row['FechaDesde'],
                        'fechaHasta' => $row['FechaHasta'],
                        'horaDesde' => $row['HoraDesde'],
                        'horaHasta' => $row['HoraHasta'],
                        'nombreAula' => $row['nombreAula'],
                        'nombreDia' => $row['nombreDia'],
                        'autorizado' => $row['Autorizado'],
                        'fechaCarga' => $row['FechaCarga'],
                        'nombreUsuario' => $row['Usuario']
                    );
                }
                $resultado['estado'] = TRUE;
                $resultado['mensaje'] = "OK";
                $resultado['datos'] = $rows;
                $resultado['clase'] = 'alert alert-success';
                $resultado['icono'] = 'glyphicon glyphicon-ok';
            } else {
                $resultado['estado'] = FALSE;
                $resultado['mensaje'] = "No hay aulas para el curso";
                $resultado['clase'] = 'alert alert-info';
                $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
            }
        } catch (PDOException $e) {
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "Error buscando aulas para el curso";
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }

        return $resultado;
    }

    public function obtenerCursoAulaPorId($idCursoAula) {
        $resultado = array();
        try {
            $db = Database::getConnection();
            $sql = "SELECT c.IdCursoAula, c.IdCurso, c.IdAula, c.IdDia, c.FechaDesde, c.FechaHasta, c.HoraDesde, c.HoraHasta, c.FechaCarga, c.Estado, c.IdUsuarioCarga, c.FechaUltimaModificacion, c.IdUsuarioUltimaModificacion, c.FechaBaja, c.IdUsuarioBaja, c.Autorizado, c.Periodicidad, a.Nombre, ce.Titulo, d.Nombre
                FROM cursoaula c
                INNER JOIN aula a ON a.IdAula = c.IdAula
                INNER JOIN cursoentidad ce ON ce.Id = c.IdCurso
                INNER JOIN dias d ON d.Id = c.IdDia
                WHERE c.IdCursoAula = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$idCursoAula]);
            $row = $stmt->fetch(PDO::FETCH_NUM);
            if ($row) {
                $datos = array(
                    'idCursoAula' => $row[0],
                    'idCursoEntidad' => $row[1],
                    'idAula' => $row[2],
                    'idDia' => $row[3],
                    'fechaInicio' => $row[4],
                    'fechaFin' => $row[5],
                    'horaInicio' => $row[6],
                    'horaFin' => $row[7],
                    'estado' => $row[9],
                    'autorizado' => $row[15],
                    'periodicidad' => $row[16],
                    'nombreAula' => $row[17],
                    'titulo' => $row[18],
                    'nombreDia' => $row[19]
                );
                $resultado['estado'] = TRUE;
                $resultado['mensaje'] = "OK";
                $resultado['datos'] = $datos;
                $resultado['clase'] = 'alert alert-success';
                $resultado['icono'] = 'glyphicon glyphicon-ok';
            } else {
                $resultado['estado'] = FALSE;
                $resultado['mensaje'] = "Error buscando cursoentidad";
                $resultado['clase'] = 'alert alert-danger';
                $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
            }
        } catch (PDOException $e) {
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "Error buscando cursoentidad";
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }

        return $resultado;
    }

    public function obtenerCursoAulaTurnoPorIdCursoAula($idCursoAula, $estado) {
        $resultado = array();
        try {
            $db = Database::getConnection();
            $sql = "SELECT cat.IdCursoAulaTurno, cat.Fecha, cat.HoraDesde, cat.HoraHasta
                FROM cursoaulaturno cat
                INNER JOIN cursoaula ca ON ca.IdCursoAula = cat.IdCursoAula AND ca.Estado = ?
                WHERE cat.IdCursoAula = ? AND cat.Estado = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$estado, $idCursoAula, $estado]);
            $datos = $stmt->fetchAll();
            if (count($datos) > 0) {
                $rows = array();
                foreach ($datos as $row) {
                    $rows[] = array(
                        'idCursoAulaTurno' => $row['IdCursoAulaTurno'],
                        'fecha' => $row['Fecha'],
                        'horaDesde' => $row['HoraDesde'],
                        'horaHasta' => $row['HoraHasta']
                    );
                }
                $resultado['estado'] = TRUE;
                $resultado['mensaje'] = "OK";
                $resultado['datos'] = $rows;
                $resultado['clase'] = 'alert alert-success';
                $resultado['icono'] = 'glyphicon glyphicon-ok';
            } else {
                $resultado['estado'] = FALSE;
                $resultado['mensaje'] = "No hay turnos del aula para el curso";
                $resultado['clase'] = 'alert alert-info';
                $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
            }
        } catch (PDOException $e) {
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "Error buscando turnos del aula para el curso";
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }

        return $resultado;
    }

    public function obtenerAulasPorEstado($estado) {
        $resultado = array();
        try {
            $db = Database::getConnection();
            $sql = "SELECT IdAula, Nombre, Lugar, Capacidad
                FROM aula
                WHERE Estado = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$estado]);
            $datos = $stmt->fetchAll();
            if (count($datos) > 0) {
                $rows = array();
                foreach ($datos as $row) {
                    $rows[] = array(
                        'idAula' => $row['IdAula'],
                        'nombreAula' => $row['Nombre'],
                        'lugar' => $row['Lugar'],
                        'capacidad' => $row['Capacidad']
                    );
                }
                $resultado['estado'] = TRUE;
                $resultado['mensaje'] = "OK";
                $resultado['datos'] = $rows;
                $resultado['clase'] = 'alert alert-success';
                $resultado['icono'] = 'glyphicon glyphicon-ok';
            } else {
                $resultado['estado'] = FALSE;
                $resultado['mensaje'] = "No hay aulas";
                $resultado['clase'] = 'alert alert-info';
                $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
            }
        } catch (PDOException $e) {
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "Error buscando aulas";
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }

        return $resultado;
    }

    public function obtenerCursoAulaTurnoPorId($idCursoAulaTurno) {
        $resultado = array();
        try {
            $db = Database::getConnection();
            $sql = "SELECT c.IdCursoAulaTurno, c.IdCursoAula, c.Fecha, c.HoraDesde, c.HoraHasta, c.Estado, c.FechaUltimaModificacion, c.IdUsuarioUltimaModificacion, c.FechaBaja, c.IdUsuarioBaja
                FROM cursoaulaturno c
                WHERE c.IdCursoAulaTurno = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$idCursoAulaTurno]);
            $row = $stmt->fetch();
            if ($row) {
                $datos = array(
                    'idCursoAulaTurno' => $row['IdCursoAulaTurno'],
                    'idCursoAula' => $row['IdCursoAula'],
                    'fecha' => $row['Fecha'],
                    'horaInicio' => $row['HoraDesde'],
                    'horaFin' => $row['HoraHasta'],
                    'estado' => $row['Estado'],
                    'fechaUltimaModificacion' => $row['FechaUltimaModificacion'],
                    'idUsuarioUltimaModificacion' => $row['IdUsuarioUltimaModificacion'],
                    'fechaBaja' => $row['FechaBaja'],
                    'idUsuarioBaja' => $row['IdUsuarioBaja']
                );
                $resultado['estado'] = TRUE;
                $resultado['mensaje'] = "OK";
                $resultado['datos'] = $datos;
                $resultado['clase'] = 'alert alert-success';
                $resultado['icono'] = 'glyphicon glyphicon-ok';
            } else {
                $resultado['estado'] = FALSE;
                $resultado['mensaje'] = "Error buscando cursoaulaturno";
                $resultado['clase'] = 'alert alert-danger';
                $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
            }
        } catch (PDOException $e) {
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "Error buscando cursoaulaturno";
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }

        return $resultado;
    }

    public function obtenerDias() {
        $resultado = array();
        try {
            $db = Database::getConnection();
            $sql = "SELECT Id, Nombre FROM dias";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            $datos = $stmt->fetchAll();
            if (count($datos) > 0) {
                $rows = array();
                foreach ($datos as $row) {
                    $rows[] = array(
                        'idDia' => $row['Id'],
                        'nombreDia' => $row['Nombre']
                    );
                }
                $resultado['estado'] = TRUE;
                $resultado['mensaje'] = "OK";
                $resultado['datos'] = $rows;
                $resultado['clase'] = 'alert alert-success';
                $resultado['icono'] = 'glyphicon glyphicon-ok';
            } else {
                $resultado['estado'] = FALSE;
                $resultado['mensaje'] = "No hay dias";
                $resultado['clase'] = 'alert alert-info';
                $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
            }
        } catch (PDOException $e) {
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "Error buscando dias";
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }

        return $resultado;
    }

    public function guardarCursoAula($idCursoAula, $idCursoEntidad, $idAula, $idDia, $fechaInicio, $fechaFin, $horaInicio, $horaFin, $autorizado, $periodicidad, $datosAnteriores) {
        try {
            $db = Database::getConnection();
            $db->beginTransaction();

            if (isset($idCursoAula)) {
                //por el momneto no se hace el editar
                $resultado['estado'] = FALSE;
                $resultado['mensaje'] = "INGRESO INCORRECTO MODIFICACION";
                $resultado['clase'] = 'alert alert-danger';
                $resultado['icono'] = 'glyphicon glyphicon-remove';
            } else {
                //ingresa para dar el alta
                $sql = "INSERT INTO cursoaula (IdCurso, IdAula, IdDia, FechaDesde, FechaHasta, HoraDesde, HoraHasta, FechaCarga, Estado, IdUsuarioCarga, Autorizado, Periodicidad)
                    VALUES(?,?,?,?,?,?,?, NOW(), 'A', ?, ?, ?)";
                $stmt = $db->prepare($sql);
                $stmt->execute([$idCursoEntidad, $idAula, $idDia, $fechaInicio, $fechaFin, $horaInicio, $horaFin, $_SESSION['user_id'], $autorizado, $periodicidad]);

                $resultado = array();
                if ($db->errorCode() == '00000') {
                    if (isset($idCursoAula) && $idCursoAula <> "") {
                        $tipoMovimiento = 'modificacion';
                    } else {
                        $idCursoAula = $db->lastInsertId();
                        $datosAnteriores = array();
                        $tipoMovimiento = 'alta';
                    }
                    $datos = serialize($datosAnteriores);
                    $sql = "INSERT INTO log_cursos (Tabla, IdTabla, Fecha, TipoMovimiento, IdUsuario, Datos)
                        VALUES ('cursoaula', ?, NOW(), ?, ?, ?)";
                    $stmt = $db->prepare($sql);
                    $stmt->execute([$idCursoAula, $tipoMovimiento, $_SESSION['user_id'], $datos]);
                    if ($db->errorCode() == '00000') {
                        /* Inserción de cursoaulaturno */
                        $sql2 = "INSERT INTO cursoaulaturno (IdCursoAula, Fecha, HoraDesde, HoraHasta, Estado)
                            VALUES(?, ?, ?, ?, 'A')";

                        $fechaTurno = $this->obtenerFechaPorDia($fechaInicio, $idDia);

                        $fecha1 = strtotime(date($fechaTurno, time()));
                        $fecha2 = strtotime(date($fechaFin, time()));

                        $estadoConsulta = TRUE;
                        while ($fecha1 <= $fecha2 && $estadoConsulta) {
                            $stmt = $db->prepare($sql2);
                            if (!$stmt->execute([$idCursoAula, $fechaTurno, $horaInicio, $horaFin])) {
                                $estadoConsulta = FALSE;
                                $mensaje = "Error al insertar cursoaulaturno";
                            }

                            switch ($periodicidad) {
                                case 'DIARIO':
                                case 'SEMANAL':
                                    $fechaTurno = $this->sumarFecha($fechaTurno, 7, "+", "day");
                                    break;

                                case 'QUINCENAL':
                                    $fechaTurno = $this->sumarFecha($fechaTurno, 14, "+", "day");
                                    break;

                                case 'MENSUAL':
                                    $fechaTurno = $this->sumarFecha($fechaTurno, 28, "+", "day");
                                    break;

                                default:
                                    $estadoConsulta = FALSE;
                                    $mensaje = "Periodicidad erronea.";
                                    break;
                            }
                            $fecha1 = strtotime(date($fechaTurno, time()));
                        }
                        if ($estadoConsulta) {
                            $resultado['estado'] = TRUE;
                            $resultado['idCursoAula'] = $idCursoAula;
                            $resultado['mensaje'] = 'EL CURSO HA SIDO GUARDADO';
                            $resultado['clase'] = 'alert alert-success';
                            $resultado['icono'] = 'glyphicon glyphicon-ok';
                        } else {
                            $resultado['estado'] = FALSE;
                            $resultado['mensaje'] .= "ERROR AL AGREGAR CURSO_AULA_TURNO -> ".$mensaje;
                            $resultado['clase'] = 'alert alert-danger';
                            $resultado['icono'] = 'glyphicon glyphicon-remove';
                        }
                    } else {
                        $resultado['estado'] = FALSE;
                        $resultado['mensaje'] = "ERROR AL AGREGAR CURSO_AULA";
                        $resultado['clase'] = 'alert alert-danger';
                        $resultado['icono'] = 'glyphicon glyphicon-remove';
                    }
                } else {
                    $resultado['estado'] = FALSE;
                    $resultado['mensaje'] = "ERROR AL AGREGAR CURSO_AULA";
                    $resultado['clase'] = 'alert alert-danger';
                    $resultado['icono'] = 'glyphicon glyphicon-remove';
                }
            }
            /* Commit or rollback transaction */
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
            $resultado['mensaje'] = "Error: " . $e->getMessage();
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-remove';
            return $resultado;
        }
    }

    public function editarCursoAula($idCursoAula, $estado, $datosAnteriores) {
        try {
            $db = Database::getConnection();
            $db->beginTransaction();

            $sql = "UPDATE cursoaula a, cursoaulaturno b
                    SET a.Estado = ?, a.FechaBaja = NOW(), a.IdUsuarioBaja = ?, b.Estado = ?, b.FechaBaja = NOW(), b.IdUsuarioBaja = ?
                    WHERE a.IdCursoAula = b.IdCursoAula AND a.IdCursoAula = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$estado, $_SESSION['user_id'], $estado, $_SESSION['user_id'], $idCursoAula]);

            $resultado = array();
            if ($db->errorCode() == '00000') {
                $tipoMovimiento = 'borrado';
                $datos = serialize($datosAnteriores);
                $sql = "INSERT INTO log_cursos (Tabla, IdTabla, Fecha, TipoMovimiento, IdUsuario, Datos)
                    VALUES ('cursoaula', ?, NOW(), ?, ?, ?)";
                $stmt = $db->prepare($sql);
                $stmt->execute([$idCursoAula, $tipoMovimiento, $_SESSION['user_id'], $datos]);
                if ($db->errorCode() == '00000') {
                    $resultado['estado'] = TRUE;
                    $resultado['idCursoAula'] = $idCursoAula;
                    $resultado['mensaje'] = 'EL CURSO HA SIDO MODIFICADO';
                    $resultado['clase'] = 'alert alert-success';
                    $resultado['icono'] = 'glyphicon glyphicon-ok';
                } else {
                    $resultado['estado'] = FALSE;
                    $resultado['mensaje'] = "ERROR AL MODIFICAR CURSO_AULA";
                    $resultado['clase'] = 'alert alert-danger';
                    $resultado['icono'] = 'glyphicon glyphicon-remove';
                }
            } else {
                $resultado['estado'] = FALSE;
                $resultado['mensaje'] = "ERROR AL MODIFICAR CURSO_AULA";
                $resultado['clase'] = 'alert alert-danger';
                $resultado['icono'] = 'glyphicon glyphicon-remove';
            }

            /* Commit or rollback transaction */
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
            $resultado['mensaje'] = "Error: " . $e->getMessage();
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-remove';
            return $resultado;
        }
    }

    public function guardarCursoAulaTurno($idCursoAulaTurno, $idCursoAula, $fecha, $horaInicio, $horaFin, $datosAnteriores) {
        try {
            $db = Database::getConnection();
            $db->beginTransaction();

            $sql = "UPDATE cursoaulaturno cat
                    INNER JOIN cursoaula ca ON ca.IdCursoAula = cat.IdCursoAula
                    SET cat.HoraDesde = ?, cat.HoraHasta = ?, cat.FechaUltimaModificacion = NOW(), cat.IdUsuarioUltimaModificacion = ?, ca.HoraDesde = ?, ca.HoraHasta = ?, ca.FechaUltimaModificacion = NOW(), ca.IdUsuarioUltimaModificacion = ?
                    WHERE cat.IdCursoAulaTurno = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$horaInicio, $horaFin, $_SESSION['user_id'], $horaInicio, $horaFin, $_SESSION['user_id'], $idCursoAulaTurno]);

            $resultado = array();
            if ($db->errorCode() == '00000') {
                if (isset($idCursoAulaTurno) && $idCursoAulaTurno <> "") {
                    $tipoMovimiento = 'modificacion';
                } else {
                    $idCursoAulaTurno = $db->lastInsertId();
                    $datosAnteriores = array();
                    $tipoMovimiento = 'alta';
                }
                $datos = serialize($datosAnteriores);
                $sql = "INSERT INTO log_cursos (Tabla, IdTabla, Fecha, TipoMovimiento, IdUsuario, Datos)
                    VALUES ('cursoaulaturno', ?, NOW(), ?, ?, ?)";
                $stmt = $db->prepare($sql);
                $stmt->execute([$idCursoAulaTurno, $tipoMovimiento, $_SESSION['user_id'], $datos]);
                if ($db->errorCode() == '00000') {
                    $resultado['estado'] = TRUE;
                    $resultado['mensaje'] = 'EL TURNO HA SIDO GUARDADO';
                    $resultado['clase'] = 'alert alert-success';
                    $resultado['icono'] = 'glyphicon glyphicon-ok';
                } else {
                    $resultado['estado'] = FALSE;
                    $resultado['mensaje'] = "ERROR AL GUARDAR CURSO_AULA_TURNO LOG";
                    $resultado['clase'] = 'alert alert-danger';
                    $resultado['icono'] = 'glyphicon glyphicon-remove';
                }
            } else {
                $resultado['estado'] = FALSE;
                $resultado['mensaje'] = "ERROR AL GUARDAR CURSO_AULA_TURNO";
                $resultado['clase'] = 'alert alert-danger';
                $resultado['icono'] = 'glyphicon glyphicon-remove';
            }

            /* Commit or rollback transaction */
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
            $resultado['mensaje'] = "Error: " . $e->getMessage();
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-remove';
            return $resultado;
        }
    }

    public function borrarCursoAulaTurno($idCursoAulaTurno, $estado, $datosAnteriores) {
        try {
            $db = Database::getConnection();
            $db->beginTransaction();
            $sql = "UPDATE cursoaulaturno
                    SET Estado = ?, FechaBaja = NOW(), IdUsuarioBaja = ?
                    WHERE IdCursoAulaTurno = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$estado, $_SESSION['user_id'], $idCursoAulaTurno]);

            $resultado = array();
            if ($db->errorCode() == '00000') {
                $tipoMovimiento = 'borrado';
                $datos = serialize($datosAnteriores);
                $sql = "INSERT INTO log_cursos (Tabla, IdTabla, Fecha, TipoMovimiento, IdUsuario, Datos)
                    VALUES ('cursoaulaturno', ?, NOW(), ?, ?, ?)";
                $stmt = $db->prepare($sql);
                $stmt->execute([$idCursoAulaTurno, $tipoMovimiento, $_SESSION['user_id'], $datos]);
                if ($db->errorCode() == '00000') {
                    $resultado['estado'] = TRUE;
                    $resultado['mensaje'] = 'EL TURNO HA SIDO MODIFICADO';
                    $resultado['clase'] = 'alert alert-success';
                    $resultado['icono'] = 'glyphicon glyphicon-ok';
                } else {
                    $resultado['estado'] = FALSE;
                    $resultado['mensaje'] = "ERROR AL MODIFICAR CURSO_AULA_TURNO";
                    $resultado['clase'] = 'alert alert-danger';
                    $resultado['icono'] = 'glyphicon glyphicon-remove';
                }
            } else {
                $resultado['estado'] = FALSE;
                $resultado['mensaje'] = "ERROR AL MODIFICAR CURSO_AULA_TURNO";
                $resultado['clase'] = 'alert alert-danger';
                $resultado['icono'] = 'glyphicon glyphicon-remove';
            }

            /* Commit or rollback transaction */
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
            $resultado['mensaje'] = "Error: " . $e->getMessage();
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-remove';
            return $resultado;
        }
    }

    public function noHayTurnoOcupado($idAula, $idDia, $fechaDesde, $fechaFin, $horaInicio, $horaFin) {
        try {
            $db = Database::getConnection();
            $sql = "SELECT cat.IdCursoAulaTurno
                    FROM cursoaulaturno cat
                    INNER JOIN cursoaula ca ON ca.IdCursoAula = cat.IdCursoAula AND ca.Estado = 'A' AND ca.IdAula = ?
                    INNER JOIN cursoentidad ce ON ce.Id = ca.IdCurso AND ce.Estado = 'A' AND ca.IdDia = ?
                    WHERE cat.Estado = 'A'
                    AND (cat.Fecha BETWEEN ? AND ?)
                    AND ((cat.HoraDesde <= ? AND ? < cat.HoraHasta)
                        OR (cat.HoraDesde < ? AND ? <= cat.HoraHasta)
                        OR (? <= cat.HoraDesde AND cat.HoraDesde < ?)
                        OR (? < cat.HoraHasta AND cat.HoraHasta <= ?));";
            $stmt = $db->prepare($sql);
            $horaInicio = number_format($horaInicio, 2);
            $horaFin = number_format($horaFin, 2);
            $stmt->execute([$idAula, $idDia, $fechaDesde, $fechaFin, $horaInicio, $horaInicio, $horaFin, $horaFin, $horaInicio, $horaFin, $horaInicio, $horaFin]);
            $datos = $stmt->fetchAll();
            if (count($datos) > 0) {
                //hay turnos asignados, no se permite cargar
                $result = false;
            } else {
                //NO hay turnos asignados, se permite cargar
                $result = true;
            }
        } catch (PDOException $e) {
            $result = false;
        }
        return $result;
    }

    public function obtenerFechaPorDia($fechaDesde, $diaPedido) {
        $fecha = strtotime($fechaDesde);

        $dia = date('w', $fecha);
        if ($dia == $diaPedido) {
            $fechaComienzo = $fechaDesde;
        } else {
            $fecha = $fechaDesde;
            while ($dia != $diaPedido) {
                $fecha = $this->sumarFecha($fecha, 1, "+", "day");
                $dia = $this->obtenerNombreDia($fecha);
            }
            $fechaComienzo = $fecha;
        }
        return $fechaComienzo;
    }

    public function sumarFecha($fecha, $cantidad, $operacion, $tipo) {
        $nuevafecha = strtotime($operacion . $cantidad . " " . $tipo, strtotime($fecha));
        $nuevafecha = date('Y-m-j', $nuevafecha);

        return $nuevafecha;
    }

    public function obtenerNombreDia($fecha) {
        $fecha = strtotime($fecha);

        return date('w', $fecha);
    }

}
