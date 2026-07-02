<?php
class reunion_consejo_pdo {

    function obtenerReunionesPorPeriodo($periodo) {
        $resultado = array();
        
        try {
            // Obtenemos la conexión PDO a través de tu Singleton de Base de Datos
            $db = Database::getConnection(); 
            
            $sql = "SELECT rc.IdReunionConsejo AS id, 
                           rc.Fecha AS fecha, 
                           rc.NroActa AS numeroActa, 
                           rc.TipoReunion AS tipoReunion, 
                           rc.Observacion AS observacion, 
                           (SELECT COUNT(rca.IdReunionConsejoAsistente) FROM reunionconsejoasistente rca WHERE rca.IdReunionConsejo = rc.IdReunionConsejo AND rca.Presente = 'S') AS cantidadAsisten, 
                           (SELECT COUNT(rca.IdReunionConsejoAsistente) FROM reunionconsejoasistente rca WHERE rca.IdReunionConsejo = rc.IdReunionConsejo AND rca.Presente <> 'S') AS cantidadNoAsisten, 
                           rc.Borrado AS borrado
                    FROM reunionconsejo rc
                    WHERE YEAR(rc.Fecha) = :periodo";
            
            $stmt = $db->prepare($sql);
            // PDO mapea los parámetros de manera segura usando bindValue o pasándolos en el execute
            $stmt->execute([':periodo' => $periodo]);
            
            // fetchAll(PDO::FETCH_ASSOC) extrae directamente todas las filas como arrays asociativos
            $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $resultado['estado'] = TRUE;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success'; 
            $resultado['icono'] = 'glyphicon glyphicon-ok'; 

        } catch (PDOException $e) {
            $resultado['estado'] = FALSE;
            // Puedes cambiar $e->getMessage() por un string genérico en producción si no quieres exponer errores internos
            $resultado['mensaje'] = "Error buscando Reuniones de consejo: " . $e->getMessage();
            $resultado['clase'] = 'alert alert-danger'; 
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
        
        return $resultado;
    }

    function obtenerReunionesEntreFechas($fechaDesde, $fechaHasta) {
        $resultado = array();
        
        try {
            $db = Database::getConnection(); 
            
            $sql = "SELECT rc.IdReunionConsejo AS id, 
                           rc.Fecha AS fecha, 
                           rc.NroActa AS numeroActa, 
                           rc.TipoReunion AS tipoReunion, 
                           rc.Observacion AS observacion, 
                           (SELECT COUNT(rca.IdReunionConsejoAsistente) FROM reunionconsejoasistente rca WHERE rca.IdReunionConsejo = rc.IdReunionConsejo AND rca.Presente = 'S') AS cantidadAsisten, 
                           (SELECT COUNT(rca.IdReunionConsejoAsistente) FROM reunionconsejoasistente rca WHERE rca.IdReunionConsejo = rc.IdReunionConsejo AND rca.Presente <> 'S') AS cantidadNoAsisten
                    FROM reunionconsejo rc
                    WHERE rc.Fecha BETWEEN :fechaDesde AND :fechaHasta";
            
            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':fechaDesde' => $fechaDesde,
                ':fechaHasta' => $fechaHasta
            ]);
            
            $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $resultado['estado'] = TRUE;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success'; 
            $resultado['icono'] = 'glyphicon glyphicon-ok'; 

        } catch (PDOException $e) {
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "Error buscando Reuniones de consejo: " . $e->getMessage();
            $resultado['clase'] = 'alert alert-danger'; 
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
        
        return $resultado;
    }

