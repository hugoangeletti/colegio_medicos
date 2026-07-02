<?php
class cursos_pdo {
    
    //estado de liquidacion de cursos
    const LIQUIDACION_INICIADA   = '1';
    const LIQUIDACION_ANULADA    = '2';
    const LIQUIDACION_CERRADA    = '3';
    const LIQUIDACION_FINALIZADA = '4';

    //estado de liquidacion de cursos docentes
    const LIQUIDACION_DOCENTES_INICIADA   = '1';
    const LIQUIDACION_DOCENTES_ANULADA    = '2';
    const LIQUIDACION_DOCENTES_CERRADA    = '3';
    const LIQUIDACION_DOCENTES_FINALIZADA = '4';
    const LIQUIDACION_DOCENTES_SALDOS     = '5';

    //cargos en el curso
    const CARGO_DIRECTOR = '1';
    const CARGO_COORDINADOR = '2';
    const CARGO_DOCENTE = '3';

    public function obtenerCursos($estadoCurso) {
        try {
            $db = Database::getConnection(); // Asumiendo que usas tu clase Database anterior

            $sql = "SELECT c.Id AS idCurso, c.Titulo AS titulo, c.FechaInicio AS fechaInicio, 
                           c.Director AS director, c.Estado AS estado, 
                           (SELECT COUNT(cc.Id) FROM cursoscuotas cc WHERE cc.IdCursos = c.Id) AS cantidadCuotas, 
                           (SELECT COUNT(ca.Id) FROM cursosasistente ca WHERE ca.IdCursos = c.Id AND ca.Estado = 'S') AS cantidadAsistentes, 
                           c.CupoInscriptos AS cupoInscriptos, c.InscripcionDesde AS inscripcionDesde, 
                           c.InscripcionHasta AS inscripcionHasta
                    FROM cursos c";

            // Filtro dinámico
            if (!empty($estadoCurso)) {
                $sql .= " WHERE c.Estado = :estado";
            }
            
            $sql .= " ORDER BY c.Titulo";

            $stmt = $db->prepare($sql);

            if (!empty($estadoCurso)) {
                $stmt->bindParam(':estado', $estadoCurso, PDO::PARAM_STR);
            }

            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (count($rows) > 0) {
                return [
                    'estado' => true,
                    'mensaje' => "OK",
                    'datos' => $rows,
                    'clase' => 'alert alert-success',
                    'icono' => 'glyphicon glyphicon-ok'
                ];
            }

            return [
                'estado' => false,
                'mensaje' => "No hay cursos",
                'datos' => [],
                'clase' => 'alert alert-info',
                'icono' => 'glyphicon glyphicon-exclamation-sign'
            ];

        } catch (PDOException $e) {
            return [
                'estado' => false,
                'mensaje' => "Error buscando cursos: " . $e->getMessage(),
                'clase' => 'alert alert-danger',
                'icono' => 'glyphicon glyphicon-remove'
            ];
        }
    }

