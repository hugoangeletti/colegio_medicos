<?php
define('EXAMEN_COLEGIO', 1);
define('EXCEPTUADO_ART_8', 2);
define('JERARQUIZADO', 3);
define('CONSULTOR', 4);
define('CALIFICACION_AGREGADA', 5);
define('RECERTIFICACION', 6);
define('OTRO_DISTRITO', 7);
define('RECONOCIMIENTO_NACION', 8);
define('CONVENIO_UNLP', 9);

class resolucionesLogic {

    public function obtenerResolucionPorId($idResolucion){
    try {
        $db = Database::getConnection();
        $sql="SELECT r.*, tr.Detalle, tr.TipoEspecialista
            FROM resolucion r
            INNER JOIN tiporesolucion tr ON(tr.Id = r.TipoResolucion)
            WHERE r.Id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idResolucion]);
        $row = $stmt->fetch();
        $resultado = array();
        if ($row) {
            $datos = array(
                    'idResolucion' => $row['Id'],
                    'numero' => $row['Numero'],
                    'fecha' => $row['Fecha'],
                    'detalle' => $row['Detalle'],
                    'idTipoResolucion' => $row['TipoResolucion'],
                    'estado' => $row['Estado'],
                    'detalleTipoResolucion' => $row['Detalle'],
                    'tipoEspecialista' => $row['TipoEspecialista']
            );
            $resultado['estado'] = TRUE;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = FALSE;
            $resultado['datos'] = NULL;
            $resultado['mensaje'] = "No se encontro la resolucion.";
            $resultado['clase'] = 'alert alert-warning';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando resolucion";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }

    return $resultado;
}

