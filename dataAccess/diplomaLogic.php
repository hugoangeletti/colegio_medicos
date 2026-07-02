<?php
class diplomaLogic {

    public function obtenerPersonaPorDiploma($idEvento) {
    $resultado = array();
    try {
        $db = Database::getConnection();
        $sql="SELECT * FROM diploma WHERE IdEvento = ? ORDER BY ApellidoNombre";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idEvento]);
        $rows = $stmt->fetchAll();
        if (count($rows) > 0) {
            $datos = array();
            foreach ($rows as $r) {
                $datos[] = array(
                    'id' => $r['Id'],
                    'idEvento' => $r['IdEvento'],
                    'apellidoNombre' => $r['ApellidoNombre'],
                    'matricula' => $r['Matricula'],
                    'caracter' => $r['Caracter'],
                    'email' => $r['Mail'],
                    'nombrePdf' => $r['NombrePDF'],
                    'path' => $r['Path']
                );
            }
            $resultado['estado'] = TRUE;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "No hay personas";
            $resultado['clase'] = 'alert alert-info';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "Error: " . $e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
    }

    return $resultado;
}

    public function obtenerEventoPorId($id) {
    $resultado = array();
    try {
        $db = Database::getConnection();
        $sql="SELECT * FROM evento WHERE Id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if ($row) {
            $datos = array(
                    'id' => $row['Id'],
                    'nombre' => $row['Nombre'],
                    'fecha' => $row['Fecha'],
                    'plantilla' => $row['Plantilla'],
                    'nombreCertificado' => $row['NombreCertificado']
                );

            $resultado['estado'] = true;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = false;
            $resultado['mensaje'] = "No se ecntontro evento";
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error: " . $e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
    }

    return $resultado;
}

    public function obtenerDiplomasEnviar($rango) {
    $resultado = array();
    try {
        $db = Database::getConnection();
        $sql="SELECT e.Nombre, e.Fecha, d.Id, d.ApellidoNombre, d.Matricula, d.Caracter, d.Mail, d.NombrePDF, d.Path
            FROM diploma d
            INNER JOIN evento e ON e.Id = d.IdEvento
            LEFT JOIN enviomaildiariocolegiado emdc ON emdc.IdReferencia = d.Id
            WHERE emdc.Id IS NULL
            ORDER BY e.Nombre, d.ApellidoNombre
            LIMIT ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$rango]);
        $rows = $stmt->fetchAll();
        $resultado['cantidad'] = count($rows);
        if (count($rows) > 0) {
            $datos = array();
            foreach ($rows as $r) {
                $datos[] = array(
                    'nombreEvento' => $r['Nombre'],
                    'fechaEvento' => $r['Fecha'],
                    'idReferencia' => $r['Id'],
                    'apellido' => $r['ApellidoNombre'],
                    'nombres' => "",
                    'matricula' => $r['Matricula'],
                    'caracter' => $r['Caracter'],
                    'mail' => $r['Mail'],
                    'nombrePdf' => $r['NombrePDF'],
                    'path' => $r['Path']
                );
            }
            $resultado['estado'] = TRUE;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "No hay personas";
            $resultado['clase'] = 'alert alert-info';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "Error: " . $e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
    }

    return $resultado;
}

    public function guardarPdfDiploma($idDiploma, $nombreArchivo, $estructura) {
    $resultado = array();
    try {
        $db = Database::getConnection();
        $sql="UPDATE diploma
                SET NombrePDF = ?, Path = ?
                WHERE Id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$nombreArchivo, $estructura, $idDiploma]);
        $resultado['estado'] = true;
        $resultado['mensaje'] = "OK";
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error: " . $e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
    }

    return $resultado;
}
}
