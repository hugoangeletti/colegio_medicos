<?php
class mesaEntradaLogic {

    function obtenerMesaEntradaPorId($idMesaEntrada) {
        $db = Database::getConnection();
        $sql="SELECT me.IdMesaEntrada, me.TipoRemitente, me.IdColegiado, me.IdRemitente, me.IdTipoMesaEntrada, me.FechaIngreso, me.FechaCierre, me.Estado, me.IdUsuario, me.Observaciones, me.EstadoMatricular, me.EstadoTesoreria, me.IdTipoPago, me.Pagado, u.Usuario, tm.Estado
			FROM mesaentrada me
            LEFT JOIN usuario u ON u.Id = me.IdUsuario
            LEFT JOIN tipomovimiento tm ON tm.Id = me.EstadoMatricular
			WHERE me.IdMesaEntrada = ?";
        $resultado = array();
        try {
            $stmt = $db->prepare($sql);
            $stmt->execute([$idMesaEntrada]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row !== false) {
                $datos = array(
                            'idMesaEntrada' => $row['IdMesaEntrada'],
                            'tipoRemitente' => $row['TipoRemitente'],
                            'idColegiado' => $row['IdColegiado'],
                            'idRemitente' => $row['IdRemitente'],
                            'idTipoMesaEntrada' => $row['IdTipoMesaEntrada'],
                            'fechaIngreso' => $row['FechaIngreso'],
                            'fechaCierre' => $row['FechaCierre'],
                            'estado' => $row['Estado'],
                            'idUsuario' => $row['IdUsuario'],
                            'observaciones' => $row['Observaciones'],
                            'estadoMatricular' => $row['EstadoMatricular'],
                            'estadoTesoreria' => $row['EstadoTesoreria'],
                            'idTipoPago' => $row['IdTipoPago'],
                            'pagado' => $row['Pagado'],
                            'nombreUsuario' => $row['Usuario'],
                            'tipoEstado' => $row['Estado']
                            );
                $resultado['estado'] = true;
                $resultado['mensaje'] = "OK";
                $resultado['datos'] = $datos;
                $resultado['clase'] = 'alert alert-success';
                $resultado['icono'] = 'glyphicon glyphicon-ok';
            } else {
                $resultado['estado'] = false;
                $resultado['mensaje'] = "No hay mesa de entrada";
                $resultado['clase'] = 'alert alert-info';
                $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
            }
        } catch (PDOException $e) {
            $resultado['estado'] = false;
            $resultado['mensaje'] = "Error buscando mesa de entrada";
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-remove';
        }

        return $resultado;
    }

    function obtenerMesaEntradaMovimientoPorId($idMesaEntrada) {
        $db = Database::getConnection();
        $sql="SELECT mem.IdMesaEntradaMovimiento, me.IdColegiado, me.FechaIngreso, me.FechaCierre, me.Estado, me.IdUsuario, me.Observaciones, mem.IdTipoMovimiento, tm.Detalle, mem.Fecha, tm.DetalleCompleto, mem.IdMotivoCancelacion, mc.Nombre, mem.Distrito, mem.ObraSocialJubilado, mem.IdPatologia, p.Nombre AS NombrePatologia, me.EstadoMatricular, me.EstadoTesoreria, tm1.DetalleCompleto AS DetalleCompletoOriginal
            FROM mesaentrada me
            INNER JOIN mesaentradamovimiento mem ON mem.IdMesaEntrada = me.IdMesaEntrada
            INNER JOIN tipomovimiento tm ON tm.Id = mem.IdTipoMovimiento
            LEFT JOIN tipomovimiento tm1 ON tm1.Id = me.EstadoMatricular
            INNER JOIN motivocancelacion mc ON mc.IdMotivoCancelacion = mem.IdMotivoCancelacion
            LEFT JOIN patologia p ON p.Id = mem.IdPatologia
            WHERE me.IdMesaEntrada = ?";
        $resultado = array();
        try {
            $stmt = $db->prepare($sql);
            $stmt->execute([$idMesaEntrada]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row !== false) {
                $datos = array(
                            'idMesaEntradaMovimiento' => $row['IdMesaEntradaMovimiento'],
                            'idColegiado' => $row['IdColegiado'],
                            'fechaIngreso' => $row['FechaIngreso'],
                            'fechaCierre' => $row['FechaCierre'],
                            'estado' => $row['Estado'],
                            'idUsuario' => $row['IdUsuario'],
                            'observaciones' => $row['Observaciones'],
                            'idTipoMovimiento' => $row['IdTipoMovimiento'],
                            'nombreTipoMovimiento' => $row['Detalle'],
                            'fechaMovimiento' => $row['Fecha'],
                            'nombreTipoMovimientoCompleto' => $row['DetalleCompleto'],
                            'idMotivoCancelacion' => $row['IdMotivoCancelacion'],
                            'nombreMotivoCancelacion' => $row['Nombre'],
                            'distrito' => $row['Distrito'],
                            'obraSocialJubilado' => $row['ObraSocialJubilado'],
                            'idPatologia' => $row['IdPatologia'],
                            'nombrePatologia' => $row['NombrePatologia'],
                            'estadoMatricular' => $row['EstadoMatricular'],
                            'estadoTesoreria' => $row['EstadoTesoreria'],
                            'estadoMatricularOriginal' => $row['DetalleCompletoOriginal']
                            );
                $resultado['estado'] = true;
                $resultado['mensaje'] = "OK";
                $resultado['datos'] = $datos;
                $resultado['clase'] = 'alert alert-success';
                $resultado['icono'] = 'glyphicon glyphicon-ok';
            } else {
                $resultado['estado'] = false;
                $resultado['mensaje'] = "No hay mesa de entrada";
                $resultado['clase'] = 'alert alert-info';
                $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
            }
        } catch (PDOException $e) {
            $resultado['estado'] = false;
            $resultado['mensaje'] = "Error buscando mesa de entrada";
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-remove';
        }

        return $resultado;
    }

    function obtenerMesaEntradaAnulacionMovimientoPorId($idMesaEntrada) {
        $db = Database::getConnection();
        $sql="SELECT mema.IdMesaEntradaMovimientoAnulacion, me.IdColegiado, me.FechaIngreso, me.Estado, me.Observaciones, mem.IdTipoMovimiento, tm.Detalle, mem.Fecha, tm.DetalleCompleto, me.EstadoMatricular, me.EstadoTesoreria, tm.Estado AS EstadoTipoMovimiento
            FROM mesaentrada me
            INNER JOIN mesaentradamovimientoanulacion mema ON mema.IdMesaEntrada = me.IdMesaEntrada
            INNER JOIN mesaentradamovimiento mem ON mem.IdMesaEntradaMovimiento = mema.IdMesaEntradaMovimiento
            INNER JOIN tipomovimiento tm ON tm.Id = mem.IdTipoMovimiento
                WHERE me.IdMesaEntrada = ?";
        $resultado = array();
        try {
            $stmt = $db->prepare($sql);
            $stmt->execute([$idMesaEntrada]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row !== false) {
                $datos = array(
                            'idMesaEntradaMovimientoAnulado' => $row['IdMesaEntradaMovimientoAnulacion'],
                            'idColegiado' => $row['IdColegiado'],
                            'fechaIngreso' => $row['FechaIngreso'],
                            'estadoMesaEntrada' => $row['Estado'],
                            'observaciones' => $row['Observaciones'],
                            'idTipoMovimiento' => $row['IdTipoMovimiento'],
                            'nombreTipoMovimiento' => $row['Detalle'],
                            'fechaMovimiento' => $row['Fecha'],
                            'nombreTipoMovimientoCompleto' => $row['DetalleCompleto'],
                            'estadoMatricular' => $row['EstadoMatricular'],
                            'estadoTesoreria' => $row['EstadoTesoreria'],
                            'estadoTipoMovimiento' => $row['EstadoTipoMovimiento']
                            );
                $resultado['estado'] = true;
                $resultado['mensaje'] = "OK";
                $resultado['datos'] = $datos;
                $resultado['clase'] = 'alert alert-success';
                $resultado['icono'] = 'glyphicon glyphicon-ok';
            } else {
                $resultado['estado'] = false;
                $resultado['mensaje'] = "No hay mesa de entrada";
                $resultado['clase'] = 'alert alert-info';
                $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
            }
        } catch (PDOException $e) {
            $resultado['estado'] = false;
            $resultado['mensaje'] = "Error buscando mesa de entrada";
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-remove';
        }

        return $resultado;
    }

    function obtenerMesaEntradaEspecialidadPorId($idMesaEntrada) {
        $db = Database::getConnection();
        $sql="SELECT mee.IdMesaEntradaEspecialidad, me.IdColegiado, me.FechaIngreso, me.FechaCierre, me.Estado, me.IdUsuario, me.Observaciones, mee.IdEspecialidad, e.Especialidad, mee.TipoEspecialidad, mee.NumeroExpediente, mee.AnioExpediente, mee.Distrito, mee.IncisoArticulo8, mee.IdTipoEspecialista, te.Nombre
            FROM mesaentrada me
            INNER JOIN mesaentradaespecialidad mee ON mee.IdMesaEntrada = me.IdMesaEntrada
            INNER JOIN especialidad e ON e.Id = mee.IdEspecialidad
            INNER JOIN tipoespecialista te ON te.IdTipoEspecialista = mee.IdTipoEspecialista
            WHERE me.IdMesaEntrada = ?";
        $resultado = array();
        try {
            $stmt = $db->prepare($sql);
            $stmt->execute([$idMesaEntrada]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row !== false) {
                $datos = array(
                            'idMesaEntradaEspecialidad' => $row['IdMesaEntradaEspecialidad'],
                            'idColegiado' => $row['IdColegiado'],
                            'fechaIngreso' => $row['FechaIngreso'],
                            'fechaCierre' => $row['FechaCierre'],
                            'estado' => $row['Estado'],
                            'idUsuario' => $row['IdUsuario'],
                            'observaciones' => $row['Observaciones'],
                            'idEspecialidad' => $row['IdEspecialidad'],
                            'nombreEspecialidad' => $row['Especialidad'],
                            'tipoEspecialidad' => $row['TipoEspecialidad'],
                            'numeroExpediente' => $row['NumeroExpediente'],
                            'anioExpediente' => $row['AnioExpediente'],
                            'distrito' => $row['Distrito'],
                            'incisoArticulo8' => $row['IncisoArticulo8'],
                            'idTipoEspecialista' => $row['IdTipoEspecialista'],
                            'nombreTipoEspecialista' => $row['Nombre']
                            );
                $resultado['estado'] = true;
                $resultado['mensaje'] = "OK";
                $resultado['datos'] = $datos;
                $resultado['clase'] = 'alert alert-success';
                $resultado['icono'] = 'glyphicon glyphicon-ok';
            } else {
                $resultado['estado'] = false;
                $resultado['mensaje'] = "No hay mesa de entrada";
                $resultado['clase'] = 'alert alert-info';
                $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
            }
        } catch (PDOException $e) {
            $resultado['estado'] = false;
            $resultado['mensaje'] = "Error buscando mesa de entrada";
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-remove';
        }

        return $resultado;
    }

    function obtenerMesaEntradaConNotaPorId($idMesaEntrada) {
        $db = Database::getConnection();
        $sql="SELECT men.IdMesaEntradaNota, me.TipoRemitente, me.IdColegiado, me.IdRemitente, me.IdTipoMesaEntrada, me.FechaIngreso, me.FechaCierre, me.Estado, me.IdUsuario, me.Observaciones, men.Tema, men.IncluyeMovimiento
            FROM mesaentrada me
            LEFT JOIN mesaentradanota men ON men.IdMesaEntrada = me.IdMesaEntrada
            WHERE me.IdMesaEntrada = ?";
        $resultado = array();
        try {
            $stmt = $db->prepare($sql);
            $stmt->execute([$idMesaEntrada]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row !== false) {
                $datos = array(
                            'idMesaEntradaNota' => $row['IdMesaEntradaNota'],
                            'tipoRemitente' => $row['TipoRemitente'],
                            'idColegiado' => $row['IdColegiado'],
                            'idRemitente' => $row['IdRemitente'],
                            'idTipoMesaEntrada' => $row['IdTipoMesaEntrada'],
                            'fechaIngreso' => $row['FechaIngreso'],
                            'fechaCierre' => $row['FechaCierre'],
                            'estado' => $row['Estado'],
                            'idUsuario' => $row['IdUsuario'],
                            'observaciones' => $row['Observaciones'],
                            'tema' => $row['Tema'],
                            'incluyeMovimiento' => $row['IncluyeMovimiento']
                            );
                $resultado['estado'] = true;
                $resultado['mensaje'] = "OK";
                $resultado['datos'] = $datos;
                $resultado['clase'] = 'alert alert-success';
                $resultado['icono'] = 'glyphicon glyphicon-ok';
            } else {
                $resultado['estado'] = false;
                $resultado['mensaje'] = "No hay mesa de entrada";
                $resultado['clase'] = 'alert alert-info';
                $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
            }
        } catch (PDOException $e) {
            $resultado['estado'] = false;
            $resultado['mensaje'] = "Error buscando mesa de entrada";
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-remove';
        }

        return $resultado;
    }

    function obtenerMesaEntradaNotaPorId($idMesaEntradaNota, $idMesaEntrada) {
        $db = Database::getConnection();
        if (isset($idMesaEntradaNota) && $idMesaEntradaNota <> "") {
            $filtro = " WHERE men.IdMesaEntradaNota = ?";
            $indice = $idMesaEntradaNota;
        } else {
            $filtro = " WHERE men.IdMesaEntrada = ?";
            $indice = $idMesaEntrada;
        }
        $sql="SELECT men.IdMesaEntradaNota, men.IdMesaEntrada, men.Tema, men.IncluyeMovimiento, men.Estado
            FROM mesaentradanota men ".$filtro;
        $resultado = array();
        try {
            $stmt = $db->prepare($sql);
            $stmt->execute([$indice]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row !== false) {
                $datos = array(
                            'idMesaEntradaNota' => $row['IdMesaEntradaNota'],
                            'idMesaEntrada' => $row['IdMesaEntrada'],
                            'tema' => $row['Tema'],
                            'incluyeMovimiento' => $row['IncluyeMovimiento'],
                            'estadoMesaEntradaNota' => $row['Estado']
                            );
                $resultado['estado'] = true;
                $resultado['mensaje'] = "OK";
                $resultado['datos'] = $datos;
                $resultado['clase'] = 'alert alert-success';
                $resultado['icono'] = 'glyphicon glyphicon-ok';
            } else {
                $resultado['estado'] = false;
                $resultado['mensaje'] = "No hay mesa de entrada";
                $resultado['clase'] = 'alert alert-info';
                $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
            }
        } catch (PDOException $e) {
            $resultado['estado'] = false;
            $resultado['mensaje'] = "Error buscando mesa de entrada";
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-remove';
        }

        return $resultado;
    }

