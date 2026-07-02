<?php
class colegiadoRecetariosLogic {

    public function obtenerRecetariosPorId($idReceta){
    try {
        $db = Database::getConnection();
        $sql="SELECT recetas.*, especialidad.Especialidad
        FROM recetas
        LEFT JOIN especialidad ON(especialidad.Id = recetas.IdEspecialidad)
        WHERE recetas.Id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idReceta]);
        $row = $stmt->fetch();
        $resultado = array();
        if ($row) {
            $datos = array(
                    'idReceta' => $row['Id'],
                    'entrega' => $row['Entrega'],
                    'fecha' => $row['Fecha'],
                    'serie' => $row['Serie'],
                    'desde' => $row['ReciboDesde'],
                    'hasta' => $row['ReciboHasta'],
                    'cantidad' => $row['Cantidad'],
                    'idUsuario' => $row['IdUsuario'],
                    'idEspecialidad' => $row['IdEspecialidad'],
                    'estado' => $row['Estado'],
                    'nombreEspecialidad' => $row['Especialidad']
            );
            $resultado['estado'] = TRUE;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = FALSE;
            $resultado['datos'] = NULL;
            $resultado['mensaje'] = "No se encontraron las recetas.";
            $resultado['clase'] = 'alert alert-warning';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando recetas";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }

    return $resultado;
}

    public function obtenerRecetariosPorIdColegiado($idColegiado) {
    try {
        $db = Database::getConnection();
        $sql="SELECT recetas.*, especialidad.Especialidad
        FROM recetas
        LEFT JOIN especialidad ON(especialidad.Id = recetas.IdEspecialidad)
        WHERE recetas.IdColegiado = ? AND recetas.Estado = 'A'";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idColegiado]);
        $datos_raw = $stmt->fetchAll();
        $resultado = array();
        if (count($datos_raw) > 0) {
            $datos = array();
            foreach ($datos_raw as $r) {
                $row = array(
                    'idReceta' => $r['Id'],
                    'entrega' => $r['Entrega'],
                    'fecha' => $r['Fecha'],
                    'serie' => $r['Serie'],
                    'desde' => $r['ReciboDesde'],
                    'hasta' => $r['ReciboHasta'],
                    'cantidad' => $r['Cantidad'],
                    'idUsuario' => $r['IdUsuario'],
                    'idEspecialidad' => $r['IdEspecialidad'],
                    'estado' => $r['Estado'],
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
            $resultado['mensaje'] = "El colegiado no tiene recetas.";
            $resultado['clase'] = 'alert alert-warning';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando recetas del colegiado";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }

    return $resultado;
}

    public function agregarEntregaReceta($serie, $desde, $hasta, $cantidad, $idEspecialidad, $idColegiado){
    $resultado = array();
    try {
        $db = Database::getConnection();
        // Obtener proxima ENTREGA
        $sql="SELECT MAX(Entrega) AS MaxEntrega FROM recetas WHERE IdColegiado = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idColegiado]);
        $rowMax = $stmt->fetch();
        $entrega = ($rowMax && $rowMax['MaxEntrega'] !== null) ? $rowMax['MaxEntrega'] : 0;
        $entrega++;

        $sql="INSERT INTO recetas
            (IdColegiado, Entrega, Fecha, Serie, ReciboDesde, ReciboHasta, Cantidad, IdUsuario, IdEspecialidad)
            VALUE (?, ?, date(now()), ?, ?, ?, ?, ?, ?)";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idColegiado, $entrega, $serie, $desde, $hasta, $cantidad, $_SESSION['user_id'], $idEspecialidad]);
        $resultado['estado'] = TRUE;
        $resultado['idReceta'] = $db->lastInsertId();
        $resultado['mensaje'] = "OK";
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "ERROR AL GENERAR ENTREGA";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function borrarEntregaReceta($idReceta){
    $resultado = array();
    try {
        $db = Database::getConnection();
        $sql="UPDATE recetas SET Estado = 'B' WHERE Id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idReceta]);
        $resultado['estado'] = TRUE;
        $resultado['mensaje'] = "OK";
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "ERROR AL BORRAR ENTREGA";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}
}
