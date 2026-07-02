<?php
function validarUsuario($userName, $clave)
{
    $result = array();
    try {
        $db = Database::getConnection();
        $sql="select Id from usuario where Usuario = ? and Clave = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$userName, $clave]);
        $datos = array();
        $row = $stmt->fetch();
        if ($row) {
            $datos = array(
                    'idUsuario' => $row['Id'],
                    'nombreUsuario' => $userName
                    );
            $result = array(
                'estado' => true,
                'mensaje' => "Ok",
                'datos' => $datos
            );
        } else {
            $result = array(
                'estado' => TRUE,
                'mensaje' => "El usuario y contraseña ingresados no son validos",
                'datos' => $datos
                );
        }
    } catch (PDOException $e) {
        $result = array(
                'estado' => false,
                'mensaje' => "Error al acceder a los datos, intente mas tarde"
                );
    }
    return $result;
}

function obtenerUsuarioPorId($id)
{
    $result = array();
    try {
        $db = Database::getConnection();
        $sql="select * from pa_usuario where IdUsuario = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if ($row) {
            $datos = array(
                'idUsuario' => $row['IdUsuario'],
                'nombreUsuario' => $row['UserName'],
                'clave' => $row['Clave'],
                'estado' => $row['Estado'],
                'correo' => $row['Correo'],
                'cambioClave' => $row['CambioClave'],
                'ultimoAcceso' => $row['UltimoAcceso'],
                'intentosFallidos' => $row['IntentosFallidos']
            );
            $result['datos'] = $datos;
            $result['estado'] = TRUE;
            $result['mensaje'] = "Ok";
        } else {
            $result['estado'] = FALSE;
            $result['mensaje'] = "No se encontro el usuario, vuelva a intentar.";
        }
    } catch (PDOException $e) {
        $result['estado'] = FALSE;
        $result['mensaje'] = "Error: " . $e->getMessage();
    }
    return $result;
}

//HUGO
function obtenerUsuarios()
{
    try {
        $db = Database::getConnection();
        $sql = "SELECT * FROM pa_usuario ORDER BY UserName";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $datos = array();
        while ($row = $stmt->fetch()) {
            $datos[] = array(
                'idUsuario' => $row['IdUsuario'],
                'nombreUsuario' => $row['UserName'],
                'clave' => $row['Clave'],
                'estado' => $row['Estado'],
                'correo' => $row['Correo'],
                'cambioClave' => $row['CambioClave'],
                'ultimoAcceso' => $row['UltimoAcceso'],
                'intentosFallidos' => $row['IntentosFallidos']
            );
        }
        $result['estado'] = TRUE;
        $result['mensaje'] = 'OK';
        $result['datos'] = $datos;
    } catch (PDOException $e) {
        $result['estado'] = FALSE;
        $result['mensaje'] = 'Error en la consulta.';
        $result['datos'] = array();
    }
    return $result;
}

function obtenerUsuarioPorNombre($userName)
{
    try {
        $db = Database::getConnection();
        $sql="select IdUsuario, NombreUsuario from pa_usuario where NombreUsuario = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$userName]);
        $row = $stmt->fetch();
        if ($row) {
            $result = array(
                'estado' => true,
                'IdUsuario' => $row['IdUsuario'],
                'NombreUsuario' => $row['NombreUsuario']
            );
        } else {
            $result = array(
                'estado' => false
            );
        }
    } catch (PDOException $e) {
        $result = array('estado' => false);
    }
    return $result;
}

function verificarRolUsuario($idUsuario, $idRol)
{
    try {
        $db = Database::getConnection();
        $sql="select count(*) as Cantidad from usuarioapprol WHERE IdAppRol = ? and IdUsuario = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idRol, $idUsuario]);
        $row = $stmt->fetch();
        $result = FALSE;
        if ($row && $row['Cantidad'] > 0) {
            $result = TRUE;
        }
    } catch (PDOException $e) {
        $result = FALSE;
    }
    return $result;
}

function verificarAppUsuario($idUsuario, $idApp)
{
    try {
        $db = Database::getConnection();
        $sql="SELECT COUNT(*) AS Cantidad
        FROM usuarioapprol
        INNER JOIN approl ON(approl.Id = usuarioapprol.IdAppRol)
        WHERE approl.IdApp = ? AND usuarioapprol.IdUsuario = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idApp, $idUsuario]);
        $row = $stmt->fetch();
        $result = FALSE;
        if ($row && $row['Cantidad'] > 0) {
            $result = TRUE;
        }
    } catch (PDOException $e) {
        $result = FALSE;
    }
    return $result;
}

