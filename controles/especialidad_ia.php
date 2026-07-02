<?php 
require_once ('../dataAccess/config.php');
require_once ('../dataAccess/conection_pdo.php');

$objEspecialidades = new especialidades_pdo();

$query = isset($_POST['query']) ? $_POST['query'] : '';
$todos = isset($_GET['todos']) ? 'todos' : NULL;
$resultado = array('estado' => false, 'data' => array());

var_dump($_POST); 
if (strlen($query) >= 3) {
	/*
	if (isset($_GET['todos'])) {
		$especialidades = $objEspecialidades->obtenerEspecialidadesAutocompletar('todos');	
	} else {
		$especialidades = $objEspecialidades->obtenerEspecialidadesAutocompletar();
	}
	$data = array('result'=>true,'data'=>$especialidades['datos']);
	echo json_encode($resultado);
	*/
	try {
        $db = Database::getConnection();
	    // Buscamos por matrícula (número) o por nombre/apellido
        $porEstado = ($todos != 'todos') ? " AND e.Estado = 'A'" : "";
        
        $sql = "SELECT e.Id, e.Especialidad AS Nombre, e.IdTipoEspecialidad 
                FROM especialidad e 
                WHERE e.Especialidad COLLATE utf8_general_ci LIKE :query 
                " . $porEstado . "
                ORDER BY Nombre";

        /*
        $sql = "SELECT id, matricula, apellido, nombre 
                FROM colegiado 
                WHERE (matricula LIKE :query 
                   OR apellido LIKE :query 
                   OR nombre LIKE :query) 
                AND estado = 'A' 
                LIMIT 15";
        */
        $stmt = $db->prepare($sql);
        $term = "%$query%";
        $stmt->bindParam(':query', $term);
        $stmt->execute();
        
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $datos = array();

        foreach ($rows as $row) {
            $nombre = $row['Nombre'];
            $nombre .= ($row['IdTipoEspecialidad'] == 3) ? ' (Calificación Agregada)' : ''; 
            $datos[] = array(
                'id' => $row['id'],
                'nombre' => $nombre
            );
        }
        /*
        foreach ($rows as $row) {
            $datos[] = array(
                'id' => $row['id'],
                // Concatenamos para que la clave en el nameIdMap sea única
                'nombre' => $row['matricula'] . " - " . $row['apellido'] . ", " . $row['nombre']
            );
        }
		*/
        $resultado['estado'] = true;
        $resultado['data'] = $datos;

    } catch (PDOException $e) {
        $resultado['mensaje'] = "Error: " . $e->getMessage();
    }
}
header('Content-Type: application/json');
echo json_encode($resultado);

