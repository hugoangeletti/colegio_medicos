<?php
class registroDNU260OtrosDistritosLogic {

    public function obtenerRegistrosOtrosDistritosTodos(){
    try {
        $db = Database::getConnection();
        $sql = "SELECT r.*, p.Nacionalidad, td.NombreCompleto
            FROM registro_dnu_260_otro_distrito r
            INNER JOIN paises p ON(p.Id = r.IdPais)
            LEFT JOIN tipodocumento td ON(td.IdTipoDocumento = r.IdTipoDocumento)";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll();
        $resultado = array();
        $datos = array();
        foreach ($rows as $r) {
            $row = array (
                'idRegistro' => $r['Id'],
                'fechaAlta' => $r['FechaAlta'],
                'numero' => $r['Numero'],
                'apellidoNombre' => trim($r['Apellido']).' '.trim($r['Nombre']),
                'nacionalidad' => $r['Nacionalidad'],
                'tipoDocumento' => $r['NombreCompleto'],
                'numeroDocumento' => $r['NumeroDocumento'],
                'numeroPasaporte' => $r['NumeroPasaporte'],
                'fechaBaja' => $r['FechaBaja'],
                'distrito' => $r['DistritoOrigen']
            );
            array_push($datos, $row);
        }
        $resultado['estado'] = true;
        $resultado['mensaje'] = "OK";
        $resultado['datos'] = $datos;
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando registros: " . $e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function obtenerRegistroOtrosDistritosPorId($idRegistro){
    try {
        $db = Database::getConnection();
        $sql = "SELECT r.*, p.Nacionalidad, td.NombreCompleto
            FROM registro_dnu_260_otro_distrito r
            INNER JOIN paises p ON(p.Id = r.IdPais)
            INNER JOIN tipodocumento td ON(td.IdTipoDocumento = r.IdTipoDocumento)
            WHERE r.Id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idRegistro]);
        $resultado = array();
        $r = $stmt->fetch();
        if ($r) {
            $datos = array (
                'idRegistro' => $r['Id'],
                'fechaAlta' => $r['FechaAlta'],
                'numero' => $r['Numero'],
                'apellido' => $r['Apellido'],
                'nombre' => $r['Nombre'],
                'idPais' => $r['IdPais'],
                'sexo' => $r['Sexo'],
                'fechaNacimiento' => $r['FechaNacimiento'],
                'idTipoDocumento' => $r['IdTipoDocumento'],
                'numeroDocumento' => $r['NumeroDocumento'],
                'numeroPasaporte' => $r['NumeroPasaporte'],
                'universidad' => $r['Universidad'],
                'fechaTitulo' => $r['FechaExpedicion'],
                'especialidad' => $r['Especialidad'],
                'idUsuario' => $r['IdUsuario'],
                'fechaCarga' => $r['FechaCarga'],
                'fechaBaja' => $r['FechaBaja'],
                'distrito' => $r['DistritoOrigen'],
                'observacion' => $r['Observacion'],
                'nacionalidad' => $r['Nacionalidad'],
                'tipoDocumento' => $r['NombreCompleto']
             );
            $resultado['estado'] = true;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = true;
            $resultado['mensaje'] = "No hay registro en otros distritos";
            $resultado['clase'] = 'alert alert-info';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando registro en otros distritos: " . $e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function agregarRegistroOtrosDistritos($numero, $apellido, $nombre, $idPais, $sexo, $fechaNacimiento, $idTipoDocumento, $numeroDocumento, $numeroPasaporte, $fechaAlta, $universidad, $fechaTitulo, $especialidad, $fechaBaja, $distrito, $observacion)
{
    try {
        $db = Database::getConnection();
        $resultado = array();
        $fechaAlta = date('Y-m-d');
        $sql="INSERT INTO  registro_dnu_260_otro_distrito
            (FechaAlta, Numero, Apellido, Nombre, IdPais, Sexo, FechaNacimiento, IdTipoDocumento, NumeroDocumento, NumeroPasaporte, Universidad, FechaExpedicion, Especialidad, IdUsuario, FechaCarga, FechaBaja, DistritoOrigen, Observacion)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?, ?)";
        $stmt = $db->prepare($sql);
        $stmt->execute([$fechaAlta, $numero, $apellido, $nombre, $idPais, $sexo, $fechaNacimiento, $idTipoDocumento, $numeroDocumento, $numeroPasaporte, $universidad, $fechaTitulo, $especialidad, $_SESSION['user_id'], $fechaBaja, $distrito, $observacion]);
        $resultado['estado'] = TRUE;
        $resultado['mensaje'] = 'EL REGISTRO HA SIDO ACTUALIZADO CORRECTAMENTE';
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "ERROR AL AGREGAR REGISTRO: " . $e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function modificarRegistroOtrosDistritos($idRegistro, $apellido, $nombre, $idPais, $sexo, $fechaNacimiento, $idTipoDocumento, $numeroDocumento, $numeroPasaporte, $fechaAlta, $universidad, $fechaTitulo, $especialidad, $fechaBaja, $distrito, $numero, $observacion, $datosAnteriores) {
    try {
        $db = Database::getConnection();
        $db->beginTransaction();
        $resultado = array();
        $sql="UPDATE registro_dnu_260_otro_distrito
            SET FechaAlta = ?, Apellido = ?, Nombre = ?, IdPais = ?, Sexo = ?, FechaNacimiento = ?,
            IdTipoDocumento = ?, NumeroDocumento = ?, NumeroPasaporte = ?, Universidad = ?, FechaExpedicion = ?, Especialidad = ?, IdUsuario = ?, fechaBaja = ?, FechaCarga = NOW(), Numero = ?, DistritoOrigen = ?, Observacion = ?
            WHERE Id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$fechaAlta, $apellido, $nombre, $idPais, $sexo, $fechaNacimiento, $idTipoDocumento, $numeroDocumento, $numeroPasaporte, $universidad, $fechaTitulo, $especialidad, $_SESSION['user_id'], $fechaBaja, $numero, $distrito, $observacion, $idRegistro]);
        $sql="INSERT INTO log_tabla
                (Tabla, IdTabla, Fecha, TipoMovimiento, IdUsuario, Datos)
                VALUES ('registro_dnu_260_otro_distrito', ?, now(), 'modificacion', ?, ?)";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idRegistro, $_SESSION['user_id'], serialize($datosAnteriores)]);
        $resultado['estado'] = TRUE;
        $resultado['mensaje'] = 'EL REGISTRO HA SIDO ACTUALIZADO CORRECTAMENTE';
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
        $db->commit();
        return $resultado;
    } catch (PDOException $e) {
        if (isset($db) && $db->inTransaction()) $db->rollBack();
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "ERROR AL MODIFICAR DATOS: " . $e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
        return $resultado;
    }
}

    public function numeroDocumentoExiste($tipoDocumento, $numeroDocumento){
    try {
        $db = Database::getConnection();
        $sql="SELECT COUNT(Id) AS Cantidad FROM registro_dnu_260_otro_distrito WHERE IdTipoDocumento = ? AND NumeroDocumento = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$tipoDocumento, $numeroDocumento]);
        $resultado = array();
        $r = $stmt->fetch();
        $cantidad = $r ? $r['Cantidad'] : 0;
        if ($cantidad > 0) {
            $resultado['estado'] = TRUE;
            $resultado['mensaje'] = "Numero de Documento YA EXISTE EN LA BASE DE DATOS";
        } else {
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "No existe Numero de Documento";
        }
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "Error al buscar el Numero de Documento: " . $e->getMessage();
    }
    return $resultado;
}

/*
    public function borrarRegistro($idRegistro, $tipoBaja, $matricula, $revalida, $convalida, $constanciaLaboral) {
    try {
        $conect = conectar();
        mysqli_set_charset( $conect, 'utf8');
        $conect->autocommit(FALSE);
        $resultado = array();
        $sql="UPDATE registro_dnu_260_2020
                SET Estado = 'B', FechaCarga = NOW(), IdUsuario = ?
                WHERE Id = ?";
        $stmt = $conect->prepare($sql);
        $stmt->bind_param('ii', $_SESSION['user_id'], $idRegistro);
        $stmt->execute();
        $stmt->store_result();
        if(mysqli_stmt_errno($stmt)==0) {
            $sql="INSERT INTO registro_dnu_260_baja
                    (IdRegistro_dnu_260_2020, Fecha, TipoBaja, Matricula, Revalida, Convalida, ConstanciaLaboral, FechaCarga, IdUsuario)
                    VALUES (?, DATE(NOW()), ?, ?, ?, ?, ?, NOW(), ?)";
            $stmt = $conect->prepare($sql);
            $stmt->bind_param('isiiiii', $idRegistro, $tipoBaja, $matricula, $revalida, $convalida, $constanciaLaboral, $_SESSION['user_id']);
            $stmt->execute();
            $stmt->store_result();
            if(mysqli_stmt_errno($stmt)==0) {
                $resultado['estado'] = TRUE;
                $resultado['mensaje'] = "REGISTRO DADO DE BAJA";
                $resultado['clase'] = 'alert alert-success';
                $resultado['icono'] = 'glyphicon glyphicon-ok';
            } else {
                $resultado['estado'] = FALSE;
                $resultado['mensaje'] = "ERROR AL REGISTRAR BAJA";
                $resultado['clase'] = 'alert alert-danger';
                $resultado['icono'] = 'glyphicon glyphicon-remove';
            }
        } else {
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "ERROR AL DAR DE BAJA AL REGISTRO";
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-remove';
        }

        if ($resultado['estado']) {
            $conect->commit();
            desconectar($conect);
            return $resultado;
        } else {
            $conect->rollback();
            desconectar($conect);
            return $resultado;
        }
    } catch (mysqli_sql_exception $e) {
        $conect->rollback();
        desconectar($conect);
        return $resultado;
    }
}

    public function activarRegistro($idRegistro) {
    try {
        $conect = conectar();
        mysqli_set_charset( $conect, 'utf8');
        $conect->autocommit(FALSE);
        $resultado = array();
        $sql="UPDATE registro_dnu_260_2020
                SET Estado = 'A', FechaCarga = NOW(), IdUsuario = ?
                WHERE Id = ?";
        $stmt = $conect->prepare($sql);
        $stmt->bind_param('ii', $_SESSION['user_id'], $idRegistro);
        $stmt->execute();
        $stmt->store_result();
        if(mysqli_stmt_errno($stmt)==0) {
            $sql="UPDATE registro_dnu_260_baja
                SET Estado = 'B', FechaCarga = NOW(), IdUsuario = ?
                WHERE IdRegistro_dnu_260_2020 = ?";
            $stmt = $conect->prepare($sql);
            $stmt->bind_param('ii', $_SESSION['user_id'], $idRegistro);
            $stmt->execute();
            $stmt->store_result();
            if(mysqli_stmt_errno($stmt)==0) {
                $resultado['estado'] = TRUE;
                $resultado['mensaje'] = "REGISTRO REACTIVADO";
                $resultado['clase'] = 'alert alert-success';
                $resultado['icono'] = 'glyphicon glyphicon-ok';
            } else {
                $resultado['estado'] = FALSE;
                $resultado['mensaje'] = "ERROR AL REACTIVAR EL REGISTRO.";
                $resultado['clase'] = 'alert alert-danger';
                $resultado['icono'] = 'glyphicon glyphicon-remove';
            }
        } else {
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "ERROR AL REACTIVAR EL REGISTRO";
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-remove';
        }

        if ($resultado['estado']) {
            $conect->commit();
            desconectar($conect);
            return $resultado;
        } else {
            $conect->rollback();
            desconectar($conect);
            return $resultado;
        }
    } catch (mysqli_sql_exception $e) {
        $conect->rollback();
        desconectar($conect);
        return $resultado;
    }
}

    public function renovarRegistro($idRegistro, $fechaRenovacion) {
    try {
        $conect = conectar();
        mysqli_set_charset( $conect, 'utf8');
        $conect->autocommit(FALSE);
        $fechaVencimiento = sumarRestarSobreFecha($fechaRenovacion, 60, 'day', '+');
        $resultado = array();
        $sql="UPDATE registro_dnu_260_2020
                SET Estado = 'A', FechaCarga = NOW(), IdUsuario = ?, FechaVencimiento = ?
                WHERE Id = ?";
        $stmt = $conect->prepare($sql);
        $stmt->bind_param('isi', $_SESSION['user_id'], $fechaVencimiento, $idRegistro);
        $stmt->execute();
        $stmt->store_result();
        if(mysqli_stmt_errno($stmt)==0) {
            $sql="INSERT INTO registro_dnu_260_renovacion
                    (IdRegistro_dnu_260_2020, Fecha, FechaCarga, IdUsuario)
                    VALUES (?, ?, NOW(), ?)";
            $stmt = $conect->prepare($sql);
            $stmt->bind_param('isi', $idRegistro, $fechaRenovacion, $_SESSION['user_id']);
            $stmt->execute();
            $stmt->store_result();
            if(mysqli_stmt_errno($stmt)==0) {
                $resultado['estado'] = TRUE;
                $resultado['mensaje'] = "EL REGISTRO HA SIDO RENOVADO";
                $resultado['clase'] = 'alert alert-success';
                $resultado['icono'] = 'glyphicon glyphicon-ok';
            } else {
                $resultado['estado'] = FALSE;
                $resultado['mensaje'] = "ERROR AL RENOVAR EL REGISTRO.";
                $resultado['clase'] = 'alert alert-danger';
                $resultado['icono'] = 'glyphicon glyphicon-remove';
            }
        } else {
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "ERROR AL RENOVAR EL REGISTRO";
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-remove';
        }

        if ($resultado['estado']) {
            $conect->commit();
            desconectar($conect);
            return $resultado;
        } else {
            $conect->rollback();
            desconectar($conect);
            return $resultado;
        }
    } catch (mysqli_sql_exception $e) {
        $conect->rollback();
        desconectar($conect);
        return $resultado;
    }
}


    public function agregarRegistroCertificado($idRegistro, $paraEnviar, $distrito, $enviaMail, $mailDestino) {
    $conect = conectar();
    mysqli_set_charset( $conect, 'utf8');
    $resultado = array();
    $sql="INSERT INTO  registro_dnu_260_certificado
        (IdRegistro_dnu_260_2020, ParaEnviar, Distrito, EnviaMail, Mail, FechaEmision, IdUsuario)
        VALUES (?, ?, ?, ?, ?, NOW(), ?)";
    $stmt = $conect->prepare($sql);
    $stmt->bind_param('isissi', $idRegistro, $paraEnviar, $distrito, $enviaMail, $mailDestino, $_SESSION['user_id']);
    $stmt->execute();
    $stmt->store_result();
    if(mysqli_stmt_errno($stmt)==0) {
        $resultado['estado'] = TRUE;
        $resultado['idCertificado'] = mysqli_stmt_insert_id($stmt);
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } else {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "ERROR AL AGREGAR REGISTRO";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function obtenerDatosLaborales($idRegistro)
{
    $conect = conectar();
    mysqli_set_charset( $conect, 'utf8');
    $sql = "SELECT *
        FROM registro_dnu_260_laboral
        WHERE IdRegistro_dnu_260_2020 = ? AND Estado = 'A'";
    $stmt = $conect->prepare($sql);
    $stmt->bind_param('i', $idRegistro);
    $stmt->execute();
    $stmt->bind_result($idRegistroLaboral, $idRegistro, $entidad, $domicilioProfesional, $localidadProfesional, $cpProfesional, $telefonoProfesional, $estado, $idUsuario, $fechaCarga);
    $stmt->store_result();

    $resultado = array();
    if(mysqli_stmt_errno($stmt)==0)
    {
        if (mysqli_stmt_num_rows($stmt) >= 0)
        {
            $datos = array();
            while (mysqli_stmt_fetch($stmt))
            {
                $row = array (
                    'idRegistroLaboral' => $idRegistroLaboral,
                    'idRegistro' => $idRegistro,
                    'entidad' => $entidad,
                    'domicilioProfesional' => trim($domicilioProfesional),
                    'localidadProfesional' => $localidadProfesional,
                    'cpProfesional' => $cpProfesional,
                    'telefonoProfesional' => $telefonoProfesional,
                    'estado' => $estado,
                    'idUsuario' => $idUsuario,
                    'fechaCarga' => $fechaCarga
                 );
                array_push($datos, $row);
            }
            $resultado['estado'] = true;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = true;
            $resultado['mensaje'] = "No hay registros";
            $resultado['clase'] = 'alert alert-info';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } else {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando registros";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function obtenerDatosLaboralesPorId($idRegistroLaboral){
    $conect = conectar();
    mysqli_set_charset( $conect, 'utf8');
    $sql = "SELECT * FROM registro_dnu_260_laboral WHERE Id = ?";
    $stmt = $conect->prepare($sql);
    $stmt->bind_param('i', $idRegistroLaboral);
    $stmt->execute();
    $stmt->bind_result($idRegistroLaboral, $idRegistro, $entidad, $domicilioProfesional, $localidadProfesional, $cpProfesional, $telefonoProfesional, $estado, $idUsuario, $fechaCarga);
    $stmt->store_result();

    $resultado = array();
    if(mysqli_stmt_errno($stmt)==0)
    {
        if (mysqli_stmt_num_rows($stmt) >= 0)
        {
            $row = mysqli_stmt_fetch($stmt);
            $datos = array (
                'idRegistroLaboral' => $idRegistroLaboral,
                'idRegistro' => $idRegistro,
                'entidad' => $entidad,
                'domicilioProfesional' => $domicilioProfesional,
                'localidadProfesional' => $localidadProfesional,
                'codigoPostalProfesional' => $cpProfesional,
                'telefonoProfesional' => $telefonoProfesional,
                'estado' => $estado,
                'idUsuario' => $idUsuario,
                'fechaCarga' => $fechaCarga
             );
            $resultado['estado'] = true;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = true;
            $resultado['mensaje'] = "No hay registro";
            $resultado['clase'] = 'alert alert-info';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } else {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando registro";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function agregarDatoLaboral($idRegistro, $entidad, $domicilioProfesional, $localidadProfesional, $codigoPostalProfesional, $telefonoProfesional, $conect) {
    if (!isset($conect)) {
        $conect = conectar();
    }
    mysqli_set_charset( $conect, 'utf8');
    $resultado = array();
    $sql="INSERT INTO  registro_dnu_260_laboral
        (IdRegistro_dnu_260_2020, Entidad, DomicilioProfesional, LocalidadProfesional, CPProfesional, TelefonoProfesional, IdUsuario, FechaCarga)
        VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
    $stmt = $conect->prepare($sql);
    $stmt->bind_param('isssssi', $idRegistro, $entidad, $domicilioProfesional, $localidadProfesional, $codigoPostalProfesional, $telefonoProfesional, $_SESSION['user_id']);
    $stmt->execute();
    $stmt->store_result();
    if(mysqli_stmt_errno($stmt)==0) {
        //agrego el movimiento para hacer el seguimiento
        $idInspector = mysqli_stmt_insert_id($stmt);
        $resultado['estado'] = TRUE;
        $resultado['mensaje'] = 'EL DATO LABORAL HA SIDO AGREGADO';
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } else {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "ERROR AL AGREGAR DATO LABORAL ".mysqli_stmt_error($stmt);
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function modificarDatoLaboral($entidad, $domicilioProfesional, $localidadProfesional, $codigoPostalProfesional, $telefonoProfesional, $idRegistroLaboral, $datosAnteriores) {
    try {
        $conect = conectar();
        mysqli_set_charset( $conect, 'utf8');
        $conect->autocommit(FALSE);
        $resultado = array();
        $sql="UPDATE registro_dnu_260_laboral
            SET Entidad = ?, DomicilioProfesional = ?, LocalidadProfesional = ?, CPProfesional = ?, TelefonoProfesional = ?, IdUsuario = ?, FechaCarga = NOW()
            WHERE Id = ?";
        $stmt = $conect->prepare($sql);
        $stmt->bind_param('sssssii', $entidad, $domicilioProfesional, $localidadProfesional, $codigoPostalProfesional, $telefonoProfesional, $_SESSION['user_id'], $idRegistroLaboral);
        $stmt->execute();
        $stmt->store_result();
        if(mysqli_stmt_errno($stmt)==0) {
            $sql="INSERT INTO log_tabla
                    (Tabla, IdTabla, Fecha, TipoMovimiento, IdUsuario, Datos)
                    VALUES ('registro_dnu_260_laboral', ?, now(), 'modificacion', ?, ?)";
            $stmt = $conect->prepare($sql);
            $stmt->bind_param('iis', $idRegistroLaboral, $_SESSION['user_id'], serialize($datosAnteriores));
            $stmt->execute();
            $stmt->store_result();
            if(mysqli_stmt_errno($stmt)==0) {
                $resultado['estado'] = TRUE;
                $resultado['mensaje'] = "DATO LABORAL SE ACTUALIZARON CON EXITO";
                $resultado['clase'] = 'alert alert-success';
                $resultado['icono'] = 'glyphicon glyphicon-ok';
            } else {
                $resultado['estado'] = FALSE;
                $resultado['mensaje'] = "ERROR AL MODIFICAR DATO LABORAL";
                $resultado['clase'] = 'alert alert-danger';
                $resultado['icono'] = 'glyphicon glyphicon-remove';
            }
        } else {
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "ERROR AL MODIFICAR DATO LABORAL";
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-remove';
        }

        if ($resultado['estado']) {
            $resultado['mensaje'] = 'EL REGISTRO HA SIDO ACTUALIZADO CORRECTAMENTE';
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
            $conect->commit();
            desconectar($conect);
            return $resultado;
        } else {
            $conect->rollback();
            desconectar($conect);
            return $resultado;
        }
    } catch (mysqli_sql_exception $e) {
        $conect->rollback();
        desconectar($conect);
        return $resultado;
    }
}

    public function borrarDatoLaboral($idRegistroLaboral){
    $conect = conectar();
    mysqli_set_charset( $conect, 'utf8');
    $resultado = array();
    $sql="UPDATE registro_dnu_260_laboral
        SET Estado = 'B', IdUsuario = ?, FechaCarga = NOW()
        WHERE Id = ?";
    $stmt = $conect->prepare($sql);
    $stmt->bind_param('ii', $_SESSION['user_id'], $idRegistroLaboral);
    $stmt->execute();
    $stmt->store_result();
    if(mysqli_stmt_errno($stmt)==0) {
        $resultado['estado'] = TRUE;
        $resultado['mensaje'] = "DATO LABORAL SE BORRO CON EXITO";
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } else {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "ERROR AL BORRAR DATO LABORAL";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }

    return $resultado;
}
*/
}
