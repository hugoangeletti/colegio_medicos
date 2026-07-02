<?php
class especialidades_pdo {

    public function obtenerEspecialidades() {
        $resultado = array();
        try {
            $db = Database::getConnection(); 
            $sql = "SELECT 
                        e.Id, 
                        e.Especialidad, 
                        e.Codigo, 
                        e.CodigoRes62707, 
                        e.IdTipoEspecialidad, 
                        te.Nombre AS TipoEspecialidad,
                        ep.Especialidad AS EspecialidadPadre,
                        (SELECT GROUP_CONCAT(ep1.Especialidad SEPARATOR ' <br> ') 
                            FROM especialidad_calificacion_agregada eca1
                            INNER JOIN especialidad ep1 ON ep1.Id = eca1.IdCalificacionAgregada 
                            WHERE eca1.IdEspecialidadPadre = e.Id) AS CalificacionesAgregadas
                    FROM especialidad e
                    INNER JOIN tipoespecialidad te ON te.Id = e.IdTipoEspecialidad
                    LEFT JOIN especialidad_calificacion_agregada eca ON eca.IdCalificacionAgregada = e.Id
                    LEFT JOIN especialidad ep ON ep.Id = eca.IdEspecialidadPadre
                    WHERE e.Estado = 'A' 
                    ORDER BY e.Especialidad";
            
            $stmt = $db->prepare($sql);
            $stmt->execute();
            
            // PDO nos permite traer todo directamente mapeado
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (count($rows) > 0) {
                $datos = array();
                foreach ($rows as $row) {
                    $datos[] = array(
                        'idEspecialidad' => $row['Id'],
                        'nombreEspecialidad' => $row['Especialidad'],
                        'codigo' => $row['Codigo'],
                        'codigoResolucion' => $row['CodigoRes62707'],
                        'idTipoEspecialidad' => $row['IdTipoEspecialidad'], 
                        'tipoEspecialidad' => $row['TipoEspecialidad'],
                        'especialidadPadre' => $row['EspecialidadPadre'],
                        'calificacionesAgregadas' => $row['CalificacionesAgregadas']
                    );
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
            $resultado['mensaje'] = "Error buscando especialidades: " . $e->getMessage();
            $resultado['clase'] = 'alert alert-error'; 
            $resultado['icono'] = 'glyphicon glyphicon-remove';
        }
        
        return $resultado;
    }

    public function obtenerCalificacionesAgergadas() {
        $resultado = array();
        try {
            $db = Database::getConnection(); 
            //$db->exec("SET SESSION group_concat_max_len = 1000000");
            $sql = "SELECT 
                        e.Id, 
                        e.Especialidad, 
                        e.Codigo, 
                        e.CodigoRes62707, 
                        e.IdTipoEspecialidad, 
                        (SELECT GROUP_CONCAT(ep.Especialidad ORDER BY ep.Especialidad SEPARATOR ' - ') 
                            FROM especialidad_calificacion_agregada eca
                            INNER JOIN especialidad ep ON ep.Id = eca.IdEspecialidadPadre 
                            WHERE eca.IdCalificacionAgregada = e.Id) AS EspecialidadPadre
                    FROM especialidad e
                    WHERE e.IdTipoEspecialidad = 3 AND e.Estado = 'A' 
                    ORDER BY e.Especialidad";
            
            $stmt = $db->prepare($sql);
            $stmt->execute();
            
            // PDO nos permite traer todo directamente mapeado
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (count($rows) > 0) {
                $datos = array();
                foreach ($rows as $row) {
                    $datos[] = array(
                        'idEspecialidad' => $row['Id'],
                        'nombreEspecialidad' => $row['Especialidad'],
                        'codigo' => $row['Codigo'],
                        'codigoResolucion' => $row['CodigoRes62707'],
                        'idTipoEspecialidad' => $row['IdTipoEspecialidad'], 
                        'especialidadPadre' => $row['EspecialidadPadre']
                    );
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
            $resultado['mensaje'] = "Error buscando especialidades: " . $e->getMessage();
            $resultado['clase'] = 'alert alert-error'; 
            $resultado['icono'] = 'glyphicon glyphicon-remove';
        }
        
        return $resultado;
    }

    public function obtenerEspecialidadesAsociadas($idCalificacion) {
        $resultado = array('estado' => false, 'mensaje' => '', 'datos' => array());
        try {
            $db = Database::getConnection();
            $sql = "SELECT e.Id AS idEspecialidad, e.Especialidad AS nombreEspecialidad 
                    FROM especialidad_calificacion_agregada eca
                    INNER JOIN especialidad e ON eca.IdEspecialidadPadre = e.Id
                    WHERE eca.IdCalificacionAgregada = :idCalificacion AND eca.Borrado = 0
                    ORDER BY e.Especialidad";
                    
            $stmt = $db->prepare($sql);
            $stmt->bindValue(':idCalificacion', intval($idCalificacion), PDO::PARAM_INT);
            $stmt->execute();
            
            $resultado['datos'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $resultado['estado'] = true;
            $resultado['mensaje'] = "OK";
            $resultado['clase'] = 'alert alert-success'; 
            $resultado['icono'] = 'glyphicon glyphicon-ok'; 
        } catch (Exception $e) {
            $resultado['estado'] = false;
            $resultado['mensaje'] = $e->getMessage();
            $resultado['clase'] = 'alert alert-info'; 
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
        return $resultado;
    }

    public function guardar_especialidad($action, $id_especialidad, $especialidad, $codigo, $codigoRes, $id_tipo_especialidad) {
        $resultado = array('estado' => false, 'mensaje' => '');
        
        try {
            $db = Database::getConnection();
            
            // Iniciamos la transacción para asegurar ambas tablas
            $db->beginTransaction();

            if ($action == 'create') {
                // Insertar la calificación agregada (IdTipoEspecialidad = 3 de forma fija)
                $sql = "INSERT INTO especialidad (Especialidad, Codigo, CodigoRes62707, IdTipoEspecialidad, Estado) 
                        VALUES (:especialidad, :codigo, :codigoRes, :idTipoEspecialidad, 'A')";
                
                $stmt = $db->prepare($sql);
                $stmt->bindValue(':especialidad', $especialidad, PDO::PARAM_STR);
                $stmt->bindValue(':codigo', !empty($codigo) ? $codigo : null, PDO::PARAM_STR);
                $stmt->bindValue(':codigoRes', $codigoRes, PDO::PARAM_STR);
                $stmt->bindValue(':idTipoEspecialidad', $id_tipo_especialidad, PDO::PARAM_INT);
                $stmt->execute();
                
                // Recuperamos el ID generado por el autoincremental
                $id_especialidad = $db->lastInsertId();

            } elseif ($action == 'edit' && $idCalificacion > 0) {
                // Actualizar la calificación existente asegurando el tipo correcto
                $sql = "UPDATE especialidad 
                        SET Especialidad = :especialidad, Codigo = :codigo, CodigoRes62707 = :codigoRes, IdTipoEspecialidad = :idTipoEspecialidad
                        WHERE Id = :id";
                
                $stmt = $db->prepare($sql);
                $stmt->bindValue(':especialidad', $especialidad, PDO::PARAM_STR);
                $stmt->bindValue(':codigo', !empty($codigo) ? $codigo : null, PDO::PARAM_STR);
                $stmt->bindValue(':codigoRes', $codigoRes, PDO::PARAM_STR);
                $stmt->bindValue(':idTipoEspecialidad', $id_tipo_especialidad, PDO::PARAM_INT);
                $stmt->bindValue(':id', $id_especialidad, PDO::PARAM_INT);
                $stmt->execute();

            } else {
                throw new Exception("Parámetros de acción incorrectos para el procesamiento.");
            }

            // Confirmar todos los cambios si no hubo fallos en ninguna consulta
            $db->commit();
            
            $resultado['estado'] = true;
            $resultado['mensaje'] = "OK";
            $resultado['clase'] = 'alert alert-success'; 
            $resultado['icono'] = 'glyphicon glyphicon-ok'; 

        } catch (Exception $e) {
            // Si algo falla, revertimos la base de datos a su estado previo
            if (isset($db) && $db->inTransaction()) {
                $db->rollBack();
            }
            $resultado['estado'] = false;
            $resultado['mensaje'] = $e->getMessage();
            $resultado['clase'] = 'alert alert-info'; 
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }

        return $resultado;
    }

    public function guardar_calificacion($action, $idCalificacion, $especialidad, $codigo, $codigoRes, $padresAsociados) {
        $resultado = array('estado' => false, 'mensaje' => '');
        
        try {
            $db = Database::getConnection();
            
            // Iniciamos la transacción para asegurar ambas tablas
            $db->beginTransaction();

            if ($action == 'create') {
                // Insertar la calificación agregada (IdTipoEspecialidad = 3 de forma fija)
                $sql = "INSERT INTO especialidad (Especialidad, Codigo, CodigoRes62707, IdTipoEspecialidad, Estado) 
                        VALUES (:especialidad, :codigo, :codigoRes, 3, 'A')";
                
                $stmt = $db->prepare($sql);
                $stmt->bindValue(':especialidad', $especialidad, PDO::PARAM_STR);
                $stmt->bindValue(':codigo', !empty($codigo) ? $codigo : null, PDO::PARAM_STR);
                $stmt->bindValue(':codigoRes', $codigoRes, PDO::PARAM_STR);
                $stmt->execute();
                
                // Recuperamos el ID generado por el autoincremental
                $idCalificacion = $db->lastInsertId();

            } elseif ($action == 'edit' && $idCalificacion > 0) {
                // Actualizar la calificación existente asegurando el tipo correcto
                $sql = "UPDATE especialidad 
                        SET Especialidad = :especialidad, Codigo = :codigo, CodigoRes62707 = :codigoRes 
                        WHERE Id = :id AND IdTipoEspecialidad = 3";
                
                $stmt = $db->prepare($sql);
                $stmt->bindValue(':especialidad', $especialidad, PDO::PARAM_STR);
                $stmt->bindValue(':codigo', !empty($codigo) ? $codigo : null, PDO::PARAM_STR);
                $stmt->bindValue(':codigoRes', $codigoRes, PDO::PARAM_STR);
                $stmt->bindValue(':id', $idCalificacion, PDO::PARAM_INT);
                $stmt->execute();

                // Limpiar las relaciones previas (Borrado físico estándar)
                $sqlClear = "DELETE FROM especialidad_calificacion_agregada WHERE IdCalificacionAgregada = :id";
                $stmtClear = $db->prepare($sqlClear);
                $stmtClear->bindValue(':id', $idCalificacion, PDO::PARAM_INT);
                $stmtClear->execute();
            } else {
                throw new Exception("Parámetros de acción incorrectos para el procesamiento.");
            }

            // Insertar los nuevos padres asignados en la tabla intermedia si el arreglo no está vacío
            if (!empty($padresAsociados) && $idCalificacion > 0) {
                $sqlRelacion = "INSERT INTO especialidad_calificacion_agregada (IdCalificacionAgregada, IdEspecialidadPadre, Borrado) 
                                VALUES (:idCalificacion, :idPadre, 0)";
                $stmtRel = $db->prepare($sqlRelacion);

                foreach ($padresAsociados as $idPadre) {
                    $stmtRel->bindValue(':idCalificacion', $idCalificacion, PDO::PARAM_INT);
                    $stmtRel->bindValue(':idPadre', intval($idPadre), PDO::PARAM_INT);
                    $stmtRel->execute();
                }
            }

            // Confirmar todos los cambios si no hubo fallos en ninguna consulta
            $db->commit();
            
            $resultado['estado'] = true;
            $resultado['mensaje'] = "OK";
            $resultado['clase'] = 'alert alert-success'; 
            $resultado['icono'] = 'glyphicon glyphicon-ok'; 

        } catch (Exception $e) {
            // Si algo falla, revertimos la base de datos a su estado previo
            if (isset($db) && $db->inTransaction()) {
                $db->rollBack();
            }
            $resultado['estado'] = false;
            $resultado['mensaje'] = $e->getMessage();
            $resultado['clase'] = 'alert alert-info'; 
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }

        return $resultado;
    }

    public function obtenerEspecialidadesParaExpedientes($idColegiado) {
        $resultado = array();
        try {
            $db = Database::getConnection();
            
            $sql = "SELECT e.Id, e.Especialidad, e.Codigo, e.CodigoRes62707, e.IdTipoEspecialidad, e.Estado, e.IdPadre 
                    FROM especialidad e
                    WHERE e.Estado = 'A' 
                    ORDER BY e.Especialidad";
            
            $stmt = $db->prepare($sql);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (count($rows) > 0) {
                $datos = array();
                foreach ($rows as $row) {
                    $datos[] = array(
                        'idEspecialidad' => $row['Id'],
                        'nombreEspecialidad' => $row['Especialidad'],
                        'codigoResolucion' => $row['CodigoRes62707'],
                        'idTipoEspecialidad' => $row['IdTipoEspecialidad'], 
                        'estado' => $row['Estado'],
                        'idPadre' => $row['IdPadre']
                    );
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
            $resultado['mensaje'] = "Error buscando especialidades: " . $e->getMessage();
            $resultado['clase'] = 'alert alert-error'; 
            $resultado['icono'] = 'glyphicon glyphicon-remove';
        }
        
        return $resultado;
    }

    public function obtenerEspecialidadesAutocompletar($estado = 'A', $query = '') {
        $resultado = array();
        try {
            $db = Database::getConnection();

            // Evitamos inyección concatenando de forma segura basándonos en lógica interna
            $porEstado = ($estado == 'A') ? " AND e.Estado = 'A'" : "";
            
            $sql = "SELECT e.Id, e.Especialidad AS Nombre, e.IdTipoEspecialidad 
                    FROM especialidad e 
                    WHERE e.Especialidad COLLATE utf8_general_ci LIKE :query
                    " . $porEstado . "
                    ORDER BY Nombre";
            
            $stmt = $db->prepare($sql);
            $term = "%$query%";
            $stmt->bindParam(':query', $term);
            $stmt->execute();
            
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (count($rows) > 0) {
                $datos = array();
                foreach ($rows as $row) {
                    $nombre = $row['Nombre'];
                    $nombre .= ($row['IdTipoEspecialidad'] == 3) ? ' (Calificación Agregada)' : ''; 
                    $datos[] = array(
                        'id' => $row['Id'],
                        'nombre' => $nombre
                    );
                }
                $resultado['estado'] = TRUE;
                $resultado['datos'] = $datos;
            } else {
                $resultado['estado'] = FALSE;
                $resultado['datos'] = NULL;
            }
        } catch (PDOException $e) {
            $resultado['estado'] = false;
            $resultado['datos'] = NULL;
        }
        
        return $resultado;
    }

    public function obtenerCalificacionesAgregadasSegunEspecialidadOtorgada($idColegiado){
        $resultado = array();
        try {
            $db = Database::getConnection();
            
            $sql = "SELECT e.Id, e.Especialidad, e.Codigo, e.CodigoRes62707, e.IdTipoEspecialidad, e.Estado, e.IdPadre, ep.Especialidad AS EspecialidadPadre
                    FROM especialidad_calificacion_agregada eca
                    INNER JOIN colegiadoespecialista ce ON ce.Especialidad = eca.IdEspecialidadPadre
                    INNER JOIN especialidad e ON e.Id = eca.IdCalificacionAgregada AND e.Estado = 'A' 
                    INNER JOIN especialidad ep ON ep.Id = eca.IdEspecialidadPadre AND ep.Estado = 'A' 
                    WHERE ce.IdColegiado = ? AND (ce.FechaVencimiento IS NULL OR ce.FechaVencimiento >= NOW())
                    ORDER BY e.Especialidad";
            
            $stmt = $db->prepare($sql);
            // Ejecutamos pasando el parámetro posicional directamente en un array
            $stmt->execute([$idColegiado]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (count($rows) > 0) {
                $datos = array();
                foreach ($rows as $row) {
                    $datos[] = array(
                        'idEspecialidad' => $row['Id'],
                        'nombreEspecialidad' => $row['Especialidad'],
                        'codigoResolucion' => $row['CodigoRes62707'],
                        'idTipoEspecialidad' => $row['IdTipoEspecialidad'], 
                        'estado' => $row['Estado'],
                        'idPadre' => $row['IdPadre'],
                        'nombreEspecialidadPadre' => $row['EspecialidadPadre']
                    );
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
            $resultado['mensaje'] = "Error buscando especialidades: " . $e->getMessage();
            $resultado['clase'] = 'alert alert-error'; 
            $resultado['icono'] = 'glyphicon glyphicon-remove';
        }
        
        return $resultado;
    }
}