    function obtenerReunionConsejoPorId($id) {
        $resultado = array();
        
        try {
            $db = Database::getConnection();
            
            $sql = "SELECT rc.Fecha AS fecha, 
                           rc.NroActa AS numeroActa, 
                           rc.TipoReunion AS tipoReunion, 
                           rc.Observacion AS observacion, 
                           rc.Borrado AS borrado
                    FROM reunionconsejo rc
                    WHERE rc.IdReunionConsejo = :id";
            
            $stmt = $db->prepare($sql);
            $stmt->execute([':id' => $id]);
            
            // fetch() devuelve la fila encontrada como array asociativo o FALSE si no hay coincidencias
            $datos = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($datos) {
                $resultado['estado'] = true;
                $resultado['mensaje'] = "OK";
                $resultado['datos'] = $datos;
                $resultado['clase'] = 'alert alert-success'; 
                $resultado['icono'] = 'glyphicon glyphicon-ok'; 
            } else {
                $resultado['estado'] = false;
                $resultado['mensaje'] = "No se encontró la reunión de consejo.";
                $resultado['clase'] = 'alert alert-danger'; 
                $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
            }

        } catch (PDOException $e) {
            $resultado['estado'] = false;
            $resultado['mensaje'] = "Error buscando reunión de consejo: " . $e->getMessage();
            $resultado['clase'] = 'alert alert-danger'; 
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }

        return $resultado;
    }

    function obtenerReunionConsejoPorIdAsistente($idReunionConsejoAsistente) {
        $resultado = array();
        
        try {
            $db = Database::getConnection();
            
            $sql = "SELECT rc.IdReunionConsejo AS idReunionConsejo, 
                           rc.Fecha AS fecha, 
                           rc.NroActa AS numeroActa, 
                           rc.TipoReunion AS tipoReunion, 
                           rc.Observacion AS observacion
                    FROM reunionconsejo rc
                    INNER JOIN reunionconsejoasistente rca ON rca.IdReunionConsejo = rc.IdReunionConsejo
                    WHERE rca.IdReunionConsejoAsistente = :idAsistente";
            
            $stmt = $db->prepare($sql);
            $stmt->execute([':idAsistente' => $idReunionConsejoAsistente]);
            
            $datos = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($datos) {
                $resultado['estado'] = true;
                $resultado['mensaje'] = "OK";
                $resultado['datos'] = $datos;
                $resultado['clase'] = 'alert alert-success'; 
                $resultado['icono'] = 'glyphicon glyphicon-ok'; 
            } else {
                $resultado['estado'] = false;
                $resultado['mensaje'] = "No se encontró la reunión de consejo por idReunionConsejoAsistente -> " . $idReunionConsejoAsistente;
                $resultado['clase'] = 'alert alert-danger'; 
                $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
            }

        } catch (PDOException $e) {
            $resultado['estado'] = false;
            $resultado['mensaje'] = "Error buscando reunión de consejo: " . $e->getMessage();
            $resultado['clase'] = 'alert alert-danger'; 
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }

        return $resultado;
    }

    function agregarAsistentesPorIdReunionConsejo($idReunionConsejo, $consejeros) {
        $resultado = array();
        
        try {
            $db = Database::getConnection();
            
            if (!empty($idReunionConsejo) && is_array($consejeros) && count($consejeros) > 0) {
                
                // Iniciamos la transacción en PDO
                $db->beginTransaction();
                $resultado['estado'] = TRUE;
                $presente = 'N';

                $sql = "INSERT INTO reunionconsejoasistente (IdReunionConsejo, IdColegiadoCargo, Presente)
                        VALUES (:idReunionConsejo, :idColegiadoCargo, :presente)";
                
                // Preparamos la consulta UNA SOLA VEZ fuera del bucle para mejor rendimiento
                $stmt = $db->prepare($sql);

                foreach ($consejeros as $value) {
                    $idColegiadoCargo = $value['idColegiadoCargo'];
                    
                    // Ejecutamos pasando los valores actuales de la iteración
                    $stmt->execute([
                        ':idReunionConsejo' => $idReunionConsejo,
                        ':idColegiadoCargo' => $idColegiadoCargo,
                        ':presente'         => $presente
                    ]);
                }

                // Si todo el bucle corrió sin lanzar excepciones, confirmamos la transacción
                $db->commit();

                $resultado['mensaje'] = 'LOS ASISTENTES HAN SIDO GUARDADOS';
                $resultado['clase'] = 'alert alert-success'; 
                $resultado['icono'] = 'glyphicon glyphicon-ok'; 

            } else {
                $resultado['estado'] = FALSE;
                $resultado['mensaje'] = "Error: no hay parámetros válidos.";
                $resultado['clase'] = 'alert alert-danger'; 
                $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
            }

        } catch (PDOException $e) {
            // Si algo falla dentro del bloque try, revertimos los cambios realizados
            if (isset($db) && $db->inTransaction()) {
                $db->rollBack();
            }
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "Error guardando asistentes -> " . $e->getMessage();
            $resultado['clase'] = 'alert alert-danger'; 
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }     

        return $resultado;
    }

