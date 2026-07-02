<?php
require_once '../../dataAccess/conection_pdo.php';
require_once '../../dataAccess/cursos_pdo.php'; 

$input = json_decode(file_get_contents('php://input'), true);

if (!empty($input['ids'])) {
	$cursos_pdo = new cursos_pdo();
    
    // Generar los marcadores de posición (?, ?, ?) dinámicamente según la cantidad de IDs recibidos
    $placeholders = implode(',', array_fill(0, count($input['ids']), '?'));
    
    // Consulta SQL uniendo la tabla intermedia de asignación de cursos con los datos de los docentes
    $docentes = $cursos_pdo->obtenerDocentesLiquidarPorCursos($placeholders, $input);
    if (count($docentes) > 0) {
        echo '<div class="table-responsive">';
        echo '<table class="table table-condensed table-striped" style="margin-bottom: 0;">';
        echo '<thead><tr><th width="40"></th><th>Docente</th><th width="120">Monto Asignado</th></tr></thead>';
        echo '<tbody>';
        foreach ($docentes as $doc) {
            echo '<tr>';
            // Checkbox para activar/desactivar al docente
            echo '<td style="vertical-align: middle; text-align: center;">';
            echo '<input type="checkbox" name="docentes_liquidados[]" value="'.$doc['Id'].'" class="check-docente" onclick="alternarInputDocente(this, '.$doc['Id'].')" checked>';
            echo '</td>';
            
            // Nombre del docente
            echo '<td style="vertical-align: middle;">';
            echo '<span class="label label-default">ID: '.$doc['Id'].'</span> ';
            echo '<strong>'.$doc['ApellidoNombres'].'</strong>';
            echo '</td>';
            
            // Input manual de monto (asociado al ID del docente)
            echo '<td style="width: 25%">';
            echo '<div class="input-group input-group-sm">';
            echo '<span class="input-group-addon">$</span>';
            echo '<input type="number" step="0.01" min="0" ';
            echo 'name="monto_docente['.$doc['Id'].']" ';
            echo 'id="monto_docente_'.$doc['Id'].'" ';
            echo 'class="form-control input-monto-docente" ';
            echo 'value="0.00" oninput="sumarMontosManuales()">';
            echo '</div>';
            echo '</td>';
            echo '</tr>';
        }
        echo '</tbody>';
        echo '</table>';
        echo '</div>';
    } else {
        echo '<p class="text-warning">No hay docentes vinculados a los cursos seleccionados.</p>';
    }
} else {
    echo '<p class="text-muted">Seleccione cursos para listar los docentes...</p>';
}
