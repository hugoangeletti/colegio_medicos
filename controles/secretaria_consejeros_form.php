<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/colegiadoLogic.php');
require_once ('../dataAccess/colegiadoCargoLogic.php');

$continua = TRUE;
$mensaje = "";
if (isset($_GET['agregar'])) {
    $accion = 'AGREGAR';
} else {
    if (isset($_GET['editar'])) {
        $accion = 'EDITAR';
    } else {
        $accion = 'CONSULTAR';
    }
}
$colegiadoCargoLogic = new colegiadoCargoLogic();
if (isset($_GET['id']) && $_GET['id'] <> "") {
    $idColegiadoCargo = $_GET['id'];
    $resColegiado = $colegiadoCargoLogic->obtenerColegiadoCargoPorId($idColegiadoCargo);
    if ($resColegiado['estado']) {
        $colegiadoCargo = $resColegiado['datos'][0];
        $fechaDesde = $colegiadoCargo['fechaDesde'];
        $fechaHasta = $colegiadoCargo['fechaHasta'];
        $fechaMesaDesde = $colegiadoCargo['fechaMesaDesde'];
        $fechaMesaHasta = $colegiadoCargo['fechaMesaHasta'];
        //$apellidoNombre = trim($colegiadoCargo['apellido']).', '.trim($colegiadoCargo['nombre']);
        //$matricula = $colegiadoCargo['matricula'];
        $idColegiado = $colegiadoCargo['idColegiado'];
        $idCargoColegioSeleccionado = $colegiadoCargo['idCargoColegio'];
        if ($idCargoColegioSeleccionado >= 1 && $idCargoColegioSeleccionado <= 9) {
            $cargo_mesa = "display: block;";
        } else {
            $cargo_mesa = "display: none;";
        }
    } else {
        $continua = FALSE;
        $mensaje .= $resColegiado['mensaje'];
    } 
} else {
    if ($accion == 'AGREGAR') {
        $idColegiadoCargo = NULL;
        $cargo_mesa = "display: none;";
    } else {
        $continua = FALSE;
        $mensaje .= 'Falta idColegiadoCargo - ';
    }
}

$panel = 'panel-default';
switch ($accion) {
    case 'AGREGAR':
        $titulo = 'Nuevo Consejero';
        break;

    case 'BORRAR':
        $titulo = 'Eliminar Consejero';
        break;

    case 'EDITAR':
        $titulo = 'Editar Consejero';
        break;

    default:
        $titulo = 'Consejero';
        break;
}

