<?php
class colegiadoEnvioChequeraLogic {

    public function guardarEnvioChequera($idEnvioMail, $idColegiadoDeudaAnual){
        $resultado = array();
        try {
            $db = Database::getConnection();
            $sql = "INSERT INTO enviomailchequera
                    (IdEnvioMail, IdColegiadoDeudaAnual, Fecha)
                    VALUES (?, ?, now())";
            $stmt = $db->prepare($sql);
            $stmt->execute([$idEnvioMail, $idColegiadoDeudaAnual]);
            $resultado['estado'] = true;
            $resultado['mensaje'] = "OK";
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } catch (PDOException $e) {
            $resultado['estado'] = false;
            $resultado['mensaje'] = "Error agregando enviomailchequera";
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-remove';
        }

        return $resultado;
    }
}
