<?php
class colegiadoSancionLogic {

    public function obtenerSanciones($estado) {
    try {
        $db = Database::getConnection();
        $sql="SELECT colegiadosancion.Id, colegiadosancion.Matricula, colegiadosancion.ApellidoNombres,
            colegiadosancion.Ley, colegiadosancion.FechaDesde, colegiadosancion.FechaHasta,
            colegiadosancion.Articulo, colegiadosancion.Detalle, colegiadosancion.Distrito,
            colegiadosancion.Provincia, colegiadosancion.IdColegiado, colegiadosanciongasto.CantidadGalenos,
            colegiadosanciongasto.FechaPago, colegiadosanciongasto.id AS IdCostas
            FROM colegiadosancion
            LEFT JOIN colegiadosanciongasto ON(colegiadosanciongasto.IdColegiadoSancion = colegiadosancion.Id AND colegiadosanciongasto.Estado = 'A')
            WHERE colegiadosancion.Estado = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$estado]);
        $rows = $stmt->fetchAll();

        $resultado = array();
        if (count($rows) > 0) {
            $datos = array();
            foreach ($rows as $row) {
                $r = array(
                    'idColegiadoSancion' => $row['Id'],
                    'matricula' => $row['Matricula'],
                    'apellidoNombre' => $row['ApellidoNombres'],
                    'ley' => $row['Ley'],
                    'fechaDesde' => $row['FechaDesde'],
                    'fechaHasta' => $row['FechaHasta'],
                    'articulo' => $row['Articulo'],
                    'detalle' => $row['Detalle'],
                    'distrito' => $row['Distrito'],
                    'provincia' => $row['Provincia'],
                    'idColegiado' => $row['IdColegiado'],
                    'cantidadGalenos' => $row['CantidadGalenos'],
                    'fechaPago' => $row['FechaPago'],
                    'idCostas' => $row['IdCostas']
                );
                array_push($datos, $r);
            }
            $resultado['estado'] = TRUE;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = FALSE;
            $resultado['datos'] = NULL;
            $resultado['mensaje'] = "NO SE ENCONTRARON SANCIONES";
            $resultado['clase'] = 'alert alert-warning';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando Sanciones";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }

    return $resultado;
}

    public function obtenerSancionPorId($idColegiadoSancion) {
    try {
        $db = Database::getConnection();
        $sql="SELECT Id, Matricula, ApellidoNombres, Ley, FechaDesde, FechaHasta, Articulo, Codigo, Detalle,
                Distrito, Provincia, IdColegiado, Estado
            FROM colegiadosancion
            WHERE Id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idColegiadoSancion]);
        $row = $stmt->fetch();

        $resultado = array();
        if ($row) {
            $estado = $row['Estado'];
            switch ($estado) {
                case 'A':
                    $estadoDetalle = 'Activa';
                    break;
                case 'B':
                    $estadoDetalle = 'Anulada';
                    break;
                default:
                    $estadoDetalle = 'Mal Cargada';
                    break;
            }
            $datos = array(
                    'idColegiadoSancion' => $row['Id'],
                    'matricula' => $row['Matricula'],
                    'apellidoNombre' => $row['ApellidoNombres'],
                    'ley' => $row['Ley'],
                    'fechaDesde' => $row['FechaDesde'],
                    'fechaHasta' => $row['FechaHasta'],
                    'articulo' => $row['Articulo'],
                    'codigo' => $row['Codigo'],
                    'detalle' => $row['Detalle'],
                    'distrito' => $row['Distrito'],
                    'provincia' => $row['Provincia'],
                    'idColegiado' => $row['IdColegiado'],
                    'estado' => $estado,
                    'estadoDetalle' => $estadoDetalle
                );
            $resultado['estado'] = TRUE;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = FALSE;
            $resultado['datos'] = NULL;
            $resultado['mensaje'] = "NO SE ENCONTRO LA SANCION";
            $resultado['clase'] = 'alert alert-warning';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando Sanciones";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }

    return $resultado;
}

