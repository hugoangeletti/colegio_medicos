<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/registroDNU260Logic.php');
$registroDNU260Logic = new registroDNU260Logic();

$accion = $_POST['accion'];
$continua = TRUE;
if (isset($_GET['idRegistro']) && $_GET['idRegistro'] > 0) {
    $idRegistro = $_GET['idRegistro'];
    $resRegistro = $registroDNU260Logic->obtenerRegistroPorId($idRegistro);
    if ($resRegistro['estado']) {
        $registro = $resRegistro['datos'];
        $apellido = $registro['apellido'];
        $nombre = $registro['nombre'];
        $numero = $registro['numero'];
        $titulo = "Baja del Registro Número: ".$numero.' - '.trim($apellido).', '.trim($nombre); 
    } else {
        $continua = FALSE;
    }
} else {
    $continua = FALSE;
}

if ($continua) {
    if (isset($_POST['mensaje'])) {
        //var_dump($_POST);
        $tipoBaja = $_POST['tipoBaja'];
        $matricula = $_POST['matricula'];
        $motivoBaja = $_POST['motivoBaja'];
        ?>
        <!--<div class="ocultarMensaje">--> 
            <div class="<?php echo $_POST['clase']; ?>" role="alert">
                <span class="<?php echo $_POST['icono'];?>" aria-hidden="true"></span>
                <span><?php echo $_POST['mensaje'];?></span>
            </div>
        <!--</div>-->
        
    <?php
    } else {
        if ($accion == 1) {
            $tipoBaja = NULL;
            $matricula = NULL;
            $motivoBaja = array();
        }    
    }
}
if ($continua) {
?>
<div class="panel panel-danger">
    <div class="panel-heading">
        <div class="row">
            <div class="col-md-9">
                <h4><?php echo $titulo; ?></h4>
            </div>
            <div class="col-md-3 text-right">
                <form  method="POST" action="registro_dnu260_lista.php">
                    <button type="submit" class="btn btn-danger" name='volver' id='name'>Volver al listado </button>
                </form>
            </div>
        </div>
    </div>
    <div class="panel-body">
        <div class="col-md-12">
        <form id="datosBaja" autocomplete="off" name="datosBaja" method="POST" onSubmit="" action="datosRegistroDnu260/baja_registro.php?id=<?php echo $idRegistro ?>">
            <div class="row">
                <div class="col-md-4">
                    <!--
                    <select required="required" class="form-control" name="tipoBaja">
                       <option value="1">Porque se matriculó</option>
                       <option value="2">Porque no presentó documentación</option>
                       <option value="3">Porque deja de ejercer en el DIstrito I</option>
                    </select>
                -->
                    <h4><input type="radio" name="tipoBaja" id="tipoBaja" value="1" checked="">&nbsp;Porque se matriculó</h4>
                    <h4><input type="radio" name="tipoBaja" id="tipoBaja" value="2">&nbsp;Porque no presentó documentación</h4>
                    <h4><input type="radio" name="tipoBaja" id="tipoBaja" value="3">&nbsp;Porque deja de ejercer en el DIstrito I</h4>
                </div>
                <div id="grupoMatricula" class="col-md-6" style="display: block;">
                    <h4><label>N° de matrícula </label>
                    <input type="number" name="matricula" value=""/></h4>
                </div>
                <div id="grupoMotivoBaja" class="col-md-6" style="display: none;">
                    <br>
                    <br>
                    <h4>
                    <input class="form-check-input" name="motivoBaja[]" type="checkbox" value="revalida" id="1">
                    <label class="form-check-label" for="1"> CONSTANCIA DE REVALIDA</label>
                    <br>
                    <input class="form-check-input" name="motivoBaja[]" type="checkbox" value="convalida" id="2">
                    <label class="form-check-label" for="2"> CONSTANCIA DE CONVALIDA</label>
                    <br>
                    <input class="form-check-input" name="motivoBaja[]" type="checkbox" value="constanciaLaboral" id="3">
                    <label class="form-check-label" for="3"> CONSTANCIA LABORAL</label>
                    </h4>
                </div>
            </div>
            <div class="row">&nbsp;</div>

            <div class="row">
                <div class="col-md-12 text-center">
                    <br>
                    <button type="submit"  class="btn btn-success btn-lg" >Confirma </button>
                    <input type="hidden" name="accion" id="accion" value="<?php echo $accion; ?>" />
                </div>
            </div>
        </form>
    </div>
    </div>
</div>
<?php
} else {
?>
    <div class="row">
        <div class="col-md-12 alert alert-danger">MAL INGRESO</div>
    </div>
<?php
}
require_once '../html/footer.php';
?>
<script>
    var lastSelected;
    $(function () {
        //if you have any radio selected by default
        //lastSelected = $('[name="tipoBaja"]:checked').val();
        lastSelected = $('[name="tipoBaja"]:checked').val();
    });
    $(document).on('change', '[name="tipoBaja"]', function (event) {
        lastSelected = $(this).val();
        //if (lastSelected != $(this).val() && typeof lastSelected != "undefined") {
            var x = document.getElementById("grupoMatricula");
            var y = document.getElementById("grupoMotivoBaja");
            if (lastSelected == '1') {
                x.style.display = "block";
                y.style.display = "none";
            } else {
                if (lastSelected == '2') {
                    x.style.display = "none";
                    y.style.display = "block";
                } else {
                    x.style.display = "none";
                    y.style.display = "none";
                }
            }
        //}
        //lastSelected = $(this).val();
    });
</script>