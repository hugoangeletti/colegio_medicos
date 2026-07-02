<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/colegiadoLogic.php');
require_once ('../dataAccess/colegiadoDeudaAnualLogic.php');
require_once ('../dataAccess/eleccionesLogic.php');
require_once ('../dataAccess/eleccionesLocalidadesLogic.php');
require_once ('../dataAccess/eleccionesLocalidadesListasLogic.php');
$eleccionesLocalidadesListasLogic = new eleccionesLocalidadesListasLogic();
require_once ('../dataAccess/eleccionesLocalidadesIntegrantesLogic.php');
$eleccionesLocalidadesIntegrantesLogic = new eleccionesLocalidadesIntegrantesLogic();
?>
        <script>
            $(document).ready(function () {
                $('#tablaOrdenada').DataTable({
                    "iDisplayLength":10,
                    "language": {
                        "url": "../public/lang/esp.lang"
                    },
                    dom: 'T<"clear">lfrtip',
                    tableTools: {
                       "sSwfPath": "../public/swf/copy_csv_xls_pdf.swf", 
                       "aButtons": [
                            {
                                "sExtends": "pdf",
                                "mColumns" : [0, 1, 2, 3, 4],
//                                "oSelectorOpts": {
//                                    page: 'current'
//                                }
                                "sTitle": "Listado de Elecciones",
                                "sPdfOrientation": "portrait",
                                "sFileName": "listado_de_elecciones.pdf"
//                              "sPdfOrientation": "landscape",
//                              "sPdfSize": "letter",  ('A[3-4]', 'letter', 'legal' or 'tabloid')
                            }
                            
                    ]
                    }
                });
            });
            
   
</script>

<?php
$continua = TRUE;
if (isset($_POST['idElecciones']) && isset($_POST['idEleccionesLocalidad']) && isset($_POST['idEleccionesLocalidadLista'])){
    $idElecciones = $_POST['idElecciones'];
    $idEleccionesLocalidad = $_POST['idEleccionesLocalidad'];
    $idEleccionesLocalidadLista = $_POST['idEleccionesLocalidadLista'];

    $tituloElecciones = "";
    $eleccionesLogic = new elecciones();
    $resElecciones = $eleccionesLogic->obtenerEleccionesPorId($idElecciones);
    if ($resElecciones['estado']) {
        $elecciones = $resElecciones['datos'];
        $tituloElecciones = $elecciones['detalle'];
        
        $eleccionesLocalidadesLogic = new eleccionesLocalidades();
        $resLocalidades = $eleccionesLocalidadesLogic->obtenerEleccionesLocalidadPorId($idEleccionesLocalidad);
        if ($resLocalidades['estado']) {
            $localidad = $resLocalidades['datos'];
            $tituloElecciones = $tituloElecciones.' - Localidad: '.$localidad['localidadDetalle'];
            $cantidadDelegados = $localidad['cantDelegados'];
            
            $resLista = $eleccionesLocalidadesListasLogic->obtenerEleccionesLocalidadListaPorId($idEleccionesLocalidadLista);
            if ($resLista['estado']) {
                $lista = $resLista['datos'];
                $tituloElecciones = $tituloElecciones.' - Lista: '.$lista['nombre'];
            } else {
                $mensaje = $resLista['mensaje'];
                $clase = $resLista['clase'];
                $icono = $resLista['icono'];
                $continua = FALSE;
            }
        } else {
            $mensaje = $resLocalidades['mensaje'];
            $clase = $resLocalidades['clase'];
            $icono = $resLocalidades['icono'];
            $continua = FALSE;
        }
    } else {
        $mensaje = $resElecciones['mensaje'];
        $clase = $resElecciones['clase'];
        $icono = $resElecciones['icono'];
        $continua = FALSE;
    }
} else {
    $mensaje = 'Acceso erroneo';
    $clase = 'alert alert-error';
    $icono = 'glyphicon glyphicon-remove';
    $continua = FALSE;
}
if (isset($_POST['mensaje']))
{
 ?>
   <div class="ocultarMensaje"> 
   <p class="<?php echo $_POST['tipomensaje'];?>"><?php echo $_POST['mensaje'];?></p>  
   </div>
 <?php    
}

