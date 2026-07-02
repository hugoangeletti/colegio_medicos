<?php
class colegiadoDebitosLogic {

    public function adheridoAlDebito($idColegiado) {
    $db = Database::getConnection();
    $sql="SELECT Tipo
            FROM debitotarjeta
            WHERE IdColegiado = ? AND Estado = 'A'";

    $resultado = array();
    try {
        $stmt = $db->prepare($sql);
        $stmt->execute([$idColegiado]);
        $row = $stmt->fetch();
        if ($row) {
            $resultado['tipo'] = $row['Tipo'];
            $resultado['estado'] = TRUE;
        } else {
            $resultado['estado'] = FALSE;
        }
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
    }

    if (!$resultado['estado']) {
        //busco en debito por cbu
        $sql="SELECT Id
                FROM debitocbu
                WHERE IdColegiado = ? AND Estado = 'A'";
        try {
            $stmt = $db->prepare($sql);
            $stmt->execute([$idColegiado]);
            $row = $stmt->fetch();
            $resultado = array();
            if ($row) {
                $resultado['tipo'] = 'H';
                $resultado['estado'] = TRUE;
            } else {
                $resultado['estado'] = FALSE;
            }
        } catch (PDOException $e) {
            $resultado['estado'] = FALSE;
        }
    }

    return $resultado;
}

