<?php
class distritoLogic {

    public function obtenerDistritos() {
    $resultado = array();
    try {
        $db = Database::getConnection();
        $sql = "SELECT * FROM distritos ORDER BY Distrito";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll();
        if (count($rows) > 0) {
            $datos = array();
            foreach ($rows as $row) {
                $datos[] = array(
                    'id' => $row['Id'],
                    'distrito' => $row['Distrito'],
                    'romano' => $row['Romano'],
                    'presidente' => $row['Presidente'],
                    'domicilio' => $row['Domicilio'],
                    'mail' => $row['Email'],
                    'pagina' => $row['Pagina']
                );
            }
            $resultado['estado'] = TRUE;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = TRUE;
            $resultado['datos'] = NULL;
            $resultado['mensaje'] = "No hay Distritos";
            $resultado['clase'] = 'alert alert-info';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "Error buscando Distritos: " . $e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
    }
    return $resultado;
}

    public function obtenerDistritoPorId($id) {
    $resultado = array();
    try {
        $db = Database::getConnection();
        $sql = "SELECT * FROM distritos WHERE Id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if ($row) {
            $datos = array(
                'id' => $row['Id'],
                'distrito' => $row['Distrito'],
                'romano' => $row['Romano'],
                'presidente' => $row['Presidente'],
                'domicilio' => $row['Domicilio'],
                'mail' => $row['Email'],
                'pagina' => $row['Pagina']
            );
            $resultado['estado'] = true;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = false;
            $resultado['mensaje'] = "No se ecntontro Distrito";
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando Distrito: " . $e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
    }
    return $resultado;
}

    public function editarDistrito($idDistrito, $presidente, $domicilio, $mail, $pagina) {
    $resultado = array();
    try {
        $db = Database::getConnection();
        $sql = "UPDATE distritos
            SET Presidente = ?,
                Domicilio = ?,
                Email = ?,
                Pagina = ?
            WHERE Id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$presidente, $domicilio, $mail, $pagina, $idDistrito]);
        $resultado['estado'] = true;
        $resultado['mensaje'] = "Distrito actualizado correctamente";
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error actualizando Distrito: " . $e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
    }
    return $resultado;
}
}
