<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/conection_pdo.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/cursos_pdo.php');
require_once ('../dataAccess/cajaDiariaLogic.php');
$cajaDiariaLogic = new cajaDiariaLogic();
require_once ('../dataAccess/usuarioLogic.php');

$idUsuario = $_SESSION['user_id'];
if (isset($_GET['idAsistente'])) {
    $idAsistente = $_GET['idAsistente'];
} else {
    if (isset($_POST['idAsistente'])) {
        $idAsistente = $_POST['idAsistente'];
    } else {
        $idAsistente = NULL;
    }
}
$continua = TRUE;
$resCajaDiaria = $cajaDiariaLogic->obtenerCajaAbierta();
if ($resCajaDiaria['estado']) {
    $idCajaDiaria = $resCajaDiaria['datos']['idCajaDiaria'];
} else {
    $continua = FALSE;
}

if ($continua) {

    if (isset($idAsistente)) {
        $idColegiado = NULL;
        $tituloCajaDiaria = "Cobranza de cursos";
        include_once 'encabezado_generar_recibo.php';

        $idResponsable = NULL;
        $idTipoCondonacion = NULL;
        $observaciones = NULL;
        //obtengo la deuda, inicializo los campos a mostrar
        $totalDeuda = 0;
        $cursos_pdo = new cursos_pdo();
        $resDeuda = $cursos_pdo->obtenerCuotasCursoAPagar($idAsistente);
        if ($resDeuda['estado']) {
            //inicializo los totales
            foreach ($resDeuda['datos'] as $row) {
                $totalDeuda += $row['importe'];
            }
        }
    ?>

    <div class="panel panel-info">
        <div class="panel-body">
        <div class="row">&nbsp;</div>
        <form id="datosRecibo" autocomplete="off" name="datosRecibo" method="POST" action="datosCajaDiaria\generar_recibo.php">
            <?php
            if ($totalDeuda > 0) {
            ?>
                <div class="row">
                    <div class="form-check">
                        <?php
                        if ($totalDeuda > 0) {
                        ?>
                            <h4><b class="text-center">Generar recibo de Cuotas de Cursos &nbsp;</b></h4>
                            <?php
                            $totalActualizado = 0;
                            foreach ($resDeuda['datos'] as $row) {
                                $importe = $row['importe'];
                                $idCursosAsistenteCuota = $row['idCursosAsistenteCuota'];
                                $cuota = $row['cuota'];
                                $fechaVencimiento = $row['fechaVencimiento'];
                                /*
                                if ($fechaVencimiento > date('Y-m-d')) {
                                    $checked = '';
                                } else {
                                    $checked = 'checked="checked"';
                                    $totalActualizado += $importe;
                                }
                                */
                            ?>
                            <div class="col-md-2">
                                <input class="form-check-input" name="generarRecibo[]" type="checkbox" <?php echo $checked; ?> 
                                    value="<?php echo $idCursosAsistenteCuota.'_'.$importe; ?>" 
                                    id="<?php echo $idCursosAsistenteCuota ?>" 
                                    onclick="cambiaTotalCuotas(<?php echo $importe ?>, <?php echo $totalActualizado; ?>, <?php echo $idCursosAsistenteCuota ?>)">
                                <label class="form-check-label" for="<?php echo $idCursosAsistenteCuota ?>">
                                  <?php echo $cuota.': $'.$importe ?>
                                </label>
                            </div>
                            <?php
                            }
                        }
                        ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <h4>Total deuda actualizada: 
                        <b><input type="text" name="totalActualizado" id="totalActualizado" value="<?php echo $totalActualizado; ?>" readonly=""></b>
                    </h4>
                </div>
                <?php 
                }
                ?>
            </div>
                    <?php
                    include 'cajadiaria_forma_pago.php'; 
                    ?>   
            
            <div class="row">&nbsp;</div>
            <div class="row">
                <div class="col-md-12 text-center" id="bloque_confirmar" style="display: none;">
                    <?php
                    if ($totalDeuda > 0) {
                    ?>
                    <h4 id="informe" style="display: none; color: #F8BB00; ">El recibo se generó con éxito.</h4>
                    <button type="submit" name='confirma' id='confirma' class="btn btn-success" onclick="show('confirma', 'informe')">Confirma Recibo</button>
                    <input type="hidden" name="idAsistente" id="idAsistente" value="<?php echo $idAsistente; ?>" />
                    <input type="hidden" name="tipoRecibo" id="tipoRecibo" value="CURSOS" />
                    <?php 
                    } else {
                    ?>
                        <h4 class="alert alert-warning">No registra deuda para generar Recibo.</h4>
                    <?php
                    }
                    ?>
                </div>
            </div>    
        </form>
        </div>
    </div>
    <?php
    } else {
        //debe seleccionar al colegiado    
    ?>
        <div class="row">
            <div class="col-md-12 text-center">
                <h4>Generar recibo de Cursos</h4>
                <h5>Seleccione al asistente</h5>
            </div>
        </div>
        <div class="row">&nbsp;</div>
        <div class="row">
            <form id="formColegiado" name="formColegiado" method="POST" onSubmit="" action="cajadiaria_cursos_recibo.php">
                <div class="row">
                    <div class="col-md-3" style="text-align: right;">
                        <label>Matr&iacute;cula o Apellido y Nombre *</label>
                    </div>
                    <div class="col-md-7">
                        <input class="form-control" autofocus autocomplete="OFF" type="text" name="colegiado_buscar" id="colegiado_buscar" placeholder="Ingrese Matrícula o Apellido del colegiao" required=""/>
                        <input type="hidden" name="idAsistente" id="idAsistente" required="" />
                    </div>
                    <div class="col-md-2">
                        <button type="submit"  class="btn btn-success">Confirma colegiado</button>
                        <input type="hidden" name="origen" id="origen" value="nuevo" />
                    </div>
                </div>
            </form>
        </div>
        <div class="row">&nbsp;</div>
        <div class="row text-center">
            <form id="formColegiado" name="formColegiado" method="POST" onSubmit="" action="cajadiaria.php">
                <button type="submit"  class="btn btn-info" >Volver a Caja Diaria</button>
            </form>
        </div>
    <?php
    }
} else {
?>
    <div class="row">&nbsp;</div>
    <div class="row">
        <div class="alert alert-warning">NO HAY CAJA ABIERTA, DEBE IR A CAJAS DIARIAS Y ABRIR PRIMERO UNA CAJA DEL DIA</div>
    </div>
    <div class="row">&nbsp;</div>
    <div class="row text-center">
        <form id="formColegiado" name="formColegiado" method="POST" onSubmit="" action="cajadiaria.php">
            <button type="submit"  class="btn btn-info" >Volver a Caja Diaria</button>
        </form>
    </div>
<?php      
}
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
                    url: 'asistente.php',
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
                $('#idAsistente').val(nameIdMap[item]);
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
    
    function cambiaTotalCuotas(importe, totalActualizado, idColegiadoDeudaAnualCuota){
        var totalActualizado = parseInt(document.getElementById('totalActualizado').value);
        var valor = parseInt(totalActualizado);
        var importe = parseInt(importe);

        if (document.getElementById(idColegiadoDeudaAnualCuota).checked)
        {
            var valor = totalActualizado + importe;
        } else {
            var valor = totalActualizado - importe;
        }

        if (valor > 0) {
            $('#bloque_forma_pago').fadeIn(); // Aparece con efecto
            $('#bloque_confirmar').fadeIn(); // Aparece con efecto
        } else {
            $('#bloque_forma_pago').fadeOut(); // Se oculta
            $('#bloque_confirmar').fadeOut(); // Se oculta
        }
        document.getElementById('totalActualizado').value = valor;
        document.getElementById('importeRecargo').value = 0;
        document.getElementById('totalConRecargo').value = valor;
        return valor;
    }

</script>