<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/recetariosLogic.php');
$recetariosLogic = new recetariosLogic();
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
<div class="panel-heading"><h4><b>Recetarios</b></h4></div>
<div class="panel-body">
    <div class="row">
    <?php
    if (isset($_POST['matricula']) && $_POST['matricula'] != ""){
        $matricula = $_POST['matricula'];
    } else {
        $matricula = NULL;
    }
    if (isset($_POST['serie']) && $_POST['serie'] != ""){
        $serie = $_POST['serie'];
    } else {
        $serie = NULL;
    }
    if (isset($_POST['numero']) && $_POST['numero'] != ""){
        $numero = $_POST['numero'];
    } else {
        $numero = NULL;
    }
    ?>
    <form method="POST" action="recetarios_lista.php">
        <div class="col-xs-2">
            <b>Matrícula </b>  
            <input class="form-control" type="number" name="matricula" id="matricula" value="<?php echo $matricula; ?>" />
        </div>
        <div class="col-xs-2">
            <b>Serie </b>  
            <input class="form-control" type="text" name="serie" id="serie" style="text-transform:uppercase;" onkeyup="javascript:this.value=this.value.toUpperCase();" value="<?php echo $serie; ?>" />
        </div>
        <div class="col-xs-2">
            <b>Número </b>  
            <input class="form-control" type="number" name="numero" id="numero" value="<?php echo $numero; ?>" />
        </div>
        <div class="col-xs-6">
            <br>
            <button type="submit" class="btn btn-success">Buscar</button>
        </div>
    </form>    
    <div class="row">&nbsp;</div>
    <?php
    if (isset($matricula) || isset($serie) || isset($numero)) {
    $resRecetarios = $recetariosLogic->buscarRecetarios($matricula, $serie, $numero);
    //var_dump($resRecetarios);
    if ($resRecetarios['estado']){
    ?>
        <br>
            <table id="tablaOrdenada" class="display">
                <thead>
                    <tr>
                        <th>Id</th>
                        <th>Matricula</th>
                        <th>Apellido y Nombre</th>
                        <th>Entrega</th>
                        <th>Fecha</th>
                        <th>Serie</th>
                        <th>Nº desde</th>
                        <th>Nº hasta</th>
                        <th>Cantidad</th>
<!--                        <th style="width: 30px">Editar</th>
                        <th style="width: 30px">Localidades</th>-->
                    </tr>
                </thead>
          <tbody>
              <?php
                  foreach ($resRecetarios['datos'] as $dato) 
                  {
                      $idRecetarios = $dato['idRecetas'];
                      $matricula = $dato['matricula'];
                      $apellidoNombre = $dato['apellidoNombre'];
                      $entrega = $dato['entrega'];
                      $fecha = $dato['fecha'];
                      $serie = $dato['serie'];
                      $numeroDesde = $dato['numeroDesde'];
                      $numeroHasta = $dato['numeroHasta'];
                      $cantidad = $dato['cantidad'];
                  ?>
                    <tr>
                	<td><?php echo $idRecetarios;?></td>
			<td><?php echo $matricula;?></td>
			<td><?php echo $apellidoNombre;?></td>
			<td><?php echo $entrega;?></td>
			<td><?php echo $fecha;?></td>
			<td><?php echo $serie;?></td>
			<td><?php echo $numeroDesde;?></td>
			<td><?php echo $numeroHasta;?></td>
			<td><?php echo $cantidad;?></td>
<!--                        <td>
                            <div align="center">
                                <form method="POST" action="elecciones_form.php">
                                    <button type="submit" class="btn btn-primary glyphicon glyphicon-pencil center-block btn-sm"></button>
                                    <input type="hidden" id="accion" name="accion" value="3">
                                    <input type="hidden" id="idElecciones" name="idElecciones" value="<?php echo $idElecciones; ?>">
                                    <input type="hidden" id="estadoElecciones" name="estadoElecciones" value="<?php echo $estadoElecciones; ?>">
                                </form>
                            </div>    
                        </td>
                        <td>
                            <div align="center">
                                <form method="POST" action="elecciones_localidades_lista.php">
                                    <button type="submit" class="btn btn-info glyphicon glyphicon-book center-block btn-sm"></button>
                                    <input type="hidden" id="idElecciones" name="idElecciones" value="<?php echo $idElecciones; ?>">
                                    <input type="hidden" id="estadoElecciones" name="estadoElecciones" value="<?php echo $estadoElecciones; ?>">
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
        <div class="<?php echo $resRecetarios['clase']; ?>" role="alert">
            <span class="<?php echo $resRecetarios['icono']; ?>" ></span>
            <span><strong><?php echo $resRecetarios['mensaje']; ?></strong></span>
        </div>
    <?php
    }  
} else {
?>
    <div class="alert alert-error" role="alert">
        <span class="glyphicon glyphicon-alert" ></span>
        <span><strong>Debe ingresar datos para realizar la busqueda</strong></span>
    </div>
<?php
}
?>
</div>
</div>
</div>
<?php
require_once '../html/footer.php';