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
$usuarioLogic = new usuarioLogic();
require_once ('../dataAccess/sumarianteLogic.php');
$sumarianteLogic = new sumarianteLogic();
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
$idUsuario = $_SESSION['user_id'];
$nombreUsuario = $_SESSION['user'];
//obtengo el rol por usuario para poder cargar movimientos o seguimiento de sumariantes
$cargaExpediente = $usuarioLogic->verificarRolUsuario($idUsuario, 2);
$editaExpediente = $usuarioLogic->verificarRolUsuario($idUsuario, 13);
$cargaMovimiento = $usuarioLogic->verificarRolUsuario($idUsuario, 11);
$cargaSeguimiento = $usuarioLogic->verificarRolUsuario($idUsuario, 12);

if (isset($_POST['mensaje']))
{
 ?>
   <div class="ocultarMensaje"> 
   <p class="<?php echo $_POST['tipomensaje'];?>"><?php echo $_POST['mensaje'];?></p>  
   </div>
 <?php    
}   
?> 
<div class="panel panel-default">
<div class="panel-heading"><h4><b>Expedientes</b></h4></div>
<div class="panel-body">
    <?php
    if (isset($_POST['estadoExpediente']) && $_POST['estadoExpediente'] != ""){
        $estadoExpediente = $_POST['estadoExpediente'];
    } else {
        $estadoExpediente = 'S';
    }
    ?>
    <div class="row">
        <div class="col-xs-6">
            <form method="POST" action="eticaExpediente_lista.php">
                <div class="col-xs-6">
                    <select class="form-control" id="estadoExpediente" name="estadoExpediente" required onChange="this.form.submit()">
                        <option value="S" <?php if($estadoExpediente == "S") { echo 'selected'; } ?>>En sumarios</option>
                        <option value="A" <?php if($estadoExpediente == "A") { echo 'selected'; } ?>>Archivado</option>
                    </select>
                </div>
            </form>    
        </div>
        <div class="col-xs-3"></div>
        <div class="col-xs-3">
            <?php
            if ($cargaExpediente) {
            ?>
            <form method="POST" action="eticaExpediente_form.php">
                <div align="right">
                <button type="submit" class="btn btn-success btn-lg">Nuevo Expediente</button>
                <input type="hidden" id="accion" name="accion" value="1">
                <input type="hidden" id="estadoExpediente" name="estadoExpediente" value="<?php echo $estadoExpediente; ?>">
                </div>
            </form>
            <?php
            } else {
                echo '';
            }
            ?>
        </div>
    </div>
    <?php
    //veo si el usuario es un sumariante, filtro la lista de sus expedientes
    $idSumarianteTitular = $sumarianteLogic->esSumariante($nombreUsuario);
    if (isset($idSumarianteTitular)) {
        $resExpedientes = $eticaExpedienteLogic->obtenerExpedientePorEstadoUsuario($estadoExpediente, $idSumarianteTitular);
    } else {
        $resExpedientes = $eticaExpedienteLogic->obtenerExpedientePorEstado($estadoExpediente);
    }
    if ($resExpedientes['estado']){
    ?>
    <div class="row">&nbsp;</div>
    <table id="tablaOrdenada" class="display">
        <thead>
            <tr>
                <th>Id</th>
                <th>Denunciado</th>
                <th>Denunciante</th>
                <th>Car&aacute;tula</th>
                <th>Nro. Expediente</th>
                <th>Estado</th>
                <th>Fecha Reuni&oacute;n</th>
                <th>Otros denunciados</th>
                <?php 
                if ($editaExpediente) {
                ?>
                <th>Editar</th>
                <?php
                }
                ?>
                <?php 
                if ($cargaMovimiento) {
                ?>
                <th>Movimientos</th>
                <?php
                }
                ?>
                <?php 
                if ($cargaSeguimiento) {
                ?>
                <th>Seguimiento</th>
                <?php
                }
                ?>
            </tr>
        </thead>
        <tbody>
          <?php
              foreach ($resExpedientes['datos'] as $dato) 
              {
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
                    <td><?php echo $apellido.' '.$nombres.' ('.$matricula.')';?></td>
                    <td><?php echo $denunciante;?></td>
                    <td><?php echo $caratula;?></td>
                    <td><?php echo $nroExpediente;?></td>
                    <td><?php echo $eticaEstado;?></td>
                    <td><div class="text-center"><?php echo cambiarFechaFormatoParaMostrar(substr($fechaReunionConsejo, 0, 10));?></div></td>
                    <td>
                        <div class="text-center">
                            <a href="eticaExpedienteOtrosDenunciados.php?id=<?php echo $idEticaExpediente; ?>"><?php 
                                if (isset($dato['otrosDenunciados'])) {
                                    echo $dato['otrosDenunciados'];
                                } else {
                                    echo 'AGREGAR';
                                } ?></a>
                        </div>    
                    </td>
                    <?php 
                    if ($editaExpediente) {
                    ?>
                    <td>
                        <div class="text-center">
                            <form method="POST" action="eticaExpediente_form.php">
                                <button type="submit" class="btn btn-primary glyphicon glyphicon-pencil center-block btn-sm"></button>
                                <input type="hidden" id="accion" name="accion" value="3">
                                <input type="hidden" id="idEticaExpediente" name="idEticaExpediente" value="<?php echo $idEticaExpediente; ?>">
                                <input type="hidden" id="estadoExpediente" name="estadoExpediente" value="<?php echo $estadoExpediente; ?>">
                            </form>
                        </div>    
                    </td>
                    <?php 
                    }
                    ?>
                    <?php 
                    if ($cargaMovimiento) {
                    ?>
                    <td>
                        <div align="center">
                            <form method="POST" action="eticaExpediente_movimientos.php">
                                <button type="submit" class="btn btn-danger glyphicon glyphicon-book center-block btn-sm"></button>
                                <input type="hidden" id="idEticaExpediente" name="idEticaExpediente" value="<?php echo $idEticaExpediente; ?>">
                                <input type="hidden" id="estadoExpediente" name="estadoExpediente" value="<?php echo $estadoExpediente; ?>">
                            </form>
                        </div>    
                    </td>
                    <?php 
                    }
                    ?>
                    <?php 
                    if ($cargaSeguimiento) {
                    ?>
                    <td>
                        <div align="center">
                            <form method="POST" action="eticaExpediente_seguimiento.php">
                                <button type="submit" class="btn btn-info glyphicon glyphicon-bookmark center-block btn-sm"></button>
                                <input type="hidden" id="idEticaExpediente" name="idEticaExpediente" value="<?php echo $idEticaExpediente; ?>">
                                <input type="hidden" id="estadoExpediente" name="estadoExpediente" value="<?php echo $estadoExpediente; ?>">
                            </form>
                        </div>    
                    </td>
                    <?php 
                    }
                    ?>
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