    function obtenerMesaEntradaConsultorioPorId($idMesaEntradaConsultorio, $idMesaEntrada) {
        $db = Database::getConnection();
        if (isset($idMesaEntradaConsultorio) && $idMesaEntradaConsultorio <> "") {
            $filtro = " WHERE mec.IdMesaEntradaConsultorio = ?";
            $indice = $idMesaEntradaConsultorio;
        } else {
            $filtro = " WHERE mec.IdMesaEntrada = ?";
            $indice = $idMesaEntrada;
        }
        $sql="SELECT mec.IdMesaEntradaConsultorio, mec.IdMesaEntrada, mec.IdConsultorio, mec.IdEspecialidad, mec.IdEspecialidadAlternativa, mec.Estado, mec.FechaBaja, mec.IdUsuarioBaja, c.TipoConsultorio, c.Nombre, c.Calle, c.Lateral, c.Numero, c.Piso, c.Departamento, c.Telefono, c.CodigoPostal, l.Nombre AS NombreLocalidad, e.Especialidad, e1.Especialidad AS EspecialidadAlternativa, c.IdLocalidad, c.Observaciones
            FROM mesaentradaconsultorio mec
            INNER JOIN consultorio c ON c.IdConsultorio = mec.IdConsultorio
            LEFT JOIN localidad l ON l.Id = c.IdLocalidad
            LEFT JOIN especialidad e ON e.Id = mec.IdEspecialidad
            LEFT JOIN especialidad e1 ON e1.Id = mec.IdEspecialidadAlternativa".$filtro;
        $resultado = array();
        try {
            $stmt = $db->prepare($sql);
            $stmt->execute([$indice]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row !== false) {
                $calle = $row['Calle'];
                $numeroCasa = $row['Numero'];
                $lateral = $row['Lateral'];
                $piso = $row['Piso'];
                $departamento = $row['Departamento'];
                $nombreLocalidad = $row['NombreLocalidad'];

                $domicilio = " ";
                if (isset($calle) && $calle <> "") {
                    $domicilio .= trim($calle);
                }
                if (isset($numeroCasa) && $numeroCasa <> "") {
                    $domicilio .= ' N°'.trim($numeroCasa);
                }
                if (isset($lateral) && $lateral <> "") {
                    $domicilio .= ' ('.trim($lateral).')';
                }
                if (isset($piso) && $piso <> "") {
                    $domicilio .= ' Piso'.trim($piso);
                }
                if (isset($departamento) && $departamento <> "") {
                    $domicilio .= ' Dto.'.trim($departamento);
                }
                if (isset($nombreLocalidad) && $nombreLocalidad <> "") {
                    $domicilio .= ' ('.trim($nombreLocalidad).')';
                }

                $datos = array(
                            'idMesaEntradaConsultorio' => $row['IdMesaEntradaConsultorio'],
                            'idMesaEntrada' => $row['IdMesaEntrada'],
                            'idConsultorio' => $row['IdConsultorio'],
                            'idEspecialidad' => $row['IdEspecialidad'],
                            'idEspecialidadAlternativa' => $row['IdEspecialidadAlternativa'],
                            'estadoMesaEntradaConsultorio' => $row['Estado'],
                            'fechaBaja' => $row['FechaBaja'],
                            'idUsuarioBaja' => $row['IdUsuarioBaja'],
                            'tipoConsultorio' => $row['TipoConsultorio'],
                            'nombreConsultorio' => $row['Nombre'],
                            'calle' => $calle,
                            'lateral' => $lateral,
                            'numeroCasa' => $numeroCasa,
                            'piso' => $piso,
                            'departamento' => $departamento,
                            'telefono' => $row['Telefono'],
                            'codigoPostal' => $row['CodigoPostal'],
                            'nombreLocalidad' => $nombreLocalidad,
                            'nombreEspecialidad' => $row['Especialidad'],
                            'nombreEspecialidadAlternativa' => $row['EspecialidadAlternativa'],
                            'idLocalidad' => $row['IdLocalidad'],
                            'observaciones' => $row['Observaciones'],
                            'domicilioCompleto' => $domicilio
                            );
                $resultado['estado'] = true;
                $resultado['mensaje'] = "OK";
                $resultado['datos'] = $datos;
                $resultado['clase'] = 'alert alert-success';
                $resultado['icono'] = 'glyphicon glyphicon-ok';
            } else {
                $resultado['estado'] = false;
                $resultado['mensaje'] = "No hay mesa de entrada";
                $resultado['clase'] = 'alert alert-info';
                $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
            }
        } catch (PDOException $e) {
            $resultado['estado'] = false;
            $resultado['mensaje'] = "Error buscando mesa de entrada";
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-remove';
        }

        return $resultado;
    }

    function obtenerHabilitacionConsultorioPorIdMesaEntrada($idMesaEntrada) {
        $db = Database::getConnection();
        $sql = "SELECT me.IdColegiado, mec.IdMesaEntradaConsultorio, con.IdConsultorio, con.Nombre, con.Calle, con.Lateral, con.Numero, con.Piso, con.Departamento, con.Telefono, con.Observaciones, mec.IdEspecialidad, mec.IdEspecialidadAlternativa, l.Nombre AS NombreLocalidad, e.Especialidad, e1.Especialidad AS EspecialidadAlternativa, me.IdUsuario, u.Usuario
                FROM mesaentrada as me
                INNER JOIN mesaentradaconsultorio as mec ON (mec.IdMesaEntrada = me.IdMesaEntrada)
                INNER JOIN consultorio as con ON (con.IdConsultorio = mec.IdConsultorio)
                LEFT JOIN localidad as l ON (l.Id = con.IdLocalidad)
                LEFT JOIN especialidad as e ON (e.Id = mec.IdEspecialidad)
                LEFT JOIN especialidad as e1 ON (e1.Id = mec.IdEspecialidadAlternativa)
                LEFT JOIN usuario as u ON (u.Id = me.IdUsuario)
                WHERE me.IdMesaEntrada = ?
                ORDER BY mec.IdMesaEntradaConsultorio DESC";
        $resultado = array();
        try {
            $stmt = $db->prepare($sql);
            $stmt->execute([$idMesaEntrada]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row !== false) {
                $calle = $row['Calle'];
                $numeroCasa = $row['Numero'];
                $lateral = $row['Lateral'];
                $piso = $row['Piso'];
                $departamento = $row['Departamento'];

                $domicilio = " ";
                if (isset($calle) && $calle <> "") {
                    $domicilio .= trim($calle);
                }
                if (isset($numeroCasa) && $numeroCasa <> "") {
                    $domicilio .= ' N°'.trim($numeroCasa);
                }
                if (isset($lateral) && $lateral <> "") {
                    $domicilio .= ' ('.trim($lateral).')';
                }
                if (isset($piso) && $piso <> "") {
                    $domicilio .= ' Piso'.trim($piso);
                }
                if (isset($departamento) && $departamento <> "") {
                    $domicilio .= ' Dto.'.trim($departamento);
                }
                $datos = array(
                            'idColegiado' => $row['IdColegiado'],
                            'idMesaEntradaConsultorio' => $row['IdMesaEntradaConsultorio'],
                            'idConsultorio' => $row['IdConsultorio'],
                            'nombreConsultorio' => $row['Nombre'],
                            'domicilioCompleto' => $domicilio,
                            'calle' => $calle,
                            'lateral' => $lateral,
                            'numeroCasa' => $numeroCasa,
                            'piso' => $piso,
                            'departamento' => $departamento,
                            'telefono' => $row['Telefono'],
                            'horarios' => $row['Observaciones'],
                            'idEspecialidad' => $row['IdEspecialidad'],
                            'idEspecialidadAlternativa' => $row['IdEspecialidadAlternativa'],
                            'nombreLocalidad' => $row['NombreLocalidad'],
                            'nombreEspecialidad' => $row['Especialidad'],
                            'nombreEspecialidadAlternativa' => $row['EspecialidadAlternativa'],
                            'idUsuario' => $row['IdUsuario'],
                            'nombreUsuario' => $row['Usuario']
                            );
                $resultado['estado'] = true;
                $resultado['mensaje'] = "OK";
                $resultado['datos'] = $datos;
                $resultado['clase'] = 'alert alert-success';
                $resultado['icono'] = 'glyphicon glyphicon-ok';
            } else {
                $resultado['estado'] = false;
                $resultado['mensaje'] = "No hay mesa de entrada";
                $resultado['clase'] = 'alert alert-info';
                $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
            }
        } catch (PDOException $e) {
            $resultado['estado'] = false;
            $resultado['mensaje'] = "Error buscando mesa de entrada";
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-remove';
        }

        return $resultado;
    }

    function obtenerMesaEntradaAutoprescripcionPorId($idMesaEntrada) {
        $db = Database::getConnection();
        $sql = "SELECT mea.IdMesaEntradaAutoprescripcion, me.IdColegiado, me.FechaIngreso, me.Estado, me.IdUsuario, me.Observaciones, mea.Fecha, mea.Autorizado, mea.DocumentoAutorizado, mea.Parentezco, mea.Autorizado2, mea.DocumentoAutorizado2, mea.Parentezco2
            FROM mesaentrada me
            INNER JOIN mesaentradaautoprescripcion mea ON mea.IdMesaEntrada = me.IdMesaEntrada
            WHERE me.IdMesaEntrada = ?";
        $resultado = array();
        try {
            $stmt = $db->prepare($sql);
            $stmt->execute([$idMesaEntrada]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row !== false) {
                $datos = array(
                            'idMesaEntradaAutoprescripcion' => $row['IdMesaEntradaAutoprescripcion'],
                            'idColegiado' => $row['IdColegiado'],
                            'fechaIngreso' => $row['FechaIngreso'],
                            'estado' => $row['Estado'],
                            'observaciones' => $row['Observaciones'],
                            'idUsuario' => $row['IdUsuario'],
                            'fecha' => $row['Fecha'],
                            'autorizado1' => $row['Autorizado'],
                            'documentoAutorizado1' => $row['DocumentoAutorizado'],
                            'parentesco1' => $row['Parentezco'],
                            'autorizado2' => $row['Autorizado2'],
                            'documentoAutorizado2' => $row['DocumentoAutorizado2'],
                            'parentesco2' => $row['Parentezco2']
                            );
                $resultado['estado'] = true;
                $resultado['mensaje'] = "OK";
                $resultado['datos'] = $datos;
                $resultado['clase'] = 'alert alert-success';
                $resultado['icono'] = 'glyphicon glyphicon-ok';
            } else {
                $resultado['estado'] = false;
                $resultado['mensaje'] = "No hay mesa de entrada";
                $resultado['clase'] = 'alert alert-info';
                $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
            }
        } catch (PDOException $e) {
            $resultado['estado'] = false;
            $resultado['mensaje'] = "Error buscando mesa de entrada";
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-remove';
        }

        return $resultado;
    }

    function obtenerMesaEntradaAnulacionPorId($idMesaEntrada) {
        $db = Database::getConnection();
        $sql = "SELECT mema.IdMesaEntradaMovimientoAnulacion, me.IdColegiado, me.FechaIngreso, me.Estado, me.IdUsuario, me.Observaciones, mema.IdMesaEntradaMovimiento, mem.Fecha, mem.IdTipoMovimiento, tm.Detalle
            FROM mesaentrada me
            INNER JOIN mesaentradamovimientoanulacion mema ON mema.IdMesaEntrada = me.IdMesaEntrada
            INNER JOIN mesaentradamovimiento mem ON mem.IdMesaEntradaMovimiento = mema.IdMesaEntradaMovimiento
            INNER JOIN tipomovimiento tm ON tm.Id = mem.IdTipoMovimiento
            WHERE me.IdMesaEntrada = ?";
        $resultado = array();
        try {
            $stmt = $db->prepare($sql);
            $stmt->execute([$idMesaEntrada]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row !== false) {
                $datos = array(
                            'idMesaEntradaAnulacion' => $row['IdMesaEntradaMovimientoAnulacion'],
                            'idColegiado' => $row['IdColegiado'],
                            'fechaIngreso' => $row['FechaIngreso'],
                            'estado' => $row['Estado'],
                            'observaciones' => $row['Observaciones'],
                            'idUsuario' => $row['IdUsuario'],
                            'idMesaEntradaMovimientoAnulado' => $row['IdMesaEntradaMovimiento'],
                            'fechaMovimiento' => $row['Fecha'],
                            'idTipoMovimiento' => $row['IdTipoMovimiento'],
                            'nombreTipoMovimiento' => $row['Detalle']
                            );
                $resultado['estado'] = true;
                $resultado['mensaje'] = "OK";
                $resultado['datos'] = $datos;
                $resultado['clase'] = 'alert alert-success';
                $resultado['icono'] = 'glyphicon glyphicon-ok';
            } else {
                $resultado['estado'] = false;
                $resultado['mensaje'] = "No hay mesa de entrada";
                $resultado['clase'] = 'alert alert-info';
                $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
            }
        } catch (PDOException $e) {
            $resultado['estado'] = false;
            $resultado['mensaje'] = "Error buscando mesa de entrada";
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-remove';
        }

        return $resultado;
    }