    public function obtenerResolucionesPorEstado($estado, $anio) {
    try {
        $db = Database::getConnection();
        $sql="SELECT resolucion.Id, resolucion.Numero, resolucion.Fecha, resolucion.Detalle, tiporesolucion.Detalle AS DetalleTipo
            FROM resolucion
            INNER JOIN tiporesolucion ON(tiporesolucion.Id = resolucion.TipoResolucion)
            WHERE resolucion.Estado = ? AND YEAR(resolucion.Fecha) = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$estado, $anio]);
        $datos_raw = $stmt->fetchAll();
        $resultado = array();
        if (count($datos_raw) > 0) {
            $datos = array();
            foreach ($datos_raw as $r) {
                $row = array(
                    'idResolucion' => $r['Id'],
                    'numero' => $r['Numero'],
                    'fecha' => $r['Fecha'],
                    'detalle' => $r['Detalle'],
                    'detalleTipo' => $r['DetalleTipo']
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
            $resultado['mensaje'] = "No existen resoluciones.";
            $resultado['clase'] = 'alert alert-warning';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando resoluciones";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }

    return $resultado;
}

    public function agregarResolucion($numero, $fecha, $detalle, $tipoResolucion){
    $resultado = array();
    try {
        $db = Database::getConnection();
        $sql="INSERT INTO resolucion
            (Numero, Fecha, Detalle, TipoResolucion, FechaCarga, IdUsuario)
            VALUE (?, ?, ?, ?, date(now()), ?)";
        $stmt = $db->prepare($sql);
        $stmt->execute([$numero, $fecha, $detalle, $tipoResolucion, $_SESSION['user_id']]);
        $idResolucion = $db->lastInsertId();
        $resultado['estado'] = TRUE;
        $resultado['idResolucion'] = $idResolucion;
        $resultado['mensaje'] = "OK";
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "ERROR AL GUARDAR RESOLUCION";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function modificarResolucion($idResolucion, $numero, $fecha, $detalle){
    $resultado = array();
    try {
        $db = Database::getConnection();
        $sql="UPDATE resolucion
                SET Numero = ?, Fecha = ?, Detalle = ?
                WHERE Id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$numero, $fecha, $detalle, $idResolucion]);
        $resultado['estado'] = TRUE;
        $resultado['mensaje'] = "OK";
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "ERROR AL MODIFICAR RESOLUCION. ".$e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function cambiarEstadoResolucion($idResolucion, $estadoOrigen, $estadoCambio){
    $resultado = array();
    try {
        $db = Database::getConnection();
        $sql="UPDATE resolucion
                SET Estado = ?
                WHERE Id = ? AND Estado = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$estadoCambio, $idResolucion, $estadoOrigen]);

        $sql="UPDATE resoluciondetalle
                SET Estado = 1
                WHERE IdResolucion = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idResolucion]);
        $resultado['estado'] = TRUE;
        $resultado['mensaje'] = "OK";
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "ERROR AL MODIFICAR RESOLUCION. ".$e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function obtenerMatriculasPorIdResolucion($idResolucion){
    try {
        $db = Database::getConnection();
        $sql = "(SELECT rd.Id, rd.TipoEspecialista, te.Nombre, e.Especialidad, c.Matricula, p.Apellido, p.Sexo, p.Nombres, rd.FechaAprobada, rd.IncisoArticulo8, rd.Estado, mee.NumeroExpediente, mee.AnioExpediente, '' as FechaEspecialista, '' as FechaEspecialista2, '' as FechaRecertificacion, '' as FechaVencimiento, te2.Nombre AS Origen, ce.IncisoArticulo8 AS EspecialistaInciso, te2.Codigo AS CodigoEspecialista, ce.HashQR, ce.Id, NULL AS por_recertificacion, tie.FechaEmision
                FROM resoluciondetalle rd
                INNER JOIN colegiado c ON c.Id = rd.IdColegiado
                INNER JOIN persona p ON p.Id = c.IdPersona
                INNER JOIN especialidad e ON e.Id = rd.Especialidad
                INNER JOIN tipoespecialista te ON te.Codigo = rd.TipoEspecialista
                LEFT JOIN tituloespecialista tie ON tie.IdResolucionDetalle = rd.Id AND tie.Borrado = 0
                LEFT JOIN mesaentradaespecialidad mee ON mee.IdMesaEntradaEspecialidad = rd.IdMesaEntradaEspecialidad
                LEFT JOIN colegiadoespecialistatipo cet ON cet.IdResolucionDetalle = rd.Id AND cet.Borrado = 0
                LEFT JOIN colegiadoespecialista ce ON ce.Id = cet.IdColegiadoEspecialista AND ce.Estado = 'A'
                LEFT JOIN tipoespecialista te2 ON te2.IdTipoEspecialista = ce.IdTipoEspecialista
                WHERE rd.IdResolucion = ? AND rd.TipoEspecialista = 'C'
                ORDER BY c.Matricula)

            UNION ALL

                (SELECT rd.Id, rd.TipoEspecialista, te.Nombre, e.Especialidad, c.Matricula, p.Apellido, p.Sexo, p.Nombres, rd.FechaAprobada, rd.IncisoArticulo8, rd.Estado, mee.NumeroExpediente, mee.AnioExpediente, '' as FechaEspecialista, '' as FechaEspecialista2, '' as FechaRecertificacion, '' as FechaVencimiento, te2.Nombre AS Origen, ce.IncisoArticulo8 AS EspecialistaInciso, te2.Codigo AS CodigoEspecialista, ce.HashQR, ce.Id, NULL AS por_recertificacion, tie.FechaEmision
                FROM resoluciondetalle rd
                INNER JOIN colegiado c ON c.Id = rd.IdColegiado
                INNER JOIN persona p ON p.Id = c.IdPersona
                INNER JOIN especialidad e ON e.Id = rd.Especialidad
                INNER JOIN tipoespecialista te ON te.Codigo = rd.TipoEspecialista
                LEFT JOIN tituloespecialista tie ON tie.IdResolucionDetalle = rd.Id AND tie.Borrado = 0
                LEFT JOIN mesaentradaespecialidad mee ON mee.IdMesaEntradaEspecialidad = rd.IdMesaEntradaEspecialidad
                LEFT JOIN colegiadoespecialistatipo cet ON cet.IdResolucionDetalle = rd.Id AND cet.Borrado = 0
                LEFT JOIN colegiadoespecialista ce ON ce.Id = cet.IdColegiadoEspecialista AND ce.Estado = 'A'
                LEFT JOIN tipoespecialista te2 ON te2.IdTipoEspecialista = cet.IdTipoEspecialista
                WHERE rd.IdResolucion = ? AND rd.TipoEspecialista = 'J'
                ORDER BY c.Matricula)

            UNION ALL

                (SELECT rd.Id, rd.TipoEspecialista, te.Nombre, e.Especialidad, c.Matricula, p.Apellido, p.Sexo, p.Nombres, rd.FechaAprobada, rd.IncisoArticulo8, rd.Estado, mee.NumeroExpediente, mee.AnioExpediente, ce.FechaEspecialista, (SELECT MAX(ce2.FechaEspecialista) FROM colegiadoespecialista ce2 WHERE ce2.IdColegiado = rd.IdColegiado AND ce2.Especialidad = rd.Especialidad) AS FechaEspecialista2, rd.FechaRecertificacion, ce.FechaVencimiento, te.Nombre AS Origen, ce.IncisoArticulo8 AS EspecialistaInciso, te.Codigo AS CodigoEspecialista, ce.HashQR, ce.Id, cer.IdColegiadoEspecialista AS por_recertificacion, tie.FechaEmision
                FROM resoluciondetalle rd
                INNER JOIN colegiado c ON c.Id = rd.IdColegiado
                INNER JOIN persona p ON p.Id = c.IdPersona
                INNER JOIN especialidad e ON e.Id = rd.Especialidad
                LEFT JOIN tituloespecialista tie ON tie.IdResolucionDetalle = rd.Id AND tie.Borrado = 0
                LEFT JOIN tipoespecialista te ON te.Codigo = rd.TipoEspecialista
                LEFT JOIN mesaentradaespecialidad mee ON mee.IdMesaEntradaEspecialidad = rd.IdMesaEntradaEspecialidad
                LEFT JOIN colegiadoespecialista ce ON ce.IdResolucionDetalle = rd.Id AND ce.Estado = 'A'
                LEFT JOIN colegiadoespecialistarecertificaciones cer ON cer.IdResolucionDetalle = rd.Id
                LEFT JOIN tipoespecialista te2 ON(te2.IdTipoEspecialista = ce.IdTipoEspecialista)
                WHERE rd.IdResolucion = ? AND rd.TipoEspecialista NOT IN('C', 'J', 'R') AND rd.Estado IN(0, 1)
                ORDER BY c.Matricula)

            UNION ALL

                (SELECT DISTINCT rd.Id, rd.TipoEspecialista, te.Nombre, e.Especialidad, c.Matricula, p.Apellido, p.Sexo, p.Nombres, rd.FechaAprobada, rd.IncisoArticulo8, rd.Estado, mee.NumeroExpediente, mee.AnioExpediente, ce.FechaEspecialista, (SELECT MAX(ce2.FechaEspecialista) FROM colegiadoespecialista ce2 WHERE ce2.IdColegiado = rd.IdColegiado AND ce2.Especialidad = rd.Especialidad) AS FechaEspecialista2, rd.FechaRecertificacion, ce.FechaVencimiento, te2.Nombre AS Origen, ce.IncisoArticulo8 AS EspecialistaInciso, te2.Codigo AS CodigoEspecialista, ce.HashQR, ce.Id, cer.IdColegiadoEspecialista AS por_recertificacion, tie.FechaEmision
                FROM resoluciondetalle rd
                INNER JOIN colegiado c ON c.Id = rd.IdColegiado
                INNER JOIN persona p ON p.Id = c.IdPersona
                INNER JOIN especialidad e ON e.Id = rd.Especialidad
                LEFT JOIN tituloespecialista tie ON tie.IdResolucionDetalle = rd.Id AND tie.Borrado = 0
                LEFT JOIN tipoespecialista te ON te.Codigo = rd.TipoEspecialista
                LEFT JOIN mesaentradaespecialidad mee ON mee.IdMesaEntradaEspecialidad = rd.IdMesaEntradaEspecialidad
                LEFT JOIN colegiadoespecialistarecertificaciones cer ON cer.IdResolucionDetalle = rd.Id
                LEFT JOIN colegiadoespecialista ce ON ce.Id = cer.IdColegiadoEspecialista
                LEFT JOIN tipoespecialista te2 ON te2.IdTipoEspecialista = ce.IdTipoEspecialista
                WHERE rd.IdResolucion = ? AND rd.TipoEspecialista = 'R' AND rd.Estado IN(0, 1)
                ORDER BY c.Matricula)";

        $stmt = $db->prepare($sql);
        $stmt->execute([$idResolucion, $idResolucion, $idResolucion, $idResolucion]);
        $datos_raw = $stmt->fetchAll();
        $resultado = array();
        $datos = array();
        foreach ($datos_raw as $r) {
            $estado = $r['Estado'];
            switch ($estado) {
                case '0': $estado = 'Enviar a Consejo'; break;
                case '1': $estado = 'Aprobado'; break;
                case '2': $estado = 'Desaprobado'; break;
                case '3': $estado = 'Ausente'; break;
                case '4': $estado = 'Dado de baja'; break;
                default: $estado = ''; break;
            }
            $row = array(
                'idResolucionDetalle' => $r['Id'],
                'codigoTipoEspecialista' => $r['TipoEspecialista'],
                'tipoEspecialista' => $r['Nombre'],
                'especialidad' => $r['Especialidad'],
                'matricula' => $r['Matricula'],
                'apellido' => $r['Apellido'],
                'nombre' => $r['Nombres'],
                'sexo' => $r['Sexo'],
                'fechaAprobacion' => $r['FechaAprobada'],
                'inciso' => $r['IncisoArticulo8'],
                'estado' => $estado,
                'nroExpediente' => $r['NumeroExpediente'],
                'anioExpediente' => $r['AnioExpediente'],
                'fechaEspecialista' => $r['FechaEspecialista'],
                'fechaEspecialista2' => $r['FechaEspecialista2'],
                'fechaRecertificacion' => $r['FechaRecertificacion'],
                'fechaVencimiento' => $r['FechaVencimiento'],
                'codigoEspecialista' => $r['CodigoEspecialista'],
                'origen' => $r['Origen'],
                'especialistaInciso' => $r['EspecialistaInciso'],
                'hash_qr' => $r['HashQR'],
                'idColegiadoEspecialista' => $r['Id'],
                'idColegiadoEspecialistaPorRecertificacion' => $r['por_recertificacion'],
                'fechaEmision' => $r['FechaEmision']
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
        $resultado['mensaje'] = "Error buscando Matriculas en la resolucion";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function obtenerDetalleResolucionPorId($idResolucion){
    try {
        $db = Database::getConnection();
        // Primero se actualizan las fechas de aprobacion
        $sql = "UPDATE resoluciondetalle rd
            INNER JOIN resolucion r ON r.Id = rd.IdResolucion
            SET rd.FechaAprobada = r.Fecha
            WHERE r.Fecha <> rd.FechaAprobada";
        $stmt = $db->prepare($sql);
        $stmt->execute();

        $sql = "SELECT rd.Id, rd.IdColegiado, rd.Especialidad, rd.TipoEspecialista, rd.Estado, rd.FechaAprobada, rd.FechaRecertificacion,
                    p.FechaNacimiento, te.IdTipoEspecialista, mee.Distrito, ce.Id AS IdColegiadoEspecialista, cet.Id AS IdColegiadoEspecialistaTipo,
                    rd.IncisoArticulo8
                FROM resoluciondetalle rd
                INNER JOIN colegiado c ON(c.Id = rd.IdColegiado)
                INNER JOIN persona p ON(p.Id = c.IdPersona)
                INNER JOIN tipoespecialista te ON(te.Codigo = rd.TipoEspecialista)
                LEFT JOIN mesaentradaespecialidad mee ON(mee.IdMesaEntradaEspecialidad = rd.IdMesaEntradaEspecialidad)
                LEFT JOIN colegiadoespecialista ce ON(ce.IdColegiado = c.Id AND ce.Especialidad = rd.Especialidad AND ce.Estado = 'A' AND ((ce.IdTipoEspecialista = te.IdTipoEspecialista AND ce.IncisoArticulo8 = rd.IncisoArticulo8) OR rd.TipoEspecialista IN('R', 'J', 'C')))
                LEFT JOIN colegiadoespecialistatipo cet ON(cet.IdColegiadoEspecialista = ce.Id AND cet.TipoEspecialista = rd.TipoEspecialista)
                WHERE rd.IdResolucion = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idResolucion]);
        $datos_raw = $stmt->fetchAll();
        $resultado = array();
        $datos = array();
        foreach ($datos_raw as $r) {
            $fechaRecertificacion = $r['FechaRecertificacion'];
            if (!isset($fechaRecertificacion)) {
                $fechaRecertificacion = $r['FechaAprobada'];
            }
            $row = array(
                'idResolucionDetalle' => $r['Id'],
                'idColegiado' => $r['IdColegiado'],
                'idEspecialidad' => $r['Especialidad'],
                'tipoEspecialista' => $r['TipoEspecialista'],
                'idEstadoResolucionDetalle' => $r['Estado'],
                'fechaAprobacion' => $r['FechaAprobada'],
                'fechaRecertificacion' => $r['FechaRecertificacion'],
                'fechaNacimiento' => $r['FechaNacimiento'],
                'idTipoEspecialista' => $r['IdTipoEspecialista'],
                'distrito' => $r['Distrito'],
                'idColegiadoEspecialista' => $r['IdColegiadoEspecialista'],
                'idColegiadoEspecialistaTipo' => $r['IdColegiadoEspecialistaTipo'],
                'incisoArticulo8' => $r['IncisoArticulo8']
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
        $resultado['mensaje'] = "Error buscando Matriculas en la resolucion";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function obtenerResolucionDetallePorId($idResolucionDetalle) {
    try {
        $db = Database::getConnection();
        $sql="SELECT rd.IdResolucion, rd.TipoEspecialista, rd.Especialidad, rd.Estado, rd.FechaAprobada, rd.FechaRecertificacion, rd.IncisoArticulo8, rd.IdColegiado, c.Matricula, p.Apellido, p.Nombres, e.Especialidad AS EspecialidadDetalle, te.Nombre AS NombreTipoEspecialista, r.TipoResolucion, tr.Detalle AS DetalleTipoResolucion, r.Numero, p.Sexo
            FROM resoluciondetalle rd
            INNER JOIN resolucion r ON r.Id = rd.IdResolucion
            INNER JOIN colegiado c ON c.Id = rd.IdColegiado
            INNER JOIN persona p ON p.Id = c.IdPersona
            INNER JOIN especialidad e ON e.Id = rd.Especialidad
            INNER JOIN tipoespecialista te ON te.Codigo = rd.TipoEspecialista
            INNER JOIN tiporesolucion tr ON tr.Id = r.TipoResolucion
            WHERE rd.Id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idResolucionDetalle]);
        $row = $stmt->fetch();
        $resultado = array();
        if ($row) {
            $datos = array(
                    'idResolucion' => $row['IdResolucion'],
                    'tipo' => $row['TipoEspecialista'],
                    'especialidad' => $row['Especialidad'],
                    'especialidadDetalle' => $row['EspecialidadDetalle'],
                    'estado' => $row['Estado'],
                    'fechaAprobada' => $row['FechaAprobada'],
                    'fechaRecertificacion' => $row['FechaRecertificacion'],
                    'idColegiado' => $row['IdColegiado'],
                    'matricula' => $row['Matricula'],
                    'apellido' => $row['Apellido'],
                    'nombre' => $row['Nombres'],
                    'inciso' => $row['IncisoArticulo8'],
                    'tipoEspecialista' => $row['NombreTipoEspecialista'],
                    'idTipoResolucion' => $row['TipoResolucion'],
                    'tipoResolucion' => $row['DetalleTipoResolucion'],
                    'numeroResolucion' => $row['Numero'],
                    'sexo' => $row['Sexo']
            );
            $resultado['estado'] = TRUE;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = FALSE;
            $resultado['datos'] = NULL;
            $resultado['mensaje'] = "No se encontro la matricula en la resolucion resolucion.";
            $resultado['clase'] = 'alert alert-warning';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando matricula de la resolucion";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }

    return $resultado;
}

    public function obtenerTiposEspecialista(){
    try {
        $db = Database::getConnection();
        $sql = "SELECT te.IdTipoEspecialista, te.Nombre, te.Codigo, te.IdTipoPago, te.IdTipoResolucion
                FROM tipoespecialista te ORDER BY te.Nombre";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $datos_raw = $stmt->fetchAll();
        $resultado = array();
        $datos = array();
        foreach ($datos_raw as $r) {
            $row = array(
                'id' => $r['IdTipoEspecialista'],
                'nombre' => $r['Nombre'],
                'codigo' => $r['Codigo'],
                'idTipoPago' => $r['IdTipoPago'],
                'idTipoResolucion' => $r['IdTipoResolucion']
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
        $resultado['mensaje'] = "Error buscando Tipos de especialista";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function obtenerTipoEspecialistaPorCodigo($codigo){
    try {
        $db = Database::getConnection();
        $sql = "SELECT te.IdTipoEspecialista, te.Nombre, te.Codigo, te.IdTipoPago, te.IdTipoResolucion
                FROM tipoespecialista te
                WHERE te.Codigo = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$codigo]);
        $row = $stmt->fetch();
        $resultado = array();
        if ($row) {
            $datos = array(
                    'id' => $row['IdTipoEspecialista'],
                    'nombre' => $row['Nombre'],
                    'codigo' => $row['Codigo'],
                    'idTipoPago' => $row['IdTipoPago'],
                    'idTipoResolucion' => $row['IdTipoResolucion']
                    );
            $resultado['estado'] = true;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = true;
            $resultado['mensaje'] = "No hay Tipos de especialista";
            $resultado['clase'] = 'alert alert-info';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando Tipos de especialista";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function agregarResolucionDetalle($idResolucion, $idMesaEntradaEspecialidad, $idEspecialidad, $tipoEspecialista, $fechaAprobada, $fechaRecertificacion, $idEspecialistaBaja, $idColegiado, $inciso, $idTipoEspecialista) {
    $resultado = array();
    try {
        $db = Database::getConnection();
        $sql="INSERT INTO resoluciondetalle (IdResolucion, Especialidad, TipoEspecialista, Estado, FechaAprobada, FechaRecertificacion, IdEspecialistaBaja, IdColegiado, IncisoArticulo8, IdMesaEntradaEspecialidad, IdTipoEspecialista)
                VALUES (?, ?, ?, '0', ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idResolucion, $idEspecialidad, $tipoEspecialista, $fechaAprobada, $fechaRecertificacion, $idEspecialistaBaja, $idColegiado, $inciso, $idMesaEntradaEspecialidad, $idTipoEspecialista]);
        $resultado['estado'] = TRUE;
        $resultado['mensaje'] = "OK";
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "ERROR AL AGREGAR MATRICULA A LA RESOLUCION. ".$e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function realizarBajaResolucionDetalle($idResolucionDetalle) {
    $resultado = array();
    try {
        $db = Database::getConnection();
        $sql="DELETE FROM resoluciondetalle WHERE Id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idResolucionDetalle]);
        $resultado['estado'] = TRUE;
        $resultado['mensaje'] = "OK";
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "ERROR AL MODIFICAR RESOLUCION. ".$e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function cambiarEstadoResolucionDetalle($idResolucionDetalle, $estado) {
    $resultado = array();
    try {
        $db = Database::getConnection();
        $sql="UPDATE resoluciondetalle
                SET Estado = ?
                WHERE Id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$estado, $idResolucionDetalle]);
        $resultado['estado'] = TRUE;
        $resultado['mensaje'] = "OK";
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "ERROR AL MODIFICAR RESOLUCION. ".$e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function obtenerAnexosResolucion($idResolucion) {
    try {
        $db = Database::getConnection();
        $sql="SELECT * FROM resolucionanexo ra WHERE ra.IdResolucion = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idResolucion]);
        $datos_raw = $stmt->fetchAll();
        $resultado = array();
        $datos = array();
        foreach ($datos_raw as $r) {
            $row = array(
                'idResolucionAnexo' => $r['Id'],
                'idResolucion' => $r['IdResolucion'],
                'observacion' => $r['Observacion'],
                'fechaCarga' => $r['FechaCarga'],
                'idUsuario' => $r['IdUsuario'],
                'borrado' => $r['Borrado']
            );
            array_push($datos, $row);
        }
        $resultado['estado'] = TRUE;
        $resultado['mensaje'] = "OK";
        $resultado['datos'] = $datos;
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando Anexo de resolucion";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }

    return $resultado;
}

    public function obtenerResolucionAnexoPorId($idAnexo) {
    try {
        $db = Database::getConnection();
        $sql="SELECT * FROM resolucionanexo ra WHERE ra.Id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idAnexo]);
        $row = $stmt->fetch();
        $resultado = array();
        if ($row) {
            $datos = array(
                    'idResolucionAnexo' => $row['Id'],
                    'idResolucion' => $row['IdResolucion'],
                    'observacion' => $row['Observacion'],
                    'fechaCarga' => $row['FechaCarga'],
                    'idUsuario' => $row['IdUsuario'],
                    'borrado' => $row['Borrado']
            );
            $resultado['estado'] = TRUE;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = FALSE;
            $resultado['datos'] = NULL;
            $resultado['mensaje'] = "No se encontro Anexo de la resolucion.";
            $resultado['clase'] = 'alert alert-warning';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando Anexo de resolucion";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }

    return $resultado;
}

    public function agregarResolucionAnexo($idResolucion, $observacion, $borrado) {
    $resultado = array();
    try {
        $db = Database::getConnection();
        $sql="INSERT INTO resolucionanexo
            (IdResolucion, Observacion, FechaCarga, IdUsuario, Borrado)
            VALUE (?, ?, now(), ?, ?)";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idResolucion, $observacion, $_SESSION['user_id'], $borrado]);
        $idAnexo = $db->lastInsertId();
        $resultado['estado'] = TRUE;
        $resultado['idAnexo'] = $idAnexo;
        $resultado['mensaje'] = "OK";
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "ERROR AL GUARDAR RESOLUCION";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function modificarResolucionAnexo($idAnexo, $observacion, $borrado) {
    $resultado = array();
    try {
        $db = Database::getConnection();
        $sql="UPDATE resolucionanexo
            SET Observacion = ?,
                FechaCarga = NOW(),
                IdUsuario = ?,
                Borrado = ?
            WHERE Id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$observacion, $_SESSION['user_id'], $borrado, $idAnexo]);
        $resultado['estado'] = TRUE;
        $resultado['mensaje'] = "OK";
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "ERROR AL GUARDAR RESOLUCION";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

//Constantes
}
