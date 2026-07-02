<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/remitenteLogic.php');
require_once ('../dataAccess/colegiadoLogic.php');
require_once ('../dataAccess/mesaEntradaLogic.php');

$continua = TRUE;
$mensaje = "";
$idColegiado = NULL;

if (isset($_GET['tipo']) && $_GET['tipo'] <> "") {
    $idTipoMesaEntrada = $_GET['tipo'];
    $mesaEntradaLogic = new mesaEntradaLogic();
    $resTipoMesaEntrada = $mesaEntradaLogic->obtenerTipoMesaEntradaPorId($idTipoMesaEntrada);
    if ($resTipoMesaEntrada['estado']) {
        $tipoMesaEntrada = $resTipoMesaEntrada['datos'];
        $titulo = $tipoMesaEntrada['nombre'];

        switch ($idTipoMesaEntrada) {
            case '1':
                $linkMesaEntrada = "mesa_entrada_movimientos_matriculares.php";
                break;
            
            case '4':
                $linkMesaEntrada = "mesa_entrada_habilitacion_consultorio.php";
                break;
            
            case '5':
                $linkMesaEntrada = "mesa_entrada_matricula_j.php";
                break;
            
            case '7':
                $linkMesaEntrada = "mesa_entrada_autoprescripcion.php";
                break;
            
            case '8':
                $linkMesaEntrada = "mesa_entrada_anular_movimiento.php";
                break;
            
            case '9':
                $linkMesaEntrada = "mesa_entrada_denuncia.php";
                break;
            
            case '10':
                $linkMesaEntrada = "mesa_entrada_entrega.php";
                break;
            
            default:
                $linkMesaEntrada = "#";
                break;
        }
        
    } else {
        $continua = FALSE;
        $mensaje .= $resTipoMesaEntrada['mensaje'];    
    }
} else {
    $continua = FALSE;
    $mensaje .= "Ingreso incorrecto - falta tipo mesa de entrada";
}

if ($_POST) {
    //ingresa por colegiado o remitente
    if (isset($_POST['idColegiado']) && $_POST['idColegiado'] <> "") {
        $idColegiado = $_POST['idColegiado'];
        $esColegiado = 'S';
        $colegiadoLogic = new colegiadoLogic();
        $resColegiado = $colegiadoLogic->obtenerColegiadoPorId($idColegiado);
        if ($resColegiado['estado']) {
            $colegiado = $resColegiado['datos'];
            $colegiado_buscar = trim($colegiado['apellido']).' '.trim($colegiado['nombre']);
        } else {
            $continua = FALSE;
            $mensaje .= $resColegiado['mensaje'];
        }
    } else {
        $continua = FALSE;
        $mensaje .= 'Mal ingresado, falta Colegiado o Remitente';
    }
} else {
    $idColegiado = NULL;
    $colegiado_buscar = NULL;
}

if (isset($_POST['mensaje'])) {
?>
    <div id="divMensaje"> 
        <p class="<?php echo $_POST['clase'];?>"><?php echo $_POST['mensaje'];?></p>  
    </div>
    <?php    
    if (isset($_POST['idColegiado']) && $_POST['idColegiado'] <> "") {
        $idColegiado = $_POST['idColegiado'];
    } else {
        $idColegiado = NULL;
    }
    if (isset($_POST['colegiado_buscar']) && $_POST['colegiado_buscar'] <> "") {
        $colegiado_buscar = $_POST['colegiado_buscar'];
    } else {
        $colegiado_buscar = NULL;
    }
}   
?>
<div class="panel panel-default">
    <div class="panel-heading">
        <div class="row">
            <div class="col-xs-9">
                <h4><b>Mesa de Entradas: <?php echo $titulo; ?></b></h4>
            </div>
            <div class="col-xs-3 text-right">
                <a href="mesa_entrada_listado.php" class="btn btn-info">Volver</a>
            </div>
        </div>
    </div>
    <div class="panel-body">
        <?php 
        if ($continua) {
        ?>  
            <form id="formMesa" name="formMesa" method="POST" onSubmit="" action="<?php echo $linkMesaEntrada; ?>">
                <div class="row">
                    <div class="col-md-5" id="esUnColegiado">
                        <label for="colegiado_buscar">Colegiado *</label>
                        <input class="form-control" autofocus autocomplete="OFF" type="text" name="colegiado_buscar" id="colegiado_buscar" placeholder="Ingrese Matrícula o Apellido del colegiado" value="<?php echo $colegiado_buscar; ?>" />
                        <input type="hidden" name="idColegiado" id="idColegiado" />
                    </div>
                    <div class="col-md-2">
                        <br>
                        <button type="submit" class="btn btn-success" >Confirma</button>
                    </div>
                </div>  
            </form>   
        <?php
        } else {
        ?>
            <div class="row">&nbsp;</div>
            <div class="row">
                <div class="col-md-12">
                    <div class="<?php echo $clase; ?>" role="alert">
                        <span><strong><?php echo $mensaje; ?></strong></span>
                    </div>
                </div>
            </div>
        <?php
        }
        ?>
    </div>
</div>
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
