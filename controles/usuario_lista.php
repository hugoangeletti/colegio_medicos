<?php
if (!isset($localhost)) {
    require_once ('../dataAccess/config.php');
}

require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/usuarioLogic.php');
$usuarioLogic = new usuarioLogic();
require_once ('../dataAccess/funcionesPhp.php');
?>
        <script>
            $(document).ready(function () {
                $('#tablaOrdenada').DataTable({
                    "iDisplayLength":25,
                    "order": [[ 1, "asc" ]],
                    "language": {
                        "url": "../public/lang/esp.lang"
                    },
                    dom: 'T<"clear">lfrtip',
                    tableTools: {
                       "sSwfPath": "../public/swf/copy_csv_xls_pdf.swf", 
                       "aButtons": [
                            {
                                "sExtends": "pdf",
                                "mColumns" : [0, 1, 2],
//                                "oSelectorOpts": {
//                                    page: 'current'
//                                }
                                "sTitle": "Listado de Tramites",
                                "sPdfOrientation": "portrait",
                                "sFileName": "ListadoDeTramites.pdf"
//                              "sPdfOrientation": "landscape",
//                              "sPdfSize": "letter",  ('A[3-4]', 'letter', 'legal' or 'tabloid')
                            }
                            
                    ]
                    }
                });
            });
</script>

<div>&nbsp;</div>
<?php
if (isset($_POST['mensaje']))
{
    $conPermiso = TRUE;
 ?>
   <div class="ocultarMensaje"> 
   <p class="<?php echo $_POST['tipomensaje'];?>"><?php echo $_POST['mensaje'];?></p>  
   </div>
 <?php    
} else {   
    //ver perimso del usuario, si puede actualizar usuarios
    $conPermiso = $usuarioLogic->verificarRolUsuario($_SESSION['user_id'], 46);
}

if (isset($_POST['estadoUsuario'])) {
    $estadoUsuario = $_POST['estadoUsuario'];
} else {
    $estadoUsuario = 'A';
}
?> 
<div class="panel panel-default">
    <div class="panel-heading"><h4><b>Usuarios del Sistema</b></h4></div>
    <div class="panel-body">
        <div class="row">
            <div class="col-md-6">
                <form id="formUsuarios" name="formUsuarios" method="POST" onSubmit="" action="usuario_lista.php">
                    <div class="row">
                        <div class="col-md-6">
                            <b>Estado </b>  
                            <select class="form-control" id="estadoUsuario" name="estadoUsuario" required="" onChange="this.form.submit()">
                                <option value="A" <?php if ($estadoUsuario == 'A') { ?> selected="" <?php } ?>>Activos</option>
                                <option value="B" <?php if ($estadoUsuario == 'B') { ?> selected="" <?php } ?>>De baja</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
            <div class="col-md-6 text-right">
                <form  method="POST" action="usuario_form.php">
                    <button type="submit" class="btn btn-success" name='agregar' id='name'>Agregar usuario </button>
                    <input type="hidden" id="accion" name="accion" value="1">
                </form>
            </div>
        </div>
        <div class="row">&nbsp;</div>
<?php
$resUsuarios = $usuarioLogic->obtenerUsuarios();
if ($resUsuarios['estado']) {
?>    
    <table id="tablaOrdenada" class="display">
        <thead>
            <tr>
                <th style="width: 50px">Id</th>
                <th style="width: 120px">Usuario</th>
                <th style="width: 120px">Apellido y Nombre</th>
                <th >Rol/es</th>
                <th style="width: 50px">Editar</th>
                <!--<th style="width: 50px">Asignar roles</th>-->
            </tr>
        </thead>
        <tbody>
            <?php
            foreach ($resUsuarios['datos'] as $dato) {
                $idUsuario = $dato['idUsuario'];
                $resRolesUsuario = $usuarioLogic->obtenerRolesPorUsuario($idUsuario);
                if ($resRolesUsuario['estado']) {
                    $rolesAsigandos = $resRolesUsuario['datos']; 
                } else {
                    $rolesAsigandos = NULL;
                }
                //$rolesAsigandos = $usuarioLogic->obtenerLeyendaRoles($idUsuario);
                
                if ($estadoUsuario == $dato['estado']) {
            ?>
                <tr>
                    <td><?php echo $idUsuario;?></td>
                    <td><?php echo $dato['nombreUsuario'];?></td>
                    <td><?php echo $dato['nombreCompleto'];?></td>
                    <td><?php 
                        if (isset($rolesAsigandos) && sizeof($rolesAsigandos) > 0) {
                            $i = 0;
                            foreach ($rolesAsigandos as $value) {
                                $idApp = $value['idApp'];
                                $nombre = $value['nombre'];
                                $cantidadRoles = $value['cantidadRoles'];
                                ?>
                                <a href="usuariorol_app_form.php?id=<?php echo $idUsuario.'_'.$idApp; ?>" name="idApp" class="btn btn-default"><?php echo $nombre.' ('.$cantidadRoles.')'; ?></a>
                                <?php
                                $i += 1;
                                if ($i >= 8) {
                                    echo '<br><br>';
                                    $i = 0;
                                } 
                            }
                        }
                        ?></td>
                    <td><?php 
                        if ($conPermiso){ ?>
                            <div align="center">
                            <form method="POST" action="usuario_form.php">
                                <button type="submit" class="btn btn-primary glyphicon glyphicon-pencil center-block btn-sm"></button>
                                <input type="hidden" id="accion" name="accion" value="3">
                                <input type="hidden" id="idUsuario" name="idUsuario" value="<?php echo $idUsuario; ?>">
                            </form>
                            </div>    
                        <?php 
                        } else { echo '--'; }?>
                    <!--<td><?php 
                        if ($conPermiso){ ?>
                            <div align="center">
                            <form method="POST" action="usuariorol_form.php">
                                <button type="submit" class="btn btn-info glyphicon glyphicon-book center-block btn-sm"></button>
                                <input type="hidden" id="accion" name="accion" value="3">
                                <input type="hidden" id="idUsuario" name="idUsuario" value="<?php echo $idUsuario; ?>">
                            </form>
                            </div>    
                        <?php 
                        } else { echo '--'; }?>
                    </td>-->
                </tr>
              <?php
                }
              }
              ?>
              
	   </tbody>
	  </table>
    <?php
    }
    else
    {
      ?>
        <div class="<?php echo $resUsuarios['clase']; ?>" role="alert">
            <span class="<?php echo $resUsuarios['icono']; ?>" ></span>
            <span><strong><?php echo $resUsuarios['mensaje']; ?></strong></span>
        </div>
    <?php    
    }    
?>
</div>
</div>
<?php    
require_once '../html/footer.php';