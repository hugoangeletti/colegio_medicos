<?php
class colegiadoCargoLogic {

    public function obtenerCargosColegio() {
        try {
            $db = Database::getConnection();
            $sql = 'SELECT cc1.IdCargo, cc1.Nombre, cc1.IdTipoCargo, cc1.Categoria, tc.Nombre AS NombreTipoCargo
                FROM cargocolegio cc1
                INNER JOIN tipocargo tc ON tc.IdTipoCargo = cc1.IdTipoCargo';
            $stmt = $db->prepare($sql);
            $stmt->execute();
            $rows = $stmt->fetchAll();

            $resultado = array();
            $resultado['estado'] = TRUE;
            if (count($rows) > 0) {
                $datos = array();
                foreach ($rows as $row) {
                    $r = array(
                        'idCargoColegio' => $row['IdCargo'],
                        'nombreCargo' => $row['Nombre'],
                        'idTipoCargo' => $row['IdTipoCargo'],
                        'categoria' => $row['Categoria'],
                        'nombreTipoCargo' => $row['NombreTipoCargo']
                        );
                    array_push($datos, $r);
                }
                $resultado['datos'] = $datos;
                $resultado['mensaje'] = "OK";
                $resultado['clase'] = 'alert alert-success';
                $resultado['icono'] = 'glyphicon glyphicon-ok';
            } else {
                $resultado['estado'] = false;
                $resultado['mensaje'] = "No se encontraron cargos";
                $resultado['clase'] = 'alert alert-info';
                $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
            }
        } catch (PDOException $e) {
            $resultado['estado'] = false;
            $resultado['mensaje'] = "Error buscando cargos";
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-remove';
        }

        return $resultado;
    }

    public function obtenerColegiadoCargoPorId($idColegiadoCargo) {
        try {
            $db = Database::getConnection();
            $sql = 'SELECT cc1.IdCargo, cc1.Nombre, cc.IdColegiado, cc.FechaDesde, cc.FechaHasta, cc.Estado, cc.FechaMesaDesde, cc.FechaMesaHasta, c.Matricula, p.Apellido, p.Nombres
                FROM colegiadocargo cc
                INNER JOIN cargocolegio cc1 ON cc1.IdCargo = cc.IdCargoColegio
                INNER JOIN colegiado c ON c.Id = cc.IdColegiado
                INNER JOIN persona p ON p.Id = c.IdPersona
                WHERE cc.IdColegiadoCargo = ?';
            $stmt = $db->prepare($sql);
            $stmt->execute([$idColegiadoCargo]);
            $rows = $stmt->fetchAll();

            $resultado = array();
            $resultado['estado'] = TRUE;
            if (count($rows) > 0) {
                $datos = array();
                foreach ($rows as $row) {
                    $r = array(
                        'idCargoColegio' => $row['IdCargo'],
                        'nombreCargo' => $row['Nombre'],
                        'idColegiado' => $row['IdColegiado'],
                        'fechaDesde' => $row['FechaDesde'],
                        'fechaHasta' => $row['FechaHasta'],
                        'estado' => $row['Estado'],
                        'fechaMesaDesde' => $row['FechaMesaDesde'],
                        'fechaMesaHasta' => $row['FechaMesaHasta'],
                        'matricula' => $row['Matricula'],
                        'apellido' => $row['Apellido'],
                        'nombre' => $row['Nombres']
                        );
                    array_push($datos, $r);
                }
                $resultado['datos'] = $datos;
                $resultado['mensaje'] = "OK";
                $resultado['clase'] = 'alert alert-success';
                $resultado['icono'] = 'glyphicon glyphicon-ok';
            } else {
                $resultado['estado'] = false;
                $resultado['mensaje'] = "No se encontraron cargos";
                $resultado['clase'] = 'alert alert-info';
                $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
            }
        } catch (PDOException $e) {
            $resultado['estado'] = false;
            $resultado['mensaje'] = "Error buscando cargos";
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-remove';
        }

        return $resultado;
    }

