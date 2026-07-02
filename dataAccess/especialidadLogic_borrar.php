<?php
function obtenerEspecialidades() {
    $db = Database::getConnection();
    $sql = "SELECT e.Id, e.Especialidad, e.Codigo, e.CodigoRes62707, e.IdTipoEspecialidad, e.IdPadre, if (e.IdPadre IS NOT NULL, (SELECT e1.Especialidad FROM especialidad e1 WHERE e1.Id = e.IdPadre), '') AS EspecialidadPadre
        FROM especialidad e
        WHERE e.Estado = 'A'
        ORDER BY e.Especialidad";
    $resultado = array();
    try {
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll();
        if (count($rows) > 0) {
            $datos = array();
            foreach ($rows as $r) {
                $row = array (
                    'idEspecialidad' => $r['Id'],
                    'nombreEspecialidad' => $r['Especialidad'],
                    'codigoResolucion' => $r['CodigoRes62707'],
                    'idTipoEspecialidad' => $r['IdTipoEspecialidad'],
                    'idPadre' => $r['IdPadre'],
                    'especialidadPadre' => $r['EspecialidadPadre']
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
            $resultado['mensaje'] = "No se encontraron especialidades.";
            $resultado['clase'] = 'alert alert-info';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando especialidades";
        $resultado['clase'] = 'alert alert-error';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }

    return $resultado;
}

function obtenerEspecialidadesParaExpedientes($idColegiado) {
    $db = Database::getConnection();
    //lo sacamos por el momento para cargar los que piden por UNLP y ya tinen la especialidad por el colegio
    /*
    $sql = "SELECT e.* FROM especialidad e
        LEFT JOIN colegiadoespecialista ce ON (ce.Especialidad = e.Id AND ce.IdColegiado = ?)
        WHERE e.Estado = 'A'
        AND (ce.Id IS NULL OR ce.IdTipoEspecialista = 8)
        ORDER BY e.Especialidad";
    */
    $sql = "SELECT e.* FROM especialidad e
        WHERE e.Estado = 'A'
        ORDER BY e.Especialidad";
    $resultado = array();
    try {
        $stmt = $db->prepare($sql);
        //$stmt->execute([$idColegiado]);
        $stmt->execute();
        $rows = $stmt->fetchAll();
        if (count($rows) > 0) {
            $datos = array();
            foreach ($rows as $r) {
                $row = array (
                    'idEspecialidad' => $r['Id'],
                    'nombreEspecialidad' => $r['Especialidad'],
                    'codigoResolucion' => $r['CodigoRes62707'],
                    'idTipoEspecialidad' => $r['IdTipoEspecialidad'],
                    'estado' => $r['Estado'],
                    'idPadre' => $r['IdPadre']
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
            $resultado['mensaje'] = "No se encontraron especialidades.";
            $resultado['clase'] = 'alert alert-info';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando especialidades";
        $resultado['clase'] = 'alert alert-error';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }

    return $resultado;
}

function obtenerEspecialidadesAutocompletar($estado = 'A') {
    $db = Database::getConnection();

    if ($estado == 'A') {
        $porEstado = " AND e.Estado = 'A'";
    } else {
        $porEstado = "";
    }
    $sql = "(SELECT e.Id, e.Especialidad AS Nombre
            FROM especialidad e
            WHERE e.IdTipoEspecialidad <> 3 ".$porEstado.")

            UNION

            (SELECT e.Id, concat(e.Especialidad, ' - ',e1.Especialidad) AS Nombre
            FROM especialidad_calificacion_agregada eca
            INNER JOIN especialidad e ON e.Id = eca.IdCalificacionAgregada
            INNER JOIN especialidad e1 ON e1.Id = eca.IdEspecialidadPadre
            WHERE e.IdTipoEspecialidad = 3 ".$porEstado.")

            ORDER BY Nombre";
    $resultado = array();
    try {
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll();
        if (count($rows) > 0) {
            $datos = array();
            foreach ($rows as $r) {
                $row = array (
                    'id' => $r['Id'],
                    'nombre' => $r['Nombre']
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
            $resultado['mensaje'] = "No se encontraron especialidades.";
            $resultado['clase'] = 'alert alert-info';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando especialidades";
        $resultado['clase'] = 'alert alert-error';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }

    return $resultado;
}

function obtenerCalificacionesAgregadasSegunEspecialidadOtorgada($idColegiado){
    $db = Database::getConnection();
    /*
    $sql = "SELECT e.*, ep.Especialidad
        FROM especialidad e
        INNER JOIN colegiadoespecialista ce ON(ce.Especialidad = e.IdPadre)
        INNER JOIN especialidad ep ON(ep.Id = e.IdPadre)
        WHERE ce.IdColegiado = ? AND e.Estado = 'A' AND e.IdTipoEspecialidad = 3
        AND (ce.IdTipoEspecialista IS NULL OR ce.IdTipoEspecialista <> 8)
        ORDER BY e.Especialidad";
    */
    $sql = "SELECT e.*, ep.Especialidad
        FROM especialidad_calificacion_agregada eca
        INNER JOIN colegiadoespecialista ce ON ce.Especialidad = eca.IdEspecialidadPadre
        INNER JOIN especialidad e ON e.Id = eca.IdCalificacionAgregada AND e.Estado = 'A'
        INNER JOIN especialidad ep ON ep.Id = eca.IdEspecialidadPadre AND ep.Estado = 'A'
        WHERE ce.IdColegiado = ? AND (ce.FechaVencimiento IS NULL OR ce.FechaVencimiento >= NOW())
        ORDER BY e.Especialidad";
    $resultado = array();
    try {
        $stmt = $db->prepare($sql);
        $stmt->execute([$idColegiado]);
        $rows = $stmt->fetchAll();
        if (count($rows) > 0) {
            $datos = array();
            foreach ($rows as $r) {
                $row = array (
                    'idEspecialidad' => $r['Id'],
                    'nombreEspecialidad' => $r['Especialidad'],
                    'codigoResolucion' => $r['CodigoRes62707'],
                    'idTipoEspecialidad' => $r['IdTipoEspecialidad'],
                    'estado' => $r['Estado'],
                    'idPadre' => $r['IdPadre'],
                    'nombreEspecialidadPadre' => $r['Especialidad']
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
            $resultado['mensaje'] = "No se encontraron especialidades.";
            $resultado['clase'] = 'alert alert-info';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando especialidades";
        $resultado['clase'] = 'alert alert-error';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }

    return $resultado;
}
