<?php
require_once ('../../dataAccess/config.php');
permisoLogueado();
include_once '../../dataAccess/funcionesConector.php';
include_once '../../dataAccess/funcionesPhp.php';
include_once '../../dataAccess/usuarioLogic.php';

$accion = $_POST['accion'];
if (isset($_POST['idUsuario'])) {
    $idUser = $_POST['idUsuario'];
} else {
    $idUser = NULL;
}
$estadoUsuario = $_POST['estadoUsuario'];

$clave = $_POST['clave'];
$nombreUsuario = $_POST['nombreUsuario'];
$nombreCompleto = $_POST['nombreCompleto'];
$tipoUsuario = $_POST['tipoUsuario'];

switch ($accion) 
{
    case '1':
        $resultado = $usuarioLogic->obtenerUsuarioPorNombre($nombreUsuario);
        //var_dump($resultado); exit;
        if ($resultado['estado']){
            $resultado['mensaje'] = "EL NOMBRE DE USUARIO YA EXISTE, VUELVA A CREARLO";
            $resultado['clase'] = 'alert alert-info'; 
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        } else {
            $resultado = $usuarioLogic->agregarUsuario($nombreUsuario, $clave, $nombreCompleto, $tipoUsuario);
        }
        break;

    case '3':
        $resultado = $usuarioLogic->actualizarUsuario($idUser, $nombreUsuario, $clave, $nombreCompleto, $tipoUsuario, $estadoUsuario);
        break;

}

//var_dump($resultado); exit;
?>

<body onLoad="document.forms['myForm'].submit()">
<?php
    if ($resultado['estado'])
    {
?>
        <form name="myForm"  method="POST" action="../usuario_lista.php">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $resultado['mensaje']; ?>">
            <input type="hidden"  name="clase" id="clase" value="<?php echo $resultado['clase'];?>">
            <input type="hidden"  name="icono" id="icono" value="<?php echo $resultado['icono'];?>">
        </form>
<?php
    } else {
?>
        <form name="myForm"  method="POST" action="../usuario_form.php">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $resultado['mensaje']; ?>">
            <input type="hidden"  name="clase" id="clase" value="<?php echo $resultado['clase'];?>">
            <input type="hidden"  name="icono" id="icono" value="<?php echo $resultado['icono'];?>">
            <input type="hidden"  name="accion" id="accion" value="<?php echo $accion;?>">
            <input type="hidden"  name="idUsuario" id="idUsuario" value="<?php echo $idUser;?>">
            <input type="hidden"  name="nombreUsuario" id="nombreUsuario" value="<?php echo $nombreUsuario;?>">
            <input type="hidden"  name="nombreCompleto" id="nombreCompleto" value="<?php echo $nombreCompleto;?>">
            <input type="hidden"  name="tipoUsuario" id="tipoUsuario" value="<?php echo $tipoUsuario;?>">
            <input type="hidden"  name="estadoUsuario" id="estadoUsuario" value="<?php echo $estadoUsuario;?>">
            <input type="hidden"  name="clave" id="clave" value="<?php echo $clave;?>">
        </form>
<?php
    }
?>
</body>