    public function obtenerCargosColegioPorColegiado($idColegiado) {
        try {
            $db = Database::getConnection();
            $sql = 'SELECT colegiadocargo.IdColegiadoCargo, cargocolegio.Nombre, colegiadocargo.FechaDesde, colegiadocargo.FechaHasta, colegiadocargo.Estado
                FROM colegiadocargo
                INNER JOIN cargocolegio ON(cargocolegio.IdCargo = colegiadocargo.IdCargoColegio)
                WHERE colegiadocargo.IdColegiado = ?';
            $stmt = $db->prepare($sql);
            $stmt->execute([$idColegiado]);
            $rows = $stmt->fetchAll();

            $resultado = array();
            $resultado['estado'] = TRUE;
            if (count($rows) > 0) {
                $datos = array();
                foreach ($rows as $row) {
                    $r = array(
                        'idColegiadoCargo' => $row['IdColegiadoCargo'],
                        'nombreCargo' => $row['Nombre'],
                        'fechaDesde' => $row['FechaDesde'],
                        'fechaHasta' => $row['FechaHasta'],
                        'estado' => $row['Estado']
                        );
                    array_push($datos, $r);
                }
                $resultado['datos'] = $datos;
                $resultado['mensaje'] = "OK";
                $resultado['clase'] = 'alert alert-success';
                $resultado['icono'] = 'glyphicon glyphicon-ok';
            } else {
                $resultado['estado'] = false;
                $resultado['mensaje'] = "No se encontraron cargos";
                $resultado['clase'] = 'alert alert-info';
                $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
            }
        } catch (PDOException $e) {
            $resultado['estado'] = false;
            $resultado['mensaje'] = "Error buscando cargos";
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-remove';
        }

        return $resultado;
    }

    public function obtenerCargoColegioPorIdColegiado($idColegiado) {
        try {
            $db = Database::getConnection();
            $sql = "SELECT cc.IdColegiadoCargo, cc.IdCargoColegio, cc1.Nombre, cc.FechaDesde, cc.FechaHasta, cc.Estado, cc.FechaMesaDesde, cc.FechaMesaHasta
                FROM colegiadocargo cc
                INNER JOIN cargocolegio cc1 ON cc1.IdCargo = cc.IdCargoColegio
                WHERE cc.IdColegiado = ? AND cc.Estado = 'A'
                AND cc.FechaDesde <= DATE(NOW()) AND cc.FechaHasta >= DATE(NOW())";
            $stmt = $db->prepare($sql);
            $stmt->execute([$idColegiado]);
            $row = $stmt->fetch();

            $resultado = array();
            if ($row) {
                $datos = array(
                        'idColegiadoCargo' => $row['IdColegiadoCargo'],
                        'idCargoColegio' => $row['IdCargoColegio'],
                        'nombreCargo' => $row['Nombre'],
                        'fechaDesde' => $row['FechaDesde'],
                        'fechaHasta' => $row['FechaHasta'],
                        'estado' => $row['Estado'],
                        'fechaMesaDesde' => $row['FechaMesaDesde'],
                        'fechaMesaHasta' => $row['FechaMesaHasta']
                        );
                $resultado['estado'] = TRUE;
                $resultado['datos'] = $datos;
                $resultado['mensaje'] = "OK";
                $resultado['clase'] = 'alert alert-success';
                $resultado['icono'] = 'glyphicon glyphicon-ok';
            } else {
                $resultado['estado'] = false;
                $resultado['mensaje'] = "No se encontraron cargos";
                $resultado['clase'] = 'alert alert-info';
                $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
            }
        } catch (PDOException $e) {
            $resultado['estado'] = false;
            $resultado['mensaje'] = "Error buscando cargos";
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-remove';
        }

        return $resultado;
    }

