<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/falsosMedicosLogic.php');
$falsosMedicosLogic = new falsosMedicosLogic();

if (isset($_GET['accion'])) {
    $accion = $_GET['accion'];
} else {
    $accion = 1;
}

$continua = TRUE;
if ($accion <> 1) {
    if (isset($_GET['id']) || isset($_POST['id'])) {
        if (isset($_GET['id'])) {
            $idFalsosMedicos = $_GET['id'];
        } else {
            $idFalsosMedicos = $_POST['id'];
        }
    } else {
        $resFalsosMedicos['clase'] = "alert alert-warning";
        $resFalsosMedicos['icono'] = "glyphicon glyphicon-exclamation-sign";
        $resFalsosMedicos['mensaje'] = "Datos mal ingresados";
        $continua = FALSE;
    }
}

switch ($accion) {
    case 1:
        $titulo = 'Nueva Denuncia de Falso Médico';
        $panel = 'panel-success';
        $textoBoton = 'Confirmar';
        $claseBoton = 'btn-success';
        $readOnly = '';
        break;

    case 2:
        $titulo = 'Eliminar Denuncia de Falso Médico';
        $panel = 'panel-danger';
        $textoBoton = 'Eliminar';
        $claseBoton = 'btn-danger';
        $readOnly = 'readonly=""';
        break;

    case 3:
        $titulo = 'Editar Denuncia de Falso Médico';
        $panel = 'panel-info';
        $claseBoton = 'btn-info';
        $textoBoton = 'Confimar';
        $readOnly = '';
        break;

    default:
        $titulo = 'Denuncia de Falso Médico - error de acceso';
        $panel = 'panel-default';
        $claseBoton = 'btn-default';
        $textoBoton = 'default';
        $readOnly = 'readonly=""';
        break;
}