    function obtenerAsistentesPorIdReunionConsejo($idReunionConsejo) {
        $resultado = array();
        
        try {
            $db = Database::getConnection();
            
            // Usamos alias (AS) para mantener exactamente la misma estructura de llaves asociativas anteriores
            $sql_asistentes = "SELECT rca.IdReunionConsejoAsistente AS idReunionConsejoAsistente, 
                                      rca.IdColegiadoCargo AS idColegiadoCargo, 
                                      rca.Presente AS presente, 
                                      c.Matricula AS matricula, 
                                      p.Apellido AS apellido, 
                                      p.Nombres AS nombre
                               FROM reunionconsejoasistente rca
                               INNER JOIN colegiadocargo cc ON cc.IdColegiadoCargo = rca.IdColegiadoCargo
                               INNER JOIN colegiado c ON c.Id = cc.IdColegiado
                               INNER JOIN persona p ON p.Id = c.IdPersona
                               WHERE rca.IdReunionConsejo = :idReunionConsejo";
            
            $stmt = $db->prepare($sql_asistentes);
            $stmt->execute([':idReunionConsejo' => $idReunionConsejo]);
            
            // Extrae todos los registros directamente formateados
            $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $resultado['estado'] = TRUE;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success'; 
            $resultado['icono'] = 'glyphicon glyphicon-ok'; 

        } catch (PDOException $e) {
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "Error buscando Asistentes de la Reunión de consejo: " . $e->getMessage();
            $resultado['clase'] = 'alert alert-danger'; 
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
        
        return $resultado;
    }

    function obtenerConsejerosPresentismo($fechaDesde, $fechaHasta) {
        $resultado = array();
        
        try {
            $db = Database::getConnection();
            
            // Usamos alias (AS) para mantener idéntica la nomenclatura de salida de los datos
            $sql = "SELECT cc.IdColegiadoCargo AS idColegiadoCargo, 
                           c.Matricula AS matricula, 
                           p.Apellido AS apellido, 
                           p.Nombres AS nombre, 
                           (SELECT GROUP_CONCAT(CONCAT(rc.Fecha, '_', rca.Presente)) 
                            FROM reunionconsejo rc 
                            INNER JOIN reunionconsejoasistente rca ON rca.IdReunionConsejo = rc.IdReunionConsejo 
                            WHERE rc.Fecha BETWEEN :fechaDesdeSub AND :fechaHastaSub 
                            AND rca.IdColegiadoCargo = cc.IdColegiadoCargo) AS reuniones
                    FROM colegiadocargo cc
                    INNER JOIN reunionconsejoasistente rca1 ON rca1.IdColegiadoCargo = cc.IdColegiadoCargo AND rca1.Presente = 'S'
                    INNER JOIN reunionconsejo rc1 ON rc1.IdReunionConsejo = rca1.IdReunionConsejo AND rc1.Fecha BETWEEN :fechaDesde AND :fechaHasta
                    INNER JOIN colegiado c ON c.Id = cc.IdColegiado
                    INNER JOIN persona p ON p.Id = c.IdPersona
                    WHERE cc.FechaDesde <= DATE(NOW()) AND cc.FechaHasta >= DATE(NOW()) AND cc.Estado = 'A'
                    GROUP BY cc.IdColegiadoCargo
                    ORDER BY p.Apellido, p.Nombres";
            
            $stmt = $db->prepare($sql);
            
            // Como las variables de fechas se usan tanto en la subconsulta como en el join principal,
            // asignamos parámetros nombrados diferenciados de manera limpia.
            $stmt->execute([
                ':fechaDesdeSub' => $fechaDesde,
                ':fechaHastaSub' => $fechaHasta,
                ':fechaDesde'    => $fechaDesde,
                ':fechaHasta'    => $fechaHasta
            ]);
            
            $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $resultado['estado'] = TRUE;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success'; 
            $resultado['icono'] = 'glyphicon glyphicon-ok'; 

        } catch (PDOException $e) {
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "Error buscando presentismo de consejeros: " . $e->getMessage();
            $resultado['clase'] = 'alert alert-danger'; 
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
        
        return $resultado;
    }

    function guardarReunionConsejo($idReunionConsejo, $fecha, $numeroActa, $tipoReunion, $observacion, $datosAnteriores) {
        $resultado = array();
        
        try {
            $db = Database::getConnection();
            $db->beginTransaction();
            
            $esEdicion = (!empty($idReunionConsejo));

            if ($esEdicion) {
                // REUNIÓN EXISTENTE: UPDATE
                $sql = "UPDATE reunionconsejo 
                        SET Fecha = :fecha, NroActa = :numeroActa, TipoReunion = :tipoReunion, Observacion = :observacion
                        WHERE IdReunionConsejo = :idReunionConsejo";
                
                $stmt = $db->prepare($sql);
                $stmt->execute([
                    ':fecha'             => $fecha,
                    ':numeroActa'        => $numeroActa,
                    ':tipoReunion'       => $tipoReunion,
                    ':observacion'       => $observacion,
                    ':idReunionConsejo'  => $idReunionConsejo
                ]);
                
                $tipoMovimiento = 'modificacion';
            } else {
                // NUEVA REUNIÓN: INSERT
                $sql = "INSERT INTO reunionconsejo (Fecha, NroActa, TipoReunion, Observacion)
                        VALUES (:fecha, :numeroActa, :tipoReunion, :observacion)";
                
                $stmt = $db->prepare($sql);
                $stmt->execute([
                    ':fecha'       => $fecha,
                    ':numeroActa'  => $numeroActa,
                    ':tipoReunion' => $tipoReunion,
                    ':observacion' => $observacion
                ]);
                
                // PDO nos otorga directamente el último ID autoincremental de la conexión activa
                $idReunionConsejo = $db->lastInsertId();
                $datosAnteriores = array();
                $tipoMovimiento = 'alta';
            }

            // Bloque para registrar la acción en la tabla de logs
            $idUsuarioCarga = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 1; 
            $datosSerializados = serialize($datosAnteriores);
            
            $sqlLog = "INSERT INTO log_reunionconsejo (Tabla, IdTabla, Fecha, TipoMovimiento, IdUsuario, Datos) 
                       VALUES ('reunionconsejo', :idTabla, NOW(), :tipoMovimiento, :idUsuario, :datos)";
            
            $stmtLog = $db->prepare($sqlLog);
            $stmtLog->execute([
                ':idTabla'        => $idReunionConsejo,
                ':tipoMovimiento' => $tipoMovimiento,
                ':idUsuario'      => $idUsuarioCarga,
                ':datos'          => $datosSerializados
            ]);

            // Confirmamos de forma segura la transacción
            $db->commit();
            
            $resultado['estado'] = TRUE;
            $resultado['idReunionConsejo'] = $idReunionConsejo;
            $resultado['mensaje'] = 'LA REUNIÓN HA SIDO GUARDADA';
            $resultado['clase'] = 'alert alert-success'; 
            $resultado['icono'] = 'glyphicon glyphicon-ok'; 

        } catch (PDOException $e) {
            // Si algo falla, revertimos el estado de la DB
            if (isset($db) && $db->inTransaction()) {
                $db->rollBack();
            }
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "Error procesando la reunión -> " . $e->getMessage();
            $resultado['clase'] = 'alert alert-danger'; 
            $resultado['icono'] = 'glyphicon glyphicon-remove';
        }     
        
        return $resultado;
    }

    function borrarReunionConsejo($idReunionConsejo, $datosAnteriores) {
        $resultado = array();
        
        try {
            $db = Database::getConnection();
            $db->beginTransaction();

            // Determinamos el nuevo estado lógico del borrado según los datos previos
            if (isset($datosAnteriores['borrado']) && $datosAnteriores['borrado'] == 1) {
                $borrado = 0;
                $accion = 'ACTIVADA';
            } else {
                $borrado = 1;
                $accion = 'BORRADA';
            }

            // 1. Ejecutamos el borrado / activación lógica de la reunión
            $sql = "UPDATE reunionconsejo 
                    SET Borrado = :borrado
                    WHERE IdReunionConsejo = :idReunionConsejo";
            
            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':borrado'          => $borrado,
                ':idReunionConsejo' => $idReunionConsejo
            ]);

            // 2. Insertamos la traza en la tabla de logs corporativos
            $tipoMovimiento = 'borrado';
            $datosSerializados = serialize($datosAnteriores);
            $idUsuarioCarga = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 1;

            $sqlLog = "INSERT INTO log_reunionconsejo (Tabla, IdTabla, Fecha, TipoMovimiento, IdUsuario, Datos) 
                       VALUES ('reunionconsejo', :idTabla, NOW(), :tipoMovimiento, :idUsuario, :datos)";
            
            $stmtLog = $db->prepare($sqlLog);
            $stmtLog->execute([
                ':idTabla'        => $idReunionConsejo,
                ':tipoMovimiento' => $tipoMovimiento,
                ':idUsuario'      => $idUsuarioCarga,
                ':datos'          => $datosSerializados
            ]);

            // Si ambos bloques corrieron sin contratiempos, consolidamos la operación
            $db->commit();

            $resultado['estado'] = TRUE;
            $resultado['mensaje'] = 'LA REUNIÓN HA SIDO ' . $accion;
            $resultado['clase'] = 'alert alert-success'; 
            $resultado['icono'] = 'glyphicon glyphicon-ok'; 

        } catch (PDOException $e) {
            // Deshacemos cualquier consulta incompleta si ocurre un fallo crítico
            if (isset($db) && $db->inTransaction()) {
                $db->rollBack();
            }
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "Error procesando la baja/alta de la reunión -> " . $e->getMessage();
            $resultado['clase'] = 'alert alert-danger'; 
            $resultado['icono'] = 'glyphicon glyphicon-remove';
        }     
        
        return $resultado;
    }

