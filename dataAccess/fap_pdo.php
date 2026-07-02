<?php
class fap_pdo {
    const CONSULTA = 'C';
    const APROBADO = 'A';
    const DESAPROBADO = 'D';
    const EN_SISTEMA = 'E';
    const CERRADO = 'R';
    const LITIGAR_SIN_GASTO = 'G';
    const MEDIACION = 'M';
    const EN_REVISION = 'V';

    //saptipotramite
    const TIPO_TRAMITE_CONSULTA = 1;
    const TIPO_TRAMITE_MEDIACION = 2;
    const TIPO_TRAMITE_LITIGAR_SIN_GASTO = 3;
    const TIPO_TRAMITE_CAUSA = 4;

    //sapestado
    const ESTADO_APROBADO = 1;
    const ESTADO_DESAPROBADO = 2;
    const ESTADO_EN_REVISION = 3;

    //saptipocausa
    const TIPO_CAUSA_CIVIL_COMERCIAL = 1;
    const TIPO_CAUSA_PENAL = 2;
    const TIPO_CAUSA_CONTENCIOSO_ADMINISTRATIVO = 3;
    const TIPO_CAUSA_MEDIACION = 5;
    const TIPO_CAUSA_ADMINISTRATIVO = 7;

    //reunion_estado
    const ESTADO_REUNION_ABIERTA = 'A';
    const ESTADO_REUNION_CERRADA = 'C';
    