    public function obtenerConsejeros() {
        try {
            $db = Database::getConnection();
            $sql="SELECT colegiadocargo.IdColegiadoCargo, colegiado.Id, colegiado.Matricula, persona.Apellido, persona.Nombres,
                cargocolegio.Nombre, colegiadocargo.FechaDesde, colegiadocargo.FechaHasta, colegiadocontacto.TelefonoFijo, colegiadocontacto.TelefonoMovil, colegiadocontacto.CorreoElectronico
                    FROM colegiadocargo
                    INNER JOIN colegiado ON(colegiado.Id = colegiadocargo.IdColegiado)
                    INNER JOIN persona ON(persona.Id = colegiado.IdPersona)
                    INNER JOIN cargocolegio ON(cargocolegio.IdCargo = colegiadocargo.IdCargoColegio)
                    INNER JOIN colegiadocontacto ON (colegiadocontacto.IdColegiado = colegiado.Id AND colegiadocontacto.IdEstado = 1)
                    WHERE cargocolegio.IdTipoCargo = 1 AND colegiadocargo.Estado<>'B'
                    ORDER BY persona.Apellido, persona.Nombres";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            $rows = $stmt->fetchAll();

            $resultado = array();
            $resultado['estado'] = TRUE;
            if (count($rows) > 0) {
                $datos = array();
                foreach ($rows as $row) {
                    $r = array(
                        'idColegiadoCargo' => $row['IdColegiadoCargo'],
                        'idColegiado' => $row['Id'],
                        'matricula' => $row['Matricula'],
                        'apellido' => $row['Apellido'],
                        'nombre' => $row['Nombres'],
                        'nombreCargo' => $row['Nombre'],
                        'fechaDesde' => $row['FechaDesde'],
                        'fechaHasta' => $row['FechaHasta'],
                        'telefonoFijo' => $row['TelefonoFijo'],
                        'telefonoMovil' => $row['TelefonoMovil'],
                        'mail' => $row['CorreoElectronico']
                    );
                    array_push($datos, $r);
                }
                $resultado['datos'] = $datos;
                $resultado['mensaje'] = "OK";
                $resultado['clase'] = 'alert alert-success';
                $resultado['icono'] = 'glyphicon glyphicon-ok';
            } else {
                $resultado['estado'] = false;
                $resultado['mensaje'] = "No se encontraron consejeros";
                $resultado['clase'] = 'alert alert-info';
                $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
            }
        } catch (PDOException $e) {
            $resultado['estado'] = false;
            $resultado['mensaje'] = "Error buscando consejeros";
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-remove';
        }

        return $resultado;
    }