    function obtenerMesaEntradaDenunciaPorId($idMesaEntrada) {
        $db = Database::getConnection();
        $sql = "SELECT med.IdMesaEntradaDenuncia, me.IdColegiado, me.FechaIngreso, me.Estado, me.IdUsuario, me.Observaciones, med.FechaDenuncia, med.FechaExtravio, med.IdTipoDenuncia, td.Nombre
            FROM mesaentrada me
            INNER JOIN mesaentradadenuncia med ON med.IdMesaEntrada = me.IdMesaEntrada
            INNER JOIN tipodenuncia td ON td.Id = med.IdTipoDenuncia
            WHERE me.IdMesaEntrada = ?";
        $resultado = array();
        try {
            $stmt = $db->prepare($sql);
            $stmt->execute([$idMesaEntrada]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row !== false) {
                $datos = array(
                            'idMesaEntradaDenuncia' => $row['IdMesaEntradaDenuncia'],
                            'idColegiado' => $row['IdColegiado'],
                            'fechaIngreso' => $row['FechaIngreso'],
                            'estado' => $row['Estado'],
                            'observaciones' => $row['Observaciones'],
                            'idUsuario' => $row['IdUsuario'],
                            'fechaDenuncia' => $row['FechaDenuncia'],
                            'fechaExtravio' => $row['FechaExtravio'],
                            'idTipoDenuncia' => $row['IdTipoDenuncia'],
                            'nombreTipoDenuncia' => $row['Nombre']
                            );
                $resultado['estado'] = true;
                $resultado['mensaje'] = "OK";
                $resultado['datos'] = $datos;
                $resultado['clase'] = 'alert alert-success';
                $resultado['icono'] = 'glyphicon glyphicon-ok';
            } else {
                $resultado['estado'] = false;
                $resultado['mensaje'] = "No hay mesa de entrada";
                $resultado['clase'] = 'alert alert-info';
                $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
            }
        } catch (PDOException $e) {
            $resultado['estado'] = false;
            $resultado['mensaje'] = "Error buscando mesa de entrada";
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-remove';
        }

        return $resultado;
    }

    function obtenerMesaEntradaEntregaPorId($idMesaEntradaEntrega, $idMesaEntrada) {
        $db = Database::getConnection();
        if (isset($idMesaEntradaEntrega) && $idMesaEntradaEntrega <> "") {
            $filtro = " WHERE mee.IdMesaEntradaNota = ?";
            $indice = $idMesaEntradaEntrega;
        } else {
            $filtro = " WHERE mee.IdMesaEntrada = ?";
            $indice = $idMesaEntrada;
        }
        $sql = "SELECT mee.IdMesaEntradaEntrega, mee.IdMesaEntrada, me.IdColegiado, me.FechaIngreso, me.Estado, me.IdUsuario, me.Observaciones, mee.FechaEntrega, mee.IdTipoEntrega, te.Nombre, te.Leyenda
            FROM mesaentrada me
            INNER JOIN mesaentradaentrega mee ON mee.IdMesaEntrada = me.IdMesaEntrada
            INNER JOIN tipoentrega te ON te.Id = mee.IdTipoEntrega ".$filtro;
        $resultado = array();
        try {
            $stmt = $db->prepare($sql);
            $stmt->execute([$indice]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row !== false) {
                $datos = array(
                            'idMesaEntradaEntrega' => $row['IdMesaEntradaEntrega'],
                            'idMesaEntrada' => $row['IdMesaEntrada'],
                            'idColegiado' => $row['IdColegiado'],
                            'fechaIngreso' => $row['FechaIngreso'],
                            'estado' => $row['Estado'],
                            'idUsuario' => $row['IdUsuario'],
                            'observaciones' => $row['Observaciones'],
                            'fechaEntrega' => $row['FechaEntrega'],
                            'idTipoEntrega' => $row['IdTipoEntrega'],
                            'nombreTipoEntrega' => $row['Nombre'],
                            'leyendaTipoEntrega' => $row['Leyenda']
                            );
                $resultado['estado'] = true;
                $resultado['mensaje'] = "OK";
                $resultado['datos'] = $datos;
                $resultado['clase'] = 'alert alert-success';
                $resultado['icono'] = 'glyphicon glyphicon-ok';
            } else {
                $resultado['estado'] = false;
                $resultado['mensaje'] = "No hay mesa de entrada";
                $resultado['clase'] = 'alert alert-info';
                $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
            }
        } catch (PDOException $e) {
            $resultado['estado'] = false;
            $resultado['mensaje'] = "Error buscando mesa de entrada";
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-remove';
        }

        return $resultado;
    }

    function obtenerEntregaPorIdMesaEntrada($idMesaEntrada) {
        $db = Database::getConnection();
        $sql = "SELECT me.IdColegiado, mee.IdMesaEntradaEntrega, mee.FechaEntrega, mee.IdTipoEntrega, te.Nombre, te.Leyenda
                FROM mesaentrada as me
                INNER JOIN mesaentradaentrega as mee ON mee.IdMesaEntrada = me.IdMesaEntrada
                INNER JOIN tipoentrega te ON te.Id = mee.IdTipoEntrega
                WHERE me.IdMesaEntrada = ?";
        $resultado = array();
        try {
            $stmt = $db->prepare($sql);
            $stmt->execute([$idMesaEntrada]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row !== false) {
                $datos = array(
                            'idColegiado' => $row['IdColegiado'],
                            'idMesaEntradaEntrega' => $row['IdMesaEntradaEntrega'],
                            'fechaEntrega' => $row['FechaEntrega'],
                            'idTipoEntrega' => $row['IdTipoEntrega'],
                            'nombreTipoEntrega' => $row['Nombre'],
                            'leyendaTipoEntrega' => $row['Leyenda']
                            );
                $resultado['estado'] = true;
                $resultado['mensaje'] = "OK";
                $resultado['datos'] = $datos;
                $resultado['clase'] = 'alert alert-success';
                $resultado['icono'] = 'glyphicon glyphicon-ok';
            } else {
                $resultado['estado'] = false;
                $resultado['mensaje'] = "No hay mesa de entrada entrega";
                $resultado['clase'] = 'alert alert-info';
                $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
            }
        } catch (PDOException $e) {
            $resultado['estado'] = false;
            $resultado['mensaje'] = "Error buscando mesa de entrada entrega";
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-remove';
        }

        return $resultado;
    }

    function obtenerTituloEspecialistaPorEntrega($idMesaEntradaEntrega) {
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
            WHERE te.IdMesaEntradaEntrega = ?";
        $resultado = array();
        try {
            $stmt = $db->prepare($sql);
            $stmt->execute([$idMesaEntradaEntrega]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row !== false) {
                $especialista = $row['Especialista'];
                $recertificacion = $row['Recertificacion'];
                $jer_con = $row['Jer_Con'];
                $tipo = $row['tipo'];
                if (isset($especialista) && $especialista <> "") {
                    $especialidadEntregar = 'Título Especialista: '.$especialista;
                } else {
                    if (isset($recertificacion) && $recertificacion <> "") {
                        $especialidadEntregar = 'Título Recertificación Especialista: '.$recertificacion;
                    } else {
                        if (isset($jer_con) && $jer_con <> "") {
                            $especialidadEntregar = 'Título Especialista '.$tipo.': '.$jer_con;
                        } else {
                            $resultado['estado'] = false;
                            $resultado['mensaje'] = "No existen titulo especialista";
                            $resultado['clase'] = 'alert alert-warning';
                            $resultado['icono'] = 'glyphicon glyphicon-remove';
                            return $resultado;
                        }
                    }
                }
                $datos = array(
                        'idTituloEspecialista' => $row['IdTituloEspecialista'],
                        'especialidadEntregar' => $especialidadEntregar
                );
                $resultado['estado'] = true;
                $resultado['mensaje'] = "OK";
                $resultado['datos'] = $datos;
                $resultado['clase'] = 'alert alert-success';
                $resultado['icono'] = 'glyphicon glyphicon-ok';
            } else {
                $resultado['estado'] = false;
                $resultado['mensaje'] = "No existen titulo especialista";
                $resultado['clase'] = 'alert alert-warning';
                $resultado['icono'] = 'glyphicon glyphicon-remove';
            }
        } catch (PDOException $e) {
            $resultado['estado'] = false;
            $resultado['mensaje'] = "Error al buscar especialidades";
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-remove';
        }

        return $resultado;
    }

    function obtenerMesaEntradaPorFechaTipo($fechaIngreso, $idTipoMesaEntrada, $idColegiado, $idRemitente){
        $db = Database::getConnection();
        $conFiltro = "";
        $porFecha = FALSE;
        $params = [];
        if ((isset($idColegiado) && $idColegiado <> "") || (isset($idRemitente) && $idRemitente <> "")) {
            if (isset($idColegiado) && $idColegiado <> "") {
                $conFiltro .= "WHERE me.IdColegiado = ".$idColegiado;
            }
            if (isset($idRemitente) && $idRemitente <> "") {
                $conFiltro .= "WHERE me.IdRemitente = ".$idRemitente;
            }
        } else {
            $conFiltro .= "WHERE me.FechaIngreso = ? ";
            $porFecha = TRUE;
            $params[] = $fechaIngreso;
        }
        if (isset($idTipoMesaEntrada) && $idTipoMesaEntrada <> "") {
            $conFiltro .= " AND me.IdTipoMesaEntrada = ".$idTipoMesaEntrada;
        }

        $sql = "SELECT me.IdMesaEntrada, me.FechaIngreso, c.Matricula, p.Apellido, p.Nombres, me.IdRemitente, r.Nombre, tme.Nombre AS NombreTipoMesaEntrada, me.IdTipoMesaEntrada, mea.IdMesaEntradaAutoprescripcion, mec.IdMesaEntradaConsultorio, med.IdMesaEntradaDenuncia, mee.IdMesaEntradaEntrega, meesp.IdMesaEntradaEspecialidad, mem.IdMesaEntradaMovimiento, men.IdMesaEntradaNota, te.Nombre AS NombreTipoEspecialista, e.Especialidad,
            CASE
                WHEN me.IdTipoMesaEntrada = 1 THEN (SELECT tm.Detalle FROM tipomovimiento tm WHERE tm.Id = mem.IdTipoMovimiento)
                WHEN me.IdTipoMesaEntrada = 8 THEN CONCAT('Mesa Entrada Nro ', (SELECT mem1.IdMesaEntrada FROM mesaentradamovimientoanulacion mema INNER JOIN mesaentradamovimiento mem1 ON mem1.IdMesaEntradaMovimiento = mema.IdMesaEntradaMovimiento WHERE mema.IdMesaEntrada = me.IdMesaEntrada LIMIT 1), ' - ', (SELECT tm1.DetalleCompleto FROM mesaentradamovimientoanulacion mema INNER JOIN mesaentradamovimiento mem1 ON mem1.IdMesaEntradaMovimiento = mema.IdMesaEntradaMovimiento INNER JOIN tipomovimiento tm1 ON tm1.Id = mem1.IdTipoMovimiento WHERE mema.IdMesaEntrada = me.IdMesaEntrada LIMIT 1))
                WHEN me.IdTipoMesaEntrada = 9 THEN (SELECT td.Nombre FROM tipodenuncia td WHERE td.Id = med.IdTipoDenuncia)
                WHEN me.IdTipoMesaEntrada = 10 THEN (SELECT te.Nombre FROM tipoentrega te WHERE te.Id = mee.IdTipoEntrega)
                ELSE ''
            END AS DetalleTipo,
            men.Tema, u.NombreCompleto
                FROM mesaentrada me
                LEFT JOIN colegiado c ON c.Id = me.IdColegiado
                LEFT JOIN persona p ON p.Id = c.IdPersona
                LEFT JOIN remitente r ON r.id = me.IdRemitente
                INNER JOIN tipomesaentrada tme ON tme.IdTipoMesaEntrada = me.IdTipoMesaEntrada
                LEFT JOIN mesaentradaautoprescripcion mea ON mea.IdMesaEntrada = me.IdMesaEntrada
                LEFT JOIN mesaentradaconsultorio mec ON mec.IdMesaEntrada = me.IdMesaEntrada
                LEFT JOIN mesaentradadenuncia med ON med.IdMesaEntrada = me.IdMesaEntrada
                LEFT JOIN mesaentradaentrega mee ON mee.IdMesaEntrada = me.IdMesaEntrada
                LEFT JOIN mesaentradaespecialidad meesp ON meesp.IdMesaEntrada = me.IdMesaEntrada
                LEFT JOIN tipoespecialista te on te.IdTipoEspecialista = meesp.IdTipoEspecialista
                LEFT JOIN especialidad e ON e.Id = meesp.IdEspecialidad
                LEFT JOIN mesaentradamovimiento mem ON mem.IdMesaEntrada = me.IdMesaEntrada
                LEFT JOIN mesaentradanota men ON men.IdMesaEntrada = me.IdMesaEntrada
                LEFT JOIN usuario u ON u.Id = me.IdUsuario
                ".$conFiltro." AND me.Estado = 'A'";
        $resultado = array();
        try {
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $datos = array();
            if (count($rows) > 0) {
                foreach ($rows as $row) {
                    $matricula = $row['Matricula'];
                    $idRemitente = $row['IdRemitente'];
                    $nombreRemitente = $row['Nombre'];
                    $apellido = $row['Apellido'];
                    $nombre = $row['Nombres'];
                    $idTipoMesaEntrada = $row['IdTipoMesaEntrada'];
                    $nombreTipoMesaEntrada = $row['NombreTipoMesaEntrada'];
                    $nombreTipoEspecialista = $row['NombreTipoEspecialista'];
                    $nombreEspecialidad = $row['Especialidad'];
                    $tema = $row['Tema'];
                    if (isset($matricula) && $matricula <> "") {
                        $nombreRemitente = trim($apellido).' '.trim($nombre);
                    }
                    if (isset($idRemitente) && $idRemitente <> "") {
                        $nombreRemitente = trim($nombreRemitente);
                    }
                    if ($idTipoMesaEntrada == 2) {
                        //si es por especialidad, agrego el tipo
                        $nombreTipoMesaEntrada .= '<br>('.$nombreTipoEspecialista.' - '.$nombreEspecialidad.')';
                    }
                    if ($idTipoMesaEntrada == 3) {
                        //si es por nota
                        $nombreTipoMesaEntrada .= '<br>(Tema: '.$tema.')';
                    }
                    $item = array(
                        'idMesaEntrada' => $row['IdMesaEntrada'],
                        'fechaIngreso' => $row['FechaIngreso'],
                        'matricula' => $matricula,
                        'idRemitente' => $idRemitente,
                        'nombreRemitente' => $nombreRemitente,
                        'nombreTipoMesaEntrada' => $nombreTipoMesaEntrada,
                        'idTipoMesaEntrada' => $idTipoMesaEntrada,
                        'idMesaEntradaAutoprescripcion' => $row['IdMesaEntradaAutoprescripcion'],
                        'idMesaEntradaConsultorio' => $row['IdMesaEntradaConsultorio'],
                        'idMesaEntradaDenuncia' => $row['IdMesaEntradaDenuncia'],
                        'idMesaEntradaEntrega' => $row['IdMesaEntradaEntrega'],
                        'idMesaEntradaEspecialidad' => $row['IdMesaEntradaEspecialidad'],
                        'idMesaEntradaMovimiento' => $row['IdMesaEntradaMovimiento'],
                        'idMesaEntradaNota' => $row['IdMesaEntradaNota'],
                        'detalleTipoMesaEntrada' => $row['DetalleTipo'],
                        'nombreUsuario' => $row['NombreCompleto']
                    );
                    array_push($datos, $item);
                }
                $resultado['estado'] = true;
                $resultado['datos'] = $datos;
                $resultado['mensaje'] = "OK";
                $resultado['clase'] = 'alert alert-success';
                $resultado['icono'] = 'glyphicon glyphicon-ok';
            } else {
                $resultado['estado'] = true;
                $resultado['datos'] = $datos;
                $resultado['mensaje'] = "No hay mesa de entrada";
                $resultado['clase'] = 'alert alert-warning';
                $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
            }
        } catch (PDOException $e) {
            $resultado['estado'] = false;
            $resultado['mensaje'] = "Error buscando mesa de entrada";
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-remove';
        }
        return $resultado;
    }

