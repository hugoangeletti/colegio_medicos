<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/resolucionesLogic.php');
$resolucionesLogic = new resolucionesLogic();
require_once ('../dataAccess/colegiadoLogic.php');
require_once ('../dataAccess/mesaEntradaEspecialistaLogic.php');
$mesaEntradaEspecialistaLogic = new mesaEntradaEspecialistaLogic();
?>
<script>
    $(document).ready(function () {
        $('#tablaOrdenada').DataTable({
            "iDisplayLength":25,
            "order": [[ 0, "desc" ]],
            "language": {
                "url": "../public/lang/esp.lang"
            },
            dom: 'T<"clear">lfrtip',
            tableTools: {
               "sSwfPath": "../public/swf/copy_csv_xls_pdf.swf", 
               "aButtons": [
                    {
                        "sExtends": "pdf",
                        "mColumns" : [0, 1, 2, 3, 4, 5, 6],
//                                "oSelectorOpts": {
//                                    page: 'current'
//                                }
                        "sTitle": "Listado de expedientes",
                        "sPdfOrientation": "portrait",
                        "sFileName": "listado_de_expedientes.pdf"
//                              "sPdfOrientation": "landscape",
//                              "sPdfSize": "letter",  ('A[3-4]', 'letter', 'legal' or 'tabloid')
                    }

            ]
            }
        });
    });              
</script>
<?php
$continua = TRUE;
//if (isset($_GET['accion']) && isset($_GET['estado']) && isset($_GET['anio']) && isset($_GET['idResolucion'])) {
if (isset($_GET['accion']) && isset($_GET['idResolucion'])) {
    $accion = $_GET['accion'];
    //$estadoResoluciones = $_GET['estado'];
    //$anioResoluciones = $_GET['anio'];
    $idResolucion = $_GET['idResolucion'];
    $titulo = "";
    $resResolucion = $resolucionesLogic->obtenerResolucionPorId($idResolucion);
    if ($resResolucion['estado']) {
        $resolucion = $resResolucion['datos'];
        $fechaResolucion = $resolucion['fecha'];
        $idTipoResolucion = $resolucion['idTipoResolucion'];
        $estadoResoluciones = $resolucion['estado'];
        $anioResoluciones = substr($fechaResolucion, 0, 4);
        $detalleTipoResolucion = $resolucion['detalleTipoResolucion'];
        $tipoEspecialista = $resolucion['tipoEspecialista'];
        $titulo = 'Resolución de '.$detalleTipoResolucion.' Nº '.$resolucion['numero'].' de fecha '.  cambiarFechaFormatoParaMostrar($fechaResolucion);
    } else {
        $continua = FALSE;        
    }
     
    if (isset($_GET['id'])){
        $idResolucionDetalle = $_GET['id'];
    } else {
        $idResolucionDetalle = NULL;
    }

    if (isset($idResolucionDetalle)){
        $resDetalle = $resolucionesLogic->obtenerResolucionDetallePorId($idResolucionDetalle);
        if ($resDetalle['estado']){
            $resolucionDetalle = $resDetalle['datos'];
            $idResolucion = $resolucionDetalle['idResolucion'];
            $tipoEspecialista = $resolucionDetalle['tipo'];
            $inciso = $resolucionDetalle['inciso'];
            $especialidad = $resolucionDetalle['especialidad'];
            $especialidadDetalle = $resolucionDetalle['especialidadDetalle'];
            $estado = $resolucionDetalle['estado']; 
            $fechaAprobada = $resolucionDetalle['fechaAprobada'];
            $fechaRecertificacion = $resolucionDetalle['fechaRecertificacion'];
            $idColegiado = $resolucionDetalle['idColegiado'];
            $matricula = $resolucionDetalle['matricula'];
            $apellidoNombre = trim($resolucionDetalle['apellido']).' '.trim($resolucionDetalle['nombre']);
        } else {
            $continua = FALSE;
        }
        $titulo .= " (Editar Matrícula)";
        $nombreBoton="Guardar cambios";
    } else {
        $titulo .= " (Nueva Matrícula)";
        $nombreBoton="Guardar";
        if ($idTipoResolucion == 2 || $idTipoResolucion == 9) {
            $tipoEspecialista = "R";
        } 
        $especialidad = "";
        $especialidadDetalle = "";
        $estado = "A"; 
        $fechaAprobada = $fechaResolucion;
        $fechaRecertificacion = "";
        $idColegiado = "";
        $matricula = NULL;
        $apellidoNombre = "";
        $inciso = "";
    }        
} else {
    $continua = FALSE;
}
?>