function obtenerRolUsuario($idUsuario, $idApp)
{
    try {
        $db = Database::getConnection();
        $sql = "select approl.Id, approl.Nombre, approl.Link
        from approl
        inner join usuarioapprol on(usuarioapprol.IdAppRol = approl.Id)
        where approl.IdApp = ? and usuarioapprol.IdUsuario = ?
        and approl.Estado = 'A' and approl.EnMenu = 'S'";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idApp, $idUsuario]);
        $datos = array();
        while ($row = $stmt->fetch()) {
            $datos[] = array(
                'idAppRol' => $row['Id'],
                'nombre' => $row['Nombre'],
                'link' => $row['Link']
            );
        }
        $result['estado'] = TRUE;
        $result['mensaje'] = 'OK';
        $result['datos'] = $datos;
    } catch (PDOException $e) {
        $result['estado'] = FALSE;
        $result['mensaje'] = 'Error en la consulta.';
        $result['datos'] = array();
    }
    return $result;
}

function obtenerLeyendaRoles($idUsuario){
    try {
        $db = Database::getConnection();
        $sql = "select pa_rol.Nombre
            from pa_usuariorol
            inner join pa_rol on (pa_usuariorol.IdRol = pa_rol.Id)
            where pa_usuariorol.IdUsuario = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idUsuario]);
        $leyenda="";
        while ($row = $stmt->fetch()) {
            $leyenda = $leyenda.$row['Nombre']." - ";
        }
        $hasta=(strlen($leyenda)-2);
        $leyenda=substr($leyenda,0,$hasta);
    } catch (PDOException $e) {
        $leyenda = "";
    }
    return $leyenda;
}

function actualizarUsuarioRol($idUsuario, $arrayIdRoles){
    $resultado['estado'] = TRUE;
    try {
        $db = Database::getConnection();
        $sql = "DELETE FROM pa_usuariorol WHERE IdUsuario = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idUsuario]);
        //crea los nuevos roles
        $cantidadRoles = sizeof($arrayIdRoles);
        for($i = 0; $i < $cantidadRoles; $i++){
            $sql = "INSERT INTO pa_usuariorol (IdUsuario, IdRol) VALUES (?, ?)";
            $stmt = $db->prepare($sql);
            $stmt->execute([$idUsuario, $arrayIdRoles[$i]]);
        }
        return $resultado;
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "Error: " . $e->getMessage();
        return $resultado;
    }
}

function agregarUsuario($nombreUsuario, $clave, $estado, $correo){
    $resultado = array();
    $claveHash = hashData($clave);
    try {
        $db = Database::getConnection();
        $sql = "INSERT INTO pa_usuario (UserName, Clave, Estado, Correo, CambioClave) "
                . "VALUES (?, ?, ?, ?, now())";
        $stmt = $db->prepare($sql);
        $stmt->execute([$nombreUsuario, $claveHash, $estado, $correo]);
        $resultado['estado'] = TRUE;
        $resultado['mensaje'] = "Ok";
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "Error al insertar el usuario";
    }
    return $resultado;
}

function actualizarUsuario($idUsuario, $nombreUsuario, $correo, $estado){
    $resultado = array();
    try {
        $db = Database::getConnection();
        $sql = "UPDATE pa_usuario "
                . "SET UserName = ?, "
                . "Estado = ?, "
                . "Correo = ?, "
                . "CambioClave = now() "
                . "WHERE IdUsuario = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$nombreUsuario, $estado, $correo, $idUsuario]);
        $resultado['estado'] = TRUE;
        $resultado['mensaje'] = "Ok";
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "Error al actualizar el usuario";
    }
    return $resultado;
}

function actualizarClaveUsuario($idUsuario, $clave){
    $resultado = array();
    $claveHash = hashData($clave);
    try {
        $db = Database::getConnection();
        $sql = "UPDATE pa_usuario "
                . "SET Clave = ?, "
                . "CambioClave = now() "
                . "WHERE IdUsuario = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$claveHash, $idUsuario]);
        $resultado['estado'] = TRUE;
        $resultado['mensaje'] = "Ok";
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "Error al actualizar la contrase&ntilde;a del usuario";
    }
    return $resultado;
}

function logUsuario($idUsuario){
    $resultado = array();
    try {
        $db = Database::getConnection();
        $sql = "UPDATE usuario SET UltimoAcceso = now() WHERE Id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idUsuario]);
        $resultado['estado'] = TRUE;
        $resultado['mensaje'] = "Ok";
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "Error al actualizar la contrase&ntilde;a del usuario";
    }
    return $resultado;
}