    public function obtenerDebitoPorIdColegiado($idColegiado) {
    $db = Database::getConnection();
    $sql="SELECT id, NumeroTarjeta, Tipo, NumeroDocumento, FechaCarga, IdBanco, IncluyePlanPagos, PagoTotal, PathArchivo, NombreArchivo, TipoArchivo
            FROM debitotarjeta
            WHERE IdColegiado = ? AND Estado = 'A'";

    $resultado = array();
    try {
        $stmt = $db->prepare($sql);
        $stmt->execute([$idColegiado]);
        $resultado['estado'] = TRUE;
        $row = $stmt->fetch();
        if ($row) {
            $datos = array(
                'idDebito' => $row['id'],
                'numeroTarjeta' => $row['NumeroTarjeta'],
                'tipo' => $row['Tipo'],
                'numeroDocumento' => $row['NumeroDocumento'],
                'fechaCarga' => $row['FechaCarga'],
                'idBanco' => $row['IdBanco'],
                'incluyePP' => $row['IncluyePlanPagos'],
                'pagoTotal' => $row['PagoTotal'],
                'pathArchivo' => $row['PathArchivo'],
                'nombreArchivo' => $row['NombreArchivo'],
                'tipoArchivo' => $row['TipoArchivo']
            );
            $resultado['datos'] = $datos;
            $resultado['mensaje'] = "OK";
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['datos'] = NULL;
            $resultado['mensaje'] = "No hay colegiado ".$idColegiado;
            $resultado['clase'] = 'alert alert-info';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "Error buscando colegiado";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function obtenerDebitoPorId($idDebito) {
    $db = Database::getConnection();
    $sql="SELECT id, NumeroTarjeta, Tipo, NumeroDocumento, FechaCarga, IdBanco, IncluyePlanPagos, PagoTotal, PathArchivo, NombreArchivo, TipoArchivo, TipoBaja
            FROM debitotarjeta
            WHERE id = ?";

    $resultado = array();
    try {
        $stmt = $db->prepare($sql);
        $stmt->execute([$idDebito]);
        $resultado['estado'] = TRUE;
        $row = $stmt->fetch();
        if ($row) {
            $datos = array(
                'idDebito' => $row['id'],
                'numeroTarjeta' => $row['NumeroTarjeta'],
                'tipo' => $row['Tipo'],
                'numeroDocumento' => $row['NumeroDocumento'],
                'fechaCarga' => $row['FechaCarga'],
                'idBanco' => $row['IdBanco'],
                'incluyePP' => $row['IncluyePlanPagos'],
                'pagoTotal' => $row['PagoTotal'],
                'pathArchivo' => $row['PathArchivo'],
                'nombreArchivo' => $row['NombreArchivo'],
                'tipoArchivo' => $row['TipoArchivo'],
                'tipoBaja' => $row['TipoBaja']
            );
            $resultado['datos'] = $datos;
            $resultado['mensaje'] = "OK";
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['datos'] = NULL;
            $resultado['mensaje'] = "No hay debito ".$idDebito;
            $resultado['clase'] = 'alert alert-info';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "Error buscando debito";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function obtenerDebitoCBUPorIdColegiado($idColegiado) {
    $db = Database::getConnection();
    $sql="SELECT Id, IdBanco, Tipo, CBUBloque1, CBUBloque2, FechaCarga, IncluyePlanPagos, PagoTotal, PathArchivo, NombreArchivo, TipoArchivo
            FROM debitocbu
            WHERE IdColegiado = ? AND Estado = 'A'";

    $resultado = array();
    try {
        $stmt = $db->prepare($sql);
        $stmt->execute([$idColegiado]);
        $resultado['estado'] = TRUE;
        $row = $stmt->fetch();
        if ($row) {
            $datos = array(
                'id' => $row['Id'],
                'idBanco' => $row['IdBanco'],
                'tipo' => $row['Tipo'],
                'numeroCbu' => trim($row['CBUBloque1'].$row['CBUBloque2']),
                'fechaCarga' => $row['FechaCarga'],
                'incluyePP' => $row['IncluyePlanPagos'],
                'pagoTotal' => $row['PagoTotal'],
                'pathArchivo' => $row['PathArchivo'],
                'nombreArchivo' => $row['NombreArchivo'],
                'tipoArchivo' => $row['TipoArchivo']
            );
            $resultado['datos'] = $datos;
            $resultado['mensaje'] = "OK";
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['datos'] = NULL;
            $resultado['mensaje'] = "No hay colegiado ".$idColegiado;
            $resultado['clase'] = 'alert alert-info';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "Error buscando colegiado";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function obtenerDebitoCBUPorId($idDebito) {
    $db = Database::getConnection();
    $sql="SELECT Id, IdBanco, Tipo, CBUBloque1, CBUBloque2, FechaCarga, IncluyePlanPagos, PagoTotal, PathArchivo, NombreArchivo, TipoArchivo, TipoBaja
            FROM debitocbu
            WHERE Id = ?";

    $resultado = array();
    try {
        $stmt = $db->prepare($sql);
        $stmt->execute([$idDebito]);
        $resultado['estado'] = TRUE;
        $row = $stmt->fetch();
        if ($row) {
            $datos = array(
                'id' => $row['Id'],
                'idBanco' => $row['IdBanco'],
                'tipo' => $row['Tipo'],
                'numeroCbu' => trim($row['CBUBloque1'].$row['CBUBloque2']),
                'fechaCarga' => $row['FechaCarga'],
                'incluyePP' => $row['IncluyePlanPagos'],
                'pagoTotal' => $row['PagoTotal'],
                'pathArchivo' => $row['PathArchivo'],
                'nombreArchivo' => $row['NombreArchivo'],
                'tipoArchivo' => $row['TipoArchivo'],
                'tipoBaja' => $row['TipoBaja']
            );
            $resultado['datos'] = $datos;
            $resultado['mensaje'] = "OK";
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['datos'] = NULL;
            $resultado['mensaje'] = "No hay colegiado ".$idDebito;
            $resultado['clase'] = 'alert alert-info';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "Error buscando colegiado";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function agregarColegiadoDebito($idColegiado, $idBanco, $tipo, $numeroTarjeta, $numeroDocumento, $incluyePP, $incluyeTotal, $tipoAnterior, $numeroCbu, $tipoCuenta){
    $db = Database::getConnection();
    $resultado = array();
    try {
        $db->beginTransaction();

        if ($tipoAnterior == 'H') {
            $sql="UPDATE debitocbu
                SET Estado = 'B'
                WHERE IdColegiado = ? AND Estado = 'A'";
        } else {
            $sql="UPDATE debitotarjeta
                SET Estado = 'B'
                WHERE IdColegiado = ? AND Estado = 'A'";
        }
        $stmt = $db->prepare($sql);
        $stmt->execute([$idColegiado]);

        if ($tipo == 'C') {
            $sql="INSERT INTO debitotarjeta (IdColegiado, NumeroTarjeta, Tipo, NumeroDocumento, FechaCarga,
                IdBanco, IncluyePlanPagos, PagoTotal, IdUsuario)
                VALUES (?, ?, ?, ?, date(now()), ?, ?, ?, ?)";
            $stmt = $db->prepare($sql);
            $stmt->execute([$idColegiado, $numeroTarjeta, $tipo, $numeroDocumento, $idBanco, $incluyePP, $incluyeTotal, $_SESSION['user_id']]);
        } else {
            $bloque1 = substr($numeroCbu, 0, 8);
            $bloque2 = substr($numeroCbu, 8, 14);
            $sql="INSERT INTO debitocbu (IdColegiado, CBUBloque1, CBUBloque2, Tipo, FechaCarga, IdBanco, IncluyePlanPagos, PagoTotal, IdUsuario)
                VALUES (?, ?, ?, ?, date(now()), ?, ?, ?, ?)";
            $stmt = $db->prepare($sql);
            $stmt->execute([$idColegiado, $bloque1, $bloque2, $tipoCuenta, $idBanco, $incluyePP, $incluyeTotal, $_SESSION['user_id']]);
        }

        $idDebito = $db->lastInsertId();
        $resultado['estado'] = TRUE;
        $resultado['idDebito'] = $idDebito;
        $resultado['mensaje'] = "OK(".$idDebito.")";
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
        $db->commit();
        return $resultado;

    } catch (PDOException $e) {
        $db->rollBack();
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "Error agregando el debito. ".$e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
        return $resultado;
    }
}

    public function obtenerDebitoCBUporIdDebito($idDebito) {
    $db = Database::getConnection();
    $sql="SELECT d.IdColegiado, p.NumeroDocumento, c.Matricula
        FROM debitocbu d
        INNER JOIN colegiado c ON c.Id = d.IdColegiado
        INNER JOIN persona p ON p.Id = c.IdPersona
        INNER JOIN enviodebitodetalle edd ON edd.IdDebitoTarjeta = d.Id
        WHERE edd.Id = ?";

    $resultado = array();
    try {
        $stmt = $db->prepare($sql);
        $stmt->execute([$idDebito]);
        $resultado['estado'] = TRUE;
        $row = $stmt->fetch();
        if ($row) {
            $datos = array(
                'idColegiado' => $row['IdColegiado'],
                'numeroDocumento' => $row['NumeroDocumento'],
                'matricula' => $row['Matricula']
            );
            $resultado['datos'] = $datos;
            $resultado['mensaje'] = "OK";
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['datos'] = NULL;
            $resultado['mensaje'] = "No hay colegiado en debito cbu".$idDebito;
            $resultado['clase'] = 'alert alert-info';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "Error buscando debito cbu";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function bajaColegiadoDebito($idDebito, $tipo, $tipoBaja) {
    $db = Database::getConnection();
    $resultado = array();
    if ($tipo == 'H') {
        $sql="UPDATE debitocbu
            SET Estado = 'B', TipoBaja = ?, FechaCarga = DATE(NOW()), IdUsuario = ?
            WHERE Id = ? AND Estado = 'A'";
    } else {
        $sql="UPDATE debitotarjeta
            SET Estado = 'B', TipoBaja = ?, FechaCarga = DATE(NOW()), IdUsuario = ?
            WHERE id = ? AND Estado = 'A'";
    }
    try {
        $stmt = $db->prepare($sql);
        $stmt->execute([$tipoBaja, $_SESSION['user_id'], $idDebito]);
        $resultado['estado'] = TRUE;
        $resultado['mensaje'] = "OK";
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "Error anulando el debito. ".$e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function obtenerDebitoHistorico($idColegiado) {
    $db = Database::getConnection();
    $sql="SELECT 'DEBITO_CBU' as tipoDebito, b.Nombre,' ' AS Tipo, CONCAT(d.CBUBloque1, '-' , d.CBUBloque2) AS Numero, d.FechaCarga, d.Estado, u.NombreCompleto
        FROM debitocbu d
        INNER JOIN colegiado c ON c.Id = d.IdColegiado
        INNER JOIN persona p ON p.Id = c.IdPersona
        INNER JOIN banco b on b.Id = d.IdBanco
        LEFT JOIN usuario u ON u.Id = d.IdUsuario
        WHERE d.IdColegiado = ? AND d.Estado <> 'A'

        UNION ALL

        SELECT 'DEBITO_TARJETA' as tipoDebito, b.Nombre, dt.Tipo, dt.NumeroTarjeta AS Numero, dt.FechaCarga, dt.Estado, u.NombreCompleto
                FROM debitotarjeta dt
                INNER JOIN colegiado c ON c.Id = dt.IdColegiado
                INNER JOIN persona p ON p.Id = c.IdPersona
                INNER JOIN banco b on b.Id = dt.IdBanco
                LEFT JOIN usuario u ON u.Id = dt.IdUsuario
                WHERE dt.IdColegiado = ? AND dt.Estado <> 'A'";

    $resultado = array();
    try {
        $stmt = $db->prepare($sql);
        $stmt->execute([$idColegiado, $idColegiado]);
        $dados = $stmt->fetchAll();
        $resultado['estado'] = TRUE;
        if (count($dados) > 0) {
            $datos = array();
            foreach ($dados as $row) {
                $datos[] = array(
                    'tipoDebito' => $row['tipoDebito'],
                    'bancoNombre' => $row['Nombre'],
                    'tipo' => $row['Tipo'],
                    'numero' => $row['Numero'],
                    'fechaCarga' => $row['FechaCarga'],
                    'estado' => $row['Estado'],
                    'usuarioNombre' => $row['NombreCompleto']
                );
            }
            $resultado['datos'] = $datos;
            $resultado['mensaje'] = "OK";
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['datos'] = NULL;
            $resultado['mensaje'] = "No hay debitos historicos para esta matricula";
            $resultado['clase'] = 'alert alert-info';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "Error buscando debito historico";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function guardarDebitoArchivo($idColegiado, $tipo, $pathArchivo, $nombreArchivo, $tipoArchivo) {
    $db = Database::getConnection();
    $resultado = array();
    if ($tipo == 'H') {
        $sql="UPDATE debitocbu
            SET PathArchivo = ?, NombreArchivo = ?, TipoArchivo = ?
            WHERE IdColegiado = ? AND Estado = 'A'";
    } else {
        $sql="UPDATE debitotarjeta
            SET PathArchivo = ?, NombreArchivo = ?, TipoArchivo = ?
            WHERE IdColegiado = ? AND Estado = 'A'";
    }
    try {
        $stmt = $db->prepare($sql);
        $stmt->execute([$pathArchivo, $nombreArchivo, $tipoArchivo, $idColegiado]);
        $resultado['estado'] = TRUE;
        $resultado['mensaje'] = "OK";
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "Error guardando archivo en el debito. ".$e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}
}
