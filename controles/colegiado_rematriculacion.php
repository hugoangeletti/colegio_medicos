<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/colegiadoLogic.php');
require_once ('../dataAccess/colegiadoConsultorioLogic.php');
require_once ('../dataAccess/colegiadoRematriculacionLogic.php');
?>
<script>
    $(document).ready(
            function () {
                $('#tablaConsultorios').DataTable({
                    "iDisplayLength": 8,
                    "order": [[0, "desc"], [1, "asc"]],
                    "language": {
                        "url": "../public/lang/esp.lang"
                    },
                    "bLengthChange": false,
                    "bFilter": false,
                });
            }
    );

    $(document).ready(
            function () {
                $('#tablaActAsistencial').DataTable({
                    "iDisplayLength": 8,
                    "order": [[0, "desc"], [1, "asc"]],
                    "language": {
                        "url": "../public/lang/esp.lang"
                    },
                    "bLengthChange": false,
                    "bFilter": false,
                });
            }

    );
    $(document).ready(
            function () {
                $('#tablaEspecialidad').DataTable({
                    "iDisplayLength": 8,
                    "order": [[0, "desc"], [1, "asc"]],
                    "language": {
                        "url": "../public/lang/esp.lang"
                    },
                    "bLengthChange": false,
                    "bFilter": false,
                });
            }

    );
