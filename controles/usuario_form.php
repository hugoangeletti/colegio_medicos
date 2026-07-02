<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
include_once '../dataAccess/funcionesConector.php';
include_once '../dataAccess/funcionesPhp.php';
require_once '../html/head.php';
require_once '../html/header.php';
include_once '../dataAccess/usuarioLogic.php';

$accion = $_POST['accion'];

switch ($accion) 
{
    case '1':
        $idUsuario = NULL;
        $nombreBoton="Guardar";
        $titulo="Nuevo Usuario";
        $nombreUsuario = "";
        $clave = "";
        $nombreCompleto = "";
        $tipoUsuario = "E";
        $estadoUsuario = "A";
        break;

    case '2':
    case '3':
        if ($accion=='2')
        {    
            $nombreBoton="Eliminar";
            $titulo="Eliminar Usuario";
        }
        if ($accion=='3')
        { 
            $nombreBoton="Editar Usuario";
            $titulo="Guardar";
        }
        $idUsuario = $_POST['idUsuario'];
        
        $resUsuario = $usuarioLogic->obtenerUsuarioPorId($idUsuario);
        
        if ($resUsuario['estado'])
        {
            $usuario = $resUsuario['datos'];
            $nombreUsuario = $usuario['nombreUsuario'];
            $clave = $usuario['clave'];
            $nombreCompleto = $usuario['nombreCompleto'];
            $tipoUsuario = $usuario['tipoUsuario'];
            $estadoUsuario = $usuario['estado'];            
        }
        else 
        {
            ?>
             <div class="<?php echo $resUsuario['clase'];?>" role="alert">
                 <span class="<?php echo $resUsuario['icono'];?>" aria-hidden="true"></span>
                 <span><?php echo $resUsuario['mensaje'];?></span>
             </div>
             <?php    
        }
        break;

    default:
        break;
}

if (isset($_POST['mensaje']))
{
?>
   <div class="ocultarMensaje"> 
        <div class="<?php echo $_POST['clase'];?>" role="alert">
        <span class="<?php echo $_POST['icono'];?>" aria-hidden="true"></span>
        <span><?php echo $_POST['mensaje'];?></span>
        </div>  
   </div>
 <?php    
    if (isset($_POST['idUsuario']) && $_POST['idUsuario'] <> "") {
        $idUsuario = $_POST['idUsuario'];
    } else {
        $idUsuario = NULL;
    }
    $nombreUsuario = $_POST['nombreUsuario'];
    $clave = $_POST['clave'];
    $nombreCompleto = $_POST['nombreCompleto'];
    $tipoUsuario = $_POST['tipoUsuario'];
    $estadoUsuario = $_POST['estadoUsuario'];            
}
?>

<div class="panel panel-default">
<div class="panel-heading"><h4><b><?php echo $titulo; ?></b></h4></div>
<div class="panel-body">
    <form method="POST" action="datosUsuarios/usuarios_abm.php" autocomplete="off">
        <div class="row">
            <div class="col-md-2">
                <label>Nombre de Usuario *</label>
                <input type="text" class="form-control" name="nombreUsuario" id="nombreUsuario" value="<?php echo $nombreUsuario; ?>" required>
            </div>
            <div class="col-md-2">
                <label>Clave *</label>  
                <input type="password" class="form-control" id="clabe" name="clave" value="<?php echo $clave; ?>" required="">
            </div>            
            <div class="col-md-4">
                <label>Apellido y Nombre *</label>  
                <input type="text" class="form-control" id="nombreCompleto" name="nombreCompleto" value="<?php echo $nombreCompleto; ?>" required="">
            </div>            
            <div class="col-md-2">
                <label>Tipo *</label>  
                <select class="form-control" id="tipoUsuario" name="tipoUsuario" required="">
                    <option value="E" <?php if ($tipoUsuario == "E") { ?> selected="" <?php }?>>Empleado/a</option>
                    <option value="M" <?php if ($tipoUsuario == "M") { ?> selected="" <?php }?>>Médico/a</option>
                </select>
            </div>            
            <div class="col-md-2">
                <label>Estado *</label>  
                <select class="form-control" id="estadoUsuario" name="estadoUsuario" required="">
                    <option value="A" <?php if ($estadoUsuario == "A") { ?> selected="" <?php }?>>Activo</option>
                    <option value="B" <?php if ($estadoUsuario == "B") { ?> selected="" <?php }?>>Borrado</option>
                </select>
            </div>            
        </div>
            
        <div class="row">&nbsp;</div>
        
        <?php
        if ($accion <= '3')
        {    
        ?>
            <div class="row">
                <div class="col-md-12">
                    <div style="text-align:center">
                        <button type="submit" class="btn btn-success btn-lg" name="" id="" ><?php echo $nombreBoton; ?></button>   
                    </div>
                </div>  
            </div>
        <?php
        }
        ?>
        <input type="hidden" value="<?php echo $accion; ?>" name="accion" id="accion"/>
        <input type="hidden" id="idUsuario" name="idUsuario" value="<?php echo $idUsuario; ?>">
    </form>  
  
</div>
</div>
 
    <!-- BOTON VOLVER -->    
    <div>&nbsp;</div>    
    <div>
        <form  method="POST" action="usuario_lista.php">
            <button type="submit" class="btn btn-default" name='volver' id='name'>Volver </button>
       </form>
    </div>  
                    
<?php
if ($_SESSION['user_id'] == 3) {
    var_dump($_SESSION);
}
require_once '../html/footer.php';


