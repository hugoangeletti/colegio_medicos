<?php
// 1. PROCESAMIENTO PHP AL INICIO ABSOLUTO (Evita el error de "headers already sent")
require_once ('../dataAccess/config.php');
permisoLogueado();

// Habilitar visualización de errores (Útil en desarrollo)
error_reporting(E_ALL);
ini_set('display_errors', 1);

$resultados = array();
$directorio_destino = '../archivos/lotes/a_procesar/';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['archivos_lotes'])) {
    if (!file_exists($directorio_destino)) {
        mkdir($directorio_destino, 0755, true);
    }
    
    $files = $_FILES['archivos_lotes'];
    $file_count = is_array($files['name']) ? count($files['name']) : 1;
    
    for ($i = 0; $i < $file_count; $i++) {
        $error_code = is_array($files['error']) ? $files['error'][$i] : $files['error'];
        $name = is_array($files['name']) ? $files['name'][$i] : $files['name'];
        $tmp_name = is_array($files['tmp_name']) ? $files['tmp_name'][$i] : $files['tmp_name'];
        
        if ($error_code === UPLOAD_ERR_OK) {
            $nombre_original = basename($name);
            $nombre_limpio = preg_replace("/[^a-zA-Z0-9\._-]/", "_", $nombre_original);
            $ruta_final = $directorio_destino . $nombre_limpio;
            
            if (pathinfo($nombre_limpio, PATHINFO_EXTENSION) === 'php') {
                $resultados[] = array('estado' => 'error', 'msg' => "<strong>$nombre_limpio</strong>: Extensión .php denegada.");
            } else if (move_uploaded_file($tmp_name, $ruta_final)) {
                $resultados[] = array('estado' => 'ok', 'msg' => "<strong>$nombre_limpio</strong>: Subido con éxito.");
            } else {
                $resultados[] = array('estado' => 'error', 'msg' => "<strong>$nombre_limpio</strong>: Error al mover al destino.");
            }
        } else if ($error_code !== UPLOAD_ERR_NO_FILE) {
            $resultados[] = array('estado' => 'error', 'msg' => "Error al subir archivo. Código: $error_code");
        }
    }

    // Limpiar el búfer e imprimir respuesta JSON limpia
    if (ob_get_length()) ob_clean();
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($resultados, JSON_UNESCAPED_UNICODE);
    exit; 
}

// 2. CARGAR COMPONENTES VISUALES LUEGO DEL PROCESAMIENTO
require_once ('../html/head.php');
require_once ('../html/header.php');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Subir Lotes Múltiples</title>
</head>
<body>
    <div class="container" style="margin-top: 50px;">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                
                <!-- Contenedor dinámico de alertas -->
                <div id="alert-container"></div>

                <div class="panel panel-primary">
                    <div class="panel-heading">
                        <h3 class="panel-title text-center" style="font-weight: bold; font-size: 18px;">
                            Carga de Lotes en Conjunto
                        </h3>
                    </div>
                    <div class="panel-body" style="padding: 30px;">
                        <div class="row">
                            <div class="col-xs-12">
                                <form id="upload-form" action="subir_archivos.php" method="POST" enctype="multipart/form-data">
                                    
                                    <!-- Control de selección de archivos tradicional de Bootstrap -->
                                    <div class="form-group">
                                        <label for="archivo_lote" style="font-weight: bold; margin-bottom: 10px;">
                                            Selecciona uno o varios archivos desde tu PC:
                                        </label>
                                        <input type="file" name="archivos_lotes[]" id="archivo_lote" multiple="multiple" class="form-control" style="height: auto; padding: 10px;">
                                    </div>

                                    <!-- Botón de envío manual -->
                                    <div class="form-group" style="margin-top: 25px;">
                                        <button type="submit" id="btn-subir" class="btn btn-success btn-block btn-lg" style="font-weight: bold;">
                                            <i class="glyphicon glyphicon-upload"></i> Subir Archivos Seleccionados
                                        </button>
                                    </div>
                                </form>
                            </div>
                            <div class="col-xs-12 text-center">
                                <a href="cobranza_lotes.php" class="btn btn-default">Volver</a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Panel dinámico para renderizar la respuesta del servidor en el HTML -->
                <div class="panel panel-info" id="server-response-panel" style="display: none; margin-top: 20px;">
                    <div class="panel-heading">
                        <h4 class="panel-title" style="font-weight: bold;">
                            Resumen del Servidor
                            <a href="archivos_lotes_a_procesar.php" class="btn btn-info">Procesar lotes</a>
                        </h4>
                    </div>
                    <div class="panel-body">
                        <ul id="response-list" class="list-group" style="margin-bottom: 0;"></ul>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Cargar jQuery (Asegúrate de que no esté cargado previamente en header.php) -->
    <script src="https://googleapis.com"></script>

    <script>
    $(document).ready(function() {
        var $form = $('#upload-form');
        var $btn = $('#btn-subir');
        var $alertContainer = $('#alert-container');
        var $responsePanel = $('#server-response-panel');
        var $responseList = $('#response-list');

        $form.on('submit', function(e) {
            e.preventDefault(); // Detener el comportamiento de recarga nativo del formulario

            var files = $('#archivo_lote').prop('files');
            if (files.length === 0) {
                $alertContainer.html('<div class="alert alert-warning alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert">&times;</button><strong>Atención:</strong> Debes seleccionar al menos un archivo primero.</div>');
                return;
            }

            // Deshabilitar botón para evitar clics duplicados
            $btn.prop('disabled', true).html('<i class="glyphicon glyphicon-refresh"></i> Subiendo archivos, por favor espera...');

            // Preparar el objeto FormData con los archivos seleccionados
            var formData = new FormData(this);

            $.ajax({
                url: $form.attr('action'),
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                dataType: 'json',
                success: function(response) {
                    // Restaurar interfaz
                    $btn.prop('disabled', false).html('<i class="glyphicon glyphicon-upload"></i> Subir Archivos Seleccionados');
                    $('#archivo_lote').val(''); // Limpiar la selección de archivos
                    $alertContainer.empty();
                    $responseList.empty();
                    
                    // Mostrar panel de resultados
                    $responsePanel.show();

                    // Renderizar cada respuesta del array devuelto por PHP
                    if (Array.isArray(response)) {
                        response.forEach(function(item) {
                            var claseItem = item.estado === 'ok' ? 'list-group-item-success' : 'list-group-item-danger';
                            var icono = item.estado === 'ok' ? 'glyphicon-ok-sign' : 'glyphicon-remove-sign';
                            
                            var htmlItem = '<li class="list-group-item ' + claseItem + '">' +
                                           '<span class="glyphicon ' + icono + '" style="margin-right: 8px;"></span>' +
                                           item.msg + 
                                           '</li>';
                            $responseList.append(htmlItem);
                        });
                    }
                },
                error: function(xhr) {
                    // Restaurar interfaz en caso de fallo crítico de red o PHP roto
                    $btn.prop('disabled', false).html('<i class="glyphicon glyphicon-upload"></i> Subir Archivos Seleccionados');
                    $responsePanel.hide();

                    var htmlError = '<div class="alert alert-danger alert-dismissible" role="alert">' +
                                    '<button type="button" class="close" data-dismiss="alert">&times;</button>' +
                                    '<strong>Error crítico:</strong> El servidor no pudo procesar la solicitud de forma adecuada.</div>';
                    $alertContainer.html(htmlError);
                    
                    console.error("Respuesta fallida del servidor:", xhr.responseText);
                }
            });
        });
    });
    </script>
</body>
</html>