</script>
<?php
$colegiadoRematriculacionLogic = new colegiadoRematriculacionLogic();
if (isset($_GET['idColegiado'])) {
    $_SESSION['menuColegiado'] = "Rematriculacion";
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
                <label>Apellido y Nombres:&nbsp; </label><?php echo $colegiado['apellido'] . ', ' . $colegiado['nombre']; ?>
                <label>- Matr&iacute;cula:&nbsp; </label><?php echo $colegiado['matricula']; ?>
            </div>
            <div class="col-md-3"><h4><b>Datos de la Rematriculaci&oacute;n</b></h4></div>
            <div class="col-md-3">&nbsp;</div>
        </div>
        <?php
        //busco las especialidades
        $resRematriculacion = $colegiadoRematriculacionLogic->obtenerUltimaRematriculacionPorIdColegiado($idColegiado);
        if ($resRematriculacion['estado']) {
            $rematriculacion = $resRematriculacion['datos'];
            $idRematriculacionColegiado = $rematriculacion['idRematriculacionColegiado'];
            $fechaRematriculacion = cambiarFechaFormatoParaMostrar($rematriculacion['fecha']);
            ?>
            <h4>Fecha de última rematriculaci&oacute;n: <?php echo $fechaRematriculacion; ?> - N&uacute;mero: <?php echo $idRematriculacionColegiado ?></h4>
            <?php
            //busco los datos declarados en la rematriculacion
            $resConsultorios = $colegiadoRematriculacionLogic->obtenerDomicilioProfesionalPorIdRematriculacionColegiado($idRematriculacionColegiado);
            if ($resConsultorios['estado']) {
                ?>
                <h4><b>&blacktriangleright; Consultorios declarados: </b></h4>
                <div class="row">
                    <div class="col-md-12">
                        <table class="table table-striped">
                            <thead>
                                <tr style="color: green;">
                                    <th style="text-align: center; display: none;">Id</th>
                                    <th>Domicilio</th>
                                    <th>Localidad</th>
                                    <th>Entidad</th>
                                    <th>Origen</th>
                                </tr>
                            </thead>
                            <tbody>
                <?php
                foreach ($resConsultorios['datos'] as $dato) {
                    $idColegiadoConsultorio = $dato['id'];

                    if ($dato['calle']) {
                        $domicilioCompleto = $dato['calle'];
                        if ($dato['numero']) {
                            $domicilioCompleto .= " Nº " . $dato['numero'];
                        }
                        if ($dato['lateral']) {
                            $domicilioCompleto .= " e/ " . $dato['lateral'];
                        }
                        if ($dato['piso'] && strtoupper($dato['piso']) != "NR") {
                            $domicilioCompleto .= " Piso " . $dato['piso'];
                        }
                        if ($dato['departamento'] && strtoupper($dato['departamento']) != "NR") {
                            $domicilioCompleto .= " Dto. " . $dato['departamento'];
                        }
                    }
                    $localidad = $dato['nombreLocalidad'] . ' (' . $dato['codigoPostal'] . ')';
                    $entidad = $dato['entidad'].' '.$dato['nombreEntidad'];
                    $origen = 'Rematriculación';
                    if ($dato['idOrigenWeb']) {
                        $origen = 'Actualización sistema web';
                    }
                    ?>
                                    <tr>
                                        <td style="display: none"><?php echo $idColegiadoConsultorio; ?></td>
                                        <td><?php echo $domicilioCompleto; ?></td>
                                        <td><?php echo $localidad; ?></td>
                                        <td><?php echo $entidad; ?></td>
                                        <td><?php echo $origen; ?></td>
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
                <h4><b>&blacktriangleright; Consultorios declarados: </b><?php echo $resConsultorios['mensaje']; ?></h4>
                <?php
            }
            //busco los datos declarados en la actividad asistencial
            $resActAsistencial = $colegiadoRematriculacionLogic-> $colegiadoRematriculacionLogic->obtenerActividadAsistencialPorIdRematriculacionColegiado($idRematriculacionColegiado);
            if ($resActAsistencial['estado']) {
                ?>
                <h4><b>&blacktriangleright; Actividad asistencial declarada</b></h4>
                <div class="row">
                    <div class="col-md-12">
                        <table class="table table-striped">
                            <thead>
                                <tr style="color: green;">
                                    <th style="text-align: center; display: none;">Id</th>
                                    <th>Tipo Institución</th>
                                    <th>Cargo</th>
                                    <th>Servicio</th>
                                    <th>Fecha Desde/Hasta</th>
                                    <th>Institución / Establecimiento</th>
                                    <th>Tipo Entidad</th>
                                    <th>Origen</th>
                                </tr>
                            </thead>
                            <tbody>
                <?php
                foreach ($resActAsistencial['datos'] as $dato) {
                    $idActividadAsistencial = $dato['idActividadAsistencial'];
                    $tipoInstitucionDetalle = $dato['tipoInstitucionDetalle'];
                    $cargo = $dato['cargo'];
                    $servicio = $dato['servicio'];
                    $fechaDesdeHasta = $dato['fechaDesdeHasta'];
                    $nombreInstitucion = $dato['nombreInstitucion'];
                    $tipoEntidad = $dato['tipoEntidad'];
                    $nombreEntidad = $dato['nombreEntidad'];
                    $origen = 'Rematriculación';
                    /*
                    if ($dato['idOrigenWeb']) {
                        $origen = 'Actualización sistema web';
                    }
                    */
                    ?>
                    <tr>
                        <td style="display: none"><?php echo $idActividadAsistencial; ?></td>
                        <td><?php echo $tipoInstitucionDetalle; ?></td>
                        <td><?php echo $cargo; ?></td>
                        <td><?php echo $servicio; ?></td>
                        <td><?php echo $fechaDesdeHasta; ?></td>
                        <td><?php echo $nombreInstitucion . ' ' . $nombreEntidad; ?></td>
                        <td><?php echo $tipoEntidad; ?></td>
                        <td><?php echo $origen; ?></td>
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
                <h4><b>&blacktriangleright; Actividad asistencial declarada: </b><?php echo $resActAsistencial['mensaje']; ?></h4>
                <?php
            }
            
            //especialidades declaradas
            $resEspecialidades = $colegiadoRematriculacionLogic->obtenerEspecialidadesPorIdRematriculacionColegiado($idRematriculacionColegiado);
            if ($resEspecialidades['estado']) {
                ?>
                <h4><b>&blacktriangleright; Especialidad declarada</b></h4>
                <div class="row">
                    <div class="col-md-12">
                        <table class="table table-striped">
                            <thead>
                                <tr style="color: green;">
                                    <th style="text-align: center; display: none;">Id</th>
                                    <th>Especialidad </th>
                                    <th>Fecha </th>
                                    <th>Entidad </th>
                                    <th>Origen </th>
                                </tr>
                            </thead>
                            <tbody>
                <?php
                foreach ($resEspecialidades['datos'] as $dato) {
                    $idEspecialidadDeclarada = $dato['idEspecialidadDeclarada'];
                    $nombreEntidad = $dato['nombreEntidad'];
                    $fecha = $dato['fecha'];
                    $especialidad = $dato['especialidad'];
                    $origen = 'Rematriculación';
                    if ($dato['idOrigenWeb']) {
                        $origen = 'Actualización sistema web';
                    }
                    ?>
                                    <tr>
                                        <td style="display: none"><?php echo $idActividadAsistencial; ?></td>
                                        <td><?php echo $especialidad; ?></td>
                                        <td><?php echo $fecha; ?></td>
                                        <td><?php echo $nombreEntidad; ?></td>
                                        <td><?php echo $origen; ?></td>
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
                <h4><b>&blacktriangleright; Especialidad declarada: </b><?php echo $resEspecialidades['mensaje']; ?></h4>
                <?php
            }
        } else {
            ?>
            <div class="row">&nbsp;</div>
            <div class="<?php echo $resRematriculacion['clase']; ?>" role="alert">
                <span class="<?php echo $resRematriculacion['icono']; ?>" aria-hidden="true"></span>
                <span><strong><?php echo $resRematriculacion['mensaje']; ?></strong></span>
            </div>        
            <?php
        }
    } else {
        ?>
        <div class="row">&nbsp;</div>
        <div class="<?php echo $resColegiado['clase']; ?>" role="alert">
            <span class="<?php echo $resColegiado['icono']; ?>" aria-hidden="true"></span>
            <span><strong><?php echo $resColegiado['mensaje']; ?></strong></span>
        </div>        
        <?php
    }
}
require_once '../html/footer.php';
