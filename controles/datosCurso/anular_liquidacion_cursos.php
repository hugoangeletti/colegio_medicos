<?php
// 1. Incluir la conexión a la base de datos
require_once '../Database.php'; 

$resultado = [
    'estado'  => false,
    'mensaje' => 'Ocurrió un error inesperado al intentar anular.',
    'clase'   => 'alert alert-danger',
    'icono'   => 'glyphicon glyphicon-exclamation-sign'
];

// 2. Verificar que se reciba el ID de la liquidación maestra por POST o GET
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_liquidacion_maestra'])) {
    
    $idMaestroDocente = intval($_POST['id_liquidacion_maestra']);

    if ($idMaestroDocente <= 0) {
        $resultado['mensaje'] = "ID de liquidación no válido.";
        echo json_encode($resultado);
        exit;
    }

    try {
        $db = Database::getConnection();
        $db->exec("SET NAMES 'utf8'");
        
        // 3. Iniciar la transacción para garantizar consistencia atómica
        $db->beginTransaction();

        // =========================================================================
        // PASO 1: DESVINCULAR LOS CURSOS PROCESADOS
        // =========================================================================
        // Buscamos todos los cursos que apuntan a esta liquidación y les quitamos la relación
        // devolviendo su estado a "pendiente de liquidación".
        $sqlLiberarCursos = "UPDATE liquidacion_cursos 
                             SET IdLiquidacionCursosDocentes = NULL 
                             WHERE IdLiquidacionCursosDocentes = ?";
        
        $stmtLiberar = $db->prepare($sqlLiberarCursos);
        $stmtLiberar->execute([$idMaestroDocente]);


        // =========================================================================
        // PASO 2: ANULACIÓN LÓGICA DEL DETALLE DE DOCENTES
        // =========================================================================
        // Marcamos como borrados (Borrado = 1) todos los desgloses de dinero de los docentes
        $sqlAnularDetalle = "UPDATE liquidacion_cursos_docentes_detalle 
                             SET Borrado = 1 
                             WHERE IdLiquidacionCursosDocentes = ?";
        
        $stmtAnularDet = $db->prepare($sqlAnularDetalle);
        $stmtAnularDet->execute([$idMaestroDocente]);


        // =========================================================================
        // PASO 3: ANULACIÓN LÓGICA DE LA CABECERA MAESTRA
        // =========================================================================
        // Finalmente, anulamos el registro maestro para que no aparezca en listados activos
        $sqlAnularMaestro = "UPDATE liquidacion_cursos_docentes 
                             SET Borrado = 1 
                             WHERE Id = ?";
        
        $stmtAnularMae = $db->prepare($sqlAnularMaestro);
        $stmtAnularMae->execute([$idMaestroDocente]);

        // Validamos que la liquidación realmente existiera y haya sido modificada
        if ($stmtAnularMae->rowCount() > 0) {
            
            // Si los 3 pasos se completaron con éxito, confirmamos los cambios en la BD
            $db->commit();

            $resultado['estado']  = true;
            $resultado['mensaje'] = "La liquidación ID {$idMaestroDocente} ha sido anulada con éxito y los cursos asociados han sido liberados.";
            $resultado['clase']   = 'alert alert-success';
            $resultado['icono']   = 'glyphicon glyphicon-ok';
        } else {
            // Si el ID maestro no existía o ya estaba anulado
            $db->rollBack();
            $resultado['mensaje'] = "No se encontró la liquidación especificada o ya se encontraba anulada.";
        }

    } catch (PDOException $e) {
        // Si cualquiera de los 3 pasos falla, revertimos todo para evitar estados inconsistentes [stmt, stmt]
        if (isset($db) && $db->inTransaction()) {
            $db->rollBack();
        }
        
        $resultado['estado']  = false;
        $resultado['mensaje'] = "Error crítico al anular la liquidación: " . $e->getMessage();
    }

    // 4. Retornar respuesta y redirigir
    session_start();
    $_SESSION['alerta'] = $resultado;
    header("Location: ../vista_liquidaciones.php"); 
    exit;
}
