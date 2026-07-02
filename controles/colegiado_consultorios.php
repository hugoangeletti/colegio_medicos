<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/colegiadoLogic.php');
require_once ('../dataAccess/colegiadoConsultorioLogic.php');
$colegiadoConsultorioLogic = new colegiadoConsultorioLogic();
?>
<script>
$(document).ready(
    function () {
                $('#tablaCertificados').DataTable({
                    "iDisplayLength":10,
                     "order": [[ 1, "asc"]],
                    "language": {
                        "url": "../public/lang/esp.lang"
                    },
                    "bLengthChange": false,
                    "bFilter": true,
//                    dom: 'T<"clear">lfrtip',
                    tableTools: {
                       "sSwfPath": "../public/swf/copy_csv_xls_pdf.swf", 
                       
                       "aButtons": [
                            {
                                "sExtends": "pdf",
                                "mColumns" : [0, 1, 2, 3,4],
//                                "oSelectorOpts": {
//                                    page: 'current'
//                                }
                                "sTitle": "Listado de Certificados",
                                "sPdfOrientation": "portrait",
                                "sFileName": "ListadoDeLlamadas.pdf"
//                              "sPdfOrientation": "landscape",
//                              "sPdfSize": "letter",  ('A[3-4]', 'letter', 'legal' or 'tabloid')
                            }
                            
                    ]
                    }
                });
    }
);
</script>
<?php
if (isset($_GET['idColegiado'])) {
    $_SESSION['menuColegiado'] = "Consultorios";
    $periodoActual = $_SESSION['periodoActual'];
    $idColegiado = $_GET['idColegiado'];
    $colegiadoLogic = new colegiadoLogic();
    $resColegiado = $colegiadoLogic->obtenerColegiadoPorId($idColegiado);
    if ($resColegiado['estado'] && $resColegiado['datos']) {
        $colegiado = $resColegiado['datos'];
        $muestraMenuCompleto = TRUE;
        include 'menuColegiado.php';
        ?>
        <div class="row">&nbsp;</div>
        <div class="row">
            <div class="col-md-6">
                <label>Apellido y Nombres:&nbsp; </label><?php echo $colegiado['apellido'].', '.$colegiado['nombre']; ?>
                <label>- Matr&iacute;cula:&nbsp; </label><?php echo $colegiado['matricula']; ?>
            </div>
            <div class="col-md-3"><h4><b>Consultorios habilitados</b></h4></div>
            <div class="col-md-3 text-right">
                <?php 
                if (!$_SESSION['user_entidad']['soloConsulta']) { 
                ?>                      
                    <form id="formColegiado" name="formColegiado" method="POST" onSubmit="" action="consultorio_form.php?idColegiado=<?php echo $idColegiado;?>">
                        <button type="submit"  class="btn btn-success" >Nuevo consultorio</button>
                    </form>
                <?php 
                }
                ?>
            </div>
        </div>
        <?php
        //busco las especialidades
        $resConsultorios = $colegiadoConsultorioLogic->obtenerConsultoriosPorIdColegiado($idColegiado);
        if ($resConsultorios['estado']){
        ?>
            <div class="row">
                <div class="col-md-12">
                <table  id="tablaCertificados" class="display">
                    <thead>
                        <tr>
                            <th style="text-align: center; display: none;">Id</th>
                            <th>Estado</th>
                            <th>N&uacute;mero resoluci&oacute;n</th>
                            <th>Domicilio</th>
                            <th>Localidad</th>
                            <th>Fecha Habilitaci&oacute;n</th>
                            <th>Fecha de baja</th>
                            <th style="text-align: center;">Imprimir</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($resConsultorios['datos'] as $dato){
                            $idColegiadoConsultorio = $dato['id'];
                            
                            $domicilioCompleto = '';
                            if ($dato['calle']) {
                                $domicilioCompleto = $dato['calle'];
                                if ($dato['numero']) {
                                    $domicilioCompleto .= " Nº ".$dato['numero'];
                                }
                                if ($dato['lateral']) {
                                    $domicilioCompleto .= " e/ ".$dato['lateral'];
                                }
                                if ($dato['piso'] && strtoupper($dato['piso']) != "NR") {
                                    $domicilioCompleto .= " Piso ".$dato['piso'];
                                }
                                if ($dato['departamento'] && strtoupper($dato['departamento']) != "NR") {
                                    $domicilioCompleto .= " Dto. ".$dato['departamento'];
                                }
                            }
                            $localidad = $dato['nombreLocalidad'].' ('.$dato['codigoPostal'].')';
                            $fechaHabilitacion= cambiarFechaFormatoParaMostrar($dato['fechaHabilitacion']);
                            $estado = $dato['estado'];
                            $estado = $dato['estadoDetalle'];
                            $telefono = $dato['telefono'];
                            $fechaBaja= cambiarFechaFormatoParaMostrar($dato['fechaBaja']);
                            $numeroResolucion = $dato['resolucion'];
                            ?>
                            <tr>
                                <td style="display: none"><?php echo $idColegiadoConsultorio; ?></td>
                                <td><?php echo $estado; ?></td>
                                <td><?php echo $numeroResolucion; ?></td>
                                <td><?php echo $domicilioCompleto; ?></td>
                                <td><?php echo $localidad; ?></td>
                                <td><?php echo $fechaHabilitacion; ?></td>
                                <td><?php echo $fechaBaja; ?></td>
                                <td style="text-align: center;">
                                    <?php
                                    if ($fechaBaja == '' && $estado <> 'B' ) {
                                        if (!$_SESSION['user_entidad']['soloConsulta']) { 
                                        ?>                      
                                            <a href="colegiado_consultorio_imprimir_certificado.php?id=<?php echo $idColegiadoConsultorio; ?>&tipo=Consultorio" class="btn btn-default" role="button" target="_BLANK">Consultorio</a>                    
                                            <a href="colegiado_consultorio_imprimir_certificado.php?id=<?php echo $idColegiadoConsultorio; ?>&tipo=Centro" class="btn btn-default" role="button" target="_BLANK">Centro</a>                    
                                        <?php 
                                        }
                                    }
                                    ?> 
                                </td>
                                <td style="text-align: center;">
                                <?php 
                                if (!$_SESSION['user_entidad']['soloConsulta']) { 
                                ?>                      
                                    <a href="consultorio_form.php?idColegiado=<?php echo $idColegiado; ?>&id=<?php echo $idColegiadoConsultorio; ?>&accion=3" class="btn btn-info" role="button">Editar</a>
                                    <a href="consultorio_form.php?idColegiado=<?php echo $idColegiado; ?>&id=<?php echo $idColegiadoConsultorio; ?>&accion=2" class="btn btn-danger" role="button">Baja</a>
                                <?php 
                                }
                                ?>
                                </td>
                            </tr>
                        <?php
                        }
                        ?>
                    </tbody>
                </table>
                </div>
            </div>
        <?php
        } else {
        ?>
            <div class="<?php echo $resConsultorios['clase']; ?>" role="alert">
                <span class="<?php echo $resConsultorios['icono']; ?>" aria-hidden="true"></span>
                <span><strong><?php echo $resConsultorios['mensaje']; ?></strong></span>
            </div>        
        <?php        
        }
    } else {
    ?>
        <div class="<?php echo $resColegiado['clase']; ?>" role="alert">
            <span class="<?php echo $resColegiado['icono']; ?>" aria-hidden="true"></span>
            <span><strong><?php echo $resColegiado['mensaje']; ?></strong></span>
        </div>        
    <?php        
    }
}
require_once '../html/footer.php';
