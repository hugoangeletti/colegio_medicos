<?php
class bancoLogic {

    public function obtenerBancos() {
        try {
            $db = Database::getConnection();
            $sql = "SELECT Id AS id, Nombre AS nombre FROM banco ORDER BY Nombre";
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
                'mensaje' => "Error buscando Bancos: " . $e->getMessage(),
                'clase'   => 'alert alert-danger',
                'icono'   => 'glyphicon glyphicon-exclamation-sign'
            ];
        }
    }

    public function obtenerBancoPorId($id) {
        try {
            $db = Database::getConnection();
            $sql = "SELECT Id AS id, Nombre AS nombre FROM banco WHERE Id = :id";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
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
                'mensaje' => "No se encontró el banco",
                'clase'   => 'alert alert-danger',
                'icono'   => 'glyphicon glyphicon-exclamation-sign'
            ];
        } catch (PDOException $e) {
            return [
                'estado'  => false,
                'mensaje' => "Error buscando banco: " . $e->getMessage(),
                'clase'   => 'alert alert-danger',
                'icono'   => 'glyphicon glyphicon-exclamation-sign'
            ];
        }
    }
}
