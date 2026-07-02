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
    $id = explode('_', $_GET['id']);
    $idUsuario = $id[0];
    $idApp = $id[1];
    $nombreBoton = "Confirmar";
    $appLogic = new appLogic();
    $resApp = $appLogic->obtenerAppPorId($idApp);
    if ($resApp['estado']) {
        $app = $resApp['datos'];
        $nombreApp = $app['nombre'];
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
                        $resAppRol = $appLogic->obtenerAppRolUsuarioPorIdApp($idApp, $idUsuario);
                        if ($resAppRol['estado']) {
                            $cantidadApp = sizeof($resAppRol['datos']);
                            if ($cantidadApp > 6) {
                                $limiteCantidadApp = 6;
                            } else {
                                $limiteCantidadApp = $cantidadApp;
                            }
                            ?>
                            <h3><b>Menú: <?php echo $nombreApp; ?></b></h3>
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
                                    $i++;
                                    $cantidadRoles += 1;
                                }
                                ?>
                            </div>
                        <?php 
                        }
                        ?>   
                        <div class="row">&nbsp;</div>
                        <div class="row">
                            <div class="col-md-12 text-center">
                                <button type="submit"  class="btn btn-success btn-lg" ><?php echo $nombreBoton; ?></button>
                                <input type="hidden" name="cantidadRoles" id="cantidadRoles" value="<?php echo $cantidadRoles; ?>">  
                                <input type="hidden" name="idUsuario" id="idUsuario" value="<?php echo $idUsuario; ?>">
                                <?php 
                                if (isset($idApp) && $idApp <> "") {
                                ?>
                                    <input type="hidden" name="idApp" id="idApp" value="<?php echo $idApp; ?>">
                                <?php 
                                }
                                ?>
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

