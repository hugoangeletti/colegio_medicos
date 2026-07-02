<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/registroDNU260Logic.php');
$registroDNU260Logic = new registroDNU260Logic();
require_once ('../dataAccess/distritoLogic.php');
$distritoLogic = new distritoLogic();

$titulo = "";
$continuar = TRUE;
if (isset($_GET['id']) && $_GET['id'] > 0) {
  $idRegistro = $_GET['id'];

  $resRegistro = $registroDNU260Logic->obtenerRegistroPorId($idRegistro);
  if ($resRegistro['estado']) {
    $registro = $resRegistro['datos'];
    $apellido = $registro['apellido'];
    $nombre = $registro['nombre'];
    $mail = $registro['mail'];
    $numero = $registro['numero'];
    $titulo .= " Registro Número: ".$numero.' - '.trim($apellido).', '.trim($nombre); 
  } else {
    $continuar = FALSE;
  }
} else {
  $continuar = FALSE;
}

if ($continuar) {
if (isset($_POST['mensaje']))
{
 ?>
   <div class="ocultarMensaje"> 
   <p class="<?php echo $_POST['tipomensaje'];?>"><?php echo $_POST['mensaje'];?></p>  
   </div>
 <?php    
}   
?> 
<div class="panel panel-danger">
    <div class="panel-heading">
      <div class="row">
        <div class="col-md-9">
            <h4><b>Emisión de Certificados del <?php echo $titulo ?></b></h4>
        </div>
        <div class="col-md-3 text-right">
            <form  method="POST" action="registro_dnu260_lista.php">
                <button type="submit" class="btn btn-danger" name='volver' id='name'>Volver al listado </button>
            </form>
        </div>
      </div>
    </div>
    <div class="panel-body">
        <form id="datosCertificado" autocomplete="off" name="datosCertificado" method="POST" onSubmit="" target="_blank" action="datosRegistroDnu260/genera_certificado.php?id=<?php echo $idRegistro; ?>">
        <div class="row">
            <div id="grupoParaEnviar" class="col-md-3 text-center">
                <label>Para enviar a </label><br>
                <?php 
                if ($registro['estado'] == 'A' && $registro['fechaVencimiento'] >= date('Y-m-d')) {
                ?> 
                <label class="radio-inline">
                    <input type="radio" name="paraEnviar" id="paraEnviar" value="1" checked="" >Lugar de trabajo
                </label>
                <?php
                }
                ?>
                <label class="radio-inline">
                    <input type="radio" name="paraEnviar" id="paraEnviar" value="2" <?php if ($registro['fechaVencimiento'] < date('Y-m-d')) { echo 'checked=""'; } ?>>Otro Distrito
                </label>
            </div>
            <div class="col-md-2">
                <label>Para presentar en *</label>
                <select class="form-control" id="paraPresentar" name="paraPresentar" <?php if ($registro['estado'] == 'B' || $registro['fechaVencimiento'] < date('Y-m-d')) { echo 'required=""'; } ?> >
                    <?php
                    $resLaboral = $registroDNU260Logic->obtenerDatosLaborales($idRegistro);
                    if ($resLaboral['estado']) {
                        ?>
                        <option value="">Seleccione Lugar de Trabajo</option>
                            <?php
                                foreach ($resLaboral['datos'] as $row) {
                                    if ($row['idRegistroLaboral'] > 1) {
                                ?>
                                    <option value="<?php echo $row['idRegistroLaboral'] ?>"><?php echo $row['entidad'] ?></option>
                                <?php
                                    }
                                }
                                ?>
                        <?php
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-2">
                <label>Distrito de cambio *</label>
                <select class="form-control" id="distrito" name="distrito" <?php if ($registro['estado'] == 'B' || $registro['fechaVencimiento'] < date('Y-m-d')) { echo 'required=""'; } ?> >
                    <?php
                    $resDistritos = $distritoLogic->obtenerDistritos();
                    if ($resDistritos['estado']) {
                        ?>
                        <option value="">Seleccione Distrito</option>
                            <?php
                                foreach ($resDistritos['datos'] as $row) {
                                    if ($row['id'] > 1) {
                                ?>
                                    <option value="<?php echo $row['id'] ?>"><?php echo $row['romano'] ?></option>
                                <?php
                                    }
                                }
                                ?>
                        <?php
                    }
                    ?>
                </select>
            </div>
            <div id="grupoEnviaMail" class="col-md-4">
                <label>Env&iacute;a por mail?</label>
                <label class="radio-inline"><input type="radio" name="enviaMail" id="enviaMail" value="S" checked="">Si</label>
                <label class="radio-inline"><input type="radio" name="enviaMail" id="enviaMail" value="N">No</label>
                <input type="email" class="form-control" id="mail" name="mail" value="<?php echo $mail; ?>" placeholder="Ingrese un correo electrónico válido" >
            </div>
        </div>
        <div class="row">&nbsp;</div>
        <div class="row">
            <div class="col-md-6 text-right">
                <button type="submit"  class="btn btn-success btn-lg" >Confirma Certificado</button>
            </div>
        </div>    
    </form>
    </div>
</div>
<?php
} else {
?>
    <div class="col-md-12">
        <div class="alert alert-error" role="alert">
            <span class="glyphicon glyphicon-remove" aria-hidden="true"></span>
            <span><strong>Ingreso incorrecto!</strong></span>
        </div>        
    </div>
    <div class="row">&nbsp;</div>
    <div class="col-md-12">            
        <form id="formColegiado" name="formColegiado" method="POST" onSubmit="" action="colegiado_consulta.php">
            <button type="submit"  class="btn btn-success" >Volver a buscar colegiado</button>
        </form>
    </div>
<?php
}
require_once '../html/footer.php';
?>
<script>
    var lastSelected;
    $(function () {
        //if you have any radio selected by default
        lastSelected = $('[name="conFirma"]:checked').val();
    });
    $(document).on('click', '[name="conFirma"]', function () {
        if (lastSelected != $(this).val() && typeof lastSelected != "undefined") {
            var x = document.getElementById("grupoEnviaMail");
            //if (x.style.display === "none") {
            if (lastSelected != 'S') {
                x.style.display = "block";
            } else {
                x.style.display = "none";
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