    public function obtenerConsejerosVigentes() {
        try {
            $db = Database::getConnection();
            $sql = "SELECT cc.IdColegiadoCargo, c.Id, c.Matricula, p.Apellido, p.Nombres, cc1.Nombre, cc.FechaDesde, cc.FechaHasta, cdr.Calle, cdr.Lateral, cdr.Numero, cdr.Piso, cdr.Departamento, l.Nombre AS NombreLocalidad, cdr.CodigoPostal, cc2.TelefonoFijo, cc2.TelefonoMovil, cc2.CorreoElectronico, cc.FechaMesaDesde, cc.FechaMesaHasta, cc.IdCargoColegio
                FROM colegiadocargo cc
                INNER JOIN cargocolegio cc1 ON (cc1.IdCargo = cc.IdCargoColegio)
                INNER JOIN colegiado c ON (c.Id = cc.IdColegiado)
                INNER JOIN persona p ON p.Id = c.IdPersona
                LEFT JOIN colegiadodomicilioreal cdr ON (cdr.idColegiado = c.Id and cdr.idEstado = 1)
                LEFT JOIN localidad l ON l.Id = cdr.idLocalidad
                LEFT JOIN colegiadocontacto cc2 ON (cc2.IdColegiado = c.Id and cc2.IdEstado = 1)
                WHERE cc.FechaDesde <= DATE(NOW()) AND cc.FechaHasta >= DATE(NOW())
                AND cc.Estado = 'A'
                AND cc1.IdTipoCargo = 1
                ORDER BY p.Apellido, p.Nombres";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            $rows = $stmt->fetchAll();

            $resultado = array();
            $resultado['estado'] = TRUE;
            if (count($rows) > 0) {
                $datos = array();
                foreach ($rows as $row) {
                    $calle = $row['Calle'];
                    $numeroCasa = $row['Numero'];
                    $lateral = $row['Lateral'];
                    $piso = $row['Piso'];
                    $departamento = $row['Departamento'];
                    $nombreLocalidad = $row['NombreLocalidad'];
                    $codigoPostal = $row['CodigoPostal'];
                    $telefonoFijo = $row['TelefonoFijo'];
                    $telefonoMovil = $row['TelefonoMovil'];

                    $domicilioCompleto = "";
                    $localidad = "";
                    if ($calle) {
                        $domicilioCompleto = $calle;
                        if ($numeroCasa) {
                            $domicilioCompleto .= " Nº ".$numeroCasa;
                        }
                        if ($lateral) {
                            $domicilioCompleto .= " e/ ".$lateral;
                        }
                        if ($piso && strtoupper($piso) != "NR") {
                            $domicilioCompleto .= " Piso ".$piso;
                        }
                        if ($departamento && strtoupper($departamento) != "NR") {
                            $domicilioCompleto .= " Dto. ".$departamento;
                        }
                        if ($nombreLocalidad) {
                            $localidad = $nombreLocalidad.' ('.$codigoPostal.')';
                        }
                    }

                    if (!isset($telefonoFijo) || strtoupper($telefonoFijo) == "NR") {
                        $telefonoFijo = "";
                    }
                    if (!isset($telefonoMovil) || strtoupper($telefonoMovil) == "NR") {
                        $telefonoMovil = "";
                    }

                    $r = array(
                        'idColegiadoCargo' => $row['IdColegiadoCargo'],
                        'idColegiado' => $row['Id'],
                        'matricula' => $row['Matricula'],
                        'apellido' => $row['Apellido'],
                        'nombre' => $row['Nombres'],
                        'nombreCargo' => $row['Nombre'],
                        'fechaDesde' => $row['FechaDesde'],
                        'fechaHasta' => $row['FechaHasta'],
                        'domicilioCompleto' => $domicilioCompleto,
                        'localidad' => $localidad,
                        'telefonoFijo' => $telefonoFijo,
                        'telefonoMovil' => $telefonoMovil,
                        'mail' => $row['CorreoElectronico'],
                        'fechaMesaDesde' => $row['FechaMesaDesde'],
                        'fechaMesaHasta' => $row['FechaMesaHasta'],
                        'idCargoColegio' => $row['IdCargoColegio']
                        );
                    array_push($datos, $r);
                }
                $resultado['datos'] = $datos;
                $resultado['mensaje'] = "OK";
                $resultado['clase'] = 'alert alert-success';
                $resultado['icono'] = 'glyphicon glyphicon-ok';
            } else {
                $resultado['estado'] = false;
                $resultado['mensaje'] = "No se encontraron consejeros";
                $resultado['clase'] = 'alert alert-info';
                $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
            }
        } catch (PDOException $e) {
            $resultado['estado'] = false;
            $resultado['mensaje'] = "Error buscando consejeros";
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-remove';
        }

        return $resultado;
    }

