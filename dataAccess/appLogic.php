<?php
class appLogic {

    public function obtenerApps() {
        try {
            $db = Database::getConnection();
            $sql = "SELECT IdApp AS id, Nombre AS nombre
                    FROM app
                    WHERE Tipo = 'P' AND Estado = 'A'
                    ORDER BY Nombre";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return [
                'estado'  => true,
                'mensaje' => "OK",
                'datos'   => $rows,
                'clase'   => 'alert alert-success',
                'icono'   => 'glyphicon glyphicon-ok'
            ];
        } catch (PDOException $e) {
            return [
                'estado'  => false,
                'mensaje' => "Error buscando app",
                'clase'   => 'alert alert-danger',
                'icono'   => 'glyphicon glyphicon-exclamation-sign'
            ];
        }
    }

    public function obtenerRoles() {
        try {
            $db = Database::getConnection();
            $sql = "SELECT ar.Id AS id, ar.Nombre AS nombreRol, app.Nombre AS nombreApp
                    FROM approl ar
                    INNER JOIN app ON app.IdApp = ar.IdApp
                    WHERE app.Estado = 'A' AND ar.Estado = 'A'
                    ORDER BY app.Nombre, ar.Nombre";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $datos = [];
            foreach ($rows as $row) {
                $datos[] = [
                    'id'     => $row['id'],
                    'nombre' => trim($row['nombreApp']) . ' - ' . trim($row['nombreRol'])
                ];
            }
            return [
                'estado'  => true,
                'mensaje' => "OK",
                'datos'   => $datos,
                'clase'   => 'alert alert-success',
                'icono'   => 'glyphicon glyphicon-ok'
            ];
        } catch (PDOException $e) {
            return [
                'estado'  => false,
                'mensaje' => "Error buscando approl",
                'clase'   => 'alert alert-danger',
                'icono'   => 'glyphicon glyphicon-exclamation-sign'
            ];
        }
    }

    public function obtenerAppPorId($idApp) {
        try {
            $db = Database::getConnection();
            $sql = "SELECT IdApp AS id, Nombre AS nombre
                    FROM app
                    WHERE IdApp = :idApp";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':idApp', $idApp, PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                return [
                    'estado'  => true,
                    'mensaje' => "OK",
                    'datos'   => $row,
                    'clase'   => 'alert alert-success',
                    'icono'   => 'glyphicon glyphicon-ok'
                ];
            }
            return [
                'estado'  => false,
                'mensaje' => "No se encontró la app",
                'clase'   => 'alert alert-danger',
                'icono'   => 'glyphicon glyphicon-exclamation-sign'
            ];
        } catch (PDOException $e) {
            return [
                'estado'  => false,
                'mensaje' => "Error buscando app",
                'clase'   => 'alert alert-danger',
                'icono'   => 'glyphicon glyphicon-exclamation-sign'
            ];
        }
    }

    public function obtenerAppRolUsuarioPorIdApp($idApp, $idUsuario) {
        try {
            $db = Database::getConnection();
            $sql = "SELECT ar.Id AS id, ar.Nombre AS nombre, uar.IdUsuario AS idUsuarioAsignado
                    FROM approl ar
                    LEFT JOIN usuarioapprol uar ON ar.Id = uar.IdAppRol AND uar.IdUsuario = :idUsuario
                    WHERE ar.IdApp = :idApp";
            $stmt = $db->prepare($sql);
            $stmt->execute([':idUsuario' => $idUsuario, ':idApp' => $idApp]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return [
                'estado'  => true,
                'mensaje' => "OK",
                'datos'   => $rows,
                'clase'   => 'alert alert-success',
                'icono'   => 'glyphicon glyphicon-ok'
            ];
        } catch (PDOException $e) {
            return [
                'estado'  => false,
                'mensaje' => "Error buscando app",
                'clase'   => 'alert alert-danger',
                'icono'   => 'glyphicon glyphicon-exclamation-sign'
            ];
        }
    }

    public function obtenerAppActividadPorIdApp($idApp) {
        try {
            $db = Database::getConnection();
            $sql = "SELECT DISTINCT aa.Id AS id, aa.Detalle AS nombre
                    FROM appactividad aa
                    INNER JOIN appactividadrol aar ON aar.IdAppActividad = aa.Id
                    INNER JOIN usuarioappactividadrol uar ON uar.IdAppActividadRol = aar.Id
                    WHERE aa.IdApp = :idApp AND aa.IdEstado = 1 AND uar.IdUsuario = 1
                      AND aar.Estado = 'A' AND aar.EnMenu = 'S'";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':idApp', $idApp, PDO::PARAM_INT);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return [
                'estado'  => true,
                'mensaje' => "OK",
                'datos'   => $rows,
                'clase'   => 'alert alert-success',
                'icono'   => 'glyphicon glyphicon-ok'
            ];
        } catch (PDOException $e) {
            return [
                'estado'  => false,
                'mensaje' => "Error buscando app",
                'clase'   => 'alert alert-danger',
                'icono'   => 'glyphicon glyphicon-exclamation-sign'
            ];
        }
    }
}
