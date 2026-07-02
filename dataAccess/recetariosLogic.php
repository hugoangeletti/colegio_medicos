<?php
class recetariosLogic {

    public function obtenerRecetariosPorAnio($anio){
    try {
        $db = Database::getConnection();
        $sql="SELECT recetas.Id, recetas.Entrega, recetas.Fecha, recetas.Serie, recetas.ReciboDesde,
                recetas.ReciboHasta, recetas.Cantidad, recetas.Estado, colegiado.Matricula, persona.Apellido,
                persona.Nombres, especialidad.Especialidad, usuario.Usuario
            FROM recetas
            INNER JOIN colegiado ON(colegiado.Id = recetas.IdColegiado)
            INNER JOIN persona ON(persona.Id = colegiado.IdPersona)
            INNER JOIN especialidad ON(especialidad.Id = recetas.IdEspecialidad)
            LEFT JOIN usuario ON(usuario.Id = recetas.IdUsuario)
            WHERE YEAR(Fecha) = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$anio]);
        $datos_raw = $stmt->fetchAll();
        $resultado = array();
        if (count($datos_raw) > 0) {
            $datos = array();
            foreach ($datos_raw as $r) {
                $row = array(
                    'idRecetas' => $r['Id'],
                    'entrega' => $r['Entrega'],
                    'fecha' => $r['Fecha'],
                    'serie' => $r['Serie'],
                    'numeroDesde' => $r['ReciboDesde'],
                    'numeroHasta' => $r['ReciboHasta'],
                    'cantidad' => $r['Cantidad'],
                    'estado' => $r['Estado'],
                    'matricula' => $r['Matricula'],
                    'apellidoNombre' => trim($r['Apellido']).' '.trim($r['Nombres']),
                    'nombreEspecialidad' => $r['Especialidad'],
                    'nombreUsuario' => $r['Usuario']
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
            $resultado['mensaje'] = "No se encontraron recetarios";
            $resultado['clase'] = 'alert alert-warning';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando recetarios";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }

    return $resultado;
}

    public function buscarRecetarios($matricula, $serie, $numero){
    if (isset($matricula) || isset($serie) || isset($numero)) {
        $conMatricula = "";
        if (isset($matricula)) {
            $conMatricula = "colegiado.Matricula = ?";
        }

        $conSerie = "";
        if (isset($serie)) {
            if ($conMatricula != "") {
                $conMatricula .= " AND ";
            }
            $conSerie = "recetas.Serie = ?";
        }

        $conNumero = "";
        if (isset($numero)) {
            if ($conMatricula != "" || $conSerie != "") {
                $conNumero = " AND";
            }
            $conNumero .= " ? BETWEEN recetas.ReciboDesde AND recetas.ReciboHasta";
        }

        try {
            $db = Database::getConnection();
            $sql="SELECT recetas.Id, recetas.Entrega, recetas.Fecha, recetas.Serie, recetas.ReciboDesde,
                    recetas.ReciboHasta, recetas.Cantidad, recetas.Estado, colegiado.Matricula, persona.Apellido,
                    persona.Nombres, especialidad.Especialidad, usuario.Usuario
                FROM recetas
                INNER JOIN colegiado ON(colegiado.Id = recetas.IdColegiado)
                INNER JOIN persona ON(persona.Id = colegiado.IdPersona)
                INNER JOIN especialidad ON(especialidad.Id = recetas.IdEspecialidad)
                LEFT JOIN usuario ON(usuario.Id = recetas.IdUsuario)
                WHERE 1=1 AND ".$conMatricula.$conSerie.$conNumero;

            $stmt = $db->prepare($sql);
            $params = array();
            if (isset($matricula)) { $params[] = $matricula; }
            if (isset($serie)) { $params[] = $serie; }
            if (isset($numero)) { $params[] = $numero; }
            $stmt->execute($params);
            $datos_raw = $stmt->fetchAll();
            $resultado = array();
            if (count($datos_raw) > 0) {
                $datos = array();
                foreach ($datos_raw as $r) {
                    $row = array(
                        'idRecetas' => $r['Id'],
                        'entrega' => $r['Entrega'],
                        'fecha' => $r['Fecha'],
                        'serie' => $r['Serie'],
                        'numeroDesde' => $r['ReciboDesde'],
                        'numeroHasta' => $r['ReciboHasta'],
                        'cantidad' => $r['Cantidad'],
                        'estado' => $r['Estado'],
                        'matricula' => $r['Matricula'],
                        'apellidoNombre' => trim($r['Apellido']).' '.trim($r['Nombres']),
                        'nombreEspecialidad' => $r['Especialidad'],
                        'nombreUsuario' => $r['Usuario']
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
                $resultado['mensaje'] = "No se encontraron recetarios";
                $resultado['clase'] = 'alert alert-warning';
                $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
            }
        } catch (PDOException $e) {
            $resultado['estado'] = false;
            $resultado['mensaje'] = "Error buscando recetarios";
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-remove';
        }
    } else {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Debe ingresar datos ";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }

    return $resultado;
}
}
