<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/mesaEntradaLogic.php');
require_once ('../dataAccess/mesaEntradaEspecialistaLogic.php');
require_once ('../dataAccess/funcionesPhp.php');
?>
<script>
    $(document).ready(function () {
        $('#tablaOrdenada').DataTable({
            "iDisplayLength":25,
            "order": [[ 0, "desc" ]],
            "language": {
                "url": "../public/lang/esp.lang"
            },
            "bPaginate": true,
            "bLengthChange": true,
            "bFilter": true,
            dom: 'T<"clear">lfrtip'
        });
    });
</script>

<?php
$accedePor = "FECHA";
if (isset($_POST['mensaje']))
{
    $conPermiso = TRUE;
 ?>
   <div class="ocultarMensaje"> 
   <p class="<?php echo $_POST['clase'];?>"><?php echo $_POST['mensaje'];?></p>  
   </div>
 <?php    
}

?> 
<div class="panel panel-default">
    <div class="panel-heading"><h4><b>Notas Ingresadas</b></h4></div>
    <div class="panel-body">
        <?php
        $mesaEntradaLogic = new mesaEntradaLogic();
        if (isset($_POST['anioMesa']) && $_POST['anioMesa'] <> "") {
            $anioMesa = $_POST['anioMesa'];
        } else {
            $anioMesa = date('Y');
        }
        if (isset($_POST['idColegiado']) && $_POST['idColegiado'] <> "") {
            $idColegiado = $_POST['idColegiado'];
            $accedePor = "COLEGIADO";
        } else {
            $idColegiado = NULL;
        }
        if (isset($_POST['idRemitente']) && $_POST['idRemitente'] <> "") {
            $idRemitente = $_POST['idRemitente'];
            $accedePor = "OTRO";
        } else {
            $idRemitente = NULL;
        }
        ?>
        <div class="row">
            <form method="POST" action="mesa_entrada_notas_listado.php">
                <div class="col-md-2">
                    <label for="fechaIngreso">Año</label>
                    <select class="form-control" id="anioMesa" name="anioMesa" required onChange="this.form.submit()">
                        <option value="" selected>Seleccione</option>
                        <?php
                        $anio = date('Y');
                        while ($anio >= 2013) {
                        ?>
                            <option value="<?php echo $anio; ?>" <?php if($anio == $anioMesa) { echo 'selected'; } ?>><?php echo $anio; ?></option>
                        <?php
                            $anio--;
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-3" id="esUnColegiado">
                    <label for="colegiado_buscar">Buscar colegiado</label>
                    <input class="form-control" autofocus autocomplete="OFF" type="text" name="colegiado_buscar" id="colegiado_buscar" placeholder="Ingrese Matrícula o Apellido del colegiado" />
                    <input type="hidden" name="idColegiado" id="idColegiado" />
                </div>
                <div class="col-md-3" id="noEsUnColegiado">
                    <label for="remitente_buscar">Buscar remitente</label>
                    <input class="form-control" autofocus autocomplete="OFF" type="text" name="remitente_buscar" id="remitente_buscar" placeholder="Buscar Remitente"/>
                    <input type="hidden" name="idRemitente" id="idRemitente" />
                </div>
                <div class="col-md-3" id="esTema">
                    <br>
                    <button type="submit"  class="btn btn-info " >Buscar</button>
                </div>
                <div class="col-md-1">
                    <br>
                    <a href="mesa_entrada_notas_oficios.php?notas" class="btn btn-info">Agregar Nota</a>
                </div>                
            </form>
        </div>
        <div class="row">&nbsp;</div>            
        <?php
        $resMesaEntrada = $mesaEntradaLogic->obtenerMesaEntradaNotasPorAnio($anioMesa, $idColegiado, $idRemitente);
        if ($resMesaEntrada['estado']) {
        ?>    
            <table id="tablaOrdenada" class="display">
                <thead>
                    <tr>
                        <th>Id</th>
                        <th>Fecha Ingreso</th>
                        <th style="text-align: right;">Matrícula</th>
                        <th>Apellido y Nombre / Remitente</th>
                        <th>Tema</th>
                        <th style="width: 200px; text-align: center;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($resMesaEntrada['datos'] as $dato) {
                        $idMesaEntrada = $dato['idMesaEntrada'];
                        $fechaIngreso = $dato['fechaIngreso'];
                        $matricula = $dato['matricula'];
                        $idRemitente = $dato['idRemitente'];
                        $nombreRemitente = $dato['nombreRemitente'];
                        $tema = $dato['tema'];
                        ?>
                        <tr>
                            <td><?php echo $idMesaEntrada;?></td>
                            <td><?php echo cambiarFechaFormatoParaMostrar($fechaIngreso);?></td>
                            <td style="text-align: right;"><?php echo $matricula;?></td>
                            <td><?php echo $nombreRemitente;?></td>
                            <td><?php echo $tema;?></td>
                            <td>
                                <a href="mesa_entrada_imprimir.php?id=<?php echo $idMesaEntrada; ?>&ingreso=notas" class="btn btn-info">Imprimir</a>
                                <a href="datosMesaEntrada\mesa_entrada.php?borrar&id=<?php echo $idMesaEntrada; ?>&ingreso=notas" class="btn btn-info" onclick="return confirmaAnular()">Borrar</a>
                            </td>
                        </tr>
                    <?php
                    }
                    ?>
        	   </tbody>
            </table>
        <?php
        } else {
        ?>
            <div class="<?php echo $resMesaEntrada['clase']; ?>" role="alert">
                <span class="<?php echo $resMesaEntrada['icono']; ?>" ></span>
                <span><strong><?php echo $resMesaEntrada['mensaje']; ?></strong></span>
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

    $(function(){
        var nameIdMap = {};
        $('#remitente_buscar').typeahead({ 
                source: function (query, process) {
                return $.ajax({
                    dataType: "json",
                    url: 'remitente.php',
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
                $('#idRemitente').val(nameIdMap[item]);
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

    
    var lastSelected;
    $(function () {
        //if you have any radio selected by default
        lastSelected = $('[name="esColegiado"]:checked').val();
    });
    $(document).on('click', '[name="esColegiado"]', function () {
        if (lastSelected != $(this).val() && typeof lastSelected != "undefined") {
            //if (x.style.display === "none") {
            var x = document.getElementById("esUnColegiado");
            var y = document.getElementById("noEsUnColegiado");
            var z = document.getElementById("incluyeMovimientos");
            if (lastSelected != 'S') {
                x.style.display = "block";
                y.style.display = "none";
                z.style.display = "none";
            } else {
                x.style.display = "none";
                y.style.display = "block";
                z.style.display = "block";
            }
        }
        lastSelected = $(this).val();
    });

    function confirmaAnular()
    {
        if(confirm('¿Estas seguro de ANULAR este registro?'))
            return true;
        else
            return false;
    }
    
</script>
