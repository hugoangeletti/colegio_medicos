<?php
class colegiadoRematriculacionLogic {

    public function obtenerRematriculaciones() {
        try {
            $db = Database::getConnection();
            $sql = "SELECT ra.idRematriculacionAnual, ra.Anio, ra.Estado FROM rematriculacionanual ra WHERE ra.Estado <> 'B'";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $resultado = array();
            if ($stmt->rowCount() > 0 || count($rows) > 0) {
                $datos = array();
                foreach ($rows as $r) {
                    $row = array(
                        'idRematriculacionAnual' => $r['idRematriculacionAnual'],
                        'anio' => $r['Anio'],
                        'estado' => $r['Estado']
                    );
                    array_push($datos, $row);
                }
                $resultado['estado'] = TRUE;
                $resultado['mensaje'] = "OK";
                $resultado['datos'] = $datos;
                $resultado['clase'] = 'alert alert-success';
                $resultado['icono'] = 'glyphicon glyphicon-ok';
            } else {
                $resultado['estado'] = TRUE;
                $resultado['datos'] = NULL;
                $resultado['mensaje'] = "No hay rematriculaciones";
                $resultado['clase'] = 'alert alert-warning';
                $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
            }
        } catch (PDOException $e) {
            $resultado['estado'] = false;
            $resultado['mensaje'] = "Error buscando rematriculaciones";
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-remove';
        }

        return $resultado;
    }

    public function obtenerRematriculacionVigente() {
        try {
            $db = Database::getConnection();
            $sql = "SELECT idRematriculacionAnual, Anio
                FROM rematriculacionanual
                WHERE Estado = 'A'";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            $r = $stmt->fetch(PDO::FETCH_ASSOC);

            $resultado = array();
            if ($r !== false) {
                $datos = array(
                    'idRematriculacionAnual' => $r['idRematriculacionAnual'],
                    'anio' => $r['Anio']
                );
                $resultado['estado'] = TRUE;
                $resultado['mensaje'] = "OK";
                $resultado['datos'] = $datos;
                $resultado['clase'] = 'alert alert-success';
                $resultado['icono'] = 'glyphicon glyphicon-ok';
            } else {
                $resultado['estado'] = FALSE;
                $resultado['datos'] = array();
                $resultado['mensaje'] = "No hay Rematriculación vigente";
                $resultado['clase'] = 'alert alert-warning';
                $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
            }
        } catch (PDOException $e) {
            $resultado['estado'] = false;
            $resultado['mensaje'] = "Error buscando Rematriculación";
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-remove';
        }

        return $resultado;
    }

    public function obtenerUltimaRematriculacionPorIdColegiado($idColegiado) {
        try {
            $db = Database::getConnection();
            $sql = "SELECT idRematriculacionColegiado, fecha
                FROM rematriculacioncolegiado
                WHERE idColegiado = ?
                ORDER BY idRematriculacionColegiado desc
                LIMIT 1";
            $stmt = $db->prepare($sql);
            $stmt->execute([$idColegiado]);
            $r = $stmt->fetch(PDO::FETCH_ASSOC);

            $resultado = array();
            if ($r !== false) {
                $datos = array(
                    'idRematriculacionColegiado' => $r['idRematriculacionColegiado'],
                    'fecha' => $r['fecha']
                );
                $resultado['estado'] = TRUE;
                $resultado['mensaje'] = "OK";
                $resultado['datos'] = $datos;
                $resultado['clase'] = 'alert alert-success';
                $resultado['icono'] = 'glyphicon glyphicon-ok';
            } else {
                $resultado['estado'] = FALSE;
                $resultado['datos'] = NULL;
                $resultado['mensaje'] = "El colegiado no tiene Rematriculación";
                $resultado['clase'] = 'alert alert-warning';
                $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
            }
        } catch (PDOException $e) {
            $resultado['estado'] = false;
            $resultado['mensaje'] = "Error buscando Rematriculación";
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-remove';
        }

        return $resultado;
    }

