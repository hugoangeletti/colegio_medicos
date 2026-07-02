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

$idUsuario = $_SESSION['user_id'];
$colegiadoRematriculacionLogic = new colegiadoRematriculacionLogic();
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

    function confirmar()
    {
        if(confirm('¿Estas seguro de elimiar el registro?'))
            return true;
        else
            return false;
    }

</script>
<?php
if (isset($_POST['mensaje'])) {
?>
    <div class="ocultarMensaje"> 
        <p class="<?php echo $_POST['clase'];?>"><?php echo $_POST['mensaje'];?></p>  
    </div>
<?php
}
if (isset($_GET['idColegiado'])) {
    $_SESSION['menuColegiado'] = "DatosProfesionales";
    $periodoActual = $_SESSION['periodoActual'];
    $idColegiado = $_GET['idColegiado'];
    $colegiadoLogic = new colegiadoLogic();
    $resColegiado = $colegiadoLogic->obtenerColegiadoPorId($idColegiado);
    if ($resColegiado['estado'] && $resColegiado['datos']) {
        $colegiado = $resColegiado['datos'];
        $fechaMatriculacion = $colegiado['fechaMatriculacion'];
        $muestraMenuCompleto = TRUE;
        include 'menuColegiado.php';
        ?>
        <div class="row">&nbsp;</div>
        <div class="row">
            <div class="col-md-6">
                <label>Apellido y Nombres:&nbsp; </label><?php echo $colegiado['apellido'] . ', ' . $colegiado['nombre']; ?>
                <label>- Matr&iacute;cula:&nbsp; </label><?php echo $colegiado['matricula']; ?>
            </div>
            <div class="col-md-3"><h4><b>Datos Profesionales</b></h4></div>
            <div class="col-md-3">&nbsp;</div>
        </div>
        <?php
        if (isset($idColegiado)) {
            //busco los datos declarados en la rematriculacion
            $resConsultorios = $colegiadoRematriculacionLogic->obtenerDomicilioProfesionalPorIdColegiado($idColegiado);
            if ($resConsultorios['estado']) {
                ?>
                <div class="row">
                    <div class="col-md-10">
                        <h4><b>&blacktriangleright; Consultorios declarados: <?php if (!isset($resConsultorios['datos'])) { echo $resConsultorios['mensaje']; } ?></b></h4>
                    </div>
                    <div class="col-md-2">
                        <?php 
                        if ($usuarioLogic->verificarRolUsuario($idUsuario, 91)) {
                        ?>
                            <a href="datos_profesionales_consultorio_form.php?idColegiado=<?php echo $idColegiado; ?>&accion=1" class="btn btn-primary btn-sm">Agregar consultorio</a>
                        <?php 
                        } 
                        ?>
                    </div>
                </div>
                <?php 
                if (isset($resConsultorios['datos'])) {
                ?>
                    <div class="row">
                        <div class="col-md-12">
                            <table class="table table-striped">
                                <thead>
                                    <tr style="color: green;">
                                        <th style="text-align: center; display: none;">Id</th>
                                        <th>Domicilio</th>
                                        <th>Localidad</th>
                                        <th>Entidad</th>
                                        <th>Fecha Carga</th>
                                        <th>Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    foreach ($resConsultorios['datos'] as $dato) {
                                        $idColegiadoConsultorio = $dato['id'];

                                        $domicilioCompleto = "";
                                        if (isset($dato['calle']) && $dato['calle'] <> "") {
                                            $domicilioCompleto = $dato['calle'];
                                            if (isset($dato['numero']) && $dato['numero'] <> "") {
                                                $domicilioCompleto .= " Nº " . $dato['numero'];
                                            }
                                            if (isset($dato['lateral']) && $dato['lateral'] <> "") {
                                                $domicilioCompleto .= " e/ " . $dato['lateral'];
                                            }
                                            if (isset($dato['piso']) && $dato['piso'] <> "" && strtoupper($dato['piso']) != "NR") {
                                                $domicilioCompleto .= " Piso " . $dato['piso'];
                                            }
                                            if (isset($dato['departamento']) && $dato['departamento'] <> "" && strtoupper($dato['departamento']) != "NR") {
                                                $domicilioCompleto .= " Dto. " . $dato['departamento'];
                                            }
                                        } 
                                        $localidad = $dato['nombreLocalidad']; 
                                        if (isset($dato['codigoPostal']) && $dato['codigoPostal'] <> "") {
                                            $localidad .= ' (' . $dato['codigoPostal'] . ')';
                                        }
                                        $entidad = $dato['entidad'];
                                        $nombreEntidad = $dato['nombreEntidad'];
                                        $fechaCarga = $dato['fechaCarga'];
                                        if (isset($entidad) && $entidad <> "") {
                                            $nombreEntidad .= $entidad;
                                        }

                                        ?>
                                        <tr>
                                            <td style="display: none"><?php echo $idColegiadoConsultorio; ?></td>
                                            <td><?php echo $domicilioCompleto; ?></td>
                                            <td><?php echo $localidad; ?></td>
                                            <td><?php echo $nombreEntidad; ?></td>
                                            <td><?php echo cambiarFechaFormatoParaMostrar($fechaCarga); ?></td>
                                            <td>
                                                <?php 
                                                if ($usuarioLogic->verificarRolUsuario($idUsuario, 91)) {
                                                ?>
                                                    <a href="datos_profesionales_consultorio_form.php?id=<?php echo $idColegiadoConsultorio; ?>&accion=3" class="btn btn-primary btn-sm">Modificar</a>
                                                    <a href="datosRematriculacion/abm_consultorio.php?id=<?php echo $idColegiadoConsultorio; ?>&accion=2" class="btn btn-primary btn-sm" onclick="return confirmar()">Borrar</a>
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
                }
            } else {
                ?>
                <h4><b>&blacktriangleright; Consultorios declarados: </b><?php echo $resConsultorios['mensaje']; ?></h4>
                <?php
            }
            
            //busco los datos declarados en la actividad asistencial
            $resActAsistencial = $colegiadoRematriculacionLogic->obtenerActividadAsistencialPorIdColegiado($idColegiado);
            if ($resActAsistencial['estado']) {
                ?>
                <div class="row">
                    <div class="col-md-10">
                        <h4><b>&blacktriangleright; Actividad asistencial declarada: </b><?php if (!isset($resActAsistencial['datos'])) { echo $resActAsistencial['mensaje']; }?></h4>
                    </div>
                    <div class="col-md-2">
                        <?php 
                        if ($usuarioLogic->verificarRolUsuario($idUsuario, 91)) {
                        ?>
                            <a href="datos_profesionales_actividad_form.php?idColegiado=<?php echo $idColegiado; ?>&accion=1" class="btn btn-primary btn-sm">Agregar actividad asistencial</a>
                        <?php 
                        } 
                        ?>
                    </div>
                </div>
                <?php 
                if (isset($resActAsistencial['datos'])) {
                ?>
                    <div class="row">
                        <div class="col-md-12">
                            <table class="table table-striped">
                                <thead>
                                    <tr style="color: green;">
                                        <th style="text-align: center; display: none;">Id</th>
                                        <th>Tipo Instituci&oacute;n</th>
                                        <th>Cargo</th>
                                        <th>Servicio</th>
                                        <th>Fecha Desde/Hasta</th>
                                        <th>Instituci&oacute;n / Establecimiento</th>
                                        <th>Tipo Entidad</th>
                                        <th>Fecha Carga</th>
                                        <th>Acción</th>
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
                                        if (!isset($nombreEntidad) || $nombreEntidad == "") {
                                            $nombreEntidad = $nombreInstitucion;
                                        }
                                        $fechaCarga = $dato['fechaCarga'];
                                        ?>
                                        <tr>
                                            <td style="display: none"><?php echo $idActividadAsistencial; ?></td>
                                            <td><?php echo $tipoInstitucionDetalle; ?></td>
                                            <td><?php echo $cargo; ?></td>
                                            <td><?php echo $servicio; ?></td>
                                            <td><?php echo $fechaDesdeHasta; ?></td>
                                            <td><?php echo $nombreEntidad; ?></td>
                                            <td><?php echo $tipoEntidad; ?></td>
                                            <td><?php echo cambiarFechaFormatoParaMostrar($fechaCarga); ?></td>
                                            <td>
                                                <?php 
                                                if ($usuarioLogic->verificarRolUsuario($idUsuario, 91)) {
                                                ?>
                                                    <a href="datos_profesionales_actividad_form.php?id=<?php echo $idActividadAsistencial; ?>&accion=3" class="btn btn-primary btn-sm">Modificar</a>
                                                    <a href="datosRematriculacion/abm_actividad_asistencial.php?id=<?php echo $idActividadAsistencial; ?>&accion=2" class="btn btn-primary btn-sm" onclick="return confirmar()">Borrar</a>
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
                }
            } else {
                ?>
                <h4><b>&blacktriangleright; Actividad asistencial declarada: </b><?php echo $resActAsistencial['mensaje']; ?></h4>
                <?php
            }
            
            /*
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
            */
        } else {
        ?>
        <?php
            //<h4><b>&blacktriangleright; Consultorios declarados: </b></h4>
            //<h4><b>&blacktriangleright; Actividad asistencial declarada</b></h4>
            //<h4><b>&blacktriangleright; Especialidad declarada: </b></h4>
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