if (isset($_POST['mensaje'])) {
?>
    <div class="ocultarMensaje"> 
        <p class="<?php echo $_POST['clase'];?>"><?php echo $_POST['mensaje'];?></p>  
    </div>
    <?php
    $apellidoNombre = $_POST['colegiado_buscar'];
    $idColegiado = $_POST['idColegiado'];
    $fechaDesde = $_POST['fechaDesde'];
    $fechaHasta = $_POST['fechaHasta'];
    $fechaMesaDesde = $_POST['fechaMesaDesde'];
    $fechaMesaHasta = $_POST['fechaMesaHasta'];   
    $idCargoColegioSeleccionado = $_POST['idCargoColegioSeleccionado'];
} else {
    if (isset($_POST['idColegiado']) && $_POST['idColegiado'] <> "") {
        $apellidoNombre = $_POST['colegiado_buscar'];
        $idColegiado = $_POST['idColegiado'];
    }
    if ($accion == 'AGREGAR') {
        $fechaDesde = NULL;
        $fechaHasta = NULL;
        $fechaMesaDesde = NULL;
        $fechaMesaHasta = NULL;
        $idCargoColegioSeleccionado = 11;
    }
}
?>
<div class="panel <?php echo $panel; ?>">
    <div class="panel-heading">
        <div class="row">
            <div class="col-md-9">
                <h4><?php echo $titulo; ?></h4>
            </div>
            <div class="col-md-3 text-left">
                <a href="secretaria_consejeros.php" class="btn btn-info">Volver a Consejeros </a>
            </div>
        </div>
    </div>
    <div class="panel-body">
    <?php
    if (!isset($_POST['idColegiado']) && isset($_GET['agregar'])) {
    ?>
        <div class="row">&nbsp;</div>
        <div class="row">
            <form id="formColegiado" name="formColegiado" method="POST" onSubmit="" action="secretaria_consejeros_form.php?agregar">
                <div class="row">
                    <div class="col-md-3" style="text-align: right;">
                        <label>Matr&iacute;cula o Apellido y Nombre *</label>
                    </div>
                    <div class="col-md-7">
                        <input class="form-control" autofocus autocomplete="OFF" type="text" name="colegiado_buscar" id="colegiado_buscar" placeholder="Ingrese Matrícula o Apellido del colegiado" required=""/>
                        <input type="hidden" name="idColegiado" id="idColegiado" required="" />
                    </div>
                    <div class="col-md-2">
                        <button type="submit"  class="btn btn-success">Confirma colegiado</button>
                    </div>
                </div>
            </form>
        </div>
    <?php 
    } else {
        $colegiadoLogic = new colegiadoLogic();
        $resColegiado = $colegiadoLogic->obtenerColegiadoPorId($idColegiado);
        if ($resColegiado['estado']) {
            $colegiado = $resColegiado['datos'];
            $matricula = $colegiado['matricula'];
            $apellidoNombre = $colegiado['apellido'].' '.$colegiado['nombre'];
        } else {
            $continua = FALSE;
            $mensaje .= $resColegiado['mensaje'];
        }
        if ($accion == 'AGREGAR') {
            //si es agregar, verificamos si ya existe el colegiado activo como consejero
            $resColegiadoCargo = $colegiadoCargoLogic->obtenerCargoColegioPorIdColegiado($idColegiado);
            if ($resColegiadoCargo['estado']) {
                $continua = FALSE;
                $mensaje .= "YA EXISTE EL CONSEJERO EN LA LISTA.";
            }
        }

        if ($continua) {
        ?>
            <div class="row">
                <div class="col-md-2">
                    <label for="matricula">Matrícula: </label>
                    <input type="text" class="form-control" name="matricula" readonly value="<?php echo $matricula; ?>" />
                </div>
                <div class="col-md-4">
                    <label for="apellidoNombre">Apellido y Nombre: </label>
                    <input type="text" class="form-control" name="apellidoNombre" readonly value="<?php echo $apellidoNombre; ?>" />
                </div>
            </div>
            <div class="row">
                <div class="col-md-6"><hr></div>
            </div>
            <form id="datosConsejero" autocomplete="off" name="datosConsejero" method="POST" action="datosConsejero/abm_consejero.php">
                <div class="row">
                    <div class="col-md-6 text-center">
                        <h4><b>Vigencia en el Consejo</b></h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3">
                        <label for="fechaDesde">Fecha desde *</label>
                        <input type="date" class="form-control" id="fechaDesde" name="fechaDesde" value="<?php echo $fechaDesde;?>" required>
                    </div>
                    <div class="col-md-3">
                        <label for="fechaHasta">Fecha hasta *</label>
                        <input type="date" class="form-control" id="fechaHasta" name="fechaHasta" value="<?php echo $fechaHasta;?>">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6"><hr></div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <label for="idCargoColegioSeleccionado">Cargo *</label>
                        <?php 
                        $resCargos = $colegiadoCargoLogic->obtenerCargosColegio();
                        if ($resCargos['estado']) {
                        ?>
                            <select class="form-control" id="idCargoColegioSeleccionado" name="idCargoColegioSeleccionado" required="" onChange="habilitar(this)">
                            <option value="">Seleccione cargo</option>
                            <?php
                            foreach ($resCargos['datos'] as $dato) {
                                $idCargoColegio = $dato['idCargoColegio'];
                                $nombreCargo = $dato['nombreCargo'];
                                ?>
                                <option value="<?php echo $idCargoColegio; ?>" <?php if ($idCargoColegio == $idCargoColegioSeleccionado) { echo 'selected'; } ?>><?php echo $nombreCargo; ?></option>
                            <?php
                            }
                            ?>
                            </select>
                        <?php 
                        } else {
                            echo $resCargos['mensaje'];
                        }
                        ?>
                    </div>
                </div>
                <div class="row">&nbsp;</div>
                <div id="cargo_mesa" style="<?php echo $cargo_mesa; ?>">
                    <div class="row">
                        <!--<div class="col-md-6">
                            <label>Cargo Mesa *</label>
                            <input class="form-control" type="text" id="cargo" name="cargo" value="<?php echo $colegiadoCargo['nombreCargo']; ?>" readonly=""/>
                        </div>-->
                        <div class="col-md-6 text-center">
                            <h4><b>Cargo en Mesa Directiva</b></h4>
                        </div>
                    </div>
                    <div class="row">&nbsp;</div>
                    <div class="row">
                        <div class="col-md-3">
                            <label for="fechaMesaDesde">Fecha desde *</label>
                            <input type="date" class="form-control" id="fechaMesaDesde" name="fechaMesaDesde" value="<?php echo $fechaMesaDesde;?>">
                        </div>
                        <div class="col-md-3">
                            <label for="fechaMesaHasta">Fecha hasta </label>
                            <input type="date" class="form-control" id="fechaMesaHasta" name="fechaMesaHasta" value="<?php echo $fechaMesaHasta;?>">
                        </div>
                    </div>
                </div> 
                <div class="row">&nbsp;</div>
                <div class="row">
                    <div class="col-md-6 text-center">
                        <button type="submit"  class="btn btn-success btn-lg" >Confirma </button>
                        <input type="hidden" name="accion" id="accion" value="<?php echo $accion; ?>" />
                        <input type="hidden" name="idColegiado" id="idColegiado" value="<?php echo $idColegiado; ?>" />
                        <input type="hidden" name="idColegiadoCargo" id="idColegiadoCargo" value="<?php echo $idColegiadoCargo; ?>" />
                        <input type="hidden" name="idCargoColegio" id="idCargoColegio" value="<?php echo $idCargoColegio; ?>" />
                    </div>
                </div>    
            </form>
        <?php
        } else {
        ?>
            <div class="row alert alert-danger">
                <div class="col-md-4"><?php echo $mensaje; ?></div>
                <div class="col-md-4">
                    <a href="secretaria_consejeros_form.php?agregar" class="btn btn-danger btn-sm"> Reintente la carga </a>
                </div>
            </div>
        <?php
        } 
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
                    url: 'colegiado.php?activos=SI',
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
   
function habilitar(sel) {
    if (sel.value == "11"){
        divT = document.getElementById("cargo_mesa");
        divT.style.display = "none";
        document.getElementById("fechaMesaDesde").required = false;
        document.getElementById("fechaMesaHasta").required = false;
    }else{
        divT = document.getElementById("cargo_mesa");
        divT.style.display = "";
        document.getElementById("fechaMesaDesde").required = true;
        document.getElementById("fechaMesaHasta").required = true;
    }
}

</script>