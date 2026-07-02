<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/habilitacionConsultorioLogic.php');
$habilitacionConsultorioLogic = new habilitacionConsultorioLogic();

$continua = TRUE;
$accion = "";
if (isset($_GET['id'])){
    $idInspectorHabilitacion = $_GET['id'];
    $resHabilitacion = $habilitacionConsultorioLogic->obtenerInspeccionPorId($idInspectorHabilitacion);
    if ($resHabilitacion['estado']) {
        $habilitacion = $resHabilitacion['datos'];
    } else {
        $continua = FALSE;
    }        
    if (isset($_POST['accion']) && $_POST['accion'] >= 1 && $_POST['accion'] <= 3) {
        $accion = $_POST['accion'];
    } else {
        $continua = FALSE;
    }
} else {
    $continua = FALSE;
}

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
        $habilita = $_POST['habilita'];
        $fechaInspeccion = $_POST['fechaInspeccion'];
        if (isset($_POST['fechaHabilitacion']) && $_POST['fechaHabilitacion'] != "") {
            $fechaHabilitacion = $_POST['fechaHabilitacion'];
        } else {
            $fechaHabilitacion = "";
        }
        if (isset($_POST['observaciones']) && $_POST['observaciones']) {
            $observaciones = $_POST['observaciones'];
        } else {
            $observaciones = "";
        }
    } else {
        $fechaInspeccion = $habilitacion['fechaInspeccion'];
        $fechaHabilitacion = $habilitacion['fechaHabilitacion'];
        $estadoInspeccion = $habilitacion['estadoInspeccion'];
        $observaciones = $habilitacion['motivoNoHabilita'];
        switch ($estadoInspeccion) {
            case 'H':
                $habilita = 'S';
                break;

            case 'B':
                $habilita = 'B';
                break;

            case 'N':
                $habilita = 'N';
                break;

            default:
                $habilita = 'S';
                break;
        }
    }
    switch ($accion) {
        case 1:
            $tituloAccion = 'Alta de Inspección';
            $volver = 'habilitaciones_asignadas_lista.php';
            break;

        case 2:
            $tituloAccion = 'Eliminar Inspección';
            $volver = 'habilitaciones_confirmadas_lista.php';
            break;

        case 3:
            $tituloAccion = 'Modificar Inspección';
            $volver = 'habilitaciones_confirmadas_lista.php';
            break;

        default:
            $tituloAccion = 'Error al cargar Inspección';
            $volver = 'administracion.php';
            break;
    }
    ?>  
    <div class="container-fluid">
        <div class="panel panel-primary">
        <div class="panel-heading"><h4><b>Inspección y Habilitación de Consultorio</b> - INSPECTOR: <?php echo $habilitacion['apellidoNombreInspector'].' ('.$habilitacion['matriculaInspector'].')'; ?></h4></div>
        <div class="panel-body">
            <div class="row">
                <div class="col-md-12 text-center">
                    <h3><?php echo $tituloAccion; ?></h3>
                </div>
            </div>
            <form id="formEliminar" name="formEliminar" method="POST" onSubmit="" action="datosHabilitaciones\confirma_inspeccion.php">
                <div class="row">
                    <div class="col-md-2">
                        Matrícula Solicitante:
                        <b><input class="form-control" type="text" value="<?php echo $habilitacion['matriculaColegiado']; ?>" readonly=""/></b>
                    </div>
                    <div class="col-md-4">
                        Apellido y Nombre Solicitante: 
                        <b><input class="form-control" type="text" value="<?php echo $habilitacion['apellidoNombreColegiado']; ?>" readonly=""/></b>
                    </div>
                    <div class="col-md-6">
                        Dirección: 
                        <b><input class="form-control" type="text" value="<?php echo $habilitacion['domicilio']; ?>" readonly=""/></b>
                    </div>
                    <!--
                    <div class="col-md-2">
                        Matrícula Inspector:
                        <b><input class="form-control" type="text" value="<?php echo $habilitacion['matriculaInspector']; ?>" readonly=""/></b>
                    </div>
                    <div class="col-md-4">
                        Apellido y Nombre Inspector: 
                        <b><input class="form-control" type="text" value="<?php echo $habilitacion['apellidoNombreInspector']; ?>" readonly=""/></b>
                    </div>
                    -->
                </div>
                <div class="row">&nbsp;</div>
                <div class="row" <?php if ($accion == 2) { ?> style="display: none;" <?php } ?>>
                    <div class="col-md-2">
                        Habilitado? *
                        <select class="form-control" id="habilita" name="habilita" <?php if ($accion != 2) { ?> required="" <?php } ?>>
                            <option value="S" <?php if ($habilita == 'S') { ?> selected="" <?php } ?>>SI</option>
                            <option value="N" <?php if ($habilita == 'N') { ?> selected="" <?php } ?>>NO</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        Fecha de inspección *
                        <input class="form-control" type="date" name="fechaInspeccion" max="<?php echo date('Y-m-d'); ?>" value="<?php echo $fechaInspeccion ?>" <?php if ($accion != 2) { ?> required="" <?php } ?>/>
                    </div>
                    <div id="grupoFechaHabilitacion" class="col-md-2" <?php if ($habilita == 'N') { ?> style="display: none;" <?php } ?>>
                        Fecha de habilitación
                        <input class="form-control" type="date" name="fechaHabilitacion" max="<?php echo date('Y-m-d'); ?>" value="<?php echo $fechaHabilitacion ?>"/>
                    </div>
                        <div id="grupoMotivo" class="col-md-8" <?php if ($habilita == 'S') { ?> style="display: none;" <?php } ?>>
                        Motivo por el cual no se Habilita
                        <textarea class="form-control" name="observaciones" id="observaciones" rows="5"><?php echo $observaciones; ?></textarea>
                    </div>
                </div>                
                <div class="row">&nbsp;</div>
                <div class="row">
                    <div class="col-md-12 text-center">
                        <button type="submit"  class="btn btn-primary">Confirma</button>
                        <input type="hidden" name="idInspectorHabilitacion" id="idInspectorHabilitacion" value="<?php echo $idInspectorHabilitacion; ?>" />
                        <input type="hidden" name="accion" id="accion" value="<?php echo $accion; ?>" />
                    </div>
                </div>
            </form>   
        </div>
     </div>