    public function obtenerConsejerosHistoricos() {
        try {
            $db = Database::getConnection();
            $sql = "SELECT cc.IdColegiadoCargo, c.Id, c.Matricula, p.Apellido, p.Nombres, cc1.Nombre, cc.FechaDesde, cc.FechaHasta, cdr.Calle, cdr.Lateral, cdr.Numero, cdr.Piso, cdr.Departamento, l.Nombre AS NombreLocalidad, cdr.CodigoPostal, cc2.TelefonoFijo, cc2.TelefonoMovil, cc2.CorreoElectronico
                FROM colegiadocargo cc
                INNER JOIN cargocolegio cc1 ON (cc1.IdCargo = cc.IdCargoColegio)
                INNER JOIN colegiado c ON (c.Id = cc.IdColegiado)
                INNER JOIN persona p ON p.Id = c.IdPersona
                LEFT JOIN colegiadodomicilioreal cdr ON (cdr.idColegiado = c.Id and cdr.idEstado = 1)
                LEFT JOIN localidad l ON l.Id = cdr.idLocalidad
                LEFT JOIN colegiadocontacto cc2 ON (cc2.IdColegiado = c.Id and cc2.IdEstado = 1)
                WHERE (cc.FechaHasta < DATE(NOW()) OR cc.Estado = 'B')
                AND cc1.IdTipoCargo = 1
                ORDER BY p.Apellido, p.Nombres";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            $rows = $stmt->fetchAll();

            $resultado = array();
            $resultado['estado'] = TRUE;
            if (count($rows) > 0) {
                $datos = array();
                foreach ($rows as $row) {
                    $calle = $row['Calle'];
                    $numeroCasa = $row['Numero'];
                    $lateral = $row['Lateral'];
                    $piso = $row['Piso'];
                    $departamento = $row['Departamento'];
                    $nombreLocalidad = $row['NombreLocalidad'];
                    $codigoPostal = $row['CodigoPostal'];
                    $telefonoFijo = $row['TelefonoFijo'];
                    $telefonoMovil = $row['TelefonoMovil'];

                    $domicilioCompleto = "";
                    $localidad = "";
                    if ($calle) {
                        $domicilioCompleto = $calle;
                        if ($numeroCasa) {
                            $domicilioCompleto .= " Nº ".$numeroCasa;
                        }
                        if ($lateral) {
                            $domicilioCompleto .= " e/ ".$lateral;
                        }
                        if ($piso && strtoupper($piso) != "NR") {
                            $domicilioCompleto .= " Piso ".$piso;
                        }
                        if ($departamento && strtoupper($departamento) != "NR") {
                            $domicilioCompleto .= " Dto. ".$departamento;
                        }
                        if ($nombreLocalidad) {
                            $localidad = $nombreLocalidad.' ('.$codigoPostal.')';
                        }
                    }

                    $telefonos = "";
                    if ($telefonoFijo && strtoupper($telefonoFijo) != "NR") {
                        $telefonos .= $telefonoFijo.'<br>';
                    }
                    if ($telefonoMovil && strtoupper($telefonoMovil) != "NR") {
                        $telefonos .= $telefonoMovil.'<br>';
                    }

                    $r = array(
                        'idColegiadoCargo' => $row['IdColegiadoCargo'],
                        'idColegiado' => $row['Id'],
                        'matricula' => $row['Matricula'],
                        'apellido' => $row['Apellido'],
                        'nombre' => $row['Nombres'],
                        'nombreCargo' => $row['Nombre'],
                        'fechaDesde' => $row['FechaDesde'],
                        'fechaHasta' => $row['FechaHasta'],
                        'domicilioCompleto' => $domicilioCompleto,
                        'localidad' => $localidad,
                        'telefonos' => $telefonos,
                        'mail' => $row['CorreoElectronico']
                        );
                    array_push($datos, $r);
                }
                $resultado['datos'] = $datos;
                $resultado['mensaje'] = "OK";
                $resultado['clase'] = 'alert alert-success';
                $resultado['icono'] = 'glyphicon glyphicon-ok';
            } else {
                $resultado['estado'] = false;
                $resultado['mensaje'] = "No se encontraron consejeros";
                $resultado['clase'] = 'alert alert-info';
                $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
            }
        } catch (PDOException $e) {
            $resultado['estado'] = false;
            $resultado['mensaje'] = "Error buscando consejeros";
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-remove';
        }

        return $resultado;
    }

