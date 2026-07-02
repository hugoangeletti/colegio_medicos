<?php
class personaLogic {

    public function obtenerPersonaPorId($idPersona) {
    try {
        $db = Database::getConnection();
        $sql="SELECT * FROM persona WHERE Id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idPersona]);
        $row = $stmt->fetch();

        $resultado = array();
        if ($row) {
            $datos = array(
                    'idPersona' => $row['Id'],
                    'apellido' => $row['Apellido'],
                    'nombre' => $row['Nombres'],
                    'sexo' => $row['Sexo'],
                    'tipoDocumento' => $row['TipoDocumento'],
                    'numeroDocumento' => $row['NumeroDocumento'],
                    'fechaNacimiento' => $row['FechaNacimiento'],
                    'idNacionalidad' => $row['IdPaises'],
                    'fechaCarga' => $row['FechaCarga'],
                    'fechaActualizacion' => $row['FechaActualizacion'],
                    'estado' => $row['Estado']
                    );

            $resultado['datos'] = $datos;
            $resultado['mensaje'] = "OK";
            $resultado['estado'] = TRUE;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['datos'] = NULL;
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "No hay persona ".$idPersona;
            $resultado['clase'] = 'alert alert-info';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando persona";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }

    return $resultado;
}

    public function obtenerPersonaPorIdColegiado($idColegiado) {
    try {
        $db = Database::getConnection();
        $sql="SELECT persona.*
            FROM persona
            INNER JOIN colegiado ON(colegiado.IdPersona = persona.Id)
            WHERE colegiado.Id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idColegiado]);
        $row = $stmt->fetch();

        $resultado = array();
        if ($row) {
            $datos = array(
                    'idPersona' => $row['Id'],
                    'apellido' => $row['Apellido'],
                    'nombre' => $row['Nombres'],
                    'sexo' => $row['Sexo'],
                    'tipoDocumento' => $row['TipoDocumento'],
                    'numeroDocumento' => $row['NumeroDocumento'],
                    'fechaNacimiento' => $row['FechaNacimiento'],
                    'idNacionalidad' => $row['IdPaises'],
                    'fechaCarga' => $row['FechaCarga'],
                    'fechaActualizacion' => $row['FechaActualizacion'],
                    'estado' => $row['Estado']
                    );

            $resultado['datos'] = $datos;
            $resultado['mensaje'] = "OK";
            $resultado['estado'] = TRUE;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['datos'] = NULL;
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "No hay persona ".$idColegiado;
            $resultado['clase'] = 'alert alert-info';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando persona";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }

    return $resultado;
}

    public function obtenerPersonasActivas() {
    try {
        $db = Database::getConnection();
        $sql="SELECT persona.Id, persona.Apellido, persona.Nombres, persona.NumeroDocumento, persona.Sexo,
                    persona.FechaNacimiento, paises.Nacionalidad
                FROM persona
                INNER JOIN paises ON(paises.Id = persona.IdPaises)
                WHERE persona.Estado = 'A'
                ORDER BY persona.Apellido, persona.Nombres";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll();

        $resultado = array();
        if (count($rows) > 0) {
            $datos = array();
            foreach ($rows as $row) {
                $sexo = $row['Sexo'];
                if ($sexo == 'M') {
                    $sexo = 'Masculino';
                } else {
                    $sexo = 'Femenino';
                }
                $r = array(
                    'id' => $row['Id'],
                    'apellido' => $row['Apellido'],
                    'nombre' => $row['Nombres'],
                    'numeroDocumento' => $row['NumeroDocumento'],
                    'sexo' => $sexo,
                    'fechaNacimiento' => $row['FechaNacimiento'],
                    'nacionalidad' => $row['Nacionalidad']
                );
                array_push($datos, $r);
            }
            $resultado['estado'] = true;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = false;
            $resultado['mensaje'] = "No hay personas";
            $resultado['clase'] = 'alert alert-info';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando personas";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function numeroDocumentoExiste($tipoDocumento, $numeroDocumento){
    try {
        $db = Database::getConnection();
        $sql="SELECT COUNT(Id) AS Cantidad FROM persona WHERE TipoDocumento = ? AND NumeroDocumento = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$tipoDocumento, $numeroDocumento]);
        $row = $stmt->fetch();

        $resultado = array();
        $cantidad = $row['Cantidad'];
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

    public function agregarPersona($apellido, $nombres, $sexo, $tipoDocumento, $numeroDocumento, $fechaNacimiento, $idPaises) {
    try {
        $db = Database::getConnection();
        $sql="INSERT INTO persona
            (Apellido, Nombres, Sexo, TipoDocumento, NumeroDocumento, FechaNacimiento, IdPaises, FechaCarga)
            VALUES (?, ?, ?, ?, ?, ?, ?, DATE(NOW()))";
        $stmt = $db->prepare($sql);
        $stmt->execute([$apellido, $nombres, $sexo, $tipoDocumento, $numeroDocumento, $fechaNacimiento, $idPaises]);
        $resultado = array();
        //agrego el movimiento para hacer el seguimiento
        $idPersona = $db->lastInsertId();
        $sql="INSERT INTO log_tabla
            (Tabla, IdTabla, Fecha, TipoMovimiento, IdUsuario)
            VALUES ('persona', ?, now(), 'alta', ?)";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idPersona, $_SESSION['user_id']]);
        $resultado['estado'] = TRUE;
        $resultado['idPersona'] = $idPersona;
        $resultado['mensaje'] = 'LA PERSONA HA SIDO AGREGADA';
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "ERROR AL AGREGAR PERSONA";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function modificarPersona($idPersona, $apellido, $nombres, $sexo, $numeroDocumento, $fechaNacimiento, $idPaises, $persona) {
    try {
        $db = Database::getConnection();
        $sql="UPDATE persona
            SET Apellido = ?,
                Nombres = ?,
                Sexo = ?,
                NumeroDocumento = ?,
                FechaNacimiento = ?,
                IdPaises = ?,
                FechaActualizacion = DATE(NOW())
            WHERE Id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$apellido, $nombres, $sexo, $numeroDocumento, $fechaNacimiento, $idPaises, $idPersona]);
        $resultado = array();
        //agrego el movimiento para hacer el seguimiento
        $sql="INSERT INTO log_tabla
            (Tabla, IdTabla, Fecha, TipoMovimiento, IdUsuario, Datos)
            VALUES ('persona', ?, now(), 'modificacion', ?, ?)";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idPersona, $_SESSION['user_id'], serialize($persona)]);
        $resultado['estado'] = TRUE;
        $resultado['mensaje'] = 'LA PERSONA HA SIDO MODIFICADA';
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "ERROR AL MODIFICAR PERSONA";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function bajaPersona($idPersona) {
    try {
        $db = Database::getConnection();
        $sql="UPDATE persona
            SET Estado = 'B'
            WHERE Id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idPersona]);
        $resultado = array();
        //agrego el movimiento para hacer el seguimiento
        $sql="INSERT INTO log_tabla
            (Tabla, IdTabla, Fecha, TipoMovimiento, IdUsuario)
            VALUES ('persona', ?, now(), 'borrada', ?)";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idPersona, $_SESSION['user_id']]);
        $resultado['estado'] = TRUE;
        $resultado['mensaje'] = 'LA PERSONA HA SIDO ELIMINADA';
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "ERROR AL ELIMINAR PERSONA";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}
}
