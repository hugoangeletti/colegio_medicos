<?php
class usuarioLogic {

    public function validarUsuario($userName, $clave)
{
    $result = array();
    try {
        $db = Database::getConnection();
        $sql="select Id, Usuario, Clave, NombreCompleto, TipoUsuario, UltimoAcceso, Estado  from usuario where Usuario = ? and Clave = ? and Estado = 'A'";
        $stmt = $db->prepare($sql);
        $stmt->execute([$userName, $clave]);
        $datos = array();
        $row = $stmt->fetch();
        if ($row) {
            $datos = array(
                    'idUsuario' => $row['Id'],
                    'nombreUsuario' => $row['Usuario'],
                    'tipoUsuario' => $row['TipoUsuario'],
                    'nombreCompleto' => $row['NombreCompleto']
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

    public function obtenerUsuarioPorId($id)
{
    $result = array();
    try {
        $db = Database::getConnection();
        $sql="select Id, Usuario, Clave, NombreCompleto, TipoUsuario, UltimoAcceso, Estado  from usuario where Id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if ($row) {
            $datos = array(
                'idUsuario' => $row['Id'],
                'nombreUsuario' => $row['Usuario'],
                'clave' => $row['Clave'],
                'nombreCompleto' => $row['NombreCompleto'],
                'tipoUsuario' => $row['TipoUsuario'],
                'ultimoAcceso' => $row['UltimoAcceso'],
                'estado' => $row['Estado']
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
    public function obtenerUsuarios()
{
    try {
        $db = Database::getConnection();
        $sql = "SELECT Id, Usuario, Clave, NombreCompleto, TipoUsuario, UltimoAcceso, Estado  FROM usuario ORDER BY Usuario";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $datos = array();
        while ($row = $stmt->fetch()) {
            $datos[] = array(
                'idUsuario' => $row['Id'],
                'nombreUsuario' => $row['Usuario'],
                'clave' => $row['Clave'],
                'nombreCompleto' => $row['NombreCompleto'],
                'tipoUsuario' => $row['TipoUsuario'],
                'ultimoAcceso' => $row['UltimoAcceso'],
                'estado' => $row['Estado']
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

    public function obtenerAplicacionesPorUsuario($idUsuario)
{
    try {
        $db = Database::getConnection();
        $sql = "SELECT a.Nombre, a.IdApp
        FROM usuario u
        INNER JOIN usuarioapprol uar ON uar.IdUsuario = u.Id
        INNER JOIN approl ar ON ar.Id = uar.IdAppRol
        INNER JOIN app a ON a.IdApp = ar.IdApp
        WHERE u.Id = ?
        GROUP BY a.Nombre, a.IdApp
        ORDER BY a.Nombre";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idUsuario]);
        $datos = array();
        while ($row = $stmt->fetch()) {
            $datos[] = array(
                'nombre' => $row['Nombre'],
                'idApp' => $row['IdApp']
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

    public function obtenerUsuarioPorNombre($userName)
{
    try {
        $db = Database::getConnection();
        $sql="select Id, Usuario from usuario where Usuario = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([trim($userName)]);
        $row = $stmt->fetch();
        if ($row) {
            $datos = array(
                'IdUsuario' => $row['Id'],
                'NombreUsuario' => $row['Usuario']
            );
            $resultado['estado'] = TRUE;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "NO SE ENCONTRO EL USUARIO";
            $resultado['clase'] = 'alert alert-info';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "ERROR BUSCANDO USUARIO";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function verificarRolUsuario($idUsuario, $idRol)
{
    try {
        $db = Database::getConnection();
        $sql = "SELECT COUNT(*) AS Cantidad
        FROM usuarioapprol
        INNER JOIN approl ON(approl.Id = usuarioapprol.IdAppRol)
        WHERE usuarioapprol.IdAppRol = ? AND usuarioapprol.IdUsuario = ? AND approl.Estado = 'A'";
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

    public function verificarAppUsuario($idUsuario, $idApp)
{
    try {
        $db = Database::getConnection();
        $sql="SELECT COUNT(*) AS Cantidad
        FROM usuarioapprol
        INNER JOIN approl ON(approl.Id = usuarioapprol.IdAppRol)
        WHERE approl.IdApp = ? AND usuarioapprol.IdUsuario = ? AND approl.Estado = 'A'";
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

    public function obtenerRolUsuario($idUsuario, $idApp)
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

    public function obtenerRolesPorUsuario($idUsuario)
{
    try {
        $db = Database::getConnection();
        $sql = "SELECT a.IdApp, a.Nombre, COUNT(c.IdUsuario) AS cantidadRoles
            FROM app a
            INNER JOIN approl b ON b.IdApp = a.IdApp
            LEFT JOIN usuarioapprol c ON c.IdUsuario = ? AND c.IdAppRol = b.Id
            WHERE a.Tipo = 'P' AND a.Estado = 'A'
            GROUP BY a.IdApp
            ORDER BY a.Nombre";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idUsuario]);
        $datos = array();
        while ($row = $stmt->fetch()) {
            $datos[] = array(
                'idApp' => $row['IdApp'],
                'nombre' => $row['Nombre'],
                'cantidadRoles' => $row['cantidadRoles']
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

    public function obtenerLeyendaRoles($idUsuario){
    try {
        $db = Database::getConnection();
        $sql = "select approl.Nombre
            from usuarioapprol
            inner join approl on (usuarioapprol.IdAppRol = approl.Id)
            where usuarioapprol.IdUsuario = ?";
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

    public function actualizarUsuarioRol($idApp, $idUsuario, $arrayIdRoles){
    $resultado['estado'] = TRUE;
    try {
        $db = Database::getConnection();
        if (isset($idApp) && $idApp <> "") {
            $sql = "DELETE uar
                    FROM usuarioapprol uar
                    INNER JOIN approl ur ON ur.Id = uar.IdAppRol
                    WHERE uar.IdUsuario = ? AND ur.IdApp = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$idUsuario, $idApp]);
        } else {
            $sql = "DELETE FROM usuarioapprol WHERE IdUsuario = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$idUsuario]);
        }
        //crea los nuevos roles
        $cantidadRoles = sizeof($arrayIdRoles);
        for($i = 0; $i < $cantidadRoles; $i++){
            $sql = "INSERT INTO usuarioapprol (IdUsuario, IdAppRol) VALUES (?, ?)";
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

    public function agregarUsuario($nombreUsuario, $clave, $nombreCompleto, $tipoUsuario){
    $resultado = array();
    try {
        $db = Database::getConnection();
        $sql = "INSERT INTO usuario (Usuario, Clave, NombreCompleto, TipoUsuario) "
                . "VALUES (?, ?, ?, ?)";
        $stmt = $db->prepare($sql);
        $stmt->execute([$nombreUsuario, $clave, $nombreCompleto, $tipoUsuario]);
        $resultado['estado'] = TRUE;
        $resultado['mensaje'] = "OK";
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "ERROR AGREGANDO USUARIO";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function actualizarUsuario($idUsuario, $nombreUsuario, $clave, $nombreCompleto, $tipoUsuario, $estado){
    $resultado = array();
    try {
        $db = Database::getConnection();
        $sql = "UPDATE usuario
                SET Usuario = ?,
                NombreCompleto = ?,
                Estado = ?,
                Clave = ?,
                TipoUsuario = ?
                WHERE Id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$nombreUsuario, $nombreCompleto, $estado, $clave, $tipoUsuario, $idUsuario]);
        $resultado['estado'] = TRUE;
        $resultado['mensaje'] = "Ok";
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "Error al actualizar el usuario";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function actualizarClaveUsuario($idUsuario, $clave){
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

    public function logUsuario($idUsuario){
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
}
