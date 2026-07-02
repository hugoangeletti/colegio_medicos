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
require_once ('../dataAccess/funcionesPhp.php');

if (isset($_POST['mensaje'])) {
?>
   <div class="ocultarMensaje"> 
       <p class="<?php echo $_POST['tipomensaje'];?>"><?php echo $_POST['mensaje'];?></p>  
   </div>
<?php 
} 

if ($usuarioLogic->verificarRolUsuario($_SESSION['user_id'], 46)) {
    $idUsuario = $_POST['idUsuario'];
    $nombreBoton = "Confirmar";
    //$usuarioRol = $usuarioLogic->obtenerRolesPorUsuario($idUsuario);
    $appLogic = new appLogic();
    $roles = $appLogic->obtenerApps();

    if ($roles['estado']) {
        $resUsuario = $usuarioLogic->obtenerUsuarioPorId($idUsuario);
        if ($resUsuario['estado']) {
            $usuario = $resUsuario['datos'];
            $userName = $usuario['nombreUsuario'];
            ?>            
            <div class="panel panel-default">
                <div class="panel-heading">
                    <div class="row">
                        <div class="col-md-10">
                            <h4><b>Rol/es asignado/s al usuario "<?php echo $userName; ?>"</b></h4>
                        </div>
                        <div class="col-md-2">
                            <form  method="POST" action="usuario_lista.php">
                                <button type="submit" class="btn btn-default" name='volver' id='name'>Volver </button>
                            </form>
                        </div> 
                    </div>
                </div>
                <div class="panel-body"> 
                    <form id="datosRoles" name="datosRoles" method="POST" onSubmit="" action="datosUsuarioRoles\abm_datosusuarioroles.php">
                        <?php 
                        $cantidadRoles = 0;
                        $i=1;  
                        foreach ($roles['datos'] as $app) {
                            $idApp = $app['id'];
                            $nombreApp = $app['nombre'];

                            $resAppRol = $appLogic->obtenerAppRolUsuarioPorIdApp($idApp, $idUsuario);
                            if ($resAppRol['estado']) {
                                $cantidadApp = sizeof($resAppRol['datos']);
                                if ($cantidadApp == 0) continue;
                                if ($cantidadApp > 6) {
                                    $limiteCantidadApp = 6;
                                } else {
                                    $limiteCantidadApp = $cantidadApp;
                                }
                                ?>
                                <h4><b><?php echo $nombreApp; ?></b></h4>
                                <div class="row"> 
                                    <?php
                                    foreach ($resAppRol['datos'] as $usuarioAppRol) {
                                        $idAppRol = $usuarioAppRol['id'];
                                        $nombreAppRol = $usuarioAppRol['nombre'];
                                        $idUsuarioAsignado = $usuarioAppRol['idUsuarioAsignado'];
                                        ?>
                                        <div class="col-md-2">
                                            <label>
                                            <input type="checkbox" name="rol<?php echo $i; ?>" 
                                                   id="rol<?php echo $i; ?>" 
                                                   value="<?php echo $idAppRol; ?>" 
                                                       <?php if (isset($idUsuarioAsignado)) { echo 'checked'; } ?>>
                                            </label>
                                            <?php echo $nombreAppRol;?>
                                        </div>
                                        <?php
                                        /*
                                        if ($i%6 == 0){ ?>
                                            </tr>
                                        <?php
                                        }
                                        */
                                        $i++;
                                        $cantidadRoles += 1;
                                    }
                                    ?>
                                </div>
                            <?php 
                            }
                        }
                        ?>   
                        <div class="row">&nbsp;</div>
                        <div class="row">
                            <div class="col-md-12 text-center">
                                <button type="submit"  class="btn btn-success btn-lg" ><?php echo $nombreBoton; ?></button>
                                <input type="hidden" name="cantidadRoles" id="cantidadRoles" value="<?php echo $cantidadRoles; ?>">  
                                <input type="hidden" name="idUsuario" id="idUsuario" value="<?php echo $idUsuario; ?>">
                            </div>
                        </div>  
                    </form>
                </div>
            </div>
         <?php
        }
    }
}
require_once '../html/footer.php';
?>

