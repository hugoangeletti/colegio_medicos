<?php
class colegiadoFapLogic {

    public function colegiadoTieneFap($idColegiado, $fecha){
        try {
            $db = Database::getConnection();
            $sql="SELECT count(sapcaratula.Id) AS Cantidad
                    FROM sapcaratula
                    INNER JOIN sapaconsejodetalle ON(sapaconsejodetalle.IdSAP = sapcaratula.Id)
                    WHERE sapcaratula.IdColegiado = ?
                    AND sapcaratula.Estado IN('E', 'A', 'M')
                    AND sapaconsejodetalle.FechaAprobacion >= ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$idColegiado, $fecha]);
            $row = $stmt->fetch();
            $resultado = FALSE;
            if ($row && $row['Cantidad'] > 0) {
                $resultado = array('estado' => TRUE);
            }
        } catch (PDOException $e) {
            $resultado = FALSE;
        }
        return $resultado;
    }

    /*
    public function obtenerCausasFAPPorIdColegiado($idColegiado) {
        ...
    }
    */
}
