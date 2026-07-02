<?php
class envioMailDiarioLogic {

    public function  obtenerEnvioDiario()
{
    try {
        $db = Database::getConnection();
        $sql="SELECT enviomaildiario.Id, enviomaildiario.Detalle, enviomaildiario.Rango, enviomaildiario.Texto,
            enviomaildiario.From, enviomaildiario.Subject
            FROM enviomaildiario
            WHERE enviomaildiario.Envia = 'S'";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll();

        if (count($rows) > 0) {
            $datos = array();
            foreach ($rows as $row) {
                $item = array (
                        'idEnvio' => $row['Id'],
                        'detalle' => $row['Detalle'],
                        'rango' => $row['Rango'],
                        'texto' => $row['Texto'],
                        'from' => $row['From'],
                        'subject' => $row['Subject']
                 );
                array_push($datos, $item);
            }
            $resultado['estado'] = true;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = false;
            $resultado['mensaje'] = "No hay Envios";
            $resultado['clase'] = 'alert alert-info';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando NOTA: " . $e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }

    return $resultado;
}

    public function  obtenerEnvioDiarioPorId($idEnvioMailDiario)
{
    try {
        $db = Database::getConnection();
        $sql="SELECT e.Id, e.Detalle, e.Rango, e.Texto, e.From, e.Subject
            FROM enviomaildiario e
            WHERE e.Id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idEnvioMailDiario]);
        $row = $stmt->fetch();

        if ($row) {
            $datos = array (
                        'idEnvio' => $row['Id'],
                        'detalle' => $row['Detalle'],
                        'rango' => $row['Rango'],
                        'texto' => $row['Texto'],
                        'from' => $row['From'],
                        'subject' => $row['Subject']
                        );

            $resultado['estado'] = true;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = false;
            $resultado['mensaje'] = "No hay Envios";
            $resultado['clase'] = 'alert alert-info';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando NOTA: " . $e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }

    return $resultado;
}

    public function guardarEnvioColegiado($idEnvio, $idColegiado, $idReferencia, $error, $estado, $mailEnvio)
{
    try {
        $db = Database::getConnection();
        $sql = "INSERT INTO enviomaildiariocolegiado
            (IdEnvioMailDiario, IdColegiado, IdReferencia, FechaEnvio, Error, Estado, MailEnvio)
            VALUES (?, ?, ?, NOW(), ?, ?, ?)";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idEnvio, $idColegiado, $idReferencia, $error, $estado, $mailEnvio]);
        $resultado['estado'] = true;
        $resultado['mensaje'] = "OK";
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error agregando Notificacion: " . $e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }

    return $resultado;
}

    public function obtenerColegiadoCambiosLink($rango) {
    try {
        $db = Database::getConnection();
        $sql = "SELECT c.Id, c.Matricula, p.Apellido, p.Nombres, p.Sexo, cc.CorreoElectronico
            FROM colegiado c
            INNER JOIN persona p ON p.Id = c.IdPersona
            INNER JOIN tipomovimiento tm ON tm.Id = c.Estado
            INNER JOIN colegiadocontacto cc ON(cc.IdColegiado = c.Id AND cc.IdEstado = 1 AND cc.CorreoElectronico is not null AND UPPER(cc.CorreoElectronico) <> 'NR' AND cc.CorreoElectronico <> '')
            LEFT JOIN enviomaildiariocolegiado emdc ON(emdc.IdColegiado = c.Id AND emdc.IdReferencia = c.Id AND emdc.Estado IN('A', 'O'))
            LEFT JOIN agremiacionesdebito ad ON ad.IdColegiado = c.Id AND ad.Borrado = 0
            LEFT JOIN debitotarjeta dt ON (dt.IdColegiado = c.Id AND dt.Estado = 'A')
            LEFT JOIN debitocbu dc ON (dc.IdColegiado = c.Id AND dc.Estado = 'A')
            LEFT JOIN colegiadomailrechazado cmr ON cmr.IdColegiado = c.Id
            WHERE tm.Estado IN('A')
            AND emdc.Id IS NULL
            AND dt.id IS NULL AND dc.Id IS NULL AND ad.Id IS NULL AND cmr.Id IS NULL
            AND c.Id IN (SELECT da.IdColegiado FROM colegiadodeudaanual da INNER JOIN colegiadodeudaanualcuotas dac ON dac.IdColegiadoDeudaAnual = da.Id LEFT JOIN cobranzadetalle cd ON cd.Recibo = dac.Id LEFT JOIN cobranza co ON co.Id = cd.IdLoteCobranza WHERE da.IdColegiado = c.Id AND da.Periodo = ".$_SESSION['periodoActual']." AND da.Estado = 'A' AND (co.IdLugarPago <> 26 OR cd.Id IS NULL))
            ORDER BY c.Matricula
            LIMIT ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$rango]);
        $rows = $stmt->fetchAll();

        if (count($rows) > 0) {
            $datos = array();
            foreach ($rows as $row) {
                $item = array (
                        'idColegiado' => $row['Id'],
                        'idReferencia' => $row['Id'],
                        'matricula' => $row['Matricula'],
                        'apellido' => $row['Apellido'],
                        'nombres' => $row['Nombres'],
                        'sexo' => $row['Sexo'],
                        'mail' => $row['CorreoElectronico']
                 );
                array_push($datos, $item);
            }
            $resultado['estado'] = true;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = false;
            $resultado['mensaje'] = "No hay pendientes de notificacion";
            $resultado['clase'] = 'alert alert-info';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando pendientes de notificacion: " . $e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }

    return $resultado;
}

    public function obtenerPagosDuplicados($periodoPagoDoble, $rango) {
    try {
        $db = Database::getConnection();
        $sql = "SELECT DISTINCT c.Id, da.Id, c.Matricula, p.Apellido, p.Nombres, p.Sexo, cc.CorreoElectronico
                FROM colegiadodeudaanualcuotas dac
                INNER JOIN colegiadodeudaanual da ON da.Id = dac.IdColegiadoDeudaAnual
                INNER JOIN colegiado c ON c.Id = da.IdColegiado
                INNER JOIN persona p ON p.Id = c.IdPersona
                INNER JOIN tipomovimiento tm ON tm.Id = c.Estado
                INNER JOIN colegiadocontacto cc ON(cc.IdColegiado = c.Id AND cc.IdEstado = 1 AND cc.CorreoElectronico IS not null AND UPPER(cc.CorreoElectronico) <> 'NR' AND cc.CorreoElectronico <> '')
                LEFT JOIN enviomaildiariocolegiado emd ON emd.IdEnvioMailDiario = 20 AND emd.IdReferencia = da.Id
                WHERE da.Periodo = ? AND dac.Reimputacion <> 0 AND tm.Estado = 'A'
                AND emd.Id IS NULL
                LIMIT ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$periodoPagoDoble, $rango]);
        $rows = $stmt->fetchAll();

        $resultado = array();
        if (count($rows) > 0) {
            $datos = array();
            foreach ($rows as $row) {
                $item = array (
                        'idColegiado' => $row['Id'],
                        'idReferencia' => $row['Id'],
                        'matricula' => $row['Matricula'],
                        'apellido' => $row['Apellido'],
                        'nombres' => $row['Nombres'],
                        'sexo' => $row['Sexo'],
                        'mail' => $row['CorreoElectronico']
                 );
                array_push($datos, $item);
            }
            $resultado['estado'] = true;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "No hay colegiacion anual";
            $resultado['clase'] = 'alert alert-info';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando cuotas de colegiacion: " . $e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function obtenerCertificadoSegur($rango) {
    try {
        $db = Database::getConnection();
        $sql = "SELECT DISTINCT c.Id, cs.Id AS IdSeguro, c.Matricula, p.Apellido, p.Nombres, p.Sexo, cc.CorreoElectronico
                FROM colegiado_seguro cs
                    INNER JOIN colegiado c ON c.Matricula = cs.Matricula
                INNER JOIN persona p ON p.Id = c.IdPersona
                INNER JOIN tipomovimiento tm ON tm.Id = c.Estado
                INNER JOIN colegiadocontacto cc ON(cc.IdColegiado = c.Id AND cc.IdEstado = 1 AND cc.CorreoElectronico IS not null AND UPPER(cc.CorreoElectronico) <> 'NR' AND cc.CorreoElectronico <> '')
                LEFT JOIN enviomaildiariocolegiado emd ON emd.IdEnvioMailDiario = 21 AND emd.IdReferencia = cs.Id
                LEFT JOIN colegiadomailrechazado cmr ON cmr.IdColegiado = c.Id
                WHERE cs.Activo = 1 AND cs.Borrado = 0 AND tm.Estado = 'A' AND emd.Id IS NULL AND cmr.Id IS NULL
                LIMIT ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$rango]);
        $rows = $stmt->fetchAll();

        $resultado = array();
        if (count($rows) > 0) {
            $datos = array();
            foreach ($rows as $row) {
                $item = array (
                        'idColegiado' => $row['Id'],
                        'idReferencia' => $row['IdSeguro'],
                        'matricula' => $row['Matricula'],
                        'apellido' => $row['Apellido'],
                        'nombres' => $row['Nombres'],
                        'sexo' => $row['Sexo'],
                        'mail' => $row['CorreoElectronico']
                 );
                array_push($datos, $item);
            }
            $resultado['estado'] = true;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "No hay colegiacion anual";
            $resultado['clase'] = 'alert alert-info';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando cuotas de colegiacion: " . $e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function obtenerRecibosEnviarPorMail($rango) {
    try {
        $db = Database::getConnection();
        $sql = "SELECT cdm.Id, c.Matricula, p.Apellido, p.Nombres, p.Sexo, cc.CorreoElectronico, cdm.Fecha, cdm.Tipo, cdm.Numero
            FROM cajadiariamovimiento cdm
            INNER JOIN colegiado c ON c.Id = cdm.IdColegiado
            INNER JOIN persona p ON p.Id = c.IdPersona
            INNER JOIN colegiadocontacto cc ON (cc.IdColegiado = c.Id AND cc.IdEstado = 1)
            LEFT JOIN enviomaildiariocolegiado em ON em.IdReferencia = cdm.Id AND em.IdEnvioMailDiario = 18
            LEFT JOIN colegiadomailrechazado cmr ON cmr.IdColegiado = c.Id
            WHERE cdm.IdAsistente IS NULL AND cdm.EnviaMail = 1 AND cc.CorreoElectronico <> '' AND cc.CorreoElectronico <> 'NR' AND cc.CorreoElectronico IS NOT NULL
            AND em.Id IS NULL AND cmr.Id IS NULL

            UNION ALL

            SELECT cdm.Id, c.Matricula, p.Apellido, p.Nombres, p.Sexo, cc.CorreoElectronico, cdm.Fecha, cdm.Tipo, cdm.Numero
            FROM cajadiariamovimiento cdm
            INNER JOIN cursosasistente ca ON ca.Id = cdm.IdAsistente
            INNER JOIN colegiado c ON c.Id = ca.IdColegiado
            INNER JOIN persona p ON p.Id = c.IdPersona
            INNER JOIN colegiadocontacto cc ON (cc.IdColegiado = c.Id AND cc.IdEstado = 1)
            LEFT JOIN enviomaildiariocolegiado em ON em.IdReferencia = cdm.Id AND em.IdEnvioMailDiario = 18
            LEFT JOIN colegiadomailrechazado cmr ON cmr.IdColegiado = c.Id
            WHERE cdm.IdColegiado IS NULL AND cdm.EnviaMail = 1 AND cc.CorreoElectronico <> '' AND cc.CorreoElectronico <> 'NR' AND cc.CorreoElectronico IS NOT NULL
            AND em.Id IS NULL AND cmr.Id IS NULL";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll();

        $resultado = array();
        if (count($rows) > 0) {
            $datos = array();
            foreach ($rows as $row) {
                $item = array (
                    'idReferencia' => $row['Id'],
                    'matricula' => $row['Matricula'],
                    'apellido' => $row['Apellido'],
                    'nombres' => $row['Nombres'],
                    'sexo' => $row['Sexo'],
                    'mail' => $row['CorreoElectronico'],
                    'fechaRecibo' => $row['Fecha'],
                    'tipo' => $row['Tipo'],
                    'numeroRecibo' => $row['Numero']
                 );
                array_push($datos, $item);
            }
            $resultado['estado'] = TRUE;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = FALSE;
            $resultado['datos'] = NULL;
            $resultado['mensaje'] = "No existen recibos para envio de mail";
            $resultado['clase'] = 'alert alert-warning';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando recibos para envio de mail: " . $e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function obtenerEnviosPorIdColegiado($idColegiado) {
    try {
        $db = Database::getConnection();
        $sql = "SELECT emdc.Id, emdc.IdEnvioMailDiario, emdc.FechaEnvio, emdc.MailEnvio, emd.Detalle, cmr.Id AS IdMailRechazado
                FROM enviomaildiariocolegiado emdc
                INNER JOIN enviomaildiario emd ON emd.Id = emdc.IdEnvioMailDiario
                LEFT JOIN colegiadomailrechazado cmr ON cmr.IdColegiado = emdc.IdColegiado
                WHERE emdc.IdColegiado = ? AND emdc.Estado = 'O'";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idColegiado]);
        $rows = $stmt->fetchAll();

        if (count($rows) > 0) {
            $datos = array();
            foreach ($rows as $row) {
                $item = array (
                        'idEnvioMailDiarioColegiado' => $row['Id'],
                        'idEnvioMailDiario' => $row['IdEnvioMailDiario'],
                        'fechaEnvio' => $row['FechaEnvio'],
                        'mail' => $row['MailEnvio'],
                        'detalleEnvio' => $row['Detalle'],
                        'idMailRechazado' => $row['IdMailRechazado']
                 );
                array_push($datos, $item);
            }
            $resultado['estado'] = true;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = false;
            $resultado['mensaje'] = "No hay notificaciones";
            $resultado['clase'] = 'alert alert-info';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando notificaciones: " . $e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }

    return $resultado;
}
}
