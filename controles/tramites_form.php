<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/tramiteLogic.php');

$continua = TRUE;
$accion = "";
$mensaje = "";
if (isset($_POST['accion']) && $_POST['accion'] >= 1 && $_POST['accion'] <= 3) {
    $accion = $_POST['accion'];
} else {
    $continua = FALSE;
    $mensaje .= "Falta ingresar accion - ";
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
        $detalle = $_POST['detalle'];
        $fechaDesde = $_POST['fechaDesde'];
        $fechaHasta = $_POST['fechaHasta'];
    } else {
        $fechaDesde = null; //obtenerFechaDesdeTramite();
        $fechaHasta = date('Y-m-d');
        $detalle = "Movimientos matriculares";
    }
    switch ($accion) {
        case 1:
            $tituloAccion = 'Alta de listado';
            break;

        case 2:
            $tituloAccion = 'Eliminar listado';
            break;

        case 3:
            $tituloAccion = 'Modificar listado';
            break;

        default:
            $tituloAccion = 'Error al cargar listado';
            break;
    }
    ?>  
    <div class="container-fluid">
        <div class="panel panel-info">
        <div class="panel-heading"><h4><b>Generar listado de momivientos matriculares y altas</h4></div>
        <div class="panel-body">
            <div class="row">
                <div class="col-md-12 text-center">
                    <h3><?php echo $tituloAccion; ?></h3>
                </div>
            </div>
            <form id="formEliminar" name="formEliminar" method="POST" onSubmit="" action="datosTramites\genera_tramites.php">
                <div class="row">
                    <div class="col-md-2">
                        Fecha desde *
                        <input class="form-control" type="date" name="fechaDesde" max="<?php echo date('Y-m-d'); ?>" value="<?php echo $fechaDesde ?>" required=""/>
                    </div>
                    <div class="col-md-2">
                        Fecha hasta *
                        <input class="form-control" type="date" name="fechaHasta" max="<?php echo date('Y-m-d'); ?>" value="<?php echo $fechaHasta ?>" required=""/>
                    </div>
                    <div class="col-md-2">
                        Tipo Trámites *
                        <select class="form-control" id="tipoTramite" name="tipoTramite" required >
                            <option value="M" selected >Movimientos y Altas</option>
                            <option value="A" >Altas</option>
                            <option value="F" >Fallecidos</option>
                            <option value="J" >Jubilados</option>
                        </select>
                    </div>
                    <!--<div class="col-md-6">
                        Detalle
                        <input class="form-control" type="text" name="detalle" value="<?php echo $detalle ?>" required />
                    </div>-->
                </div>                
                <div class="row">&nbsp;</div>
                <div class="row">
                    <div class="col-md-12 text-center">
                        <button type="submit"  class="btn btn-primary">Confirma</button>
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
        <form  method="POST" action="tramites_listado.php">
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