    public function obtenerColegiadosPorIdRematriculacion($idRematriculacionAnual) {
        try {
            $db = Database::getConnection();
            $sql = "SELECT rc.idRematriculacionColegiado, rc.fecha, c.Id, c.Matricula, p.Apellido, p.Nombres, tm.Id, tm.Detalle
                FROM rematriculacioncolegiado rc
                INNER JOIN colegiado c ON c.Id = rc.idColegiado
                INNER JOIN persona p ON p.Id = c.IdPersona
                INNER JOIN tipomovimiento tm ON tm.Id = c.Estado
                WHERE rc.idRematriculacionAnual = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$idRematriculacionAnual]);
            $rows = $stmt->fetchAll(PDO::FETCH_NUM);

            $resultado = array();
            if (count($rows) > 0) {
                $datos = array();
                foreach ($rows as $r) {
                    $row = array(
                        'idRematriculacionColegiado' => $r[0],
                        'fechaCarga' => $r[1],
                        'idColegiado' => $r[2],
                        'matricula' => $r[3],
                        'apellido' => $r[4],
                        'nombre' => $r[5],
                        'estadoMatricular' => $r[6],
                        'detalleMovimiento' => $r[7]
                    );
                    array_push($datos, $row);
                }
                $resultado['estado'] = TRUE;
                $resultado['mensaje'] = "OK";
                $resultado['datos'] = $datos;
                $resultado['clase'] = 'alert alert-success';
                $resultado['icono'] = 'glyphicon glyphicon-ok';
            } else {
                $resultado['estado'] = TRUE;
                $resultado['datos'] = NULL;
                $resultado['mensaje'] = "No hay colegiados en la rematriculacion";
                $resultado['clase'] = 'alert alert-warning';
                $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
            }
        } catch (PDOException $e) {
            $resultado['estado'] = false;
            $resultado['mensaje'] = "Error buscando colegiados en la rematriculacion";
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-remove';
        }

        return $resultado;
    }

    public function obtenerDomicilioProfesionalPorIdColegiado($idColegiado) {
        try {
            $db = Database::getConnection();
            $sql = "SELECT cdp.Id, cdp.Entidad, cdp.Calle, cdp.Lateral, cdp.Numero, cdp.Piso, cdp.Departamento, cdp.IdLocalidad, cdp.CodigoPostal, cdp.Telefono, cdp.TelefonoFax, cdp.IdEntidad, l.Nombre AS NombreLocalidad, e.Nombre AS NombreEntidad, cdp.FechaCarga
                FROM colegiadodomicilioprofesional cdp
                LEFT JOIN localidad l ON l.Id = cdp.IdLocalidad
                LEFT JOIN entidad e ON e.Id = cdp.IdEntidad
                WHERE cdp.IdColegiado = ? and cdp.IdEstado = 1";
            $stmt = $db->prepare($sql);
            $stmt->execute([$idColegiado]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $resultado = array();
            if (count($rows) > 0) {
                $datos = array();
                foreach ($rows as $r) {
                    $row = array(
                        'id' => $r['Id'],
                        'entidad' => $r['Entidad'],
                        'calle' => $r['Calle'],
                        'lateral' => $r['Lateral'],
                        'numero' => $r['Numero'],
                        'piso' => $r['Piso'],
                        'departamento' => $r['Departamento'],
                        'codigoPostal' => $r['CodigoPostal'],
                        'telefono1' => $r['Telefono'],
                        'telefono2' => $r['TelefonoFax'],
                        'fechaCarga' => $r['FechaCarga'],
                        'idEntidad' => $r['IdEntidad'],
                        'nombreLocalidad' => $r['NombreLocalidad'],
                        'nombreEntidad' => $r['NombreEntidad']
                    );
                    array_push($datos, $row);
                }
                $resultado['estado'] = TRUE;
                $resultado['mensaje'] = "OK";
                $resultado['datos'] = $datos;
                $resultado['clase'] = 'alert alert-success';
                $resultado['icono'] = 'glyphicon glyphicon-ok';
            } else {
                $resultado['estado'] = TRUE;
                $resultado['datos'] = NULL;
                $resultado['mensaje'] = "No tiene consultorios declarados";
                $resultado['clase'] = 'alert alert-warning';
                $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
            }
        } catch (PDOException $e) {
            $resultado['estado'] = false;
            $resultado['mensaje'] = "Error buscando consultorios";
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-remove';
        }

        return $resultado;
    }

    public function obtenerDomicilioProfesionalPorIdRematriculacionColegiado($idRematriculacionColegiado) {
        try {
            $db = Database::getConnection();
            $sql = "SELECT cdp.IdColegiado, cdp.Entidad, cdp.Calle, cdp.Lateral, cdp.Numero, cdp.Piso, cdp.Departamento, cdp.IdLocalidad, cdp.CodigoPostal, cdp.Telefono, cdp.IdEntidad, l.Nombre AS NombreLocalidad, e.Nombre AS NombreEntidad, cdp.FechaCarga
                FROM colegiadodomicilioprofesional cdp
                LEFT JOIN localidad l ON l.Id = cdp.IdLocalidad
                LEFT JOIN entidad e ON e.Id = cdp.IdEntidad
                WHERE cdp.Id = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$idRematriculacionColegiado]);
            $r = $stmt->fetch(PDO::FETCH_ASSOC);

            $resultado = array();
            if ($r !== false) {
                $datos = array(
                    'idColegiado' => $r['IdColegiado'],
                    'entidad' => $r['Entidad'],
                    'calle' => $r['Calle'],
                    'lateral' => $r['Lateral'],
                    'numero' => $r['Numero'],
                    'piso' => $r['Piso'],
                    'departamento' => $r['Departamento'],
                    'codigoPostal' => $r['CodigoPostal'],
                    'telefono' => $r['Telefono'],
                    'fechaCarga' => $r['FechaCarga'],
                    'idEntidad' => $r['IdEntidad'],
                    'idLocalidad' => $r['IdLocalidad'],
                    'nombreLocalidad' => $r['NombreLocalidad'],
                    'nombreEntidad' => $r['NombreEntidad']
                );
                $resultado['estado'] = TRUE;
                $resultado['mensaje'] = "OK";
                $resultado['datos'] = $datos;
                $resultado['clase'] = 'alert alert-success';
                $resultado['icono'] = 'glyphicon glyphicon-ok';
            } else {
                $resultado['estado'] = FALSE;
                $resultado['datos'] = NULL;
                $resultado['mensaje'] = "No se encontró consultorio declarado en Rematriculación";
                $resultado['clase'] = 'alert alert-warning';
                $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
            }
        } catch (PDOException $e) {
            $resultado['estado'] = false;
            $resultado['mensaje'] = "Error buscando consultorio declarado en Rematriculación (".$idRematriculacionColegiado.")";
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-remove';
        }

        return $resultado;
    }

    public function obtenerConsultorioDeclaradoPorId($idConsultorio) {
        try {
            $db = Database::getConnection();
            $sql = "SELECT cdp.IdColegiado, cdp.Entidad, cdp.Calle, cdp.Lateral, cdp.Numero, cdp.Piso, cdp.Departamento, cdp.IdLocalidad, cdp.CodigoPostal, cdp.Telefono, cdp.IdEntidad, l.Nombre AS NombreLocalidad, e.Nombre AS NombreEntidad, cdp.FechaCarga
                FROM colegiadodomicilioprofesional cdp
                LEFT JOIN localidad l ON l.Id = cdp.IdLocalidad
                LEFT JOIN entidad e ON e.Id = cdp.IdEntidad
                WHERE cdp.Id = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$idConsultorio]);
            $r = $stmt->fetch(PDO::FETCH_ASSOC);

            $resultado = array();
            if ($r !== false) {
                $datos = array(
                    'idColegiado' => $r['IdColegiado'],
                    'entidad' => $r['Entidad'],
                    'calle' => $r['Calle'],
                    'lateral' => $r['Lateral'],
                    'numero' => $r['Numero'],
                    'piso' => $r['Piso'],
                    'departamento' => $r['Departamento'],
                    'codigoPostal' => $r['CodigoPostal'],
                    'telefono' => $r['Telefono'],
                    'fechaCarga' => $r['FechaCarga'],
                    'idEntidad' => $r['IdEntidad'],
                    'idLocalidad' => $r['IdLocalidad'],
                    'nombreLocalidad' => $r['NombreLocalidad'],
                    'nombreEntidad' => $r['NombreEntidad']
                );
                $resultado['estado'] = TRUE;
                $resultado['mensaje'] = "OK";
                $resultado['datos'] = $datos;
                $resultado['clase'] = 'alert alert-success';
                $resultado['icono'] = 'glyphicon glyphicon-ok';
            } else {
                $resultado['estado'] = FALSE;
                $resultado['datos'] = NULL;
                $resultado['mensaje'] = "No se encontró el consultorio declarado";
                $resultado['clase'] = 'alert alert-warning';
                $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
            }
        } catch (PDOException $e) {
            $resultado['estado'] = false;
            $resultado['mensaje'] = "Error buscando consultorio declarado (".$idConsultorio.")";
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-remove';
        }

        return $resultado;
    }

    public function obtenerActividadAsistencialPorIdRematriculacionColegiado($idRematriculacionColegiado) {
        try {
            $db = Database::getConnection();
            $sql = "SELECT aa.IdColegiadoActividadAsistencial, aa.TipoInstitucion, aa.IdEntidad, aa.Cargo, aa.Servicio, aa.FechaDesdeHasta, aa.FechaCarga, aa.NombreInstitucion, te.Nombre AS NombreTipoEntidad, e.Nombre AS NombreEntidad
                FROM actividadasistencial aa
                LEFT JOIN entidad e ON(e.Id = aa.IdEntidad)
                LEFT JOIN tipoentidad te ON(te.Id = e.IdTipoEntidad)
                WHERE aa.IdRematriculacionColegiado = ? AND aa.Estado = 'A'";
            $stmt = $db->prepare($sql);
            $stmt->execute([$idRematriculacionColegiado]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $resultado = array();
            if (count($rows) > 0) {
                $datos = array();
                foreach ($rows as $r) {
                    $tipoInstitucion = $r['TipoInstitucion'];
                    if ($tipoInstitucion == '1') {
                        $tipoInstitucionDetalle = 'Pública';
                    } else {
                        if ($tipoInstitucion == '2') {
                            $tipoInstitucionDetalle = 'Privada';
                        } else {
                            $tipoInstitucionDetalle = 'Sin informar';
                        }
                    }
                    $row = array(
                        'idActividadAsistencial' => $r['IdColegiadoActividadAsistencial'],
                        'tipoInstitucion' => $tipoInstitucion,
                        'tipoInstitucionDetalle' => $tipoInstitucionDetalle,
                        'idEntidad' => $r['IdEntidad'],
                        'cargo' => $r['Cargo'],
                        'servicio' => $r['Servicio'],
                        'fechaDesdeHasta' => $r['FechaDesdeHasta'],
                        'fechaCarga' => $r['FechaCarga'],
                        'nombreInstitucion' => $r['NombreInstitucion'],
                        'tipoEntidad' => $r['NombreTipoEntidad'],
                        'nombreEntidad' => $r['NombreEntidad']
                    );
                    array_push($datos, $row);
                }
                $resultado['estado'] = TRUE;
                $resultado['mensaje'] = "OK";
                $resultado['datos'] = $datos;
                $resultado['clase'] = 'alert alert-success';
                $resultado['icono'] = 'glyphicon glyphicon-ok';
            } else {
                $resultado['estado'] = TRUE;
                $resultado['datos'] = NULL;
                $resultado['mensaje'] = "No tiene actividad asistencial declarada";
                $resultado['clase'] = 'alert alert-warning';
                $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
            }
        } catch (PDOException $e) {
            $resultado['estado'] = false;
            $resultado['mensaje'] = "Error buscando actividad asistencial";
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-remove';
        }

        return $resultado;
    }

    public function obtenerActividadAsistencialPorIdColegiado($idColegiado) {
        try {
            $db = Database::getConnection();
            $sql = "SELECT aa.IdColegiadoActividadAsistencial, aa.TipoInstitucion, aa.IdEntidad, aa.Cargo, aa.Servicio, aa.FechaDesdeHasta, aa.FechaCarga, aa.NombreInstitucion, te.Nombre AS NombreTipoEntidad, e.Nombre AS NombreEntidad
                FROM actividadasistencial aa
                LEFT JOIN entidad e ON(e.Id = aa.IdEntidad)
                LEFT JOIN tipoentidad te ON(te.Id = e.IdTipoEntidad)
                WHERE aa.IdColegiado = ? AND aa.Estado = 'A'";
            $stmt = $db->prepare($sql);
            $stmt->execute([$idColegiado]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $resultado = array();
            if (count($rows) > 0) {
                $datos = array();
                foreach ($rows as $r) {
                    $tipoInstitucion = $r['TipoInstitucion'];
                    if ($tipoInstitucion == '1') {
                        $tipoInstitucionDetalle = 'Pública';
                    } else {
                        if ($tipoInstitucion == '2') {
                            $tipoInstitucionDetalle = 'Privada';
                        } else {
                            $tipoInstitucionDetalle = 'Sin informar';
                        }
                    }
                    $row = array(
                        'idActividadAsistencial' => $r['IdColegiadoActividadAsistencial'],
                        'tipoInstitucion' => $tipoInstitucion,
                        'tipoInstitucionDetalle' => $tipoInstitucionDetalle,
                        'idEntidad' => $r['IdEntidad'],
                        'cargo' => $r['Cargo'],
                        'servicio' => $r['Servicio'],
                        'fechaDesdeHasta' => $r['FechaDesdeHasta'],
                        'fechaCarga' => $r['FechaCarga'],
                        'nombreInstitucion' => $r['NombreInstitucion'],
                        'tipoEntidad' => $r['NombreTipoEntidad'],
                        'nombreEntidad' => $r['NombreEntidad']
                    );
                    array_push($datos, $row);
                }
                $resultado['estado'] = TRUE;
                $resultado['mensaje'] = "OK";
                $resultado['datos'] = $datos;
                $resultado['clase'] = 'alert alert-success';
                $resultado['icono'] = 'glyphicon glyphicon-ok';
            } else {
                $resultado['estado'] = TRUE;
                $resultado['datos'] = NULL;
                $resultado['mensaje'] = "No tiene actividad asistencial declarada";
                $resultado['clase'] = 'alert alert-warning';
                $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
            }
        } catch (PDOException $e) {
            $resultado['estado'] = false;
            $resultado['mensaje'] = "Error buscando actividad asistencial";
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-remove';
        }

        return $resultado;
    }

    public function obtenerActividadAsistencialPorId($idActividadAsistencial) {
        try {
            $db = Database::getConnection();
            $sql = "SELECT aa.IdColegiado, aa.TipoInstitucion, aa.IdEntidad, aa.Cargo, aa.Servicio, aa.FechaDesdeHasta, aa.FechaCarga, aa.NombreInstitucion, te.Nombre AS NombreTipoEntidad, e.Nombre AS NombreEntidad
                FROM actividadasistencial aa
                LEFT JOIN entidad e ON(e.Id = aa.IdEntidad)
                LEFT JOIN tipoentidad te ON(te.Id = e.IdTipoEntidad)
                WHERE aa.IdColegiadoActividadAsistencial = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$idActividadAsistencial]);
            $r = $stmt->fetch(PDO::FETCH_ASSOC);

            $resultado = array();
            if ($r !== false) {
                $tipoInstitucion = $r['TipoInstitucion'];
                if ($tipoInstitucion == '1') {
                    $tipoInstitucionDetalle = 'Pública';
                } else {
                    if ($tipoInstitucion == '2') {
                        $tipoInstitucionDetalle = 'Privada';
                    } else {
                        $tipoInstitucionDetalle = 'Sin informar';
                    }
                }
                $datos = array(
                    'idColegiado' => $r['IdColegiado'],
                    'tipoInstitucion' => $tipoInstitucion,
                    'tipoInstitucionDetalle' => $tipoInstitucionDetalle,
                    'idEntidad' => $r['IdEntidad'],
                    'cargo' => $r['Cargo'],
                    'servicio' => $r['Servicio'],
                    'fechaDesdeHasta' => $r['FechaDesdeHasta'],
                    'fechaCarga' => $r['FechaCarga'],
                    'nombreInstitucion' => $r['NombreInstitucion'],
                    'tipoEntidad' => $r['NombreTipoEntidad'],
                    'nombreEntidad' => $r['NombreEntidad']
                );
                $resultado['estado'] = TRUE;
                $resultado['mensaje'] = "OK";
                $resultado['datos'] = $datos;
                $resultado['clase'] = 'alert alert-success';
                $resultado['icono'] = 'glyphicon glyphicon-ok';
            } else {
                $resultado['estado'] = FALSE;
                $resultado['datos'] = NULL;
                $resultado['mensaje'] = "No se encontró el consultorio declarado (".$idActividadAsistencial.")";
                $resultado['clase'] = 'alert alert-warning';
                $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
            }
        } catch (PDOException $e) {
            $resultado['estado'] = false;
            $resultado['mensaje'] = "Error buscando consultorio declarado (".$idActividadAsistencial.")";
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-remove';
        }

        return $resultado;
    }

    public function obtenerEspecialidadesPorIdRematriculacionColegiado($idRematriculacionColegiado) {
        try {
            $db = Database::getConnection();
            $sql = "SELECT especialidaddeclarada.IdEspecialidadDeclarada, especialidaddeclarada.IdEspecialidad,
                especialidaddeclarada.Fecha, especialidaddeclarada.NombreEntidad, especialidaddeclarada.IdOrigenWeb,
                especialidad.Especialidad
                FROM especialidaddeclarada
                INNER JOIN especialidad ON(especialidad.Id = especialidaddeclarada.IdEspecialidad)
                WHERE especialidaddeclarada.IdRematriculacionColegiado = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$idRematriculacionColegiado]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $resultado = array();
            if (count($rows) > 0) {
                $datos = array();
                foreach ($rows as $r) {
                    $row = array(
                        'idEspecialidadDeclarada' => $r['IdEspecialidadDeclarada'],
                        'idEspecialidad' => $r['IdEspecialidad'],
                        'fecha' => $r['Fecha'],
                        'nombreEntidad' => $r['NombreEntidad'],
                        'idOrigenWeb' => $r['IdOrigenWeb'],
                        'especialidad' => $r['Especialidad']
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
                $resultado['mensaje'] = "No tiene especialidad declarada";
                $resultado['clase'] = 'alert alert-warning';
                $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
            }
        } catch (PDOException $e) {
            $resultado['estado'] = false;
            $resultado['mensaje'] = "Error buscando especialidades";
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-remove';
        }

        return $resultado;
    }

    public function guardarConsultorioDeclarado($idConsultorio, $idColegiado, $calle, $numero, $piso, $departamento, $lateral, $idEntidad, $idLocalidad, $codigoPostal, $telefono) {
        try {
            $db = Database::getConnection();
            if (isset($idConsultorio) && $idConsultorio <> "") {
                //actualiza los datos
                $sql = "UPDATE colegiadodomicilioprofesional
                        SET Calle = ?, Numero = ?, Piso = ?, Departamento = ?, Lateral = ?, IdLocalidad = ?, CodigoPostal = ?, Telefono = ?, FechaCarga = DATE(NOW()), IdUsuario = ?, IdEntidad = ?
                        WHERE Id = ?";
                $stmt = $db->prepare($sql);
                $stmt->execute([$calle, $numero, $piso, $departamento, $lateral, $idLocalidad, $codigoPostal, $telefono, $_SESSION['user_id'], $idEntidad, $idConsultorio]);
            } else {
                $sql = "INSERT INTO colegiadodomicilioprofesional
                        (IdColegiado, IdEstado, Calle, Numero, Piso, Departamento, Lateral, IdLocalidad, CodigoPostal, Telefono, FechaCarga, IdUsuario, IdEntidad)
                        VALUES (?, 1, ?, ?, ?, ?, ?, ?, ?, ?, DATE(NOW()), ?, ?)";
                $stmt = $db->prepare($sql);
                $stmt->execute([$idColegiado, $calle, $numero, $piso, $departamento, $lateral, $idLocalidad, $codigoPostal, $telefono, $_SESSION['user_id'], $idEntidad]);
            }
            $resultado = array();
            $resultado['estado'] = TRUE;
            $resultado['clase'] = 'alert alert-success';
            $resultado['mensaje'] = "OK";
        } catch (PDOException $e) {
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "Error guardando consultorio declarado. ".$e->getMessage();
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-remove';
        }

        return $resultado;
    }

    public function borrarConsultorioDeclarado($idConsultorio, $idEstado) {
        try {
            $db = Database::getConnection();
            //actualiza los datos
            $sql = "UPDATE colegiadodomicilioprofesional
                    SET IdEstado = ?, FechaBaja = NOW(), IdUsuarioBaja = ?
                    WHERE Id = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$idEstado, $_SESSION['user_id'], $idConsultorio]);
            $resultado = array();
            $resultado['estado'] = TRUE;
            $resultado['clase'] = 'alert alert-success';
            $resultado['mensaje'] = "OK";
        } catch (PDOException $e) {
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "Error borrando consultorio declarado. ".$e->getMessage();
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-remove';
        }

        return $resultado;
    }

    public function guardarActividadAsistencial($idActividadAsistencial, $idColegiado, $tipoInstitucion, $idEntidad, $cargo, $servicio, $fechaDesdeHasta) {
        try {
            $db = Database::getConnection();
            if (isset($idActividadAsistencial) && $idActividadAsistencial <> "") {
                //actualiza los datos
                $sql = "UPDATE actividadasistencial
                        SET TipoInstitucion = ?, IdEntidad = ?, Cargo = ?, Servicio = ?, FechaDesdeHasta = ?, FechaCarga = DATE(NOW()), Estado = 'A', IdUsuarioCarga = ?
                        WHERE IdColegiadoActividadAsistencial = ?";
                $stmt = $db->prepare($sql);
                $stmt->execute([$tipoInstitucion, $idEntidad, $cargo, $servicio, $fechaDesdeHasta, $_SESSION['user_id'], $idActividadAsistencial]);
            } else {
                $sql = "INSERT INTO actividadasistencial
                        (IdColegiado, TipoInstitucion, IdEntidad, Cargo, Servicio, FechaDesdeHasta, FechaCarga, Estado, IdUsuarioCarga)
                        VALUES (?, ?, ?, ?, ?, ?, DATE(NOW()), 'A', ?)";
                $stmt = $db->prepare($sql);
                $stmt->execute([$idColegiado, $tipoInstitucion, $idEntidad, $cargo, $servicio, $fechaDesdeHasta, $_SESSION['user_id']]);
            }
            $resultado = array();
            $resultado['estado'] = TRUE;
            $resultado['clase'] = 'alert alert-success';
            $resultado['mensaje'] = "OK";
        } catch (PDOException $e) {
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "Error guardando actividad asistencial. ".$e->getMessage();
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-remove';
        }

        return $resultado;
    }

    public function borrarActividadAsistencial($idActividadAsistencial, $estado) {
        try {
            $db = Database::getConnection();
            //actualiza los datos
            $sql = "UPDATE actividadasistencial
                    SET Estado = ?, FechaBaja = NOW(), IdUsuarioBaja = ?
                    WHERE IdColegiadoActividadAsistencial = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$estado, $_SESSION['user_id'], $idActividadAsistencial]);
            $resultado = array();
            $resultado['estado'] = TRUE;
            $resultado['clase'] = 'alert alert-success';
            $resultado['mensaje'] = "OK";
        } catch (PDOException $e) {
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "Error borrando actividad asistencial. ".$e->getMessage();
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-remove';
        }

        return $resultado;
    }

}