    function guardarReunionAsistentes($idReunionConsejo, $fecha, $numeroActa, $tipoReunion, $observacion, $idUsuarioCarga, $asistentesIds) {
        $resultado = array();

        try {
            // Obtenemos el conector PDO e iniciamos la transacción de forma nativa
            $db = Database::getConnection();
            $db->beginTransaction();

            // Aseguramos conversión de tipos básicos para evitar inconsistencias
            $idReunionConsejo = intval($idReunionConsejo);
            $idUsuarioCarga   = intval($idUsuarioCarga);

            if ($idReunionConsejo === 0) {
                // NUEVA REUNIÓN: Hacemos el INSERT
                $queryReunion = "INSERT INTO reunionconsejo (Fecha, NroActa, TipoReunion, Observacion, FechaCarga, IdUsuarioCarga, Borrado) 
                                 VALUES (:fecha, :numeroActa, :tipoReunion, :observacion, NOW(), :idUsuarioCarga, 0)";
                
                $stmt = $db->prepare($queryReunion);
                $stmt->execute([
                    ':fecha'          => $fecha,
                    ':numeroActa'     => $numeroActa,
                    ':tipoReunion'    => $tipoReunion,
                    ':observacion'    => $observacion,
                    ':idUsuarioCarga' => $idUsuarioCarga
                ]);
                
                // Obtenemos el ID de la reunión recién creada
                $idReunionConsejo = $db->lastInsertId();
                $resultado['estado'] = TRUE;
                $resultado['mensaje'] = 'La reunión ha sido creada exitosamente.';
                $resultado['idNuevaReunion'] = $idReunionConsejo; 
            } else {
                // REUNIÓN EXISTENTE: Hacemos el UPDATE
                $queryReunion = "UPDATE reunionconsejo SET 
                                    Fecha = :fecha, 
                                    NroActa = :numeroActa, 
                                    TipoReunion = :tipoReunion, 
                                    Observacion = :observacion,
                                    FechaCarga = NOW(),
                                    IdUsuarioCarga = :idUsuarioCarga
                                 WHERE IdReunionConsejo = :idReunionConsejo";
                
                $stmt = $db->prepare($queryReunion);
                $stmt->execute([
                    ':fecha'             => $fecha,
                    ':numeroActa'        => $numeroActa,
                    ':tipoReunion'       => $tipoReunion,
                    ':observacion'       => $observacion,
                    ':idUsuarioCarga'    => $idUsuarioCarga,
                    ':idReunionConsejo'  => $idReunionConsejo
                ]);
                
                $resultado['estado'] = TRUE;
                $resultado['mensaje'] = 'La reunión ha sido modificada exitosamente.';
            }

            // Si el bloque de reunión se guardó bien, procesamos el bloque de asistencias
            if ($resultado['estado']) {
                
                // Verificamos si ya existen asistentes vinculados a esta reunión
                $queryCheck = "SELECT COUNT(*) AS total FROM reunionconsejoasistente WHERE IdReunionConsejo = :idReunionConsejo";
                $stmtCheck = $db->prepare($queryCheck);
                $stmtCheck->execute([':idReunionConsejo' => $idReunionConsejo]);
                $total = intval($stmtCheck->fetchColumn());

                if ($total === 0) {
                    // MODO ALTA / NO EXISTEN ASISTENTES: Traemos los consejeros vigentes
                    $colegiadoCargoLogic = new colegiadoCargoLogic();
                    $resConsejeros = $colegiadoCargoLogic->obtenerConsejerosVigentes();
                    
                    if ($resConsejeros['estado'] && !empty($resConsejeros['datos'])) {
                        $queryInsertAsistente = "INSERT INTO reunionconsejoasistente (IdReunionConsejo, IdColegiadoCargo, IdColegiado, Presente) 
                                                 VALUES (:idReunionConsejo, :idColegiadoCargo, :idColegiado, :presente)";
                        
                        $stmtInsert = $db->prepare($queryInsertAsistente);
                        $asistentesIdsLimpios = array_map('intval', $asistentesIds);

                        foreach ($resConsejeros['datos'] as $consejero) {
                            $idColegiadoCargo = intval($consejero['idColegiadoCargo']); 
                            $idColegiado      = intval($consejero['idColegiado']);
                            
                            $presente = (in_array($idColegiadoCargo, $asistentesIdsLimpios)) ? 'S' : 'N';

                            $stmtInsert->execute([
                                ':idReunionConsejo'  => $idReunionConsejo,
                                ':idColegiadoCargo' => $idColegiadoCargo,
                                ':idColegiado'      => $idColegiado,
                                ':presente'          => $presente
                            ]);
                        }
                    }
                } else {
                    // MODO EDICIÓN / YA EXISTÍAN ASISTENTES:
                    // 1. Reset general a Ausente ('N')
                    $queryReset = "UPDATE reunionconsejoasistente SET Presente = 'N' WHERE IdReunionConsejo = :idReunionConsejo";
                    $stmtReset = $db->prepare($queryReset);
                    $stmtReset->execute([':idReunionConsejo' => $idReunionConsejo]);

                    // 2. Pasamos a 'S' a los que vengan en el listado
                    if (!empty($asistentesIds)) {
                        $asistentesIdsLimpios = array_map('intval', $asistentesIds);
                        
                        // Generamos los placeholders ?,?,? para PDO
                        $placeholders = implode(',', array_fill(0, count($asistentesIdsLimpios), '?'));
                        
                        $querySetAsistentes = "UPDATE reunionconsejoasistente 
                                               SET Presente = 'S' 
                                               WHERE IdReunionConsejo = ? 
                                               AND IdReunionConsejoAsistente IN ($placeholders)";
                        
                        $stmtSet = $db->prepare($querySetAsistentes);
                        
                        // Combinamos el primer parámetro fijo con el array dinámico de IDs
                        $paramsExecute = array_merge([$idReunionConsejo], $asistentesIdsLimpios);
                        
                        // PDO ejecuta la lista plana sin necesidad de punteros ni referencias manuales
                        $stmtSet->execute($paramsExecute);
                    }
                }
            }

            // Si todo fue correcto cerramos transacción con éxito
            $db->commit();
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok-sign';
            
            return $resultado;

        } catch (PDOException $e) {
            // Cancelamos cualquier alteración si salta una excepción de base de datos
            if (isset($db) && $db->inTransaction()) {
                $db->rollBack();
            }
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "Error crítico transaccional: " . $e->getMessage();
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
            
            return $resultado;
        } 
    }

