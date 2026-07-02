<?php
class planPagosLogic {

    public function obtenerPlanPagosPorEstado($estado){
    $db = Database::getConnection();
    $sql = "SELECT planpagos.Id, planpagos.FechaCreacion, planpagos.ImporteTotal, planpagos.Cuotas,
                colegiado.Matricula, persona.Apellido, persona.Nombres, planpagos.IdColegiado
            FROM planpagos
            Inner join colegiado on(colegiado.Id = planpagos.IdColegiado)
            inner join persona on(persona.Id = colegiado.IdPersona)
            WHERE planpagos.Estado = ?";

    $resultado = array();
    try {
        $stmt = $db->prepare($sql);
        $stmt->execute([$estado]);
        $dados = $stmt->fetchAll();
        if (count($dados) > 0) {
            $datos = array();
            foreach ($dados as $row) {
                $datos[] = array(
                    'idPlanPago' => $row['Id'],
                    'fechaCreacion' => $row['FechaCreacion'],
                    'importe' => $row['ImporteTotal'],
                    'cuotas' => $row['Cuotas'],
                    'matricula' => $row['Matricula'],
                    'apellidoNombre' => $row['Apellido'].' '.$row['Nombres'],
                    'idColegiado' => $row['IdColegiado']
                );
            }
            $resultado['estado'] = TRUE;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "No hay Planes de pago";
            $resultado['clase'] = 'alert alert-info';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "Error buscando Planes de pago";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}
}