    function obtenerMesaEntradaNotasPorAnio($anio, $idColegiado, $idRemitente){
        $db = Database::getConnection();
        $conFiltro = "";
        $params = [];
        if ((isset($anio) && $anio >= 2013 && $anio <= date('Y'))) {
            $conFiltro .= " AND SUBSTR(me.FechaIngreso, 1, 4) = ?";
            $params[] = $anio;
        }
        if ((isset($idColegiado) && $idColegiado <> "") || (isset($idRemitente) && $idRemitente <> "")) {
            if (isset($idColegiado) && $idColegiado <> "") {
                $conFiltro .= " AND me.IdColegiado = ".$idColegiado;
            }
            if (isset($idRemitente) && $idRemitente <> "") {
                $conFiltro .= " AND me.IdRemitente = ".$idRemitente;
            }
        }

        $sql = "SELECT me.IdMesaEntrada, me.FechaIngreso, c.Matricula, p.Apellido, p.Nombres, me.IdRemitente, r.Nombre, men.Tema
                FROM mesaentrada me
                LEFT JOIN colegiado c ON c.Id = me.IdColegiado
                LEFT JOIN persona p ON p.Id = c.IdPersona
                LEFT JOIN remitente r ON r.id = me.IdRemitente
                INNER JOIN tipomesaentrada tme ON tme.IdTipoMesaEntrada = me.IdTipoMesaEntrada
                INNER JOIN mesaentradanota men ON men.IdMesaEntrada = me.IdMesaEntrada
                WHERE me.Estado = 'A' ".$conFiltro;
        $resultado = array();
        try {
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $datos = array();
            if (count($rows) > 0) {
                foreach ($rows as $row) {
                    $matricula = $row['Matricula'];
                    $idRemitente = $row['IdRemitente'];
                    $nombreRemitente = $row['Nombre'];
                    $apellido = $row['Apellido'];
                    $nombre = $row['Nombres'];
                    if (isset($matricula) && $matricula <> "") {
                        $nombreRemitente = trim($apellido).' '.trim($nombre);
                    }
                    if (isset($idRemitente) && $idRemitente <> "") {
                        $nombreRemitente = trim($nombreRemitente);
                    }
                    $item = array(
                        'idMesaEntrada' => $row['IdMesaEntrada'],
                        'fechaIngreso' => $row['FechaIngreso'],
                        'matricula' => $matricula,
                        'idRemitente' => $idRemitente,
                        'nombreRemitente' => $nombreRemitente,
                        'tema' => $row['Tema']
                    );
                    array_push($datos, $item);
                }
                $resultado['estado'] = true;
                $resultado['datos'] = $datos;
                $resultado['mensaje'] = "OK";
                $resultado['clase'] = 'alert alert-success';
                $resultado['icono'] = 'glyphicon glyphicon-ok';
            } else {
                $resultado['estado'] = true;
                $resultado['datos'] = $datos;
                $resultado['mensaje'] = "No hay mesa de entrada";
                $resultado['clase'] = 'alert alert-warning';
                $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
            }
        } catch (PDOException $e) {
            $resultado['estado'] = false;
            $resultado['mensaje'] = "Error buscando mesa de entrada";
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-remove';
        }
        return $resultado;
    }

    function obtenerPresidenteDistrito($distrito) {
        $db = Database::getConnection();
        $sql="SELECT d.Id, d.Romanos, d.Presidente
            FROM distritos d
            WHERE d.Distrito = ?";
        $resultado = array();
        try {
            $stmt = $db->prepare($sql);
            $stmt->execute([$distrito]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row !== false) {
                $datos = array(
                            'idDistrito' => $row['Id'],
                            'romano' => $row['Romanos'],
                            'presidente' => $row['Presidente']
                            );
                $resultado['estado'] = true;
                $resultado['mensaje'] = "OK";
                $resultado['datos'] = $datos;
                $resultado['clase'] = 'alert alert-success';
                $resultado['icono'] = 'glyphicon glyphicon-ok';
            } else {
                $resultado['estado'] = false;
                $resultado['mensaje'] = "No hay presidente";
                $resultado['clase'] = 'alert alert-info';
                $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
            }
        } catch (PDOException $e) {
            $resultado['estado'] = false;
            $resultado['mensaje'] = "Error buscando presidente";
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-remove';
        }

        return $resultado;
    }

    function obtenerTipoMesaEntradaPorId($idTipoMesaEntrada){
        $db = Database::getConnection();
        $sql = "SELECT IdTipoMesaEntrada, Nombre
                FROM tipomesaentrada
                WHERE IdTipoMesaEntrada = ?";
        $resultado = array();
        try {
            $stmt = $db->prepare($sql);
            $stmt->execute([$idTipoMesaEntrada]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row !== false) {
                $datos = array(
                        'id' => $row['IdTipoMesaEntrada'],
                        'nombre' => $row['Nombre']
                );
                $resultado['estado'] = true;
                $resultado['mensaje'] = "OK";
                $resultado['datos'] = $datos;
                $resultado['clase'] = 'alert alert-success';
                $resultado['icono'] = 'glyphicon glyphicon-ok';
            } else {
                $resultado['estado'] = false;
                $resultado['mensaje'] = "No hay tipos de mesa de entrada";
                $resultado['clase'] = 'alert alert-warning';
                $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
            }
        } catch (PDOException $e) {
            $resultado['estado'] = false;
            $resultado['mensaje'] = "Error buscando tipos de mesa de entrada";
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-remove';
        }
        return $resultado;
    }