<div class="container-fluid">
    <div class="panel panel-default">
    <div class="panel-heading"><h4><b><?php echo $titulo; ?></b></h4></div>
    <div class="panel-body">
        <?php
        if ($continua){
        ?>
            <?php
            if (isset($_POST['mensaje']))
            {
             ?>
                <div id="divMensaje"> 
                    <p class="<?php echo $_POST['tipomensaje'];?>"><?php echo $_POST['mensaje'];?></p>  
                </div>
             <?php    
            }
            
            $resMesaEntradaEspecialista = $mesaEntradaEspecialistaLogic->obtenerMesaEntradaEspecialistaParaResolucion($tipoEspecialista);
            if ($resMesaEntradaEspecialista['estado']) {
            ?>
                <table id="tablaOrdenada" class="display">
                    <thead>
                        <tr>
                            <th style="display: none;">Id</th>
                            <th>Expediente</th>
                            <th>Fecha Entrada</th>
                            <th>Matricula</th>
                            <th>Apellido y Nombre</th>
                            <th>Especialidad</th>
                            <th>Tipo trámite</th>
                            <th style="text-align: center;">Seleccione</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($resMesaEntradaEspecialista['datos'] as $row) {
                            $idMesaEntradaEspecialidad = $row['idMesaEntradaEspecialidad'];
                        ?>
                            <tr>
                                <td style="display: none;"><?php echo $idMesaEntradaEspecialidad; ?></td>
                                <td><?php echo $row['numeroExpediente'].'/'.$row['anioExpediente']; ?></td>
                                <td><?php echo cambiarFechaFormatoParaMostrar($row['fechaIngreso']); ?></td>
                                <td><?php echo $row['matricula']; ?></td>
                                <td><?php echo $row['apellidoNombre']; ?></td>
                                <td><?php echo $row['nombreEspecialidad']; ?></td>
                                <td><?php echo $row['tipoEspecialista']; 
                                        if ($row['inciso'] <> "") {
                                            echo " inc. ".$row['inciso']; 
                                        } ?></td>
                                <td style="text-align: center;">
                                    <a href="datosResoluciones/abm_resolucion_detalle.php?idResolucion=<?php echo $idResolucion; ?>&accion=1&id=<?php echo $idMesaEntradaEspecialidad ?>&fecha=<?php echo $fechaResolucion; ?>" class="btn btn-success">Agregar</a>
                                    <!--<input class="form-check-input" name="ids_MesaEntrada[]" type="checkbox" value="<?php echo $idMesaEntradaEspecialidad ?>" id="<?php echo $idMesaEntradaEspecialidad ?>">-->
                                </td>
                            </tr>
                        <?php
                        }
                        ?>
                    </tbody>
                </table>
            <?php 
            } else {
            ?>
                <div class="<?php echo $resMesaEntradaEspecialista['clase']; ?>" role="alert">
                    <span class="<?php echo $resMesaEntradaEspecialista['icono']; ?>" ></span>
                    <span><strong><?php echo $resMesaEntradaEspecialista['mensaje']; ?></strong></span>
                </div>                        
            <?php
            }
            ?>
            <div class="row">&nbsp;</div>
        <?php
        } 
        ?>
    </div>
</div>
<!-- BOTON VOLVER -->    
    <div class="col-md-12" style="text-align:right;">
        <form  method="POST" action="especialidades_resoluciones_matriculas.php?idResolucion=<?php echo $idResolucion; ?>&estado=<?php echo $estadoResoluciones; ?>&accion=3&anio=<?php echo $anioResoluciones ?>">
            <button type="submit" class="btn btn-default" name='volver' id='name'>Volver </button>
            <input type="hidden" id="estadoResoluciones" name="estadoResoluciones" value="<?php echo $estadoResoluciones; ?>">
            <input type="hidden" id="anioResoluciones" name="anioResoluciones" value="<?php echo $anioResoluciones; ?>">
       </form>
    </div>  

</div>
<?php
require_once '../html/footer.php';