    public function obtenerSancionesPorIdColegiado($idColegiado) {
    try {
        $db = Database::getConnection();
        $sql="SELECT Id, Ley, FechaDesde, FechaHasta, Articulo, Detalle, Distrito, Provincia, Estado
        FROM colegiadosancion
        WHERE IdColegiado = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idColegiado]);
        $rows = $stmt->fetchAll();

        $resultado = array();
        if (count($rows) > 0) {
            $datos = array();
            foreach ($rows as $row) {
                $estado = $row['Estado'];
                switch ($estado) {
                    case 'A':
                        $estadoDetalle = 'ACTIVA';
                        break;
                    case 'B':
                        $estadoDetalle = 'ANULADA';
                        break;
                    default:
                        $estadoDetalle = 'Mal Cargada';
                        break;
                }
                $r = array(
                    'idColegiadoSancion' => $row['Id'],
                    'ley' => $row['Ley'],
                    'fechaDesde' => $row['FechaDesde'],
                    'fechaHasta' => $row['FechaHasta'],
                    'articulo' => $row['Articulo'],
                    'detalle' => $row['Detalle'],
                    'distrito' => $row['Distrito'],
                    'provincia' => $row['Provincia'],
                    'estado' => $estado,
                    'estadoDetalle' => $estadoDetalle
                );
                array_push($datos, $r);
            }
            $resultado['estado'] = TRUE;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = FALSE;
            $resultado['datos'] = NULL;
            $resultado['mensaje'] = "El colegiado no tiene Sanciones.";
            $resultado['clase'] = 'alert alert-warning';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando Sanciones";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }

    return $resultado;
}