    function obtenerSapCaratulaPorId($idSapCaratula) {
        try {
            // Se asume que conectar() ahora retorna un objeto PDO
            $db = Database::getConnection();

            $sql = "SELECT sc.Id, sc.FechaRecepcion, sc.FechaIngreso, sc.NombreCausa, sc.Abogados, 
                           sc.Juzgado as IdJuzgado, j.Nombre AS NombreJuzgado, sc.IdTipoCausa, 
                           tc.Nombre AS NombreTipoCausa, sc.DepartamentoJudicial, 
                           dj.Nombre AS NombreDepartamentoJudicial, sc.Estado, sc.TipoSistema, 
                           sc.FechaHecho, sc.LugarHecho, sc.Ambito, sc.Especialidad, sc.Condicion, 
                           sc.CaratulaDefinitiva, sc.DomicilioHecho, sc.TelefonoHecho, 
                           sc.FechaNotificacion, sc.LugarNotificacion, sc.Recepcion, 
                           sc.TieneCobertura, sc.NombreCobertura, sc.CoberturaDesde, sc.Edad, 
                           sc.Sexo, sc.DomicilioReal, sc.DomicilioProfesional, sc.TelefonoParticular, 
                           sc.Celular, sc.Mail, sc.ConCedula, sc.ConFotoDemanda, sc.Recepciono, 
                           sc.FechaResolucion, sc.NumeroResolucion, sc.NumeroCausa, sc.Observaciones, 
                           sc.IdColegiado, c.Matricula, p.Apellido, p.Nombres, 
                           (SELECT GROUP_CONCAT(DISTINCT e.Especialidad SEPARATOR ' - ') 
                            FROM especialidad e 
                            INNER JOIN colegiadoespecialista ce ON ce.Especialidad = e.Id 
                            WHERE ce.IdColegiado = c.Id) AS Especialidades, 
                           sc.IdSapEstado, se.Nombre AS NombreSapEstado, sc.IdSapTipoTramite, 
                           stt.Nombre AS NombreSapTipoTramite, sc.IdSapCondicion, 
                           sco.Nombre AS NombreSapCondicion, sc.InscriptoDistrito
                    FROM sapcaratula sc 
                    LEFT JOIN juzgado j ON j.Id = sc.Juzgado
                    LEFT JOIN tipocausa tc ON tc.Id = sc.IdTipoCausa
                    LEFT JOIN departamentojudicial dj ON dj.Id = sc.DepartamentoJudicial
                    LEFT JOIN sapestado se ON se.Id = sc.IdSapEstado
                    LEFT JOIN saptipotramite stt ON stt.Id = sc.IdSapTipoTramite
                    LEFT JOIN sapcondicion sco ON sco.Id = sc.IdSapCondicion
                    INNER JOIN colegiado c ON c.Id = sc.IdColegiado
                    INNER JOIN persona p ON p.Id = c.IdPersona
                    WHERE sc.Id = :id";

            $stmt = $db->prepare($sql);
            $stmt->bindParam(':id', $idSapCaratula, PDO::PARAM_INT);
            $stmt->execute();

            // fetch(PDO::FETCH_ASSOC) nos devuelve un array asociativo directamente
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row) {
                // Manejo de especialidades nulas
                if (empty($row['Especialidades'])) {
                    $row['Especialidades'] = 'No tiene.';
                }

                return [
                    'estado' => true,
                    'mensaje' => "OK",
                    'datos' => $row, // PDO ya nos da el array con los nombres de las columnas
                    'clase' => 'alert alert-success',
                    'icono' => 'glyphicon glyphicon-ok'
                ];
            } else {
                return [
                    'estado' => false,
                    'datos' => null,
                    'mensaje' => "No se encontró carátula del FAP",
                    'clase' => 'alert alert-warning',
                    'icono' => 'glyphicon glyphicon-exclamation-sign'
                ];
            }

        } catch (PDOException $e) {
            // Es buena práctica loguear el error real: error_log($e->getMessage());
            return [
                'estado' => false,
                'mensaje' => "Error buscando carátula del FAP",
                'clase' => 'alert alert-danger',
                'icono' => 'glyphicon glyphicon-remove'
            ];
        }
    }

    function obtenerFapCaratulasPorPeriodoEstado($periodoSeleccionado, $estadoSapCaratula, $idColegiado, $caratula_buscar) {
        try {
            $db = Database::getConnection();
            
            $params = [];
            $conditions = [];

            // 1. Filtro por Colegiado o Nombre de Causa
            if (!empty($idColegiado)) {
                $conditions[] = "sc.IdColegiado = :idColegiado";
                $params[':idColegiado'] = $idColegiado;
            } elseif (!empty($caratula_buscar)) {
                $conditions[] = "sc.NombreCausa LIKE :buscar";
                $params[':buscar'] = "%$caratula_buscar%";
            }

            // 2. Filtro por Periodo (Año)
            if (!empty($periodoSeleccionado) && $periodoSeleccionado <> '9999') {
                $conditions[] = "YEAR(sc.FechaIngreso) = :periodo";
                $params[':periodo'] = $periodoSeleccionado;
            }

            // 3. Filtro por Estado
            if (!empty($estadoSapCaratula) && $estadoSapCaratula <> "0") {
                switch ($estadoSapCaratula) {
                    case '1':
                        $conditions[] = "sc.Estado IN (:aprobado, :enSistema)";
                        $params[':aprobado'] = self::APROBADO;
                        $params[':enSistema'] = self::EN_SISTEMA;
                        break;
                    case '2':
                        $conditions[] = "sc.Estado = :litigar";
                        $params[':litigar'] = self::LITIGAR_SIN_GASTO;
                        break;
                    case '3':
                        $conditions[] = "sc.Estado = :mediacion";
                        $params[':mediacion'] = self::MEDIACION;
                        break;
                    case '4':
                        $conditions[] = "sc.Estado = :consulta";
                        $params[':consulta'] = self::CONSULTA;
                        break;
                }
            }

            // Construir el WHERE final
            $whereSQL = !empty($conditions) ? " WHERE " . implode(" AND ", $conditions) : "";

            $sql = "SELECT sc.Id AS IdSapCaratula, sc.IdColegiado, sc.Matricula, p.Apellido, p.Nombres, sc.FechaRecepcion, sc.FechaIngreso, sc.NombreCausa, sc.Abogados, sc.Juzgado, j.Nombre AS NombreJuzgado, sc.IdTipoCausa, tc.Nombre AS NombreTipoCausa, sc.DepartamentoJudicial, dj.Nombre AS NombreDepartamentoJudicial, sc.Estado, sc.TipoSistema, sc.FechaHecho, sc.LugarHecho, sc.Ambito, sc.Especialidad, sc.CaratulaDefinitiva, sc.DomicilioHecho, sc.TelefonoHecho, sc.FechaNotificacion, sc.LugarNotificacion, sc.Recepcion, sc.InscriptoDistrito, sc.TieneCobertura, sc.NombreCobertura, sc.CoberturaDesde, sc.Edad, sc.Sexo, sc.DomicilioReal, sc.DomicilioProfesional, sc.DomicilioNotificacion, sc.TelefonoParticular, sc.Celular, sc.Mail, sc.ConCedula, sc.ConFotoDemanda, sc.Recepciono, sc.FechaResolucion, sc.NumeroResolucion, sc.NumeroCausa, sc.Observaciones, scon.FechaReunion, sc.Id AS NumeroSAP, sc.IdSapEstado, se.Nombre AS NombreSapEstado , sc.IdSapTipoTramite, stt.Nombre AS NombreTipoTramite, sc.IdSapCondicion, sco.Nombre AS NombreCondicion
                    FROM sapcaratula sc
                    INNER JOIN colegiado c ON c.Id = sc.IdColegiado
                    INNER JOIN persona p ON p.Id = c.IdPersona
                    LEFT JOIN sapestado se ON se.Id = sc.IdSapEstado
                    LEFT JOIN saptipotramite stt ON stt.Id = sc.IdSapTipoTramite
                    LEFT JOIN sapcondicion sco ON sco.Id = sc.IdSapCondicion
                    LEFT JOIN juzgado j ON j.Id = sc.Juzgado
                    LEFT JOIN tipocausa tc ON tc.Id = sc.IdTipoCausa
                    LEFT JOIN departamentojudicial dj ON dj.Id = sc.DepartamentoJudicial
                    LEFT JOIN sapaconsejodetalle scd ON scd.IdSAP = sc.Id
                    LEFT JOIN sapaconsejo scon ON scon.Id = scd.IdSAPaconsejo
                    $whereSQL
                    ORDER BY sc.Id DESC";

            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            
            $datos = $stmt->fetchAll(); // Obtiene todas las filas como array asociativo

            if (count($datos) > 0) {
                return [
                    'estado' => true,
                    'mensaje' => "OK",
                    'datos' => $datos,
                    'clase' => 'alert alert-success',
                    'icono' => 'glyphicon glyphicon-ok'
                ];
            }

            return [
                'estado' => false,
                'datos' => null,
                'mensaje' => "No se encontraron carátulas del FAP",
                'clase' => 'alert alert-warning',
                'icono' => 'glyphicon glyphicon-exclamation-sign'
            ];

        } catch (PDOException $e) {
            return [
                'estado' => false,
                'mensaje' => "Error buscando carátulas del FAP",
                'clase' => 'alert alert-danger',
                'icono' => 'glyphicon glyphicon-remove'
            ];
        }
    }

    function obtenerSapReunionPorId($idSapConsejo) {
        try {
            // Usamos la conexión centralizada
            $db = Database::getConnection();

            $sql = "SELECT sac.Id as id, 
                           sac.FechaReunion as fechaReunion, 
                           sac.Resolucion as resolucion, 
                           sac.EstadoReunion as estadoReunion, 
                           sac.Observaciones as observaciones
                    FROM sapaconsejo sac
                    WHERE sac.Id = :id";

            $stmt = $db->prepare($sql);
            // Vinculamos el parámetro por nombre
            $stmt->execute([':id' => $idSapConsejo]);
            
            // fetch() obtiene la fila directamente como array asociativo
            $row = $stmt->fetch();

            if ($row) {
                return [
                    'estado'  => true,
                    'mensaje' => "OK",
                    'datos'   => $row,
                    'clase'   => 'alert alert-success',
                    'icono'   => 'glyphicon glyphicon-ok'
                ];
            } else {
                return [
                    'estado'  => false,
                    'datos'   => null,
                    'mensaje' => "No se encontró reunión.",
                    'clase'   => 'alert alert-warning',
                    'icono'   => 'glyphicon glyphicon-exclamation-sign'
                ];
            }

        } catch (PDOException $e) {
            // En un entorno real, podrías registrar el error: error_log($e->getMessage());
            return [
                'estado'  => false,
                'mensaje' => "Error buscando reunión.",
                'clase'   => 'alert alert-danger',
                'icono'   => 'glyphicon glyphicon-remove'
            ];
        }
    }

    function obtenerSapReunionDetallePorId($idSapConsejoDetalle) {
        try {
            $db = Database::getConnection();

            $sql = "SELECT sacd.IdSAPaconsejo AS idSapConsejo, 
                           sacd.IdSAP AS idSapCaratula, 
                           sacd.Estado AS estado, 
                           sacd.FechaAprobacion AS fechaAprobacion, 
                           sacd.Observacion AS observacion
                    FROM sapaconsejodetalle sacd
                    WHERE sacd.Id = :id";

            $stmt = $db->prepare($sql);
            $stmt->execute([':id' => $idSapConsejoDetalle]);
            
            $row = $stmt->fetch();

            if ($row) {
                return [
                    'estado' => true,
                    'mensaje' => "OK",
                    'datos' => $row,
                    'clase' => 'alert alert-success',
                    'icono' => 'glyphicon glyphicon-ok'
                ];
            } else {
                return [
                    'estado' => false,
                    'datos' => null,
                    'mensaje' => "No se encontró detalle de la reunión.",
                    'clase' => 'alert alert-warning',
                    'icono' => 'glyphicon glyphicon-exclamation-sign'
                ];
            }

        } catch (PDOException $e) {
            return [
                'estado' => false,
                'mensaje' => "Error buscando detalle de la reunión.",
                'clase' => 'alert alert-danger',
                'icono' => 'glyphicon glyphicon-remove'
            ];
        }
    }

    function obtenerReunionesPorPeriodo($periodoSeleccionado, $estadoSeleccionado) {
        try {
            $db = Database::getConnection();
            
            $params = [];
            $conditions = [];

            // 1. Filtro por Estado (Si no es 'T' de Todos)
            if (!empty($estadoSeleccionado) && $estadoSeleccionado <> 'T') {
                $conditions[] = "sac.EstadoReunion = :estado";
                $params[':estado'] = $estadoSeleccionado;
            }

            // 2. Filtro por Periodo
            if (!empty($periodoSeleccionado) && $periodoSeleccionado <> '9999') {
                $conditions[] = "YEAR(sac.FechaReunion) = :periodo";
                $params[':periodo'] = $periodoSeleccionado;
            }

            // Construir el WHERE
            $whereSQL = !empty($conditions) ? " WHERE " . implode(" AND ", $conditions) : "";

            $sql = "SELECT sac.Id AS idSapConsejo, 
                           sac.FechaReunion AS fechaReunion, 
                           sac.Resolucion AS resolucion, 
                           sac.EstadoReunion AS estadoReunion, 
                           sac.Observaciones AS observaciones
                    FROM sapaconsejo sac
                    $whereSQL
                    ORDER BY sac.Id DESC";

            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            
            // Obtenemos todas las filas
            $rows = $stmt->fetchAll();

            if (count($rows) > 0) {
                // Procesamos los nombres de los estados
                foreach ($rows as &$row) {
                    $estados = [
                        'A' => 'Abierta',
                        'C' => 'Cerrada'
                    ];

                    $row['nombreEstadoReunion'] = isset($estados[$row['estadoReunion']]) 
                                                  ? $estados[$row['estadoReunion']] 
                                                  : 'Sin dato';
                }

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
                'datos' => null,
                'mensaje' => "No se encontraron reuniones de consejo del FAP",
                'clase' => 'alert alert-warning',
                'icono' => 'glyphicon glyphicon-exclamation-sign'
            ];

        } catch (PDOException $e) {
            return [
                'estado' => false,
                'mensaje' => "Error buscando reuniones de consejo del FAP",
                'clase' => 'alert alert-danger',
                'icono' => 'glyphicon glyphicon-remove'
            ];
        }
    }

    function obtenerReunionDetallePorIdReunion($idSapConsejo) {
        try {
            $db = Database::getConnection();

            $sql = "SELECT sacd.Id AS idSapConsejoDetalle, 
                           sacd.IdSAP AS idSapCaratula, 
                           sacd.Estado AS estado, 
                           sacd.FechaAprobacion AS fechaAprobacion, 
                           sacd.Observacion AS observaciones, 
                           c.Matricula AS matricula, 
                           p.Apellido AS apellido, 
                           p.Nombres AS nombre, 
                           se.Nombre AS nombreSapEstado, 
                           stt.Nombre AS nombreSapTipoTramite, 
                           tc.Nombre AS nombreTipoCausa, 
                           dj.Nombre AS nombreDepartamentoJudicial, 
                           sco.Nombre AS nombreSapCondicion, 
                           sc.NombreCausa AS nombreCausa
                    FROM sapaconsejodetalle sacd
                    INNER JOIN sapcaratula sc ON sc.Id = sacd.IdSAP
                    INNER JOIN tipocausa tc ON tc.Id = sc.IdTipoCausa
                    INNER JOIN sapestado se ON se.Id = sc.IdSapEstado
                    INNER JOIN saptipotramite stt ON stt.Id = sc.IdSapTipoTramite
                    INNER JOIN departamentojudicial dj ON dj.Id = sc.DepartamentoJudicial
                    INNER JOIN sapcondicion sco ON sco.Id = sc.IdSapCondicion
                    INNER JOIN colegiado c ON c.Id = sc.IdColegiado
                    INNER JOIN persona p ON p.Id = c.IdPersona
                    WHERE sacd.IdSAPaconsejo = :id
                    ORDER BY sc.Id";

            $stmt = $db->prepare($sql);
            $stmt->execute([':id' => $idSapConsejo]);
            
            $rows = $stmt->fetchAll();

            if (count($rows) > 0) {
                $orden = 1;
                foreach ($rows as &$row) {
                    // Agregar número de orden
                    $row['orden'] = $orden;
                    
                    // Mapeo de nombre de estado
                    $estados = [
                        'A' => 'Aprobado',
                        'D' => 'Desaprobado',
                        'P' => 'Pendiente',
                    ];

                    $row['nombreEstado'] = isset($estados[$row['estado']]) 
                                                  ? $estados[$row['estado']] 
                                                  : 'Sin dato';
                    
                    $orden++;
                }

                return [
                    'estado' => true,
                    'mensaje' => "OK",
                    'datos' => $rows,
                    'clase' => 'alert alert-success',
                    'icono' => 'glyphicon glyphicon-ok'
                ];
            } else {
                return [
                    'estado' => true, // Mantenemos TRUE según tu lógica original si la consulta no falla
                    'datos' => [],
                    'mensaje' => "No se encontró el detalle de la reunión.",
                    'clase' => 'alert alert-warning',
                    'icono' => 'glyphicon glyphicon-exclamation-sign'
                ];
            }

        } catch (PDOException $e) {
            return [
                'estado' => false,
                'mensaje' => "Error buscando detalle de la reunión.",
                'clase' => 'alert alert-danger',
                'icono' => 'glyphicon glyphicon-remove',
                'datos' => null
            ];
        }
    }

    function guardarFapReunionDetalle($accion, $idSapConsejo, $idSapConsejoDetalle, $idSapCaratula, $estado, $fechaAprobacion, $observacion, $datosAnteriores) {
        try {
            $db = Database::getConnection();
            
            // Iniciamos la transacción
            $db->beginTransaction();

            switch ($accion) {
                case 'agregar':
                    $sql = "INSERT INTO sapaconsejodetalle (IdSAPaconsejo, IdSAP, Estado, FechaAprobacion, Observacion)
                            VALUES (:idConsejo, :idCaratula, :estado, :fecha, :obs)";
                    $stmt = $db->prepare($sql);
                    $stmt->execute([
                        ':idConsejo'  => $idSapConsejo,
                        ':idCaratula' => $idSapCaratula,
                        ':estado'     => $estado,
                        ':fecha'      => $fechaAprobacion,
                        ':obs'        => $observacion
                    ]);
                    
                    // Obtenemos el ID generado para el log
                    $idSapConsejoDetalle = $db->lastInsertId();
                    $tipoMovimiento = 'alta';
                    $datosLog = serialize([]); // Datos vacíos para alta
                    break;
                
                case 'editar':
                    $sql = "UPDATE sapaconsejodetalle 
                            SET Estado = :estado, FechaAprobacion = :fecha, Observacion = :obs
                            WHERE Id = :id";
                    $stmt = $db->prepare($sql);
                    $stmt->execute([
                        ':estado' => $estado,
                        ':fecha'  => $fechaAprobacion,
                        ':obs'    => $observacion,
                        ':id'     => $idSapConsejoDetalle
                    ]);
                    
                    $tipoMovimiento = 'modificacion';
                    $datosLog = serialize($datosAnteriores);
                    break;

                case 'borrar':
                    $sql = "DELETE FROM sapaconsejodetalle WHERE Id = :id";
                    $stmt = $db->prepare($sql);
                    $stmt->execute([':id' => $idSapConsejoDetalle]);
                    
                    $tipoMovimiento = 'baja'; // Agregado para integridad
                    $datosLog = serialize($datosAnteriores);
                    break;

                default:
                    throw new Exception("Acción no válida");
            }

            // Guardamos el log de auditoría
            $sqlLog = "INSERT INTO log_sap (Tabla, IdTabla, Fecha, TipoMovimiento, IdUsuario, Datos) 
                       VALUES ('sapaconsejodetalle', :idTabla, NOW(), :movimiento, :idUsuario, :datos)";
            
            $stmtLog = $db->prepare($sqlLog);
            $stmtLog->execute([
                ':idTabla'    => $idSapConsejoDetalle,
                ':movimiento' => $tipoMovimiento,
                ':idUsuario'  => $_SESSION['user_id'], 
                ':datos'      => $datosLog
            ]);

            // Si todo salió bien, confirmamos los cambios
            $db->commit();

            return [
                'estado'  => true,
                'mensaje' => "FAP guardada correctamente en la reunión de consejo.",
                'clase'   => 'alert alert-success',
                'icono'   => 'glyphicon glyphicon-ok'
            ];

        } catch (Exception $e) {
            // Si algo falla, revertimos todo
            if (isset($db)) {
                $db->rollback();
            }

            return [
                'estado'  => false,
                'mensaje' => "Error guardando FAP de la Reunión -> " . $e->getMessage(),
                'clase'   => 'alert alert-danger',
                'icono'   => 'glyphicon glyphicon-remove'
            ];
        }
    }

    function agregarFapConsulta($matricula, $fechaRecepcion, $fechaIngreso, $nombreCausa, $estado, $tipoSistema, $edad, $sexo, $domicilioReal, $domicilioProfesional, $telefonoParticular, $mail, $recepciono, $observaciones, $idColegiado, $idSapTipoTramite) {
        try {
            $db = Database::getConnection();

            $sql = "INSERT INTO sapcaratula (
                        Matricula, FechaRecepcion, FechaIngreso, NombreCausa, Estado, 
                        TipoSistema, Edad, Sexo, DomicilioReal, DomicilioProfesional, 
                        TelefonoParticular, Mail, Recepciono, Observaciones, 
                        IdColegiado, IdSapTipoTramite
                    ) VALUES (
                        :matricula, :fechaRecepcion, :fechaIngreso, :nombreCausa, :estado, 
                        :tipoSistema, :edad, :sexo, :domicilioReal, :domicilioProfesional, 
                        :telefono, :mail, :recepciono, :observaciones, 
                        :idColegiado, :idSapTipoTramite
                    )";

            $stmt = $db->prepare($sql);
            
            $stmt->execute([
                ':matricula'            => $matricula,
                ':fechaRecepcion'       => $fechaRecepcion,
                ':fechaIngreso'         => $fechaIngreso,
                ':nombreCausa'          => $nombreCausa,
                ':estado'               => $estado,
                ':tipoSistema'          => $tipoSistema,
                ':edad'                 => $edad,
                ':sexo'                 => $sexo,
                ':domicilioReal'        => $domicilioReal,
                ':domicilioProfesional' => $domicilioProfesional,
                ':telefono'             => $telefonoParticular,
                ':mail'                 => $mail,
                ':recepciono'           => $recepciono,
                ':observaciones'        => $observaciones,
                ':idColegiado'          => $idColegiado,
                ':idSapTipoTramite'     => $idSapTipoTramite
            ]);

            return [
                'estado'         => true,
                'mensaje'        => "Consulta agregada correctamente.",
                'idSapCaratula'  => $db->lastInsertId(),
                'clase'          => 'alert alert-success',
                'icono'          => 'glyphicon glyphicon-ok'
            ];

        } catch (PDOException $e) {
            // Opcional: loguear el error $e->getMessage()
            return [
                'estado'  => false,
                'mensaje' => "Error agregando Consulta",
                'clase'   => 'alert alert-danger',
                'icono'   => 'glyphicon glyphicon-remove'
            ];
        }
    }

    function agregarFapCausa($matricula, $fechaRecepcion, $fechaIngreso, $nombreCausa, $abogados, $idJuzgado, $idTipoCausa, $idDepartamentoJudicial, $estado, $tipoSistema, $fechaHecho, $lugarHecho, $ambito, $especialidad, $caratulaDefinitiva, $domicilioHecho, $telefonoHecho, $fechaNotificacion, $lugarNotificacion, $recepcion, $tieneCobertura, $nombreCobertura, $coberturaDesde, $edad, $sexo, $domicilioReal, $domicilioProfesional, $telefonoParticular, $celular, $mail, $conCedula, $conFotoDemanda, $recepciono, $observaciones, $idColegiado, $inscriptoDistrito, $idSapTipoTramite, $idSapEstado, $idSapCondicion) {
        try {
            $db = Database::getConnection();

            $sql = "INSERT INTO sapcaratula (
                        Matricula, FechaRecepcion, FechaIngreso, NombreCausa, Abogados, 
                        Juzgado, IdTipoCausa, DepartamentoJudicial, Estado, TipoSistema, 
                        FechaHecho, LugarHecho, Ambito, Especialidad, CaratulaDefinitiva, 
                        DomicilioHecho, TelefonoHecho, FechaNotificacion, LugarNotificacion, 
                        Recepcion, TieneCobertura, NombreCobertura, CoberturaDesde, Edad, 
                        Sexo, DomicilioReal, DomicilioProfesional, TelefonoParticular, 
                        Celular, Mail, ConCedula, ConFotoDemanda, Recepciono, 
                        Observaciones, IdColegiado, InscriptoDistrito, IdSapTipoTramite, 
                        IdSapEstado, IdSapCondicion
                    ) VALUES (
                        :matricula, :fechaRecepcion, :fechaIngreso, :nombreCausa, :abogados, 
                        :idJuzgado, :idTipoCausa, :idDepartamentoJudicial, :estado, :tipoSistema, 
                        :fechaHecho, :lugarHecho, :ambito, :especialidad, :caratulaDefinitiva, 
                        :domicilioHecho, :telefonoHecho, :fechaNotificacion, :lugarNotificacion, 
                        :recepcion, :tieneCobertura, :nombreCobertura, :coberturaDesde, :edad, 
                        :sexo, :domicilioReal, :domicilioProfesional, :telefonoParticular, 
                        :celular, :mail, :conCedula, :conFotoDemanda, :recepciono, 
                        :observaciones, :idColegiado, :inscriptoDistrito, :idSapTipoTramite, 
                        :idSapEstado, :idSapCondicion
                    )";

            $stmt = $db->prepare($sql);
            
            $stmt->execute([
                ':matricula' => $matricula, ':fechaRecepcion' => $fechaRecepcion, ':fechaIngreso' => $fechaIngreso,
                ':nombreCausa' => $nombreCausa, ':abogados' => $abogados, ':idJuzgado' => $idJuzgado,
                ':idTipoCausa' => $idTipoCausa, ':idDepartamentoJudicial' => $idDepartamentoJudicial, ':estado' => $estado,
                ':tipoSistema' => $tipoSistema, ':fechaHecho' => $fechaHecho, ':lugarHecho' => $lugarHecho,
                ':ambito' => $ambito, ':especialidad' => $especialidad, ':caratulaDefinitiva' => $caratulaDefinitiva,
                ':domicilioHecho' => $domicilioHecho, ':telefonoHecho' => $telefonoHecho, ':fechaNotificacion' => $fechaNotificacion,
                ':lugarNotificacion' => $lugarNotificacion, ':recepcion' => $recepcion, ':tieneCobertura' => $tieneCobertura,
                ':nombreCobertura' => $nombreCobertura, ':coberturaDesde' => $coberturaDesde, ':edad' => $edad,
                ':sexo' => $sexo, ':domicilioReal' => $domicilioReal, ':domicilioProfesional' => $domicilioProfesional,
                ':telefonoParticular' => $telefonoParticular, ':celular' => $celular, ':mail' => $mail,
                ':conCedula' => $conCedula, ':conFotoDemanda' => $conFotoDemanda, ':recepciono' => $recepciono,
                ':observaciones' => $observaciones, ':idColegiado' => $idColegiado, ':inscriptoDistrito' => $inscriptoDistrito,
                ':idSapTipoTramite' => $idSapTipoTramite, ':idSapEstado' => $idSapEstado, ':idSapCondicion' => $idSapCondicion
            ]);

            return [
                'estado' => true,
                'mensaje' => "Caratula del FAP agregada correctamente.",
                'idSapCaratula' => $db->lastInsertId(),
                'clase' => 'alert alert-success',
                'icono' => 'glyphicon glyphicon-ok'
            ];

        } catch (PDOException $e) {
            return [
                'estado' => false,
                'mensaje' => "Error agregando caratula del FAP -> " . $e->getMessage(),
                'clase' => 'alert alert-danger',
                'icono' => 'glyphicon glyphicon-remove'
            ];
        }
    }

    function editarFapConsulta($idSapCaratula, $fechaRecepcion, $fechaIngreso, $nombreCausa, $edad, $sexo, $domicilioReal, $domicilioProfesional, $telefonoParticular, $celular, $mail, $recepciono, $observaciones, $matricula, $idColegiado, $datosAnteriores) {
        try {
            $db = Database::getConnection();
            
            // Iniciamos la transacción
            $db->beginTransaction();

            $sql = "UPDATE sapcaratula 
                    SET FechaRecepcion = :fechaRec, 
                        FechaIngreso = :fechaIng, 
                        NombreCausa = :nombre, 
                        Edad = :edad, 
                        Sexo = :sexo, 
                        DomicilioReal = :domReal, 
                        DomicilioProfesional = :domProf, 
                        TelefonoParticular = :telPart, 
                        Celular = :cel, 
                        Mail = :mail, 
                        Recepciono = :recep, 
                        Observaciones = :obs, 
                        Matricula = :mat, 
                        IdColegiado = :idCol
                    WHERE Id = :id";

            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':fechaRec' => $fechaRecepcion,
                ':fechaIng' => $fechaIngreso,
                ':nombre'   => $nombreCausa,
                ':edad'     => $edad,
                ':sexo'     => $sexo,
                ':domReal'  => $domicilioReal,
                ':domProf'  => $domicilioProfesional,
                ':telPart'  => $telefonoParticular,
                ':cel'      => $celular,
                ':mail'     => $mail,
                ':recep'    => $recepciono,
                ':obs'      => $observaciones,
                ':mat'      => $matricula,
                ':idCol'    => $idColegiado,
                ':id'       => $idSapCaratula
            ]);

            // Lógica de Log de Auditoría
            $tipoMovimiento = (!empty($idSapCaratula)) ? 'modificacion' : 'alta';
            $datosSerializados = serialize($datosAnteriores);

            $sqlLog = "INSERT INTO log_sap (Tabla, IdTabla, Fecha, TipoMovimiento, IdUsuario, Datos) 
                       VALUES ('sapcaratula', :idTabla, NOW(), :movimiento, :idUsuario, :datos)";
            
            $stmtLog = $db->prepare($sqlLog);
            $stmtLog->execute([
                ':idTabla'    => $idSapCaratula,
                ':movimiento' => $tipoMovimiento,
                ':idUsuario'  => $_SESSION['user_id'],
                ':datos'      => $datosSerializados
            ]);

            // Si todo fue bien, confirmamos los cambios
            $db->commit();

            return [
                'estado'  => true,
                'mensaje' => "Consulta guardada correctamente.",
                'clase'   => 'alert alert-success',
                'icono'   => 'glyphicon glyphicon-ok'
            ];

        } catch (Exception $e) {
            // En caso de cualquier error, revertimos la transacción
            if (isset($db)) { $db->rollback(); }

            return [
                'estado'  => false,
                'mensaje' => "Error guardando Consulta -> " . $e->getMessage(),
                'clase'   => 'alert alert-danger',
                'icono'   => 'glyphicon glyphicon-remove'
            ];
        }
    }

    function cambiarAConsultaFapCaratula($idSapCaratula, $datosAnteriores) {
        try {
            $db = Database::getConnection();
            
            // Iniciamos la transacción
            $db->beginTransaction();

            // 1. Actualizamos la carátula a estado "Consulta" (IdSapTipoTramite = 1)
            $sql = "UPDATE sapcaratula 
                    SET IdSapTipoTramite = 1, 
                        IdSapCondicion = NULL, 
                        IdSapEstado = NULL, 
                        Estado = :estadoConsulta, 
                        IdTipoCausa = NULL
                    WHERE Id = :id";

            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':estadoConsulta' => self::CONSULTA, // Usamos la constante 'C' definida en la clase
                ':id'             => $idSapCaratula
            ]);

            // 2. Registro en el log de auditoría
            $tipoMovimiento = 'cambiar a consulta';
            $datosSerializados = serialize($datosAnteriores);

            $sqlLog = "INSERT INTO log_sap (Tabla, IdTabla, Fecha, TipoMovimiento, IdUsuario, Datos) 
                       VALUES ('sapcaratula', :idTabla, NOW(), :movimiento, :idUsuario, :datos)";
            
            $stmtLog = $db->prepare($sqlLog);
            $stmtLog->execute([
                ':idTabla'    => $idSapCaratula,
                ':movimiento' => $tipoMovimiento,
                ':idUsuario'  => $_SESSION['user_id'],
                ':datos'      => $datosSerializados
            ]);

            // Si todo salió bien, confirmamos los cambios
            $db->commit();

            return [
                'estado'  => true,
                'mensaje' => "Consulta guardada correctamente.",
                'clase'   => 'alert alert-success',
                'icono'   => 'glyphicon glyphicon-ok'
            ];

        } catch (Exception $e) {
            // Si hay cualquier error, revertimos la transacción
            if (isset($db)) {
                $db->rollback();
            }

            return [
                'estado'  => false,
                'mensaje' => "Error guardando Consulta -> " . $e->getMessage(),
                'clase'   => 'alert alert-danger',
                'icono'   => 'glyphicon glyphicon-remove'
            ];
        }
    }

    function editarFapCaratula($idSapCaratula, $fechaRecepcion, $fechaIngreso, $nombreCausa, $abogados, $idJuzgado, $idTipoCausa, $idDepartamentoJudicial, $estado, $tipoSistema, $fechaHecho, $lugarHecho, $ambito, $especialidad, $caratulaDefinitiva, $domicilioHecho, $telefonoHecho, $fechaNotificacion, $lugarNotificacion, $recepcion, $tieneCobertura, $nombreCobertura, $coberturaDesde, $edad, $sexo, $domicilioReal, $domicilioProfesional, $telefonoParticular, $celular, $mail, $conCedula, $conFotoDemanda, $recepciono, $observaciones, $inscriptoDistrito, $idSapEstado, $idSapCondicion, $matricula, $idColegiado, $datosAnteriores) {
        try {
            $db = Database::getConnection();
            $db->beginTransaction();

            $sql = "UPDATE sapcaratula 
                    SET FechaRecepcion = :fRec, FechaIngreso = :fIng, NombreCausa = :nom, Abogados = :abog, 
                        Juzgado = :juz, IdTipoCausa = :tCausa, DepartamentoJudicial = :dept, Estado = :est, 
                        TipoSistema = :tSist, FechaHecho = :fHecho, LugarHecho = :lHecho, Ambito = :amb, 
                        Especialidad = :esp, CaratulaDefinitiva = :cDef, DomicilioHecho = :dHecho, 
                        TelefonoHecho = :tHecho, FechaNotificacion = :fNotif, LugarNotificacion = :lNotif, 
                        Recepcion = :recep, TieneCobertura = :cober, NombreCobertura = :nCober, 
                        CoberturaDesde = :cDesde, Edad = :edad, Sexo = :sexo, DomicilioReal = :dReal, 
                        DomicilioProfesional = :dProf, TelefonoParticular = :tPart, Celular = :cel, 
                        Mail = :mail, ConCedula = :ced, ConFotoDemanda = :foto, Recepciono = :recepciono, 
                        Observaciones = :obs, InscriptoDistrito = :insDist, IdSapEstado = :sEst, 
                        IdSapCondicion = :sCond, Matricula = :mat, IdColegiado = :idCol
                    WHERE Id = :id";

            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':fRec' => $fechaRecepcion, ':fIng' => $fechaIngreso, ':nom' => $nombreCausa, ':abog' => $abogados,
                ':juz' => $idJuzgado, ':tCausa' => $idTipoCausa, ':dept' => $idDepartamentoJudicial, ':est' => $estado,
                ':tSist' => $tipoSistema, ':fHecho' => $fechaHecho, ':lHecho' => $lugarHecho, ':amb' => $ambito,
                ':esp' => $especialidad, ':cDef' => $caratulaDefinitiva, ':dHecho' => $domicilioHecho,
                ':tHecho' => $telefonoHecho, ':fNotif' => $fechaNotificacion, ':lNotif' => $lugarNotificacion,
                ':recep' => $recepcion, ':cober' => $tieneCobertura, ':nCober' => $nombreCobertura,
                ':cDesde' => $coberturaDesde, ':edad' => $edad, ':sexo' => $sexo, ':dReal' => $domicilioReal,
                ':dProf' => $domicilioProfesional, ':tPart' => $telefonoParticular, ':cel' => $celular,
                ':mail' => $mail, ':ced' => $conCedula, ':foto' => $conFotoDemanda, ':recepciono' => $recepciono,
                ':obs' => $observaciones, ':insDist' => $inscriptoDistrito, ':sEst' => $idSapEstado,
                ':sCond' => $idSapCondicion, ':mat' => $matricula, ':idCol' => $idColegiado, ':id' => $idSapCaratula
            ]);

            // Lógica de Log
            $tipoMovimiento = (!empty($idSapCaratula)) ? 'modificacion' : 'alta';
            $datosLog = serialize($datosAnteriores);

            $sqlLog = "INSERT INTO log_sap (Tabla, IdTabla, Fecha, TipoMovimiento, IdUsuario, Datos) 
                       VALUES ('sapcaratula', :idT, NOW(), :mov, :idU, :dat)";
            
            $stmtLog = $db->prepare($sqlLog);
            $stmtLog->execute([
                ':idT' => $idSapCaratula,
                ':mov' => $tipoMovimiento,
                ':idU' => $_SESSION['user_id'],
                ':dat' => $datosLog
            ]);

            $db->commit();

            return [
                'estado' => true,
                'mensaje' => "Consulta guardada correctamente.",
                'clase' => 'alert alert-success',
                'icono' => 'glyphicon glyphicon-ok'
            ];

        } catch (Exception $e) {
            if (isset($db)) { $db->rollback(); }
            return [
                'estado' => false,
                'mensaje' => "Error guardando Consulta -> " . $e->getMessage(),
                'clase' => 'alert alert-danger',
                'icono' => 'glyphicon glyphicon-remove'
            ];
        }
    }

    function guardarFapReunion($accion, $idSapReunion, $fechaReunion, $estadoReunion, $resolucion, $observaciones, $datosAnteriores) {
        try {
            $db = Database::getConnection();
            $db->beginTransaction();

            switch ($accion) {
                case 'agregar':
                    $sql = "INSERT INTO sapaconsejo (FechaReunion, Resolucion, EstadoReunion, Observaciones)
                            VALUES (:fecha, :resol, :estado, :obs)";
                    $stmt = $db->prepare($sql);
                    $stmt->execute([
                        ':fecha'  => $fechaReunion,
                        ':resol'  => $resolucion,
                        ':estado' => $estadoReunion,
                        ':obs'    => $observaciones
                    ]);
                    $idSapReunion = $db->lastInsertId();
                    $datosAnteriores = []; // Para el log en caso de alta
                    break;
                
                case 'editar':
                    $sql = "UPDATE sapaconsejo 
                            SET FechaReunion = :fecha, Resolucion = :resol, 
                                EstadoReunion = :estado, Observaciones = :obs
                            WHERE Id = :id";
                    $stmt = $db->prepare($sql);
                    $stmt->execute([
                        ':fecha'  => $fechaReunion,
                        ':resol'  => $resolucion,
                        ':estado' => $estadoReunion,
                        ':obs'    => $observaciones,
                        ':id'     => $idSapReunion
                    ]);
                    break;

                case 'cerrar':
                    $sql = "UPDATE sapaconsejo SET EstadoReunion = :estado WHERE Id = :id";
                    $stmt = $db->prepare($sql);
                    $stmt->execute([
                        ':estado' => $estadoReunion,
                        ':id'     => $idSapReunion
                    ]);
                    break;

                default:
                    throw new Exception("Acción no válida: " . $accion);
            }

            // Registro de Log de Auditoría
            $datosLog = serialize($datosAnteriores);
            $sqlLog = "INSERT INTO log_sap (Tabla, IdTabla, Fecha, TipoMovimiento, IdUsuario, Datos) 
                       VALUES ('sapaconsejo', :idTabla, NOW(), :movimiento, :idUsuario, :datos)";
            
            $stmtLog = $db->prepare($sqlLog);
            $stmtLog->execute([
                ':idTabla'    => $idSapReunion,
                ':movimiento' => $accion,
                ':idUsuario'  => $_SESSION['user_id'],
                ':datos'      => $datosLog
            ]);

            $db->commit();

            return [
                'estado'       => true,
                'idSapReunion' => $idSapReunion,
                'mensaje'      => "Reunión de consejo guardada correctamente.",
                'clase'        => 'alert alert-success', 
                'icono'        => 'glyphicon glyphicon-ok'
            ];

        } catch (Exception $e) {
            if (isset($db)) {
                $db->rollback();
            }
            return [
                'estado'  => false,
                'mensaje' => "Error guardando Reunión de consejo -> " . $e->getMessage(),
                'clase'   => 'alert alert-danger', 
                'icono'   => 'glyphicon glyphicon-remove'
            ];
        }
    }

    function agregarCaratulaArchivo($idSapCaratula, $path, $nombreAdjunto, $extensionAdjunto, $tipoArchivo) {
        try {
            $db = Database::getConnection();

            $sql = "INSERT INTO sapcaratulaarchivo (IdSapCaratula, Path, NombreArchivo, Extension, TipoArchivo, IdUsuario, FechaCarga) 
                    VALUES (:id, :path, :nombre, :ext, :tipo, :idUsuario, NOW())";
            
            $stmt = $db->prepare($sql);
            
            $stmt->execute([
                ':id'        => $idSapCaratula,
                ':path'      => $path,
                ':nombre'    => $nombreAdjunto,
                ':ext'       => $extensionAdjunto,
                ':tipo'      => $tipoArchivo,
                ':idUsuario' => $_SESSION['user_id']
            ]);

            return [
                'estado'  => true,
                'mensaje' => "OK",
                'clase'   => 'alert alert-success',
                'icono'   => 'glyphicon glyphicon-ok'
            ];

        } catch (PDOException $e) {
            // En producción podrías usar: error_log($e->getMessage());
            return [
                'estado'  => false,
                'mensaje' => "Error al agregar archivo a la carátula",
                'clase'   => 'alert alert-danger',
                'icono'   => 'glyphicon glyphicon-remove'
            ];
        }
    }

    function eliminarCaratulaArchivo($idSapCaratulaArchivo) {
        try {
            $db = Database::getConnection();
            $db->beginTransaction();

            // 1. Realizamos el borrado lógico
            $sql = "UPDATE sapcaratulaarchivo 
                    SET Borrado = 1, IdUsuarioBorrado = :idUsuario, FechaBorrado = NOW() 
                    WHERE Id = :id";
            
            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':idUsuario' => $_SESSION['user_id'],
                ':id'        => $idSapCaratulaArchivo
            ]);

            // 2. Recuperamos los datos del archivo para devolverlos (útil si necesitas borrar el archivo físico)
            $sqlInfo = "SELECT Path as path, 
                               NombreArchivo as nombreArchivo, 
                               Extension as extensionAdjunto, 
                               TipoArchivo as tipoArchivo 
                        FROM sapcaratulaarchivo 
                        WHERE Id = :id";
            
            $stmtInfo = $db->prepare($sqlInfo);
            $stmtInfo->execute([':id' => $idSapCaratulaArchivo]);
            $datos = $stmtInfo->fetch(); // Retorna array asociativo o FALSE

            $db->commit();

            return [
                'estado'  => true,
                'datos'   => $datos ? $datos : null,
                'mensaje' => "OK",
                'clase'   => 'alert alert-success',
                'icono'   => 'glyphicon glyphicon-ok'
            ];

        } catch (PDOException $e) {
            if (isset($db)) { $db->rollback(); }
            
            return [
                'estado'  => false,
                'mensaje' => "Error al eliminar archivo a la carátula",
                'clase'   => 'alert alert-danger',
                'icono'   => 'glyphicon glyphicon-remove'
            ];
        }
    }

    function obtenerCaratulaArchivoPorId($idSapCaratulaArchivo) {
        try {
            $db = Database::getConnection();

            $sql = "SELECT IdSapCaratula as idSapCaratula, 
                           Path as path, 
                           NombreArchivo as nombreArchivo, 
                           Extension as extensionAdjunto, 
                           TipoArchivo as tipoArchivo 
                    FROM sapcaratulaarchivo 
                    WHERE Id = :id";
            
            $stmt = $db->prepare($sql);
            $stmt->execute([':id' => $idSapCaratulaArchivo]);
            
            // fetch() obtiene la fila como array asociativo
            $row = $stmt->fetch();

            if ($row) {
                return [
                    'estado' => true,
                    'datos' => $row,
                    'mensaje' => "OK",
                    'clase' => 'alert alert-success',
                    'icono' => 'glyphicon glyphicon-ok'
                ];
            } else {
                return [
                    'estado' => false,
                    'mensaje' => "No existe archivo",
                    'clase' => 'alert alert-warning',
                    'icono' => 'glyphicon glyphicon-exclamation-sign'
                ];
            }

        } catch (PDOException $e) {
            return [
                'estado' => false,
                'mensaje' => "Error al buscar el archivo de la carátula",
                'clase' => 'alert alert-danger',
                'icono' => 'glyphicon glyphicon-remove'
            ];
        }
    }

    function obtenerCaratulaAdjuntosPorIdCaratula($idSapCaratula) {
        try {
            $db = Database::getConnection();

            $sql = "SELECT Id as idSapCaratulaArchivo, 
                           Path as path, 
                           NombreArchivo as nombreArchivo, 
                           Extension as extensionAdjunto, 
                           TipoArchivo as tipoArchivo 
                    FROM sapcaratulaarchivo 
                    WHERE IdSapCaratula = :id AND Borrado = 0";
            
            $stmt = $db->prepare($sql);
            $stmt->execute([':id' => $idSapCaratula]);
            
            // fetchAll() nos devuelve un array con todas las filas encontradas
            $datos = $stmt->fetchAll();

            return [
                'estado'  => true,
                'datos'   => $datos, // Será un array vacío si no hay adjuntos
                'mensaje' => "OK",
                'clase'   => 'alert alert-success',
                'icono'   => 'glyphicon glyphicon-ok'
            ];

        } catch (PDOException $e) {
            return [
                'estado'  => false,
                'mensaje' => "Error al obtener archivos de la carátula",
                'clase'   => 'alert alert-danger',
                'icono'   => 'glyphicon glyphicon-remove'
            ];
        }
    }

    function obtenerReunionConejoPorIdFap($idSap) {
        try {
            $db = Database::getConnection();

            $sql = "SELECT scd.Id AS idSapConsejoDetalle, 
                           scd.IdSAPaconsejo AS idSapConsejo, 
                           scd.IdSAP AS idSap, 
                           scd.Estado AS estadoSapConsejoDetalle, 
                           scd.FechaAprobacion AS fechaAprobacion, 
                           scd.Observacion AS observacionSapConsejoDetalle, 
                           sc.FechaReunion AS fechaReunion, 
                           sc.Resolucion AS numeroResolucion, 
                           sc.EstadoReunion AS estadoReunion, 
                           sc.Observaciones AS observacionesReunion
                    FROM sapaconsejodetalle scd
                    INNER JOIN sapaconsejo sc ON sc.Id = scd.IdSAPaconsejo
                    WHERE scd.IdSAP = :id";

            $stmt = $db->prepare($sql);
            $stmt->execute([':id' => $idSap]);
            
            $row = $stmt->fetch();

            if ($row) {
                return [
                    'estado'  => true,
                    'datos'   => $row,
                    'mensaje' => "OK",
                    'clase'   => 'alert alert-success',
                    'icono'   => 'glyphicon glyphicon-ok'
                ];
            } else {
                return [
                    'estado'  => false,
                    'mensaje' => "No existe datos de la reunion",
                    'clase'   => 'alert alert-warning',
                    'icono'   => 'glyphicon glyphicon-exclamation-sign'
                ];
            }

        } catch (PDOException $e) {
            return [
                'estado'  => false,
                'mensaje' => "Error al buscando datos de la reunion",
                'clase'   => 'alert alert-danger',
                'icono'   => 'glyphicon glyphicon-remove'
            ];
        }
    }

    function cantidadCausasPorPeriodoActual($idColegiado) {
        try {
            $db = Database::getConnection();

            // Definición de fechas basada en la constante PERIODO_ACTUAL
            $fechaDesde = PERIODO_ACTUAL . '-05-01';
            $fechaHasta = (PERIODO_ACTUAL + 1) . '-04-30';

            $sql = "SELECT COUNT(sc.Id) 
                    FROM sapcaratula sc
                    WHERE sc.IdColegiado = :id
                    AND sc.FechaIngreso BETWEEN :desde AND :hasta
                    AND sc.Estado IN(:aprobado, :enSistema)";

            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':id'       => $idColegiado,
                ':desde'    => $fechaDesde,
                ':hasta'    => $fechaHasta,
                ':aprobado'  => self::APROBADO,  // Usando las constantes de la clase ('A')
                ':enSistema' => self::EN_SISTEMA  // ('E')
            ]);

            // fetchColumn() devuelve directamente el primer valor de la primera fila (el COUNT)
            return (int)$stmt->fetchColumn();

        } catch (PDOException $e) {
            // En caso de error, devolvemos 0 o podrías loguear el error
            return 0;
        }
    }

    function obtenerFapPenditesDeReunion() {
        try {
            $db = Database::getConnection();

            $sql = "SELECT sc.Id AS idSapCaratula, 
                           sc.NombreCausa AS nombreCausa,
                           sc.FechaIngreso AS fechaIngreso, 
                           tc.Nombre AS nombreTipoCausa, 
                           stt.Nombre AS nombreSapTipoTramite, 
                           se.Nombre AS nombreSapEstado,
                           sco.Nombre AS nombreSapCondicion, 
                           c.Matricula AS matricula, 
                           p.Apellido AS apellido, 
                           p.Nombres AS nombre
                    FROM sapcaratula sc
                    LEFT JOIN sapaconsejodetalle sacd ON sacd.IdSAP = sc.Id
                    INNER JOIN tipocausa tc ON tc.Id = sc.IdTipoCausa
                    INNER JOIN saptipotramite stt ON stt.Id = sc.IdSapTipoTramite
                    INNER JOIN sapestado se ON se.Id = sc.IdSapEstado
                    INNER JOIN sapcondicion sco ON sco.Id = sc.IdSapCondicion
                    INNER JOIN colegiado c ON c.Id = sc.IdColegiado
                    INNER JOIN persona p ON p.Id = c.IdPersona
                    WHERE sc.IdSapTipoTramite IN(2, 3, 4) 
                      AND sc.IdSapEstado IN(1, 2, 3) 
                      AND YEAR(sc.FechaIngreso) = YEAR(NOW())
                      AND sacd.Id IS NULL
                    ORDER BY sc.FechaIngreso DESC";

            $stmt = $db->query($sql);
            $datos = $stmt->fetchAll();

            // En PDO fetchAll devuelve array vacío [] si no hay resultados
            if (count($datos) > 0) {
                return [
                    'estado' => true,
                    'mensaje' => "OK",
                    'datos' => $datos,
                    'clase' => 'alert alert-success',
                    'icono' => 'glyphicon glyphicon-ok'
                ];
            } else {
                return [
                    'estado' => true,
                    'mensaje' => "No hay expedientes pendientes",
                    'datos' => [],
                    'clase' => 'alert alert-info',
                    'icono' => 'glyphicon glyphicon-exclamation-sign'
                ];
            }

        } catch (PDOException $e) {
            return [
                'estado' => false,
                'mensaje' => "Error buscando expedientes pendientes",
                'clase' => 'alert alert-danger', // Corregido 'alert-error' por estándar Bootstrap
                'icono' => 'glyphicon glyphicon-remove'
            ];
        }
    }

    function obtenerFapPenditesDeReunionAutocompletar() {
        try {
            $db = Database::getConnection();

            $sql = "SELECT sc.Id, sc.FechaIngreso, tc.Nombre AS nombreTipoCausa, 
                           stt.Nombre AS nombreSapTipoTramite, sc.NombreCausa, 
                           c.Matricula, p.Apellido, p.Nombres
                    FROM sapcaratula sc
                    LEFT JOIN sapaconsejodetalle sacd ON sacd.IdSAP = sc.Id
                    INNER JOIN tipocausa tc ON tc.Id = sc.IdTipoCausa
                    INNER JOIN saptipotramite stt ON stt.Id = sc.IdSapTipoTramite
                    INNER JOIN sapestado se ON se.Id = sc.IdSapEstado
                    INNER JOIN colegiado c ON c.Id = sc.IdColegiado
                    INNER JOIN persona p ON p.Id = c.IdPersona
                    WHERE sc.IdSapTipoTramite IN(2, 3, 4) AND sc.IdSapEstado = 1
                    AND sacd.Id IS NULL
                    ORDER BY sc.FechaIngreso DESC";

            $stmt = $db->query($sql);
            $rows = $stmt->fetchAll();

            $datos = array();
            foreach ($rows as $row) {
                // Armamos el formato requerido para el autocompletar
                $datos[] = [
                    'id' => $row['Id'],
                    'nombre' => "FAP N° {$row['Id']} - Matricula {$row['Matricula']} - " . 
                                trim($row['Apellido']) . " " . trim($row['Nombres']) . 
                                " (CAUSA: {$row['NombreCausa']})"
                ];
            }

            if (count($datos) > 0) {
                return [
                    'estado' => true,
                    'mensaje' => "OK",
                    'datos' => $datos,
                    'clase' => 'alert alert-success',
                    'icono' => 'glyphicon glyphicon-ok'
                ];
            }

            return [
                'estado' => true,
                'mensaje' => "No hay expedientes",
                'datos' => [],
                'clase' => 'alert alert-info',
                'icono' => 'glyphicon glyphicon-exclamation-sign'
            ];

        } catch (PDOException $e) {
            return [
                'estado' => false,
                'mensaje' => "Error buscando expedientes",
                'clase' => 'alert alert-danger',
                'icono' => 'glyphicon glyphicon-remove'
            ];
        }
    }

    function existeEnReunionConsejo($idSapCaratula) {
        try {
            $db = Database::getConnection();
            $sql = "SELECT COUNT(Id) FROM sapaconsejodetalle WHERE IdSAP = :id";
            
            $stmt = $db->prepare($sql);
            $stmt->execute([':id' => $idSapCaratula]);
            
            // Si el conteo es mayor a 0, existe.
            return ($stmt->fetchColumn() > 0);
            
        } catch (PDOException $e) {
            return false;
        }
    }

    function obtenerDeudoresAsistidos($cuotasAdeudadas, $antiguedad) {
        try {
            $db = Database::getConnection();

            $sql = "SELECT DISTINCT c.Matricula as matricula, p.Apellido as apellido, p.Nombres as nombre, 
                           cdr.Calle, cdr.Lateral, cdr.Numero, cdr.Piso, cdr.Departamento, 
                           l.Nombre AS nombreLocalidad, cdr.CodigoPostal as codigoPostal, 
                           cc.TelefonoFijo as telefonoFijo, cc.TelefonoMovil as telefonoMovil, 
                           cc.CorreoElectronico as email, tm.Detalle as estadoDetalle,
                    (SELECT COUNT(*) 
                     FROM colegiadodeudaanualcuotas dac 
                     INNER JOIN colegiadodeudaanual da ON da.Id = dac.IdColegiadoDeudaAnual AND da.Estado = 'A' 
                     WHERE da.IdColegiado = c.Id AND dac.Estado = 1 AND dac.FechaVencimiento < DATE(NOW())) AS cantidadCuotas, 
                    (SELECT GROUP_CONCAT(CONCAT(sc1.Id, ' (', stt.Nombre, ')') SEPARATOR ' - ') 
                     FROM sapcaratula sc1 
                     INNER JOIN saptipotramite stt ON stt.Id = sc1.IdSapTipoTramite 
                     WHERE sc1.IdColegiado = c.Id AND sc1.IdSapTipoTramite <> 1 
                           AND sc1.IdSapEstado = 1 
                           AND sc1.FechaIngreso >= DATE_ADD(NOW(), INTERVAL -:antiguedad1 YEAR)) AS fap
                    FROM colegiado c
                    INNER JOIN persona p ON p.Id = c.IdPersona
                    INNER JOIN tipomovimiento tm ON tm.Id = c.Estado
                    INNER JOIN colegiadodomicilioreal cdr ON cdr.idColegiado = c.Id AND cdr.idEstado = 1
                    LEFT JOIN localidad l ON l.Id = cdr.idLocalidad
                    INNER JOIN colegiadocontacto cc ON cc.IdColegiado = c.Id AND cc.IdEstado = 1
                    INNER JOIN sapcaratula sc ON (sc.IdColegiado = c.Id 
                                                AND sc.IdSapTipoTramite <> 1 
                                                AND sc.IdSapEstado = 1 
                                                AND sc.FechaIngreso >= DATE_ADD(NOW(), INTERVAL -:antiguedad2 YEAR))
                    WHERE c.Estado IN (1, 5, 10, 8)
                    HAVING cantidadCuotas > :cuotas
                    ORDER BY p.Apellido, p.Nombres";

            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':antiguedad1' => $antiguedad,
                ':antiguedad2' => $antiguedad,
                ':cuotas'      => $cuotasAdeudadas
            ]);

            $rows = $stmt->fetchAll();
            $datos = [];

            foreach ($rows as $row) {
                // Construcción del Domicilio Completo
                $domicilio = "";
                if (!empty($row['Calle'])) {
                    $domicilio = $row['Calle'];
                    if (!empty($row['Numero'])) $domicilio .= " Nº " . $row['Numero'];
                    if (!empty($row['Lateral'])) $domicilio .= " e/ " . $row['Lateral'];
                    
                    if (!empty($row['Piso']) && strtoupper($row['Piso']) != "NR") {
                        $domicilio .= " Piso " . $row['Piso'];
                    }
                    if (!empty($row['Departamento']) && strtoupper($row['Departamento']) != "NR") {
                        $domicilio .= " Dto. " . $row['Departamento'];
                    }
                    if (!empty($row['nombreLocalidad'])) {
                        $domicilio .= ' - Loc: ' . $row['nombreLocalidad'] . ' (' . $row['codigoPostal'] . ')';
                    }
                }

                $datos[] = [
                    'matricula'         => $row['matricula'],
                    'apellido'          => $row['apellido'],
                    'nombre'            => $row['nombre'],
                    'estadoDetalle'     => $row['estadoDetalle'],
                    'domicilioCompleto' => $domicilio,
                    'telefonoFijo'      => $row['telefonoFijo'],
                    'telefonoMovil'     => $row['telefonoMovil'],
                    'correoElectronico' => $row['email'],
                    'cantidadCuotas'    => $row['cantidadCuotas'],
                    'fap'               => $row['fap']
                ];
            }

            if (count($datos) > 0) {
                return [
                    'estado'  => true,
                    'mensaje' => "OK",
                    'datos'   => $datos,
                    'clase'   => 'alert alert-success',
                    'icono'   => 'glyphicon glyphicon-ok'
                ];
            }

            return [
                'estado'  => true,
                'mensaje' => "No se encontraron deudores con asistencia FAP",
                'datos'   => [],
                'clase'   => 'alert alert-info',
                'icono'   => 'glyphicon glyphicon-exclamation-sign'
            ];

        } catch (PDOException $e) {
            return [
                'estado'  => false,
                'mensaje' => "Error buscando deudores: " . $e->getMessage(),
                'clase'   => 'alert alert-danger',
                'icono'   => 'glyphicon glyphicon-remove'
            ];
        }
    }

    function obtenerTotalesParaEstadisticas($fechaDesde, $fechaHasta) {
        try {
            $db = Database::getConnection();

            $sql = "SELECT a.IdTipoCausa as idTipoCausa, 
                           b.Nombre as nombreTipoCausa, 
                           a.IdSapTipoTramite as idSapTipoTramite, 
                           tt.Nombre as nombreSapTipoTramite, 
                           e.Nombre as nombreSapEstado, 
                           COUNT(a.id) as cantidad
                    FROM sapcaratula a
                    INNER JOIN saptipotramite tt ON tt.Id = a.IdSapTipoTramite
                    LEFT JOIN sapestado e ON e.Id = a.IdSapEstado
                    LEFT JOIN tipocausa b ON b.id = a.idtipocausa
                    WHERE a.FechaIngreso BETWEEN :desde AND :hasta
                    GROUP BY a.IdTipoCausa, b.Nombre, a.IdSapTipoTramite, tt.Nombre, e.Nombre";

            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':desde' => $fechaDesde,
                ':hasta' => $fechaHasta
            ]);

            $datos = $stmt->fetchAll();

            if (count($datos) > 0) {
                return [
                    'estado' => true,
                    'mensaje' => "OK",
                    'datos' => $datos,
                    'clase' => 'alert alert-success',
                    'icono' => 'glyphicon glyphicon-ok'
                ];
            }

            return [
                'estado' => true,
                'mensaje' => "No hay datos para el período seleccionado",
                'datos' => [],
                'clase' => 'alert alert-info',
                'icono' => 'glyphicon glyphicon-exclamation-sign'
            ];

        } catch (PDOException $e) {
            return [
                'estado' => false,
                'mensaje' => "Error buscando estadísticas: " . $e->getMessage(),
                'clase' => 'alert alert-danger',
                'icono' => 'glyphicon glyphicon-remove'
            ];
        }
    }

    function obtenerCantidadPorAnio($anioDesde, $anioHasta, $idSapTipoTramite) {
        try {
            $db = Database::getConnection();

            // Usamos YEAR() que es más óptimo que substr() para campos DATE/DATETIME
            $sql = "SELECT YEAR(sc.FechaIngreso) as anio, COUNT(sc.Id) as cantidad
                    FROM sapcaratula sc 
                    WHERE YEAR(sc.FechaIngreso) BETWEEN :anioDesde AND :anioHasta
                    AND sc.IdSapTipoTramite = :tipoTramite 
                    AND sc.IdSapEstado = 1
                    GROUP BY YEAR(sc.FechaIngreso)
                    ORDER BY anio ASC";

            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':anioDesde'   => $anioDesde,
                ':anioHasta'   => $anioHasta,
                ':tipoTramite' => $idSapTipoTramite
            ]);

            $datos = $stmt->fetchAll();

            if (count($datos) > 0) {
                return [
                    'estado' => true,
                    'mensaje' => "OK",
                    'datos' => $datos,
                    'clase' => 'alert alert-success',
                    'icono' => 'glyphicon glyphicon-ok'
                ];
            }

            return [
                'estado' => true,
                'mensaje' => "No hay datos para el rango de años seleccionado",
                'datos' => [],
                'clase' => 'alert alert-info',
                'icono' => 'glyphicon glyphicon-exclamation-sign'
            ];

        } catch (PDOException $e) {
            return [
                'estado' => false,
                'mensaje' => "Error buscando datos anuales",
                'clase' => 'alert alert-danger',
                'icono' => 'glyphicon glyphicon-remove'
            ];
        }
    }

    /**
     * Método genérico privado para obtener catálogos simples (Id, Nombre)
     */
    private function obtenerCatalogoSimple($tabla, $nombreEntidad) {
        $tablasPermitidas = ['juzgado', 'tipocausa', 'departamentojudicial', 'sapcondicion', 'sapestado'];
        if (!in_array($tabla, $tablasPermitidas)) {
            throw new Exception("Tabla no permitida");
        }
        try {
            $db = Database::getConnection();
            // Nota: En PDO, las variables de tabla no pueden ser parámetros (:tabla), 
            // por eso se inyectan directamente en el string. Al ser nombres fijos 
            // definidos por el desarrollador, es seguro.
            $sql = "SELECT Id as id, Nombre as nombre FROM $tabla ORDER BY Nombre";
            
            $stmt = $db->query($sql);
            $datos = $stmt->fetchAll();

            if (count($datos) > 0) {
                return [
                    'estado'  => true,
                    'mensaje' => "OK",
                    'datos'   => $datos,
                    'clase'   => 'alert alert-success',
                    'icono'   => 'glyphicon glyphicon-ok'
                ];
            }

            return [
                'estado'  => false,
                'datos'   => null,
                'mensaje' => "No se encontraron $nombreEntidad",
                'clase'   => 'alert alert-warning',
                'icono'   => 'glyphicon glyphicon-exclamation-sign'
            ];
        } catch (PDOException $e) {
            return [
                'estado'  => false, 
                'mensaje' => "Error buscando $nombreEntidad", 
                'clase'   => 'alert alert-danger', 
                'icono'   => 'glyphicon glyphicon-remove'
            ];
        }
    }

    /* --- Métodos Públicos --- */

    function obtenerDepartamentosJudiciales() {
        return $this->obtenerCatalogoSimple('departamentojudicial', 'departamentos');
    }

    function obtenerJuzgados() {
        return $this->obtenerCatalogoSimple('juzgado', 'juzgados');
    }

    function obtenerTiposCausa() {
        return $this->obtenerCatalogoSimple('tipocausa', 'tipos de causa');
    }

    function obtenerCondicionsFap() {
        return $this->obtenerCatalogoSimple('sapcondicion', 'condicion');
    }

    function obtenerEstadosFap() {
        return $this->obtenerCatalogoSimple('sapestado', 'estado');
    }

    /*
    function obtenerDepartamentosJudiciales() {
        try {
            $db = Database::getConnection();
            $sql = "SELECT Id as id, Nombre as nombre FROM departamentojudicial ORDER BY Nombre";
            $stmt = $db->query($sql);
            $datos = $stmt->fetchAll();

            if (count($datos) > 0) {
                return [
                    'estado' => true,
                    'mensaje' => "OK",
                    'datos' => $datos,
                    'clase' => 'alert alert-success',
                    'icono' => 'glyphicon glyphicon-ok'
                ];
            } else {
                return [
                    'estado' => false,
                    'datos' => null,
                    'mensaje' => "No se encontraron departamentos",
                    'clase' => 'alert alert-warning',
                    'icono' => 'glyphicon glyphicon-exclamation-sign'
                ];
            }
        } catch (PDOException $e) {
            return ['estado' => false, 'mensaje' => "Error buscando departamentos", 'clase' => 'alert alert-danger', 'icono' => 'glyphicon glyphicon-remove'];
        }
    }

    function obtenerJuzgados() {
        try {
            $db = Database::getConnection();
            $sql = "SELECT Id as id, Nombre as nombre FROM juzgado ORDER BY Nombre";
            $stmt = $db->query($sql);
            $datos = $stmt->fetchAll();

            if (count($datos) > 0) {
                return [
                    'estado' => true,
                    'mensaje' => "OK",
                    'datos' => $datos,
                    'clase' => 'alert alert-success',
                    'icono' => 'glyphicon glyphicon-ok'
                ];
            } else {
                return [
                    'estado' => false,
                    'datos' => null,
                    'mensaje' => "No se encontraron juzgados",
                    'clase' => 'alert alert-warning',
                    'icono' => 'glyphicon glyphicon-exclamation-sign'
                ];
            }
        } catch (PDOException $e) {
            return ['estado' => false, 'mensaje' => "Error buscando juzgados", 'clase' => 'alert alert-danger', 'icono' => 'glyphicon glyphicon-remove'];
        }
    }

    function obtenerTiposCausa() {
        try {
            $db = Database::getConnection();
            $sql = "SELECT Id as id, Nombre as nombre FROM tipocausa ORDER BY Nombre";
            $stmt = $db->query($sql);
            $datos = $stmt->fetchAll();

            if (count($datos) > 0) {
                return [
                    'estado' => true,
                    'mensaje' => "OK",
                    'datos' => $datos,
                    'clase' => 'alert alert-success',
                    'icono' => 'glyphicon glyphicon-ok'
                ];
            } else {
                return [
                    'estado' => false,
                    'datos' => null,
                    'mensaje' => "No se encontraron tipos de causa",
                    'clase' => 'alert alert-warning',
                    'icono' => 'glyphicon glyphicon-exclamation-sign'
                ];
            }
        } catch (PDOException $e) {
            return ['estado' => false, 'mensaje' => "Error buscando tipos de causa", 'clase' => 'alert alert-danger', 'icono' => 'glyphicon glyphicon-remove'];
        }
    }
    */
}
