<?php
class habilitacionConsultorioLogic {

    public function obtenerHabilitacionesSolicitadas(){
    $db = Database::getConnection();
    $sql = "SELECT DISTINCT(me.IdMesaEntrada), me.IdColegiado, me.FechaIngreso,
        me.Observaciones, c.Matricula, p.Apellido, p.Nombres, con.Calle,
        con.Lateral, con.Numero, con.Piso, con.Departamento, con.Telefono, con.Observaciones as Horarios,
        mec.IdConsultorio, l.Nombre as NombreLocalidad, e.Especialidad as NombreEspecialidad, cc.CorreoElectronico as Email
        FROM mesaentrada as me
        INNER JOIN colegiado as c ON (c.Id = me.IdColegiado)
        INNER JOIN persona as p ON(p.Id = c.IdPersona)
        INNER JOIN colegiadocontacto as cc ON (cc.IdColegiado = c.Id AND cc.IdEstado = 1)
        INNER JOIN mesaentradaconsultorio as mec ON (mec.IdMesaEntrada = me.IdMesaEntrada)
        INNER JOIN consultorio as con ON (con.IdConsultorio = mec.IdConsultorio)
        INNER JOIN localidad as l ON (l.Id = con.IdLocalidad)
        INNER JOIN especialidad as e ON (e.Id = mec.IdEspecialidad)
        WHERE me.IdTipoMesaEntrada = 4 AND con.Estado = 'A' AND me.Estado = 'A' AND mec.Estado = 'A'
        AND me.IdMesaEntrada NOT IN(SELECT ih.IdMesaEntrada
                                    FROM inspectorhabilitacion as ih
                                    WHERE ih.Estado = 'A')
        GROUP BY me.IdMesaEntrada";
    $resultado = array();
    try {
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll();
        $datos = array();
        foreach ($rows as $r) {
            $calle  = $r['Calle'];
            $numero = $r['Numero'];
            $lateral = $r['Lateral'];
            $piso   = $r['Piso'];
            $depto  = $r['Departamento'];
            $domicilioCompleto = $calle;
            if (isset($numero) && strtoupper($numero) != "NR" && $numero != "") {
                $domicilioCompleto .= " Nº ".$numero;
            }
            if (isset($lateral) && $lateral != "") {
                $domicilioCompleto .= " e/ ".$lateral;
            }
            if (isset($piso) && strtoupper($piso) != "NR" && $piso != "") {
                $domicilioCompleto .= " Piso ".$piso;
            }
            if (isset($depto) && strtoupper($depto) != "NR" && $depto != "") {
                $domicilioCompleto .= " Dto. ".$depto;
            }
            $row = array (
                'idMesaEntrada' => $r['IdMesaEntrada'],
                'idColegiado' => $r['IdColegiado'],
                'fechaIngreso' => $r['FechaIngreso'],
                'observaciones' => $r['Observaciones'],
                'matricula' => $r['Matricula'],
                'apellidoNombre' => trim($r['Apellido'])." ".trim($r['Nombres']),
                'domicilio' => $domicilioCompleto,
                'telefono' => $r['Telefono'],
                'horarios' => $r['Horarios'],
                'idConsultorio' => $r['IdConsultorio'],
                'localidad' => $r['NombreLocalidad'],
                'especialidad' => $r['NombreEspecialidad'],
                'mail' => $r['Email']
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
        $resultado['mensaje'] = "Error buscando habilitaciones solicitadas";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function obtenerHabilitacionSolicitadaPorId($idMesaEntrada){
    $db = Database::getConnection();
    $sql = "SELECT me.IdMesaEntrada, mec.IdMesaEntradaConsultorio, me.IdColegiado, me.FechaIngreso,
        me.Observaciones, c.Matricula, p.Apellido, p.Nombres, con.Calle,
        con.Lateral, con.Numero, con.Piso, con.Departamento, con.Telefono, con.Observaciones as Horarios,
        mec.IdConsultorio, l.Nombre as NombreLocalidad, e.Especialidad as NombreEspecialidad, cc.CorreoElectronico as Email
        FROM mesaentrada as me
        INNER JOIN colegiado as c ON (c.Id = me.IdColegiado)
        INNER JOIN persona as p ON(p.Id = c.IdPersona)
        INNER JOIN colegiadocontacto as cc ON (cc.IdColegiado = c.Id AND cc.IdEstado = 1)
        INNER JOIN mesaentradaconsultorio as mec ON (mec.IdMesaEntrada = me.IdMesaEntrada)
        INNER JOIN consultorio as con ON (con.IdConsultorio = mec.IdConsultorio)
        INNER JOIN localidad as l ON (l.Id = con.IdLocalidad)
        INNER JOIN especialidad as e ON (e.Id = mec.IdEspecialidad)
        WHERE me.IdMesaEntrada = ? AND mec.Estado = 'A'";
    $resultado = array();
    try {
        $stmt = $db->prepare($sql);
        $stmt->execute([$idMesaEntrada]);
        $r = $stmt->fetch();
        if ($r) {
            $calle  = $r['Calle'];
            $numero = $r['Numero'];
            $lateral = $r['Lateral'];
            $piso   = $r['Piso'];
            $depto  = $r['Departamento'];
            $domicilioCompleto = $calle;
            if (isset($numero) && strtoupper($numero) != "NR" && $numero != "") {
                $domicilioCompleto .= " Nº ".$numero;
            }
            if (isset($lateral) && $lateral != "") {
                $domicilioCompleto .= " e/ ".$lateral;
            }
            if (isset($piso) && strtoupper($piso) != "NR" && $piso != "") {
                $domicilioCompleto .= " Piso ".$piso;
            }
            if (isset($depto) && strtoupper($depto) != "NR" && $depto != "") {
                $domicilioCompleto .= " Dto. ".$depto;
            }
            $datos = array (
                'idMesaEntrada' => $r['IdMesaEntrada'],
                'idMesaEntradaConsultorio' => $r['IdMesaEntradaConsultorio'],
                'idColegiado' => $r['IdColegiado'],
                'fechaIngreso' => $r['FechaIngreso'],
                'observaciones' => $r['Observaciones'],
                'matricula' => $r['Matricula'],
                'apellidoNombre' => trim($r['Apellido'])." ".trim($r['Nombres']),
                'domicilio' => $domicilioCompleto,
                'telefono' => $r['Telefono'],
                'horarios' => $r['Horarios'],
                'idConsultorio' => $r['IdConsultorio'],
                'localidad' => $r['NombreLocalidad'],
                'especialidad' => $r['NombreEspecialidad'],
                'mail' => $r['Email']
             );
            $resultado['estado'] = true;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = true;
            $resultado['mensaje'] = "No hay habilitaciones solicitadas";
            $resultado['clase'] = 'alert alert-info';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando habilitaciones solicitadas";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function obtenerHabilitacionesAsignadasPorInspector($idInspector) {
    $db = Database::getConnection();
    $conInspector = ' ';
    if (isset($idInspector) && $idInspector != "") {
        $conInspector = ' AND ih.IdInspector =  '.$idInspector;
    }
    $sql = "SELECT DISTINCT(ih.IdInspectorHabilitacion), me.IdMesaEntrada, me.IdColegiado,
            me.FechaIngreso, me.Observaciones, c.Matricula, p.Apellido, p.Nombres,
            con.Calle, con.Lateral, con.Numero, con.Piso, con.Departamento, con.Telefono,
            con.Observaciones as Horarios, l.Nombre as NombreLocalidad,
            e.Especialidad as NombreEspecialidad, cc.CorreoElectronico as Email
            FROM inspectorhabilitacion as ih
            INNER JOIN mesaentrada as me ON (me.IdMesaEntrada = ih.IdMesaEntrada)
            INNER JOIN colegiado as c ON (c.Id = me.IdColegiado)
            INNER JOIN persona as p ON(p.Id = c.IdPersona)
            INNER JOIN colegiadocontacto as cc ON (cc.IdColegiado = c.Id and cc.IdEstado = 1)
            INNER JOIN mesaentradaconsultorio as mec ON (mec.IdMesaEntrada = me.IdMesaEntrada)
            INNER JOIN consultorio as con ON (con.IdConsultorio = mec.IdConsultorio)
            INNER JOIN localidad as l ON (l.Id = con.IdLocalidad)
            INNER JOIN especialidad as e ON (e.Id = mec.IdEspecialidad)
            WHERE ih.FechaInspeccion IS NULL
            AND ih.Estado = 'A' ".$conInspector."
            GROUP BY me.IdMesaEntrada";
    $resultado = array();
    try {
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll();
        $datos = array();
        foreach ($rows as $r) {
            $calle  = $r['Calle'];
            $numero = $r['Numero'];
            $lateral = $r['Lateral'];
            $piso   = $r['Piso'];
            $depto  = $r['Departamento'];
            $domicilioCompleto = $calle;
            if (isset($numero) && strtoupper($numero) != "NR" && $numero != "") {
                $domicilioCompleto .= " Nº ".$numero;
            }
            if (isset($lateral) && $lateral != "") {
                $domicilioCompleto .= " e/ ".$lateral;
            }
            if (isset($piso) && strtoupper($piso) != "NR" && $piso != "") {
                $domicilioCompleto .= " Piso ".$piso;
            }
            if (isset($depto) && strtoupper($depto) != "NR" && $depto != "") {
                $domicilioCompleto .= " Dto. ".$depto;
            }
            $row = array (
                'idInspectorHabilitacion' => $r['IdInspectorHabilitacion'],
                'idMesaEntrada' => $r['IdMesaEntrada'],
                'idColegiado' => $r['IdColegiado'],
                'fechaIngreso' => $r['FechaIngreso'],
                'observaciones' => $r['Observaciones'],
                'matricula' => $r['Matricula'],
                'apellidoNombre' => trim($r['Apellido'])." ".trim($r['Nombres']),
                'domicilio' => $domicilioCompleto,
                'telefono' => $r['Telefono'],
                'horarios' => $r['Horarios'],
                'localidad' => $r['NombreLocalidad'],
                'especialidad' => $r['NombreEspecialidad'],
                'mail' => $r['Email']
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
        $resultado['mensaje'] = "Error buscando habilitaciones solicitadas";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function obtenerHabilitacionesConfirmadasPorInspector($idInspector) {
    $db = Database::getConnection();
    $conInspector = ' ';
    if (isset($idInspector) && $idInspector != "") {
        $conInspector = ' AND ih.IdInspector =  '.$idInspector;
    }
    $sql = "SELECT DISTINCT(ih.IdInspectorHabilitacion), me.IdMesaEntrada, me.IdColegiado,
            me.FechaIngreso, me.Observaciones, c.Matricula, p.Apellido, p.Nombres,
            con.Calle, con.Lateral, con.Numero, con.Piso, con.Departamento, con.Telefono,
            con.Observaciones as Horarios, l.Nombre as NombreLocalidad,
            e.Especialidad as NombreEspecialidad, cc.CorreoElectronico as Email, ih.FechaInspeccion,
            ih.FechaHabilitacion
            FROM inspectorhabilitacion as ih
            INNER JOIN mesaentrada as me ON (me.IdMesaEntrada = ih.IdMesaEntrada)
            INNER JOIN colegiado as c ON (c.Id = me.IdColegiado)
            INNER JOIN persona as p ON(p.Id = c.IdPersona)
            INNER JOIN colegiadocontacto as cc ON (cc.IdColegiado = c.Id and cc.IdEstado = 1)
            INNER JOIN mesaentradaconsultorio as mec ON (mec.IdMesaEntrada = me.IdMesaEntrada)
            INNER JOIN consultorio as con ON (con.IdConsultorio = mec.IdConsultorio)
            INNER JOIN localidad as l ON (l.Id = con.IdLocalidad)
            INNER JOIN especialidad as e ON (e.Id = mec.IdEspecialidad)
            WHERE ih.FechaInspeccion IS NOT NULL
            AND ih.Estado = 'A' ".$conInspector." AND ih.EstadoInspeccion <> 'B'
            GROUP BY me.IdMesaEntrada";
    $resultado = array();
    try {
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll();
        $datos = array();
        foreach ($rows as $r) {
            $calle  = $r['Calle'];
            $numero = $r['Numero'];
            $lateral = $r['Lateral'];
            $piso   = $r['Piso'];
            $depto  = $r['Departamento'];
            $domicilioCompleto = $calle;
            if (isset($numero) && strtoupper($numero) != "NR" && $numero != "") {
                $domicilioCompleto .= " Nº ".$numero;
            }
            if (isset($lateral) && $lateral != "") {
                $domicilioCompleto .= " e/ ".$lateral;
            }
            if (isset($piso) && strtoupper($piso) != "NR" && $piso != "") {
                $domicilioCompleto .= " Piso ".$piso;
            }
            if (isset($depto) && strtoupper($depto) != "NR" && $depto != "") {
                $domicilioCompleto .= " Dto. ".$depto;
            }
            $row = array (
                'idInspectorHabilitacion' => $r['IdInspectorHabilitacion'],
                'idMesaEntrada' => $r['IdMesaEntrada'],
                'idColegiado' => $r['IdColegiado'],
                'fechaIngreso' => $r['FechaIngreso'],
                'observaciones' => $r['Observaciones'],
                'matricula' => $r['Matricula'],
                'apellidoNombre' => trim($r['Apellido'])." ".trim($r['Nombres']),
                'domicilio' => $domicilioCompleto,
                'telefono' => $r['Telefono'],
                'horarios' => $r['Horarios'],
                'localidad' => $r['NombreLocalidad'],
                'especialidad' => $r['NombreEspecialidad'],
                'mail' => $r['Email'],
                'fechaInspeccion' => $r['FechaInspeccion'],
                'fechaHabilitacion' => $r['FechaHabilitacion']
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
        $resultado['mensaje'] = "Error buscando habilitaciones solicitadas";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function borrarSolicitudHabilitacion($idMesaEntrada) {
    $db = Database::getConnection();
    $resultado = array();
    $sql="UPDATE mesaentradaconsultorio
            SET Estado = 'B',
            FechaBaja = NOW(),
            IdUsuarioBaja = ?
            WHERE IdMesaEntrada = ?";
    try {
        $stmt = $db->prepare($sql);
        $stmt->execute([$_SESSION['user_id'], $idMesaEntrada]);
        $resultado['estado'] = TRUE;
        $resultado['mensaje'] = 'LA SOLICITUDA DE HABILITACION HA SIDO BORRADO';
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "ERROR AL BORRAR SOLICITUD DE HABILITACION";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function obtenerInspectores($estadoInspectores){
    $db = Database::getConnection();
    $sql = "SELECT i.IdInspector, c.Matricula, p.Apellido, p.Nombres
        FROM inspector as i
        INNER JOIN colegiado as c ON (c.Id = i.IdColegiado)
        INNER JOIN persona as p ON(p.Id = c.IdPersona)
        WHERE i.Estado = ?
        ORDER BY p.Apellido, p.Nombres";
    $resultado = array();
    try {
        $stmt = $db->prepare($sql);
        $stmt->execute([$estadoInspectores]);
        $rows = $stmt->fetchAll();
        $datos = array();
        foreach ($rows as $r) {
            $row = array (
                'idInspector' => $r['IdInspector'],
                'matricula' => $r['Matricula'],
                'apellidoNombre' => trim($r['Apellido'])." ".trim($r['Nombres'])
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
        $resultado['mensaje'] = "Error buscando inspectores";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function obtenerInspectorPorId($idInspector){
    $db = Database::getConnection();
    $sql = "SELECT i.IdColegiado, c.Matricula, p.Apellido, p.Nombres
        FROM inspector as i
        INNER JOIN colegiado as c ON (c.Id = i.IdColegiado)
        INNER JOIN persona as p ON(p.Id = c.IdPersona)
        WHERE i.IdInspector = ?";
    $resultado = array();
    try {
        $stmt = $db->prepare($sql);
        $stmt->execute([$idInspector]);
        $r = $stmt->fetch();
        if ($r) {
            $datos = array(
                    'idInspector' => $idInspector,
                    'idColegiado' => $r['IdColegiado'],
                    'matricula' => $r['Matricula'],
                    'apellidoNombre' => trim($r['Apellido'])." ".trim($r['Nombres'])
                 );
            $resultado['estado'] = true;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = true;
            $resultado['mensaje'] = "No hay inspector";
            $resultado['clase'] = 'alert alert-info';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando inspector";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function existeInspector($idColegiado) {
    $db = Database::getConnection();
    $sql = "SELECT IdInspector as Cantidad FROM inspector WHERE IdColegiado = ?";
    $resultado = NULL;
    try {
        $stmt = $db->prepare($sql);
        $stmt->execute([$idColegiado]);
        $r = $stmt->fetch();
        if ($r && $r['Cantidad'] > 0) {
            $resultado = $r['Cantidad'];
        }
    } catch (PDOException $e) {
        // return NULL
    }
    return $resultado;
}

    public function agregarInspector($idColegiado) {
    $db = Database::getConnection();
    $resultado = array();
    $sql="INSERT INTO inspector
            (IdColegiado, FechaCarga, IdUsuarioCarga)
            VALUES (?, DATE(NOW()), ?)";
    try {
        $stmt = $db->prepare($sql);
        $stmt->execute([$idColegiado, $_SESSION['user_id']]);
        $idInspector = $db->lastInsertId();
        $resultado['estado'] = TRUE;
        $resultado['mensaje'] = 'EL INSPECTOR HA SIDO AGREGADO';
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "ERROR AL AGREGAR INSPECTOR";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function borrarInspector($idInspector, $estado) {
    $db = Database::getConnection();
    $resultado = array();
    if ($estado == 'A') {
        $estado = 'B';
    } else {
        $estado = 'A';
    }
    $sql="UPDATE inspector
            SET Estado = ?, FechaBaja = DATE(NOW()), IdUsuarioBaja = ? WHERE IdInspector = ?";
    try {
        $stmt = $db->prepare($sql);
        $stmt->execute([$estado, $_SESSION['user_id'], $idInspector]);
        $resultado['estado'] = TRUE;
        $resultado['mensaje'] = 'EL INSPECTOR HA SIDO ACTUALIZADO';
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "ERROR AL ACTUALIZADO INSPECTOR";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function asignarInspectorAHabilitacion($idInspector, $idsMesaEntrada) {
    $db = Database::getConnection();
    $resultado = array();
    $datos = array();
    $sql="INSERT INTO inspectorhabilitacion
            (IdInspector, IdMesaEntrada, FechaAsignacion, Estado)
            VALUES (?, ?, DATE(NOW()), 'A')";
    $stmt = $db->prepare($sql);
    foreach ($idsMesaEntrada as $idMesaEntrada) {
        try {
            $stmt->execute([$idInspector, $idMesaEntrada]);
            $idInspeccion = $db->lastInsertId();
            $row = array (
                    'idInspeccion' => $idInspeccion
                 );
            array_push($datos, $row);
            $resultado['estado'] = TRUE;
            $resultado['mensaje'] = 'EL INSPECTOR HA SIDO ASIGNADO';
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } catch (PDOException $e) {
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "ERROR AL ASIGNAR INSPECTOR";
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-remove';
        }
    }
    if ($resultado['estado']) {
        $resultado['datos'] = $datos;
    }
    return $resultado;
}

    public function desasignarInspectorAHabilitacion($idInspectorHabilitacion) {
    $db = Database::getConnection();
    $resultado = array();
    $sql="UPDATE inspectorhabilitacion
        SET Estado = 'B'
        WHERE IdInspectorHabilitacion = ?";
    try {
        $stmt = $db->prepare($sql);
        $stmt->execute([$idInspectorHabilitacion]);
        $resultado['estado'] = TRUE;
        $resultado['mensaje'] = 'EL INSPECTOR HA SIDO DESASIGNADO';
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "ERROR AL DESASIGNAR INSPECTOR";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function obtenerInspeccionPorId($idInspectorHabilitacion) {
    $db = Database::getConnection();
    $sql = "SELECT c.Matricula as MatriculaInspector, p.Apellido as ApellidoInspector, p.Nombres as NombreInspector,
            loc.Nombre as NombreLocalidad, con.Calle, con.Lateral, con.Numero, con.Piso, con.Departamento,
            col.Matricula as MatriculaColegiadoConsultorio, per.Apellido as ApellidoColegiadoConsultorio,
            per.Nombres as NombreColegiadoConsultorio, ih.FechaInspeccion, ih.FechaHabilitacion,
            ih.EstadoInspeccion, ih.MotivoNoHabilitacion
            FROM inspectorhabilitacion as ih
            INNER JOIN inspector as i ON (i.IdInspector = ih.IdInspector)
            INNER JOIN colegiado as c ON (c.Id = i.IdColegiado)
            INNER JOIN persona as p ON (p.Id = c.IdPersona)
            INNER JOIN mesaentrada as me ON (me.IdMesaEntrada = ih.IdMesaEntrada)
            INNER JOIN mesaentradaconsultorio as mec ON (mec.IdMesaEntrada = me.IdMesaEntrada)
            INNER JOIN consultorio as con ON (con.IdConsultorio = mec.IdConsultorio)
            INNER JOIN colegiado as col ON (col.Id = me.IdColegiado)
            INNER JOIN persona as per ON (per.Id = col.IdPersona)
            INNER JOIN localidad as loc ON (loc.Id = con.IdLocalidad)
            WHERE ih.IdInspectorHabilitacion = ?
            AND ih.Estado = 'A'";
    $resultado = array();
    try {
        $stmt = $db->prepare($sql);
        $stmt->execute([$idInspectorHabilitacion]);
        $r = $stmt->fetch();
        if ($r) {
            $calle  = $r['Calle'];
            $numero = $r['Numero'];
            $lateral = $r['Lateral'];
            $piso   = $r['Piso'];
            $depto  = $r['Departamento'];
            $localidad = $r['NombreLocalidad'];
            $domicilioCompleto = $calle;
            if (isset($numero) && strtoupper($numero) != "NR" && $numero != "") {
                $domicilioCompleto .= " Nº ".$numero;
            }
            if (isset($lateral) && $lateral != "") {
                $domicilioCompleto .= " e/ ".$lateral;
            }
            if (isset($piso) && strtoupper($piso) != "NR" && $piso != "") {
                $domicilioCompleto .= " Piso ".$piso;
            }
            if (isset($depto) && strtoupper($depto) != "NR" && $depto != "") {
                $domicilioCompleto .= " Dto. ".$depto;
            }
            if (isset($localidad) && $localidad != "") {
                $domicilioCompleto .= " ".$localidad;
            }
            $datos = array(
                    'matriculaInspector' => $r['MatriculaInspector'],
                    'apellidoNombreInspector' => trim($r['ApellidoInspector']).' '.$r['NombreInspector'],
                    'matriculaColegiado' => $r['MatriculaColegiadoConsultorio'],
                    'apellidoNombreColegiado' => trim($r['ApellidoColegiadoConsultorio'])." ".trim($r['NombreColegiadoConsultorio']),
                    'domicilio' => $domicilioCompleto,
                    'fechaInspeccion' => $r['FechaInspeccion'],
                    'fechaHabilitacion' => $r['FechaHabilitacion'],
                    'estadoInspeccion' => $r['EstadoInspeccion'],
                    'motivoNoHabilita' => $r['MotivoNoHabilitacion']
                 );
            $resultado['estado'] = true;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = true;
            $resultado['mensaje'] = "No hay inspeccion asociada";
            $resultado['clase'] = 'alert alert-info';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando inspeccion";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function confirmarInspeccion($IdInspectorHabilitacion, $fechaHabilitacion, $fechaInspeccion, $observaciones, $estadoInspeccion) {
    $db = Database::getConnection();
    $resultado = array();
    $sql="UPDATE inspectorhabilitacion
            SET FechaInspeccion = ?, FechaHabilitacion = ?, EstadoInspeccion = ?, MotivoNoHabilitacion = ?
            WHERE IdInspectorHabilitacion = ?";
    try {
        $stmt = $db->prepare($sql);
        $stmt->execute([$fechaInspeccion, $fechaHabilitacion, $estadoInspeccion, $observaciones, $IdInspectorHabilitacion]);
        $resultado['estado'] = TRUE;
        $resultado['mensaje'] = 'SE GUARDO LA INSPECCION CON EXITO';
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "ERROR AL GUARDAR LA INSPECCION";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}
}