<?php
} else {
    echo 'Error de Acceso';
}
?>
    <!-- BOTON VOLVER -->    
    <div class="col-md-12" style="text-align:right;">
        <form  method="POST" action="<?php echo $volver; ?>">
            <button type="submit" class="btn btn-info" name='volver' id='name'>Volver </button>
       </form>
    </div>  
    <div class="row">&nbsp;</div>
<?php    
require_once '../html/footer.php';
?>
<script>
    var lastSelected;
    $(function () {
        //if you have any radio selected by default
        lastSelected = $('[name="habilita"]:checked').val();
    });
    $(document).on('click', '[name="habilita"]', function () {
        if (lastSelected != $(this).val() && typeof lastSelected != "undefined") {
            var x = document.getElementById("grupoMotivo");
            var fh = document.getElementById("grupoFechaHabilitacion");
            //if (x.style.display === "none") {
            if (lastSelected != 'N') {
                x.style.display = "block";
                fh.style.display = "none";
            } else {
                x.style.display = "none";
                fh.style.display = "block";
            }
            //alert("radio box with value " + $('[name="conFirma"][value="' + lastSelected + '"]').val() + " was deselected");
        }
        lastSelected = $(this).val();
    });
    /*
    var rad = document.datosCertificado.conFirma;
    var prev = null;
    for(var i = 0; i < rad.length; i++) {
        rad[i].onclick = function() {
            (prev)? console.log(prev.value):null;
            if(this !== prev) {
                prev = this;
                alert('Cambia radio');
            }
            console.log(this.value)
        };
    }
    */
</script>
