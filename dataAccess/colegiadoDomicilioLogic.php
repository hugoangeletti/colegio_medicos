<?php
class colegiadoDomicilioLogic {

    public function obtenerColegiadoDomicilioPorIdColegiado($idColegiado) {
    try {
        $db = Database::getConnection();
        $sql="SELECT colegiadodomicilioreal.idColegiadoDomicilioReal, colegiadodomicilioreal.Calle,
            colegiadodomicilioreal.Lateral, colegiadodomicilioreal.Numero, colegiadodomicilioreal.Piso,
            colegiadodomicilioreal.Departamento, colegiadodomicilioreal.idLocalidad, colegiadodomicilioreal.CodigoPostal,
            colegiadodomicilioreal.FechaCarga, localidad.Nombre AS NombreLocalidad, origendomicilio.Nombre AS Origen
        FROM colegiadodomicilioreal
        INNER JOIN localidad ON(localidad.Id = colegiadodomicilioreal.idLocalidad)
        INNER JOIN origendomicilio ON(origendomicilio.idOrigenDomicilio = colegiadodomicilioreal.idOrigen)
        WHERE IdColegiado = ? and IdEstado = 1";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idColegiado]);
        $row = $stmt->fetch();

        $resultado = array();
        $resultado['estado'] = TRUE;
        if ($row) {
            $datos = array(
                    'idColegiadoDomicilio' => $row['idColegiadoDomicilioReal'],
                    'calle' => $row['Calle'],
                    'lateral' => $row['Lateral'],
                    'numero' => $row['Numero'],
                    'piso' => $row['Piso'],
                    'depto' => $row['Departamento'],
                    'idLocalidad' => $row['idLocalidad'],
                    'codigoPostal' => $row['CodigoPostal'],
                    'fechaCarga' => $row['FechaCarga'],
                    'nombreLocalidad' => $row['NombreLocalidad'],
                    'origen' => $row['Origen']
                    );

            $resultado['datos'] = $datos;
            $resultado['mensaje'] = "OK";
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['datos'] = NULL;
            $resultado['mensaje'] = "No hay colegiado ".$idColegiado;
            $resultado['clase'] = 'alert alert-info';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando colegiado";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }

    return $resultado;
}


    public function obtenerDomiciliosPorIdColegiado($idColegiado) {
    try {
        $db = Database::getConnection();
        $sql="SELECT colegiadodomicilioreal.idColegiadoDomicilioReal, colegiadodomicilioreal.Calle,
            colegiadodomicilioreal.Lateral, colegiadodomicilioreal.Numero, colegiadodomicilioreal.Piso,
            colegiadodomicilioreal.Departamento, colegiadodomicilioreal.idLocalidad, colegiadodomicilioreal.CodigoPostal,
            colegiadodomicilioreal.FechaCarga, localidad.Nombre AS NombreLocalidad, origendomicilio.Nombre AS Origen,
            colegiadodomicilioreal.IdEstado
        FROM colegiadodomicilioreal
        INNER JOIN localidad ON(localidad.Id = colegiadodomicilioreal.idLocalidad)
        INNER JOIN origendomicilio ON(origendomicilio.idOrigenDomicilio = colegiadodomicilioreal.idOrigen)
        WHERE IdColegiado = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idColegiado]);
        $rows = $stmt->fetchAll();

        $resultado = array();
        $resultado['estado'] = TRUE;
        if (count($rows) > 0) {
            $datos = array();
            foreach ($rows as $row) {
                $calle = $row['Calle'];
                $numero = $row['Numero'];
                $lateral = $row['Lateral'];
                $piso = $row['Piso'];
                $depto = $row['Departamento'];
                $codigoPostal = $row['CodigoPostal'];
                $nombreLocalidad = $row['NombreLocalidad'];

                $domicilioCompleto = "";
                if ($calle) {
                    $domicilioCompleto = $calle;
                    if ($numero) {
                        $domicilioCompleto .= " Nº ".$numero;
                    }
                    if ($lateral) {
                        $domicilioCompleto .= " e/ ".$lateral;
                    }
                    if ($piso && strtoupper($piso) != "NR") {
                        $domicilioCompleto .= " Piso ".$piso;
                    }
                    if ($depto && strtoupper($depto) != "NR") {
                        $domicilioCompleto .= " Dto. ".$depto;
                    }
                }
                $r = array(
                    'idColegiadoDomicilio' => $row['idColegiadoDomicilioReal'],
                    'domicilio' => $domicilioCompleto,
                    'codigoPostal' => $codigoPostal,
                    'fechaActualizacion' => $row['FechaCarga'],
                    'nombreLocalidad' => $nombreLocalidad.' ('.$codigoPostal.')',
                    'origen' => $row['Origen'],
                    'idEstado' => $row['IdEstado']
                );
                array_push($datos, $r);
            }
            $resultado['estado'] = true;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['datos'] = NULL;
            $resultado['mensaje'] = "No hay domicilios ".$idColegiado;
            $resultado['clase'] = 'alert alert-info';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando domicilios";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }

    return $resultado;
}


    public function agregarColegiadoDomicilio($idColegiado, $calle, $numero, $lateral, $piso, $depto, $idLocalidad, $codigoPostal){
    $resultado = array();
    try {
        $db = Database::getConnection();
        $db->beginTransaction();

        //marco como anulado el domicilio actualmente activo y luego doy de alta el nuevo domicilio
        $sql="UPDATE colegiadodomicilioreal
            SET idEstado = 2
            WHERE IdColegiado = ? AND idEstado = 1";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idColegiado]);

        $calle = mb_strtoupper($calle, 'UTF-8');
        if (isset($lateral)) {
            $lateral = mb_strtoupper($lateral, 'UTF-8');
        }
        $sql="INSERT INTO colegiadodomicilioreal
            (idColegiado, Calle, Lateral, Numero, Piso, Departamento, idLocalidad, CodigoPostal, idEstado, FechaCarga, idUsuario, idOrigen)
            VALUE (?, ?, ?, ?, ?, ?, ?, ?, 1, date(now()), ?, 2)";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idColegiado, $calle, $lateral, $numero, $piso, $depto, $idLocalidad, $codigoPostal, $_SESSION['user_id']]);
        $idColegiadoDomicilio = $db->lastInsertId();
        $resultado['estado'] = TRUE;
        $resultado['mensaje'] = "OK";
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';

        $resultado['mensaje'] .= '('.$idColegiadoDomicilio.')';
        $db->commit();
        return $resultado;
    } catch (PDOException $e) {
        $db->rollBack();
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "ERROR AL ACTUALIZAR DOMICILIO";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
        return $resultado;
    }
}


    public function obtenerDomicilioProfesional($idColegiado) {
    try {
        $db = Database::getConnection();
        $sql="SELECT colegiadoconsultorio.Calle, colegiadoconsultorio.Lateral, colegiadoconsultorio.Numero,
            colegiadoconsultorio.Piso, colegiadoconsultorio.Departamento, localidad.Nombre AS NombreLocalidad
        FROM colegiadoconsultorio
        LEFT JOIN localidad ON(localidad.Id = colegiadoconsultorio.IdLocalidad)
        WHERE colegiadoconsultorio.IdColegiado = ?
        AND (colegiadoconsultorio.Estado='A' AND colegiadoconsultorio.FechaBaja is null)
        ORDER BY colegiadoconsultorio.Id DESC
        LIMIT 1";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idColegiado]);
        $row = $stmt->fetch();

        $resultado = array();
        if ($row) {
            $calle = $row['Calle'];
            $numero = $row['Numero'];
            $lateral = $row['Lateral'];
            $piso = $row['Piso'];
            $depto = $row['Departamento'];
            $nombreLocalidad = $row['NombreLocalidad'];

            $domicilioCompleto = "";
            if ($calle) {
                $domicilioCompleto = $calle;
                if ($numero) {
                    $domicilioCompleto .= " Nº ".$numero;
                }
                if ($lateral) {
                    $domicilioCompleto .= " e/ ".$lateral;
                }
                if ($piso && strtoupper($piso) != "NR") {
                    $domicilioCompleto .= " Piso ".$piso;
                }
                if ($depto && strtoupper($depto) != "NR") {
                    $domicilioCompleto .= " Dto. ".$depto;
                }
            }
            $datos = array(
                'domicilio' => $domicilioCompleto,
                'nombreLocalidad' => $nombreLocalidad
            );

            $resultado['estado'] = true;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = false;
            $resultado['mensaje'] = "No hay domicilios ".$idColegiado;
            $resultado['clase'] = 'alert alert-info';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando domicilios";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }

    return $resultado;
}
}

