<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/eticaExpedienteLogic.php');
$eticaExpedienteLogic = new eticaExpedienteLogic();
require_once ('../dataAccess/usuarioLogic.php');
require_once ('../dataAccess/sumarianteLogic.php');
require_once ('../dataAccess/colegiadoLogic.php');
?>
        <script>
            $(document).ready(function () {
                $('#tablaOrdenada').DataTable({
                    "iDisplayLength":25,
                    "language": {
                        "url": "../public/lang/esp.lang"
                    },
                    dom: 'T<"clear">lfrtip',
                    tableTools: {
                       "sSwfPath": "../public/swf/copy_csv_xls_pdf.swf", 
                       "aButtons": [
                            {
                                "sExtends": "pdf",
                                "mColumns" : [0, 1, 2, 3, 4, 5, 6],
//                                "oSelectorOpts": {
//                                    page: 'current'
//                                }
                                "sTitle": "Listado de expedientes",
                                "sPdfOrientation": "portrait",
                                "sFileName": "listado_de_expedientes.pdf"
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
$mensaje = "";
if (isset($_GET['idColegiado']) && $_GET['idColegiado'] <> "") {
    $idColegiado = $_GET['idColegiado'];
    $colegiadoLogic = new colegiadoLogic();
    $resColegiado = $colegiadoLogic->obtenerColegiadoPorId($idColegiado);
    if ($resColegiado['estado']) {
        $colegiado = $resColegiado['datos'];
        $matricula = $colegiado['matricula'];
        $apellidoNombre = trim($colegiado['apellido']).' '.trim($colegiado['nombre']);
    } else {
        $idColegiado = NULL;
        $matricula = NULL;
        $apellidoNombre = NULL;
        $continua = FALSE;
        $mensaje .= $resColegiado['mensaje'];
    }
} else {
    $idColegiado = NULL;
    $continua = FALSE;
    $mensaje .= 'Falta el colegiado.-';
}
?> 
<div class="panel panel-default">
    <div class="panel-heading">
        <div class="row">
            <div class="col-md-9">
                <h4><b>Expedientes de: <?php echo $apellidoNombre.' Matrícula: '.$matricula; ?></b></h4>
            </div>
            <div class="col-md-3 text-left">
                <form id="formColegiado" name="formColegiado" method="POST" onSubmit="" action="colegiado_consulta.php?idColegiado=<?php echo $idColegiado;?>">
                    <button type="submit"  class="btn btn-info" >Volver a Datos del colegiado</button>
                </form>
            </div>
        </div>
    </div>
    <div class="panel-body">
        <?php
        $resExpedientes = $eticaExpedienteLogic->obtenerExpedientePorIdColegiado($idColegiado);
        if ($resExpedientes['estado']){
        ?>
        <div class="row">&nbsp;</div>
        <table id="tablaOrdenada" class="display">
            <thead>
                <tr>
                    <th>Id</th>
                    <th>Rol en la denuncia</th>
                    <th>Denunciado</th>
                    <th>Denunciante</th>
                    <th>Car&aacute;tula</th>
                    <th>Nro. Expediente</th>
                    <th>Estado</th>
                    <th>Fecha Reuni&oacute;n</th>
                    <!--<th>Movimientos</th>
                    <th>Seguimiento</th>-->
                </tr>
            </thead>
            <tbody>
              <?php
                  foreach ($resExpedientes['datos'] as $dato) 
                  {
                      $rolDenuncia = $dato['rolDenuncia'];
                      $idEticaExpediente = $dato['idEticaExpediente'];
                      $idColegiado = ($dato['idColegiado']);
                      $nroExpediente = ($dato['nroExpediente']);
                      $caratula = $dato['caratula'];
                      $observaciones = $dato['observaciones'];
                      $idEticaEstado = $dato['idEticaEstado'];
                      $idUsuario = $dato['idUsuario'];
                      $fecha = $dato['fecha'];
                      $fechaReunionConsejo = $dato['fechaReunionConsejo'];
                      $matricula = $dato['matricula'];
                      $apellido = $dato['apellido'];
                      $nombres = $dato['nombres'];
                      $denunciante = $dato['denunciante'];
                      $eticaEstado = $dato['eticaEstado'];
                  ?>
                    <tr>
                        <td><?php echo $idEticaExpediente;?></td>
                        <td><?php echo $rolDenuncia;?></td>
                        <td><?php echo $apellido.' '.$nombres.' ('.$matricula.')';?></td>
                        <td><?php echo $denunciante;?></td>
                        <td><?php echo $caratula;?></td>
                        <td><?php echo $nroExpediente;?></td>
                        <td><?php echo $eticaEstado;?></td>
                        <td><div class="text-center"><?php echo cambiarFechaFormatoParaMostrar(substr($fechaReunionConsejo, 0, 10));?></div></td>
                        <!--
                        <td>
                            <div align="center">
                                <form method="POST" action="eticaExpediente_movimientos.php">
                                    <button type="submit" class="btn btn-danger glyphicon glyphicon-book center-block btn-sm"></button>
                                    <input type="hidden" id="idEticaExpediente" name="idEticaExpediente" value="<?php echo $idEticaExpediente; ?>">
                                    <input type="hidden" id="estadoExpediente" name="estadoExpediente" value="<?php echo $estadoExpediente; ?>">
                                </form>
                            </div>    
                        </td>
                        <td>
                            <div align="center">
                                <form method="POST" action="eticaExpediente_seguimiento.php">
                                    <button type="submit" class="btn btn-info glyphicon glyphicon-bookmark center-block btn-sm"></button>
                                    <input type="hidden" id="idEticaExpediente" name="idEticaExpediente" value="<?php echo $idEticaExpediente; ?>">
                                    <input type="hidden" id="estadoExpediente" name="estadoExpediente" value="<?php echo $estadoExpediente; ?>">
                                </form>
                            </div>    
                        </td>-->
                   </tr>
                  <?php
                  }
              ?>
            </tbody>
        </table>
        <?php
    } else {
    ?>
        <div class="<?php echo $resExpedientes['clase']; ?>" role="alert">
            <span class="<?php echo $resExpedientes['icono']; ?>" aria-hidden="true"></span>
            <span><strong><?php echo $resExpedientes['mensaje']; ?></strong></span>
        </div>
    <?php
    }    
    ?>
    </div>
</div>
<?php
require_once '../html/footer.php';