if ($continua) {
    $selectTitular = FALSE;
    $selectSuplente = FALSE;
    $cantTitulares = NULL;
    $resTitulares = $eleccionesLocalidadesIntegrantesLogic->obtenerCantidadIntegrantesPorCargo($idEleccionesLocalidadLista, 'T');
    $resSuplentes = $eleccionesLocalidadesIntegrantesLogic->obtenerCantidadIntegrantesPorCargo($idEleccionesLocalidadLista, 'S');
    if ($resTitulares['estado']) {
        $cantTitulares = $resTitulares['cantidad'];
        if ($cantTitulares < $cantidadDelegados) {
            $selectTitular = TRUE;
        }
    }

    $cantSuplentes = NULL;
    if ($resSuplentes['estado']) {
        $cantSuplentes = $resSuplentes['cantidad']; 
        if (!$selectTitular) {
            if ($cantSuplentes < $cantidadDelegados) {
                $selectSuplente = TRUE;
            }
        }
    }
    ?>
    <div class="panel panel-default">
    <div class="panel-heading"><h4><b><?php echo $tituloElecciones; ?></b></h4></div>
    <div class="panel-body">
        <div class="row">
            <div class="col-md-12">
                <?php 
                if ($cantTitulares < $cantidadDelegados || $cantSuplentes < $cantidadDelegados) { 
                ?>
                    <form id="formElecciones" name="formElecciones" method="POST" onSubmit="" action="datosElecciones\abm_elecciones_integrantes.php">
                        <div class="row">
                            <div class="col-md-5">
                                <b>Matr&iacute;cula / Apellido y Nombre *</b>  
                                <input class="form-control" autofocus autocomplete="OFF" type="text" name="colegiado_buscar" id="colegiado_buscar" placeholder="Ingrese Matrícula o Apellido del colegiao" required=""/>
                                <input type="hidden" name="idColegiado" id="idColegiado" required="" />
                            </div>
                            <div class="col-md-4">
                                <b>Cargo *</b>  
                                <select class="form-control" id="cargo" name="cargo" required="">
                                    <?php
                                    if ($selectTitular) {
                                    ?>
                                        <option value="T" <?php if($selectTitular) { echo 'selected'; } ?>>Titular</option>
                                    <?php
                                    }
                                    if ($selectSuplente) {
                                    ?>
                                        <option value="S" <?php if($selectSuplente) { echo 'selected'; } ?>>Suplente</option>
                                    <?php
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <b>Orden </b>
                                <input class="form-control" type="number" name="orden" id="orden" value="<?php echo $orden; ?>"/>
                            </div>
                            <div class="col-md-1">
                                <b>&nbsp;</b>
                                <button type="submit" class="btn btn-success " >Confirma</button>
                            </div>
                        </div>  

                        <input type="hidden" name="idElecciones" id="idElecciones" value="<?php echo $idElecciones; ?>" />
                        <input type="hidden" name="idEleccionesLocalidad" id="idEleccionesLocalidad" value="<?php echo $idEleccionesLocalidad; ?>" />
                        <input type="hidden" name="idEleccionesLocalidadLista" id="idEleccionesLocalidadLista" value="<?php echo $idEleccionesLocalidadLista; ?>" />
                        <input type="hidden" name="accion" id="accion" value="1" />
                    </form>   
                <?php
                } else {
                ?>
                    <div style="color: #81CEF9;"><?php echo 'Ya están los integrantes cargados.'; ?></div>
                <?php
                }
                ?>
                <b style="color: red;">Los campos marcados en ROJO indican que el matriculado no est&aacute; en condiciones para integrar la lista.</b>
            </div>
<!--            <div class="col-xs-9">&nbsp;</div>
            <div class="col-xs-3">
                <form method="POST" action="elecciones_integrantes_form.php">
                    <div align="right">
                        <button type="submit" class="btn btn-success btn-lg">Nuevo Integrante</button>
                        <input type="hidden" id="idElecciones" name="idElecciones" value="<?php echo $idElecciones; ?>">
                        <input type="hidden" id="idEleccionesLocalidad" name="idEleccionesLocalidad" value="<?php echo $idEleccionesLocalidad; ?>">
                        <input type="hidden" id="idEleccionesLocalidadLista" name="idEleccionesLocalidadLista" value="<?php echo $idEleccionesLocalidadLista; ?>">
                        <input type="hidden" id="accion" name="accion" value="1">
                    </div>
                </form>
            </div>-->
        </div>        
        <div class="row">&nbsp;</div>
        <?php
        $resIntegrantes = $eleccionesLocalidadesIntegrantesLogic->obtenerIntegrantesPorIdEleccionesLocalidadLista($idEleccionesLocalidadLista);   
        if ($resIntegrantes['estado']){
        ?>
        <table style="width: 100%">
                <thead>
                    <tr>
                        <th>Orden</th>
                        <th>Matr&iacute;cula</th>
                        <th>Apellido y Nombre</th>
                        <th>Cargo</th>
                        <th>Estado Matricular</th>
                        <th>Estado Tesorería</th>
                        <th>Antigüedad</th>
                        <th>Localidad</th>
                        <th style="width: 50px">Editar</th>
                        <th style="width: 50px">Borrar</th>
                        <th style="display: none;">Id</th>
                    </tr>
                </thead>
            <tbody>
              <?php
                  foreach ($resIntegrantes['datos'] as $dato) 
                  {
                      $idEleccionesLocalidadListaIntegrante = $dato['idEleccionesLocalidadIntegrante'];
                      $matricula = $dato['matricula'];
                      $apellidoNombre = $dato['apellidoNombre'];
                      $cargo = $dato['cargo'];
                      $orden = $dato['orden'];
                      $estado = $dato['estado'];
                      
                      $resEnOtraLista = $eleccionesLocalidadesIntegrantesLogic->matriculaExisteEnLista($matricula, $idEleccionesLocalidadLista);
                      if ($resEnOtraLista['estado']) {
                          $otraLista = '<br>'.$resEnOtraLista['otraLista'];
                          $estilo = ' bgcolor="#D98880"';
                      } else {
                          $otraLista = '';
                          $estilo = '';
                      }
                  ?>
                    <tr <?php echo $estilo; ?>>
                        <td style="text-align: center;"><?php echo $orden;?></td>
			<td><?php echo $matricula;?></td>
			<td><?php echo $apellidoNombre.$otraLista;?></td>
                        <td><?php                                
                            switch ($cargo) {
                                case 'T':
                                    echo 'Titular';
                                    break;

                                case 'S':
                                    echo 'Suplente';
                                    break;

                                default:
                                    echo 'Sin detalle';
                                    break;
                            }?></td>
                            <?php
                            $resObservaciones = $eleccionesLocalidadesIntegrantesLogic->obtenerObservacionesIntegrante($idEleccionesLocalidadListaIntegrante);
                            if ($resObservaciones['estado']) {
                                $observaciones = $resObservaciones['datos'];
                                ?>
                                <td><?php
                                    $estilo = ' style="color: red;"';
                                    switch ($observaciones['estadoMatricular']) {
                                        case 'A':
                                            $estado = 'Activo';
                                            $estilo = ' style="color: green;"';
                                            break;
                                        
                                        case 'C':
                                            $estado = 'Cancelado';
                                            break;

                                        case 'J':
                                            $estado = 'Jubilado';
                                            break;
                                        
                                        case 'F':
                                            $estado = 'Fallecido';
                                            break;
                                        
                                        case 'I':
                                            $estado = 'Inscripto';
                                            break;
                                        
                                        default:
                                            $estado = 'Sin dato';
                                            break;
                                    }
                                    ?>
                                    <div <?php echo $estilo; ?>><?php echo $estado; ?></div>
                                </td>
                                <td><?php
                                    $estilo = ' style="color: red;"';
                                    if ($observaciones['estadoTesoreria'] == 'Al día') {
                                        $estilo = ' style="color: green;"';
                                    }
                                    ?>
                                    <div <?php echo $estilo; ?>><?php echo $observaciones['estadoTesoreria']; ?></div>
                                </td>
                                <td><?php
                                    $estilo = ' style="color: red;"';
                                    if (substr($observaciones['antiguedad'], 0, 1) == 'T' || substr($observaciones['antiguedad'], 0, 1) == 'C') {
                                        $estilo = ' style="color: green;"';
                                    }
                                    ?>
                                    <div <?php echo $estilo; ?>><?php echo $observaciones['antiguedad']; ?></div>
                                </td>
                                <td><?php
                                    $estilo = ' style="color: red;"';
                                    if ($observaciones['zonaNombre'] == $localidad['localidadDetalle']) {
                                        $estilo = ' style="color: green;"';
                                    }
                                    ?>
                                    <div <?php echo $estilo; ?>><?php echo $observaciones['zonaNombre']; ?></div>
                                </td>
                            <?php
                            } else {
                            ?>
                                <td colspan="4"><?php echo $resObservaciones['mensaje']; ?></td>
                            <?php
                            }
                            ?>
                        </td>
                        <td>
                            <div align="center">
                                <form method="POST" action="elecciones_integrantes_form.php">
                                    <button type="submit" class="btn btn-primary glyphicon glyphicon-pencil center-block btn-sm"></button>
                                    <input type="hidden" id="accion" name="accion" value="3">
                                    <input type="hidden" id="idElecciones" name="idElecciones" value="<?php echo $idElecciones; ?>">
                                    <input type="hidden" id="idEleccionesLocalidad" name="idEleccionesLocalidad" value="<?php echo $idEleccionesLocalidad; ?>">
                                    <input type="hidden" id="idEleccionesLocalidadLista" name="idEleccionesLocalidadLista" value="<?php echo $idEleccionesLocalidadLista; ?>">
                                    <input type="hidden" id="idEleccionesLocalidadListaIntegrante" name="idEleccionesLocalidadListaIntegrante" value="<?php echo $idEleccionesLocalidadListaIntegrante; ?>">
                                </form>
                            </div>    
                        </td>
                        <td>
                            <div align="center">
                                <form method="POST" action="datosElecciones/abm_elecciones_integrantes.php">
                                    <button type="submit" class="btn btn-danger glyphicon glyphicon-move center-block btn-sm"></button>
                                    <input type="hidden" id="accion" name="accion" value="2">
                                    <input type="hidden" id="idElecciones" name="idElecciones" value="<?php echo $idElecciones; ?>">
                                    <input type="hidden" id="idEleccionesLocalidad" name="idEleccionesLocalidad" value="<?php echo $idEleccionesLocalidad; ?>">
                                    <input type="hidden" id="idEleccionesLocalidadLista" name="idEleccionesLocalidadLista" value="<?php echo $idEleccionesLocalidadLista; ?>">
                                    <input type="hidden" id="idEleccionesLocalidadListaIntegrante" name="idEleccionesLocalidadListaIntegrante" value="<?php echo $idEleccionesLocalidadListaIntegrante; ?>">
                                </form>
                            </div>    
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
            <div class="<?php echo $resIntegrantes['clase']; ?>" role="alert">
                <span class="<?php echo $resIntegrantes['icono']; ?>" ></span>
                <span><strong><?php echo $resIntegrantes['mensaje']; ?></strong></span>
            </div>
        <?php
        }    
        ?>
    </div>
    </div>
<?php
} else {
?>
    <div class="<?php echo $clase; ?>" role="alert">
        <span class="<?php echo $icono; ?>" ></span>
        <span><strong><?php echo $mensaje; ?></strong></span>
    </div>
<?php
}
?>
<div class="row">&nbsp;</div>
<!-- BOTON VOLVER -->    
<div class="col-md-12" style="text-align:right;">
    <form  method="POST" action="elecciones_listas_lista.php">
        <button type="submit" class="btn btn-info" name='volver' id='name'>Volver </button>
        <input type="hidden" name="idElecciones" id="idElecciones" value="<?php echo $idElecciones; ?>" />
        <input type="hidden" name="idEleccionesLocalidad" id="idEleccionesLocalidad" value="<?php echo $idEleccionesLocalidad; ?>" />
   </form>
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