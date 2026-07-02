<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/colegiadoLogic.php');
?>

<div class="row">&nbsp;</div> 
<div class="panel panel-default">
<div class="panel-heading"><h4><b>Listado de Colegiados</b></h4></div>
<div class="panel-body">
    <?php
    if (isset($_POST['orden'])){
        $orden = $_POST['orden'];
    } else {
        if (isset($_GET['orden'])){
            $orden = $_GET['orden'];
        } else {
            $orden = 'M';
        }
    }
    
    if (isset($_POST['buscar'])){
        $buscar = $_POST['buscar'];
    } else {
        if (isset($_GET['buscar'])){
            $buscar = $_GET['buscar'];
        } else {
            $buscar = NULL;
        }        
    }
    ?>
    <div class="row">
        <div class="col-md-4">
            <form method="POST" action="colegiado_listado.php">
                <select class="form-control" id="orden" name="orden" onChange='this.form.submit()'>
                    <option value="M" <?php if($orden == 'M') { echo 'selected'; } ?>>Por Matrícula</option>
                    <option value="A" <?php if($orden == 'A') { echo 'selected'; } ?>>Por Apellido y Nombre</option>
                </select>
            </form>    
        </div>
        <div class="col-md-3">
            <?php
            $total_records = 0;
            $colegiadoLogic = new colegiadoLogic();
            $resCantidad = $colegiadoLogic->obtenerCantidadMatriculasPaginacion($buscar);
            if ($resCantidad['estado']) {
                $total_records = $resCantidad['cantidad'];
            }
            ?>
            <!--<h4>Total de registros: <?php echo $total_records; ?></h4>-->
        </div>    
        <div class="col-md-5">
            <form method="POST" action="colegiado_listado.php">
                <div class="col-md-10">
                    <input type="text" class="form-control" id="buscar" name="buscar" value="<?php echo $buscar; ?>" placeholder="buscar por Nro de matrícula o Documento o Apellido" minlength="3">
                </div>
                <div class="col-md-2">
                    <button type="submit"  class="btn btn-success" >Buscar </button>
                    <input type="hidden" id="orden" name="orden" value="<?php echo $orden; ?>">
                </div>
            </form>
        </div>
   </div>     
    <div class="row">&nbsp;</div>
    <?php
    //para paginacion
    $registro_por_pagina = 50;
    $pagina = '';
    if(isset($_GET["pagina"]))
    {
     $pagina = $_GET["pagina"];
    }
    else
    {
     $pagina = 1;
    }

    $start_from = ($pagina-1)*$registro_por_pagina;
    //fin para paginacion
  
    if (isset($buscar)) {
        //buscar
        $resColegiados = $colegiadoLogic->obtenerColegiadosPaginacion($start_from, $registro_por_pagina, $buscar, $orden);
    } else {
        $resColegiados = $colegiadoLogic->obtenerColegiadosPaginacion($start_from, $registro_por_pagina, null, $orden);
    }
    //$resColegiados = obtenerReclamosPorEstado($idEstado);  
    if ($resColegiados['estado'])
    {
        if(sizeof($resColegiados['datos'])>0)
        {
        ?>  
            <div class="table-responsive">
                <table class="table table-bordered">
                <thead>
                    <tr>
                        <th style="width: 100px">N&ordm; Matrícula</th>
                        <th>Apellido y Nombre</th>
                        <th>Numero Documento</th>
                        <th>Estado actual</th>
                        <th>Teléfonos</th>
                        <th>Correo electrónico</th>
                    </tr>
               </thead>
               <tbody>
               <?php
                   foreach ($resColegiados['datos'] as $datos) 
                   {
                       $idColegiado = $datos['idColegiado'];
                       $matricula = $datos['matricula'];
                       $numeroDocumento = $datos['numeroDocumento'];
                       $apellidoNombre = $datos['apellidoNombre'];
                       $tipoMovimiento = $datos['tipoMovimiento'];
                       $telefonos = $datos['telefono1'].' '.$datos['telefono2'];
                       $mail = $datos['mail'];
                   ?>
                   <tr>
                	<td><?php echo $matricula;?></td>
                        <td><?php echo $apellidoNombre;?></td>
                        <td><?php echo $numeroDocumento;?></td>
                        <td><?php echo $tipoMovimiento;?></td>
                        <td><?php echo $telefonos;?></td>
                        <td><?php echo $mail;?></td>
                   </tr>     
                   <?php
                   }
               ?>
               </tbody>    
            </table>
                </div>
        <?php    
        }
        else
        {
        ?>
            <h1>Colegiados</h1>
            <div class="alert alert-warning" role="alert">
              <span class="glyphicon glyphicon-exclamation-sign" ></span><span><strong>&nbsp;&nbsp;NO SE ENCONTRARON COINCIDENCIAS</strong></span>
            </div>
        <?php
        }    
    }
    else 
    {
    ?>
        <h1>Colegiados</h1>
        <div class="alert alert-danger" role="alert">
            <span class="glyphicon glyphicon-remove" aria-hidden="true"></span><span><strong>&nbsp;&nbsp;ERROR AL BUSCAR LOS RECLAMOS</strong></span>
        </div>
    <?php
    }
    ?>
        <div align="center">
                <br />
                <?php
                
                    $total_pages = ceil($total_records/$registro_por_pagina);
                    $start_loop = $pagina;
                    $diferencia = $total_pages - $pagina;
                    if($diferencia <= 5 && $total_pages > 5)
                    {
                     $start_loop = $total_pages - 5;
                    }
                    $end_loop = $start_loop + 4;
                    if ($end_loop > $total_pages) {
                        $end_loop = $total_pages;
                    }
                        
                    //echo $pagina.' - '.$total_records.'-Total-'.$total_pages.' - '.$start_loop.' - '.$end_loop.'<br>';
                    if($pagina > 1)
                    {
                     echo "<a class='btn btn-default btn-lg' href='colegiado_listado.php?orden=".$orden."&buscar=".$buscar."&pagina=1'>Primera</a>";
                     echo "<a class='btn btn-default btn-lg' href='v.php?orden=".$orden."&buscar=".$buscar."&pagina=".($pagina - 1)."'>Anterior</a>";
                    }
                    for($i=$start_loop; $i<=$end_loop; $i++)
                    {     
                     echo "<a class='btn btn-default btn-lg' href='colegiado_listado.php?orden=".$orden."&buscar=".$buscar."&pagina=".$i."'>".$i."</a>";
                    }
                    if($pagina < $end_loop)
                    {
                     echo "<a class='btn btn-default btn-lg' href='colegiado_listado.php?orden=".$orden."&buscar=".$buscar."&pagina=".($pagina + 1)."'>Próxima</a>";
                     echo "<a class='btn btn-default btn-lg' href='colegiado_listado.php?orden=".$orden."&buscar=".$buscar."&pagina=".$total_pages."'>Última</a>";
                    }
                //}

                ?>
                </div>
</div>
</div>
<?php    
require_once '../html/footer.php';
