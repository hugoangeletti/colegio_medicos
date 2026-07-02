<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/envios_caja_medicosLogic.php');

$continua = TRUE;
$accion = "1";
$mensaje = "";
if (isset($_POST['mensaje'])) {
?>
    <div id="divMensaje"> 
        <p class="<?php echo $_POST['tipomensaje'];?>"><?php echo $_POST['mensaje'];?></p>  
    </div>
<?php    
    $fechaDesde = $_POST['fechaDesde'];
    $fechaHasta = $_POST['fechaHasta'];
} else {
    $mes_y_año = date('Y-m', strtotime("-1 month"));
    $fechaDesde = $mes_y_año."-01";
    $fechaHasta = date('Y-m-t', strtotime("-1 month"));
}
?>  
<div class="container-fluid">
    <div class="panel panel-info">
    <div class="panel-heading">
        <div class="row">
            <div class="col-md-10">
                <h4>Generar listado de momivientos matriculares y altas</h4>
            </div>
            <div class="col-md-2 text-right">
                <form  method="POST" action="envios_caja_medicos.php">
                    <button type="submit" class="btn btn-info" name='volver' id='name'>Volver </button>
               </form>
            </div>  
        </div>
    </div>
    <div class="panel-body">
        <form id="formEliminar" name="formEliminar" method="POST" onSubmit="" action="datosTramites\genera_envio_caja_medicos.php">
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
                    <br>
                    <button type="submit"  class="btn btn-primary">Confirma</button>
                </div>
            </div>
        </form>   
    </div>
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