   function obtenerTiposDenuncia() {
        $db = Database::getConnection();
        $sql = "SELECT Id, Nombre
                FROM tipodenuncia
                ORDER BY Nombre";
        $resultado = array();
        try {
            $stmt = $db->prepare($sql);
            $stmt->execute([]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (count($rows) > 0) {
                $datos = array();
                foreach ($rows as $row) {
                    $item = array(
                        'id' => $row['Id'],
                        'nombre' => $row['Nombre']
                    );
                    array_push($datos, $item);
                }
                $resultado['estado'] = true;
                $resultado['mensaje'] = "OK";
                $resultado['datos'] = $datos;
                $resultado['clase'] = 'alert alert-success';
                $resultado['icono'] = 'glyphicon glyphicon-ok';
            } else {
                $resultado['estado'] = true;
                $resultado['datos'] = NULL;
                $resultado['mensaje'] = "No hay tipos de denuncia";
                $resultado['clase'] = 'alert alert-warning';
                $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
            }
        } catch (PDOException $e) {
            $resultado['estado'] = false;
            $resultado['mensaje'] = "Error buscando tipos de denuncia";
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-remove';
        }
        return $resultado;
    }

    function obtenerTiposEntrega() {
        $db = Database::getConnection();
        $sql = "SELECT Id, Nombre
                FROM tipoentrega
                ORDER BY Nombre";
        $resultado = array();
        try {
            $stmt = $db->prepare($sql);
            $stmt->execute([]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (count($rows) > 0) {
                $datos = array();
                foreach ($rows as $row) {
                    $item = array(
                        'id' => $row['Id'],
                        'nombre' => $row['Nombre']
                    );
                    array_push($datos, $item);
                }
                $resultado['estado'] = true;
                $resultado['mensaje'] = "OK";
                $resultado['datos'] = $datos;
                $resultado['clase'] = 'alert alert-success';
                $resultado['icono'] = 'glyphicon glyphicon-ok';
            } else {
                $resultado['estado'] = true;
                $resultado['datos'] = NULL;
                $resultado['mensaje'] = "No hay tipos de entrega";
                $resultado['clase'] = 'alert alert-warning';
                $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
            }
        } catch (PDOException $e) {
            $resultado['estado'] = false;
            $resultado['mensaje'] = "Error buscando tipos de entrega";
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-remove';
        }
        return $resultado;
    }

    function obtenerTiposMovimientoMesaEntrada($idEstadoMatricular) {
        $db = Database::getConnection();
        $sql = "SELECT tm.Id, tm.DetalleCompleto
            FROM movimientomesaentradas mme
            INNER JOIN tipomovimiento tm ON(tm.Id = mme.IdTipoMovimiento)
            WHERE mme.IdMovimientoActual = ?
            ORDER BY tm.DetalleCompleto";
        $resultado = array();
        try {
            $stmt = $db->prepare($sql);
            $stmt->execute([$idEstadoMatricular]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (count($rows) > 0)
            {
                $datos = array();
                foreach ($rows as $row) {
                    $item = array(
                        'id' => $row['Id'],
                        'nombre' => $row['DetalleCompleto']
                    );
                    array_push($datos, $item);
                }
                $resultado['estado'] = true;
                $resultado['mensaje'] = "OK";
                $resultado['datos'] = $datos;
                $resultado['clase'] = 'alert alert-success';
                $resultado['icono'] = 'glyphicon glyphicon-ok';
            } else {
                $resultado['estado'] = true;
                $resultado['datos'] = NULL;
                $resultado['mensaje'] = "No hay tipos de Movimiento";
                $resultado['clase'] = 'alert alert-warning';
                $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
            }
        } catch (PDOException $e) {
            $resultado['estado'] = false;
            $resultado['mensaje'] = "Error buscando tipos de Movimiento";
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-remove';
        }
        return $resultado;
    }

    function obtenerMotivosCancelacion() {
        $db = Database::getConnection();
        $sql = "SELECT IdMotivoCancelacion, Nombre
            FROM motivocancelacion
            ORDER BY Nombre";
        $resultado = array();
        try {
            $stmt = $db->prepare($sql);
            $stmt->execute([]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (count($rows) > 0) {
                $datos = array();
                foreach ($rows as $row) {
                    $item = array(
                        'id' => $row['IdMotivoCancelacion'],
                        'nombre' => $row['Nombre']
                    );
                    array_push($datos, $item);
                }
                $resultado['estado'] = true;
                $resultado['mensaje'] = "OK";
                $resultado['datos'] = $datos;
                $resultado['clase'] = 'alert alert-success';
                $resultado['icono'] = 'glyphicon glyphicon-ok';
            } else {
                $resultado['estado'] = true;
                $resultado['datos'] = NULL;
                $resultado['mensaje'] = "No hay motivos";
                $resultado['clase'] = 'alert alert-warning';
                $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
            }
        } catch (PDOException $e) {
            $resultado['estado'] = false;
            $resultado['mensaje'] = "Error buscando motivos";
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-remove';
        }
        return $resultado;
    }

    function obtenerRemitentes(){
        $db = Database::getConnection();
        $sql = "SELECT Id, Nombre
                FROM remitente
                ORDER BY Nombre";
        $resultado = array();
        try {
            $stmt = $db->prepare($sql);
            $stmt->execute([]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (count($rows) > 0) {
                $datos = array();
                foreach ($rows as $row) {
                    $item = array(
                        'id' => $row['Id'],
                        'nombre' => $row['Nombre']
                    );
                    array_push($datos, $item);
                }
                $resultado['estado'] = true;
                $resultado['mensaje'] = "OK";
                $resultado['datos'] = $datos;
                $resultado['clase'] = 'alert alert-success';
                $resultado['icono'] = 'glyphicon glyphicon-ok';
            } else {
                $resultado['estado'] = true;
                $resultado['datos'] = NULL;
                $resultado['mensaje'] = "No hay remitentes";
                $resultado['clase'] = 'alert alert-warning';
                $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
            }
        } catch (PDOException $e) {
            $resultado['estado'] = false;
            $resultado['mensaje'] = "Error buscando remitentes";
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-remove';
        }
        return $resultado;
    }

    function guardarRemitente($idRemitente, $nombre, $datosAnteriores) {
        $db = Database::getConnection();
        $resultado = array();
        try {
            $db->beginTransaction();
            if (isset($idRemitente)) {
                $sql = "UPDATE remitente
                        SET Nombre = ?
                        WHERE Id = ?";
                $stmt = $db->prepare($sql);
                $stmt->execute([$nombre, $idRemitente]);
                $tipoMovimiento = 'modificacion';
            } else {
                $sql="INSERT INTO remitente (Nombre)
                VALUES (?)";
                $stmt = $db->prepare($sql);
                $stmt->execute([$nombre]);
                $idRemitente = $db->lastInsertId();
                $datosAnteriores = array();
                $tipoMovimiento = 'alta';
            }
            $datos = serialize($datosAnteriores);
            $sql="INSERT INTO log_tabla (Tabla, IdTabla, Fecha, TipoMovimiento, IdUsuario, Datos)
                VALUES ('remitente', ?, NOW(), ?, ?, ?)";
            $stmt = $db->prepare($sql);
            $stmt->execute([$idRemitente, $tipoMovimiento, $_SESSION['user_id'], $datos]);
            $resultado['estado'] = TRUE;
            $resultado['idRemitente'] = $idRemitente;
            $resultado['mensaje'] = 'EL REMITENTE HA SIDO GUARDADO';
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
            $db->commit();
            return $resultado;
        } catch (PDOException $e) {
            $db->rollBack();
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "Error guardando remitente -> ".$e->getMessage();
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
            return $resultado;
        }
    }

    function agregarMesaEntrada($idTipoMesaEntrada, $tipoRemitente, $idColegiado, $idRemitente, $estadoMatricular, $estadoTesoreria, $observaciones, $datosTipoMesaEntrada) {
        $db = Database::getConnection();
        $resultado = array();
        try {
            $db->beginTransaction();
            $sql = "INSERT INTO mesaentrada(TipoRemitente, IdColegiado, IdRemitente, IdTipoMesaEntrada, FechaIngreso, Estado, IdUsuario, EstadoMatricular, EstadoTesoreria, Observaciones)
                VALUES(?, ?, ?, ?, DATE(NOW()), 'A', ?, ?, ?, ?)";
            $stmt = $db->prepare($sql);
            $stmt->execute([$tipoRemitente, $idColegiado, $idRemitente, $idTipoMesaEntrada, $_SESSION['user_id'], $estadoMatricular, $estadoTesoreria, $observaciones]);
            $idMesaEntrada = $db->lastInsertId();
            if (true) {

                //echo 'idTipoMesaEntrada->'.$idTipoMesaEntrada.'<br>';
                switch ($idTipoMesaEntrada) {
                    case '1': // movimiento matriculares 
                    case '8': //Anulación de Movimiento Matricular
                        if ($idTipoMesaEntrada == '1') {
                            //agrega el movimiento en la mesa
                            $idTipoMovimiento = $datosTipoMesaEntrada['idTipoMovimiento'];
                            $fechaMovimiento = $datosTipoMesaEntrada['fechaMovimiento'];
                            $idMotivoCancelacion = $datosTipoMesaEntrada['idMotivoCancelacion'];
                            $distrito = $datosTipoMesaEntrada['distrito'];

                            //se agrega el dato del movimiento
                            $sql="INSERT INTO mesaentradamovimiento
                                    (IdMesaEntrada, IdTipoMovimiento, Fecha, IdMotivoCancelacion, Distrito)
                                    VALUES (?, ?, ?, ?, ?)";
                            $stmt = $db->prepare($sql);
                            $stmt->execute([$idMesaEntrada, $idTipoMovimiento, $fechaMovimiento, $idMotivoCancelacion, $distrito]);
                            $idMesaEntradaMovimiento = $db->lastInsertId();

                            $resultado['estado'] = TRUE;
                            $resultado['mensaje'] = 'El movimiento se registro correctamente';
                            $resultado['clase'] = 'alert alert-success';
                            $resultado['icono'] = 'glyphicon glyphicon-ok';

                            if ($idTipoMovimiento == 20) {
                                //si es rehabilitacion de alguna cancelacion, debo actualizar la fecha hasta del movimiento
                                $sql_2 = "SELECT cm.Id, cm.FechaDesde
                                        FROM colegiadomovimiento cm
                                        INNER JOIN colegiado c ON c.Id = cm.IdColegiado AND c.Estado = cm.IdMovimiento
                                        INNER JOIN tipomovimiento tm ON tm.Id = c.Estado
                                        WHERE c.Id = ? AND tm.Estado IN('C', 'J', 'F') AND cm.FechaHasta IS NULL
                                        ORDER BY cm.FechaDesde DESC
                                        LIMIT 1";
                                $stmt2 = $db->prepare($sql_2);
                                $stmt2->execute([$idColegiado]);
                                $row2 = $stmt2->fetch(PDO::FETCH_ASSOC);
                                if ($row2 !== false) {
                                    $idColegiadoMovimiento = $row2['Id'];
                                    $sql_2 = "UPDATE colegiadomovimiento
                                            SET FechaHasta = ?, FechaCargaRehabilitacion = DATE(NOW()), IdUsuarioRehabilitador = ?
                                            WHERE Id = ?";
                                    $stmt2 = $db->prepare($sql_2);
                                    $stmt2->execute([$fechaMovimiento, $_SESSION['user_id'], $idColegiadoMovimiento]);
                                } else {
                                    $resultado['estado'] = FALSE;
                                    $resultado['mensaje'] = "ERROR NO HAY CANCELACIÓN PARA REHABILITAR.";
                                    $resultado['clase'] = 'alert alert-danger';
                                    $resultado['icono'] = 'glyphicon glyphicon-remove';
                                }
                            } else {
                                //crea el movimiento en el colegiadomovimiento
                                $sql_2 = "INSERT INTO colegiadomovimiento (IdColegiado, IdMovimiento, FechaDesde, DistritoCambio, IdUsuarioCarga, FechaCarga, Estado)
                                        VALUE (?, ?, ?, ?, ?, DATE(NOW()), 'O')";
                                $stmt2 = $db->prepare($sql_2);
                                $stmt2->execute([$idColegiado, $idTipoMovimiento, $fechaMovimiento, $distrito, $_SESSION['user_id']]);
                                $idColegiadoMovimiento = $db->lastInsertId();
                            }

                            //se actualiza el estado en colegiado y se agrega la relacion entre colegiadomovimiento y mesaentrada
                            if ($idTipoMovimiento == 20) {
                                //si rehabilta se le pone ultimo estado como activo que tenia, se busca en los movimientos
                                $estado = 1;
                            } else {
                                $estado = $idTipoMovimiento;
                            }
                            $sql_2 = "UPDATE colegiado SET Estado = ?, FechaActualizacion = DATE(NOW())
                                    WHERE Id = ?";
                            $stmt2 = $db->prepare($sql_2);
                            $stmt2->execute([$estado, $idColegiado]);
                            $sql_2 = "INSERT INTO colegiadomovimientomesaentrada (IdColegiadoMovimiento, IdMesaEntrada)
                                    VALUES(?, ?)";
                            $stmt2 = $db->prepare($sql_2);
                            $stmt2->execute([$idColegiadoMovimiento, $idMesaEntrada]);
                        } else {
                            if ($idTipoMesaEntrada == '8') {
                                //agrega el movimiento anulado en la mesa
                                //obtenemos los datos del movimiento que se va a anular
                                $idMesaEntradaAnular = $datosTipoMesaEntrada['idMesaEntradaAnular'];
                //echo 'idMesaEntradaAnular->'.$idMesaEntradaAnular.'<br>';
                                $sql = "SELECT me.IdColegiado, me.EstadoMatricular, mem.IdMesaEntradaMovimiento, mem.IdTipoMovimiento, cm.Id, cm.IdMovimiento, cm.FechaHasta, cmme.IdMesaEntrada
                                        FROM mesaentrada me
                                        INNER JOIN mesaentradamovimiento mem ON mem.IdMesaEntrada = me.IdMesaEntrada
                                        INNER JOIN colegiadomovimientomesaentrada cmme ON cmme.IdMesaEntrada = me.IdMesaEntrada
                                        INNER JOIN colegiadomovimiento cm ON cm.Id = cmme.IdColegiadoMovimiento
                                        WHERE me.IdMesaEntrada = ?";
                                $stmt = $db->prepare($sql);
                                $stmt->execute([$idMesaEntradaAnular]);
                                $rowAnular = $stmt->fetch(PDO::FETCH_ASSOC);
                                if ($rowAnular !== false) {
                                    $idColegiado = $rowAnular['IdColegiado'];
                                    $idEstadoMatricularOrigen = $rowAnular['EstadoMatricular'];
                                    $idMesaEntradaMovimiento = $rowAnular['IdMesaEntradaMovimiento'];
                                    $idTipoMovimientoAnular = $rowAnular['IdTipoMovimiento'];
                                    $idColegiadoMovimiento = $rowAnular['Id'];
                                    $idTipoMovimientoColegiadoMovimiento = $rowAnular['IdMovimiento'];
                                    //se agrega el movimiento anulado en mesa de entrada
                                    $sql="INSERT INTO mesaentradamovimientoanulacion
                                            (IdMesaEntrada, IdMesaEntradaMovimiento)
                                            VALUES (?, ?)";
                                    $stmt = $db->prepare($sql);
                                    $stmt->execute([$idMesaEntrada, $idMesaEntradaMovimiento]);
                                    $idMesaEntradaMovimientoAnulado = $db->lastInsertId();
                                    $resultado['estado'] = TRUE;
                                    $resultado['mensaje'] = 'El movimiento se registro correctamente';
                                    $resultado['clase'] = 'alert alert-success';
                                    $resultado['icono'] = 'glyphicon glyphicon-ok';

                                    //segun el idtipomovimiento, si es rehabilitacion se anula el los datos de la rehabilitacion, sino se anula el registro de colegiadomovimiento
                                    if ($idTipoMovimientoAnular == 20) {
                                        //si es una rehabilitacion que se anula, el estado del movimiento queda 'O' OK
                                        $estadoMovimiento = 'O'; //Ok
                                    } else {
                                        $estadoMovimiento = 'A'; //Anulado
                                    }
                //echo 'idEstadoMatricularOrigen->'.$idEstadoMatricularOrigen.' - estadoMovimiento->'.$estadoMovimiento.' - idMesaEntradaAnular->'.$idMesaEntradaAnular.'<br>';
                                    $sql_2 = "UPDATE colegiado c
                                            INNER JOIN colegiadomovimiento cm ON cm.IdColegiado = c.Id
                                            INNER JOIN colegiadomovimientomesaentrada cmme ON cmme.IdColegiadoMovimiento = cm.Id
                                            SET c.Estado = ?, c.FechaActualizacion = DATE(NOW()), cm.FechaHasta = NULL, cm.Estado = ?, cm.IdUsuarioCarga = 1, cm.FechaCarga = DATE(NOW()), cm.FechaCargaRehabilitacion = NULL, cm.IdUsuarioRehabilitador = NULL
                                            WHERE cmme.IdMesaEntrada = ?";
                                    $stmt2 = $db->prepare($sql_2);
                                    $stmt2->execute([$idEstadoMatricularOrigen, $estadoMovimiento, $idMesaEntradaAnular]);
                                } else {
                                    $resultado['estado'] = FALSE;
                                    $resultado['mensaje'] = "ERROR NO SE ENCONTRO EL MOVIMIENTO PARA ANULAR.";
                                    $resultado['clase'] = 'alert alert-danger';
                                    $resultado['icono'] = 'glyphicon glyphicon-remove';
                                }
                            } else {
                                $resultado['estado'] = FALSE;
                                $resultado['mensaje'] = "ERROR TIPO MESA ENTRADA ".$idTipoMesaEntrada;
                                $resultado['clase'] = 'alert alert-danger'; 
                                $resultado['icono'] = 'glyphicon glyphicon-remove';
                            }
                        }

                        if ($resultado['estado']) {                            
                            //verificar deuda
                            //se verifica si genera cuenta corriente
                            if ($idTipoMesaEntrada == 8) {
                                //anula mesa de entrada
                                if ($idTipoMovimientoAnular == 20) {
                                    //si fue una rehabilitacion lo que se anula, entonces se debe obtener si el tipo de movimiento rehablitado genera o no deuda (idTipoMovimientoColegiadoMovimiento)
                                    $movimientoAnteriorGeneroCtaCte = $this->elMovimientoGeneraCtaCte($idTipoMovimientoColegiadoMovimiento);
                                } else {
                                    //obtenemos los datos del movimiento anterior si es que tiene
                                    $sql_2 = "SELECT cm.IdMovimiento, cm.FechaDesde, tm.GeneraCtaCte
                                            FROM colegiadomovimiento cm
                                            INNER JOIN tipomovimiento tm ON tm.Id = cm.IdMovimiento
                                            WHERE cm.IdColegiado = ? AND cm.Estado = 'O' AND cm.Id <> ? AND cm.FechaHasta IS NULL
                                            ORDER BY cm.Id DESC
                                            LIMIT 1";
                                    $stmt2 = $db->prepare($sql_2);
                                    $stmt2->execute([$idColegiado, $idColegiadoMovimiento]);
                                    $row2 = $stmt2->fetch(PDO::FETCH_ASSOC);
                                    if ($row2 !== false) {
                                        $movimientoAnteriorGeneroCtaCte = $row2['GeneraCtaCte'];
                                    } else {
                                        //si no encontro ningun moviento anterior se toma como que era colegiado y se debe generar la deuda
                                        $movimientoAnteriorGeneroCtaCte = 'C';
                                    }
                                }
                //echo 'idColegiado->'.$idColegiado.' - idColegiadoMovimiento->'.$idColegiadoMovimiento.' - movimientoAnteriorGeneroCtaCte->'.$movimientoAnteriorGeneroCtaCte.'<br>';
                                $actualizarDeuda = FALSE;
                                if ($movimientoAnteriorGeneroCtaCte == 'C') {
                                    $actualizarDeuda = TRUE;
                                    $estadoCuotaActual = 5;
                                    $estadoCuotaSetear = 1;
                                } else {
                                    if ($movimientoAnteriorGeneroCtaCte == 'B') {
                                        $actualizarDeuda = TRUE;
                                        $estadoCuotaActual = 1;
                                        $estadoCuotaSetear = 5;
                                    }
                                }
                                /*
                                if ($movimientoNuevoGeneroCtaCte == 'C') {
                                    //si el movimiento a borrar genero deuda, debo verificar si el movimiento original del colegiado, genero o no deuda
                                    if ($movimientoAnteriorGeneroCtaCte <> 'C') {
                                        //si el movimiento original del colegiado NO genero deuda, entonces debo anular la que genero el nuevo movimiento
                                        $actualizarDeuda = TRUE;
                                        $estadoCuotaActual = 1;
                                        $estadoCuotaSetear = 5;
                                    }
                                } else {
                                    if ($movimientoNuevoGeneroCtaCte == 'B') {
                                        //si el movimiento a borrar borró deuda, debo verificar si el movimiento original del colegiado, genero o no deuda
                                        if ($movimientoAnteriorGeneroCtaCte == 'C') {
                                            //si el movimiento original del colegiado NO genero deuda, entonces debo anular la que genero el nuevo movimiento
                                            $actualizarDeuda = TRUE;
                                            $estadoCuotaActual = 5;
                                            $estadoCuotaSetear = 1;
                                        }
                                    } else {
                                        
                                    }
                                }
                                */
                                if ($actualizarDeuda) {
                                    $sql_2 = "UPDATE colegiadodeudaanualcuotas dac
                                            INNER JOIN colegiadodeudaanual da ON da.Id = dac.IdColegiadoDeudaAnual AND da.Estado = 'A'
                                            SET dac.Estado = ?, dac.FechaActualizacion = DATE(NOW())
                                            WHERE da.IdColegiado = ? AND dac.Estado = ?";
                                    $stmt2 = $db->prepare($sql_2);
                                    $stmt2->execute([$estadoCuotaSetear, $idColegiado, $estadoCuotaActual]);
                                }
                            } else {
                                $generaCtaCte = $this->elMovimientoGeneraCtaCte($idTipoMovimiento);
                                if ($generaCtaCte == 'C') {
                                    //si es 5 (Ingreso definitivo) o 10 (Inscripto a otro Distrito), se le genera la deuda a partir de la vencimiento de la fecha del movimiento, 1 nuevo matriculado o 20 (Rehabilitación)

                                    $periodoDesde = substr($fechaMovimiento, 0, 4);
                                    if (substr($fechaMovimiento, 5, 2) < '07') {
                                        $periodoDesde -= 1;
                                    }

                                    //obtenemos los peridos a liquidar la deuda
                                    $sql_1 = "SELECT ca.Id, ca.Periodo, ca.Antiguedad, ca.Importe, ca.PrimerVencimiento, ca.Cuotas, ca.PagoTotal, ca.VencimientoPagoTotal
                                            FROM colegiacion_anual ca
                                            LEFT JOIN colegiadodeudaanual da ON da.IdColegiado = ? AND da.Periodo = ca.Periodo AND da.Estado <> 'B'
                                            WHERE ca.Periodo >= ? AND ca.Borrado = 0 AND ca.Antiguedad = (SELECT if (TIMESTAMPDIFF(YEAR, ct.FechaTitulo, DATE(NOW())) >= 5, 2, 1) AS Antiguedad FROM colegiadotitulo ct WHERE ct.IdColegiado = ?)
                                            AND da.Id IS NULL";
                                    $stmt1 = $db->prepare($sql_1);
                                    $stmt1->execute([$idColegiado, $periodoDesde, $idColegiado]);
                                    $rows1 = $stmt1->fetchAll(PDO::FETCH_ASSOC);
                                    if (count($rows1) > 0) {
                                        //genera la deuda anual de los que no estan generados
                                        foreach ($rows1 as $row1) {
                                            $idColegiacionAnual = $row1['Id'];
                                            $periodo = $row1['Periodo'];
                                            $antiguedad = $row1['Antiguedad'];
                                            $importe = $row1['Importe'];
                                            $cuotas = $row1['Cuotas'];
                                            //agregamos todos los periodos encontrados
                                            $sql_2 = "INSERT INTO colegiadodeudaanual (IdColegiado, Periodo, Importe, Cuotas, Antiguedad, EstadoMatricular, FechaCreacion)
                                                    VALUE (?, ?, ?, ?, ?, ?, DATE(NOW()))";
                                            $stmt2 = $db->prepare($sql_2);
                                            $stmt2->execute([$idColegiado, $periodo, $importe, $cuotas, $antiguedad, $estadoMatricular]);
                                            $idColegiadoDeudaAnual = $db->lastInsertId();

                                            $sql_2 = "INSERT INTO colegiadodeudaanualcuotas (IdColegiadoDeudaAnual, Cuota, Importe, FechaVencimiento, Recargo, SegundoVencimiento, Estado)
                                                (SELECT ?, cac.Cuota, cac.Importe, cac.FechaVencimiento, cac.Importe, cac.FechaVencimiento, IF (cac.FechaVencimiento > ?, 1, 5) AS Estado FROM colegiacion_anual_cuota cac WHERE cac.IdColegiacionAnual = ? AND cac.Borrado = 0)";
                                            //agregamos las cuotas del periodo
                                            $stmt2 = $db->prepare($sql_2);
                                            $stmt2->execute([$idColegiadoDeudaAnual, $fechaMovimiento, $idColegiacionAnual]);
                                        }
                                    } else {
                                        //reactiva las cuotas en estado 5 de los que ya estaban generados
                                        $sql_2 = "UPDATE colegiadodeudaanualcuotas dac
                                                INNER JOIN colegiadodeudaanual da ON da.Id = dac.IdColegiadoDeudaAnual
                                                SET dac.Estado = 1, dac.FechaActualizacion = DATE(NOW())
                                                WHERE da.IdColegiado = ? AND dac.Estado = 5 AND dac.FechaVencimiento > ?";
                                        $stmt2 = $db->prepare($sql_2);
                                        $stmt2->execute([$idColegiado, $fechaMovimiento]);
                                    }
                                } else {
                                    //si es cancelacion por cualquier motivo, cambiamos el estado de las cuotas de 1 (Deuda) a 5 (No Aplica Deuda)
                                    if ($generaCtaCte == 'B') {
                                        $sql_2 = "UPDATE colegiadodeudaanualcuotas dac
                                                INNER JOIN colegiadodeudaanual da ON (da.Id = dac.IdColegiadoDeudaAnual)
                                                SET dac.Estado = 5, dac.FechaActualizacion = DATE(NOW())
                                                WHERE da.IdColegiado = ? AND dac.Estado = 1";
                                        $stmt2 = $db->prepare($sql_2);
                                        $stmt2->execute([$idColegiado]);
                                    }
                                }
                            }
                        }                            
                        break;
                    
                    case '3':
                        // Notas
                        $tema = $datosTipoMesaEntrada['tema'];
                        $incluyeMovimiento = $datosTipoMesaEntrada['incluyeMovimiento'];

                        $sql="INSERT INTO mesaentradanota
                                (IdMesaEntrada, Tema, Estado, IncluyeMovimiento)
                                VALUES (?, ?, 'A', ?)";
                        $stmt = $db->prepare($sql);
                        $stmt->execute([$idMesaEntrada, $tema, $incluyeMovimiento]);
                        $idMesaEntradaNota = $db->lastInsertId();
                        $resultado['estado'] = TRUE;
                        $resultado['mensaje'] = 'La Nota/Oficio se registro correctamente';
                        $resultado['clase'] = 'alert alert-success';
                        $resultado['icono'] = 'glyphicon glyphicon-ok';
                        break;

                    case '4':
                        //habilitacion de consultorios
                        $idEspecialidad = $datosTipoMesaEntrada['idEspecialidad'];
                        $idEspecialidadAlternativa = $datosTipoMesaEntrada['idEspecialidadAlternativa'];
                        $tipoConsultorio = $datosTipoMesaEntrada['tipoConsultorio'];
                        $nombreConsultorio = $datosTipoMesaEntrada['nombreConsultorio'];
                        $cantidadConsultorios = $datosTipoMesaEntrada['cantidadConsultorios'];
                        $calle = $datosTipoMesaEntrada['calle'];
                        $lateral = $datosTipoMesaEntrada['lateral'];
                        $numero = $datosTipoMesaEntrada['numero'];
                        $piso = $datosTipoMesaEntrada['piso'];
                        $departamento = $datosTipoMesaEntrada['departamento'];
                        $telefono = $datosTipoMesaEntrada['telefono'];
                        $idLocalidad = $datosTipoMesaEntrada['idLocalidad'];
                        $codigoPostal = $datosTipoMesaEntrada['codigoPostal'];
                        $observaciones = $datosTipoMesaEntrada['observaciones'];

                        $sql = "INSERT INTO consultorio (TipoConsultorio, Nombre, Calle, Lateral, Numero, Piso, Departamento, Telefono, IdLocalidad, CodigoPostal, Estado, FechaCarga, IdUsuario, Observaciones, CantidadConsultorios)
                            VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'A', DATE(NOW()), ?, ?, ?)";
                        $stmt = $db->prepare($sql);
                        $stmt->execute([$tipoConsultorio, $nombreConsultorio, $calle, $lateral, $numero, $piso, $departamento, $telefono, $idLocalidad, $codigoPostal, $_SESSION['user_id'], $observaciones, $cantidadConsultorios]);
                        $idConsultorio = $db->lastInsertId();

                        $sql="INSERT INTO mesaentradaconsultorio
                                (IdMesaEntrada, IdConsultorio, IdEspecialidad, IdEspecialidadAlternativa, Estado)
                                VALUES (?, ?, ?, ?, 'A')";
                        $stmt = $db->prepare($sql);
                        $stmt->execute([$idMesaEntrada, $idConsultorio, $idEspecialidad, $idEspecialidadAlternativa]);
                        $idMesaEntradaConsultorio = $db->lastInsertId();
                        $resultado['estado'] = TRUE;
                        $resultado['idMesaEntradaConsultorio'] = $idMesaEntradaConsultorio;
                        $resultado['mensaje'] = 'La Habilitacion de consultorio se registro correctamente';
                        $resultado['clase'] = 'alert alert-success';
                        $resultado['icono'] = 'glyphicon glyphicon-ok';
                        break;

                    case '5': //Matricula J
                        $resultado['estado'] = TRUE;
                        $resultado['mensaje'] = 'La matrícula J se registro correctamente';
                        $resultado['clase'] = 'alert alert-success'; 
                        $resultado['icono'] = 'glyphicon glyphicon-ok';
                        break;

                    case '7': //Autoprescripción
                        $fecha = $datosTipoMesaEntrada['fecha'];
                        $autorizado1 = $datosTipoMesaEntrada['autorizado1'];
                        $documentoAutorizado1 = $datosTipoMesaEntrada['documentoAutorizado1'];
                        $parentescoAutorizado1 = $datosTipoMesaEntrada['parentescoAutorizado1'];
                        $autorizado2 = $datosTipoMesaEntrada['autorizado2'];
                        $documentoAutorizado2 = $datosTipoMesaEntrada['documentoAutorizado2'];
                        $parentescoAutorizado2 = $datosTipoMesaEntrada['parentescoAutorizado2'];
                        $sql="INSERT INTO mesaentradaautoprescripcion
                                (IdMesaEntrada, Fecha, Autorizado, DocumentoAutorizado, Parentezco, Autorizado2, DocumentoAutorizado2, Parentezco2)
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                        $stmt = $db->prepare($sql);
                        $stmt->execute([$idMesaEntrada, $fecha, $autorizado1, $documentoAutorizado1, $parentescoAutorizado1, $autorizado2, $documentoAutorizado2, $parentescoAutorizado2]);
                        $idMesaEntradaAutoprescripcion = $db->lastInsertId();
                        $resultado['estado'] = TRUE;
                        $resultado['mensaje'] = 'La Autoprescripción se registro correctamente';
                        $resultado['clase'] = 'alert alert-success';
                        $resultado['icono'] = 'glyphicon glyphicon-ok';
                        break;

                    case '9': //Denuncia de Extravío o Falsificación
                        $fechaDenuncia = $datosTipoMesaEntrada['fechaDenuncia'];
                        $fechaExtravio = $datosTipoMesaEntrada['fechaExtravio'];
                        $idTipoDenuncia = $datosTipoMesaEntrada['idTipoDenuncia'];

                        $sql="INSERT INTO mesaentradadenuncia (IdMesaEntrada, FechaDenuncia, FechaExtravio, IdTipoDenuncia)
                            VALUES (?, ?, ?, ?)";
                        $stmt = $db->prepare($sql);
                        $stmt->execute([$idMesaEntrada, $fechaDenuncia, $fechaExtravio, $idTipoDenuncia]);
                        $idMesaEntradaDenuncia = $db->lastInsertId();
                        $resultado['estado'] = TRUE;
                        $resultado['mensaje'] = 'La Denuncia se registro correctamente';
                        $resultado['clase'] = 'alert alert-success';
                        $resultado['icono'] = 'glyphicon glyphicon-ok';
                        break;

                    case '10': //Entregas
                        $fechaEntrega = $datosTipoMesaEntrada['fechaEntrega'];
                        $idTipoEntrega = $datosTipoMesaEntrada['idTipoEntrega'];
                        $idTituloEspecialista = $datosTipoMesaEntrada['idTituloEspecialista'];
                        $sql="INSERT INTO mesaentradaentrega
                                (IdMesaEntrada, FechaEntrega, IdTipoEntrega)
                                VALUES (?, ?, ?)";
                        $stmt = $db->prepare($sql);
                        $stmt->execute([$idMesaEntrada, $fechaEntrega, $idTipoEntrega]);
                        $idMesaEntradaEntrega = $db->lastInsertId();
                        $resultado['estado'] = TRUE;
                        $resultado['mensaje'] = 'La Entrega se registro correctamente';
                        $resultado['clase'] = 'alert alert-success';
                        $resultado['icono'] = 'glyphicon glyphicon-ok';

                        if (isset($idTituloEspecialista) && $idTituloEspecialista <> "") {
                            //marco la fecha de entrega
                            $sql_entrega = "UPDATE tituloespecialista
                                            SET FechaEntrega = NOW(), IdUsuarioEntrega = ?, IdMesaEntradaEntrega = ?
                                            WHERE IdTituloEspecialista = ?";
                            $stmt_entrega = $db->prepare($sql_entrega);
                            $stmt_entrega->execute([$_SESSION['user_id'], $idMesaEntradaEntrega, $idTituloEspecialista]);
                        } else {
                            //si tipoentrega es habilitacion de consultorio
                            if ($idTipoEntrega == 4 || $idTipoEntrega == 7) {
                                //se marca como retirado
                                $sql_retiro = "UPDATE retirodocumentacion
                                            SET Estado = 'E',
                                                IdUsuarioRetiro = ?,
                                                FechaRetiro = NOW(),
                                                IdUsuarioBorrado = NULL,
                                                FechaBorrado = NULL
                                            WHERE IdColegiado = ? AND Estado = 'A'";
                                $stmt_retiro = $db->prepare($sql_retiro);
                                $stmt_retiro->execute([$_SESSION['user_id'], $idColegiado]);
                                $resultado['estado'] = TRUE;
                                $resultado['mensaje'] = 'La Habilitacion de consultorio se registro correctamente';
                                $resultado['clase'] = 'alert alert-success';
                                $resultado['icono'] = 'glyphicon glyphicon-ok';
                            }
                        }
                        break;

                    default:
                        // code...
                        break;
                }
            }

            if ($resultado['estado']) {
                $resultado['idMesaEntrada'] = $idMesaEntrada;
                $db->commit();
                return $resultado;
            } else {
                $db->rollBack();
                return $resultado;
            }
        } catch (PDOException $e) {
            $db->rollBack();
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "ERROR AL AGREGAR mesaentrada: ".$e->getMessage();
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-remove';
            return $resultado;
        }
    }

    function borrarMesaEntrada($idMesaEntrada) {
        $db = Database::getConnection();
        try {
            /* Autocommit false para la transaccion */
            $db->beginTransaction();
            $datos_borrados = array();
            $resultado = array();
            $sql = "SELECT me.FechaIngreso, me.IdTipoMesaEntrada, me.EstadoMatricular, me.IdColegiado, me.IdRemitente
                    FROM mesaentrada me
                    WHERE me.IdMesaEntrada = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$idMesaEntrada]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row !== false) {
                $fechaIngreso = $row['FechaIngreso'];
                    $idTipoMesaEntrada = $row['IdTipoMesaEntrada'];
                    $estadoMatricular = $row['EstadoMatricular'];
                    $idColegiado = $row['IdColegiado'];
                    $idRemitente = $row['IdRemitente'];
                    $resultado['estado'] = TRUE;
                    switch ($idTipoMesaEntrada) {
                        case '1':
                            //Movimientos Matriculares
                            $sql = "SELECT mem.IdMesaEntradaMovimiento, mem.IdTipoMovimiento, mem.Fecha
                                    FROM mesaentradamovimiento mem
                                    WHERE mem.IdMesaEntrada = ?";
                            $stmt = $db->prepare($sql);
                            $stmt->execute([$idMesaEntrada]);
                            $row = $stmt->fetch(PDO::FETCH_ASSOC);
                            if ($row !== false) {
                                $idMesaEntradaMovimiento = $row['IdMesaEntradaMovimiento'];
                                $idTipoMovimiento = $row['IdTipoMovimiento'];
                                $fechaMovimiento = $row['Fecha'];

                                if ($idTipoMovimiento == 20) {
                                    //si es una rehabilitacion que se anula, el estado del movimiento queda 'O' OK
                                    $estadoMovimiento = 'O'; //Ok
                                } else {
                                    $estadoMovimiento = 'A'; //Anulado
                                }
                                $sql_2 = "UPDATE colegiado c
                                        INNER JOIN colegiadomovimiento cm ON cm.IdColegiado = c.Id
                                        INNER JOIN colegiadomovimientomesaentrada cmme ON cmme.IdColegiadoMovimiento = cm.Id
                                        SET c.Estado = ?, c.FechaActualizacion = DATE(NOW()), cm.FechaHasta = NULL, cm.Estado = ?, cm.IdUsuarioCarga = 1, cm.FechaCarga = DATE(NOW()), cm.FechaCargaRehabilitacion = NULL, cm.IdUsuarioRehabilitador = NULL
                                        WHERE cmme.IdMesaEntrada = ?";
                                $stmt2 = $db->prepare($sql_2);
                                $stmt2->execute([$estadoMatricular, $estadoMovimiento, $idMesaEntrada]);
                                $resultado['estado'] = TRUE;
                                $resultado['mensaje'] = 'Mesa de Entradas se borro correctamente. Movimientos Matriculares';
                                $resultado['clase'] = 'alert alert-success';
                                $resultado['icono'] = 'glyphicon glyphicon-ok';
                                //se verifica si genera cuenta corriente
                                $movimientoNuevoGeneroCtaCte = $this->elMovimientoGeneraCtaCte($idTipoMovimiento);
                                $movimientoAnteriorGeneroCtaCte = $this->elMovimientoGeneraCtaCte($estadoMatricular);
                                $actualizarDeuda = FALSE;
                                if ($movimientoNuevoGeneroCtaCte == 'C') {
                                    //si el movimiento a borrar genero deuda, debo verificar si el movimiento original del colegiado, genero o no deuda
                                    if ($movimientoAnteriorGeneroCtaCte <> 'C') {
                                        //si el movimiento original del colegiado NO genero deuda, entonces debo anular la que genero el nuevo movimiento
                                        $actualizarDeuda = TRUE;
                                        $estadoCuotaActual = 1;
                                        $estadoCuotaSetear = 5;
                                    }
                                } else {
                                    if ($movimientoNuevoGeneroCtaCte == 'B') {
                                        //si el movimiento a borrar borró deuda, debo verificar si el movimiento original del colegiado, genero o no deuda
                                        if ($movimientoAnteriorGeneroCtaCte == 'C') {
                                            //si el movimiento original del colegiado NO genero deuda, entonces debo anular la que genero el nuevo movimiento
                                            $actualizarDeuda = TRUE;
                                            $estadoCuotaActual = 5;
                                            $estadoCuotaSetear = 1;
                                        }
                                    }
                                }
                                if ($actualizarDeuda) {
                                    $sql_2 = "UPDATE colegiadodeudaanualcuotas dac
                                            INNER JOIN colegiadodeudaanual da ON da.Id = dac.IdColegiadoDeudaAnual AND da.Estado = 'A'
                                            SET dac.Estado = ?, dac.FechaActualizacion = DATE(NOW())
                                            WHERE da.IdColegiado = ? AND dac.Estado = ?";
                                    $stmt2 = $db->prepare($sql_2);
                                    $stmt2->execute([$estadoCuotaSetear, $idColegiado, $estadoCuotaActual]);
                                }
                            } else {
                                $resultado['estado'] = FALSE;
                                $resultado['mensaje'] = "ERROR AL BORRAR MESA ENTRADAS -> NO ENCONTRO MOVIMIENTO";
                                $resultado['clase'] = 'alert alert-danger';
                                $resultado['icono'] = 'glyphicon glyphicon-remove';
                            }
                            break;
                        
                        case '2':
                            //Especialidades
                            break;
                        
                        case '3': // Notas
                            $datos_borrados = array(
                                            'tipo' => 'NOTA'
                                            );
                            break;
                        case '4': // Habilitación de Consultorio
                            //se anula el consultorio a habilitar
                            $sql = "SELECT mec.IdMesaEntradaConsultorio, mec.IdConsultorio
                                    FROM mesaentradaconsultorio mec
                                    WHERE mec.IdMesaEntrada = ?";
                            $stmt = $db->prepare($sql);
                            $stmt->execute([$idMesaEntrada]);
                            $row = $stmt->fetch(PDO::FETCH_ASSOC);
                            if ($row !== false) {
                                $idMesaEntradaConsultorio = $row['IdMesaEntradaConsultorio'];
                                $idConsultorio = $row['IdConsultorio'];
                                $resultado['estado'] = TRUE;

                                $sql_2 = "UPDATE consultorio
                                        SET Estado = 'B', IdUsuario = ?
                                        WHERE IdConsultorio = ?";
                                $stmt2 = $db->prepare($sql_2);
                                $stmt2->execute([$_SESSION['user_id'], $idConsultorio]);
                                $datos_borrados = array(
                                            'tipo' => 'Habilitación de Consultorio ID('.$idConsultorio.')'
                                            );
                            }
                            break;
                        case '5': // Matricula J
                            $datos_borrados = array(
                                            'tipo' => 'Matricula J'
                                            );
                            break;
                        case '7': // Autoprescripción
                            $datos_borrados = array(
                                            'tipo' => 'Autoprescripción'
                                            );
                            break;
                        case '8': // Anulación de movimientos
                            $datos_borrados = array(
                                            'tipo' => 'Anulación de movimientos'
                                            );
                            break;
                        case '9': // Denuncia de Extravío o Falsificación
                            $datos_borrados = array(
                                            'tipo' => 'Denuncia de Extravío o Falsificación'
                                            );
                        case '10': // Entregas
                            $datos_borrados = array(
                                            'tipo' => 'Entregas'
                                            );
                            break;
                        
                        default:
                            $resultado['estado'] = FALSE;
                            $resultado['mensaje'] = "ERROR AL BORRAR MESA ENTRADAS -> INGRESO INCORRECTO";
                            $resultado['clase'] = 'alert alert-danger'; 
                            $resultado['icono'] = 'glyphicon glyphicon-remove';
                            break;
                    }
            }

            if ($resultado['estado']) {
                $sql = "UPDATE mesaentrada SET Estado = 'B' WHERE IdMesaEntrada = ?";
                $stmt = $db->prepare($sql);
                $stmt->execute([$idMesaEntrada]);
                $tipoMovimiento = "borrado";
                $datos_borrados_guardar = serialize($datos_borrados);
                $sql="INSERT INTO log_mesa_entrada (Tabla, IdTabla, Fecha, TipoMovimiento, IdUsuario, Datos)
                    VALUES ('mesaentrada', ?, NOW(), ?, ?, ?)";
                $stmt = $db->prepare($sql);
                $stmt->execute([$idMesaEntrada, $tipoMovimiento, $_SESSION['user_id'], $datos_borrados_guardar]);
                $resultado['estado'] = TRUE;
                $resultado['mensaje'] = 'Mesa de Entradas se borro correctamente';
                $resultado['clase'] = 'alert alert-success';
                $resultado['icono'] = 'glyphicon glyphicon-ok';
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
            return $resultado;
        }
    }

    function modificarMesaEntradaNota($idMesaEntradaNota, $tema, $incluyeMovimiento, $observaciones, $datosAnteriores) {
        $db = Database::getConnection();
        try {
            /* Autocommit false para la transaccion */
            $db->beginTransaction();
            $sql="UPDATE mesaentradanota men
                INNER JOIN mesaentrada me on me.IdMesaEntrada = men.IdMesaEntrada
                SET men.Tema = ?, men.IncluyeMovimiento = ?, me.Observaciones = ?
                WHERE men.IdMesaEntradaNota = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$tema, $incluyeMovimiento, $observaciones, $idMesaEntradaNota]);
            $resultado = array();
            //guardo log
            $tipoMovimiento = 'modificacion';
            $datos = serialize($datosAnteriores);
            $sql="INSERT INTO log_tabla (Tabla, IdTabla, Fecha, TipoMovimiento, IdUsuario, Datos)
                VALUES ('mesaentradanota', ?, NOW(), ?, ?, ?)";
            $stmt = $db->prepare($sql);
            $stmt->execute([$idMesaEntradaNota, $tipoMovimiento, $_SESSION['user_id'], $datos]);
            $resultado['estado'] = TRUE;
            $resultado['mensaje'] = 'LA NOTA/OFICIO HA SIDO GUARDADO';
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';

            $db->commit();
            return $resultado;
        } catch (PDOException $e) {
            $db->rollBack();
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "Error guardando NOTA/OFICIO -> ".$e->getMessage();
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
            return $resultado;
        }
    }

    function modificarMesaEntradaConsultorio($idMesaEntradaConsultorio, $idEspecialidad, $idEspecialidadAlternativa, $tipoConsultorio, $nombreConsultorio, $cantidadConsultorios, $calle, $lateral, $numero, $piso, $departamento, $telefono, $idLocalidad, $codigoPostal, $datosAnteriores) {
        $db = Database::getConnection();
        try {
            /* Autocommit false para la transaccion */
            $db->beginTransaction();
            $sql="UPDATE mesaentradaconsultorio mec
                INNER JOIN consultorio c ON c.IdConsultorio = mec.IdConsultorio
                SET mec.IdEspecialidad = ?, mec.IdEspecialidadAlternativa = ?,
                c.TipoConsultorio = ?, c.Nombre = ?, c.Calle = ?, c.Lateral = ?, c.Numero = ?, c.Piso = ?, c.Departamento = ?, c.Telefono = ?, c.IdLocalidad = ?, c.CodigoPostal = ?, c.FechaCarga = DATE(NOW()), c.IdUsuario = ?, c.CantidadConsultorios = ?
                WHERE mec.IdMesaEntradaConsultorio = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$idEspecialidad, $idEspecialidadAlternativa, $tipoConsultorio, $nombreConsultorio, $calle, $lateral, $numero, $piso, $departamento, $telefono, $idLocalidad, $codigoPostal, $_SESSION['user_id'], $cantidadConsultorios, $idMesaEntradaConsultorio]);
            $resultado = array();
            //guardo log
            $tipoMovimiento = 'modificacion';
            $datos = serialize($datosAnteriores);
            $sql="INSERT INTO log_tabla (Tabla, IdTabla, Fecha, TipoMovimiento, IdUsuario, Datos)
                VALUES ('mesaentradaconsultorio', ?, NOW(), ?, ?, ?)";
            $stmt = $db->prepare($sql);
            $stmt->execute([$idMesaEntradaConsultorio, $tipoMovimiento, $_SESSION['user_id'], $datos]);
            $resultado['estado'] = TRUE;
            $resultado['mensaje'] = 'EL CONSULTORIO HA SIDO GUARDADO';
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';

            $db->commit();
            return $resultado;
        } catch (PDOException $e) {
            $db->rollBack();
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "Error guardando CONSULTORIO -> ".$e->getMessage();
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
            return $resultado;
        }
    }

    //medicos autorizados para habilitacion de consultorios
    function obtenerMesaEntradaConsultorioOtrosMedicos($idMesaEntradaConsultorio) {
        $db = Database::getConnection();
        $sql="SELECT meca.IdMesaEntradaConsultorioAutorizado, meca.IdColegiado, c.Matricula, p.Apellido, p.Nombres
            FROM mesaentradaconsultorioautorizado as meca
            INNER JOIN colegiado as c ON (c.Id = meca.IdColegiado)
            INNER JOIN persona as p ON (p.Id = c.IdPersona)
            WHERE meca.IdMesaEntradaConsultorio = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idMesaEntradaConsultorio]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $resultado = array();
        if (count($rows) > 0) {
            $datos = array();
            foreach ($rows as $row) {
                $item = array(
                        'idMesaEntradaConsultorioAutorizado' => $row['IdMesaEntradaConsultorioAutorizado'],
                        'idColegiado' => $row['IdColegiado'],
                        'matricula' => $row['Matricula'],
                        'apellido' => $row['Apellido'],
                        'nombre' => $row['Nombres']
                );
                array_push($datos, $item);
            }

            $resultado['estado'] = true;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = false;
            $resultado['mensaje'] = "No hay mesa de entrada medicos";
            $resultado['clase'] = 'alert alert-info';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }

        return $resultado;
    }
    
    function agregarMesaEntradaConsultorioAutorizado($idMesaEntradaConsultorio, $idColegiadoAutorizado) {
        $db = Database::getConnection();
        $resultado = array();
        try {
            $sql = "INSERT INTO mesaentradaconsultorioautorizado (IdMesaEntradaConsultorio, IdColegiado)
                VALUES(?, ?)";
            $stmt = $db->prepare($sql);
            $stmt->execute([$idMesaEntradaConsultorio, $idColegiadoAutorizado]);
            $resultado['estado'] = TRUE;
            $resultado['mensaje'] = 'Médico Autorizado se registro correctamente';
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } catch (PDOException $e) {
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "ERROR AL AGREGAR Médico Autorizado";
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-remove';
        }
        return $resultado;
    }

    function borrarMesaEntradaConsultorioAutorizado($idMesaEntradaConsultorioAutorizado) {
        $db = Database::getConnection();
        $resultado = array();
        try {
            $sql = "DELETE FROM mesaentradaconsultorioautorizado
                WHERE IdMesaEntradaConsultorioAutorizado = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$idMesaEntradaConsultorioAutorizado]);
            $resultado['estado'] = TRUE;
            $resultado['mensaje'] = 'Médico Autorizado se borro correctamente';
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } catch (PDOException $e) {
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "ERROR AL BORRAR Médico Autorizado";
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-remove';
        }
        return $resultado;
    }

    function obtenerTiposMesaEntrada(){
        $db = Database::getConnection();
        $sql = "SELECT IdTipoMesaEntrada, Nombre
                FROM tipomesaentrada";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $resultado = array();
        if (count($rows) > 0) {
            $datos = array();
            foreach ($rows as $row) {
                $item = array(
                    'id' => $row['IdTipoMesaEntrada'],
                    'nombre' => $row['Nombre']
                );
                array_push($datos, $item);
            }
            $resultado['estado'] = true;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = true;
            $resultado['datos'] = NULL;
            $resultado['mensaje'] = "No hay tipos de mesa de entrada";
            $resultado['clase'] = 'alert alert-warning';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
        return $resultado;

    }

    function existeMovimientoParaColegiadoFecha($idColegiado, $idTipoMovimiento) {
        $db = Database::getConnection();
        $sql = "SELECT count(*)
            FROM mesaentrada me
            INNER JOIN mesaentradamovimiento mem ON mem.IdMesaEntrada = me.IdMesaEntrada
            WHERE me.IdColegiado = ?
            AND me.FechaIngreso = substr(NOW(), 1, 10)
            AND me.Estado = 'A'";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idColegiado]);
        $row = $stmt->fetch(PDO::FETCH_NUM);
        if ($row !== false && $row[0] > 0) {
            $respuesta = TRUE;
        } else {
            $respuesta = FALSE;
        }
        return $respuesta;
    }

    function elMovimientoGeneraCtaCte($idTipoMovimiento) {
        $db = Database::getConnection();
        $sql = "SELECT tm.GeneraCtaCte FROM tipomovimiento tm WHERE tm.Id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idTipoMovimiento]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row !== false) {
            $respuesta = $row['GeneraCtaCte'];
        } else {
            $respuesta = NULL;
        }
        return $respuesta;
    }

    function elMovimientoSePuedeAnular($idMesaEntrada) {
        $db = Database::getConnection();
        $sql = "SELECT SUM(a.Cantidad) FROM (
                (SELECT COUNT(me.IdMesaEntrada) AS Cantidad
                FROM mesaentrada me
                INNER JOIN mesaentradamovimiento mem ON mem.IdMesaEntrada = me.IdMesaEntrada
                INNER JOIN colegiadomovimientomesaentrada cmme ON cmme.IdMesaEntrada = me.IdMesaEntrada
                INNER JOIN colegiadomovimiento cm ON cm.Id = cmme.IdColegiadoMovimiento AND cm.Estado = 'A'
                INNER JOIN mesaentradamovimientoanulacion mema ON mema.IdMesaEntradaMovimiento = mem.IdMesaEntradaMovimiento
                INNER JOIN mesaentrada me2 ON me2.IdMesaEntrada = mema.IdMesaEntrada AND me2.Estado = 'A'
                WHERE me.IdMesaEntrada = ?)
                UNION ALL
                (SELECT COUNT(me.IdMesaEntrada) AS Cantidad
                FROM mesaentrada me
                INNER JOIN mesaentradamovimientoanulacion meme ON meme.IdMesaEntrada = me.IdMesaEntrada
                WHERE me.IdMesaEntrada = ?)) AS a";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idMesaEntrada, $idMesaEntrada]);
        $row = $stmt->fetch(PDO::FETCH_NUM);
        if ($row !== false && $row[0] == 0) {
            $respuesta = TRUE;
        } else {
            $respuesta = FALSE;
        }
        return $respuesta;
    }

    function obtenerEstadisticasEntreFechas($fechaDesde, $fechaHasta) {
        $db = Database::getConnection();
        $sql = "SELECT tme.IdTipoMesaEntrada, tme.Nombre, (SELECT COUNT(me.IdMesaEntrada)
            FROM mesaentrada me
            WHERE tme.IdTipoMesaEntrada = me.IdTipoMesaEntrada
                AND FechaIngreso BETWEEN ? AND ?
            AND Estado = 'A') AS Cantidad
            FROM tipomesaentrada tme";
        $stmt = $db->prepare($sql);
        $stmt->execute([$fechaDesde, $fechaHasta]);
        $mainRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $resultado = array();
        if (count($mainRows) > 0) {
            $datos = array();
            foreach ($mainRows as $mainRow) {
                $idTipoMesaEntrada = $mainRow['IdTipoMesaEntrada'];
                $nombreTipoMesaEntrada = $mainRow['Nombre'];
                $cantidad = $mainRow['Cantidad'];
                    $detalle = array();
                    $consulta = TRUE;
                    switch ($idTipoMesaEntrada) {
                        case '1':
                            $sql_2 = "SELECT tm.Detalle, COUNT(me.IdMesaEntrada)
                                    FROM mesaentrada as me
                                    INNER JOIN mesaentradamovimiento as mem ON mem.IdMesaEntrada = me.IdMesaEntrada
                                    INNER JOIN tipomovimiento tm ON tm.Id = mem.IdTipoMovimiento
                                    WHERE me.IdTipoMesaEntrada = ?
                                    AND me.FechaIngreso BETWEEN ? AND ?
                                    AND me.Estado = 'A'
                                    GROUP BY tm.Detalle";
                            break;
                        
                        case '2':
                            $sql_2 = "SELECT te.Nombre, COUNT(me.IdMesaEntrada)
                                    FROM mesaentrada as me
                                    INNER JOIN mesaentradaespecialidad as mee ON mee.IdMesaEntrada = me.IdMesaEntrada
                                    INNER JOIN tipoespecialista te ON te.IdTipoEspecialista = mee.IdTipoEspecialista
                                    WHERE me.IdTipoMesaEntrada = ?
                                    AND me.FechaIngreso BETWEEN ? AND ?
                                    AND me.Estado = 'A'
                                    GROUP BY te.Nombre";
                            break;
                        
                        case '3':
                            $sql_2 = "(SELECT 'Notas de Colegiados', COUNT(me.IdMesaEntrada)
                                                    FROM mesaentrada as me
                                                    WHERE me.IdTipoMesaEntrada = ?
                                                    AND me.FechaIngreso BETWEEN ? AND ?
                                                    AND me.IdColegiado IS NOT NULL 
                                                    AND me.Estado = 'A')
                                        UNION ALL
                                        (SELECT 'Notas de Remitentes', COUNT(me.IdMesaEntrada)
                                                    FROM mesaentrada as me
                                                    WHERE me.IdTipoMesaEntrada = ?
                                                    AND me.FechaIngreso BETWEEN ? AND ?
                                                    AND me.IdRemitente IS NOT NULL 
                                                    AND me.Estado = 'A')";
                            break;

                        case '4':
                            $sql_2 = "SELECT c.TipoConsultorio, COUNT(me.IdMesaEntrada)
                                    FROM mesaentrada me
                                    INNER JOIN mesaentradaconsultorio mec ON mec.IdMesaEntrada = me.IdMesaEntrada
                                    INNER JOIN consultorio c ON c.IdConsultorio = mec.IdConsultorio
                                    WHERE me.IdTipoMesaEntrada = ?
                                    AND me.FechaIngreso BETWEEN ? AND ?
                                    AND me.Estado = 'A'
                                    GROUP BY c.TipoConsultorio";
                            break;

                        case '9':
                            $sql_2 = "SELECT td.Nombre, COUNT(me.IdMesaEntrada)
                                    FROM mesaentrada me
                                    INNER JOIN mesaentradadenuncia med ON med.IdMesaEntrada = me.IdMesaEntrada
                                    INNER JOIN tipodenuncia td ON td.Id = med.IdTipoDenuncia
                                    WHERE me.IdTipoMesaEntrada = ?
                                    AND me.FechaIngreso BETWEEN ? AND ?
                                    AND me.Estado = 'A'
                                    GROUP BY td.Nombre";
                            break;

                        case '10':
                            $sql_2 = "SELECT te.Nombre, COUNT(me.IdMesaEntrada)
                                    FROM mesaentrada me
                                    INNER JOIN mesaentradaentrega mee ON mee.IdMesaEntrada = me.IdMesaEntrada
                                    INNER JOIN tipoentrega te ON te.Id = mee.IdTipoEntrega
                                    WHERE me.IdTipoMesaEntrada = ?
                                    AND me.FechaIngreso BETWEEN ? AND ?
                                    AND me.Estado = 'A'
                                    GROUP BY te.Nombre";
                            break;

                        default:
                            $consulta = FALSE;
                            break;
                    }
                    if ($consulta) {
                        $stmt2 = $db->prepare($sql_2);
                        if ($idTipoMesaEntrada == 3) {
                            $stmt2->execute([$idTipoMesaEntrada, $fechaDesde, $fechaHasta, $idTipoMesaEntrada, $fechaDesde, $fechaHasta]);
                        } else {
                            $stmt2->execute([$idTipoMesaEntrada, $fechaDesde, $fechaHasta]);
                        }
                        $detalleRows = $stmt2->fetchAll(PDO::FETCH_NUM);
                        foreach ($detalleRows as $dr) {
                            $nombre = $dr[0];
                            $cantidad_detalle = $dr[1];
                            if ($idTipoMesaEntrada == 4) {
                                if ($nombre == 'U') {
                                    $nombre = 'Consultorios Únicos';
                                } else {
                                    if ($nombre == 'P') {
                                        $nombre = 'Policonsultorios';
                                    } else {
                                        if ($nombre == 'I') {
                                            $nombre = 'Instituciones';
                                        } else {
                                            $nombre = 'Sin dato';
                                        }
                                    }
                                }
                            }
                            $row = array(
                                'nombre' => $nombre,
                                'cantidad_detalle' => $cantidad_detalle
                            );
                            array_push($detalle, $row);
                        }
                    }
                    $row = array(
                        'idTipoMesaEntrada' => $idTipoMesaEntrada,
                        'nombreTipoMesaEntrada' => $nombreTipoMesaEntrada,
                        'cantidad' => $cantidad,
                        'detalle' => $detalle
                    );
                    array_push($datos, $row);
            }

            //agregamos los matriculados del periodo
            $resultado = array();
            $total = 0;
            $detalle = array();
            $sql_2 = "SELECT 'Nuevos Matriculados', COUNT(c.Id)
                    FROM colegiado c
                    WHERE c.FechaMatriculacion BETWEEN ? AND ?
                    AND c.DistritoOrigen = 1";
            $stmt2 = $db->prepare($sql_2);
            $stmt2->execute([$fechaDesde, $fechaHasta]);
            $matRows = $stmt2->fetchAll(PDO::FETCH_NUM);
            foreach ($matRows as $mr) {
                $total += $mr[1];
            }

            $row = array(
                'idTipoMesaEntrada' => 'matriculados',
                'nombreTipoMesaEntrada' => 'Nuevos Matriculados',
                'cantidad' => $total,
                'detalle' => $detalle
            );
            array_push($datos, $row);

            //agregamos los certificados solicitados
            $resultado = array();
            $total = 0;
            $detalle = array();
            $sql_2 = "SELECT tc.Detalle, COUNT(*) as cantidad
                    FROM solicitudcertificados sc
                    INNER JOIN tipocertificado tc ON tc.Id = sc.IdTipoCertificado
                    WHERE sc.FechaSolicitud BETWEEN ? AND ?
                    GROUP BY sc.IdTipoCertificado";
            $stmt2 = $db->prepare($sql_2);
            $stmt2->execute([$fechaDesde, $fechaHasta]);
            $certRows = $stmt2->fetchAll(PDO::FETCH_NUM);
            foreach ($certRows as $cr) {
                $row = array(
                    'nombre' => $cr[0],
                    'cantidad_detalle' => $cr[1]
                );
                array_push($detalle, $row);
                $total += $cr[1];
            }

            $row = array(
                'idTipoMesaEntrada' => 'certificados',
                'nombreTipoMesaEntrada' => 'Solicitudes de Certificados',
                'cantidad' => $total,
                'detalle' => $detalle
            );
            array_push($datos, $row);

            $resultado['estado'] = true;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = true;
            $resultado['datos'] = NULL;
            $resultado['mensaje'] = "No hay tipos de mesa de entrada";
            $resultado['clase'] = 'alert alert-warning';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
        return $resultado;
    }

}