    public function obtenerCursoPorId($idCurso) {
        try {
            $db = Database::getConnection();
            
            $sql = "SELECT 
                        c.Id AS idCurso, 
                        c.Titulo AS titulo, 
                        c.Tema AS tema, 
                        c.Dias AS dias, 
                        c.Fechas AS fechas, 
                        c.Salon AS salon, 
                        c.Lugar AS lugar, 
                        c.Director AS director, 
                        c.Coordinador AS coordinador, 
                        c.FechaInicio AS fechaInicio, 
                        c.VigenciaHasta AS vigenciaHasta, 
                        c.Estado AS estado, 
                        c.CupoInscriptos AS cupoInscriptos, 
                        c.InscripcionDesde AS inscripcionDesde, 
                        c.InscripcionHasta AS inscripcionHasta,
                        c.ValorCuotaLiquidacion AS valorCuotaLiquidacion,
                        c.PorcentajeRetencionColegio AS porcentajeRetencionColegio
                    FROM cursos c
                    WHERE c.Id = :idCurso";

            $stmt = $db->prepare($sql);
            $stmt->bindParam(':idCurso', $idCurso, PDO::PARAM_INT);
            $stmt->execute();

            // Usamos fetch porque buscamos un único registro
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row) {
                return [
                    'estado' => true,
                    'mensaje' => "OK",
                    'datos' => $row,
                    'clase' => 'alert alert-success',
                    'icono' => 'glyphicon glyphicon-ok'
                ];
            }

            return [
                'estado' => false,
                'mensaje' => "No se encontró el curso con ID: " . $idCurso,
                'datos' => null,
                'clase' => 'alert alert-warning',
                'icono' => 'glyphicon glyphicon-exclamation-sign'
            ];

        } catch (PDOException $e) {
            return [
                'estado' => false,
                'mensaje' => "Error buscando curso: " . $e->getMessage(),
                'clase' => 'alert alert-danger',
                'icono' => 'glyphicon glyphicon-remove'
            ];
        }
    }

    public function obtenerCuotasPorIdCurso($idCurso) {
        // 1. Obtener la conexión PDO
        $db = Database::getConnection();
        
        $sql = "SELECT cc.Id, cc.Cuota, cc.DetalleCuota, cc.FechaVencimiento, cc.Importe, 
                (SELECT COUNT(*) FROM cursosasistentecuotas a INNER JOIN cursosasistente b ON b.Id = a.IdCursosAsistente WHERE b.IdCursos = cc.IdCursos AND cc.Cuota = a.Cuota) AS CantidadCuotas
                FROM cursoscuotas cc 
                WHERE cc.IdCursos = :idCurso";
                
        $resultado = array();
        
        try {
            // 2. Preparar y ejecutar la consulta
            $stmt = $db->prepare($sql);
            $stmt->execute([':idCurso' => $idCurso]);
            
            // 3. Procesar resultados si existen registros
            if ($stmt->rowCount() > 0) {
                $datos = array();
                
                while ($rowFetch = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $row = array (
                        'idCuota'          => $rowFetch['Id'],
                        'cuota'            => $rowFetch['Cuota'],
                        'detalleCuota'     => $rowFetch['DetalleCuota'],
                        'fechaVencimiento' => $rowFetch['FechaVencimiento'],
                        'importe'          => $rowFetch['Importe'],
                        'cantidadCuotas'   => $rowFetch['CantidadCuotas']
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
                $resultado['mensaje'] = "No hay cuotas del curso";
                $resultado['clase'] = 'alert alert-info'; 
                $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
            }
        } catch (PDOException $e) {
            // 4. Captura de errores de base de datos
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "Error buscando cuotas del curso";
            $resultado['clase'] = 'alert alert-danger'; 
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
        
        return $resultado;
    }

    public function obtenerAsistentesPorIdCurso($idCurso, $asiste) {
        // 1. Obtener la conexión PDO
        $db = Database::getConnection();
        
        // 2. Construir la consulta dinámica y los parámetros
        $conAsiste = "";
        $params = [':idCurso' => $idCurso];

        if (isset($asiste) && $asiste <> "") {
            $conAsiste = "AND ca.Estado = :asiste";
            $params[':asiste'] = $asiste;
        }

        $sql = "SELECT ca.Id, ca.ApellidoNombre, ca.Estado, ca.FechaDiploma, ca.FechaEntrega, ca.IdColegiado, c.Matricula, ca.IdUsuario, u.Usuario AS nombreUsuario, ca.FechaCarga, ca.FechaBaja, ca.IdUsuarioBaja, u1.Usuario AS usuarioBaja, ca.Observaciones,
            (SELECT COUNT(*) FROM cursosasistentecuotas cac WHERE cac.IdCursosAsistente = ca.Id AND cac.FechaPago <> '0000-00-00' AND cac.FechaPago IS NOT NULL) AS CuotasPagas, 
            if (ca.IdColegiado IS NOT NULL, (SELECT cc.CorreoElectronico FROM colegiadocontacto cc WHERE cc.IdColegiado = c.Id AND cc.IdEstado = 1), NULL) AS CorreoElectronico
            FROM cursosasistente ca
            LEFT JOIN colegiado c ON c.Id = ca.IdColegiado
            LEFT JOIN usuario u ON u.Id = ca.IdUsuario
            LEFT JOIN usuario u1 ON u1.Id = ca.IdUsuarioBaja
            WHERE ca.IdCursos = :idCurso AND ca.Borrado = 0 " . $conAsiste . " ORDER BY ca.ApellidoNombre";

        $resultado = array();

        try {
            // 3. Preparar y ejecutar la consulta
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            
            // 4. Procesar los resultados
            if ($stmt->rowCount() > 0) {
                $datos = array();
                
                // PDO mapea directamente las columnas a un array asociativo
                while ($rowFetch = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $row = array (
                        'idCursosAsistente' => $rowFetch['Id'],
                        'apellidoNombre'    => $rowFetch['ApellidoNombre'],
                        'estado'            => $rowFetch['Estado'],
                        'fechaDiploma'      => $rowFetch['FechaDiploma'],
                        'fechaEntrega'      => $rowFetch['FechaEntrega'],
                        'idColegiado'       => $rowFetch['IdColegiado'],
                        'matricula'         => $rowFetch['Matricula'],
                        'idUsuario'         => $rowFetch['IdUsuario'],
                        'nombreUsuario'     => $rowFetch['nombreUsuario'],
                        'fechaCarga'        => $rowFetch['FechaCarga'],
                        'fechaBaja'         => $rowFetch['FechaBaja'],
                        'idUsuarioBaja'     => $rowFetch['IdUsuarioBaja'],
                        'usuarioBaja'       => $rowFetch['usuarioBaja'],
                        'observaciones'     => $rowFetch['Observaciones'],
                        'cuotasPagas'       => $rowFetch['CuotasPagas'],
                        'correoElectronico' => $rowFetch['CorreoElectronico']
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
                $resultado['mensaje'] = "No hay asistentes en el curso";
                $resultado['clase'] = 'alert alert-info'; 
                $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
            }
        } catch (PDOException $e) {
            // 5. Manejo de errores de base de datos
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "Error buscando asistentes en el curso";
            $resultado['clase'] = 'alert alert-danger'; 
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
        
        return $resultado;
    }

    public function obtenerCursosAsistenteCuotaPorId($idCursosAsistenteCuota) {
        // 1. Obtener la conexión PDO
        $db = Database::getConnection();
        $resultado = array();
        
        $sql = "SELECT cac.Id, cac.IdCursosAsistente, cac.Cuota, cac.Importe, cac.FechaVencimiento, cac.FechaPago, cac.Recibo, cac.DetalleCuota, cac.FechaActualizacion
                FROM cursosasistentecuotas cac
                WHERE cac.Id = :idCursosAsistenteCuota";
                
        try {
            // 2. Preparar y ejecutar la consulta de forma segura
            $stmt = $db->prepare($sql);
            $stmt->execute([':idCursosAsistenteCuota' => $idCursosAsistenteCuota]);
            
            // 3. Obtener el registro asociativo
            $rowFetch = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($rowFetch) {
                $datos = array (
                    'idCursosAsistenteCuota' => $rowFetch['Id'],
                    'idCursosAsistente'      => $rowFetch['IdCursosAsistente'],
                    'cuota'                  => $rowFetch['Cuota'],
                    'importe'                => $rowFetch['Importe'],
                    'fechaVencimiento'       => $rowFetch['FechaVencimiento'],
                    'fechaPago'              => $rowFetch['FechaPago'],
                    'recibo'                 => $rowFetch['Recibo'],
                    'detalleCuota'           => $rowFetch['DetalleCuota'],
                    'fechaActualizacion'     => $rowFetch['FechaActualizacion']
                );
                
                $resultado['estado'] = TRUE;
                $resultado['mensaje'] = "OK";
                $resultado['datos'] = $datos;
                $resultado['clase'] = 'alert alert-success'; 
                $resultado['icono'] = 'glyphicon glyphicon-ok'; 
            } else {
                // Control por si el ID enviado no existe
                $resultado['estado'] = FALSE;
                $resultado['mensaje'] = "No se encontró la cuota solicitada";
                $resultado['clase'] = 'alert alert-info'; 
                $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
            }
            
        } catch (PDOException $e) {
            // 4. Captura ante fallos de sintaxis SQL o de conexión
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "Error buscando cuota";
            $resultado['clase'] = 'alert alert-danger'; 
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }

        return $resultado;
    }

    public function obtenerCuotasPorAsistente($idCursosAsistente) {
        // 1. Obtener la conexión PDO
        $db = Database::getConnection();
        
        $sql = "SELECT Id, Cuota, Importe, FechaVencimiento, DetalleCuota, FechaPago, Recibo
                FROM cursosasistentecuotas 
                WHERE IdCursosAsistente = :idCursosAsistente AND Borrado = 0";
                
        $resultado = array();
        
        try {
            // 2. Preparar y ejecutar la consulta
            $stmt = $db->prepare($sql);
            $stmt->execute([':idCursosAsistente' => $idCursosAsistente]);
            
            $datos = array();
            $cuotasAdeudadas = 0;
            
            // 3. Procesar los resultados dinámicamente
            while ($rowFetch = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $fechaPago = $rowFetch['FechaPago'];
                
                if ($fechaPago == '0000-00-00' || !isset($fechaPago) || $fechaPago == '') {
                    $cuotasAdeudadas += 1;
                    $abonada = FALSE;
                } else {
                    $abonada = TRUE;
                }
                
                $row = array (
                    'idCursosAsistenteCuota' => $rowFetch['Id'],
                    'cuota'                  => $rowFetch['Cuota'],
                    'importe'                => $rowFetch['Importe'],
                    'fechaVencimiento'       => $rowFetch['FechaVencimiento'],
                    'detalleCuota'           => $rowFetch['DetalleCuota'],
                    'fechaPago'              => $fechaPago,
                    'recibo'                 => $rowFetch['Recibo'],
                    'abonada'                => $abonada
                );
                array_push($datos, $row);
            }
            
            // 4. Armar respuesta de éxito
            $resultado['estado'] = TRUE;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['cuotasAdeudadas'] = $cuotasAdeudadas;
            $resultado['cantidad'] = count($datos); // Reemplazado sizeof por count (alias estándar)
            $resultado['clase'] = 'alert alert-success'; 
            $resultado['icono'] = 'glyphicon glyphicon-ok'; 
            
        } catch (PDOException $e) {
            // 5. Manejo de errores de base de datos
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "Error buscando cuotas a pagar";
            $resultado['clase'] = 'alert alert-danger'; 
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
        
        return $resultado;
    }

    public function guardarCuotaAsistente($idCursosAsistenteCuota, $idCursosAsistente, $cuota, $detalleCuota, $importe, $fechaVencimiento, $fechaPago, $recibo, $datosAnteriores) {
        // 1. Obtener la conexión PDO
        $db = Database::getConnection();
        $resultado = array();
        
        try {
            // 2. Iniciar la transacción nativa
            $db->beginTransaction();

            $idUsuario = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 49; // Fallback por seguridad si no hay sesión

            if (isset($idCursosAsistenteCuota) && $idCursosAsistenteCuota <> "") {
                // Caso UPDATE
                $sql = "UPDATE cursosasistentecuotas
                        SET Cuota = :cuota, DetalleCuota = :detalleCuota, Importe = :importe, 
                            FechaVencimiento = :fechaVencimiento, FechaPago = :fechaPago, Recibo = :recibo
                        WHERE Id = :idCursosAsistenteCuota";
                
                $stmt = $db->prepare($sql);
                $stmt->execute([
                    ':cuota'                  => $cuota,
                    ':detalleCuota'           => $detalleCuota,
                    ':importe'                => $importe,
                    ':fechaVencimiento'       => $fechaVencimiento,
                    ':fechaPago'              => !empty($fechaPago) ? $fechaPago : null, // Guarda NULL si viene vacío
                    ':recibo'                 => !empty($recibo) ? $recibo : null,
                    ':idCursosAsistenteCuota' => $idCursosAsistenteCuota
                ]);

                // Registrar log de auditoría para la modificación
                $tipoMovimiento = 'modificacion';
                $datos = serialize($datosAnteriores);
                
                $sqlLog = "INSERT INTO log_cursos (Tabla, IdTabla, Fecha, TipoMovimiento, IdUsuario, Datos) 
                           VALUES ('cursosasistentecuotas', :idCursosAsistenteCuota, NOW(), :tipoMovimiento, :idUsuario, :datos)";
                
                $stmtLog = $db->prepare($sqlLog);
                $stmtLog->execute([
                    ':idCursosAsistenteCuota' => $idCursosAsistenteCuota,
                    ':tipoMovimiento'         => $tipoMovimiento,
                    ':idUsuario'              => $idUsuario,
                    ':datos'                  => $datos
                ]);

                $resultado['estado'] = TRUE;
                $resultado['idCursosAsistenteCuota'] = $idCursosAsistenteCuota;
                $resultado['mensaje'] = 'LA CUOTA HA SIDO GUARDADA';
                $resultado['clase'] = 'alert alert-success'; 
                $resultado['icono'] = 'glyphicon glyphicon-ok'; 

            } else {
                // Caso INSERT
                $sql = "INSERT INTO cursosasistentecuotas (IdCursosAsistente, Cuota, DetalleCuota, Importe, FechaVencimiento)
                        VALUES (:idCursosAsistente, :cuota, :detalleCuota, :importe, :fechaVencimiento)";
                
                $stmt = $db->prepare($sql);
                $stmt->execute([
                    ':idCursosAsistente' => $idCursosAsistente,
                    ':cuota'             => $cuota,
                    ':detalleCuota'      => $detalleCuota,
                    ':importe'           => $importe,
                    ':fechaVencimiento'  => $fechaVencimiento
                ]);

                // Obtener el ID generado por el INSERT
                $idCursosAsistenteCuota = $db->lastInsertId();

                $resultado['estado'] = TRUE;
                $resultado['idCursosAsistenteCuota'] = $idCursosAsistenteCuota;
                $resultado['mensaje'] = 'LA CUOTA HA SIDO CREADA SATISFACTORIAMENTE';
                $resultado['clase'] = 'alert alert-success'; 
                $resultado['icono'] = 'glyphicon glyphicon-ok';
            }

            // 3. Confirmar cambios si todo fue exitoso
            $db->commit();

        } catch (PDOException $e) {
            // 4. Cancelar cambios ante cualquier error de base de datos
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "Error guardando cuota del curso -> " . $e->getMessage();
            $resultado['clase'] = 'alert alert-danger'; 
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }     

        return $resultado;
    }

    public function borrarCuotaAsistenteCurso($idCursosAsistenteCuota, $datosAnteriores) {
        // 1. Obtener la conexión PDO
        $db = Database::getConnection();
        $resultado = array();
        
        try {
            // 2. Iniciar la transacción nativa
            $db->beginTransaction();

            $idUsuario = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 49; // Fallback por seguridad si no hay sesión

            // 3. Ejecutar el borrado lógico de la cuota
            $sql = "UPDATE cursosasistentecuotas
                    SET Borrado = 1
                    WHERE Id = :idCursosAsistenteCuota";
                    
            $stmt = $db->prepare($sql);
            $stmt->execute([':idCursosAsistenteCuota' => $idCursosAsistenteCuota]);

            // 4. Registrar la auditoría en la tabla log_cursos
            $tipoMovimiento = 'borrado';
            $datos = serialize($datosAnteriores);
            
            $sqlLog = "INSERT INTO log_cursos (Tabla, IdTabla, Fecha, TipoMovimiento, IdUsuario, Datos) 
                       VALUES ('cursosasistentecuotas', :idCursosAsistenteCuota, NOW(), :tipoMovimiento, :idUsuario, :datos)";
                       
            $stmtLog = $db->prepare($sqlLog);
            $stmtLog->execute([
                ':idCursosAsistenteCuota' => $idCursosAsistenteCuota,
                ':tipoMovimiento'         => $tipoMovimiento,
                ':idUsuario'              => $idUsuario,
                ':datos'                  => $datos
            ]);

            // Si el flujo no arroja excepciones, la operación fue un éxito
            $resultado['estado'] = TRUE;
            $resultado['mensaje'] = 'LA CUOTA HA SIDO BORRADA';
            $resultado['clase'] = 'alert alert-success'; 
            $resultado['icono'] = 'glyphicon glyphicon-ok'; 

            // 5. Confirmar los cambios
            $db->commit();

        } catch (PDOException $e) {
            // 6. Deshacer cambios ante cualquier fallo SQL
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "ERROR AL BORRAR CUOTA -> " . $e->getMessage();
            $resultado['clase'] = 'alert alert-danger'; 
            $resultado['icono'] = 'glyphicon glyphicon-remove';
        }     

        return $resultado;
    }

    public function obtenerAsistentePorId($idAsistente){
        // 1. Obtener la conexión PDO
        $db = Database::getConnection();
        
        $sql = "SELECT ca.IdCursos, ca.ApellidoNombre, ca.IdColegiado, ca.Estado, cur.Titulo, c.Matricula
                FROM cursosasistente ca
                INNER JOIN cursos cur ON cur.Id = ca.IdCursos
                LEFT JOIN colegiado c ON c.Id = ca.IdColegiado
                WHERE ca.Id = :idAsistente";
                
        $resultado = array();
        
        try {
            // 2. Preparar y ejecutar la consulta
            $stmt = $db->prepare($sql);
            $stmt->execute([':idAsistente' => $idAsistente]);
            
            // 3. Obtener el único registro
            $rowFetch = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($rowFetch) {
                $datos = array (
                    'idCurso'        => $rowFetch['IdCursos'],
                    'apellidoNombre' => $rowFetch['ApellidoNombre'],
                    'idColegiado'    => $rowFetch['IdColegiado'],
                    'asiste'         => $rowFetch['Estado'],
                    'tituloCurso'    => $rowFetch['Titulo'],
                    'matricula'      => $rowFetch['Matricula']
                );
                
                $resultado['estado'] = TRUE;
                $resultado['mensaje'] = "OK";
                $resultado['datos'] = $datos;
                $resultado['clase'] = 'alert alert-success'; 
                $resultado['icono'] = 'glyphicon glyphicon-ok'; 
            } else {
                // Caso en el que el ID no exista en la base de datos
                $resultado['estado'] = FALSE;
                $resultado['mensaje'] = "No se encontró el asistente solicitado";
                $resultado['clase'] = 'alert alert-info'; 
                $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
            }
            
        } catch (PDOException $e) {
            // 4. Manejo de errores de base de datos
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "Error buscando asistente";
            $resultado['clase'] = 'alert alert-danger'; 
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }

        return $resultado;
    }

    public function guardarAsistenteCurso($idCursosAsistente, $idCurso, $idColegiado, $apellidoNombre, $estado, $borrado, $datosAnteriores) {
        // 1. Obtener la conexión PDO
        $db = Database::getConnection();
        $resultado = array();
        
        try {
            // 2. Iniciar la transacción de manera nativa en PDO
            $db->beginTransaction();

            if (isset($_SESSION['user_id'])) {
                $idUsuario = $_SESSION['user_id'];
            } else {
                $idUsuario = 49; // tramites-web o ws
            }

            if (isset($idCursosAsistente) && $idCursosAsistente <> "") {
                // Caso UPDATE
                $sql = "UPDATE cursosasistente
                        SET ApellidoNombre = :apellidoNombre, Estado = :estado, Borrado = :borrado, FechaCarga = DATE(NOW()), IdUsuario = :idUsuario
                        WHERE Id = :idCursosAsistente";
                
                $stmt = $db->prepare($sql);
                $stmt->execute([
                    ':apellidoNombre'     => $apellidoNombre,
                    ':estado'             => $estado,
                    ':borrado'            => $borrado,
                    ':idUsuario'          => $idUsuario,
                    ':idCursosAsistente'  => $idCursosAsistente
                ]);
                
                // Log de auditoría para la modificación
                $tipoMovimiento = 'modificacion';
                $datos = serialize($datosAnteriores);
                
                $sqlLog = "INSERT INTO log_cursos (Tabla, IdTabla, Fecha, TipoMovimiento, IdUsuario, Datos) 
                           VALUES ('cursosasistente', :idCursosAsistente, NOW(), :tipoMovimiento, :idUsuario, :datos)";
                
                $stmtLog = $db->prepare($sqlLog);
                $stmtLog->execute([
                    ':idCursosAsistente' => $idCursosAsistente,
                    ':tipoMovimiento'    => $tipoMovimiento,
                    ':idUsuario'         => $idUsuario,
                    ':datos'             => $datos
                ]);

                $resultado['estado'] = TRUE;
                $resultado['idCursosAsistente'] = $idCursosAsistente;
                $resultado['mensaje'] = 'EL ASISTENTE HA SIDO INSCRIPTO SATISFACTORIAMENTE.';
                $resultado['clase'] = 'alert alert-success'; 
                $resultado['icono'] = 'glyphicon glyphicon-ok'; 

            } else {
                // Caso INSERT
                $sql = "INSERT INTO cursosasistente (IdCursos, IdColegiado, ApellidoNombre, Estado, FechaCarga, IdUsuario)
                        VALUES (:idCurso, :idColegiado, :apellidoNombre, 'S', DATE(NOW()), :idUsuario)";
                
                $stmt = $db->prepare($sql);
                $stmt->execute([
                    ':idCurso'        => $idCurso,
                    ':idColegiado'    => $idColegiado,
                    ':apellidoNombre' => $apellidoNombre,
                    ':idUsuario'      => $idUsuario
                ]);
                
                // Obtener el ID generado por el INSERT usando PDO
                $idCursosAsistente = $db->lastInsertId();
                $resultado['estado'] = TRUE;

                // Generar las cuotas del asistente
                $resCuotas = $this->obtenerCuotasPorIdCurso($idCurso);
                
                if ($resCuotas['estado']) {
                    $sqlCuota = "INSERT INTO cursosasistentecuotas (IdCursosAsistente, Cuota, DetalleCuota, Importe, FechaVencimiento)
                                 VALUES (:idCursosAsistente, :cuota, :detalleCuota, :importe, :fechaVencimiento)";
                    $stmtCuota = $db->prepare($sqlCuota);

                    foreach ($resCuotas['datos'] as $dato) {
                        $stmtCuota->execute([
                            ':idCursosAsistente' => $idCursosAsistente,
                            ':cuota'             => $dato['cuota'],
                            ':detalleCuota'      => $dato['detalleCuota'],
                            ':importe'           => $dato['importe'],
                            ':fechaVencimiento'  => $dato['fechaVencimiento']
                        ]);
                    }
                } else {
                    $resultado['estado'] = FALSE;
                    $resultado['mensaje'] = "ERROR AL AGREGAR ASISTENTE DEL CURSO, BUSCANDO CUOTAS DEL CURSO -> " . $resCuotas['mensaje'];
                    $resultado['clase'] = 'alert alert-danger'; 
                    $resultado['icono'] = 'glyphicon glyphicon-remove';
                }

                // Si se pudieron agregar las cuotas correctamente
                if ($resultado['estado']) {
                    $resultado['idCursosAsistente'] = $idCursosAsistente;
                    $resultado['mensaje'] = 'EL ASISTENTE HA SIDO INSCRIPTO SATISFACTORIAMENTE.';
                    $resultado['clase'] = 'alert alert-success'; 
                    $resultado['icono'] = 'glyphicon glyphicon-ok';
                }
            }

            // 3. Confirmar la transacción si todo fue exitoso
            $db->commit();

        } catch (PDOException $e) {
            // 4. Cancelar la transacción en caso de cualquier error SQL
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "Error guardando asistente del curso -> " . $e->getMessage();
            $resultado['clase'] = 'alert alert-danger'; 
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }     

        return $resultado;
    }

    public function borrarAsistenteCurso($idCursosAsistente, $datosAnteriores) {
        // 1. Obtener la conexión PDO
        $db = Database::getConnection();
        $resultado = array();
        
        try {
            // 2. Iniciar la transacción
            $db->beginTransaction();

            if (isset($_SESSION['user_id'])) {
                $idUsuario = $_SESSION['user_id'];
            } else {
                $idUsuario = 49; // tramites-web o ws 
            }

            // 3. Ejecutar la baja lógica en cursosasistente
            $sql = "UPDATE cursosasistente
                    SET Estado = 'N', Borrado = 1, IdUsuario = :idUsuario
                    WHERE Id = :idCursosAsistente";
                    
            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':idUsuario'         => $idUsuario,
                ':idCursosAsistente' => $idCursosAsistente
            ]);

            // 4. Registrar la auditoría en la tabla log_cursos
            $tipoMovimiento = 'borrado';
            $datos = serialize($datosAnteriores);
            
            $sqlLog = "INSERT INTO log_cursos (Tabla, IdTabla, Fecha, TipoMovimiento, IdUsuario, Datos) 
                       VALUES ('cursosasistente', :idCursosAsistente, NOW(), :tipoMovimiento, :idUsuario, :datos)";
                       
            $stmtLog = $db->prepare($sqlLog);
            $stmtLog->execute([
                ':idCursosAsistente' => $idCursosAsistente,
                ':tipoMovimiento'    => $tipoMovimiento,
                ':idUsuario'         => $idUsuario,
                ':datos'             => $datos
            ]);

            // Si llegó hasta aquí sin lanzar excepciones, el flujo es exitoso
            $resultado['estado'] = TRUE;
            $resultado['mensaje'] = 'EL ASISTENTE HA CANCELADO LA INSCRIPCIÓN AL CURSO';
            $resultado['clase'] = 'alert alert-success'; 
            $resultado['icono'] = 'glyphicon glyphicon-ok'; 

            // 5. Confirmar los cambios
            $db->commit();

        } catch (PDOException $e) {
            // 6. Deshacer cambios ante cualquier fallo en la base de datos
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "ERROR AL BORRAR ASISTENTE -> " . $e->getMessage();
            $resultado['clase'] = 'alert alert-danger'; 
            $resultado['icono'] = 'glyphicon glyphicon-remove';
        }     

        return $resultado;
    }

    public function asisteAsistenteCurso($idCursosAsistente, $asiste, $fecha_baja, $observaciones, $datosAnteriores) {
        // 1. Obtener la conexión PDO
        $db = Database::getConnection();
        $resultado = array();
        
        try {
            // 2. Iniciar la transacción
            $db->beginTransaction();

            $idUsuarioBaja = NULL;
            if ($asiste == 'N') {
                $idUsuarioBaja = $_SESSION['user_id']; // Evita warnings si no está definida la sesión
            }

            // 3. Ejecutar la actualización del estado de asistencia
            $sql = "UPDATE cursosasistente 
                    SET Estado = :asiste, FechaBaja = :fechaBaja, IdUsuarioBaja = :idUsuarioBaja, Observaciones = :observaciones
                    WHERE Id = :idCursosAsistente";
                    
            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':asiste'             => $asiste,
                ':fechaBaja'          => $fecha_baja,
                ':idUsuarioBaja'      => $idUsuarioBaja,
                ':observaciones'      => $observaciones,
                ':idCursosAsistente'  => $idCursosAsistente
            ]);

            // 4. Determinar el tipo de movimiento y registrar la auditoría
            $tipoMovimiento = ($asiste == 'S') ? 'asiste' : 'no_asiste';
            $datos = serialize($datosAnteriores);
            $idUsuarioLog = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 49; // Fallback por seguridad para el log
            
            $sqlLog = "INSERT INTO log_cursos (Tabla, IdTabla, Fecha, TipoMovimiento, IdUsuario, Datos) 
                       VALUES ('cursosasistente', :idCursosAsistente, NOW(), :tipoMovimiento, :idUsuario, :datos)";
                       
            $stmtLog = $db->prepare($sqlLog);
            $stmtLog->execute([
                ':idCursosAsistente' => $idCursosAsistente,
                ':tipoMovimiento'    => $tipoMovimiento,
                ':idUsuario'         => $idUsuarioLog,
                ':datos'             => $datos
            ]);

            // Si el flujo llega aquí sin excepciones, la operación fue un éxito
            $resultado['estado'] = TRUE;
            $resultado['mensaje'] = 'EL ASISTENTE HA SIDO MODIFICADO';
            $resultado['clase'] = 'alert alert-success'; 
            $resultado['icono'] = 'glyphicon glyphicon-ok'; 

            // 5. Confirmar los cambios en la base de datos
            $db->commit();

        } catch (PDOException $e) {
            // 6. Deshacer cambios ante cualquier error SQL
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "ERROR AL MODIFICAR ASISTENTE -> " . $e->getMessage();
            $resultado['clase'] = 'alert alert-danger'; 
            $resultado['icono'] = 'glyphicon glyphicon-remove';
        }     

        return $resultado;
    }

    public function obtenerAsistenteParaPlanillaPorId($idAsistente){
        // 1. Obtener la conexión PDO
        $db = Database::getConnection();
        $resultado = array();
        
        // Agregamos alias explícitos en el SELECT para las columnas cuyos nombres originales podrían repetirse
        $sql = "SELECT ca.IdCursos, ca.ApellidoNombre, ca.IdColegiado, ca.Estado, cur.Titulo AS tituloCurso, c.Matricula, 
                       p.NumeroDocumento, pa.Nacionalidad, cdr.Calle, cdr.Lateral, cdr.Numero, cdr.Piso, cdr.Departamento, 
                       l.Nombre AS nombreLocalidad, cc.TelefonoFijo, cc.TelefonoMovil, cc.CorreoElectronico AS mail, 
                       ct.FechaTitulo, tt.Nombre AS nombreProfesion, 
                       (SELECT GROUP_CONCAT(DISTINCT e.Especialidad, ' - ') 
                        FROM colegiadoespecialista ce 
                        INNER JOIN especialidad e ON e.Id = ce.Especialidad 
                        WHERE ce.IdColegiado = c.Id AND ce.Estado = 'A') AS Especialidades
                FROM cursosasistente ca
                INNER JOIN cursos cur ON cur.Id = ca.IdCursos
                LEFT JOIN colegiado c ON c.Id = ca.IdColegiado
                LEFT JOIN persona p ON p.Id = c.IdPersona
                LEFT JOIN paises pa ON pa.Id = p.IdPaises
                LEFT JOIN colegiadodomicilioreal cdr ON cdr.IdColegiado = c.Id AND cdr.IdEstado = 1
                LEFT JOIN localidad l ON l.Id = cdr.IdLocalidad
                LEFT JOIN colegiadocontacto cc ON cc.IdColegiado = c.Id AND cc.IdEstado = 1
                LEFT JOIN colegiadotitulo ct ON ct.IdColegiado = c.Id
                LEFT JOIN tipotitulo tt ON tt.IdTipoTitulo = ct.IdTipoTitulo
                WHERE ca.Id = :idAsistente";
                
        try {
            // 2. Preparar y ejecutar la consulta usando parámetros con nombre
            $stmt = $db->prepare($sql);
            $stmt->execute([':idAsistente' => $idAsistente]);
            
            // 3. Recuperar la fila asociativa
            $rowFetch = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($rowFetch) {
                $datos = array (
                    'idCurso'         => $rowFetch['IdCursos'],
                    'apellidoNombre'  => $rowFetch['ApellidoNombre'],
                    'idColegiado'     => $rowFetch['IdColegiado'],
                    'asiste'          => $rowFetch['Estado'],
                    'tituloCurso'     => $rowFetch['tituloCurso'],
                    'matricula'       => $rowFetch['Matricula'],
                    'numeroDocumento' => $rowFetch['NumeroDocumento'],
                    'nacionalidad'    => $rowFetch['Nacionalidad'],
                    'calle'           => $rowFetch['Calle'],
                    'lateral'         => $rowFetch['Lateral'],
                    'numero'          => $rowFetch['Numero'],
                    'piso'            => $rowFetch['Piso'],
                    'departamento'    => $rowFetch['Departamento'],
                    'nombreLocalidad' => $rowFetch['nombreLocalidad'],
                    'telefonoFijo'    => $rowFetch['TelefonoFijo'],
                    'telefonoMovil'   => $rowFetch['TelefonoMovil'],
                    'mail'            => $rowFetch['mail'],
                    'fechaTitulo'     => $rowFetch['FechaTitulo'],
                    'nombreProfesion' => $rowFetch['nombreProfesion'],
                    'especialidades'  => $rowFetch['Especialidades']
                );
                
                $resultado['estado'] = TRUE;
                $resultado['mensaje'] = "OK";
                $resultado['datos'] = $datos;
                $resultado['clase'] = 'alert alert-success'; 
                $resultado['icono'] = 'glyphicon glyphicon-ok'; 
            } else {
                // Control por si el ID de asistente no existe en la base de datos
                $resultado['estado'] = FALSE;
                $resultado['mensaje'] = "No se encontró el asistente solicitado";
                $resultado['clase'] = 'alert alert-info'; 
                $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
            }
            
        } catch (PDOException $e) {
            // 4. Captura ante fallos estructurales o de conexión
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "Error buscando asistente";
            $resultado['clase'] = 'alert alert-danger'; 
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }

        return $resultado;
    }

    public function obtenerDetalleCobranzaPorPeriodoFechaPago($id_curso, $fecha_pago, $anio_periodo, $mes_periodo, $criterio_liquidacion) {
        try {
            $db = Database::getConnection();
            
            $periodo = $anio_periodo.'-'.$mes_periodo;
            
            // La condición (:id_curso = 0 OR a.IdCursos = :id_curso) permite el "bypass"
            $sql = "SELECT 
                        ac.Id AS idCursosAsistenteCuotas, 
                        a.Id AS idAsistente, 
                        a.ApellidoNombre AS apellidoNombre, 
                        a.IdColegiado AS idColegiado, 
                        ac.Cuota AS cuota,
                        ac.FechaVencimiento AS fechaVencimiento, 
                        ac.Importe AS importe, 
                        if (c.ValorCuotaLiquidacion IS NULL, ac.Importe, c.ValorCuotaLiquidacion) AS importe_base,
                        ac.FechaPago AS fechaPago
                    FROM cursosasistentecuotas ac
                    INNER JOIN cursosasistente a ON a.Id = ac.IdCursosAsistente
                    INNER JOIN cursos c ON c.Id = a.IdCursos
                    WHERE a.IdCursos = :id_curso
                      AND ac.FechaPago <= :fecha_pago
                      AND (:criterio_liquidacion = 'fecha' OR SUBSTR(ac.FechaVencimiento, 1, 7) <= :periodo)
                      AND ac.Borrado = 0
                      AND ac.IdLiquidacionCursos IS NULL
                    ORDER BY a.ApellidoNombre ASC, ac.Cuota ASC";

            $stmt = $db->prepare($sql);
            
            $stmt->bindParam(':id_curso', $id_curso, PDO::PARAM_INT);
            $stmt->bindParam(':fecha_pago', $fecha_pago, PDO::PARAM_STR);
            $stmt->bindParam(':periodo', $periodo, PDO::PARAM_STR);
            $stmt->bindParam(':criterio_liquidacion', $criterio_liquidacion, PDO::PARAM_STR);
            
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (count($rows) > 0) {
                return [
                    'estado' => true,
                    'mensaje' => "OK",
                    'datos' => $rows,
                    'cantidad' => count($rows),
                    'clase' => 'alert alert-success',
                    'icono' => 'glyphicon glyphicon-ok'
                ];
            }

            return [
                'estado' => false,
                'mensaje' => "No se encontraron cuotas para los criterios seleccionados.",
                'datos' => [],
                'cantidad' => 0,
                'clase' => 'alert alert-info',
                'icono' => 'glyphicon glyphicon-exclamation-sign'
            ];

        } catch (PDOException $e) {
            return [
                'estado' => false,
                'mensaje' => "Error buscando detalle: " . $e->getMessage(),
                'clase' => 'alert alert-danger',
                'icono' => 'glyphicon glyphicon-remove'
            ];
        }
    }

    public function obtenerDetalleCobranzaPorCursoPeriodoFechaPago($fecha_cobranza, $anio_periodo, $mes_periodo, $criterio_liquidacion) {
        try {
            $db = Database::getConnection();
            
            $periodo = $anio_periodo.'-'.$mes_periodo;
            
            $sql = "SELECT 
                        c.Id AS idCursos,
                        c.Titulo AS titulo,
                        SUM(ac.Importe) AS importe
                        if (c.ValorCuotaLiquidacion IS NULL, ac.Importe, c.ValorCuotaLiquidacion) AS importe_base,
                    FROM cursosasistentecuotas ac
                    INNER JOIN cursosasistente a ON a.Id = ac.IdCursosAsistente
                    INNER JOIN cursos c ON c.Id = a.IdCursos
                    WHERE ac.FechaPago <= :fecha_cobranza
                      AND (:criterio_liquidacion = 'fecha' OR SUBSTR(ac.FechaVencimiento, 1, 7) = :periodo)
                      AND ac.Borrado = 0
                      AND ac.IdLiquidacionCursos IS NULL
                    GROUP BY a.IdCursos
                    ORDER BY a.IdCursos ASC";

            $stmt = $db->prepare($sql);
            
            $stmt->bindParam(':fecha_cobranza', $fecha_cobranza, PDO::PARAM_STR);
            $stmt->bindParam(':criterio_liquidacion', $criterio_liquidacion, PDO::PARAM_STR);
            $stmt->bindParam(':periodo', $periodo, PDO::PARAM_STR);
            
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (count($rows) > 0) {
                return [
                    'estado' => true,
                    'mensaje' => "OK",
                    'datos' => $rows,
                    'cantidad' => count($rows),
                    'clase' => 'alert alert-success',
                    'icono' => 'glyphicon glyphicon-ok'
                ];
            }

            return [
                'estado' => false,
                'mensaje' => "No se encontraron cuotas para los criterios seleccionados.",
                'datos' => [],
                'cantidad' => 0,
                'clase' => 'alert alert-info',
                'icono' => 'glyphicon glyphicon-exclamation-sign'
            ];

        } catch (PDOException $e) {
            return [
                'estado' => false,
                'mensaje' => "Error buscando detalle: " . $e->getMessage(),
                'clase' => 'alert alert-danger',
                'icono' => 'glyphicon glyphicon-remove'
            ];
        }
    }

    public function obtenerLiquidacionesDocentesPendientePago() {
        try {
            $db = Database::getConnection();
            $sql = "SELECT lc.Id AS IdLiquidacionCursos,
                        lc.IdCursos, 
                        CONCAT('[', c.Id, '] ', c.Titulo) AS Titulo, 
                        lc.FechaCobranza, 
                        lc.TotalCobrado,
                        lc.TotalLiquidacion,
                        lc.TotalLiquidado, 
                        lc.CantidadCuotas
                    FROM liquidacion_cursos lc
                    INNER JOIN cursos c ON c.Id = lc.IdCursos
                    WHERE lc.IdEstadoLiquidacionCursos = 1
                        AND lc.IdLiquidacionCursosDocentes IS NULL";
            $stmt = $db->prepare($sql);
            
            //$stmt->bindParam(':idEstadoLiquidacionCursos', $this::LIQUIDACION_INICIADA, PDO::PARAM_INT);
            
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (count($rows) > 0) {
                return [
                    'estado' => true,
                    'mensaje' => "OK",
                    'datos' => $rows,
                    'cantidad' => count($rows),
                    'clase' => 'alert alert-success',
                    'icono' => 'glyphicon glyphicon-ok'
                ];
            }

            return [
                'estado' => false,
                'mensaje' => "No se encontraron liquidaciones para criterio seleccionado.",
                'datos' => [],
                'cantidad' => 0,
                'clase' => 'alert alert-info',
                'icono' => 'glyphicon glyphicon-exclamation-sign'
            ];

        } catch (PDOException $e) {
            return [
                'estado' => false,
                'mensaje' => "Error buscando liquidaciones: " . $e->getMessage(),
                'clase' => 'alert alert-danger',
                'icono' => 'glyphicon glyphicon-remove'
            ];
        }
    }

    public function obtenerLiquidacionesConSaldoPendientePago() {
        try {
            $db = Database::getConnection();
            $sql = "SELECT lcd.Id,
                            (SELECT GROUP_CONCAT(lc.IdCursos)
                            FROM liquidacion_cursos lc
                            WHERE lc.IdLiquidacionCursosDocentes = lcd.Id) AS IdCursos,
                            lcd.FechaLiquidacion, 
                            lcd.Saldo, 
                            (SELECT GROUP_CONCAT(DISTINCT CONCAT('[', c1.Id, '] ', c1.Titulo) SEPARATOR ' <br> ')
                            FROM cursos c1
                            INNER JOIN liquidacion_cursos lc1 ON lc1.IdCursos = c1.Id
                            WHERE lc1.IdLiquidacionCursosDocentes = lcd.Id) AS Cursos_Liquidados
                    FROM liquidacion_cursos_docentes lcd
                    WHERE lcd.TipoLiquidacion = 'docentes' 
                        AND lcd.Saldo > 0 
                        AND lcd.IdLiquidacionSaldos IS NULL 
                        AND lcd.Borrado = 0";
            $stmt = $db->prepare($sql);
            
            //$stmt->bindParam(':idEstadoLiquidacionCursos', $this::LIQUIDACION_INICIADA, PDO::PARAM_INT);
            
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (count($rows) > 0) {
                return [
                    'estado' => true,
                    'mensaje' => "OK",
                    'datos' => $rows,
                    'cantidad' => count($rows),
                    'clase' => 'alert alert-success',
                    'icono' => 'glyphicon glyphicon-ok'
                ];
            }

            return [
                'estado' => false,
                'mensaje' => "No se encontraron liquidaciones para criterio seleccionado.",
                'datos' => [],
                'cantidad' => 0,
                'clase' => 'alert alert-info',
                'icono' => 'glyphicon glyphicon-exclamation-sign'
            ];

        } catch (PDOException $e) {
            return [
                'estado' => false,
                'mensaje' => "Error buscando liquidaciones: " . $e->getMessage(),
                'clase' => 'alert alert-danger',
                'icono' => 'glyphicon glyphicon-remove'
            ];
        }
    }

    public function obtenerLiquidacionesCursosPendientePago($criterio_liquidacion) {
        try {
            $db = Database::getConnection();
            
            $sql = "SELECT 
                        c.Id AS idCursos,
                        c.Titulo AS titulo,
                        SUM(ac.Importe) AS importe
                    FROM cursosasistentecuotas ac
                    INNER JOIN cursosasistente a ON a.Id = ac.IdCursosAsistente
                    INNER JOIN cursos c ON c.Id = a.IdCursos
                    WHERE ac.FechaPago <= :fecha_cobranza
                      AND (:criterio_liquidacion = 'fecha' OR SUBSTR(ac.FechaVencimiento, 1, 7) = :periodo)
                      AND ac.Borrado = 0
                      AND ac.IdLiquidacionCursos IS NULL
                    GROUP BY a.IdCursos
                    ORDER BY a.IdCursos ASC";

            $stmt = $db->prepare($sql);
            
            $stmt->bindParam(':fecha_cobranza', $fecha_cobranza, PDO::PARAM_STR);
            $stmt->bindParam(':criterio_liquidacion', $criterio_liquidacion, PDO::PARAM_STR);
            $stmt->bindParam(':periodo', $periodo, PDO::PARAM_STR);
            
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (count($rows) > 0) {
                return [
                    'estado' => true,
                    'mensaje' => "OK",
                    'datos' => $rows,
                    'cantidad' => count($rows),
                    'clase' => 'alert alert-success',
                    'icono' => 'glyphicon glyphicon-ok'
                ];
            }

            return [
                'estado' => false,
                'mensaje' => "No se encontraron cuotas para los criterios seleccionados.",
                'datos' => [],
                'cantidad' => 0,
                'clase' => 'alert alert-info',
                'icono' => 'glyphicon glyphicon-exclamation-sign'
            ];

        } catch (PDOException $e) {
            return [
                'estado' => false,
                'mensaje' => "Error buscando detalle: " . $e->getMessage(),
                'clase' => 'alert alert-danger',
                'icono' => 'glyphicon glyphicon-remove'
            ];
        }
    }

    public function obtenerLiquidacionPorId($id) {
        $db = Database::getConnection();
        $sql = "SELECT lc.*, c.Titulo AS NombreCurso, c.ValorCuotaLiquidacion, c.PorcentajeRetencionColegio 
                FROM liquidacion_cursos lc 
                INNER JOIN cursos c ON c.Id = lc.IdCursos 
                WHERE lc.Id = :id";
        $stmt = $db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return ['estado' => (bool)$res, 'datos' => $res, 'mensaje' => 'No encontrada'];
    }

    public function obtenerDetalleLiquidacion($id) {
        $db = Database::getConnection();
        $sql = "SELECT ac.Importe AS importe, ac.Cuota AS cuota, ac.FechaPago AS fechaPago, 
                       a.ApellidoNombre AS apellidoNombre
                FROM cursosasistentecuotas ac
                INNER JOIN cursosasistente a ON a.Id = ac.IdCursosAsistente
                WHERE ac.IdLiquidacionCursos = :id
                ORDER BY ac.Cuota ASC, a.ApellidoNombre ASC"; // Orden clave
        $stmt = $db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return ['estado' => true, 'datos' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
    }

    public function obtenerLiquidacionDocentesPorId($id) {
        $db = Database::getConnection();
        $sql = "SELECT lc.*, c.Titulo AS NombreCurso 
                FROM liquidacion_cursos lc 
                INNER JOIN cursos c ON c.Id = lc.IdCursos 
                WHERE lc.Id = :id";
        $stmt = $db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return ['estado' => (bool)$res, 'datos' => $res, 'mensaje' => 'No encontrada'];
    }

    public function obtenerDetalleLiquidacionDocente($id) {
        $db = Database::getConnection();
        $sql = "SELECT ac.Importe AS importe, ac.Cuota AS cuota, ac.FechaPago AS fechaPago, 
                       a.ApellidoNombre AS apellidoNombre
                FROM cursosasistentecuotas ac
                INNER JOIN cursosasistente a ON a.Id = ac.IdCursosAsistente
                WHERE ac.IdLiquidacionCursos = :id
                ORDER BY ac.Cuota ASC, a.ApellidoNombre ASC"; // Orden clave
        $stmt = $db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return ['estado' => true, 'datos' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
    }

    public function obtenerLiquidacionesGlobales($anio, $mes, $idAsistente, $cursoElegido) {
        try {
            $db = Database::getConnection();
            
            $sql = "SELECT DISTINCT lc.id AS IdLiquidacionCursos, lc.IdCursos, c.Titulo AS NombreCurso, 
                           lc.PeriodoLiquidacion, lc.FechaLiquidacion, lc.TotalCobrado, lc.TotalLiquidacion, lc.TotalLiquidado, lc.FechaCobranza, lc.CantidadCuotas, e.Nombre AS EstadoLiquidacionCuotas, lc.IdLiquidacionCursosDocentes
                    FROM liquidacion_cursos lc
                    INNER JOIN cursos c ON c.Id = lc.IdCursos
                    INNER JOIN estado_liquidacion_cursos e ON e.Id = lc.IdEstadoLiquidacionCursos
                    INNER JOIN cursosasistente ca ON c.Id = ca.IdCursos
                    WHERE lc.Borrado = 0";

            // Filtro por Asistente (Obligatorio opcional)
            if (!empty($idAsistente)) {
                $sql .= " AND ca.Id = :idAsistente";
                $filtrar = 'PARA EL ASISTENTE';
            } else {
                if (!empty($cursoElegido) && $cursoElegido <> "TODOS") {
                    $sql .= " AND c.Id = :idCurso";
                    $filtrar = 'PARA EL CURSO';
                } else {
                    // Filtro por Periodo (Solo si se envían ambos)
                    if (!empty($anio) && !empty($mes)) {
                        $periodoBusqueda = $anio . str_pad($mes, 2, "0", STR_PAD_LEFT);
                        $sql .= " AND lc.PeriodoLiquidacion = :periodo";
                        $filtrar = 'PARA EL PERIODO '.$periodoBusqueda;
                    }
                }
            }

            $stmt = $db->prepare($sql);

            if (!empty($idAsistente)) {
                $stmt->bindParam(':idAsistente', $idAsistente, PDO::PARAM_INT);
            } else {
                if (!empty($cursoElegido) && $cursoElegido <> "TODOS") {
                    $stmt->bindParam(':idCurso', $cursoElegido, PDO::PARAM_INT);
                } else {
                    if (!empty($anio) && !empty($mes)) {
                        $stmt->bindParam(':periodo', $periodoBusqueda, PDO::PARAM_INT);
                    }
                }
            }

            $stmt->execute();
            $rows = $stmt->fetchAll();

            return [
                'estado' => count($rows) > 0,
                'datos' => $rows,
                'mensaje' => count($rows) > 0 ? "OK" : "No hay registros. ".$filtrar,
                'clase' => count($rows) > 0 ? 'alert alert-success' : 'alert alert-warning',
                'icono' => count($rows) > 0 ? 'glyphicon glyphicon-ok' : 'glyphicon-exclamation-sign'
            ];

        } catch (PDOException $e) {
            return ['estado' => false, 'mensaje' => $e->getMessage(), 'clase' => 'alert alert-danger'];
        }
    }

    public function obtenerLiquidacionesDocentesGlobales($anio, $id_docente, $cursoElegido) {
        try {
            $db = Database::getConnection();
            
            $sql = "SELECT DISTINCT 
                        lcd.Id, 
                        lcd.TipoLiquidacion, 
                        lcd.FechaLiquidacion,
                        if (lcd.TipoLiquidacion = 'docentes', 
                            (SELECT GROUP_CONCAT(DISTINCT CONCAT('[', c1.Id, '] ', c1.Titulo) SEPARATOR ' <br> ') 
                             FROM cursos c1 
                             INNER JOIN liquidacion_cursos lc1 ON lc1.IdCursos = c1.Id 
                             WHERE lc1.IdLiquidacionCursosDocentes = lcd.Id),
                                     (SELECT GROUP_CONCAT(DISTINCT CONCAT('[', c1.Id, '] ', c1.Titulo) SEPARATOR ' <br> ') 
                             FROM cursos c1 
                             INNER JOIN liquidacion_cursos lc1 ON lc1.IdCursos = c1.Id 
                             INNER JOIN liquidacion_cursos_docentes lcd1 ON lcd1.Id = lc1.IdLiquidacionCursosDocentes
                             WHERE lcd1.IdLiquidacionSaldos = lcd.Id)
                        ) AS Cursos_Liquidados, 
                        lcd.TotalLiquidado, 
                        lcd.TotalAbonado, 
                        lcd.Saldo, 
                        lcd.IdEstadoLiquidacionCursosDocentes,
                        eld.Nombre AS EstadoLiquidacionDocentes,
                        lcd.IdLiquidacionSaldos,
                        (SELECT GROUP_CONCAT(CONCAT(dc.ApellidoNombres, ':', lcdd.TotalLiquidado) SEPARATOR '||')
                         FROM liquidacion_cursos_docentes_detalle lcdd
                         INNER JOIN docente_cursos dc ON lcdd.IdDocenteCursos = dc.Id
                         WHERE lcdd.IdLiquidacionCursosDocentes = lcd.Id) AS Detalle_Docentes
                    FROM liquidacion_cursos_docentes lcd
                    INNER JOIN estado_liquidacion_cursos_docentes eld ON eld.Id = lcd.IdEstadoLiquidacionCursosDocentes
                    LEFT JOIN liquidacion_cursos lc ON lc.IdLiquidacionCursosDocentes = lcd.Id
                    WHERE lcd.Borrado = 0";

            // Filtro por Asistente (Obligatorio opcional)
            if (!empty($id_docente)) {
                $sql .= " AND lcd.Id = :id_docente";
                $filtrar = 'PARA EL DOCENTE';
            } else {
                if (!empty($cursoElegido) && $cursoElegido <> "TODOS") {
                    $sql .= " AND lc.IdCursos = :idCurso";
                    $filtrar = 'PARA EL CURSO';
                } else {
                    // Filtro por Periodo (Solo si se envían ambos)
                    if (!empty($anio)) {
                        $periodoBusqueda = $anio;
                        $sql .= " AND SUBSTR(lcd.FechaLiquidacion, 1, 4) = :periodo";
                        $filtrar = 'PARA EL PERIODO '.$periodoBusqueda;
                    }
                }
            }
            $sql .= " ORDER BY lcd.Id DESC";

            $stmt = $db->prepare($sql);

            if (!empty($id_docente)) {
                $stmt->bindParam(':id_docente', $id_docente, PDO::PARAM_INT);
            } else {
                if (!empty($cursoElegido) && $cursoElegido <> "TODOS") {
                    $stmt->bindParam(':idCurso', $cursoElegido, PDO::PARAM_INT);
                } else {
                    if (!empty($anio)) {
                        $stmt->bindParam(':periodo', $periodoBusqueda, PDO::PARAM_INT);
                    }
                }
            }

            $stmt->execute();
            $rows = $stmt->fetchAll();
            
            return [
                'estado' => count($rows) > 0,
                'datos' => $rows,
                'mensaje' => count($rows) > 0 ? "OK" : "No hay registros. ".$filtrar,
                'clase' => count($rows) > 0 ? 'alert alert-success' : 'alert alert-warning',
                'icono' => count($rows) > 0 ? 'glyphicon glyphicon-ok' : 'glyphicon-exclamation-sign'
            ];

        } catch (PDOException $e) {
            return ['estado' => false, 'mensaje' => $e->getMessage(), 'clase' => 'alert alert-danger'];
        }
    }

    public function generarLiquidacion($id_curso, $fecha_cobranza, $periodo_liquidacion, $cuotasIds, $monto_total, $monto_base_total, $monto_liquidar_total, $criterio_liquidacion) {
        $db = Database::getConnection();
        
        try {
            // 1. Iniciar Transacción
            $db->beginTransaction();

            // 2. INSERT en la cabecera (liquidacion_cursos)
            $sqlInsert = "INSERT INTO liquidacion_cursos 
                          (IdCursos, CriterioLiquidacion, PeriodoLiquidacion, TotalCobrado, TotalLiquidacion, TotalLiquidado, CantidadCuotas, FechaLiquidacion, FechaCobranza, IdEstadoLiquidacionCursos, IdUsuario, Borrado) 
                          VALUES 
                          (:idCurso, :criterioLiquidacion, :periodo, :monto_total, :monto_base_total, :monto_liquidar_total, :cantidad, NOW(), :fechaCobranza, 1, :idUsuario, 0)";
            
            $stmtInsert = $db->prepare($sqlInsert);
            $cantidadCuotas = count($cuotasIds);
            
            $stmtInsert->bindParam(':idCurso', $id_curso, PDO::PARAM_INT);
            $stmtInsert->bindParam(':criterioLiquidacion', $criterio_liquidacion, PDO::PARAM_STR);
            $stmtInsert->bindParam(':periodo', $periodo_liquidacion, PDO::PARAM_STR);
            $stmtInsert->bindParam(':fechaCobranza', $fecha_cobranza, PDO::PARAM_STR);
            $stmtInsert->bindParam(':monto_total', $monto_total);
            $stmtInsert->bindParam(':monto_base_total', $monto_base_total);
            $stmtInsert->bindParam(':monto_liquidar_total', $monto_liquidar_total);
            $stmtInsert->bindParam(':cantidad', $cantidadCuotas, PDO::PARAM_INT);
            $stmtInsert->bindParam(':idUsuario', $_SESSION['user_id'], PDO::PARAM_INT);
            
            $stmtInsert->execute();
            
            // Obtenemos el ID de la liquidación recién creada
            $idLiquidacion = $db->lastInsertId();

            // 3. UPDATE masivo en las cuotas (cursosasistentecuotas)
            // Creamos los placeholders (?,?,?) para el array de IDs
            $placeholders = implode(',', array_fill(0, count($cuotasIds), '?'));
            
            $sqlUpdate = "UPDATE cursosasistentecuotas 
                          SET IdLiquidacionCursos = ? 
                          WHERE Id IN ($placeholders)";
            
            $stmtUpdate = $db->prepare($sqlUpdate);
            
            // Ejecutamos pasando el ID de liquidación primero, luego el array de IDs de cuotas
            $params = array_merge([$idLiquidacion], $cuotasIds);
            $stmtUpdate->execute($params);

            // 4. Si todo salió bien, confirmamos (COMMIT)
            $db->commit();

            $mensaje = "Liquidación generada con éxito. Lote N°: " . $idLiquidacion;
            $clase = "alert alert-success";
            return [
                'estado' => true,
                'mensaje' => "Liquidación generada con éxito. Lote N°: " . $idLiquidacion,
                'clase' => 'alert alert-success',
                'icono' => 'glyphicon glyphicon-ok'
            ];

        } catch (Exception $e) {
            // Si algo falla, deshacemos todo (ROLLBACK)
            $db->rollBack();
            return ['estado' => false, 'mensaje' => $e->getMessage(), 'clase' => 'alert alert-danger'];
        }
    }

    public function anularLiquidacion($id_liquidacion, $datosAnteriores) {
        $db = Database::getConnection();
        
        try {
            // 1. Iniciar Transacción
            $db->beginTransaction();

            // 2. INSERT en la cabecera (liquidacion_cursos)
            $sqlUpdLiquidacion = "UPDATE liquidacion_cursos lc
                                INNER JOIN cursosasistentecuotas ac ON ac.IdLiquidacionCursos = lc.id
                                SET lc.Borrado = 1, ac.IdLiquidacionCursos = NULL
                                WHERE lc.Id = :id_liquidacion";
            
            $stmtInsert = $db->prepare($sqlUpdLiquidacion);
            $stmtInsert->bindParam(':id_liquidacion', $id_liquidacion, PDO::PARAM_INT);
            $stmtInsert->execute();
            
            //guardamos el log_cursos de la anulacion
            $tipoMovimiento = 'modificacion';
            $datos = serialize($datosAnteriores);
            $sqlLog = "INSERT INTO log_cursos (Tabla, IdTabla, Fecha, TipoMovimiento, IdUsuario, Datos) 
                    VALUES ('liquidacion_cursos', :id_liquidacion, NOW(), 'anular', :idUsuario, :datos)";
            $stmtLog = $db->prepare($sqlLog);
            $stmtLog->bindParam(':id_liquidacion', $id_liquidacion, PDO::PARAM_INT);
            $stmtLog->bindParam(':idUsuario', $_SESSION['user_id'], PDO::PARAM_INT);
            $stmtLog->bindParam(':datos', $datos, PDO::PARAM_STR);
            $stmtLog->execute();

            $db->commit();

            return [
                'estado' => true,
                'mensaje' => "Liquidación generada con éxito. Lote N°: " . $id_liquidacion,
                'clase' => 'alert alert-success',
                'icono' => 'glyphicon glyphicon-ok'
            ];

        } catch (Exception $e) {
            // Si algo falla, deshacemos todo (ROLLBACK)
            $db->rollBack();
            return ['estado' => false, 'mensaje' => $e->getMessage(), 'clase' => 'alert alert-danger'];
        }
    }

    public function anularLiquidacionCursosDocentes($id_liquidacion_cursos_docentes) {
        $db = Database::getConnection();
        
        try {
            // 1. Iniciar Transacción
            $db->beginTransaction();

            $id_usuario = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 1;
            $id_estado_liquidacion_cursos = $this::LIQUIDACION_INICIADA;
            // 2. INSERT en la cabecera (liquidacion_cursos)
            $sqlUpdLiquidacion = "UPDATE liquidacion_cursos_docentes lcd
                                INNER JOIN liquidacion_cursos lc ON lc.IdLiquidacionCursosDocentes = lcd.Id
                                INNER JOIN liquidacion_cursos_docentes_detalle lcdd ON lcdd.IdLiquidacionCursosDocentes = lcd.Id
                                SET lcd.Borrado = 1, 
                                    lcd.IdUsuarioBorrado = :id_usuario,
                                    lcd.FechaBorrado = NOW(),
                                    lc.IdEstadoLiquidacionCursos = :id_estado_liquidacion_cursos,
                                    lc.IdLiquidacionCursosDocentes = NULL,
                                    lcdd.Borrado = 1
                                WHERE lcd.Id = :id_liquidacion_cursos_docentes";
            
            $stmtInsert = $db->prepare($sqlUpdLiquidacion);
            $stmtInsert->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $stmtInsert->bindParam(':id_estado_liquidacion_cursos', $id_liquidacion_cursos_docentes, PDO::PARAM_INT);
            $stmtInsert->bindParam(':id_liquidacion_cursos_docentes', $id_liquidacion_cursos_docentes, PDO::PARAM_INT);
            $stmtInsert->execute();
            
            $db->commit();

            return [
                'estado' => true,
                'mensaje' => "Liquidación anulada con éxito. Lote N°: " . $id_liquidacion_cursos_docentes,
                'clase' => 'alert alert-success',
                'icono' => 'glyphicon glyphicon-ok'
            ];

        } catch (Exception $e) {
            // Si algo falla, deshacemos todo (ROLLBACK)
            $db->rollBack();
            return ['estado' => false, 'mensaje' => $e->getMessage(), 'clase' => 'alert alert-danger'];
        }
    }

    public function anularLiquidacionCursosSaldos($id_liquidacion_cursos_saldos) {
        $db = Database::getConnection();
        
        try {
            $db->beginTransaction();

            $id_usuario = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 1;
            $id_estado_liquidacion_cursos_docentes = $this::LIQUIDACION_DOCENTES_FINALIZADA;
            $sqlUpdLiquidacion = "UPDATE liquidacion_cursos_docentes lcd
                                INNER JOIN liquidacion_cursos_docentes_detalle lcdd ON lcdd.IdLiquidacionCursosDocentes = lcd.Id
                                INNER JOIN liquidacion_cursos_docentes lcd1 ON lcd1.IdLiquidacionSaldos = lcd.Id
                                SET lcd.Borrado = 1, 
                                    lcd.IdUsuarioBorrado = :id_usuario,
                                    lcd.FechaBorrado = NOW(),
                                    lcdd.Borrado = 1,
                                    lcd1.IdEstadoLiquidacionCursosDocentes = :id_estado_liquidacion_cursos_docentes,
                                    lcd1.IdLiquidacionSaldos = NULL
                                WHERE lcd.Id = :id_liquidacion_cursos_saldos";
            
            $stmtInsert = $db->prepare($sqlUpdLiquidacion);
            $stmtInsert->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $stmtInsert->bindParam(':id_estado_liquidacion_cursos_docentes', $id_estado_liquidacion_cursos_docentes, PDO::PARAM_INT);            
            $stmtInsert->bindParam(':id_liquidacion_cursos_saldos', $id_liquidacion_cursos_saldos, PDO::PARAM_INT);
            $stmtInsert->execute();
            
            $db->commit();

            return [
                'estado' => true,
                'mensaje' => "Liquidación anulada con éxito. Lote N°: " . $id_liquidacion_cursos_saldos,
                'clase' => 'alert alert-success',
                'icono' => 'glyphicon glyphicon-ok'
            ];

        } catch (Exception $e) {
            // Si algo falla, deshacemos todo (ROLLBACK)
            $db->rollBack();
            return ['estado' => false, 'mensaje' => $e->getMessage(), 'clase' => 'alert alert-danger'];
        }
    }

    public function generarLiquidacionDocentes($criterio_liquidacion, $monto_total_cursos, $monto_liquidar, $cursos_seleccionados, $docentes_liquidados, $montos_docentes) {
        try {
            $db = Database::getConnection();
            $db->exec("SET NAMES 'utf8'");
            
            // 2. Iniciar la transacción general [stmt]
            $db->beginTransaction();

            // =========================================================================
            // PASO A: INSERTAR EN LA TABLA MAESTRA (liquidacion_cursos_docentes)
            // =========================================================================
            // Calculamos los campos financieros iniciales basados en lo que se va a pagar hoy
            $totalLiquidado = $monto_total_cursos; // El valor original de los cursos
            $totalAbonado   = $monto_liquidar;     // Lo que efectivamente se le asignó a los docentes
            $saldo          = $totalLiquidado - $totalAbonado; // El remanente pendiente

            if ($criterio_liquidacion == 'docentes') {
                $idEstadoLiquidacionCursosDocentes = $this::LIQUIDACION_DOCENTES_FINALIZADA;
            } else {
                $idEstadoLiquidacionCursosDocentes = $this::LIQUIDACION_DOCENTES_SALDOS;
            }

            $idUsuario = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 1;

            $sqlMaestro = "INSERT INTO liquidacion_cursos_docentes 
                           (TipoLiquidacion, FechaLiquidacion, TotalLiquidado, TotalAbonado, Saldo, IdUsuario, FechaCarga, IdEstadoLiquidacionCursosDocentes, Borrado) 
                           VALUES (?, NOW(), ?, ?, ?, ?, NOW(), ?, 0)";
            
            $stmtMaestro = $db->prepare($sqlMaestro); 
            $stmtMaestro->execute([
                $criterio_liquidacion,
                $totalLiquidado,
                $totalAbonado,
                $saldo,
                $idUsuario,
                $idEstadoLiquidacionCursosDocentes
            ]); 

            // CAPTURAR EL ID PADRE RECIÉN GENERADO [stmt]
            $idMaestroDocente = $db->lastInsertId(); 


            // =========================================================================
            // PASO B: INSERTAR DESGLOSE DE DOCENTES (liquidacion_cursos_docentes_detail)
            // =========================================================================
            $sqlDetalle = "INSERT INTO liquidacion_cursos_docentes_detalle 
                           (IdLiquidacionCursosDocentes, IdDocenteCursos, TotalLiquidado) 
                           VALUES (?, ?, ?)";
            
            $stmtDetalle = $db->prepare($sqlDetalle); 
            $insercionesDocentes = 0;

            foreach ($docentes_liquidados as $idDocente) {
                $montoIndividual = isset($montos_docentes[$idDocente]) ? floatval($montos_docentes[$idDocente]) : 0.0;

                if ($montoIndividual <= 0) {
                    continue; // Saltamos docentes sin dinero asignado
                }

                $stmtDetalle->execute([
                    $idMaestroDocente, // Vinculamos al padre creado en el Paso A
                    $idDocente,
                    $montoIndividual
                ]); 
                $insercionesDocentes++;
            }


            // =========================================================================
            // PASO C: ACTUALIZAR LA RELACIÓN EN LOS CURSOS PROCESADOS (liquidacion_cursos)
            // =========================================================================
            // Preparamos el UPDATE para estampar el ID Padre en cada curso seleccionado
            $sqlUpdateCurso = "UPDATE liquidacion_cursos 
                               SET IdLiquidacionCursosDocentes = ?, IdEstadoLiquidacionCursos = ?
                               WHERE Id = ?";
            
            $stmtUpdateCurso = $db->prepare($sqlUpdateCurso); 

            foreach ($cursos_seleccionados as $idLiquidacionCurso) {
                $stmtUpdateCurso->execute([
                    $idMaestroDocente,   // El ID de la tabla maestra
                    $this::LIQUIDACION_FINALIZADA,
                    $idLiquidacionCurso  // El ID del registro de la lista
                ]); 
            }


            // =========================================================================
            // CONFIRMACIÓN DE LA TRANSACCIÓN
            // =========================================================================
            if ($insercionesDocentes > 0) {
                // Si todo se ejecutó perfectamente sin errores, guardamos los datos físicamente [stmt]
                $db->commit(); 

                $resultado['estado']  = true;
                $resultado['mensaje'] = "Liquidación procesada con éxito. Se generó el comprobante Maestro ID: {$idMaestroDocente} y se actualizaron los cursos.";
                $resultado['clase']   = 'alert alert-success';
                $resultado['icono']   = 'glyphicon glyphicon-ok';
            } else {
                // Si no se asignó dinero a ningún docente, cancelamos todo para no dejar un registro maestro vacío [stmt, stmt]
                $db->rollBack(); 
                $resultado['mensaje'] = "No se pudo procesar la liquidación porque todos los montos de los docentes estaban en $0.00.";
            }

        } catch (PDOException $e) {
            // En caso de cualquier fallo en el PASO A, B o C, el rollBack borra TODO de la base de datos [stmt, stmt]
            if (isset($db) && $db->inTransaction()) {
                $db->rollBack(); 
            }
            
            $resultado['estado']  = false;
            $resultado['mensaje'] = "Error crítico de base de datos: " . $e->getMessage();
        }

        return $resultado;
    }

    public function generarLiquidacionSaldos($monto_liquidar, $liquidaciones_seleccionadas, $docentes_liquidados, $montos_docentes) {
        try {
            $db = Database::getConnection();
            $db->exec("SET NAMES 'utf8'");
            
            // 2. Iniciar la transacción general [stmt]
            $db->beginTransaction();

            // =========================================================================
            // PASO A: INSERTAR EN LA TABLA MAESTRA (liquidacion_cursos_docentes)
            // =========================================================================
            // Calculamos los campos financieros iniciales basados en lo que se va a pagar hoy
            $totalLiquidado = $monto_liquidar;
            $totalAbonado   = $monto_liquidar;
            $saldo          = 0;

            $criterio_liquidacion = 'saldos';
            $idEstadoLiquidacionCursosDocentes = $this::LIQUIDACION_DOCENTES_SALDOS;

            $idUsuario = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 1;

            $sqlMaestro = "INSERT INTO liquidacion_cursos_docentes 
                           (TipoLiquidacion, FechaLiquidacion, TotalLiquidado, TotalAbonado, Saldo, IdUsuario, FechaCarga, IdEstadoLiquidacionCursosDocentes, Borrado) 
                           VALUES (?, NOW(), ?, ?, ?, ?, NOW(), ?, 0)";
            
            $stmtMaestro = $db->prepare($sqlMaestro); 
            $stmtMaestro->execute([
                $criterio_liquidacion,
                $totalLiquidado,
                $totalAbonado,
                $saldo,
                $idUsuario,
                $idEstadoLiquidacionCursosDocentes
            ]); 

            // CAPTURAR EL ID PADRE RECIÉN GENERADO [stmt]
            $idMaestroDocente = $db->lastInsertId(); 


            // =========================================================================
            // PASO B: INSERTAR DESGLOSE DE DOCENTES (liquidacion_cursos_docentes_detail)
            // =========================================================================
            $sqlDetalle = "INSERT INTO liquidacion_cursos_docentes_detalle 
                           (IdLiquidacionCursosDocentes, IdDocenteCursos, TotalLiquidado) 
                           VALUES (?, ?, ?)";
            
            $stmtDetalle = $db->prepare($sqlDetalle); 
            $insercionesDocentes = 0;

            foreach ($docentes_liquidados as $idDocente) {
                $montoIndividual = isset($montos_docentes[$idDocente]) ? floatval($montos_docentes[$idDocente]) : 0.0;

                if ($montoIndividual <= 0) {
                    continue; // Saltamos docentes sin dinero asignado
                }

                $stmtDetalle->execute([
                    $idMaestroDocente, // Vinculamos al padre creado en el Paso A
                    $idDocente,
                    $montoIndividual
                ]); 
                $insercionesDocentes++;
            }


            // =========================================================================
            // PASO C: ACTUALIZAR LA RELACIÓN EN LOS CURSOS PROCESADOS (liquidacion_cursos)
            // =========================================================================
            // Preparamos el UPDATE para estampar el ID Padre en cada curso seleccionado
            $sqlUpdateCurso = "UPDATE liquidacion_cursos_docentes 
                               SET IdLiquidacionSaldos = ?, IdEstadoLiquidacionCursosDocentes = ?
                               WHERE Id = ?";
            
            $stmtUpdateCurso = $db->prepare($sqlUpdateCurso); 

            foreach ($liquidaciones_seleccionadas as $idLiquidacionCurso) {
                $stmtUpdateCurso->execute([
                    $idMaestroDocente,   // El ID de la tabla maestra
                    $this::LIQUIDACION_DOCENTES_SALDOS,
                    $idLiquidacionCurso  // El ID del registro de la lista
                ]); 
            }


            // =========================================================================
            // CONFIRMACIÓN DE LA TRANSACCIÓN
            // =========================================================================
            if ($insercionesDocentes > 0) {
                // Si todo se ejecutó perfectamente sin errores, guardamos los datos físicamente [stmt]
                $db->commit(); 

                $resultado['estado']  = true;
                $resultado['mensaje'] = "Liquidación procesada con éxito. Se generó el comprobante Maestro ID: {$idMaestroDocente} y se actualizaron los cursos.";
                $resultado['clase']   = 'alert alert-success';
                $resultado['icono']   = 'glyphicon glyphicon-ok';
            } else {
                // Si no se asignó dinero a ningún docente, cancelamos todo para no dejar un registro maestro vacío [stmt, stmt]
                $db->rollBack(); 
                $resultado['mensaje'] = "No se pudo procesar la liquidación porque todos los montos de los docentes estaban en $0.00.";
            }

        } catch (PDOException $e) {
            // En caso de cualquier fallo en el PASO A, B o C, el rollBack borra TODO de la base de datos [stmt, stmt]
            if (isset($db) && $db->inTransaction()) {
                $db->rollBack(); 
            }
            
            $resultado['estado']  = false;
            $resultado['mensaje'] = "Error crítico de base de datos: " . $e->getMessage();
        }

        return $resultado;
    }

    public function imprimirChequeraAsistenteCurso($idCursosAsistente, $cursos_pdo, $asistente, $pdf, $pathOrigen) {
        require_once($pathOrigen.'dataAccess/funcionesPhp.php');
        require_once($pathOrigen.'dataAccess/colegiadoDeudaAnualLogic.php');
        
        // Style unificado para el código de barras
        $styleCB = array(
            'position' => '',
            'align' => 'C',
            'stretch' => false,
            'fitwidth' => true,
            'cellfitalign' => '',
            'border' => false,
            'hpadding' => 'auto',
            'vpadding' => 'auto',
            'fgcolor' => array(0,0,0),
            'bgcolor' => false,
            'text' => true,
            'font' => 'helvetica',
            'fontsize' => 8,
            'stretchtext' => 4
        );

        $tipoPdf = 'F';
        $apellidoNombre = $asistente['apellidoNombre'];
        $idColegiado = $asistente['idColegiado'];
        $matricula = $asistente['matricula'];
        $idCurso = $asistente['idCurso'];
        $esColegiado = (!empty($idColegiado)) ? "S" : "N";
        $titulo = $asistente['tituloCurso'];
        
        $generoChequera = FALSE;
        $resultado = array();

        // Invoca la lógica PDO mapeada en los pasos previos
        $resCuotas = $cursos_pdo->obtenerCuotasPorAsistente($idCursosAsistente);
        
        if ($resCuotas['estado'] && $resCuotas['cuotasAdeudadas'] > 0) {
            $cuotasImpresas = 0; // CORRECCIÓN: Inicialización obligatoria para evitar fallos en PHP 8+
            $posLinea = 0;

            foreach ($resCuotas['datos'] as $dato) {
                $idCursosAsistenteCuota = $dato['idCursosAsistenteCuota'];
                $cuota = $dato['cuota'];
                $importe = $dato['importe'];
                $fechaVencimiento = $dato['fechaVencimiento'];
                $cuotaAbonada = $dato['abonada'];
                $detalleCuota = $cuota.'-'.substr($fechaVencimiento, 0, 4).' ('.trim($dato['detalleCuota']).')';

                if ($cuotaAbonada) { 
                    continue; 
                }
                
                if (!$generoChequera) {
                    $pdf->SetFont('dejavusans', '', 8);
                    $pdf->AddPage();
                    $posLinea = 0;
                    $generoChequera = TRUE;
                }
                
                $posLinea += 40;
                
                // Si la cuota venció, se patea el vencimiento al último día del mes corriente
                if ($fechaVencimiento < date('Y-m-d')) {
                    $fechaVencimiento = ultmioDiaDelMes(date('Y-m-d'));
                }
                
                // Renderizado de las secciones del PDF (Talón izquierdo de control)
                $pdf->Image($pathOrigen.'public/images/logoChequera.png' , 5, $posLinea-35, 80 , 10,'PNG');                                
                $pdf->SetXY(3, $posLinea-25);
                $pdf->SetFont('dejavusans', 'B', 9);
                $pdf->MultiCell(0, 5, $apellidoNombre, 0, 'L', false, 1, '', '', true);
                $pdf->SetXY(3, $posLinea-20);
                $pdf->SetFont('dejavusans', '', 9);
                $pdf->MultiCell(0, 5, 'Asistente: ', 0, 'L', false, 1, '', '', true);
                $pdf->SetXY(25, $posLinea-20);
                $pdf->SetFont('dejavusans', 'B', 9);
                $pdf->MultiCell(0, 5, $idCursosAsistente, 0, 'L', false, 1, '', '', true);
                $pdf->SetXY(3, $posLinea-15);
                $pdf->SetFont('dejavusans', '', 9);
                $pdf->MultiCell(0, 5, 'Recibo ', 0, 'L', false, 1, '', '', true);
                $pdf->SetXY(25, $posLinea-15);
                $pdf->SetFont('dejavusans', 'B', 9);
                $pdf->MultiCell(0, 5, $idCursosAsistenteCuota, 0, 'L', false, 1, '', '', true);
                $pdf->SetXY(3, $posLinea-10);
                $pdf->SetFont('dejavusans', '', 9);
                $pdf->MultiCell(0, 5, 'Vencimiento: ', 0, 'L', false, 1, '', '', true);
                $pdf->SetXY(25, $posLinea-10);
                $pdf->SetFont('dejavusans', 'B', 9);
                $pdf->MultiCell(0, 5, cambiarFechaFormatoParaMostrar($fechaVencimiento), 0, 'L', false, 1, '', '', true);
                $pdf->SetXY(3, $posLinea-5);
                $pdf->SetFont('dejavusans', '', 9);
                $pdf->MultiCell(0, 5, 'Importe: ', 0, 'L', false, 1, '', '', true);
                $pdf->SetXY(25, $posLinea-5);
                $pdf->SetFont('dejavusans', 'B', 9);
                $pdf->MultiCell(0, 5, '$'.number_format($importe, 2, ',', ''), 0, 'L', false, 1, '', '', true);
                
                // Cuerpo principal (Talón derecho de pago)
                $pdf->SetXY(90, $posLinea-35);
                $pdf->SetFont('dejavusans', 'B', 9);
                $pdf->MultiCell(0, 0, $titulo, 0, 'L', false, 1, '', '', true);
                $pdf->SetXY(100, $posLinea-25);
                $pdf->SetFont('dejavusans', 'B', 12);
                $pdf->MultiCell(0, 5, 'CUOTA '.$detalleCuota, 0, 'L', false, 1, '', '', true);
                
                $comprobante = '7'.rellenarCeros($idCursosAsistenteCuota, 6);
                $codigoBarra = obtenerCodigoBarra44($comprobante, $importe, $importe, $fechaVencimiento, $fechaVencimiento, NULL);
                
                $pdf->SetXY(80, $posLinea-20);
                $pdf->write1DBarcode($codigoBarra, 'I25', '', '', '', 18, 0.4, $styleCB, 'N');
                
                $pdf->Line(0, $posLinea, 220, $posLinea, array('width' => 0));
                
                $cuotasImpresas++;
                // Límite físico de la hoja A4 para evitar encimamiento de código de barras
                if ($cuotasImpresas >= 7) { 
                    $cuotasImpresas = 0;
                    $pdf->AddPage();
                    $posLinea = 0;
                }
            }
        }

        if ($generoChequera) {
            $pdf->lastPage();
            ob_clean();
            
            $camino = $_SERVER['DOCUMENT_ROOT'] . PATH_PDF;
            $pathArchivo = $pathOrigen."archivos/chequera_cursos/".PERIODO_ACTUAL;
            $nombreArchivo = 'Chequera_Curso_Asistente_'.$idCursosAsistente.'.pdf';
            
            // Creación del directorio si no existiera
            if (!file_exists($pathArchivo)) {
                mkdir($pathArchivo, 0777, true);
            }
            
            // Remover el archivo temporal previo si existe
            if (file_exists($pathArchivo."/".$nombreArchivo)) {
                unlink($pathArchivo."/".$nombreArchivo);
            } 

            // Guarda físicamente el archivo en el servidor ('F')
            $pdf->Output($camino.'/archivos/chequera_cursos/'.PERIODO_ACTUAL.'/'.$nombreArchivo, $tipoPdf);      

            $resultado['estado'] = TRUE;
            $resultado['mensaje'] = "Chequera generada.";
            $resultado['pathArchivo'] = $pathArchivo;
            $resultado['nombreArchivo'] = $nombreArchivo;

            if (file_exists($pathArchivo.'/'.$nombreArchivo)) {
                $pdf_content = file_get_contents($pathArchivo.'/'.$nombreArchivo);        
                $resultado['chequeraPDF'] = base64_encode($pdf_content);   
            } else {
                $resultado['estado'] = FALSE;
                $resultado['mensaje'] = 'CHEQUERA NO EXISTE.';
            }
        } else {
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "No se generó la chequera.";
        }

        return $resultado;
    }

    public function imprimirPlanillaInscripcion($idCursosAsistente, $cursos_pdo, $asistente, $pdf, $pathOrigen) {
        require_once($pathOrigen.'dataAccess/funcionesPhp.php');    
        $tipoPdf = 'F';
        $resultado = array();
        
        $apellidoNombre = (isset($asistente['apellidoNombre'])) ? $asistente['apellidoNombre'] : '__________________________________________________';
        $idColegiado    = (isset($asistente['idColegiado']))    ? $asistente['idColegiado']    : '';
        $matricula      = (isset($asistente['matricula']))      ? $asistente['matricula']      : '_______';
        $idCurso        = (isset($asistente['idCurso']))        ? $asistente['idCurso']        : '';
        $titulo         = (isset($asistente['tituloCurso']))    ? $asistente['tituloCurso']    : '__________________________________________________';

        if (!empty($asistente)) {
            $numeroDocumento = (isset($asistente['numeroDocumento'])) ? $asistente['numeroDocumento'] : '____________________';
            $nacionalidad    = (isset($asistente['nacionalidad']))    ? $asistente['nacionalidad']    : '____________________';
            $mail            = (isset($asistente['mail']))            ? $asistente['mail']            : '____________________';
            $telefono1       = (isset($asistente['telefonoFijo']))    ? $asistente['telefonoFijo']    : '____________________';
            $telefono2       = (isset($asistente['telefonoMovil']))   ? $asistente['telefonoMovil']   : '____________________';
            $nombreLocalidad = (isset($asistente['nombreLocalidad'])) ? $asistente['nombreLocalidad'] : '____________________';
            $nombreTitulo    = (isset($asistente['nombreProfesion'])) ? $asistente['nombreProfesion'] : '____________________';

            $fechaTitulo     = (isset($asistente['fechaTitulo']))     ? $asistente['fechaTitulo']     : '';

            $antiguedad      = (!empty($fechaTitulo)) ? calcular_edad($fechaTitulo) : '____________________';
            $antiguedad      = str_replace("A&ntilde;os", "Años", $antiguedad);
            
            $nombreEspecialidad = isset($asistente['especialidades']) ? $asistente['especialidades'] : '____________________';

            // CORRECCIÓN: Estructura de concatenación de domicilio apuntando correctamente al array $asistente
            $domicilio = '';
            if (!empty($asistente['calle'])) {
                $domicilio = $asistente['calle'];
                if (!empty($asistente['numero'])) {
                    $domicilio .= " Nº " . $asistente['numero'];
                }
                if (!empty($asistente['piso']) && strtoupper($asistente['piso']) != "NR") {
                    $domicilio .= " Piso " . $asistente['piso'];
                }
                if (!empty($asistente['departamento']) && strtoupper($asistente['departamento']) != "NR") {
                    $domicilio .= " Dto. " . $asistente['departamento'];
                }
            } else {
                $domicilio = "______________________________________________________________________________________________________________";
            }
        } else {
            $numeroDocumento = "____________________";
            $nacionalidad    = "____________________";
            $mail            = "____________________";
            $telefono1       = "__________";
            $telefono2       = "__________";
            $domicilio       = "____________________";
            $nombreLocalidad = "____________________";
            $nombreTitulo    = "____________________";
            $antiguedad      = "____________________";
            $nombreEspecialidad = "____________________";
        }

        $esColegiado = (!empty($idColegiado)) ? "S" : "N";

        // Inicializar propiedades del PDF
        $pdf->SetFont('dejavusans', '', 8);
        $pdf->AddPage();

        // Dibujo del encabezado e imágenes corporativas
        $image_file = $pathOrigen . 'public/images/logo_colmed1_hr.png';
        $pdf->Image($image_file, 5, 5, 80, 20, 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        
        $pdf->Ln(5);
        $pdf->SetFont('dejavusans', 'B', 14);
        $pdf->MultiCell(0, 10, 'Cursos de Post-Grado   Ciclo ' . date('Y'), 0, 'L', false, 1, '100', '');
        $pdf->Ln(10);
        $pdf->MultiCell(0, 10, 'Escuela Superior de Educación Médica Prof. Dr. Heraldo Tavella', 0, 'C', false, 1, '10', '');
        
        $pdf->Ln(5);
        $pdf->SetFont('dejavusans', 'B', 12);
        $pdf->MultiCell(0, 20, 'Curso: ' . $titulo, 0, 'L', false, 1, '10', '');
        $pdf->Ln(2);
        
        $pdf->MultiCell(0, 7, 'Datos de Inscripción', 0, 'L', false, 0, '10', '');
        $pdf->MultiCell(0, 7, 'Inscripto Nº ' . $idCursosAsistente, 0, 'L', false, 1, '120', '');
        
        // Bloque de Datos Personales del Inscripto
        $pdf->Ln(10);
        $pdf->SetFont('dejavusans', '', 8);
        $pdf->MultiCell(0, 7, 'Apellido y Nombres: ' . $apellidoNombre, 0, 'L', false, 1, '10', '');
        $pdf->MultiCell(0, 7, 'Documento de Identidad: ' . $numeroDocumento, 0, 'L', false, 1, '10', '');
        $pdf->MultiCell(0, 7, 'Nacionalidad: ' . $nacionalidad, 0, 'L', false, 1, '10', '');
        $pdf->MultiCell(0, 7, 'Domicilio: ' . $domicilio, 0, 'L', false, 1, '10', '');
        $pdf->MultiCell(0, 7, 'Teléfonos: ' . $telefono1 . ' - ' . $telefono2, 0, 'L', false, 1, '10', '');
        $pdf->MultiCell(0, 7, 'Localidad: ' . $nombreLocalidad, 0, 'L', false, 1, '10', '');
        $pdf->MultiCell(0, 7, 'E-mail: ' . $mail, 0, 'L', false, 1, '10', '');
        $pdf->MultiCell(0, 7, 'Profesión: ' . $nombreTitulo, 0, 'L', false, 1, '10', '');
        $pdf->MultiCell(0, 7, 'M.P. Nº: ' . $matricula, 0, 'L', false, 1, '10', '');
        $pdf->MultiCell(0, 7, 'Años de Ejercicio Profesional: ' . $antiguedad, 0, 'L', false, 1, '10', '');
        $pdf->MultiCell(0, 7, 'Especialidad: ' . $nombreEspecialidad, 0, 'L', false, 1, '10', '');

        // Sección de Actividad Laboral (Se completó el bloque que estaba truncado)
        $pdf->Ln(5);
        $pdf->SetFont('dejavusans', 'B', 12);
        $pdf->MultiCell(0, 7, 'Actividad Laboral', 0, 'L', false, 1, '10', '');
        $pdf->Ln(7);
        $pdf->SetFont('dejavusans', 'B', 8);
        $pdf->MultiCell(0, 7, 'Institución: ', 0, 'L', false, 0, '10', '');
        $pdf->SetFont('dejavusans', '', 8);
        $pdf->MultiCell(0, 7, '______________________________________________________________________________________________________________', 0, 'L', false, 1, '40', '');
        $pdf->SetFont('dejavusans', 'B', 8);
        $pdf->MultiCell(0, 7, 'Ingreso: ', 0, 'L', false, 0, '10', '');
        $pdf->SetFont('dejavusans', '', 8);
        $pdf->MultiCell(0, 7, '______________________________________________________________________________________________________________', 0, 'L', false, 1, '40', '');
        $pdf->SetFont('dejavusans', 'B', 8);
        $pdf->MultiCell(0, 7, 'Domicilio: ', 0, 'L', false, 0, '10', '');
        $pdf->SetFont('dejavusans', '', 8);
        $pdf->MultiCell(0, 7, '______________________________________________________________________________________________________________', 0, 'L', false, 1, '40', '');
        $pdf->SetFont('dejavusans', 'B', 8);
        $pdf->MultiCell(0, 7, 'Localidad: ', 0, 'L', false, 0, '10', '');
        $pdf->SetFont('dejavusans', '', 8);
        $pdf->MultiCell(0, 7, '______________________________________________________________________________________________________________', 0, 'L', false, 1, '40', '');
        $pdf->SetFont('dejavusans', 'B', 8);
        $pdf->MultiCell(0, 7, 'Teléfono: ', 0, 'L', false, 0, '10', '');
        $pdf->SetFont('dejavusans', '', 8);
        $pdf->MultiCell(0, 7, '______________________________________________________________________________________________________________', 0, 'L', false, 1, '40', '');
        $pdf->SetFont('dejavusans', 'B', 8);
        $pdf->MultiCell(0, 7, 'Cargo: ', 0, 'L', false, 0, '10', '');
        $pdf->SetFont('dejavusans', '', 8);
        $pdf->MultiCell(0, 7, '______________________________________________________________________________________________________________', 0, 'L', false, 1, '40', '');
        $pdf->SetFont('dejavusans', 'B', 8);
        $pdf->MultiCell(0, 7, 'Otras Actividades: ', 0, 'L', false, 0, '10', '');
        $pdf->SetFont('dejavusans', '', 8);
        $pdf->MultiCell(0, 7, '______________________________________________________________________________________________________________', 0, 'L', false, 1, '40', '');

        //cuerpo
        $x_inicio = 10;
        $y_inicio = 80;
        $x_fin = $x_inicio + 190; //200;
        $y_fin = $y_inicio + 150;
        $pdf->Line($x_inicio, $y_fin, $x_fin, $y_fin, array('width' => 1));

        $pdf->SetXY($x_inicio, $y_fin + 10);
        $html = 'Declaro por la presente conocer el Reglamento General de Cursos que rige el presente y la incumbencia, condiciones de admisibilidad, puntaje otorgante, horarios y demás características del curso al que me inscribo. Asimismo me notifico que de no cumplir con las condiciones de pago en tiempo y forma, al incurrir en una mora de dos (2) cuotas consecutivas, perderé la condición de cursillista regular, debiendo abandonar el curso sin derecho a reclamo o devolución de inscripción y/o pago, y a certificación alguna. ';
        $pdf->SetFont('dejavusans', '', 8);
        $pdf->writeHTMLCell(180, 10, '10', '', $html, 0, 1, 0, true, 'J', true);
        $pdf->SetFont('dejavusans', 'B', 10);
        $pdf->Ln(6);
        $pdf->SetFont('dejavusans', 'B', 8);
        $pdf->MultiCell(0, 5, 'Firma del Cursillista: ', 0, 'L', false, 0, '10', '');
        $pdf->SetFont('dejavusans', '', 8);
        $pdf->MultiCell(0, 5, '_________________________', 0, 'L', false, 1, '50', '');
        $pdf->SetFont('dejavusans', 'B', 10);
        $pdf->MultiCell(0, 5, 'Nota: La presente inscripción no será válida sin la firma del cursillista', 0, 'L', false, 1, '10', '');

        $pdf->lastPage();
        ob_clean();
        
        $camino = $_SERVER['DOCUMENT_ROOT'] . PATH_PDF;
        $pathArchivo = $pathOrigen . "archivos/tmp";
        $nombreArchivo = 'PlanillaInscripcion_' . $idCursosAsistente . '.pdf';
        
        if (!file_exists($pathArchivo)) {
            mkdir($pathArchivo, 0777, true);
        }
        if (file_exists($pathArchivo . "/" . $nombreArchivo)) {
            unlink($pathArchivo . "/" . $nombreArchivo);
        } 

        $pdf->Output($camino . '/archivos/tmp/' . $nombreArchivo, $tipoPdf);      

        $resultado['estado'] = TRUE;
        $resultado['mensaje'] = "Planilla generada con éxito.";
        $resultado['pathArchivo'] = $pathArchivo;
        $resultado['nombreArchivo'] = $nombreArchivo;

        if (file_exists($pathArchivo . '/' . $nombreArchivo)) {
            $pdf_content = file_get_contents($pathArchivo . '/' . $nombreArchivo);        
            $resultado['planillaPDF'] = base64_encode($pdf_content);   
        } else {
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = 'LA PLANILLA NO EXISTE EN EL SERVIDOR.';
        }

        return $resultado;
    }

    public function obtenerDocentePorId($id_docente) {
        try {
            // Obtener la conexión PDO instanciada
            $db = Database::getConnection();
            
            // Configurar codificación si la conexión no la tiene por defecto
            $db->exec("SET NAMES 'utf8'");
            
            $sql = "SELECT ApellidoNombres FROM docente_cursos WHERE Id = :id_docente";
    
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':id_docente', $id_docente, PDO::PARAM_INT);
            $stmt->execute();
            
            $resultado = array();

            // PDO::FETCH_ASSOC mapea directamente a un array asociativo por fila
            if ($stmt->rowCount() > 0) {
                $datos = $stmt->fetch(PDO::FETCH_ASSOC);
                
                $resultado['estado'] = true;
                $resultado['mensaje'] = "OK";
                $resultado['datos'] = $datos;
                $resultado['clase'] = 'alert alert-success'; 
                $resultado['icono'] = 'glyphicon glyphicon-ok'; 
            } else {
                $resultado['estado'] = true;
                $resultado['mensaje'] = "No hay asistentes registrados";
                $resultado['datos'] = array();
                $resultado['clase'] = 'alert alert-info'; 
                $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
            }
            
        } catch (PDOException $e) {
            // Captura cualquier error de base de datos de manera controlada
            $resultado['estado'] = false;
            $resultado['mensaje'] = "Error buscando asistente: " . $e->getMessage();
            $resultado['clase'] = 'alert alert-danger'; 
            $resultado['icono'] = 'glyphicon glyphicon-remove';
        }
        
        return $resultado;
    }

    public function obtenerCursosPorDocente($id_docente) {
        try {
            // Obtener la conexión PDO instanciada
            $db = Database::getConnection();
            
            // Configurar codificación si la conexión no la tiene por defecto
            $db->exec("SET NAMES 'utf8'");
            
            $sql = "SELECT cd.Id AS IdCursosDocente, cd.IdCursos, c.Titulo, cc.Nombre as Cargo
                FROM cursosdocente cd
                INNER JOIN cursos c ON c.Id = cd.IdCursos AND c.Estado = 'A'
                LEFT JOIN cursoscargo cc ON cc.Id = cd.IdCursosCargo
                WHERE cd.IdDocenteCursos = :id_docente AND cd.Borrado = 0
                ORDER BY c.Titulo";
    
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':id_docente', $id_docente, PDO::PARAM_INT);
            $stmt->execute();
            
            $resultado = array();

            // PDO::FETCH_ASSOC mapea directamente a un array asociativo por fila
            if ($stmt->rowCount() > 0) {
                $datos = array();
                while ($row_db = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $row = array (
                        'id_cursos_docente' => $row_db['IdCursosDocente'],
                        'id_cursos' => $row_db['IdCursos'],
                        'titulo' => trim($row_db['Titulo']),
                        'cargo' => $row_db['Cargo']
                    );
                    $datos[] = $row;
                }
                
                $resultado['estado'] = true;
                $resultado['mensaje'] = "OK";
                $resultado['datos'] = $datos;
                $resultado['clase'] = 'alert alert-success'; 
                $resultado['icono'] = 'glyphicon glyphicon-ok'; 
            } else {
                $resultado['estado'] = true;
                $resultado['mensaje'] = "No hay asistentes registrados";
                $resultado['datos'] = array();
                $resultado['clase'] = 'alert alert-info'; 
                $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
            }
            
        } catch (PDOException $e) {
            // Captura cualquier error de base de datos de manera controlada
            $resultado['estado'] = false;
            $resultado['mensaje'] = "Error buscando asistente: " . $e->getMessage();
            $resultado['clase'] = 'alert alert-danger'; 
            $resultado['icono'] = 'glyphicon glyphicon-remove';
        }
        
        return $resultado;
    }

    public function obtenerDocentes($estado) {
        try {
            // Obtener la conexión PDO instanciada
            $db = Database::getConnection();
            
            // Configurar codificación si la conexión no la tiene por defecto
            $db->exec("SET NAMES 'utf8'");
            
            $borrado = isset($estado) ? $estado : 0;

            $sql = "SELECT dc.Id, dc.IdColegiado, c.Matricula, dc.ApellidoNombres, dc.FechaCarga
                FROM docente_cursos dc
                LEFT JOIN colegiado c ON c.Id = dc.IdColegiado
                WHERE dc.Borrado = :borrado
                ORDER BY dc.ApellidoNombres";
    
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':borrado', $borrado, PDO::PARAM_INT);
            $stmt->execute();
            
            $resultado = array();

            // PDO::FETCH_ASSOC mapea directamente a un array asociativo por fila
            if ($stmt->rowCount() > 0) {
                $datos = array();
                while ($row_db = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $row = array (
                        'id_docente_cursos' => $row_db['Id'],
                        'id_colegiado' => $row_db['IdColegiado'],
                        'matricula' => $row_db['Matricula'],
                        'apellido_nombre' => $row_db['ApellidoNombres'],
                        'fecha_carga' => $row_db['FechaCarga']
                    );
                    $datos[] = $row;
                }
                
                $resultado['estado'] = true;
                $resultado['mensaje'] = "OK";
                $resultado['datos'] = $datos;
                $resultado['clase'] = 'alert alert-success'; 
                $resultado['icono'] = 'glyphicon glyphicon-ok'; 
            } else {
                $resultado['estado'] = true;
                $resultado['mensaje'] = "No hay docentes registrados";
                $resultado['datos'] = array();
                $resultado['clase'] = 'alert alert-info'; 
                $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
            }
            
        } catch (PDOException $e) {
            // Captura cualquier error de base de datos de manera controlada
            $resultado['estado'] = false;
            $resultado['mensaje'] = "Error buscando docentes: " . $e->getMessage();
            $resultado['clase'] = 'alert alert-danger'; 
            $resultado['icono'] = 'glyphicon glyphicon-remove';
        }
        
        return $resultado;
    }

    public function guardarCursosDocente($id_cursos_docente, $id_docente, $id_cursos_cargo, $id_curso, $id_usuario, $borrado) {
        try {
            // Conexión usando tu estándar nativo
            $db = Database::getConnection();
            $db->exec("SET NAMES 'utf8'");

            if (empty($id_cursos_docente)) {
                // Insertar el nuevo registro en la tabla intermedia
                $sql = "INSERT INTO cursosdocente (IdCursos, IdDocenteCursos, IdCursosCargo, FechaCarga, IdUsuario, Borrado) 
                    VALUES (:id_curso, :id_docente, :id_cursos_cargo, NOW(), :id_usuario, :borrado)";
                $stmt = $db->prepare($sql);
                $stmt->execute([
                    'id_curso' => $id_curso,
                    'id_docente' => $id_docente,
                    'id_cursos_cargo' => $id_cursos_cargo,
                    'id_usuario' => $id_usuario,
                    'borrado' => $borrado
                ]);

                $resultado['estado'] = true;
                $resultado['mensaje'] = "¡El curso se ha asignado correctamente al docente!";;

            } else {
                // cambiar a borrado = 1
                $sql = "UPDATE cursosdocente 
                        SET Borrado = :borrado, IdUsuarioBorrado = :id_usuario, FechaBorrado = NOW() 
                        WHERE Id = :id_cursos_docente";
                $stmt = $db->prepare($sql);
                $stmt->execute([
                    'borrado' => $borrado,
                    'id_usuario' => $id_usuario,
                    'id_cursos_docente' => $id_cursos_docente
                ]);

                $resultado['estado'] = true;
                $resultado['mensaje'] = "¡El curso se ha desasignado correctamente al docente!";;

            }
        } catch (PDOException $e) {
            $resultado['estado'] = false;
            $resultado['mensaje'] = "Error buscando docentes: " . urlencode($e->getMessage());
        }

        return $resultado;
    }

    public function obtenerCursosAutocompletar($estadoCurso){
        try {
            // Obtener la conexión PDO instanciada
            $db = Database::getConnection();
            
            // Configurar codificación si la conexión no la tiene por defecto
            $db->exec("SET NAMES 'utf8'");
            
            $sql = "SELECT c.Id, c.Titulo
                    FROM cursos c";

            // Filtro dinámico
            if (!empty($estadoCurso)) {
                $sql .= " WHERE c.Estado = :estado";
            }
            
            $sql .= " ORDER BY c.Titulo";
        
            $stmt = $db->prepare($sql);
            if (!empty($estadoCurso)) {
                $stmt->bindParam(':estado', $estadoCurso, PDO::PARAM_STR);
            }
            $stmt->execute();
            
            $resultado = array();

            // PDO::FETCH_ASSOC mapea directamente a un array asociativo por fila
            if ($stmt->rowCount() > 0) {
                $datos = array();
                while ($row_db = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $row = array (
                        'id' => $row_db['Id'],
                        'nombre' => trim($row_db['Titulo'])
                    );
                    $datos[] = $row;
                }
                
                $resultado['estado'] = true;
                $resultado['mensaje'] = "OK";
                $resultado['datos'] = $datos;
                $resultado['clase'] = 'alert alert-success'; 
                $resultado['icono'] = 'glyphicon glyphicon-ok'; 
            } else {
                $resultado['estado'] = true;
                $resultado['mensaje'] = "No hay asistentes registrados";
                $resultado['datos'] = array();
                $resultado['clase'] = 'alert alert-info'; 
                $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
            }
            
        } catch (PDOException $e) {
            // Captura cualquier error de base de datos de manera controlada
            $resultado['estado'] = false;
            $resultado['mensaje'] = "Error buscando asistente: " . $e->getMessage();
            $resultado['clase'] = 'alert alert-danger'; 
            $resultado['icono'] = 'glyphicon glyphicon-remove';
        }
        
        return $resultado;
    }

    public function obtenerAsistentesAutocompletar(){
        try {
            // Obtener la conexión PDO instanciada
            $db = Database::getConnection();
            
            // Configurar codificación si la conexión no la tiene por defecto
            $db->exec("SET NAMES 'utf8'");
            
            $sql = "SELECT ca.Id, c.Matricula, ca.ApellidoNombre, cur.Titulo, ca.Estado
                    FROM cursosasistente ca
                    INNER JOIN cursos cur on cur.Id = ca.IdCursos
                    LEFT JOIN colegiado c on c.Id = ca.IdColegiado
                    WHERE ca.Estado = 'S' AND cur.Estado = 'A' AND ca.Borrado = 0
                    ORDER BY ca.ApellidoNombre";
                    
            $stmt = $db->prepare($sql);
            $stmt->execute();
            
            $resultado = array();

            // PDO::FETCH_ASSOC mapea directamente a un array asociativo por fila
            if ($stmt->rowCount() > 0) {
                $datos = array();
                while ($row_db = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    
                    $asiste_texto = ($row_db['Estado'] == 'S') ? 'Asiste' : 'NO ASISTE';
                    $matricula_texto = !empty($row_db['Matricula']) ? $row_db['Matricula'] : 'Sin Matrícula';

                    $row = array (
                        'id' => $row_db['Id'],
                        'nombre' => trim($row_db['ApellidoNombre']) . ' - ' . trim($row_db['Titulo']) . ' (' . $asiste_texto . ') - ' . $matricula_texto
                    );
                    $datos[] = $row;
                }
                
                $resultado['estado'] = true;
                $resultado['mensaje'] = "OK";
                $resultado['datos'] = $datos;
                $resultado['clase'] = 'alert alert-success'; 
                $resultado['icono'] = 'glyphicon glyphicon-ok'; 
            } else {
                $resultado['estado'] = true;
                $resultado['mensaje'] = "No hay asistentes registrados";
                $resultado['datos'] = array();
                $resultado['clase'] = 'alert alert-info'; 
                $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
            }
            
        } catch (PDOException $e) {
            // Captura cualquier error de base de datos de manera controlada
            $resultado['estado'] = false;
            $resultado['mensaje'] = "Error buscando asistente: " . $e->getMessage();
            $resultado['clase'] = 'alert alert-danger'; 
            $resultado['icono'] = 'glyphicon glyphicon-remove';
        }
        
        return $resultado;
    }

    public function obtenerDocentesAutocompletar() {
        try {
            // Obtener la conexión PDO instanciada
            $db = Database::getConnection();
            
            // Configurar codificación si la conexión no la tiene por defecto
            $db->exec("SET NAMES 'utf8'");
            
            $sql = "SELECT dc.Id, dc.ApellidoNombres, c.Matricula
                    FROM docente_cursos dc
                    LEFT JOIN colegiado c ON c.Id = dc.IdColegiado
                    WHERE dc.Borrado = 0
                    ORDER BY dc.ApellidoNombres";
                    
            $stmt = $db->prepare($sql);
            $stmt->execute();
            
            $resultado = array();

            // PDO::FETCH_ASSOC mapea directamente a un array asociativo por fila
            if ($stmt->rowCount() > 0) {
                $datos = array();
                while ($row_db = $stmt->fetch(PDO::FETCH_ASSOC)) {

                    $row = array (
                        'id' => $row_db['Id'],
                        'nombre' => trim($row_db['ApellidoNombres']) . $row_db['Matricula']
                    );
                    $datos[] = $row;
                }
                
                $resultado['estado'] = true;
                $resultado['mensaje'] = "OK";
                $resultado['datos'] = $datos;
                $resultado['clase'] = 'alert alert-success'; 
                $resultado['icono'] = 'glyphicon glyphicon-ok'; 
            } else {
                $resultado['estado'] = true;
                $resultado['mensaje'] = "No hay docentes registrados";
                $resultado['datos'] = array();
                $resultado['clase'] = 'alert alert-info'; 
                $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
            }
            
        } catch (PDOException $e) {
            // Captura cualquier error de base de datos de manera controlada
            $resultado['estado'] = false;
            $resultado['mensaje'] = "Error buscando docentes: " . $e->getMessage();
            $resultado['clase'] = 'alert alert-danger'; 
            $resultado['icono'] = 'glyphicon glyphicon-remove';
        }
        
        return $resultado;
    }

    public function obtenerCuotasCursoAPagar($idAsistente) {
        try {
            // Obtener la conexión PDO instanciada
            $db = Database::getConnection();
            $db->exec("SET NAMES 'utf8'");
            
            $sql = "SELECT Id, Cuota, Importe, FechaVencimiento, DetalleCuota
                    FROM cursosasistentecuotas 
                    WHERE IdCursosAsistente = :idAsistente 
                      AND (FechaPago IS NULL OR FechaPago = '0000-00-00')
                      AND Borrado = 0";
                      
            $stmt = $db->prepare($sql);
            
            // Vinculamos el parámetro de forma segura controlando el tipo de dato entero
            $stmt->bindValue(':idAsistente', $idAsistente, PDO::PARAM_INT);
            $stmt->execute();
            
            $resultado = array();

            if ($stmt->rowCount() > 0) {
                $datos = array();
                while ($row_db = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $row = array (
                        'idCursosAsistenteCuota' => $row_db['Id'],
                        'cuota' => $row_db['Cuota'],
                        'importe' => $row_db['Importe'],
                        'fechaVencimiento' => $row_db['FechaVencimiento'],
                        'detalleCuota' => $row_db['DetalleCuota']
                    );
                    $datos[] = $row;
                }
                
                $resultado['estado'] = TRUE;
                $resultado['mensaje'] = "OK";
                $resultado['datos'] = $datos;
                $resultado['clase'] = 'alert alert-success'; 
                $resultado['icono'] = 'glyphicon glyphicon-ok'; 
            } else {
                $resultado['estado'] = FALSE;
                $resultado['mensaje'] = "No hay cuotas a pagar";
                $resultado['clase'] = 'alert alert-info'; 
                $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
            }
            
        } catch (PDOException $e) {
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "Error buscando cuotas a pagar: " . $e->getMessage();
            $resultado['clase'] = 'alert alert-danger'; 
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
        
        return $resultado;
    }

    public function obtenerAsistentePorIdCuotaCurso($idCursosAsistenteCuota){
        try {
            // Obtener la conexión PDO instanciada
            $db = Database::getConnection();
            $db->exec("SET NAMES 'utf8'");
            
            $sql = "SELECT ca.Id, ca.IdCursos, cac.Cuota, cac.Importe, cac.FechaVencimiento
                    FROM cursosasistente ca
                    INNER JOIN cursosasistentecuotas cac ON cac.IdCursosAsistente = ca.Id
                    WHERE cac.Id = :idCursosAsistenteCuota";
                    
            $stmt = $db->prepare($sql);
            
            // Vinculamos el parámetro de forma segura como entero
            $stmt->bindValue(':idCursosAsistenteCuota', $idCursosAsistenteCuota, PDO::PARAM_INT);
            $stmt->execute();
            
            $resultado = array();

            // Intentamos obtener la fila única devuelta por la consulta
            $row_db = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row_db) {
                $datos = array (
                    'idAsistente'      => $row_db['Id'],
                    'idCurso'          => $row_db['IdCursos'],
                    'cuota'            => $row_db['Cuota'],
                    'importe'          => $row_db['Importe'],
                    'fechaVencimiento' => $row_db['FechaVencimiento']
                );
                
                $resultado['estado'] = TRUE;
                $resultado['mensaje'] = "OK";
                $resultado['datos']   = $datos;
                $resultado['clase']   = 'alert alert-success'; 
                $resultado['icono']   = 'glyphicon glyphicon-ok'; 
            } else {
                // Manejo en caso de que el ID de cuota no exista en el sistema
                $resultado['estado'] = FALSE;
                $resultado['mensaje'] = "No se encontró la cuota solicitada";
                $resultado['clase']   = 'alert alert-info'; 
                $resultado['icono']   = 'glyphicon glyphicon-exclamation-sign';
            }
            
        } catch (PDOException $e) {
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "Error buscando la cuota: " . $e->getMessage();
            $resultado['clase']   = 'alert alert-danger'; 
            $resultado['icono']   = 'glyphicon glyphicon-exclamation-sign';
        }

        return $resultado;
    }

    public function obtenerNombreCursoAsistente($idAsistente) {
        try {
            // Obtener la conexión PDO instanciada
            $db = Database::getConnection();
            $db->exec("SET NAMES 'utf8'");
            
            $sql = "SELECT c.Titulo
                    FROM cursos c
                    INNER JOIN cursosasistente ca ON ca.IdCursos = c.Id
                    WHERE ca.Id = :idAsistente";
                    
            $stmt = $db->prepare($sql);
            
            // Vinculamos el parámetro como entero de forma segura
            $stmt->bindValue(':idAsistente', $idAsistente, PDO::PARAM_INT);
            $stmt->execute();
            
            $resultado = array();

            // Obtenemos la fila directamente como un array asociativo
            $row_db = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row_db) {
                $resultado['estado'] = TRUE;
                $resultado['mensaje'] = "OK";
                $resultado['titulo'] = $row_db['Titulo'];
                $resultado['clase'] = 'alert alert-success'; 
                $resultado['icono'] = 'glyphicon glyphicon-ok'; 
            } else {
                // Manejo por si el ID de asistente no está registrado o no tiene curso asignado
                $resultado['estado'] = FALSE;
                $resultado['mensaje'] = "No se encontró el curso para el asistente solicitado";
                $resultado['titulo'] = "";
                $resultado['clase'] = 'alert alert-warning'; 
                $resultado['icono'] = 'glyphicon glyphicon-warning-sign';
            }
            
        } catch (PDOException $e) {
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "Error buscando el nombre del curso: " . $e->getMessage();
            $resultado['titulo'] = "";
            $resultado['clase'] = 'alert alert-danger'; 
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }

        return $resultado;
    }

    public function obtenerTotalCobranzaPorPeriodo($idCurso, $fechaDesde, $fechaHasta) {
        try {
            // Obtener la conexión PDO instanciada
            $db = Database::getConnection();
            $db->exec("SET NAMES 'utf8'");
            
            // Evaluar dinámicamente si se filtra por curso
            $porCurso = "";
            $filtrarCurso = (isset($idCurso) && $idCurso !== "");
            if ($filtrarCurso) {
                $porCurso = "AND c.Id = :idCurso";
            }

            $sql = "SELECT c.titulo, substr(a.fechapago, 1, 4) AS anio, substr(a.fechapago, 6, 2) AS mes, count(a.id) AS cantidad, sum(a.importe) AS total, c.id AS curso_id
                    FROM cursosasistentecuotas a
                    INNER JOIN cursosasistente b ON b.id = a.idcursosasistente
                    INNER JOIN cursos c ON c.id = b.idcursos AND c.estado = 'A'
                    WHERE a.fechapago BETWEEN :fechaDesde AND :fechaHasta
                    AND a.fechapago <> 0 " . $porCurso . "            
                    GROUP BY c.titulo, substr(a.fechapago, 1, 4), substr(a.fechapago, 6, 2), c.id
                    ORDER BY c.Id, substr(a.fechapago, 1, 4), substr(a.fechapago, 6, 2)";
                    
            $stmt = $db->prepare($sql);
            
            // Vincular los parámetros obligatorios de fechas
            $stmt->bindValue(':fechaDesde', $fechaDesde, PDO::PARAM_STR);
            $stmt->bindValue(':fechaHasta', $fechaHasta, PDO::PARAM_STR);
            
            // Vincular el parámetro opcional de curso de forma segura si corresponde
            if ($filtrarCurso) {
                $stmt->bindValue(':idCurso', $idCurso, PDO::PARAM_INT);
            }
            
            $stmt->execute();
            
            $resultado = array();
            $datos = array();

            // Recorrer los resultados asociativos devueltos por PDO
            while ($row_db = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $row = array (
                    'idCurso' => $row_db['curso_id'],
                    'nombreCurso' => $row_db['titulo'],
                    'anioPago' => $row_db['anio'],
                    'mesPago' => $row_db['mes'],
                    'cantidadPagos' => $row_db['cantidad'],
                    'importePagado' => $row_db['total']
                );
                $datos[] = $row;
            }

            $resultado['estado'] = TRUE;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['cantidad'] = count($datos); // count() es la forma estándar en PHP, más legible que sizeof()
            $resultado['clase'] = 'alert alert-success'; 
            $resultado['icono'] = 'glyphicon glyphicon-ok'; 
            
        } catch (PDOException $e) {
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "Error buscando totales de cobranza: " . $e->getMessage();
            $resultado['clase'] = 'alert alert-danger'; 
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
        
        return $resultado;
    }

    public function obtenerTotalPorCuotaPeriodo($idCurso, $fechaDesde, $fechaHasta) {
        try {
            // Obtener la conexión PDO instanciada
            $db = Database::getConnection();
            $db->exec("SET NAMES 'utf8'");
            
            // Evaluar dinámicamente si se filtra por curso
            $porCurso1 = "";
            $porCurso2 = "";
            $filtrarCurso = (isset($idCurso) && $idCurso !== "");
            
            if ($filtrarCurso) {
                $porCurso1 = "AND c.Id = :idCurso1";
                $porCurso2 = "AND c.Id = :idCurso2";
            }

            $sql = "(SELECT c.Id, c.titulo, a.Cuota, b.ApellidoNombre, a.fechapago, a.importe, b.Estado, a.Recibo, a.DetalleCuota, a.FechaVencimiento
                FROM cursosasistentecuotas a
                INNER JOIN cursosasistente b ON b.id = a.idcursosasistente 
                INNER JOIN cursos c ON c.id = b.idcursos AND c.estado = 'A'
                WHERE a.fechapago BETWEEN :fechaDesde AND :fechaHasta1
                AND a.fechapago <> 0 AND a.FechaVencimiento <= :fechaHasta2 " . $porCurso1 . ")
                
                UNION ALL

                (SELECT c.Id, c.titulo, a.Cuota, b.ApellidoNombre, a.fechapago, a.importe, b.Estado, NULL, a.DetalleCuota, a.FechaVencimiento
                FROM cursosasistentecuotas a
                INNER JOIN cursosasistente b ON b.id = a.idcursosasistente AND b.Estado = 'S'
                INNER JOIN cursos c ON c.id = b.idcursos AND c.estado = 'A'
                WHERE a.FechaVencimiento <= :fechaHasta3 AND a.fechapago IS NULL " . $porCurso2 . ")            
                ORDER BY Titulo, Cuota, fechapago DESC, ApellidoNombre";

            $stmt = $db->prepare($sql);
            
            // Vincular parámetros de fechas
            $stmt->bindValue(':fechaDesde', $fechaDesde, PDO::PARAM_STR);
            $stmt->bindValue(':fechaHasta1', $fechaHasta, PDO::PARAM_STR);
            $stmt->bindValue(':fechaHasta2', $fechaHasta, PDO::PARAM_STR);
            $stmt->bindValue(':fechaHasta3', $fechaHasta, PDO::PARAM_STR);
            
            // Vincular parámetros de curso de manera segura si aplica
            if ($filtrarCurso) {
                $stmt->bindValue(':idCurso1', $idCurso, PDO::PARAM_INT);
                $stmt->bindValue(':idCurso2', $idCurso, PDO::PARAM_INT);
            }
            
            $stmt->execute();
            
            $resultado = array();
            $datos = array();

            // Procesar las filas con FETCH_ASSOC
            while ($row_db = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $row = array (
                    'idCurso'          => $row_db['Id'],
                    'nombreCurso'      => $row_db['titulo'],
                    'cuota'            => $row_db['Cuota'],
                    'apellidoNombre'   => $row_db['ApellidoNombre'],
                    'fechaPago'        => $row_db['fechapago'],
                    'importePagado'    => $row_db['importe'],
                    'asiste'           => $row_db['Estado'],
                    'recibo'           => $row_db['Recibo'],
                    'detalleCuota'     => $row_db['DetalleCuota'],
                    'fechaVencimiento' => $row_db['FechaVencimiento']
                );
                $datos[] = $row;
            }

            $resultado['estado'] = TRUE;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['cantidad'] = count($datos);
            $resultado['clase'] = 'alert alert-success'; 
            $resultado['icono'] = 'glyphicon glyphicon-ok'; 
            
        } catch (PDOException $e) {
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "Error buscando cuotas por periodo: " . $e->getMessage();
            $resultado['clase'] = 'alert alert-danger'; 
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
        
        return $resultado;
    }

    public function obtenerDetalleCobranzaPorPeriodo($idCurso, $fechaDesde, $fechaHasta, $anioPago, $mesPago) {
        try {
            // Obtener la conexión PDO instanciada
            $db = Database::getConnection();
            $db->exec("SET NAMES 'utf8'");
            
            // Armar el periodo en formato YYYY-MM para la comparación de SUBSTR
            $periodoCobrado = $anioPago . '-' . $mesPago;
            
            $sql = "SELECT b.ApellidoNombre, a.FechaPago, a.Importe, a.Recibo, a.DetalleCuota
                    FROM cursosasistentecuotas a
                    INNER JOIN cursosasistente b ON b.Id = a.IdCursosAsistente
                    INNER JOIN cursos c ON c.Id = b.IdCursos AND c.Estado = 'A'
                    WHERE c.Id = :idCurso
                      AND a.FechaPago BETWEEN :fechaDesde AND :fechaHasta
                      AND :periodoCobrado = SUBSTR(a.FechaPago, 1, 7)
                    ORDER BY b.ApellidoNombre, YEAR(a.FechaPago), MONTH(a.FechaPago)";
                    
            $stmt = $db->prepare($sql);
            
            // Vincular los parámetros de forma segura controlando sus tipos de datos
            $stmt->bindValue(':idCurso', $idCurso, PDO::PARAM_INT);
            $stmt->bindValue(':fechaDesde', $fechaDesde, PDO::PARAM_STR);
            $stmt->bindValue(':fechaHasta', $fechaHasta, PDO::PARAM_STR);
            $stmt->bindValue(':periodoCobrado', $periodoCobrado, PDO::PARAM_STR);
            
            $stmt->execute();
            
            $resultado = array();
            $datos = array();

            // Procesar las filas con FETCH_ASSOC
            while ($row_db = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $row = array (
                    'apellidoNombre' => $row_db['ApellidoNombre'],
                    'fechaPago'      => $row_db['FechaPago'],
                    'importe'        => $row_db['Importe'],
                    'recibo'         => $row_db['Recibo'],
                    'detalleCuota'   => $row_db['DetalleCuota']
                );
                $datos[] = $row;
            }

            $resultado['estado'] = TRUE;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['cantidad'] = count($datos); // count() en lugar de sizeof()
            $resultado['clase'] = 'alert alert-success'; 
            $resultado['icono'] = 'glyphicon glyphicon-ok'; 
            
        } catch (PDOException $e) {
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "Error buscando detalle de cobranza: " . $e->getMessage();
            $resultado['clase'] = 'alert alert-danger'; 
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
        
        return $resultado;        
    }

    public function obtenerColegiadosAsistentesAutocompletar(){
        try {
            $db = Database::getConnection();
            $db->exec("SET NAMES 'utf8'");
            
            $sql = "SELECT DISTINCT c.Id, c.Matricula, p.Apellido, p.Nombres, p.NumeroDocumento 
                    FROM colegiado c
                    INNER JOIN persona p ON p.Id = c.IdPersona
                    INNER JOIN cursosasistente ca ON ca.IdColegiado = c.Id
                    ORDER BY p.Apellido, p.Nombres";
                    
            $stmt = $db->prepare($sql);
            $stmt->execute();
            
            $resultado = array();

            if ($stmt->rowCount() > 0) {
                $datos = array();
                while ($row_db = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $row = array (
                        'id' => $row_db['Id'],
                        'nombre' => $row_db['Matricula'] . ' - ' . trim($row_db['Apellido']) . " " . trim($row_db['Nombres'])
                    );
                    $datos[] = $row;
                }
                $resultado['estado'] = true;
                $resultado['mensaje'] = "OK";
                $resultado['datos'] = $datos;
                $resultado['clase'] = 'alert alert-success'; 
                $resultado['icono'] = 'glyphicon glyphicon-ok'; 
            } else {
                $resultado['estado'] = true;
                $resultado['mensaje'] = "No hay asistentes colegiados";
                $resultado['datos'] = array();
                $resultado['clase'] = 'alert alert-info'; 
                $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
            }
        } catch (PDOException $e) {
            $resultado['estado'] = false;
            $resultado['mensaje'] = "Error buscando asistentes colegiados: " . $e->getMessage();
            $resultado['clase'] = 'alert alert-danger'; 
            $resultado['icono'] = 'glyphicon glyphicon-remove';
        }
        return $resultado;
    }

    public function obtenerAsistentesNoColegiadosAutocompletar(){
        try {
            $db = Database::getConnection();
            $db->exec("SET NAMES 'utf8'");
            
            $sql = "SELECT ca.Id, ca.ApellidoNombre, c.Titulo
                    FROM cursosasistente ca 
                    INNER JOIN cursos c ON c.Id = ca.IdCursos AND YEAR(c.FechaInicio) >= (YEAR(NOW())-10) 
                    WHERE ca.IdColegiado IS NULL AND ca.Estado = 'S'
                    ORDER BY ca.ApellidoNombre";
                    
            $stmt = $db->prepare($sql);
            $stmt->execute();
            
            $resultado = array();

            if ($stmt->rowCount() > 0) {
                $datos = array();
                while ($row_db = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $row = array (
                        'id' => $row_db['Id'],
                        'nombre' => trim($row_db['ApellidoNombre']) . ' - CURSO: ' . trim($row_db['Titulo'])
                    );
                    $datos[] = $row;
                }
                $resultado['estado'] = true;
                $resultado['mensaje'] = "OK";
                $resultado['datos'] = $datos;
                $resultado['clase'] = 'alert alert-success'; 
                $resultado['icono'] = 'glyphicon glyphicon-ok'; 
            } else {
                $resultado['estado'] = true;
                $resultado['mensaje'] = "No hay asistentes no colegiados";
                $resultado['datos'] = array();
                $resultado['clase'] = 'alert alert-info'; 
                $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
            }
        } catch (PDOException $e) {
            $resultado['estado'] = false;
            $resultado['mensaje'] = "Error buscando asistentes no colegiados: " . $e->getMessage();
            $resultado['clase'] = 'alert alert-danger'; 
            $resultado['icono'] = 'glyphicon glyphicon-remove';
        }
        return $resultado;
    }

    public function obtenerCursosPorIdColegiado($idColegiado) {
        try {
            // Obtener la conexión PDO instanciada
            $db = Database::getConnection();
            $db->exec("SET NAMES 'utf8'");
            
            // Agregamos alias explícitos a ca.Id y c.Id para que no se sobreescriban en FETCH_ASSOC
            $sql = "SELECT ca.Id AS idAsistente, c.Id AS idCurso, c.Titulo, c.FechaInicio, c.Director, c.Estado, 
                           (SELECT COUNT(cc.Id) FROM cursoscuotas cc WHERE cc.IdCursos = c.Id) AS CantidadCuotas, 
                           (SELECT COUNT(cac.Id) FROM cursosasistentecuotas cac WHERE cac.IdCursosAsistente = ca.Id AND (cac.FechaPago IS NULL OR cac.FechaPago = '0000-00-00')) AS CantidadCuotasImpagas
                    FROM cursosasistente ca
                    INNER JOIN cursos c ON c.Id = ca.IdCursos
                    WHERE ca.IdColegiado = :idColegiado 
                      AND ca.Estado = 'S' 
                      AND ca.Borrado = 0";
                      
            $stmt = $db->prepare($sql);
            
            // Vinculamos el parámetro controlando de forma estricta que sea entero
            $stmt->bindValue(':idColegiado', $idColegiado, PDO::PARAM_INT);
            $stmt->execute();
            
            $resultado = array();

            if ($stmt->rowCount() > 0) {
                $datos = array();
                while ($row_db = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $row = array (
                        'idCursosAsistente'     => $row_db['idAsistente'], // Consumido mediante el alias asignado
                        'idCurso'               => $row_db['idCurso'],     // Consumido mediante el alias asignado
                        'titulo'                => $row_db['Titulo'],
                        'fechaInicio'           => $row_db['FechaInicio'],
                        'director'              => $row_db['Director'],
                        'estado'                => $row_db['Estado'],
                        'cantidadCuotas'        => $row_db['CantidadCuotas'],
                        'cantidadCuotasImpagas' => $row_db['CantidadCuotasImpagas']
                    );
                    $datos[] = $row;
                }
                $resultado['estado'] = TRUE;
                $resultado['mensaje'] = "OK";
                $resultado['datos'] = $datos;
                $resultado['clase'] = 'alert alert-success'; 
                $resultado['icono'] = 'glyphicon glyphicon-ok'; 
            } else {
                $resultado['estado'] = FALSE;
                $resultado['mensaje'] = "No hay cursos asignados para este colegiado";
                $resultado['datos'] = array(); // Devolvemos array vacío para evitar fallos en el frontend
                $resultado['clase'] = 'alert alert-info'; 
                $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
            }
            
        } catch (PDOException $e) {
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "Error buscando cursos del colegiado: " . $e->getMessage();
            $resultado['clase'] = 'alert alert-danger'; 
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
        
        return $resultado;
    }

    public function guardarCurso($idCurso, $titulo, $tema, $dias, $fechas, $salon, $lugar, $director, $coordinador, $fechaInicio, $vigenciHasta, $estadoCurso, $inscripcionDesde, $inscripcionHasta, $datosAnteriores){
        $resultado = array();
        try {
            // Obtener la conexión PDO instanciada
            $db = Database::getConnection();
            $db->exec("SET NAMES 'utf8'");
            
            // Iniciar la transacción de forma nativa en PDO
            $db->beginTransaction();
            
            $esModificacion = (isset($idCurso) && $idCurso <> "");

            if ($esModificacion) {
                $sql = "UPDATE cursos 
                        SET Titulo = ?, Tema = ?, Dias = ?, Fechas = ?, Salon = ?, Lugar = ?, Director = ?, Coordinador = ?, FechaInicio = ?, VigenciaHasta = ?, Estado = ?, InscripcionDesde = ?, InscripcionHasta = ?
                        WHERE Id = ?";
                $stmt = $db->prepare($sql);
                
                // Pasamos los parámetros ordenados como un array plano al execute
                $stmt->execute([
                    $titulo, $tema, $dias, $fechas, $salon, $lugar, $director, $coordinador, 
                    $fechaInicio, $vigenciHasta, $estadoCurso, $inscripcionDesde, $inscripcionHasta, 
                    $idCurso
                ]);
                
                $tipoMovimiento = 'modificacion';
            } else {
                $sql = "INSERT INTO cursos (Titulo, Tema, Dias, Fechas, Salon, Lugar, Director, Coordinador, FechaInicio, VigenciaHasta, Estado, InscripcionDesde, InscripcionHasta)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $db->prepare($sql);
                
                $stmt->execute([
                    $titulo, $tema, $dias, $fechas, $salon, $lugar, $director, $coordinador, 
                    $fechaInicio, $vigenciHasta, $estadoCurso, $inscripcionDesde, $inscripcionHasta
                ]);
                
                // Obtener el ID autoincremental recién generado
                $idCurso = $db->lastInsertId();
                $datosAnteriores = array();
                $tipoMovimiento = 'alta';
            }

            // Preparar y registrar el log del curso
            $datos = serialize($datosAnteriores);
            $sqlLog = "INSERT INTO log_cursos (Tabla, IdTabla, Fecha, TipoMovimiento, IdUsuario, Datos) 
                       VALUES ('cursos', ?, NOW(), ?, ?, ?)";
            
            $stmtLog = $db->prepare($sqlLog);
            $stmtLog->execute([
                $idCurso, 
                $tipoMovimiento, 
                $_SESSION['user_id'], 
                $datos
            ]);

            // Si todo se ejecutó sin lanzar excepciones, confirmamos los cambios
            $db->commit();

            $resultado['estado'] = TRUE;
            $resultado['idCurso'] = $idCurso;
            $resultado['mensaje'] = 'EL CURSO HA SIDO GUARDADO';
            $resultado['clase'] = 'alert alert-success'; 
            $resultado['icono'] = 'glyphicon glyphicon-ok'; 

        } catch (PDOException $e) {
            // En caso de cualquier fallo, revertimos la transacción de manera segura
            if (isset($db) && $db->inTransaction()) {
                $db->rollBack();
            }
            
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "Error procesando el curso en la base de datos: " . $e->getMessage();
            $resultado['clase'] = 'alert alert-danger'; 
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }     
        
        return $resultado;
    }

    public function finalizarCurso($idCurso, $datosAnteriores){
        $resultado = array();
        try {
            // Verificar entrada básica antes de abrir la transacción
            if (!isset($idCurso) || $idCurso == "") {
                $resultado['estado'] = FALSE;
                $resultado['mensaje'] = "Error finalizando curso -> Ingreso incorrecto";
                $resultado['clase'] = 'alert alert-danger'; 
                $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
                return $resultado;
            }

            // Obtener la conexión PDO instanciada
            $db = Database::getConnection();
            $db->exec("SET NAMES 'utf8'");
            
            // Iniciar la transacción nativa en PDO
            $db->beginTransaction();

            // 1. Actualizar el estado del curso a Finalizado ('F')
            $sql = "UPDATE cursos SET Estado = 'F' WHERE Id = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$idCurso]);

            // 2. Preparar y registrar la auditoría en la tabla log_cursos
            $tipoMovimiento = 'finalizar';
            $datos = serialize($datosAnteriores);
            
            $sqlLog = "INSERT INTO log_cursos (Tabla, IdTabla, Fecha, TipoMovimiento, IdUsuario, Datos) 
                       VALUES ('cursos', ?, NOW(), ?, ?, ?)";
            
            $stmtLog = $db->prepare($sqlLog);
            $stmtLog->execute([
                $idCurso, 
                $tipoMovimiento, 
                $_SESSION['user_id'], 
                $datos
            ]);

            // Si las dos consultas se completaron sin excepciones, confirmamos cambios
            $db->commit();

            $resultado['estado'] = TRUE;
            $resultado['idCurso'] = $idCurso;
            $resultado['mensaje'] = 'EL CURSO HA SIDO FINALIZADO Y GUARDADO';
            $resultado['clase'] = 'alert alert-success'; 
            $resultado['icono'] = 'glyphicon glyphicon-ok'; 

        } catch (PDOException $e) {
            // En caso de cualquier error, cancelamos la transacción de manera segura
            if (isset($db) && $db->inTransaction()) {
                $db->rollBack();
            }
            
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "Error al finalizar el curso en la base de datos: " . $e->getMessage();
            $resultado['clase'] = 'alert alert-danger'; 
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }     
        
        return $resultado;
    }

    public function tesoreriaCurso($idCurso, $valorCuotaLiquidacion, $porcentajeRetencionColegio){
        $resultado = array();
        try {
            // Verificar entrada básica antes de abrir la transacción
            if (!isset($idCurso) || $idCurso == "") {
                $resultado['estado'] = FALSE;
                $resultado['mensaje'] = "Error finalizando curso -> Ingreso incorrecto";
                $resultado['clase'] = 'alert alert-danger'; 
                $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
                return $resultado;
            }

            // Obtener la conexión PDO instanciada
            $db = Database::getConnection();
            $db->exec("SET NAMES 'utf8'");
            
            // Iniciar la transacción nativa en PDO
            $db->beginTransaction();

            // 1. Actualizar el estado del curso a Finalizado ('F')
            $sql = "UPDATE cursos 
                    SET ValorCuotaLiquidacion = ?, 
                        PorcentajeRetencionColegio = ? 
                    WHERE Id = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$valorCuotaLiquidacion, $porcentajeRetencionColegio, $idCurso]);

            $db->commit();

            $resultado['estado'] = TRUE;
            $resultado['idCurso'] = $idCurso;
            $resultado['mensaje'] = 'EL CURSO HA SIDO GUARDADO';
            $resultado['clase'] = 'alert alert-success'; 
            $resultado['icono'] = 'glyphicon glyphicon-ok'; 

        } catch (PDOException $e) {
            // En caso de cualquier error, cancelamos la transacción de manera segura
            if (isset($db) && $db->inTransaction()) {
                $db->rollBack();
            }
            
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "Error al guardar el curso en la base de datos: " . $e->getMessage();
            $resultado['clase'] = 'alert alert-danger'; 
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }     
        
        return $resultado;
    }

    public function guardarCuotaCurso($idCursoCuota, $idCurso, $cuota, $detalleCuota, $importe, $fechaVencimiento, $datosAnteriores){
        $resultado = array();
        try {
            // Obtener la conexión PDO instanciada
            $db = Database::getConnection();
            $db->exec("SET NAMES 'utf8'");
            
            // Iniciar la transacción de forma nativa en PDO
            $db->beginTransaction();
            
            $esModificacion = (isset($idCursoCuota) && $idCursoCuota <> "");

            if ($esModificacion) {
                $sql = "UPDATE cursoscuotas 
                        SET Cuota = ?, DetalleCuota = ?, Importe = ?, FechaVencimiento = ?
                        WHERE Id = ?";
                $stmt = $db->prepare($sql);
                
                // Ejecución con parámetros ordenados en un array plano
                $stmt->execute([$cuota, $detalleCuota, $importe, $fechaVencimiento, $idCursoCuota]);
                
                $tipoMovimiento = 'modificacion';
            } else {
                $sql = "INSERT INTO cursoscuotas (IdCursos, Cuota, DetalleCuota, Importe, FechaVencimiento)
                        VALUES (?, ?, ?, ?, ?)";
                $stmt = $db->prepare($sql);
                
                $stmt->execute([$idCurso, $cuota, $detalleCuota, $importe, $fechaVencimiento]);
                
                // Obtener el ID autoincremental recién generado para la cuota
                $idCursoCuota = $db->lastInsertId();
                $datosAnteriores = array();
                $tipoMovimiento = 'alta';
            }

            // Preparar y registrar el log de la cuota del curso
            $datos = serialize($datosAnteriores);
            $sqlLog = "INSERT INTO log_cursos (Tabla, IdTabla, Fecha, TipoMovimiento, IdUsuario, Datos) 
                       VALUES ('cursoscuotas', ?, NOW(), ?, ?, ?)";
            
            $stmtLog = $db->prepare($sqlLog);
            $stmtLog->execute([
                $idCursoCuota, 
                $tipoMovimiento, 
                $_SESSION['user_id'], 
                $datos
            ]);

            // Confirmamos los cambios de manera definitiva si no hubo excepciones
            $db->commit();

            $resultado['estado'] = TRUE;
            $resultado['idCursoCuota'] = $idCursoCuota;
            $resultado['mensaje'] = 'LA CUOTA DEL CURSO HA SIDO GUARDADA';
            $resultado['clase'] = 'alert alert-success'; 
            $resultado['icono'] = 'glyphicon glyphicon-ok'; 

        } catch (PDOException $e) {
            // Ante cualquier error, revertimos la transacción de manera segura
            if (isset($db) && $db->inTransaction()) {
                $db->rollBack();
            }
            
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "Error guardando cuota del curso en la base de datos: " . $e->getMessage();
            $resultado['clase'] = 'alert alert-danger'; 
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }     
        
        return $resultado;
    }

    public function obtenerCuotaCursoPorIdCursoCuota($idCursoCuota) {
        try {
            // Obtener la conexión PDO instanciada
            $db = Database::getConnection();
            $db->exec("SET NAMES 'utf8'");
            
            $sql = "SELECT Id, IdCursos, Cuota, DetalleCuota, Importe, FechaVencimiento
                    FROM cursoscuotas
                    WHERE Id = :idCursoCuota";
                    
            $stmt = $db->prepare($sql);
            
            // Vinculamos el parámetro como entero de forma segura
            $stmt->bindValue(':idCursoCuota', $idCursoCuota, PDO::PARAM_INT);
            $stmt->execute();
            
            $resultado = array();

            // Obtenemos la fila directamente como un array asociativo
            $row_db = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row_db) {
                $datos = array (
                    'idCursoCuota'     => $row_db['Id'],
                    'idCurso'          => $row_db['IdCursos'],
                    'cuota'            => $row_db['Cuota'],
                    'detalleCuota'     => $row_db['DetalleCuota'], // Se agrega el campo faltante del código original
                    'importe'          => $row_db['Importe'],
                    'fechaVencimiento' => $row_db['FechaVencimiento']
                );
                
                $resultado['estado'] = TRUE;
                $resultado['mensaje'] = "OK";
                $resultado['datos'] = $datos;
                $resultado['clase'] = 'alert alert-success'; 
                $resultado['icono'] = 'glyphicon glyphicon-ok'; 
            } else {
                // Manejo por si el ID de la cuota no existe en el sistema
                $resultado['estado'] = FALSE;
                $resultado['mensaje'] = "No se encontró la cuota del curso solicitada";
                $resultado['clase'] = 'alert alert-warning'; 
                $resultado['icono'] = 'glyphicon glyphicon-warning-sign';
            }
            
        } catch (PDOException $e) {
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "Error buscando la cuota del curso: " . $e->getMessage();
            $resultado['clase'] = 'alert alert-danger'; 
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }

        return $resultado;
    }

    public function borrarCuotaCurso($idCursoCuota, $datosAnteriores) {
        $resultado = array();
        try {
            // Obtener la conexión PDO instanciada
            $db = Database::getConnection();
            $db->exec("SET NAMES 'utf8'");
            
            // Iniciar la transacción nativa en PDO
            $db->beginTransaction();

            // 1. Ejecutar la eliminación física de la cuota
            $sql = "DELETE FROM cursoscuotas WHERE Id = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$idCursoCuota]);

            // 2. Registrar la auditoría del borrado en la tabla log_cursos
            $tipoMovimiento = 'borrar';
            $datos = serialize($datosAnteriores);
            
            $sqlLog = "INSERT INTO log_cursos (Tabla, IdTabla, Fecha, TipoMovimiento, IdUsuario, Datos) 
                       VALUES ('cursoscuotas', ?, NOW(), ?, ?, ?)";
            
            $stmtLog = $db->prepare($sqlLog);
            $stmtLog->execute([
                $idCursoCuota, 
                $tipoMovimiento, 
                $_SESSION['user_id'], 
                $datos
            ]);

            // Si ambas consultas se ejecutan sin lanzar excepciones, confirmamos los cambios
            $db->commit();

            $resultado['estado'] = TRUE;
            $resultado['mensaje'] = 'LA CUOTA DEL CURSO HA SIDO BORRADA';
            $resultado['clase'] = 'alert alert-success'; 
            $resultado['icono'] = 'glyphicon glyphicon-ok'; 

        } catch (PDOException $e) {
            // En caso de cualquier error, cancelamos la transacción de manera segura
            if (isset($db) && $db->inTransaction()) {
                $db->rollBack();
            }
            
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "Error al borrar la cuota del curso en la base de datos: " . $e->getMessage();
            $resultado['clase'] = 'alert alert-danger'; 
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }     
        
        return $resultado;
    }

    public function actualizarCuotasAsistentesPorCurso($idCurso) {
        $resultado = array();
        try {
            // Obtener la conexión PDO instanciada
            $db = Database::getConnection();
            $db->exec("SET NAMES 'utf8'");
            
            // Iniciar la transacción de forma nativa en PDO
            $db->beginTransaction();

            // 1. Insertar cuotas faltantes (Sincronización inicial)
            $sqlInsert = "INSERT INTO cursosasistentecuotas (IdCursosAsistente, Cuota, Importe, FechaVencimiento, DetalleCuota) 
                          (SELECT ca.Id, cc.Cuota, cc.Importe, cc.FechaVencimiento, cc.DetalleCuota
                           FROM cursoscuotas cc
                           INNER JOIN cursosasistente ca ON ca.IdCursos = cc.IdCursos
                           LEFT JOIN cursosasistentecuotas cac ON cac.IdCursosAsistente = ca.Id AND cac.Cuota = cc.Cuota
                           WHERE cc.IdCursos = ? AND cac.Id IS NULL)";
            
            $stmtInsert = $db->prepare($sqlInsert);
            $stmtInsert->execute([$idCurso]);

            // 2. Actualizar importes, vencimientos o detalles en cuotas no pagadas que hayan cambiado
            $sqlUpdate = "UPDATE cursosasistentecuotas AS a
                          INNER JOIN cursosasistente ca ON ca.Id = a.IdCursosAsistente
                          INNER JOIN cursoscuotas cc ON cc.IdCursos = ca.IdCursos AND a.Cuota = cc.Cuota
                          SET a.FechaVencimiento = cc.FechaVencimiento, a.Importe = cc.Importe, a.DetalleCuota = cc.DetalleCuota
                          WHERE cc.IdCursos = ? 
                            AND (a.FechaPago IS NULL OR a.FechaPago = '0000-00-00')
                            AND (a.FechaVencimiento <> cc.FechaVencimiento OR a.Importe <> cc.Importe OR a.DetalleCuota <> cc.DetalleCuota)";
            
            $stmtUpdate = $db->prepare($sqlUpdate);
            $stmtUpdate->execute([$idCurso]);

            // Si ambas operaciones se ejecutan sin lanzar excepciones, confirmamos los cambios
            $db->commit();

            $resultado['estado'] = TRUE;
            $resultado['mensaje'] = 'LAS CUOTAS FUERON ACTUALIZADAS CON ÉXITO';
            $resultado['clase'] = 'alert alert-success'; 
            $resultado['icono'] = 'glyphicon glyphicon-ok'; 

        } catch (PDOException $e) {
            // Revertir cualquier cambio pendiente ante un error en la base de datos
            if (isset($db) && $db->inTransaction()) {
                $db->rollBack();
            }
            
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "Error procesando la actualización de cuotas: " . $e->getMessage();
            $resultado['clase'] = 'alert alert-danger'; 
            $resultado['icono'] = 'glyphicon glyphicon-remove';
        }     
        
        return $resultado;
    }

    public function obtenerAsistenteCursoColegiado($idCurso, $idColegiado) {
        try {
            // Obtener la conexión PDO instanciada
            $db = Database::getConnection();
            $db->exec("SET NAMES 'utf8'");
            
            $sql = "SELECT ca.Id, ca.ApellidoNombre, ca.Estado, ca.FechaCarga
                    FROM cursosasistente ca
                    WHERE ca.IdCursos = :idCurso 
                      AND ca.IdColegiado = :idColegiado 
                      AND ca.Borrado = 0";
                      
            $stmt = $db->prepare($sql);
            
            // Vincular los parámetros controlando estrictamente que sean enteros
            $stmt->bindValue(':idCurso', $idCurso, PDO::PARAM_INT);
            $stmt->bindValue(':idColegiado', $idColegiado, PDO::PARAM_INT);
            $stmt->execute();
            
            $resultado = array();

            // Obtener la fila directamente como un array asociativo
            $row_db = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row_db) {
                $datos = array (
                    'idAsistente'    => $row_db['Id'],
                    'apellidoNombre' => $row_db['ApellidoNombre'],
                    'asiste'         => $row_db['Estado'],
                    'fechaCarga'     => $row_db['FechaCarga']
                );
                
                $resultado['estado'] = TRUE;
                $resultado['mensaje'] = "OK";
                $resultado['datos'] = $datos;
                $resultado['clase'] = 'alert alert-success'; 
                $resultado['icono'] = 'glyphicon glyphicon-ok'; 
            } else {
                $resultado['estado'] = FALSE;
                $resultado['mensaje'] = "No existe colegiado registrado en este curso";
                $resultado['clase'] = 'alert alert-info'; // Cambiado a alert-info por semántica al ser una búsqueda vacía
                $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
            }
            
        } catch (PDOException $e) {
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "Error buscando colegiado en el curso: " . $e->getMessage();
            $resultado['clase'] = 'alert alert-danger'; 
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }

        return $resultado;    
    }

    public function obtenerTotalCobranzaPorPerido($idCurso, $fechaDesde, $fechaHasta) {
        try {
            // Obtener la conexión PDO desde tu clase estática
            $conect = Database::getConnection();
            
            // Asegurar codificación UTF-8
            $conect->exec("SET NAMES utf8");

            // Base de la consulta SQL con alias claros
            $sql = "SELECT c.titulo AS nombreCurso, 
                           SUBSTR(a.fechapago, 1, 4) AS anioPago, 
                           SUBSTR(a.fechapago, 6, 2) AS mesPago, 
                           COUNT(a.id) AS cantidadPagos, 
                           SUM(a.importe) AS importePagado, 
                           c.id AS idCurso
                    FROM cursosasistentecuotas a
                    INNER JOIN cursosasistente b ON b.id = a.idcursosasistente
                    INNER JOIN cursos c ON c.id = b.idcursos AND c.estado = 'A'
                    WHERE a.fechapago BETWEEN ? AND ?
                    AND a.fechapago <> 0";

            // Arreglo dinámico de parámetros para execute()
            $params = [$fechaDesde, $fechaHasta];

            // Filtro condicional por curso
            if (isset($idCurso) && $idCurso !== "") {
                $sql .= " AND c.Id = ?";
                $params[] = $idCurso;
            }

            $sql .= " GROUP BY c.titulo, SUBSTR(a.fechapago, 1, 4), SUBSTR(a.fechapago, 6, 2), c.id
                      ORDER BY c.Id, SUBSTR(a.fechapago, 1, 4), SUBSTR(a.fechapago, 6, 2)";

            $stmt = $conect->prepare($sql);
            $stmt->execute($params);
            
            // PDO mapea los alias de SQL directamente a las llaves del arreglo asociativo
            $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'estado' => TRUE,
                'mensaje' => 'OK',
                'datos' => $datos,
                'cantidad' => count($datos),
                'clase' => 'alert alert-success',
                'icono' => 'glyphicon glyphicon-ok'
            ];

        } catch (PDOException $e) {
            return [
                'estado' => FALSE,
                'mensaje' => 'Error buscando cuotas a pagar: ' . $e->getMessage(),
                'clase' => 'alert alert-danger',
                'icono' => 'glyphicon glyphicon-exclamation-sign',
                'datos' => []
            ];
        }
    }

    public function obtenerDetalleCobranzaPorPerido($idCurso, $fechaDesde, $fechaHasta, $anioPago, $mesPago) {
        try {
            // Obtener la conexión PDO desde tu clase estática
            $conect = Database::getConnection();
            
            // Asegurar codificación UTF-8
            $conect->exec("SET NAMES utf8");

            // Armar el periodo cobrado (AAAA-MM)
            $periodoCobrado = $anioPago . '-' . $mesPago;

            // Consulta SQL con alias que coinciden exactamente con las llaves deseadas
            $sql = "SELECT b.ApellidoNombre AS apellidoNombre, 
                           a.FechaPago AS fechaPago, 
                           a.Importe AS importe, 
                           a.Recibo AS recibo, 
                           a.DetalleCuota AS detalleCuota
                    FROM cursosasistentecuotas a
                    INNER JOIN cursosasistente b ON b.Id = a.IdCursosAsistente
                    INNER JOIN cursos c ON c.Id = b.IdCursos AND c.Estado = 'A'
                    WHERE c.Id = ?
                      AND a.FechaPago BETWEEN ? AND ?
                      AND ? = SUBSTR(a.FechaPago, 1, 7)
                    ORDER BY b.ApellidoNombre, YEAR(a.FechaPago), MONTH(a.FechaPago)";

            $stmt = $conect->prepare($sql);
            
            // Ejecutar pasando el arreglo de parámetros en el mismo orden de los signos '?'
            $stmt->execute([$idCurso, $fechaDesde, $fechaHasta, $periodoCobrado]);
            
            // PDO extrae los datos directamente con las llaves correctas gracias a los alias
            $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'estado' => TRUE,
                'mensaje' => 'OK',
                'datos' => $datos,
                'cantidad' => count($datos),
                'clase' => 'alert alert-success',
                'icono' => 'glyphicon glyphicon-ok'
            ];

        } catch (PDOException $e) {
            return [
                'estado' => FALSE,
                'mensaje' => 'Error buscando cuotas a pagar: ' . $e->getMessage(),
                'clase' => 'alert alert-danger',
                'icono' => 'glyphicon glyphicon-exclamation-sign',
                'datos' => []
            ];
        }
    }

    public function obtenerDocentesLiquidarPorCursos($placeholders, $input) {
        $conect = Database::getConnection();
            
        // Asegurar codificación UTF-8
        $conect->exec("SET NAMES utf8");

        if (!empty($input['ids'])) {
            // Consulta SQL uniendo la tabla intermedia de asignación de cursos con los datos de los docentes
            $sql = "SELECT DISTINCT d.Id, d.ApellidoNombres
                    FROM cursosdocente cd
                    INNER JOIN docente_cursos d ON cd.IdDocenteCursos = d.Id
                    WHERE cd.IdCursos IN ($placeholders) AND cd.Borrado = 0";
                    
            $stmt = $conect->prepare($sql);
            $stmt->execute($input['ids']);
            $docentes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $docentes = array();
        }
        return $docentes;
    }    

    public function guardarDocente($id_docente, $id_colegiado, $apellido_nombre, $borrado) {
        // 1. Obtener la conexión PDO
        $db = Database::getConnection();
        $resultado = array();
        
        try {
            // 2. Iniciar la transacción de manera nativa en PDO
            $db->beginTransaction();

            $id_usuario = isset($_SESSION['user_id']) ? $_SESSION['user_id'] :  49; //49(tramites web)

            if (isset($id_docente) && $id_docente <> "") {
                // Caso UPDATE
                $sql = "UPDATE docente_cursos
                        SET IdColegiado = :id_colegiado, 
                            ApellidoNombres = :apellido_nombre, 
                            Borrado = :borrado, 
                            FechaCarga = DATE(NOW()), 
                            IdUsuario = :id_usuario
                        WHERE Id = :id_docente";
                
                $stmt = $db->prepare($sql);
                $stmt->execute([
                    ':id_colegiado'       => $id_colegiado,
                    ':apellido_nombre'     => $apellido_nombre,
                    ':borrado'            => $borrado,
                    ':id_usuario'          => $id_usuario,
                    ':id_docente'  => $id_docente
                ]);
                
                $resultado['estado'] = TRUE;
                $resultado['id_docente'] = $id_docente;
                $resultado['mensaje'] = 'EL DOCENTE HA SIDO MODIFICADO SATISFACTORIAMENTE.';
                $resultado['clase'] = 'alert alert-success'; 
                $resultado['icono'] = 'glyphicon glyphicon-ok'; 

            } else {
                // Caso INSERT
                $sql = "INSERT INTO docente_cursos (IdColegiado, ApellidoNombres, FechaCarga, IdUsuario)
                        VALUES (:id_colegiado, :apellido_nombre, NOW(), :id_usuario)";
                
                $stmt = $db->prepare($sql);
                $stmt->execute([
                    ':id_colegiado'   => $id_colegiado,
                    ':apellido_nombre'    => $apellido_nombre,
                    ':id_usuario' => $id_usuario
                ]);
                
                // Obtener el ID generado por el INSERT usando PDO
                $id_docente = $db->lastInsertId();
                $resultado['estado'] = TRUE;
                $resultado['id_docente'] = $id_docente;
                $resultado['mensaje'] = 'EL DOCENTE HA SIDO AGREGADO SATISFACTORIAMENTE.';
                $resultado['clase'] = 'alert alert-success'; 
                $resultado['icono'] = 'glyphicon glyphicon-ok';
            }

            // 3. Confirmar la transacción si todo fue exitoso
            $db->commit();

        } catch (PDOException $e) {
            // 4. Cancelar la transacción en caso de cualquier error SQL
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "Error guardando docente -> " . $e->getMessage();
            $resultado['clase'] = 'alert alert-danger'; 
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }     

        return $resultado;
    }
}
