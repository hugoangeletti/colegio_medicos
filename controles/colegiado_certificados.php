<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/usuarioLogic.php');
$usuarioLogic = new usuarioLogic();
require_once ('../dataAccess/colegiadoLogic.php');
require_once ('../dataAccess/colegiadoCertificadosLogic.php');
$colegiadoCertificadosLogic = new colegiadoCertificadosLogic();
?>
<script>
$(document).ready(
    function () {
                $('#tablaCertificados').DataTable({
                    "iDisplayLength":10,
                     "order": [[ 0, "desc" ], [ 1, "asc"]],
                    "language": {
                        "url": "../public/lang/esp.lang"
                    },
                    "bLengthChange": true,
                    "bFilter": true,
                    dom: 'T<"clear">lfrtip'
                });
    }
);

function confirmar()
{
	if(confirm('¿Estas seguro de elimiar esta entrega de recetarios?'))
		return true;
	else
		return false;
}
</script>
<?php
if (isset($_GET['idColegiado'])) {
    $_SESSION['menuColegiado'] = "Certificados";
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
            <div class="col-md-2"><h4><b>Certificados emitidos</b></h4></div>
            <div class="col-md-4 text-right">  
                <form id="formColegiado" name="formColegiado" method="POST" onSubmit="" action="colegiado_certificados_alta.php?idColegiado=<?php echo $idColegiado;?>">
                    <button type="submit"  class="btn btn-primary" >Nuevo certificado</button>
                </form>
            </div>
        </div>
        <?php
        //busco las especialidades
        $resCertificados = $colegiadoCertificadosLogic->obtenerCertificadosPorIdColegiado($idColegiado);
        if ($resCertificados['estado']){
        ?>
            <div class="row">
                <div class="col-md-12">
                <table  id="tablaCertificados" class="display">
                    <thead>
                        <tr>
                            <th style="text-align: center; display: none;">Id</th>
                            <th style="text-align: center;">Fecha Emisi&oacute;n</th>
                            <!--<th>Tipo</th>-->
                            <th>Entregar a</th>
                            <th>Mail enviado</th>
                            <th>Realizado por</th>
                            <th>Acci&oacute;n</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($resCertificados['datos'] as $dato){
                            $idColegiadoCertificado = $dato['idColegiadoCertificado'];
                            $fechaEmision= $dato['fechaEmision'];
                            $tipoCertificado = $dato['tipoCertificado'];
                            $entregar = $dato['entregar'];
                            if (!isset($entregar) || $entregar == "") {
                                $entregar = $tipoCertificado;
                            }
                            if (isset($dato['distrito']) && $dato['distrito'] <> "" && $dato['distrito'] <> "0") {
                                $entregar .= " (Distrito de cambio: ".$dato['distrito'].")";
                            }
                            $enviaMail = $dato['enviaMail'];
                            $mail = $dato['mail'];
                            $usuarioSolicitante = $dato['usuarioSolicitante']
                            ?>
                            <tr>
                                <td style="display: none"><?php echo $idColegiadoCertificado;?></td>
                                <td style="text-align: center;"><?php echo cambiarFechaFormatoParaMostrar($fechaEmision);?></td>
                                <!--<td><?php echo $tipoCertificado;?></td>-->
                                <td><?php echo $entregar;?></td>
                                <td><?php if (isset($enviaMail) && $enviaMail == "S") {
                                            echo $mail; 
                                        } else {
                                            echo 'NO';
                                        } ?></td>
                                <td><?php if (isset($usuarioSolicitante)) {
                                            echo $usuarioSolicitante; 
                                        } else {
                                            echo 'no identificado';
                                        } ?></td>
                                <td>
                                    <?php
                                    $fechaLimite = sumarRestarSobreFecha(date('Y-m-d'), 2, 'month', '-');
                                    if ($fechaEmision > $fechaLimite) {
                                        //datosColegiadoCertificado/imprimir_certificado.php?idCertificado=<?php echo $idColegiadoCertificado; 
                                    ?>
                                        <a href="colegiado_certificados_imprimir.php?id=<?php echo $idColegiadoCertificado; ?>" class="btn btn-info" role="button">
                                            <?php if (isset($enviaMail) && $enviaMail == "S") {
                                                echo "Enviar mail";
                                            } else {
                                                echo 'Imprimir';
                                            }
                                            ?>
                                        </a>
                                        <?php
                                        if ($usuarioLogic->verificarRolUsuario($_SESSION['user_id'], 35)){
                                        ?>
                                            <a href="datosColegiadoCertificado/abm_certificado.php?idCertificado=<?php echo $idColegiadoCertificado; ?>&accion=3&idColegiado=<?php echo $idColegiado; ?>" class="btn btn-danger" role="button" onclick="return confirmar()">Eliminar</a>                    
                                        <?php
                                        }
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
            <div class="<?php echo $resCertificados['clase']; ?>" role="alert">
                <span class="<?php echo $resCertificados['icono']; ?>" aria-hidden="true"></span>
                <span><strong><?php echo $resCertificados['mensaje']; ?></strong></span>
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
