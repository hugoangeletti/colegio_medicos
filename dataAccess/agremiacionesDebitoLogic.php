<?php
class agremiacionesDebitoLogic {

    public function obtenerColegiadoPorAgremiacion($idLugarPago, $periodo) {
        try {
            $db = Database::getConnection();
            $sql = "SELECT ad.Id AS idAgremiacionesDebito, ad.IdColegiado AS idColegiado,
                           c.Matricula AS matricula, c.Estado AS estadoActual,
                           p.Apellido AS apellido, p.Nombres AS nombre,
                           tm.Detalle AS tipoMovimientoDetalle, tm.Estado AS estadoTipoMovimiento,
                           dt.id AS idDebitoTarjeta, dc.Id AS idDebitoCBU
                    FROM agremiacionesdebito ad
                    INNER JOIN colegiado c ON c.Id = ad.IdColegiado
                    INNER JOIN persona p ON p.Id = c.IdPersona
                    INNER JOIN tipomovimiento tm ON tm.Id = c.Estado
                    LEFT JOIN debitotarjeta dt ON dt.IdColegiado = ad.IdColegiado AND dt.Estado = 'A'
                    LEFT JOIN debitocbu dc ON dc.IdColegiado = ad.IdColegiado AND dc.Estado = 'A'
                    WHERE ad.IdLugarPago = :idLugarPago AND ad.Periodo = :periodo AND ad.Borrado = 0";

            $stmt = $db->prepare($sql);
            $stmt->execute([':idLugarPago' => $idLugarPago, ':periodo' => $periodo]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (count($rows) > 0) {
                $datos = [];
                foreach ($rows as $row) {
                    $conDebitoAutomatico = "";
                    if (!empty($row['idDebitoTarjeta']) && $row['idDebitoTarjeta'] > 0) {
                        $conDebitoAutomatico = "Adherido al débito por Tarjeta";
                    }
                    if (!empty($row['idDebitoCBU']) && $row['idDebitoCBU'] > 0) {
                        $conDebitoAutomatico = "Adherido al débito por CBU";
                    }
                    $datos[] = [
                        'idAgremiacionesDebito' => $row['idAgremiacionesDebito'],
                        'idColegiado'           => $row['idColegiado'],
                        'matricula'             => $row['matricula'],
                        'estadoActual'          => $row['estadoActual'],
                        'apellido'              => $row['apellido'],
                        'nombre'                => $row['nombre'],
                        'tipoMovimientoDetalle' => $row['tipoMovimientoDetalle'],
                        'estadoTipoMovimiento'  => $row['estadoTipoMovimiento'],
                        'conDebitoAutomatico'   => $conDebitoAutomatico
                    ];
                }
                return [
                    'estado'  => true,
                    'datos'   => $datos,
                    'mensaje' => "OK",
                    'clase'   => 'alert alert-success',
                    'icono'   => 'glyphicon glyphicon-ok'
                ];
            }

            return [
                'estado'  => false,
                'datos'   => null,
                'mensaje' => "No hay colegiados adheridos a la agremiación.",
                'clase'   => 'alert alert-info',
                'icono'   => 'glyphicon glyphicon-exclamation-sign'
            ];

        } catch (PDOException $e) {
            return [
                'estado'  => false,
                'mensaje' => "Error buscando colegiados por agremiación",
                'clase'   => 'alert alert-danger',
                'icono'   => 'glyphicon glyphicon-remove'
            ];
        }
    }

    public function agregarColegiadoDebitoAgremiacion($idColegiado, $idLugarPago, $periodo) {
        try {
            $db = Database::getConnection();
            $sql = "INSERT INTO agremiacionesdebito
                    (IdColegiado, IdLugarPago, Periodo, FechaCarga, IdUsuarioCarga)
                    VALUES (:idColegiado, :idLugarPago, :periodo, NOW(), :idUsuario)";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':idColegiado' => $idColegiado,
                ':idLugarPago' => $idLugarPago,
                ':periodo'     => $periodo,
                ':idUsuario'   => $_SESSION['user_id']
            ]);
            return [
                'estado'  => true,
                'mensaje' => "OK",
                'clase'   => 'alert alert-success',
                'icono'   => 'glyphicon glyphicon-ok'
            ];
        } catch (PDOException $e) {
            return [
                'estado'  => false,
                'mensaje' => "ERROR AL AGREGAR COLEGIADO A LA AGREMIACION",
                'clase'   => 'alert alert-danger',
                'icono'   => 'glyphicon glyphicon-remove'
            ];
        }
    }

    public function borrarColegiadoDebitoAgremiacion($idAgremiacionesDebito) {
        try {
            $db = Database::getConnection();
            $sql = "UPDATE agremiacionesdebito
                    SET Borrado = 1, FechaBorrado = NOW(), IdUsuarioBorrado = :idUsuario
                    WHERE Id = :id";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':idUsuario' => $_SESSION['user_id'],
                ':id'        => $idAgremiacionesDebito
            ]);
            return [
                'estado'  => true,
                'mensaje' => "OK",
                'clase'   => 'alert alert-success',
                'icono'   => 'glyphicon glyphicon-ok'
            ];
        } catch (PDOException $e) {
            return [
                'estado'  => false,
                'mensaje' => "ERROR AL BORRAR COLEGIADO A LA AGREMIACION.",
                'clase'   => 'alert alert-danger',
                'icono'   => 'glyphicon glyphicon-remove'
            ];
        }
    }
}
