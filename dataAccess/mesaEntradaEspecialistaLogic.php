<?php
class mesaEntradaEspecialistaLogic {

    public function obtenerEspecialistaPorExpediente($expediente, $anio){
    try {
        $db = Database::getConnection();
        $sql = "SELECT c.Id, c.Matricula, p.Apellido, p.Nombres, e.Id, e.Especialidad, mee.IdMesaEntradaEspecialidad, mee.TipoEspecialidad, ce.FechaRecertificacion, ce.FechaVencimiento, e.IdTipoEspecialidad, te.Nombre, tes.Nombre, me.IdMesaEntrada, me.FechaIngreso, me.EstadoMatricular, me.EstadoTesoreria, mee.IncisoArticulo8, mee.Distrito, tes1.Codigo
            FROM mesaentradaespecialidad mee
            INNER JOIN mesaentrada me ON(me.IdMesaEntrada = mee.IdMesaEntrada)
            INNER JOIN colegiado c ON(c.Id = me.IdColegiado)
            INNER JOIN persona p ON(p.Id = c.IdPersona)
            INNER JOIN especialidad e ON(e.Id = mee.IdEspecialidad)
            INNER JOIN tipoespecialista tes ON(tes.Codigo = mee.TipoEspecialidad)
            LEFT JOIN colegiadoespecialista ce ON(ce.IdColegiado = c.Id AND ce.Especialidad = mee.IdEspecialidad)
            LEFT JOIN tipoespecialista tes1 ON(tes1.IdTipoEspecialista = ce.IdTipoEspecialista)
            LEFT JOIN colegiadoespecialistatipo cet ON(cet.IdColegiadoEspecialista = ce.Id)
            LEFT JOIN tipoespecialidad te ON(te.id = e.IdTipoEspecialidad)
            WHERE mee.NumeroExpediente = ? AND mee.AnioExpediente = ? AND me.Estado = 'A'";
        $stmt = $db->prepare($sql);
        $stmt->execute([$expediente, $anio]);

        $resultado = array();
        $r = $stmt->fetch(PDO::FETCH_NUM);
        if ($r) {
            $tipoTramiteEspecialista = $r[7]; // TipoEspecialidad
            $inciso = $r[17]; // IncisoArticulo8
            $distrito = $r[18]; // Distrito
            $nombreTipoEspecialista = $r[12]; // tes.Nombre (second)

            if ($tipoTramiteEspecialista == 'X') {
                if (isset($inciso) && $inciso <> "") {
                    $nombreTipoEspecialista .= ' Inciso '.$inciso.' ('.  obtenerDetalleIncisoEspecialistaArt8($inciso).')';
                }
            }

            if ($tipoTramiteEspecialista == 'O') {
                if (isset($distrito) && $distrito <> "") {
                    $nombreTipoEspecialista .= ' (Origen: Distrito '.obtenerNumeroRomano($distrito).')';
                }
            }

            $datos = array(
                'idColegiado' => $r[0],
                'matricula' => $r[1],
                'apellidoNombre' => trim($r[2]).' '.trim($r[3]),
                'idEspecialidad' => $r[4],
                'nombreEspecialidad' => $r[5],
                'idMesaEntradaEspecialidad' => $r[6],
                'tipoTramiteEspecialista' => $tipoTramiteEspecialista,
                'ultimaRecertificacion' => $r[8],
                'fechaVencimiento' => $r[9],
                'idTipoEspecialidad' => $r[10],
                'nombreTipoEspecialidad' => $r[11],
                'nombreTipoEspecialista' => $nombreTipoEspecialista,
                'idMesaEntrada' => $r[13],
                'fechaMesaEntrada' => $r[14],
                'estadoMatricular' => $r[15],
                'estadoTesoreria' => $r[16],
                'inciso' => $inciso,
                'distrito' => $distrito,
                'codigoEspecialista' => $r[19]
            );
            $resultado['estado'] = TRUE;
            $resultado['datos'] = $datos;
            $resultado['mensaje'] = "OK";
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "No se encontró EXPEDIENTE ".$expediente.'/'.$anio;
            $resultado['clase'] = 'alert alert-info';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "Error buscando EXPEDIENTE ".$expediente.'/'.$anio;
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function obtenerExpedientesPorFechaMatricula($fecha, $matricula) {
    try {
        $db = Database::getConnection();

        if (isset($matricula) && $matricula > 0) {
            $where = "WHERE me.Estado = 'A' AND c.Matricula = ? AND mee.Borrado = 0";
            $param = $matricula;
        } else {
            $where = "WHERE me.Estado = 'A' AND me.FechaIngreso = ? AND mee.Borrado = 0";
            $param = $fecha;
        }

        $sql = "SELECT c.Id, c.Matricula, p.Apellido, p.Nombres, e.Id, e.Especialidad, mee.IdMesaEntradaEspecialidad, mee.TipoEspecialidad, ce.FechaRecertificacion, ce.FechaVencimiento, e.IdTipoEspecialidad, te.Nombre, tes.Nombre, me.IdMesaEntrada, me.FechaIngreso, me.EstadoMatricular, me.EstadoTesoreria, mee.IncisoArticulo8, mee.Distrito, mee.NumeroExpediente, mee.AnioExpediente, tes1.Codigo
            FROM mesaentradaespecialidad mee
            INNER JOIN mesaentrada me ON(me.IdMesaEntrada = mee.IdMesaEntrada)
            INNER JOIN colegiado c ON(c.Id = me.IdColegiado)
            INNER JOIN persona p ON(p.Id = c.IdPersona)
            INNER JOIN especialidad e ON(e.Id = mee.IdEspecialidad)
            INNER JOIN tipoespecialista tes ON(tes.Codigo = mee.TipoEspecialidad)
            LEFT JOIN colegiadoespecialista ce ON(ce.IdColegiado = c.Id AND ce.Especialidad = mee.IdEspecialidad)
            LEFT JOIN tipoespecialista tes1 ON(tes1.IdTipoEspecialista = ce.IdTipoEspecialista)
            LEFT JOIN colegiadoespecialistatipo cet ON(cet.IdColegiadoEspecialista = ce.Id)
            LEFT JOIN tipoespecialidad te ON(te.id = e.IdTipoEspecialidad) ".$where;
        $stmt = $db->prepare($sql);
        $stmt->execute([$param]);
        $rows = $stmt->fetchAll(PDO::FETCH_NUM);

        $resultado = array();
        if (count($rows) > 0) {
            $datos = array();
            foreach ($rows as $r) {
                $tipoTramiteEspecialista = $r[7];
                $inciso = $r[17];
                $distrito = $r[18];
                $nombreTipoEspecialista = $r[12];

                if ($tipoTramiteEspecialista == 'X') {
                    if (isset($inciso) && $inciso <> "") {
                        $nombreTipoEspecialista .= ' Inciso '.$inciso.' ('.  obtenerDetalleIncisoEspecialistaArt8($inciso).')';
                    }
                }

                if ($tipoTramiteEspecialista == 'O') {
                    if (isset($distrito) && $distrito <> "") {
                        $nombreTipoEspecialista .= ' (Origen: Distrito '.obtenerNumeroRomano($distrito).')';
                    }
                }

                $row = array (
                    'idColegiado' => $r[0],
                    'matricula' => $r[1],
                    'apellidoNombre' => trim($r[2]).' '.trim($r[3]),
                    'idEspecialidad' => $r[4],
                    'nombreEspecialidad' => $r[5],
                    'idMesaEntradaEspecialidad' => $r[6],
                    'tipoTramiteEspecialista' => $tipoTramiteEspecialista,
                    'ultimaRecertificacion' => $r[8],
                    'fechaVencimiento' => $r[9],
                    'idTipoEspecialidad' => $r[10],
                    'nombreTipoEspecialidad' => $r[11],
                    'nombreTipoEspecialista' => $nombreTipoEspecialista,
                    'idMesaEntrada' => $r[13],
                    'fechaMesaEntrada' => $r[14],
                    'estadoMatricular' => $r[15],
                    'estadoTesoreria' => $r[16],
                    'inciso' => $inciso,
                    'distrito' => $distrito,
                    'numeroExpediente' => $r[19],
                    'anioExpediente' => $r[20],
                    'codigoEspecialista' => $r[21]
                 );
                array_push($datos, $row);
            }
            $resultado['estado'] = TRUE;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = FALSE;
            $resultado['datos'] = NULL;
            $resultado['mensaje'] = "No existen expedientes.";
            $resultado['clase'] = 'alert alert-warning';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando expedientes";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }

    return $resultado;
}

    public function realizarAltaMesaEntrada($idColegiado, $tipoEspecialista, $idEspecialidad, $idTipoMovimiento, $estadoTesoreria, $distrito, $incisoArticulo8, $idTipoEspecialista) {
    try {
        $db = Database::getConnection();
        $db->beginTransaction();
        $resultado = array();

        switch ($tipoEspecialista) {
            case 'A':
                $idTipoPago = 72;
                break;

            case 'C':
                $idTipoPago = 38;
                break;

            case 'E':
                $idTipoPago = 59;
                break;

            case 'J':
                $idTipoPago = 37;
                break;

            case 'N':
                $idTipoPago = 82;
                break;

            case 'O':
                $idTipoPago = 52;
                break;

            case 'R':
                $idTipoPago = 55;
                break;

            case 'U':
                $idTipoPago = 59;
                break;

            case 'X':
                $idTipoPago = 61;
                break;

            default:
                $idTipoPago = 59;
                break;
        }
        $sql = "INSERT INTO mesaentrada(TipoRemitente, IdColegiado, IdTipoMesaEntrada, FechaIngreso,
                Estado, IdUsuario, EstadoMatricular, EstadoTesoreria, IdTipoPago, Pagado)
                VALUES('C', ?, 2, date(now()), 'A', ?, ?, ?, ?, 0)";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idColegiado, $_SESSION['user_id'], $idTipoMovimiento, $estadoTesoreria, $idTipoPago]);
        $idMesaEntrada = $db->lastInsertId();

        $anioExpediente = date('Y');
        $resExpediente = $this->obtenerUltimoNumeroExpediente($anioExpediente);
        if ($resExpediente['estado']) {
            $numeroExpediente = $resExpediente['numeroExpediente'];

            $sql="INSERT INTO mesaentradaespecialidad
                (IdMesaEntrada, IdEspecialidad, TipoEspecialidad, NumeroExpediente, AnioExpediente, Distrito, IncisoArticulo8, IdTipoEspecialista)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $db->prepare($sql);
            $stmt->execute([$idMesaEntrada, $idEspecialidad, $tipoEspecialista, $numeroExpediente, $anioExpediente, $distrito, $incisoArticulo8, $idTipoEspecialista]);
            $idMesaEntradaEspecialidad = $db->lastInsertId();

            $datos['idMesaEntrada'] = $idMesaEntrada;
            $datos['idMesaEntradaEspecialidad'] = $idMesaEntradaEspecialidad;
            $datos['numeroExpediente'] = $numeroExpediente;
            $datos['anioExpediente'] = $anioExpediente;
            $resultado['estado'] = TRUE;
            $resultado['mensaje'] = 'El movimiento en Mesa de Entrada se registro correctamente';
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
            $resultado['datos'] = $datos;
            $db->commit();
            return $resultado;
        } else {
            $db->rollBack();
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "ERROR AL BUSCAR EL NUMERO DE EXPEDIENTE DEL AÑO ".$anioExpediente.' (DEBE IR AL SISTEMA DE MESA DE ENTRADAS Y REGISTRAR EL MOVIMIENTO)';
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-remove';
            return $resultado;
        }
    } catch (PDOException $e) {
        if (isset($db) && $db->inTransaction()) $db->rollBack();
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "ERROR: ".$e->getMessage().' (DEBE IR AL SISTEMA DE MESA DE ENTRADAS Y REGISTRAR EL MOVIMIENTO)';
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
        return $resultado;
    }
}

    public function realizarModificacionMesaEntrada($idMesaEntradaEspecialidad, $tipoEspecialista, $idEspecialidad, $distrito, $inciso, $idTipoEspecialista) {
    try {
        $db = Database::getConnection();
        $resultado = array();

        $sql="UPDATE mesaentradaespecialidad
            SET IdEspecialidad = ?,
                TipoEspecialidad = ?,
                Distrito = ?,
                IncisoArticulo8 = ?,
                IdTipoEspecialista = ?
            WHERE IdMesaEntradaEspecialidad = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idEspecialidad, $tipoEspecialista, $distrito, $inciso, $idTipoEspecialista, $idMesaEntradaEspecialidad]);
        $resultado['estado'] = TRUE;
        $resultado['mensaje'] = 'El movimiento en Mesa de Entrada se modifico correctamente';
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "ERROR AL modificar mesaentradaespecialidad";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function realizarBajaMesaEntrada($idMesaEntradaEspecialidad) {
    try {
        $db = Database::getConnection();
        $resultado = array();

        $sql="UPDATE mesaentrada me
            INNER JOIN mesaentradaespecialidad mee ON(mee.IdMesaEntrada = me.IdMesaEntrada)
            SET Estado = 'B'
            WHERE mee.IdMesaEntradaEspecialidad = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idMesaEntradaEspecialidad]);
        $resultado['estado'] = TRUE;
        $resultado['mensaje'] = 'El movimiento en Mesa de Entrada se elimino correctamente';
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "ERROR AL eliminar mesaentradaespecialidad";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function obtenerUltimoNumeroExpediente($anio) {
    try {
        $db = Database::getConnection();
        $sql = "SELECT MAX(mee.NumeroExpediente) as numero
                FROM mesaentradaespecialidad mee
                INNER JOIN mesaentrada me ON(me.IdMesaEntrada = mee.IdMesaEntrada)
                WHERE mee.AnioExpediente = ? AND me.Estado = 'A'";
        $stmt = $db->prepare($sql);
        $stmt->execute([$anio]);

        $resultado = array();
        $r = $stmt->fetch(PDO::FETCH_NUM);
        $numero = $r ? $r['numero'] : null;
        if (isset($numero)) {
            $resultado['numeroExpediente'] = $numero + 1;
        } else {
            $resultado['numeroExpediente'] = 1;
        }
        $resultado['estado'] = TRUE;
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
    }
    return $resultado;
}

    public function expedienteIngresadoPendiente($idColegiado, $idEspecialidad, $tipoEspecilidad) {
    try {
        $db = Database::getConnection();
        $sql = "SELECT mee.NumeroExpediente, mee.AnioExpediente
                FROM mesaentradaespecialidad mee
                INNER JOIN mesaentrada me ON(me.IdMesaEntrada = mee.IdMesaEntrada)
                LEFT JOIN resoluciondetalle rd ON(rd.IdMesaEntradaEspecialidad = mee.IdMesaEntradaEspecialidad)
                WHERE me.IdColegiado = ? AND mee.IdEspecialidad = ? AND mee.TipoEspecialidad = ?
                AND me.Estado = 'A' AND rd.Id IS NULL AND NOW() < date_add(me.FechaIngreso, INTERVAL 365 DAY)
                AND mee.Borrado = 0";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idColegiado, $idEspecialidad, $tipoEspecilidad]);

        $resultado = array();
        $resultado['estado'] = FALSE;
        $rows = $stmt->fetchAll(PDO::FETCH_NUM);
        if (count($rows) > 0) {
            $r = $rows[0];
            if (isset($r['NumeroExpediente'])) {
                $resultado['numeroExpediente'] = $r['NumeroExpediente'];
                $resultado['anioExpediente'] = $r['AnioExpediente'];
            }
            $resultado['estado'] = TRUE;
        }
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
    }
    return $resultado;
}

    public function obtenerMesaEntradaEspecialistaPorId($idMesaEntradaEspecialidad) {
    try {
        $db = Database::getConnection();
        $sql = "SELECT c.Id, c.Matricula, p.Apellido, p.Nombres, e.Id, e.Especialidad, mee.IdMesaEntradaEspecialidad, mee.TipoEspecialidad, ce.FechaRecertificacion, ce.FechaVencimiento, e.IdTipoEspecialidad, te.Nombre, tes.Nombre, me.IdMesaEntrada, me.FechaIngreso, me.EstadoMatricular, me.EstadoTesoreria, mee.NumeroExpediente, mee.AnioExpediente, mee.IncisoArticulo8, mee.Distrito, ce.FechaEspecialista, tes.IdTipoEspecialista
            FROM mesaentradaespecialidad mee
            INNER JOIN mesaentrada me ON(me.IdMesaEntrada = mee.IdMesaEntrada)
            INNER JOIN colegiado c ON(c.Id = me.IdColegiado)
            INNER JOIN persona p ON(p.Id = c.IdPersona)
            INNER JOIN especialidad e ON(e.Id = mee.IdEspecialidad)
            INNER JOIN tipoespecialista tes ON(tes.Codigo = mee.TipoEspecialidad)
            LEFT JOIN colegiadoespecialista ce ON(ce.IdColegiado = c.Id AND ce.Especialidad = mee.IdEspecialidad)
            LEFT JOIN colegiadoespecialistatipo cet ON(cet.IdColegiadoEspecialista = ce.Id)
            LEFT JOIN tipoespecialidad te ON(te.id = e.IdTipoEspecialidad)
            WHERE mee.IdMesaEntradaEspecialidad = ? AND mee.Borrado = 0";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idMesaEntradaEspecialidad]);

        $resultado = array();
        $r = $stmt->fetch(PDO::FETCH_NUM);
        if ($r) {
            $resultado['estado'] = TRUE;
            $datos = array(
                'idColegiado' => $r[0],
                'matricula' => $r[1],
                'apellidoNombre' => trim($r[2]).' '.trim($r[3]),
                'idEspecialidad' => $r[4],
                'nombreEspecialidad' => $r[5],
                'idMesaEntradaEspecialidad' => $r[6],
                'tipoTramiteEspecialista' => $r[7],
                'ultimaRecertificacion' => $r[8],
                'fechaVencimiento' => $r[9],
                'idTipoEspecialidad' => $r[10],
                'nombreTipoEspecialidad' => $r[11],
                'nombreTipoEspecialista' => $r[12],
                'idMesaEntrada' => $r[13],
                'fechaMesaEntrada' => $r[14],
                'estadoMatricular' => $r[15],
                'estadoTesoreria' => $r[16],
                'numeroExpediente' => $r[17],
                'anioExpediente' => $r[18],
                'inciso' => $r[19],
                'distrito' => $r[20],
                'fechaEspecialista' => $r[21],
                'idTipoEspecialista' => $r[22]
            );
            $resultado['datos'] = $datos;
            $resultado['mensaje'] = "OK";
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "No se encontró EXPEDIENTE";
            $resultado['clase'] = 'alert alert-info';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "Error buscando EXPEDIENTE";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function obtenerExpedientesSinResolucionAnulados() {
    try {
        $db = Database::getConnection();

        $sql = "SELECT mee.IdMesaEntradaEspecialidad, me.IdColegiado, c.Matricula, p.Apellido, p.Nombres, me.FechaIngreso, mee.IdEspecialidad, e.Especialidad, mee.TipoEspecialidad, te.Nombre, mee.NumeroExpediente, mee.AnioExpediente, mee.IncisoArticulo8, me.IdMesaEntrada, u.NombreCompleto, mee.FechaAnulacion, mee.Observacion
                FROM mesaentradaespecialidad mee
                INNER JOIN mesaentrada me ON(me.IdMesaEntrada = mee.IdMesaEntrada)
                INNER JOIN especialidad e ON(e.Id = mee.IdEspecialidad)
                INNER JOIN colegiado c ON(c.Id = me.IdColegiado)
                INNER JOIN persona p ON(p.Id = c.IdPersona)
                INNER JOIN tipoespecialista te ON(te.Codigo = mee.TipoEspecialidad)
                INNER JOIN usuario u ON u.Id = mee.IdUsuarioAnulacion
                WHERE me.Estado = 'A'
                AND mee.Borrado = 1
                ORDER BY mee.AnioExpediente DESC, mee.NumeroExpediente DESC";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_NUM);

        $resultado = array();
        if (count($rows) > 0) {
            $datos = array();
            foreach ($rows as $r) {
                $row = array (
                    'idMesaEntradaEspecialidad' => $r['IdMesaEntradaEspecialidad'],
                    'idMesaEntrada' => $r['IdMesaEntrada'],
                    'idColegiado' => $r['IdColegiado'],
                    'matricula' => $r['Matricula'],
                    'apellidoNombre' => trim($r['Apellido']).' '.trim($r['Nombres']),
                    'fechaIngreso' => $r['FechaIngreso'],
                    'idEspecialidad' => $r['IdEspecialidad'],
                    'nombreEspecialidad' => $r['Especialidad'],
                    'tipoEspecialidad' => $r['TipoEspecialidad'],
                    'nombreTipoEspecialista' => $r['Nombre'],
                    'numeroExpediente' => $r['NumeroExpediente'],
                    'anioExpediente' => $r['AnioExpediente'],
                    'inciso' => $r['IncisoArticulo8'],
                    'nombreUsuario' => $r['NombreCompleto'],
                    'fechaAnulacion' => $r['FechaAnulacion'],
                    'observaciones' => $r['Observacion']
                 );
                array_push($datos, $row);
            }
            $resultado['estado'] = TRUE;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = FALSE;
            $resultado['datos'] = NULL;
            $resultado['mensaje'] = "No existen expedientes.";
            $resultado['clase'] = 'alert alert-warning';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando expedientes";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }

    return $resultado;
}

    public function obtenerExpedientesSinResolucion() {
    try {
        $db = Database::getConnection();

        $sql = "SELECT mee.IdMesaEntradaEspecialidad, me.IdColegiado, c.Matricula, p.Apellido, p.Nombres, me.FechaIngreso, mee.IdEspecialidad, e.Especialidad, mee.TipoEspecialidad, te.Nombre, mee.NumeroExpediente, mee.AnioExpediente, mee.IncisoArticulo8, me.IdMesaEntrada, u.NombreCompleto
                FROM mesaentradaespecialidad mee
                INNER JOIN mesaentrada me ON(me.IdMesaEntrada = mee.IdMesaEntrada)
                INNER JOIN especialidad e ON(e.Id = mee.IdEspecialidad)
                INNER JOIN colegiado c ON(c.Id = me.IdColegiado)
                INNER JOIN persona p ON(p.Id = c.IdPersona)
                INNER JOIN tipoespecialista te ON(te.Codigo = mee.TipoEspecialidad)
                INNER JOIN usuario u ON u.Id = me.IdUsuario
                LEFT JOIN resoluciondetalle rd ON(rd.IdMesaEntradaEspecialidad = mee.IdMesaEntradaEspecialidad)
                WHERE me.Estado = 'A'
                AND rd.Id IS NULL
                AND mee.Borrado = 0
                ORDER BY mee.AnioExpediente DESC, mee.NumeroExpediente DESC";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_NUM);

        $resultado = array();
        if (count($rows) > 0) {
            $datos = array();
            foreach ($rows as $r) {
                $row = array (
                    'idMesaEntradaEspecialidad' => $r['IdMesaEntradaEspecialidad'],
                    'idMesaEntrada' => $r['IdMesaEntrada'],
                    'idColegiado' => $r['IdColegiado'],
                    'matricula' => $r['Matricula'],
                    'apellidoNombre' => trim($r['Apellido']).' '.trim($r['Nombres']),
                    'fechaIngreso' => $r['FechaIngreso'],
                    'idEspecialidad' => $r['IdEspecialidad'],
                    'nombreEspecialidad' => $r['Especialidad'],
                    'tipoEspecialidad' => $r['TipoEspecialidad'],
                    'nombreTipoEspecialista' => $r['Nombre'],
                    'numeroExpediente' => $r['NumeroExpediente'],
                    'anioExpediente' => $r['AnioExpediente'],
                    'inciso' => $r['IncisoArticulo8'],
                    'nombreUsuario' => $r['NombreCompleto']
                 );
                array_push($datos, $row);
            }
            $resultado['estado'] = TRUE;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = FALSE;
            $resultado['datos'] = NULL;
            $resultado['mensaje'] = "No existen expedientes.";
            $resultado['clase'] = 'alert alert-warning';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando expedientes";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }

    return $resultado;
}

    public function obtenerMesaEntradaEspecialistaParaResolucion($tipoResolucion) {
    try {
        $db = Database::getConnection();

        if ($tipoResolucion == 'E') {
            $filtro = "IN ('E', 'X', 'J', 'C', 'U')";
        } else {
            $filtro = "= '".$tipoResolucion."'";
        }

        $sql = "SELECT mee.IdMesaEntradaEspecialidad, me.IdColegiado, c.Matricula, p.Apellido, p.Nombres, me.FechaIngreso, mee.IdEspecialidad, e.Especialidad,
                    mee.TipoEspecialidad, te.Nombre, mee.NumeroExpediente, mee.AnioExpediente, mee.IncisoArticulo8
                FROM mesaentradaespecialidad mee
                INNER JOIN mesaentrada me ON(me.IdMesaEntrada = mee.IdMesaEntrada)
                INNER JOIN especialidad e ON(e.Id = mee.IdEspecialidad)
                INNER JOIN colegiado c ON(c.Id = me.IdColegiado)
                INNER JOIN persona p ON(p.Id = c.IdPersona)
                INNER JOIN tipoespecialista te ON(te.Codigo = mee.TipoEspecialidad)
                LEFT JOIN resoluciondetalle rd ON(rd.IdMesaEntradaEspecialidad = mee.IdMesaEntradaEspecialidad)
                WHERE me.Estado = 'A'
                AND rd.Id IS NULL
                AND NOW() < date_add(me.FechaIngreso, INTERVAL ".DIAS_VIGENCIA_EXPEDIENTES." DAY)
                AND mee.TipoEspecialidad ".$filtro."
                AND mee.Borrado = 0
                ORDER BY mee.AnioExpediente DESC, mee.NumeroExpediente DESC";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_NUM);

        $resultado = array();
        if (count($rows) > 0) {
            $datos = array();
            foreach ($rows as $r) {
                $row = array (
                    'idMesaEntradaEspecialidad' => $r['IdMesaEntradaEspecialidad'],
                    'idColegiado' => $r['IdColegiado'],
                    'matricula' => $r['Matricula'],
                    'apellidoNombre' => trim($r['Apellido']).' '.trim($r['Nombres']),
                    'fechaIngreso' => $r['FechaIngreso'],
                    'idEspecialidad' => $r['IdEspecialidad'],
                    'nombreEspecialidad' => $r['Especialidad'],
                    'tipoEspecialidad' => $r['TipoEspecialidad'],
                    'tipoEspecialista' => $r['Nombre'],
                    'numeroExpediente' => $r['NumeroExpediente'],
                    'anioExpediente' => $r['AnioExpediente'],
                    'inciso' => $r['IncisoArticulo8']
                 );
                array_push($datos, $row);
            }
            $resultado['estado'] = TRUE;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = FALSE;
            $resultado['datos'] = NULL;
            $resultado['mensaje'] = "No existen expedientes.";
            $resultado['clase'] = 'alert alert-warning';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando expedientes";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }

    return $resultado;
}

    public function obtenerDeudaEspecialistas() {
    try {
        $db = Database::getConnection();
        $sql = "SELECT c.Matricula, c.Id, p.Apellido, p.Nombres, SUM(tp.Importe) AS Total,
                    (SELECT GROUP_CONCAT(me1.IdMesaEntrada) FROM mesaentrada me1
                    INNER JOIN mesaentradaespecialidad mee ON mee.IdMesaEntrada = me1.IdMesaEntrada AND mee.DeudaAnulada = 0
                    LEFT JOIN tipoespecialista te ON te.Codigo = mee.TipoEspecialidad
                    INNER JOIN especialidad e ON e.Id = mee.IdEspecialidad
                    WHERE me1.IdTipoPago > 0 AND me1.Pagado = 0 AND me1.IdColegiado = c.Id AND me1.Estado <> 'B') AS IdMesaEntrada
            FROM mesaentrada me
            INNER JOIN mesaentradaespecialidad mee1 ON mee1.IdMesaEntrada = me.IdMesaEntrada AND mee1.DeudaAnulada = 0
            INNER JOIN colegiado c ON c.Id = me.IdColegiado
            INNER JOIN persona p ON p.Id = c.IdPersona
            INNER JOIN tipopago tp ON tp.Id = me.IdTipoPago
            WHERE me.IdTipoPago > 0 AND me.Pagado = 0 AND me.Estado <> 'B'
            GROUP BY c.Matricula, p.Apellido, p.Nombres";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_NUM);

        $resultado = array();
        if (count($rows) > 0) {
            $datos = array();
            foreach ($rows as $r) {
                $row = array (
                    'listaIdMesaEntrada' => $r['IdMesaEntrada'],
                    'idColegiado' => $r['Id'],
                    'matricula' => $r['Matricula'],
                    'apellidoNombre' => trim($r['Apellido']).' '.trim($r['Nombres']),
                    'total' => $r['Total']
                 );
                array_push($datos, $row);
            }
            $resultado['estado'] = TRUE;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = FALSE;
            $resultado['datos'] = NULL;
            $resultado['mensaje'] = "No existen expedientes a cobrar.";
            $resultado['clase'] = 'alert alert-warning';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando expedientes a cobrar";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }

    return $resultado;
}

    public function obtenerExpedientesAnulados() {
    try {
        $db = Database::getConnection();
        $sql = "SELECT me.IdMesaEntrada, me.FechaIngreso, c.Matricula, c.Id, p.Apellido, p.Nombres, tp.Importe, e.Id, e.Especialidad, mee.IdTipoEspecialista, te.Nombre, mee.IncisoArticulo8, u.NombreCompleto, mee.Observacion
              FROM mesaentrada me
              INNER JOIN mesaentradaespecialidad mee ON mee.IdMesaEntrada = me.IdMesaEntrada AND mee.DeudaAnulada = 1
              INNER JOIN especialidad e ON e.Id = mee.IdEspecialidad
              INNER JOIN tipoespecialista te ON te.IdTipoEspecialista = mee.IdTipoEspecialista
            INNER JOIN colegiado c ON c.Id = me.IdColegiado
            INNER JOIN persona p ON p.Id = c.IdPersona
            INNER JOIN tipopago tp ON tp.Id = me.IdTipoPago
            INNER JOIN usuario u ON u.Id = me.IdUsuario
            WHERE me.IdTipoPago > 0 AND me.Pagado = 0 AND me.Estado <> 'B'
            ORDER BY me.IdMesaEntrada, c.Matricula, p.Apellido, p.Nombres";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_NUM);

        $resultado = array();
        if (count($rows) > 0) {
            $datos = array();
            foreach ($rows as $r) {
                $row = array (
                    'idMesaEntrada' => $r['IdMesaEntrada'],
                    'fechaIngreso' => $r['FechaIngreso'],
                    'idColegiado' => $r['Id'],
                    'matricula' => $r['Matricula'],
                    'apellidoNombre' => trim($r['Apellido']).' '.trim($r['Nombres']),
                    'importe' => $r['Importe'],
                    'idEspecialidad' => $r['Id'],
                    'nombreEspecialidad' => $r['Especialidad'],
                    'idTipoEspecialista' => $r['IdTipoEspecialista'],
                    'nombreTipoEspecialista' => $r['Nombre'],
                    'incisoArticulo8' => $r['IncisoArticulo8'],
                    'nombreUsuario' => $r['NombreCompleto'],
                    'observacion' => $r['Observacion']
                 );
                array_push($datos, $row);
            }
            $resultado['estado'] = TRUE;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = FALSE;
            $resultado['datos'] = NULL;
            $resultado['mensaje'] = "No existen expedientes a cobrar.";
            $resultado['clase'] = 'alert alert-warning';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando expedientes a cobrar";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }

    return $resultado;
}

    public function obtenerExpedientesPendientesPago() {
    try {
        $db = Database::getConnection();
        $sql = "SELECT me.IdMesaEntrada, me.FechaIngreso, c.Matricula, c.Id, p.Apellido, p.Nombres, tp.Importe, e.Id, e.Especialidad, mee.IdTipoEspecialista, te.Nombre, mee.IncisoArticulo8, u.NombreCompleto
              FROM mesaentrada me
              INNER JOIN mesaentradaespecialidad mee ON mee.IdMesaEntrada = me.IdMesaEntrada AND mee.DeudaAnulada = 0 AND mee.Borrado = 0
              INNER JOIN especialidad e ON e.Id = mee.IdEspecialidad
              INNER JOIN tipoespecialista te ON te.IdTipoEspecialista = mee.IdTipoEspecialista
            INNER JOIN colegiado c ON c.Id = me.IdColegiado
            INNER JOIN persona p ON p.Id = c.IdPersona
            INNER JOIN tipopago tp ON tp.Id = me.IdTipoPago
            INNER JOIN usuario u ON u.Id = me.IdUsuario
            WHERE me.IdTipoPago > 0 AND me.Pagado = 0 AND me.Estado <> 'B'
            ORDER BY me.IdMesaEntrada, c.Matricula, p.Apellido, p.Nombres";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_NUM);

        $resultado = array();
        if (count($rows) > 0) {
            $datos = array();
            foreach ($rows as $r) {
                $row = array (
                    'idMesaEntrada' => $r['IdMesaEntrada'],
                    'fechaIngreso' => $r['FechaIngreso'],
                    'idColegiado' => $r['Id'],
                    'matricula' => $r['Matricula'],
                    'apellidoNombre' => trim($r['Apellido']).' '.trim($r['Nombres']),
                    'importe' => $r['Importe'],
                    'idEspecialidad' => $r['Id'],
                    'nombreEspecialidad' => $r['Especialidad'],
                    'idTipoEspecialista' => $r['IdTipoEspecialista'],
                    'nombreTipoEspecialista' => $r['Nombre'],
                    'incisoArticulo8' => $r['IncisoArticulo8'],
                    'nombreUsuario' => $r['NombreCompleto']
                 );
                array_push($datos, $row);
            }
            $resultado['estado'] = TRUE;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = FALSE;
            $resultado['datos'] = NULL;
            $resultado['mensaje'] = "No existen expedientes a cobrar.";
            $resultado['clase'] = 'alert alert-warning';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando expedientes a cobrar";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }

    return $resultado;
}

    public function obtenerMesaEntradaEspecialistaPorIdMesaEntrada($idMesaEntrada) {
    try {
        $db = Database::getConnection();
        $sql = "SELECT me.IdMesaEntrada, me.FechaIngreso, c.Matricula, c.Id, p.Apellido, p.Nombres, tp.Importe, e.Id, e.Especialidad, mee.IdTipoEspecialista, te.Nombre, mee.IncisoArticulo8
              FROM mesaentrada me
              INNER JOIN mesaentradaespecialidad mee ON mee.IdMesaEntrada = me.IdMesaEntrada
              INNER JOIN especialidad e ON e.Id = mee.IdEspecialidad
              INNER JOIN tipoespecialista te ON te.IdTipoEspecialista = mee.IdTipoEspecialista
            INNER JOIN colegiado c ON c.Id = me.IdColegiado
            INNER JOIN persona p ON p.Id = c.IdPersona
            LEFT JOIN tipopago tp ON tp.Id = me.IdTipoPago
            WHERE me.IdMesaEntrada = ?
            ORDER BY me.IdMesaEntrada, c.Matricula, p.Apellido, p.Nombres";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idMesaEntrada]);

        $resultado = array();
        $r = $stmt->fetch(PDO::FETCH_NUM);
        if ($r) {
            $datos = array (
                    'idMesaEntrada' => $r['IdMesaEntrada'],
                    'fechaIngreso' => $r['FechaIngreso'],
                    'idColegiado' => $r['Id'],
                    'matricula' => $r['Matricula'],
                    'apellidoNombre' => trim($r['Apellido']).' '.trim($r['Nombres']),
                    'importe' => $r['Importe'],
                    'idEspecialidad' => $r['Id'],
                    'nombreEspecialidad' => $r['Especialidad'],
                    'idTipoEspecialista' => $r['IdTipoEspecialista'],
                    'nombreTipoEspecialista' => $r['Nombre'],
                    'incisoArticulo8' => $r['IncisoArticulo8']
                 );
            $resultado['estado'] = TRUE;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = FALSE;
            $resultado['datos'] = NULL;
            $resultado['mensaje'] = "No existe idMesaEntrada ".$idMesaEntrada;
            $resultado['clase'] = 'alert alert-warning';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando mesa de entrada ".$idMesaEntrada;
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }

    return $resultado;
}

    public function anularExpedienteEspecialistaPendientePago($idMesaEntrada, $observacion, $accion) {
    try {
        $db = Database::getConnection();
        $resultado = array();

        if (isset($idMesaEntrada) && $idMesaEntrada <> "") {
            if ($accion == "ANULAR") {
                $sql="UPDATE mesaentradaespecialidad
                    SET DeudaAnulada = 1,
                        IdUsuarioAnulacion = ?,
                        FechaAnulacion = NOW(),
                        Observacion = ?
                    WHERE IdMesaEntrada = ?";
                $stmt = $db->prepare($sql);
                $stmt->execute([$_SESSION['user_id'], $observacion, $idMesaEntrada]);
            } else {
                $sql="UPDATE mesaentradaespecialidad
                    SET Borrado = 1,
                        IdUsuarioAnulacion = ?,
                        FechaAnulacion = NOW(),
                        Observacion = ?
                    WHERE IdMesaEntrada = ?";
                $stmt = $db->prepare($sql);
                $stmt->execute([$_SESSION['user_id'], $observacion, $idMesaEntrada]);
            }
        } else {
            $sql="UPDATE mesaentradaespecialidad mee
                    INNER JOIN mesaentrada me ON me.IdMesaEntrada = mee.IdMesaEntrada
                    SET mee.DeudaAnulada = 1, mee.IdUsuarioAnulacion = ?, mee.FechaAnulacion = NOW(), mee.Observacion = ?
                    WHERE me.IdTipoPago > 0 AND me.Pagado = 0 AND me.Estado <> 'B'
                    AND me.FechaIngreso < ADDDATE(date(now()), INTERVAL -365 DAY)";
            $stmt = $db->prepare($sql);
            $stmt->execute([$_SESSION['user_id'], $observacion]);
        }
        $resultado['estado'] = TRUE;
        $resultado['mensaje'] = 'El movimiento en Mesa de Entrada se modifico correctamente';
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "ERROR AL modificar mesaentradaespecialidad ->".$e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function hayPagosPenditesPorExpediente() {
    try {
        $db = Database::getConnection();
        $sql = "SELECT COUNT(*) AS Cantidad
                FROM mesaentradaespecialidad mee
                INNER JOIN mesaentrada me ON me.IdMesaEntrada = mee.IdMesaEntrada
                WHERE me.IdTipoPago > 0 AND me.Pagado = 0 AND me.Estado <> 'B' AND mee.DeudaAnulada = 0
                AND me.FechaIngreso < ADDDATE(date(now()), INTERVAL -365 DAY)";
        $stmt = $db->prepare($sql);
        $stmt->execute();

        $resultado = FALSE;
        $r = $stmt->fetch(PDO::FETCH_NUM);
        if ($r && $r['Cantidad'] > 0) {
            $resultado = TRUE;
        }
    } catch (PDOException $e) {
        $resultado = FALSE;
    }

    return $resultado;
}

    public function obtenerMesaEntradaEspecialistasAPagar($listaIdMesaEntrada) {
    try {
        $db = Database::getConnection();
        $sql = "SELECT me.IdMesaEntrada, me.FechaIngreso, mee.NumeroExpediente, mee.AnioExpediente, e.Especialidad, mee.TipoEspecialidad, mee.IncisoArticulo8, te.Nombre, tp.Importe
            FROM mesaentrada me
            INNER JOIN mesaentradaespecialidad mee ON mee.IdMesaEntrada = me.IdMesaEntrada
            LEFT JOIN tipoespecialista te ON te.Codigo = mee.TipoEspecialidad
            INNER JOIN especialidad e ON e.Id = mee.IdEspecialidad
            INNER JOIN tipopago tp ON tp.Id = me.IdTipoPago
            WHERE me.IdMesaEntrada IN(".$listaIdMesaEntrada.")";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_NUM);

        $resultado = array();
        if (count($rows) > 0) {
            $datos = array();
            foreach ($rows as $r) {
                $row = array (
                    'idMesaEntrada' => $r['IdMesaEntrada'],
                    'fechaIngreso' => $r['FechaIngreso'],
                    'numeroExpediente' => $r['NumeroExpediente'],
                    'anioExpediente' => $r['AnioExpediente'],
                    'especialidad' => $r['Especialidad'],
                    'tipoEspecilidad' => $r['TipoEspecialidad'],
                    'incisoArticulo8' => $r['IncisoArticulo8'],
                    'nombreTipoEspecialidad' => $r['Nombre'],
                    'importe' => $r['Importe']
                 );
                array_push($datos, $row);
            }
            $resultado['estado'] = TRUE;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = FALSE;
            $resultado['datos'] = NULL;
            $resultado['mensaje'] = "No existen expedientes a cobrar.";
            $resultado['clase'] = 'alert alert-warning';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando expedientes a cobrar";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }

    return $resultado;
}

    public function obtenerMesaEntradaPorId($idMesaEntrada) {
    try {
        $db = Database::getConnection();
        $sql = "SELECT me.IdTipoPago, tp.Importe
            FROM mesaentrada me
            INNER JOIN tipopago tp ON tp.Id = me.IdTipoPago
            WHERE me.IdMesaEntrada = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idMesaEntrada]);

        $resultado = array();
        $r = $stmt->fetch(PDO::FETCH_NUM);
        if ($r) {
            $datos = array (
                    'idTipoPago' => $r['IdTipoPago'],
                    'importe' => $r['Importe']
                 );
            $resultado['estado'] = TRUE;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = FALSE;
            $resultado['datos'] = NULL;
            $resultado['mensaje'] = "No existe idMesaEntrada ".$idMesaEntrada;
            $resultado['clase'] = 'alert alert-warning';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando mesa de entrada ".$idMesaEntrada;
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }

    return $resultado;
}

    public function obtenerEspecialidadPorIdMesaEntrada($idMesaEntrada) {
    try {
        $db = Database::getConnection();
        $sql = "SELECT e.Id, e.Especialidad, mee.IdMesaEntradaEspecialidad, mee.TipoEspecialidad, e.IdTipoEspecialidad, tes.Nombre, me.FechaIngreso, mee.NumeroExpediente, mee.AnioExpediente, mee.IncisoArticulo8
            FROM mesaentradaespecialidad mee
            INNER JOIN mesaentrada me ON me.IdMesaEntrada = mee.IdMesaEntrada
            INNER JOIN especialidad e ON(e.Id = mee.IdEspecialidad)
            INNER JOIN tipoespecialista tes ON(tes.Codigo = mee.TipoEspecialidad)
            WHERE mee.IdMesaEntrada = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idMesaEntrada]);

        $resultado = array();
        $r = $stmt->fetch(PDO::FETCH_NUM);
        if ($r) {
            $resultado['estado'] = TRUE;
            $datos = array(
                'idEspecialidad' => $r['Id'],
                'nombreEspecialidad' => $r['Especialidad'],
                'idMesaEntradaEspecialidad' => $r['IdMesaEntradaEspecialidad'],
                'tipoTramiteEspecialista' => $r['TipoEspecialidad'],
                'idTipoEspecialidad' => $r['IdTipoEspecialidad'],
                'nombreTipoEspecialista' => $r['Nombre'],
                'fechaMesaEntrada' => $r['FechaIngreso'],
                'numeroExpediente' => $r['NumeroExpediente'],
                'anioExpediente' => $r['AnioExpediente'],
                'incisoArticulo8' => $r['IncisoArticulo8']
            );
            $resultado['datos'] = $datos;
            $resultado['mensaje'] = "OK";
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "No se encontró EXPEDIENTE";
            $resultado['clase'] = 'alert alert-info';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "Error buscando EXPEDIENTE";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}
}