    public function guardarColegiadoCargo($idColegiadoCargo, $idCargoColegioSeleccionado, $fechaDesde, $fechaHasta, $fechaMesaDesde, $fechaMesaHasta, $idColegiado){
        try {
            $db = Database::getConnection();
            $db->beginTransaction();
            $resultado = array();
            if (isset($idColegiadoCargo) && $idColegiadoCargo <> "") {
                $sql = "UPDATE colegiadocargo
                        SET IdCargoColegio = ?, FechaDesde = ?, FechaHasta = ?, FechaMesaDesde = ?, FechaMesaHasta = ?
                        WHERE IdColegiadoCargo = ?";
                $stmt = $db->prepare($sql);
                $stmt->execute([$idCargoColegioSeleccionado, $fechaDesde, $fechaHasta, $fechaMesaDesde, $fechaMesaHasta, $idColegiadoCargo]);
            } else {
                $sql="INSERT INTO colegiadocargo (IdCargoColegio, IdColegiado, FechaDesde, FechaHasta, FechaMesaDesde, FechaMesaHasta, Estado)
                VALUES (?, ?, ?, ?, ?, ?, 'A')";
                $stmt = $db->prepare($sql);
                $stmt->execute([$idCargoColegioSeleccionado, $idColegiado, $fechaDesde, $fechaHasta, $fechaMesaDesde, $fechaMesaHasta]);
            }

            if (isset($idColegiadoCargo) && $idColegiadoCargo <> "") {
                $tipoMovimiento = 'modificacion';
            } else {
                $idColegiadoCargo = $db->lastInsertId();
                $datosAnteriores = array();
                $tipoMovimiento = 'alta';
            }
            $datos = serialize($datosAnteriores);
            $sql="INSERT INTO log_consejeros (Tabla, IdTabla, Fecha, TipoMovimiento, IdUsuario, Datos)
                VALUES ('colegiadocargo', ?, NOW(), ?, ?, ?)";
            $stmt = $db->prepare($sql);
            $stmt->execute([$idColegiadoCargo, $tipoMovimiento, $_SESSION['user_id'], $datos]);
            $resultado['estado'] = TRUE;
            $resultado['idColegiadoCargo'] = $idColegiadoCargo;
            $resultado['mensaje'] = 'EL CONSEJERO HA SIDO GUARDADO';
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';

            $db->commit();
            return $resultado;
        } catch (PDOException $e) {
            $db->rollBack();
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "Error guardando consejero -> ".$e->getMessage();
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
            return $resultado;
        }
    }

    public function bajaColegiadoCargo($idColegiadoCargo, $datosAnteriores) {
        try {
            $db = Database::getConnection();
            $db->beginTransaction();
            $resultado = array();
            $sql = "UPDATE colegiadocargo
                    SET Estado = 'B'
                    WHERE IdColegiadoCargo = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$idColegiadoCargo]);

            $tipoMovimiento = 'baja';
            $datos = serialize($datosAnteriores);
            $sql="INSERT INTO log_consejeros (Tabla, IdTabla, Fecha, TipoMovimiento, IdUsuario, Datos)
                VALUES ('colegiadocargo', ?, NOW(), ?, ?, ?)";
            $stmt = $db->prepare($sql);
            $stmt->execute([$idColegiadoCargo, $tipoMovimiento, $_SESSION['user_id'], $datos]);
            $resultado['estado'] = TRUE;
            $resultado['idColegiadoCargo'] = $idColegiadoCargo;
            $resultado['mensaje'] = 'EL CONSEJERO HA SIDO DADO DE BAJA';
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';

            $db->commit();
            return $resultado;
        } catch (PDOException $e) {
            $db->rollBack();
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "Error guardando baja del consejero -> ".$e->getMessage();
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
            return $resultado;
        }
    }
}