    public function agregarSancion($matricula, $apellidoNombre, $ley, $fechaDesde, $fechaHasta, $articulo, $codigo,
        $detalle, $distrito, $provincia, $idColegiado) {
    try {
        $db = Database::getConnection();
        $db->beginTransaction();
        $fechaCarga = date('Y-m-d');
        $sql = "INSERT INTO colegiadosancion
                (Matricula, ApellidoNombres, Ley, FechaDesde, FechaHasta, Articulo, Codigo, Detalle, Distrito,
                Provincia, IdUsuario, FechaCarga, IdColegiado, Estado)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'A')";
        $stmt = $db->prepare($sql);
        $stmt->execute([$matricula, $apellidoNombre, $ley, $fechaDesde, $fechaHasta,
                $articulo, $codigo, $detalle, $distrito, $provincia, $_SESSION['user_id'], $fechaCarga,
                $idColegiado]);
        $resultado['estado'] = TRUE;
        $resultado['mensaje'] = "SE REGISTRO LA SANCION CORRECTAMENTE";
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';

        $db->commit();
        return $resultado;

    } catch (PDOException $e) {
        $db->rollBack();
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "ERROR AL REGISTRAR FALSO MEDICO";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
        return $resultado;
    }
}

    public function editarSancion($idColegiadoSancion, $matricula, $apellidoNombre, $ley, $fechaDesde, $fechaHasta, $articulo, $codigo,
        $detalle, $distrito, $provincia, $idColegiado, $estado) {
    try {
        $db = Database::getConnection();
        $db->beginTransaction();
        $fechaCarga = date('Y-m-d');
        $sql = "UPDATE colegiadosancion
                SET Matricula = ?,
                ApellidoNombres = ?,
                Ley = ?,
                FechaDesde = ?,
                FechaHasta = ?,
                Articulo = ?,
                Codigo = ?,
                Detalle = ?,
                Distrito = ?,
                Provincia = ?,
                IdUsuario = ?,
                FechaCarga = ?,
                IdColegiado = ?,
                Estado = ?
                WHERE Id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$matricula, $apellidoNombre, $ley, $fechaDesde, $fechaHasta,
                $articulo, $codigo, $detalle, $distrito, $provincia, $_SESSION['user_id'], $fechaCarga,
                $idColegiado, $estado, $idColegiadoSancion]);
        $resultado['estado'] = TRUE;
        $resultado['mensaje'] = "SE ACTUALIZO LA SANCION CORRECTAMENTE";
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';

        $db->commit();
        return $resultado;

    } catch (PDOException $e) {
        $db->rollBack();
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "ERROR AL REGISTRAR FALSO MEDICO";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
        return $resultado;
    }
}

    public function obtenerCostasPorId($idCostas) {
    try {
        $db = Database::getConnection();
        $sql="SELECT * FROM colegiadosanciongasto WHERE Id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idCostas]);
        $row = $stmt->fetch();

        $resultado = array();
        if ($row) {
            $estado = $row['Estado'];
            switch ($estado) {
                case 'A':
                    $estadoDetalle = 'A pagar';
                    break;
                case 'B':
                    $estadoDetalle = 'Anulada';
                    break;
                case 'P':
                    $estadoDetalle = 'Abonada';
                    break;
                default:
                    $estadoDetalle = 'Mal Cargada';
                    break;
            }
            $datos = array(
                    'idColegiadoSancion' => $row['IdColegiadoSancion'],
                    'cantidadGalenos' => $row['CantidadGalenos'],
                    'fechaVencimiento' => $row['FechaVencimiento'],
                    'fechaPago' => $row['FechaPago'],
                    'importePagado' => $row['ImportePagado'],
                    'estado' => $estado,
                    'estadoDetalle' => $estadoDetalle,
                    'idUsuario' => $row['IdUsuario'],
                    'fechaCarga' => $row['FechaCarga']
                );
            $resultado['estado'] = TRUE;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = FALSE;
            $resultado['datos'] = NULL;
            $resultado['mensaje'] = "NO SE ENCONTRO COSTAS";
            $resultado['clase'] = 'alert alert-warning';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando COSTAS";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }

    return $resultado;
}

    public function agregarCostas($idColegiadoSancion, $cantidadGalenos, $fechaVencimiento) {
    try {
        $db = Database::getConnection();
        $db->beginTransaction();
        $fechaCarga = date('Y-m-d');
        $sql = "INSERT INTO colegiadosanciongasto
                (IdColegiadoSancion, CantidadGalenos, FechaVencimiento, Estado, IdUsuario, FechaCarga)
                VALUES (?, ?, ?, 'A', ?, ?)";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idColegiadoSancion, $cantidadGalenos, $fechaVencimiento, $_SESSION['user_id'], $fechaCarga]);
        $resultado['estado'] = TRUE;
        $resultado['mensaje'] = "SE REGISTRO COSTAS CORRECTAMENTE";
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';

        $db->commit();
        return $resultado;

    } catch (PDOException $e) {
        $db->rollBack();
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "ERROR AL REGISTRAR COSTAS";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
        return $resultado;
    }
}

    public function editarCostas($idCostas, $cantidadGalenos, $fechaVencimiento, $estado) {
    try {
        $db = Database::getConnection();
        $db->beginTransaction();
        $fechaCarga = date('Y-m-d');
        $sql = "UPDATE colegiadosanciongasto
                SET CantidadGalenos = ?,
                    FechaVencimiento = ?,
                    Estado = ?,
                    IdUsuario = ?,
                    FechaCarga = ?
                WHERE id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$cantidadGalenos, $fechaVencimiento, $estado, $_SESSION['user_id'], $fechaCarga, $idCostas]);
        $resultado['estado'] = TRUE;
        $resultado['mensaje'] = "SE ACTUALIZO COSTAS CORRECTAMENTE";
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';

        $db->commit();
        return $resultado;

    } catch (PDOException $e) {
        $db->rollBack();
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "ERROR AL REGISTRAR COSTAS";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
        return $resultado;
    }
}
}
