<?php
if (!isset($localhost)) {
    require_once ('../dataAccess/config.php');
}

require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/usuarioLogic.php');
$usuarioLogic = new usuarioLogic();
require_once ('../dataAccess/appLogic.php');
$appLogic = new appLogic();
require_once ('../dataAccess/funcionesPhp.php');

if ($usuarioLogic->verificarRolUsuario($_SESSION['user_id'], 46)){
    $idUsuario = $_POST['idUsuario'];
    $nombreBoton = "Confirmar";
    $usuarioRol = $usuarioLogic->obtenerRolesPorUsuario($idUsuario);
    $roles = $appLogic->obtenerRoles();

    if ($usuarioRol['estado']){
        $resUsuario = $usuarioLogic->obtenerUsuarioPorId($idUsuario);
        if ($resUsuario['estado']){
            $usuario = $resUsuario['datos'];
            $userName = $usuario['nombreUsuario'];
            if (isset($_POST['mensaje'])) {
                ?>
               <div class="ocultarMensaje"> 
               <p class="<?php echo $_POST['tipomensaje'];?>"><?php echo $_POST['mensaje'];?></p>  
               </div>
             <?php } ?>
            <div class="row">&nbsp;</div> 
            <div class="panel panel-default">
                <div class="panel-heading"><h4><b>Rol/es asignado/s al usuario "<?php echo $userName; ?>"</b></h4></div>
                <div class="panel-body"> 
                    <form id="datosRoles" name="datosRoles" method="POST" onSubmit="" action="datosUsuarioRoles\abm_datosusuarioroles.php">
                        <div class="row"> 
                            <div class="col-xs-12">
                                <table>
                                    <thead>
                                        <tr>
                                            <th style="width: 50px">Marcar</th>
                                            <th style="width: 200px">Rol</th>
                                            <th style="width: 50px">Marcar</th>
                                            <th style="width: 200px">Rol</th>
                                            <th style="width: 50px">Marcar</th>
                                            <th style="width: 200px">Rol</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $i=1;  
                                        foreach ($roles['datos'] as $rol) {
                                            $idRol = $rol['id'];
                                            if ($i%3 == 1){ ?>
                                                <tr>
                                        <?php } ?>
                                                    <td><div class="checkbox">
                                                        <label>
                                                        <input type="checkbox" name="rol<?php echo $i; ?>" 
                                                               id="rol<?php echo $i; ?>" 
                                                               value="<?php echo $idRol; ?>" 
                                                                   <?php if ($usuarioLogic->verificarRolUsuario($idUsuario, $idRol)){?> 
                                                                checked
                                                                <?php } ?>>
                                                        </label>
                                                        </div>
                                                    </td>
                                                    <td><?php echo $rol['nombre'];?></td>
                                            <?php
                                            if ($i%3 == 0){ ?>
                                                </tr>
                                            <?php
                                            }
                                            $i++;
                                        }
                                        ?>
                                    </tbody>
                                </table>
                                <input type="hidden" name="cantidadRoles" id="cantidadRoles" value="<?php echo $i; ?>">  
                                <input type="hidden" name="idUsuario" id="idUsuario" value="<?php echo $idUsuario; ?>">  
                            </div>
                        </div>

                        <div class="row">&nbsp;</div>

                        <div class="row">
                             <div style="text-align:center">
                                 <button type="submit"  class="btn btn-success btn-lg" ><?php echo $nombreBoton; ?></button>
                             </div>
                        </div>  

                    </form>   
                </div>
            </div>
         <?php
        }
    }
}
?>
<!-- BOTON VOLVER -->    
<div>&nbsp;</div>    
<div>
    <form  method="POST" action="usuario_lista.php">
        <button type="submit" class="btn btn-default" name='volver' id='name'>Volver </button>
    </form>
</div>  
<?php    
require_once '../html/footer.php';
?>