?>
<div class="panel <?php echo $panel; ?>">
    <div class="panel-heading">
        <div class="row">
            <div class="col-md-9">
                <h4>Denuncia de Falsos Médicos</h4>
            </div>
            <div class="col-md-3 text-left">
                <form id="formColegiado" name="formColegiado" method="POST" onSubmit="" action="secretaria_falsosmedicos.php">
                    <button type="submit"  class="btn <?php echo $claseBoton ?>" >Volver a Denuncias</button>
                </form>
            </div>
        </div>
    </div>
    <div class="panel-body">
        <?php
    if (isset($_POST['mensaje'])) {
    ?>
       <div class="ocultarMensaje"> 
           <p class="<?php echo $_POST['clase'];?>"><?php echo $_POST['mensaje'];?></p>  
       </div>
     <?php
        $idFalsosMedicos = $_POST['id'];
        $apellido = $_POST['apellido'];
        $nombre = $_POST['nombre'];
        $nroDocumento = $_POST['nroDocumento'];
        $matricula = $_POST['matricula'];
        $origenMatricula = $_POST['origenMatricula'];
        $remitido = $_POST['remitido'];
        $observaciones = $_POST['observaciones'];
        $fechaDenuncia = $_POST['fechaDenuncia'];
    } else {
        if ($accion != 1) {
            $resFalsosMedicos = $falsosMedicosLogic->obtenerFalsosMedicosPorId($idFalsosMedicos);
            if ($resFalsosMedicos['estado']) {
                $falsosMedicos = $resFalsosMedicos['datos'];
                $apellido = $falsosMedicos['apellido'];
                $nombre = $falsosMedicos['nombre'];
                $nroDocumento = $falsosMedicos['nroDocumento'];
                $matricula = $falsosMedicos['matricula'];
                $origenMatricula = $falsosMedicos['origenMatricula'];
                $remitido = $falsosMedicos['remitido'];
                $observaciones = $falsosMedicos['observaciones'];
                $fechaDenuncia = $falsosMedicos['fechaDenuncia'];
            } else {
                $continua = FALSE;
            }
        } else {
            $idFalsosMedicos = '';
            $apellido = '';
            $nombre = '';
            $nroDocumento = '';
            $matricula = '';
            $origenMatricula = '';
            $remitido = '';
            $observaciones = '';
            $fechaDenuncia = '';
        }
    }
        if ($continua) {
        ?>
            <div class="row">
                <div class="col-md-12 text-center"><h4><b><?php echo $titulo; ?></b></h4></div>
            </div>
            <form id="datosDenuncia" autocomplete="off" name="datosDenuncia" method="POST" action="datosFalsosMedicos/abm_falsosmedicos.php">
                <div class="row">
                    <div class="col-md-3">
                        <label>Apellido: * </label>
                        <input class="form-control" type="text" style="text-transform:uppercase;" onkeyup="javascript:this.value=this.value.toUpperCase();"  id="apellido" name="apellido" value="<?php echo trim($apellido); ?>" required="" <?php echo $readOnly; ?>/>
                    </div>
                    <div class="col-md-3">
                        <label>Nombres: * </label>
                        <input class="form-control" type="text" style="text-transform:uppercase;" onkeyup="javascript:this.value=this.value.toUpperCase();"  id="nombre" name="nombre" value="<?php echo trim($nombre); ?>" required="" <?php echo $readOnly; ?>/>
                    </div>
                    <div class="col-md-2">
                        <label>Documento Nº: </label>
                        <input class="form-control" type="number" id="nroDocumento" name="nroDocumento" value="<?php echo $nroDocumento; ?>" <?php echo $readOnly; ?> />
                    </div>
                    <div class="col-md-2">
                        <label>Matrícula: </label>
                        <input class="form-control" type="number" id="matricula" name="matricula" value="<?php echo $matricula; ?>" <?php echo $readOnly; ?> />
                    </div>
                    <div class="col-md-2">
                        <label>Origen de la matrícula: </label>
                        <input class="form-control" type="text" id="origenMatricula" name="origenMatricula" value="<?php echo $origenMatricula; ?>" <?php echo $readOnly; ?>/>
                    </div>
                </div>
                <div class="row">&nbsp;</div>
                <div class="row">
                    <div class="col-md-3">
                        <label>Fecha de la Denuncia *</label>
                        <input type="date" class="form-control" id="fechaDenuncia" name="fechaDenuncia" value="<?php echo $fechaDenuncia;?>" required <?php echo $readOnly; ?>>
                    </div>
                    <div class="col-md-9">
                        <label>Remitido por *</label>
                        <input class="form-control" type="text" style="text-transform:uppercase;" onkeyup="javascript:this.value=this.value.toUpperCase();"  id="remitido" name="remitido" value="<?php echo $remitido; ?>" required="" <?php echo $readOnly; ?>/>
                    </div>
                </div>
                <div class="row">&nbsp;</div>
                <div class="row">
                    <div class="col-md-12">
                        <label>Observaciones </label>
                        <textarea class="form-control" name="observaciones" id="observaciones" rows="5" <?php echo $readOnly; ?> ><?php echo $observaciones; ?></textarea>
                    </div>
                </div>
                <div class="row">&nbsp;</div>
                <div class="row">
                    <div class="col-md-12 text-center">
                        <button type="submit"  class="btn <?php echo $claseBoton ?> btn-lg" ><?php echo $textoBoton; ?> </button>
                        <input type="hidden" name="accion" id="accion" value="<?php echo $accion; ?>" />
                        <input type="hidden" name="idFalsosMedicos" id="idFalsosMedicos" value="<?php echo $idFalsosMedicos; ?>" />
                    </div>
                </div>    
            </form>
        <?php
        } else {
        ?>
            <div class="col-md-12">
                <div class="<?php echo $resFalsosMedicos['clase']; ?>" role="alert">
                    <span class="<?php echo $resFalsosMedicos['icono']; ?>" aria-hidden="true"></span>
                    <span><strong><?php echo $resFalsosMedicos['mensaje']; ?></strong></span>
                </div>        
            </div>
        <?php 
        }
        ?>
    </div>    
</div>
<?php
require_once '../html/footer.php';