    function guardarAsistenteEnReunionConsejo($idReunionConsejoAsistente, $asiste, $datosAnteriores) {
        $resultado = array();
        
        try {
            $db = Database::getConnection();
            
            if (!empty($idReunionConsejoAsistente)) {
                // Iniciamos la transacción nativa en PDO
                $db->beginTransaction();

                $presente = ($asiste == 'asiste') ? 'S' : 'N';
                
                // 1. Actualizamos el estado de asistencia
                $sql = "UPDATE reunionconsejoasistente 
                        SET Presente = :presente
                        WHERE IdReunionConsejoAsistente = :idAsistente";
                
                $stmt = $db->prepare($sql);
                $stmt->execute([
                    ':presente'   => $presente,
                    ':idAsistente' => $idReunionConsejoAsistente
                ]);

                // 2. Insertamos la traza en la tabla de logs corporativos
                $tipoMovimiento = 'modificacion';
                $datosSerializados = serialize($datosAnteriores);
                $idUsuarioCarga = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 1;

                $sqlLog = "INSERT INTO log_reunionconsejo (Tabla, IdTabla, Fecha, TipoMovimiento, IdUsuario, Datos) 
                           VALUES ('reunionconsejoasistente', :idTabla, NOW(), :tipoMovimiento, :idUsuario, :datos)";
                
                $stmtLog = $db->prepare($sqlLog);
                $stmtLog->execute([
                    ':idTabla'        => $idReunionConsejoAsistente,
                    ':tipoMovimiento' => $tipoMovimiento,
                    ':idUsuario'      => $idUsuarioCarga,
                    ':datos'          => $datosSerializados
                ]);

                // Confirmamos la transacción al no haber excepciones
                $db->commit();

                $resultado['estado'] = TRUE;
                $resultado['idReunionConsejoAsistente'] = $idReunionConsejoAsistente;
                $resultado['mensaje'] = 'LA REUNIÓN HA SIDO GUARDADA';
                $resultado['clase'] = 'alert alert-success'; 
                $resultado['icono'] = 'glyphicon glyphicon-ok'; 

            } else {
                $resultado['estado'] = FALSE;
                $resultado['mensaje'] = "Error guardando reunión -> Ingreso incorrecto";
                $resultado['clase'] = 'alert alert-danger'; 
                $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
            }

        } catch (PDOException $e) {
            // Si ocurre un error, revertimos cualquier cambio pendiente
            if (isset($db) && $db->inTransaction()) {
                $db->rollBack();
            }
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "Error guardando asistente -> " . $e->getMessage();
            $resultado['clase'] = 'alert alert-danger'; 
            $resultado['icono'] = 'glyphicon glyphicon-remove';
        }     
        
        return $resultado;
    }
}