<?php
class consultorioLogic {
    public function obtenerConsultorioPorId($idConsultorio) {
        try {
            $db = Database::getConnection();
            $sql="SELECT c.TipoConsultorio, c.Nombre, c.Calle, c.Lateral, c.Numero, c.Piso, c.Departamento, c.Telefono, c.IdLocalidad, c.CodigoPostal, c.Estado, c.FechaCarga, c.IdUsuario, c.Observaciones, c.CantidadConsultorios, l.Nombre AS NombreLocalidad
                FROM consultorio c
                LEFT JOIN localidad l ON l.Id = c.IdLocalidad
                WHERE c.IdConsultorio = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$idConsultorio]);
            $row = $stmt->fetch();

            $resultado = array();
            if ($row) {
                $datos = array(
                            'tipoConsultorio' => $row['TipoConsultorio'],
                            'nombreConsultorio' => $row['Nombre'],
                            'calle' => $row['Calle'],
                            'lateral' => $row['Lateral'],
                            'numeroCasa' => $row['Numero'],
                            'piso' => $row['Piso'],
                            'departamento' => $row['Departamento'],
                            'telefono' => $row['Telefono'],
                            'idLocalidad' => $row['IdLocalidad'],
                            'codigoPostal' => $row['CodigoPostal'],
                            'estado' => $row['Estado'],
                            'fechaCarga' => $row['FechaCarga'],
                            'idUsuario' => $row['IdUsuario'],
                            'observaciones' => $row['Observaciones'],
                            'cantidadConsultorios' => $row['CantidadConsultorios'],
                            'nombreLocalidad' => $row['NombreLocalidad']
                            );
                $resultado['estado'] = true;
                $resultado['mensaje'] = "OK";
                $resultado['datos'] = $datos;
                $resultado['clase'] = 'alert alert-success';
                $resultado['icono'] = 'glyphicon glyphicon-ok';
            } else {
                $resultado['estado'] = false;
                $resultado['mensaje'] = "No hay consultorio";
                $resultado['clase'] = 'alert alert-info';
                $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
            }
        } catch (PDOException $e) {
            $resultado['estado'] = false;
            $resultado['mensaje'] = "Error buscando consultorio";
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-remove';
        }

        return $resultado;
    }

    public function obtenerConsultorios(){
        try {
            $db = Database::getConnection();
            $sql = "SELECT c.IdConsultorio, c.TipoConsultorio, c.Nombre, c.Calle, c.Lateral, c.Numero, c.Piso, c.Departamento, c.Telefono, c.IdLocalidad, c.CodigoPostal, c.Estado, c.FechaCarga, c.IdUsuario, c.Observaciones, c.CantidadConsultorios, l.Nombre AS NombreLocalidad
                FROM consultorio c
                LEFT JOIN localidad l ON l.Id = c.IdLocalidad
                WHERE c.Estado = 'A'";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            $rows = $stmt->fetchAll();

            $resultado = array();
            if (count($rows) > 0) {
                $datos = array();
                foreach ($rows as $row) {
                    $r = array(
                            'idConsultorio' => $row['IdConsultorio'],
                            'tipoConsultorio' => $row['TipoConsultorio'],
                            'nombreConsultorio' => $row['Nombre'],
                            'calle' => $row['Calle'],
                            'lateral' => $row['Lateral'],
                            'numeroCasa' => $row['Numero'],
                            'piso' => $row['Piso'],
                            'departamento' => $row['Departamento'],
                            'telefono' => $row['Telefono'],
                            'idLocalidad' => $row['IdLocalidad'],
                            'codigoPostal' => $row['CodigoPostal'],
                            'estado' => $row['Estado'],
                            'fechaCarga' => $row['FechaCarga'],
                            'idUsuario' => $row['IdUsuario'],
                            'observaciones' => $row['Observaciones'],
                            'cantidadConsultorios' => $row['CantidadConsultorios'],
                            'nombreLocalidad' => $row['NombreLocalidad']
                    );
                    array_push($datos, $r);
                }
                $resultado['estado'] = true;
                $resultado['mensaje'] = "OK";
                $resultado['datos'] = $datos;
                $resultado['clase'] = 'alert alert-success';
                $resultado['icono'] = 'glyphicon glyphicon-ok';
            } else {
                $resultado['estado'] = true;
                $resultado['datos'] = NULL;
                $resultado['mensaje'] = "No hay remitentes";
                $resultado['clase'] = 'alert alert-warning';
                $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
            }
        } catch (PDOException $e) {
            $resultado['estado'] = false;
            $resultado['mensaje'] = "Error buscando remitentes";
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-remove';
        }
        return $resultado;
    }

    public function obtenerConsultoriosAutocompletar(){
        try {
            $db = Database::getConnection();
            $sql = "SELECT c.IdConsultorio, c.Nombre, c.Calle, c.Lateral, c.Numero, c.Piso, c.Departamento, l.Nombre AS NombreLocalidad
                FROM consultorio c
                LEFT JOIN localidad l ON l.Id = c.IdLocalidad
                WHERE c.Estado = 'A'
                ORDER BY c.Nombre, c.Calle, c.Numero";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            $rows = $stmt->fetchAll();

            $resultado = array();
            if (count($rows) > 0) {
                $datos = array();
                foreach ($rows as $row) {
                    $nombreConsultorio = $row['Nombre'];
                    $calle = $row['Calle'];
                    $numeroCasa = $row['Numero'];
                    $lateral = $row['Lateral'];
                    $piso = $row['Piso'];
                    $departamento = $row['Departamento'];
                    $nombreLocalidad = $row['NombreLocalidad'];

                    $nombre = "";
                    if (isset($nombreConsultorio) && $nombreConsultorio <> "" && $nombreConsultorio <> "-") {
                        $nombre .= $nombreConsultorio;
                    }

                    $domicilio = " ";
                    if (isset($calle) && $calle <> "") {
                        $domicilio .= trim($calle);
                    }
                    if (isset($numeroCasa) && $numeroCasa <> "") {
                        $domicilio .= ' N°'.trim($numeroCasa);
                    }
                    if (isset($lateral) && $lateral <> "") {
                        $domicilio .= ' ('.trim($lateral).')';
                    }
                    if (isset($piso) && $piso <> "") {
                        $domicilio .= ' Piso'.trim($piso);
                    }
                    if (isset($departamento) && $departamento <> "") {
                        $domicilio .= ' Dto.'.trim($departamento);
                    }
                    if (isset($nombreLocalidad) && $nombreLocalidad <> "") {
                        $domicilio .= ' ('.trim($nombreLocalidad).')';
                    }
                    $r = array(
                            'id' => $row['IdConsultorio'],
                            'nombre' => $nombre.$domicilio
                    );
                    array_push($datos, $r);
                }
                $resultado['estado'] = true;
                $resultado['mensaje'] = "OK";
                $resultado['datos'] = $datos;
                $resultado['clase'] = 'alert alert-success';
                $resultado['icono'] = 'glyphicon glyphicon-ok';
            } else {
                $resultado['estado'] = true;
                $resultado['datos'] = NULL;
                $resultado['mensaje'] = "No hay consultorios";
                $resultado['clase'] = 'alert alert-warning';
                $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
            }
        } catch (PDOException $e) {
            $resultado['estado'] = false;
            $resultado['mensaje'] = "Error buscando consultorios";
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-remove';
        }
        return $resultado;
    }

    public function guardarConsultorio($idConsultorio, $tipoConsultorio, $nombreConsultorio, $calle, $lateral, $numeroCasa, $piso, $departamento, $telefono, $idLocalidad, $codigoPostal, $estado, $fechaCarga, $observaciones, $cantidadConsultorios, $datosAnteriores) {
        try {
            $db = Database::getConnection();
            $db->beginTransaction();
            if (isset($idConsultorio)) {
                $sql = "UPDATE consultorio
                        SET TipoConsultorio = ?, Nombre = ?, Calle = ?, Lateral = ?, Numero = ?, Piso = ?, Departamento = ?, Telefono = ?, IdLocalidad = ?, CodigoPostal = ?, Estado = ?, FechaCarga = DATE(NOW()), IdUsuario = ?, Observaciones = ?, CantidadConsultorios = ?
                        WHERE IdConsultorio = ?";
                $stmt = $db->prepare($sql);
                $stmt->execute([$tipoConsultorio, $nombreConsultorio, $calle, $lateral, $numeroCasa, $piso, $departamento, $telefono, $idLocalidad, $codigoPostal, $estado, $_SESSION['user_id'], $observaciones, $cantidadConsultorios, $idConsultorio]);
            } else {
                $sql="INSERT INTO consultorio (TipoConsultorio, Nombre, Calle, Lateral, Numero, Piso, Departamento, Telefono, IdLocalidad, CodigoPostal, Estado, FechaCarga, IdUsuario, Observaciones, CantidadConsultorios)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, DATE(NOW()), ?, ?, ?)";
                $stmt = $db->prepare($sql);
                $stmt->execute([$tipoConsultorio, $nombreConsultorio, $calle, $lateral, $numeroCasa, $piso, $departamento, $telefono, $idLocalidad, $codigoPostal, $estado, $_SESSION['user_id'], $observaciones, $cantidadConsultorios]);
            }
            $resultado = array();
            if (isset($idConsultorio) && $idConsultorio <> "") {
                $tipoMovimiento = 'modificacion';
            } else {
                $idConsultorio = $db->lastInsertId();
                $datosAnteriores = array();
                $tipoMovimiento = 'alta';
            }
            $datos = serialize($datosAnteriores);
            $sql="INSERT INTO log_tabla (Tabla, IdTabla, Fecha, TipoMovimiento, IdUsuario, Datos)
                VALUES ('consultorio', ?, NOW(), ?, ?, ?)";
            $stmt = $db->prepare($sql);
            $stmt->execute([$idConsultorio, $tipoMovimiento, $_SESSION['user_id'], $datos]);
            $resultado['estado'] = TRUE;
            $resultado['idConsultorio'] = $idConsultorio;
            $resultado['mensaje'] = 'EL CONSUTORIO HA SIDO GUARDADO';
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';

            $db->commit();
            return $resultado;
        } catch (PDOException $e) {
            $db->rollBack();
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "Error guardando consultorio -> ".$e->getMessage();
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
            return $resultado;
        }
    }
}
