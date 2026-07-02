<?php

define('SOLICITUD_WEB_GENERADA', 1);
define('SOLICITUD_WEB_PENDIENTE', 2);
define('SOLICITUD_WEB_ANULADA', 3);
define('SOLICITUD_WEB_RECHAZADA', 4);
define('SOLICITUD_WEB_GENERADA_RETIRAR', 5);

class colegiadoCertificadosLogic {

    public function obtenerCertificadosPorIdColegiado($idColegiado) {
    $sql="SELECT sc.Id, sc.FechaEmision, sc.Presentado, tc.Detalle, sc.EnvioMail, sc.Mail, u.NombreCompleto, sc.Distrito
    FROM solicitudcertificados sc
    INNER JOIN tipocertificado tc ON tc.Id = sc.IdTipoCertificado
    LEFT JOIN usuario u ON u.Id = sc.IdUsuarioSolicitante
    WHERE sc.IdColegiado = ? AND sc.Estado <> 'A'";

    $resultado = array();
    try {
        $db = Database::getConnection();
        $stmt = $db->prepare($sql);
        $stmt->execute([$idColegiado]);
        $rows = $stmt->fetchAll();
        if (count($rows) > 0) {
            $datos = array();
            foreach ($rows as $r) {
                $row = array (
                    'idColegiadoCertificado' => $r['Id'],
                    'fechaEmision' => $r['FechaEmision'],
                    'tipoCertificado' => $r['Detalle'],
                    'entregar' => $r['Presentado'],
                    'enviaMail' => $r['EnvioMail'],
                    'mail' => $r['Mail'],
                    'usuarioSolicitante' => $r['NombreCompleto'],
                    'distrito' => $r['Distrito']
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
            $resultado['mensaje'] = "El colegiado no tiene certificados emitidos.";
            $resultado['clase'] = 'alert alert-warning';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando certificados del colegiado";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }

    return $resultado;
}

    public function obtenerCertificadoPorId($idCertificado){
    $sql="SELECT sc.Id, sc.IdColegiado, sc.FechaSolicitud, sc.HoraSolicitud, sc.IdUsuarioSolicitante, sc.IdTipoCertificado, sc.Presentado, sc.Distrito, sc.IdUsuarioEmision, sc.FechaEmision, sc.HoraEmision, sc.FechaEntrega, sc.Estado, sc.EstadoConTesoreria, sc.CuotasAdeudadas, sc.IdNotaCambioDistrito, sc.ConFirma, sc.ConLeyendaTeso, sc.IdColegiadoEspecialista, sc.EnvioMail, sc.Mail, sc.HashQR, sc.`Path`, sc.NombreArchivo
        FROM solicitudcertificados sc
        WHERE sc.Id = ?";

    $resultado = array();
    try {
        $db = Database::getConnection();
        $stmt = $db->prepare($sql);
        $stmt->execute([$idCertificado]);
        $r = $stmt->fetch();
        if ($r) {
            $datos = array (
                'idCertificado' => $r['Id'],
                'idColegiado' => $r['IdColegiado'],
                'fechaSolicitud' => $r['FechaSolicitud'],
                'horaSolicitud' => $r['HoraSolicitud'],
                'idUsuarioSolicitante' => $r['IdUsuarioSolicitante'],
                'idTipoCertificado' => $r['IdTipoCertificado'],
                'presentado' => $r['Presentado'],
                'distrito' => $r['Distrito'],
                'idUsuarioEmision' => $r['IdUsuarioEmision'],
                'fechaEmision' => $r['FechaEmision'],
                'horaEmision' => $r['HoraEmision'],
                'fechaEntrega' => $r['FechaEntrega'],
                'estado' => $r['Estado'],
                'estadoConTesoreria' => $r['EstadoConTesoreria'],
                'cuotasAdeudadas' => $r['CuotasAdeudadas'],
                'idNotaCambioDistrito' => $r['IdNotaCambioDistrito'],
                'conFirma' => $r['ConFirma'],
                'conLeyendaTeso' => $r['ConLeyendaTeso'],
                'idColegiadoEspecialista' => $r['IdColegiadoEspecialista'],
                'envioMail' => $r['EnvioMail'],
                'mail' => $r['Mail'],
                'hash_qr' => $r['HashQR'],
                'path' => $r['Path'],
                'nombreArchivo' => $r['NombreArchivo']
            );

            $resultado['estado'] = TRUE;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "No existe el Certificado";
            $resultado['clase'] = 'alert alert-warning';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando certificado";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }

    return $resultado;
}

    public function agregarSolicitudCertificado($idColegiado, $idTipoCertificado, $presentado, $distrito, $codigoDeudor, $cuotasAdeudadas, $idNotaCambioDistrito, $conFirma, $conLeyendaTeso, $idColegiadoEspecialista, $enviaMail, $mail){
    $resultado = array();
    try {
        $db = Database::getConnection();

        $creado = date('YmdHis');
        $hash_qr = hashData($idColegiado.'_'.$creado);
        if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] == "") {
            $idUsuarioSolicitante = 49; //tramites-web
        } else {
            $idUsuarioSolicitante = $_SESSION['user_id'];
        }

        //agrego la solicitud de certificado
        $sql="INSERT INTO solicitudcertificados
            (IdColegiado, FechaSolicitud, HoraSolicitud, IdUsuarioSolicitante, IdTipoCertificado, Presentado,
            Distrito, IdUsuarioEmision, FechaEmision, HoraEmision, EstadoConTesoreria, CuotasAdeudadas,
            IdNotaCambioDistrito, ConFirma, ConLeyendaTeso, IdColegiadoEspecialista, EnvioMail, Estado, Mail, HashQR)
            VALUE (?, date(now()), time(now()), ?, ?, ?, ?, ?, date(now()), time(now()), ?, ?, ?, ?, ?, ?, ?, 'I', ?, ?)";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idColegiado, $idUsuarioSolicitante, $idTipoCertificado, $presentado, $distrito, $idUsuarioSolicitante, $codigoDeudor, $cuotasAdeudadas, $idNotaCambioDistrito, $conFirma, $conLeyendaTeso, $idColegiadoEspecialista, $enviaMail, $mail, $hash_qr]);
        $resultado['estado'] = TRUE;
        $resultado['idCertificado'] = $db->lastInsertId();
        $resultado['mensaje'] = "OK";
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "ERROR AL GENERAR SOLICITUD DE CERTIFICADO.";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function anularSolicitudCertificado($idCertificado) {
    $resultado = array();
    try {
        $db = Database::getConnection();

        $sql="UPDATE solicitudcertificados SET Estado = 'A' WHERE Id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idCertificado]);
        $resultado['estado'] = TRUE;
        $resultado['mensaje'] = "EL CERTIFICADO SE ANULO CORRECTAMENTE";
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "ERROR AL ANULAR CERTIFICADO";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function guardarSolicitudCertificadoPdf($idCertificado, $path, $nombrePdf) {
    $resultado = array();
    try {
        $db = Database::getConnection();

        $sql="UPDATE solicitudcertificados
            SET Path = ?, NombreArchivo = ?
            WHERE Id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$path, $nombrePdf, $idCertificado]);
        $resultado['estado'] = TRUE;
        $resultado['mensaje'] = "EL CERTIFICADO SE REGISTRO CORRECTAMENTE";
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "ERROR AL CARGAR CERTIFICADO";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function guardarSolicitudCertificadoWeb($accion, $idSolicitudCertificadoWeb, $idColegiado, $idTipoCertificado, $idSolicitudCertificadoWebEntidad, $presentado, $distrito, $idSolicitudCertificados, $idSolicitudCertificadoWebEstado) {
    $resultado = array();
    try {
        $db = Database::getConnection();
        $continua = TRUE;
        $hash = null;
        switch ($accion) {
            case 'agregar':
                $creado = date('YmdHis');
                $hash = hashData($idColegiado.$creado);

                $sql="INSERT INTO solicitud_certificado_web (IdColegiado, FechaSolicitud, IdTipoCertificado, IdSolicitudCertificadoWebEntidad, Presentado, Hash, IdSolicitudCertificadoWebEstado)
                VALUE (?, NOW(), ?, ?, ?, ?, ?)";
                $stmt = $db->prepare($sql);
                $params = [$idColegiado, $idTipoCertificado, $idSolicitudCertificadoWebEntidad, $presentado, $hash, $idSolicitudCertificadoWebEstado];
                break;

            case 'editar_certificado_id':
                $sql="UPDATE solicitud_certificado_web
                    SET IdSolicitudCertificados = ?, IdSolicitudCertificadoWebEstado = ?
                    WHERE Id = ?";
                $stmt = $db->prepare($sql);
                $params = [$idSolicitudCertificados, $idSolicitudCertificadoWebEstado, $idSolicitudCertificadoWeb];
                break;

            default:
                $continua = FALSE;
                break;
        }
        if ($continua) {
            $stmt->execute($params);
            $resultado['estado'] = TRUE;
            if ($accion == 'agregar') {
                $resultado['idSolicitudCertificadoWeb'] = $db->lastInsertId();
                $resultado['hashCertificado'] = $hash;
            }
            $resultado['mensaje'] = "OK";
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "ERROR AL GENERAR SOLICITUD DE CERTIFICADO -> Ingreso incorrecto ".$accion;
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-remove';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "ERROR AL GENERAR SOLICITUD DE CERTIFICADO.";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function anularSolicitudCertificadoWeb($idSolicitudCertificadoWeb) {
    $resultado = array();
    try {
        $db = Database::getConnection();
        $idEstado = SOLICITUD_WEB_ANULADA;

        $sql="UPDATE solicitud_certificado_web scw
            SET scw.FechaAnulacion = NOW(),
                scw.IdUsuarioAnulacion = ?,
                scw.IdSolicitudCertificadoWebEstado = ?
            WHERE scw.Id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$_SESSION['user_id'], $idEstado, $idSolicitudCertificadoWeb]);
        $resultado['estado'] = TRUE;
        $resultado['mensaje'] = "EL CERTIFICADO SE ANULO CORRECTAMENTE";
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "ERROR AL ANULAR CERTIFICADO";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function marcarCertificadoWebDescargado($hashCertificado) {
    $resultado = array();
    try {
        $db = Database::getConnection();

        $sql="UPDATE solicitud_certificado_web scw
            INNER JOIN solicitudcertificados sc ON sc.Id = scw.IdSolicitudCertificados
            SET scw.FechaEntrega = NOW(),
                scw.CantidadDescargas = scw.CantidadDescargas + 1,
                sc.FechaEntrega = DATE(NOW())
            WHERE Hash = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$hashCertificado]);
        $resultado['estado'] = TRUE;
        $resultado['mensaje'] = "EL CERTIFICADO SE DESCARGO CORRECTAMENTE";
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "ERROR AL DESCARGAR CERTIFICADO";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function obtenerSolicitudCertificadoWebPorIdColegiado($idColegiado) {
    $sql="SELECT scw.Id, scw.FechaSolicitud, scw.IdTipoCertificado, tc.Detalle, scw.IdSolicitudCertificadoWebEntidad, scwe.Nombre, scw.Presentado, scw.Distrito, scw.FechaEntrega, scw.Hash, scw.IdSolicitudCertificados, sc.FechaEmision, sc.HoraEmision, scw.IdSolicitudCertificadoWebEstado, e.Nombre, e.LeyendaPublica
        FROM solicitud_certificado_web scw
        INNER JOIN tipocertificado tc ON tc.Id = scw.IdTipoCertificado
        INNER JOIN solicitud_certificado_web_estado e ON e.Id = scw.IdSolicitudCertificadoWebEstado
        LEFT JOIN solicitud_certificado_web_entidad scwe ON scwe.Id = scw.IdSolicitudCertificadoWebEntidad
        LEFT JOIN solicitudcertificados sc ON sc.Id = scw.IdSolicitudCertificados
        WHERE scw.IdColegiado = ? AND scw.Borrado = 0
        ORDER BY scw.Id DESC";

    $resultado = array();
    try {
        $db = Database::getConnection();
        $stmt = $db->prepare($sql);
        $stmt->execute([$idColegiado]);
        $rows = $stmt->fetchAll();
        if (count($rows) > 0) {
            $datos = array();
            foreach ($rows as $r) {
                $fechaEmision = $r['FechaEmision'];
                if (isset($fechaEmision) && $fechaEmision <> "") {
                    $fechaVencimiento = sumarRestarSobreFecha($fechaEmision, 30, 'day', '+');
                    if (date('Y-m-d') > $fechaVencimiento) {
                        $vencido = 1;
                    } else {
                        $vencido = 0;
                    }
                } else {
                    $vencido = 2;
                }
                $row = array (
                    'idSolicitudCertificadoWeb' => $r['Id'],
                    'fechaSolicitud' => $r['FechaSolicitud'],
                    'idTipoCertificado' => $r['IdTipoCertificado'],
                    'nombreTipoCertificado' => $r['Detalle'],
                    'idSolicitudCertificadoWebEntidad' => $r['IdSolicitudCertificadoWebEntidad'],
                    'nombreSolcitudCertificadoWebEntidad' => $r['Nombre'],
                    'presentado' => $r['Presentado'],
                    'distrito' => $r['Distrito'],
                    'fechaEntrega' => $r['FechaEntrega'],
                    'hash' => $r['Hash'],
                    'idSolicitudCertificados' => $r['IdSolicitudCertificados'],
                    'fechaEmision' => $r['FechaEmision'],
                    'horaEmision' => $r['HoraEmision'],
                    'vencido' => $vencido,
                    'idSolicitudCertificadoWebEstado' => $r['IdSolicitudCertificadoWebEstado'],
                    'nombreSolcitudCertificadoWebEstado' => $r['Nombre'],
                    'leyendaSolicitudCertificadoWebEstado' => $r['LeyendaPublica']
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
            $resultado['mensaje'] = "El colegiado no tiene certificados solicitidaos.";
            $resultado['clase'] = 'alert alert-warning';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando solicitidaos del colegiado";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }

    return $resultado;
}

    public function obtenerSolicitudCertificadoWebPendientes() {
    $sql="SELECT scw.Id, scw.FechaSolicitud, scw.IdTipoCertificado, tc.Detalle, scw.Presentado, scw.Distrito, scw.Hash, c.Id, c.Matricula, p.Apellido, p.Nombres
        FROM solicitud_certificado_web scw
        INNER JOIN tipocertificado tc ON tc.Id = scw.IdTipoCertificado
        INNER JOIN colegiado c ON c.Id = scw.IdColegiado
        INNER JOIN persona p ON p.Id = c.IdPersona
        LEFT JOIN solicitudcertificados sc ON sc.Id = scw.IdSolicitudCertificados
        WHERE scw.Borrado = 0 AND scw.IdSolicitudCertificadoWebEstado = ".SOLICITUD_WEB_PENDIENTE;

    $resultado = array();
    try {
        $db = Database::getConnection();
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll();
        if (count($rows) > 0) {
            $datos = array();
            foreach ($rows as $r) {
                $row = array (
                    'idSolicitudCertificadoWeb' => $r['Id'],
                    'fechaSolicitud' => $r['FechaSolicitud'],
                    'idTipoCertificado' => $r['IdTipoCertificado'],
                    'nombreTipoCertificado' => $r['Detalle'],
                    'presentado' => $r['Presentado'],
                    'distrito' => $r['Distrito'],
                    'hash' => $r['Hash'],
                    'idColegiado' => $r['Id'],
                    'matricula' => $r['Matricula'],
                    'apellido' => $r['Apellido'],
                    'nombre' => $r['Nombres']
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
            $resultado['mensaje'] = "No hay solicitudes pendientes de aprobación.";
            $resultado['clase'] = 'alert alert-warning';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando certificados_online.php";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }

    return $resultado;
}

    public function obtenerSolicitudCertificadoWebFinalizados($periodo) {
    $sql="SELECT scw.Id, scw.FechaSolicitud, scw.IdTipoCertificado, tc.Detalle, scw.Presentado, e.Nombre, scw.Distrito, scw.Hash, c.Id, c.Matricula, p.Apellido, p.Nombres, scw.IdSolicitudCertificadoWebEntidad, scw.FechaEntrega, scw.CantidadDescargas
        FROM solicitud_certificado_web scw
        INNER JOIN tipocertificado tc ON tc.Id = scw.IdTipoCertificado
        INNER JOIN colegiado c ON c.Id = scw.IdColegiado
        INNER JOIN persona p ON p.Id = c.IdPersona
        INNER JOIN solicitudcertificados sc ON sc.Id = scw.IdSolicitudCertificados
        LEFT JOIN solicitud_certificado_web_entidad e ON e.Id = scw.IdSolicitudCertificadoWebEntidad
        WHERE substr(scw.FechaSolicitud, 1, 4) = ? AND scw.Borrado = 0";

    $resultado = array();
    try {
        $db = Database::getConnection();
        $stmt = $db->prepare($sql);
        $stmt->execute([$periodo]);
        $rows = $stmt->fetchAll();
        if (count($rows) > 0) {
            $datos = array();
            foreach ($rows as $r) {
                $row = array (
                    'idSolicitudCertificadoWeb' => $r['Id'],
                    'fechaSolicitud' => $r['FechaSolicitud'],
                    'idTipoCertificado' => $r['IdTipoCertificado'],
                    'nombreTipoCertificado' => $r['Detalle'],
                    'presentado' => $r['Presentado'],
                    'entidad' => $r['Nombre'],
                    'distrito' => $r['Distrito'],
                    'hash' => $r['Hash'],
                    'idColegiado' => $r['Id'],
                    'matricula' => $r['Matricula'],
                    'apellido' => $r['Apellido'],
                    'nombre' => $r['Nombres'],
                    'idSolicitudCertificadoWebEntidad' => $r['IdSolicitudCertificadoWebEntidad'],
                    'fechaEntrega' => $r['FechaEntrega'],
                    'cantidadDescargas' => $r['CantidadDescargas']
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
            $resultado['mensaje'] = "No hay solicitudes pendientes de aprobación.";
            $resultado['clase'] = 'alert alert-warning';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando certificados_online.php";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }

    return $resultado;
}

    public function existenSolicitudCertificadoWebPendientes() {
    $sql="SELECT COUNT(scw.Id) AS cantidad
        FROM solicitud_certificado_web scw
        WHERE scw.IdSolicitudCertificadoWebEstado = ".SOLICITUD_WEB_PENDIENTE." AND scw.Borrado = 0";

    $cantidad = NULL;
    try {
        $db = Database::getConnection();
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($r) {
            $cantidad = $r['cantidad'];
        }
    } catch (PDOException $e) {
        $cantidad = NULL;
    }
    return $cantidad;
}

    public function obtenerCertificadoWebPorHash($hashCertificado) {
    $sql="SELECT scw.Id, c.Matricula, c.Hash, p.Apellido, p.Nombres, scw.IdSolicitudCertificados, sc.`Path`, sc.NombreArchivo
        FROM solicitud_certificado_web scw
          LEFT JOIN solicitudcertificados sc ON sc.Id = scw.IdSolicitudCertificados
          INNER JOIN colegiado c ON c.Id = scw.IdColegiado
          INNER JOIN persona p ON p.Id = c.IdPersona
          WHERE scw.Hash = ?";

    $resultado = array();
    try {
        $db = Database::getConnection();
        $stmt = $db->prepare($sql);
        $stmt->execute([$hashCertificado]);
        $r = $stmt->fetch();
        if ($r) {
            $datos = array (
                'idSolicitudCertificadoWeb' => $r['Id'],
                'matricula' => $r['Matricula'],
                'hashColegiado' => $r['Hash'],
                'apellido' => $r['Apellido'],
                'nombre' => $r['Nombres'],
                'idCertificado' => $r['IdSolicitudCertificados'],
                'path' => $r['Path'],
                'nombreArchivo' => $r['NombreArchivo']
            );
            $resultado['estado'] = TRUE;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = FALSE;
            $resultado['datos'] = NULL;
            $resultado['mensaje'] = "No se encontro el certificado solicitado.";
            $resultado['clase'] = 'alert alert-warning';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando certificado.";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }

    return $resultado;
}

    public function obtenerSolicitudCertificadoWebPorId($idSolicitudCertificadoWeb) {
    $sql="SELECT scw.FechaSolicitud, scw.IdTipoCertificado, scw.Presentado, c.Matricula, c.Hash, p.Apellido, p.Nombres, scw.IdSolicitudCertificados, sc.`Path`, sc.NombreArchivo
        FROM solicitud_certificado_web scw
          LEFT JOIN solicitudcertificados sc ON sc.Id = scw.IdSolicitudCertificados
          INNER JOIN colegiado c ON c.Id = scw.IdColegiado
          INNER JOIN persona p ON p.Id = c.IdPersona
          WHERE scw.Id = ?";

    $resultado = array();
    try {
        $db = Database::getConnection();
        $stmt = $db->prepare($sql);
        $stmt->execute([$idSolicitudCertificadoWeb]);
        $r = $stmt->fetch();
        if ($r) {
            $datos = array (
                'fechaSolicitud' => $r['FechaSolicitud'],
                'idTipoCertificado' => $r['IdTipoCertificado'],
                'presentado' => $r['Presentado'],
                'matricula' => $r['Matricula'],
                'hashColegiado' => $r['Hash'],
                'apellido' => $r['Apellido'],
                'nombre' => $r['Nombres'],
                'idCertificado' => $r['IdSolicitudCertificados'],
                'path' => $r['Path'],
                'nombreArchivo' => $r['NombreArchivo']
            );
            $resultado['estado'] = TRUE;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = FALSE;
            $resultado['datos'] = NULL;
            $resultado['mensaje'] = "No se encontro el certificado solicitado.";
            $resultado['clase'] = 'alert alert-warning';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando certificado.";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }

    return $resultado;
}

    public function obtenerSolicitudCertificadoWebEntidadPorId($idSolicitudCertificadoWebEntidad) {
    $sql="SELECT a.Nombre, a.Visible, a.Borrado
        FROM solicitud_certificado_web_entidad a
        WHERE a.Id = ?";

    $resultado = array();
    try {
        $db = Database::getConnection();
        $stmt = $db->prepare($sql);
        $stmt->execute([$idSolicitudCertificadoWebEntidad]);
        $r = $stmt->fetch();
        if ($r) {
            $datos = array (
                'nombre' => $r['Nombre'],
                'visible' => $r['Visible'],
                'borrado' => $r['Borrado']
            );

            $resultado['estado'] = TRUE;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "No existe la entidad";
            $resultado['clase'] = 'alert alert-warning';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando entidad";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }

    return $resultado;
}

    public function obtenerSolicitudCertificadoWebEntidades($visibles, $borrado) {
    if (isset($visibles) || isset($borrado)) {
        if ($visibles) {
            $esVisible = 1;
        } else {
            $esVisible = 0;
        }
        if ($borrado) {
            $esBorrado = 1;
        } else {
            $esBorrado = 0;
        }
        $filtro = " WHERE scwe.`Visible` = ? AND scwe.Borrado = ? ";
    } else {
        $filtro = " ";
    }
    $sql="SELECT scwe.Id, scwe.Nombre, scwe.Visible, scwe.Borrado, scwe.IdUsuarioCarga, scwe.FechaCarga, scwe.IdUsuarioBorrado, scwe.FechaBorrado
        FROM solicitud_certificado_web_entidad scwe
        ".$filtro."
        ORDER BY scwe.Nombre";

    $resultado = array();
    try {
        $db = Database::getConnection();
        $stmt = $db->prepare($sql);
        if ($filtro <> " ") {
            $stmt->execute([$esVisible, $esBorrado]);
        } else {
            $stmt->execute();
        }
        $rows = $stmt->fetchAll();
        if (count($rows) > 0) {
            $datos = array();
            foreach ($rows as $r) {
                $row = array (
                    'id' => $r['Id'],
                    'nombre' => $r['Nombre'],
                    'visible' => $r['Visible'],
                    'borrado' => $r['Borrado'],
                    'idUsuarioCarga' => $r['IdUsuarioCarga'],
                    'fechaCarga' => $r['FechaCarga'],
                    'idUsuarioBorrado' => $r['IdUsuarioBorrado'],
                    'fechaBorrado' => $r['FechaBorrado']
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
            $resultado['mensaje'] = "No se encontraron entidades.";
            $resultado['clase'] = 'alert alert-warning';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando entidades";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }

    return $resultado;
}

    public function guardarEntidad($idEntidad, $nombre, $visible, $borrado, $datosAnteriores) {
    try {
        $db = Database::getConnection();
        $db->beginTransaction();
        if (isset($idEntidad)) {
            if ($borrado == 0) {
                $sql = "UPDATE solicitud_certificado_web_entidad
                        SET Nombre = ?, Visible = ?, Borrado = ?, IdUsuarioCarga = ?, FechaCarga = NOW(), IdUsuarioBorrado = NULL, FechaBorrado = NULL
                        WHERE Id = ?";
                $stmt = $db->prepare($sql);
                $stmt->execute([$nombre, $visible, $borrado, $_SESSION['user_id'], $idEntidad]);
            } else {
                $sql = "UPDATE solicitud_certificado_web_entidad
                        SET Nombre = ?, Visible = ?, Borrado = ?, IdUsuarioBorrado = ?, FechaBorrado = NOW()
                        WHERE Id = ?";
                $stmt = $db->prepare($sql);
                $stmt->execute([$nombre, $visible, $borrado, $_SESSION['user_id'], $idEntidad]);
            }
        } else {
            $sql="INSERT INTO solicitud_certificado_web_entidad (Nombre, Visible, Borrado, IdUsuarioCarga, FechaCarga)
                VALUES (?, ?, ?, ?, NOW())";
            $stmt = $db->prepare($sql);
            $stmt->execute([$nombre, $visible, $borrado, $_SESSION['user_id']]);
        }

        $resultado = array();
        if (isset($idEntidad) && $idEntidad <> "") {
            $tipoMovimiento = 'modificacion';
        } else {
            $idEntidad = $db->lastInsertId();
            $datosAnteriores = array();
            $tipoMovimiento = 'alta';
        }
        $datos = serialize($datosAnteriores);
        $sql="INSERT INTO log_tabla (Tabla, IdTabla, Fecha, TipoMovimiento, IdUsuario, Datos)
            VALUES ('solicitud_certificado_web_entidad', ?, NOW(), ?, ?, ?)";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idEntidad, $tipoMovimiento, $_SESSION['user_id'], $datos]);
        $resultado['estado'] = TRUE;
        $resultado['idEntidad'] = $idEntidad;
        $resultado['mensaje'] = 'LA ENTIDAD HA SIDO GUARDADA';
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';

        $db->commit();
        return $resultado;
    } catch (PDOException $e) {
        $db->rollBack();
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "Error guardando ENTIDAD -> ".$e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        return $resultado;
    }
}

}
