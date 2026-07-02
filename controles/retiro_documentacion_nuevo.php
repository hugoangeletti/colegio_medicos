<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/colegiadoLogic.php');
require_once ('../dataAccess/retiroDocumentacionLogic.php');
$retiroDocumentacionLogic = new retiroDocumentacionLogic();

$accion = $_POST['accion'];

if (isset($_POST['mensaje'])) {
    $idTipoDocumentacionRetiro =$_POST['idTipoDocumentacionRetiro'];
    $observacion = $_POST['observacion'];
    $idColegiado = $_POST['idColegiado'];
    $colegiado_buscar = $_POST['colegiado_buscar'];
    $estadoRetiro = $_POST['estadoRetiro'];
?>
    <div class="ocultarMensaje"> 
        <p class="<?php echo $_POST['clase'];?>"><?php echo $_POST['mensaje'];?></p>  
    </div>
 <?php
} else {
    if (isset($_POST['idRetiroDocumentacion']) && $_POST['idRetiroDocumentacion']) {
        $idRetiroDocumentacion = $_POST['idRetiroDocumentacion'];
        $resRetiro = $retiroDocumentacionLogic->obtenerRetiroDocumentacionPorId($idRetiroDocumentacion);
        if ($resRetiro['estado']) {
            $retiro = $resRetiro['datos'];
            $idColegiado = $retiro['idColegiado'];
            $matricula = $retiro['matricula'];
            $colegiado_buscar = $matricula.' - '.trim($retiro['apellidoNombre'])." (DNI ".$retiro['numeroDocumento'].")";
            $observacion = $retiro['observacion'];
            $estadoRetiro = $retiro['estadoRetiro'];
            $idTipoDocumentacionRetiro = $retiro['idTipoDocumentacionRetiro'];
        }
    } else {
        $idTipoDocumentacionRetiro = NULL;
        $observacion = NULL;
        $idColegiado = NULL;
        $colegiado_buscar = NULL;
        $estadoRetiro = 'A';
    }
}
?>

<div class="panel panel-info">
    <div class="panel-heading">
        <div class="row">
            <div class="col-md-9">
                <h4>Alta de documentación a retirar</h4>
            </div>
            <div class="col-md-3 text-left">
                <form id="formColegiado" name="formColegiado" method="POST" onSubmit="" action="retiro_documentacion.php">
                    <button type="submit"  class="btn btn-info" >Volver a Retiros de documentación</button>
                </form>
            </div>
        </div>
    </div>
    <div class="panel-body">
        <div class="row">&nbsp;</div>
        <form id="formColegiado" name="formColegiado" method="POST" onSubmit="" action="datosRetiro/abm_retiro_documentacion.php">
            <div class="row">
                <div class="col-md-6">
                    <label>Matr&iacute;cula o Apellido y Nombre *</label>
                    <input class="form-control" autofocus autocomplete="OFF" type="text" name="colegiado_buscar" id="colegiado_buscar" placeholder="Ingrese Matrícula o Apellido del colegiado" value="<?php echo $colegiado_buscar ?>" required=""/>
                    <input type="hidden" name="idColegiado" id="idColegiado" value="<?php echo $idColegiado; ?>" required="" />
                </div>
                <div class="col-md-6">
                    <label>Tipo de documentación *</label>
                    <select class="form-control" id="idTipoDocumentacionRetiro" name="idTipoDocumentacionRetiro" required="" >
                        <?php
                        $resTipoDocRetiro = $retiroDocumentacionLogic->obtenerTiposDocumentacion();
                        if ($resTipoDocRetiro['estado']) {
                            ?>
                            <option value="">Seleccione Tipo de Documentación</option>
                            <?php
                            foreach ($resTipoDocRetiro['datos'] as $row) {
                            ?>
                                <option value="<?php echo $row['id'] ?>" <?php if($idTipoDocumentacionRetiro == $row['id']) { echo 'selected'; } ?>><?php echo $row['nombre'] ?></option>
                            <?php
                            }
                        }
                        ?>
                    </select>
                </div>
            </div>

            <div class="row">&nbsp;</div>
            <div class="row">
                <div class="col-md-12">
                    <label>Observaciones </label>
                    <textarea class="form-control" style="text-transform:uppercase;" onkeyup="javascript:this.value=this.value.toUpperCase();" name="observacion" id="observacion" rows="5" ><?php echo $observacion; ?></textarea>
                </div>
            </div>    

            <div class="row">&nbsp;</div>
            <div class="row">
                <div class="col-md-12 text-center">
                    <button type="submit" name='confirma' id='confirma' class="btn btn-primary" onclick="show('confirma', 'informe')">Confirma </button>
                    <input type="hidden" name="estadoRetiro" id="estadoRetiro" value="<?php echo $estadoRetiro; ?>" />
                    <input type="hidden" name="idRetiroDocumentacion" id="idRetiroDocumentacion" value="<?php echo $idRetiroDocumentacion; ?>" />
                    <input type="hidden" name="accion" id="accion" value="<?php echo $accion; ?>" />
                </div>
            </div>    
        </form>
    </div>
</div>
<div class="row">&nbsp;</div>
<?php
require_once '../html/footer.php';
?>
<!--AUTOCOMLETE-->
<script src="../public/js/bootstrap3-typeahead.js"></script>    
<script language="JavaScript">
    $(function(){
        var nameIdMap = {};
        $('#colegiado_buscar').typeahead({ 
                source: function (query, process) {
                return $.ajax({
                    dataType: "json",
                    url: 'colegiado.php',
                    data: {query: query},
                    type: 'POST',
                    success: function (json) {
                        process(getOptionsFromJson(json.data));
                    }
                });
            },
           
            minLength: 3,
            //maxItem:15,
            
            updater: function (item) {
                $('#idColegiado').val(nameIdMap[item]);
                return item;
            }
        });
        function getOptionsFromJson(json) {
             
            $.each(json, function (i, v) {
                //console.log(v);
                nameIdMap[v.nombre] = v.id;
            });
            return $.map(json, function (n, i) {
                return n.nombre;
            });
        }
    });  
    
</script>