<?php
class registroDNU260Logic {

    public function obtenerTodos($distrito){
    if ($distrito == '1') {
        $porDistrito = " AND r.DistritoOrigen = 1";
    } else {
        $porDistrito = " AND r.DistritoOrigen <> 1";
    }
    try {
        $db = Database::getConnection();
        $sql = "SELECT r.Id, r.FechaAlta, r.Numero, r.Apellido, r.Nombre, p.Nacionalidad, td.NombreCompleto, r.NumeroDocumento,
            r.NumeroPasaporte, r.FechaIngreso, r.Estado, r.FechaVencimiento, r.DistritoOrigen
            FROM registro_dnu_260_2020 r
            INNER JOIN paises p ON(p.Id = r.IdPais)
            INNER JOIN tipodocumento td ON(td.IdTipoDocumento = r.IdTipoDocumento)
            WHERE r.Estado IN('A', 'B', 'V') ".$porDistrito;
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
                'fechaIngreso' => $r['FechaIngreso'],
                'estado' => $r['Estado'],
                'fechaVencimiento' => $r['FechaVencimiento'],
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
        $resultado['mensaje'] = "Error buscando registros";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}


    public function obtenerRegistroPorId($idRegistro){
    try {
        $db = Database::getConnection();
        $sql = "SELECT r.*, p.Nacionalidad, td.NombreCompleto
            FROM registro_dnu_260_2020 r
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
                'estadoCivil' => $r['EstadoCivil'],
                'idTipoDocumento' => $r['IdTipoDocumento'],
                'numeroDocumento' => $r['NumeroDocumento'],
                'numeroPasaporte' => $r['NumeroPasaporte'],
                'fechaIngreso' => $r['FechaIngreso'],
                'universidad' => $r['Universidad'],
                'fechaTitulo' => $r['FechaExpedicion'],
                'especialidad' => $r['Especialidad'],
                'domicilioParticular' => $r['DomicilioParticular'],
                'localidadParticular' => $r['LocalidadParticular'],
                'codigoPostalParticular' => $r['CPParticular'],
                'telefonoFijo' => $r['TelefonoFijo'],
                'telefonoMovil' => $r['TelefonoMovil'],
                'mail' => $r['Mail'],
                'estado' => $r['Estado'],
                'idUsuario' => $r['IdUsuario'],
                'fechaCarga' => $r['FechaCarga'],
                'fechaInicioValidaTitulo' => $r['FechaInicioValidaTitulo'],
                'fechaVencimiento' => $r['FechaVencimiento'],
                'distrito' => $r['DistritoOrigen'],
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
            $resultado['mensaje'] = "No hay registro";
            $resultado['clase'] = 'alert alert-info';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando registro";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function numeroDocumentoExiste($tipoDocumento, $numeroDocumento){
    try {
        $db = Database::getConnection();
        $sql="SELECT COUNT(Id) AS Cantidad FROM registro_dnu_260_2020 WHERE IdTipoDocumento = ? AND NumeroDocumento = ?";
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
        $resultado['mensaje'] = "Error al buscar el Numero de Documento";
    }

    return $resultado;
}

    public function obtenerNumeroRegistro() {
    try {
        $db = Database::getConnection();
        $sql="SELECT MAX(Numero) AS Numero FROM registro_dnu_260_2020 WHERE DistritoOrigen = 1";
        $stmt = $db->prepare($sql);
        $stmt->execute();

        $resultado = array();
        $r = $stmt->fetch();
        $numero = $r ? $r['Numero'] : null;
        if ($numero > 0) {
            $resultado['estado'] = TRUE;
            $resultado['numero'] = $numero + 1;
            $resultado['mensaje'] = "OK";
        } else {
            $resultado['estado'] = TRUE;
            $resultado['numero'] = 1000001;
            $resultado['mensaje'] = "OK, inicial";
        }
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "Error al buscar el Numero de Documento";
    }

    return $resultado;
}

    public function agregarRegistro($numero, $apellido, $nombre, $idPais, $sexo, $fechaNacimiento, $estadoCivil, $idTipoDocumento, $numeroDocumento, $numeroPasaporte, $fechaIngreso, $universidad, $fechaTitulo, $especialidad, $domicilioParticular, $localidadParticular, $codigoPostalParticular, $entidad, $domicilioProfesional, $localidadProfesional, $codigoPostalProfesional, $telefonoFijo, $telefonoMovil, $mail, $fechaInicioValidaTitulo, $telefonoProfesional, $distrito)
{
    try {
        $db = Database::getConnection();
        $db->beginTransaction();
        $resultado = array();
        $fechaAlta = date('Y-m-d');
        $fechaVencimiento = sumarRestarSobreFecha($fechaAlta, 60, 'day', '+');
        $sql="INSERT INTO  registro_dnu_260_2020
            (FechaAlta, Numero, Apellido, Nombre, IdPais, Sexo, FechaNacimiento, EstadoCivil, IdTipoDocumento, NumeroDocumento, NumeroPasaporte, FechaIngreso, Universidad, FechaExpedicion, Especialidad, DomicilioParticular, LocalidadParticular,
            CPParticular, TelefonoFijo, TelefonoMovil, Mail, IdUsuario, FechaCarga, FechaInicioValidaTitulo, FechaVencimiento, DistritoOrigen)
            VALUES (NOW(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?, ?)";
        $stmt = $db->prepare($sql);
        $stmt->execute([$numero, $apellido, $nombre, $idPais, $sexo, $fechaNacimiento, $estadoCivil, $idTipoDocumento, $numeroDocumento, $numeroPasaporte, $fechaIngreso, $universidad, $fechaTitulo, $especialidad, $domicilioParticular, $localidadParticular, $codigoPostalParticular, $telefonoFijo, $telefonoMovil, $mail, $_SESSION['user_id'], $fechaInicioValidaTitulo, $fechaVencimiento, $distrito]);

        $idRegistro = $db->lastInsertId();
        $resultado['estado'] = TRUE;

        //agregor los datos del domicilio laboral
        $resultado = $this->agregarDatoLaboral($idRegistro, $entidad, $domicilioProfesional, $localidadProfesional, $codigoPostalProfesional, $telefonoProfesional, $db);

        if ($resultado['estado']) {
            $resultado['mensaje'] = 'EL REGISTRO HA SIDO ACTUALIZADO CORRECTAMENTE';
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
            $db->commit();
            return $resultado;
        } else {
            $db->rollBack();
            return $resultado;
        }
    } catch (PDOException $e) {
        if (isset($db) && $db->inTransaction()) $db->rollBack();
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "ERROR AL AGREGAR REGISTRO ";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
        return $resultado;
    }
}

    public function modificarRegistro($apellido, $nombre, $idPais, $sexo, $fechaNacimiento, $estadoCivil,
            $idTipoDocumento, $numeroDocumento, $numeroPasaporte, $fechaIngreso, $universidad, $FechaExpedicion, $especialidad, $domicilioParticular, $localidadParticular, $codigoPostalParticular, $telefonoFijo, $telefonoMovil, $mail, $fechaInicioValidaTitulo, $idRegistro, $numero, $distrito, $datosAnteriores) {
    try {
        $db = Database::getConnection();
        $db->beginTransaction();
        $resultado = array();
        $sql="UPDATE registro_dnu_260_2020
            SET Apellido = ?, Nombre = ?, IdPais = ?, Sexo = ?, FechaNacimiento = ?, EstadoCivil = ?,
            IdTipoDocumento = ?, NumeroDocumento = ?, NumeroPasaporte = ?, FechaIngreso = ?, Universidad = ?, FechaExpedicion = ?,
            Especialidad = ?, DomicilioParticular = ?, LocalidadParticular = ?, CPParticular = ?, TelefonoFijo = ?, TelefonoMovil = ?, Mail = ?, IdUsuario = ?, FechaInicioValidaTitulo = ?, FechaCarga = NOW(), Numero = ?, DistritoOrigen = ?
            WHERE Id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$apellido, $nombre, $idPais, $sexo, $fechaNacimiento, $estadoCivil, $idTipoDocumento, $numeroDocumento, $numeroPasaporte, $fechaIngreso, $universidad, $FechaExpedicion, $especialidad, $domicilioParticular, $localidadParticular, $codigoPostalParticular, $telefonoFijo, $telefonoMovil, $mail, $_SESSION['user_id'], $fechaInicioValidaTitulo, $numero, $distrito, $idRegistro]);

        $sql="INSERT INTO log_tabla
                (Tabla, IdTabla, Fecha, TipoMovimiento, IdUsuario, Datos)
                VALUES ('registro_dnu_260_2020', ?, now(), 'modificacion', ?, ?)";
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
        $resultado['mensaje'] = "ERROR AL MODIFICAR REGISTRO";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
        return $resultado;
    }
}

    public function borrarRegistro($idRegistro, $tipoBaja, $matricula, $revalida, $convalida, $constanciaLaboral) {
    try {
        $db = Database::getConnection();
        $db->beginTransaction();
        $resultado = array();
        $sql="UPDATE registro_dnu_260_2020
                SET Estado = 'B', FechaCarga = NOW(), IdUsuario = ?
                WHERE Id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$_SESSION['user_id'], $idRegistro]);

        $sql="INSERT INTO registro_dnu_260_baja
                (IdRegistro_dnu_260_2020, Fecha, TipoBaja, Matricula, Revalida, Convalida, ConstanciaLaboral, FechaCarga, IdUsuario)
                VALUES (?, DATE(NOW()), ?, ?, ?, ?, ?, NOW(), ?)";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idRegistro, $tipoBaja, $matricula, $revalida, $convalida, $constanciaLaboral, $_SESSION['user_id']]);

        $resultado['estado'] = TRUE;
        $resultado['mensaje'] = "REGISTRO DADO DE BAJA";
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
        $db->commit();
        return $resultado;
    } catch (PDOException $e) {
        if (isset($db) && $db->inTransaction()) $db->rollBack();
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "ERROR AL DAR DE BAJA AL REGISTRO";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
        return $resultado;
    }
}

    public function activarRegistro($idRegistro) {
    try {
        $db = Database::getConnection();
        $db->beginTransaction();
        $resultado = array();
        $sql="UPDATE registro_dnu_260_2020
                SET Estado = 'A', FechaCarga = NOW(), IdUsuario = ?
                WHERE Id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$_SESSION['user_id'], $idRegistro]);

        $sql="UPDATE registro_dnu_260_baja
            SET Estado = 'B', FechaCarga = NOW(), IdUsuario = ?
            WHERE IdRegistro_dnu_260_2020 = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$_SESSION['user_id'], $idRegistro]);

        $resultado['estado'] = TRUE;
        $resultado['mensaje'] = "REGISTRO REACTIVADO";
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
        $db->commit();
        return $resultado;
    } catch (PDOException $e) {
        if (isset($db) && $db->inTransaction()) $db->rollBack();
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "ERROR AL REACTIVAR EL REGISTRO";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
        return $resultado;
    }
}

    public function renovarRegistro($idRegistro, $fechaRenovacion) {
    try {
        $db = Database::getConnection();
        $db->beginTransaction();
        $fechaVencimiento = sumarRestarSobreFecha($fechaRenovacion, 60, 'day', '+');
        $resultado = array();
        $sql="UPDATE registro_dnu_260_2020
                SET Estado = 'A', FechaCarga = NOW(), IdUsuario = ?, FechaVencimiento = ?
                WHERE Id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$_SESSION['user_id'], $fechaVencimiento, $idRegistro]);

        $sql="INSERT INTO registro_dnu_260_renovacion
                (IdRegistro_dnu_260_2020, Fecha, FechaCarga, IdUsuario)
                VALUES (?, ?, NOW(), ?)";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idRegistro, $fechaRenovacion, $_SESSION['user_id']]);

        $resultado['estado'] = TRUE;
        $resultado['mensaje'] = "EL REGISTRO HA SIDO RENOVADO";
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
        $db->commit();
        return $resultado;
    } catch (PDOException $e) {
        if (isset($db) && $db->inTransaction()) $db->rollBack();
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "ERROR AL RENOVAR EL REGISTRO";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
        return $resultado;
    }
}


    public function agregarRegistroCertificado($idRegistro, $paraEnviar, $distrito, $enviaMail, $mailDestino) {
    try {
        $db = Database::getConnection();
        $resultado = array();
        $sql="INSERT INTO  registro_dnu_260_certificado
            (IdRegistro_dnu_260_2020, ParaEnviar, Distrito, EnviaMail, Mail, FechaEmision, IdUsuario)
            VALUES (?, ?, ?, ?, ?, NOW(), ?)";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idRegistro, $paraEnviar, $distrito, $enviaMail, $mailDestino, $_SESSION['user_id']]);
        $resultado['estado'] = TRUE;
        $resultado['idCertificado'] = $db->lastInsertId();
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "ERROR AL AGREGAR REGISTRO";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function obtenerDatosLaborales($idRegistro)
{
    try {
        $db = Database::getConnection();
        $sql = "SELECT *
            FROM registro_dnu_260_laboral
            WHERE IdRegistro_dnu_260_2020 = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idRegistro]);
        $rows = $stmt->fetchAll();

        $resultado = array();
        $datos = array();
        foreach ($rows as $r) {
            $estado = $r['Estado'];
            if ($estado == 'A') {
                $estado = "Activo";
            } else {
                if ($estado == 'B') {
                    $estado = 'BAJA';
                } else {
                    $estado = "";
                }
            }
            $row = array (
                'idRegistroLaboral' => $r['Id'],
                'idRegistro' => $r['IdRegistro_dnu_260_2020'],
                'entidad' => $r['Entidad'],
                'domicilioProfesional' => trim($r['DomicilioProfesional']),
                'localidadProfesional' => $r['LocalidadProfesional'],
                'cpProfesional' => $r['CPProfesional'],
                'telefonoProfesional' => $r['TelefonoProfesional'],
                'estado' => $estado,
                'idUsuario' => $r['IdUsuario'],
                'fechaCarga' => $r['FechaCarga']
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
        $resultado['mensaje'] = "Error buscando registros";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function obtenerDatosLaboralesPorId($idRegistroLaboral){
    try {
        $db = Database::getConnection();
        $sql = "SELECT * FROM registro_dnu_260_laboral WHERE Id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idRegistroLaboral]);

        $resultado = array();
        $r = $stmt->fetch();
        if ($r) {
            $datos = array (
                'idRegistroLaboral' => $r['Id'],
                'idRegistro' => $r['IdRegistro_dnu_260_2020'],
                'entidad' => $r['Entidad'],
                'domicilioProfesional' => $r['DomicilioProfesional'],
                'localidadProfesional' => $r['LocalidadProfesional'],
                'codigoPostalProfesional' => $r['CPProfesional'],
                'telefonoProfesional' => $r['TelefonoProfesional'],
                'estado' => $r['Estado'],
                'idUsuario' => $r['IdUsuario'],
                'fechaCarga' => $r['FechaCarga']
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
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando registro";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function agregarDatoLaboral($idRegistro, $entidad, $domicilioProfesional, $localidadProfesional, $codigoPostalProfesional, $telefonoProfesional, $db = null) {
    try {
        if (!isset($db)) {
            $db = Database::getConnection();
        }
        $resultado = array();
        $sql="INSERT INTO  registro_dnu_260_laboral
            (IdRegistro_dnu_260_2020, Entidad, DomicilioProfesional, LocalidadProfesional, CPProfesional, TelefonoProfesional, IdUsuario, FechaCarga)
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idRegistro, $entidad, $domicilioProfesional, $localidadProfesional, $codigoPostalProfesional, $telefonoProfesional, $_SESSION['user_id']]);
        $resultado['estado'] = TRUE;
        $resultado['mensaje'] = 'EL DATO LABORAL HA SIDO AGREGADO';
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "ERROR AL AGREGAR DATO LABORAL ".$e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function modificarDatoLaboral($entidad, $domicilioProfesional, $localidadProfesional, $codigoPostalProfesional, $telefonoProfesional, $idRegistroLaboral, $datosAnteriores) {
    try {
        $db = Database::getConnection();
        $db->beginTransaction();
        $resultado = array();
        $sql="UPDATE registro_dnu_260_laboral
            SET Entidad = ?, DomicilioProfesional = ?, LocalidadProfesional = ?, CPProfesional = ?, TelefonoProfesional = ?, IdUsuario = ?, FechaCarga = NOW()
            WHERE Id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$entidad, $domicilioProfesional, $localidadProfesional, $codigoPostalProfesional, $telefonoProfesional, $_SESSION['user_id'], $idRegistroLaboral]);

        $sql="INSERT INTO log_tabla
                (Tabla, IdTabla, Fecha, TipoMovimiento, IdUsuario, Datos)
                VALUES ('registro_dnu_260_laboral', ?, now(), 'modificacion', ?, ?)";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idRegistroLaboral, $_SESSION['user_id'], serialize($datosAnteriores)]);

        $resultado['estado'] = TRUE;
        $resultado['mensaje'] = 'EL REGISTRO HA SIDO ACTUALIZADO CORRECTAMENTE';
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
        $db->commit();
        return $resultado;
    } catch (PDOException $e) {
        if (isset($db) && $db->inTransaction()) $db->rollBack();
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "ERROR AL MODIFICAR DATO LABORAL";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
        return $resultado;
    }
}

    public function borrarDatoLaboral($idRegistroLaboral){
    try {
        $db = Database::getConnection();
        $resultado = array();
        $sql="UPDATE registro_dnu_260_laboral
            SET Estado = 'B', IdUsuario = ?, FechaCarga = NOW()
            WHERE Id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$_SESSION['user_id'], $idRegistroLaboral]);
        $resultado['estado'] = TRUE;
        $resultado['mensaje'] = "DATO LABORAL SE BORRO CON EXITO";
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "ERROR AL BORRAR DATO LABORAL";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

/* se actualizo para sacar los campos laborales */

    public function agregarRegistroCompleto($numero, $apellido, $nombre, $idPais, $sexo, $fechaNacimiento, $estadoCivil,
            $idTipoDocumento, $numeroDocumento, $numeroPasaporte, $fechaIngreso, $universidad, $FechaExpedicion, $especialidad,
            $domicilioParticular, $localidadParticular, $codigoPostalParticular, $domicilioProfesional, $localidadProfesional,
            $codigoPostalProfesional, $telefonoFijo, $telefonoMovil, $mail, $fechaInicioValidaTitulo) {
    try {
        $db = Database::getConnection();
        $resultado = array();
        $sql="INSERT INTO  registro_dnu_260_2020
            (FechaAlta, Numero, Apellido, Nombre, IdPais, Sexo, FechaNacimiento, EstadoCivil, IdTipoDocumento, NumeroDocumento,
            NumeroPasaporte, FechaIngreso, Universidad, FechaExpedicion, Especialidad, DomicilioParticular, LocalidadParticular,
            CPParticular, DomicilioProfesional, LocalidadProfesional, CPProfesional, TelefonoFijo, TelefonoMovil, Mail, IdUsuario, FechaCarga, FechaInicioValidaTitulo)
            VALUES (NOW(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?)";
        $stmt = $db->prepare($sql);
        $stmt->execute([$numero, $apellido, $nombre, $idPais, $sexo, $fechaNacimiento, $estadoCivil,
                $idTipoDocumento, $numeroDocumento, $numeroPasaporte, $fechaIngreso, $universidad, $FechaExpedicion, $especialidad,
                $domicilioParticular, $localidadParticular, $codigoPostalParticular, $domicilioProfesional, $localidadProfesional,
                $codigoPostalProfesional, $telefonoFijo, $telefonoMovil, $mail, $_SESSION['user_id'], $fechaInicioValidaTitulo]);
        $resultado['estado'] = TRUE;
        $resultado['mensaje'] = 'EL REGISTRO HA SIDO AGREGADO';
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "ERROR AL AGREGAR REGISTRO";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function modificarRegistroCompleto($apellido, $nombre, $idPais, $sexo, $fechaNacimiento, $estadoCivil,
            $idTipoDocumento, $numeroDocumento, $numeroPasaporte, $fechaIngreso, $universidad, $FechaExpedicion, $especialidad,
            $domicilioParticular, $localidadParticular, $codigoPostalParticular, $domicilioProfesional, $localidadProfesional,
            $codigoPostalProfesional, $telefonoFijo, $telefonoMovil, $mail, $fechaInicioValidaTitulo, $idRegistro, $datosAnteriores) {
    try {
        $db = Database::getConnection();
        $db->beginTransaction();
        $resultado = array();
        $sql="UPDATE registro_dnu_260_2020
            SET Apellido = ?, Nombre = ?, IdPais = ?, Sexo = ?, FechaNacimiento = ?, EstadoCivil = ?,
            IdTipoDocumento = ?, NumeroDocumento = ?, NumeroPasaporte = ?, FechaIngreso = ?, Universidad = ?, FechaExpedicion = ?,
            Especialidad = ?, DomicilioParticular = ?, LocalidadParticular = ?, CPParticular = ?, DomicilioProfesional = ?,
            LocalidadProfesional = ?, CPProfesional = ?, TelefonoFijo = ?, TelefonoMovil = ?, Mail = ?, IdUsuario = ?, FechaInicioValidaTitulo = ?, FechaCarga = NOW()
            WHERE Id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$apellido, $nombre, $idPais, $sexo, $fechaNacimiento, $estadoCivil,
                $idTipoDocumento, $numeroDocumento, $numeroPasaporte, $fechaIngreso, $universidad, $FechaExpedicion, $especialidad,
                $domicilioParticular, $localidadParticular, $codigoPostalParticular, $domicilioProfesional, $localidadProfesional,
                $codigoPostalProfesional, $telefonoFijo, $telefonoMovil, $mail, $_SESSION['user_id'], $fechaInicioValidaTitulo, $idRegistro]);

        $sql="INSERT INTO log_tabla
                (Tabla, IdTabla, Fecha, TipoMovimiento, IdUsuario, Datos)
                VALUES ('registro_dnu_260_2020', ?, now(), 'modificacion', ?, ?)";
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
        $resultado['mensaje'] = "ERROR AL MODIFICAR REGISTRO";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
        return $resultado;
    }
}
}
