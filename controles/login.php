<?php
require_once '../dataAccess/config.php';
require_once '../html/head.php';
require_once '../html/encabezado.php';
?>
<div class="row">
    <div class="col-md-2">&nbsp;</div>
    <div class="col-md-8">
        <h3>Ingreso al sistema</h3>
    </div>
    <div class="col-md-2">&nbsp;</div>
</div>

<form class="form-horizontal" name="login" method="post" action="control.php">
    <?php
    if (isset($_GET['err']) && $_GET['err'] == 'OK'){
    ?>
    <div class="ocultarMensaje"> 
        <div class="form-group alert alert-danger">
            <div class="col-sm-2">&nbsp;</div>
            <div class="col-sm-10">
                Error en el usuario o contraseña, vuelva a intentar...
            </div>
        </div>
    </div>
    <?php
    }
    ?>
  <div class="form-group ">
    <label class="control-label col-sm-2" for="email">Usuario:</label>
    <div class="col-sm-3">
        <input type="text" autofocus="" class="form-control" id="userName" name="userName" placeholder="Ingrese usuario">
    </div>
    <div class="col-sm-7">
    </div>
  </div>
  <div class="form-group">
      <label class="control-label col-sm-2" for="pwd">Contrase&nacute;a:</label>
    <div class="col-sm-3"> 
        <input type="password" class="form-control" id="clave" name="clave" placeholder="Ingrese contraseña">
    </div>
    <div class="col-sm-7">
    </div>
  </div>
  <div class="form-group"> 
    <div class="col-sm-offset-2 col-sm-10">
      <button type="submit" class="btn btn-default">Ingresar</button>
    </div>
  </div>
</form>
<?php include("../html/footer.php");?>




