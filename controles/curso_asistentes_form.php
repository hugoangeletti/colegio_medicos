<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/conection_pdo.php');
require_once ('../dataAccess/cursos_pdo.php');

$continua = TRUE;
$mensaje = "";
$accion = NULL;
$readOnly = NULL;
$requerido = NULL;
$cursos_pdo = new cursos_pdo();

if (isset($_POST['mensaje'])) {
?>
    <div class="ocultarMensaje"> 
        <p class="<?php echo $_POST['clase'];?>"><?php echo $_POST['mensaje'];?></p>  
    </div>
<?php
}
if (isset($_GET['idCurso']) && $_GET['idCurso'] <> "") {
    $idCurso = $_GET['idCurso'];
    $resCurso = $cursos_pdo->obtenerCursoPorId($idCurso);
    if ($resCurso['estado']) {
        $curso = $resCurso['datos'];
        $titulo = $curso['titulo'];
        $director = $curso['director'];
        $fechaInicio = $curso['fechaInicio'];
        $estadoCurso = $curso['estado'];
        $tema = $curso['tema'];
        $dias = $curso['dias'];
        $fechas = $curso['fechas'];
        $salon = $curso['salon'];
        $lugar = $curso['lugar'];
        $coordinador = $curso['coordinador'];
        $vigenciaHasta = $curso['vigenciaHasta'];
    } else {
        $continua = FALSE;
        $mensaje .= "ERROR->".$resCurso['mensaje'];
    }

    if (isset($_GET['id']) && $_GET['id'] <> "") {
        $idCursosAsistente = $_GET['id'];
        if (isset($_GET['editar'])) {
            $accion = "EDITAR";
            $requerido = "required";
        } else {
            $accion = "CONSULTAR";
            $readOnly = "readonly";
        }
    } else {
        if (isset($_GET['agregar'])) {
            $accion = "AGREGAR";
            $requerido = "required";
            $idCursosAsistente = NULL;

            $idColegiado = NULL;
            $apellidoNombre = NULL;
            $esColegiado = "S";
            if (isset($_POST['mensaje'])) {
                $idColegiado = $_POST['idColegiado'];
                $apellidoNombre = $_POST['apellidoNombre'];
                $esColegiado = $_POST['esColegiado'];
            }
        }
    }
} else {
    $continua = FALSE;
    $mensaje .= "Falta idCursosAsistente - ";
}

if (isset($accion)) {
    if (isset($idCursosAsistente) && $idCursosAsistente <> "") {
        $resAsistente = $cursos_pdo->obtenerAsistentePorId($idCursosAsistente);
        if ($resAsistente['estado']) {
            $asistente = $resAsistente['datos'];
            $apellidoNombre = $asistente['apellidoNombre'];
            $idColegiado = $curso['idColegiado'];
            $matricula = $curso['matricula'];
            if (isset($idColegiado) && $idColegiado <> "") {
                $esColegiado = "S";
            } else {
                $esColegiado = "N";
            }
        } else {
            $continua = FALSE;
            $mensaje .= "ERROR->".$resCurso['mensaje'];
        }
    }
} else {
    $continua = FALSE;
    $mensaje .= "Acceso incorrecto";
}        
if ($continua) {
    ?>
    <div class="panel panel-info">
        <div class="panel-heading">
            <div class="row">
                <div class="col-md-9">
                    <h5><?php echo $accion; ?> ASISTENTE DEL CURSO (#<?php echo $idCurso;?>) - <?php echo $titulo; ?>.</h5>
                </div>
                <div class="col-md-3 text-right">
                    <a href="curso_asistentes.php?idCurso=<?php echo $idCurso; ?>" class="btn btn-primary" >Volver</a>
                </div> 
            </div>
        </div>
        <div class="panel-body">
            <form id="datosCurso" name="datosCurso" method="POST" action="datosCurso\abm_curso_asistente.php">
                <div class="row">
                    <div class="col-md-2">
                        <label for="esColegiado">Es colegiado?</label>
                        <br>
                        <label class="radio-inline"><input type="radio" name="esColegiado" id="esColegiado" value="S" <?php if ($esColegiado == 'S') { ?> checked="" <?php } ?>>Si</label>
                        <label class="radio-inline"><input type="radio" name="esColegiado" id="esColegiado" value="N" <?php if ($esColegiado == 'N') { ?> checked="" <?php } ?>>No</label>
                    </div>
                    <div class="col-md-8" id="esUnColegiado">
                        <label for="colegiado_buscar">Buscar colegiado *</label>
                        <input class="form-control" autofocus autocomplete="OFF" type="text" name="colegiado_buscar" id="colegiado_buscar" placeholder="Ingrese Matrícula o Apellido del colegiado" />
                        <input type="hidden" name="idColegiado" id="idColegiado" />
                    </div>
                    <div class="col-md-6" id="noEsUnColegiado" style="display: none;">
                        <label for="apellidoNombre">Apellido y Nombre: *</label>
                        <input class="form-control" type="text" name="apellidoNombre" id="apellidoNombre" value="<?php echo $apellidoNombre; ?>" placeholder="Ingrese el Apellido y Nombre" <?php echo $readOnly; ?> />
                    </div>
                </div>
                <div class="row">&nbsp;</div>
                <?php 
                if ($accion <> "CONSULTAR") {
                ?>
                    <div class="row">&nbsp;</div>
                    <div class="row">
                        <div class="col-md-12 text-center">
                            <br>
                            <button type="submit" name='confirma' id='confirma' class="btn btn-primary">Guardar</button>
                            <input type="hidden" name="accion" id="accion" value="<?php echo $accion; ?>">
                            <input type="hidden" name="idCurso" id="idCurso" value="<?php echo $idCurso; ?>">
                        </div>
                    </div>
                <?php 
                } 
                ?>
            </form>
        </div>
    </div>
<?php
} else {
?>
    <div class="row">&nbsp;</div>
    <div class="alert alert-danger" role="alert">
        <span class="glyphicon glyphicon-remove" aria-hidden="true"></span>
        <span><strong><?php echo $mensaje; ?></strong></span>
    </div>        
    <div class="row">&nbsp;</div>
    <div class="row">
        <div class="col-md-12">
            <a href="curso_listado.php" class="btn btn-primary" >Volver</a>
        </div>
    </div>
<?php            
}
require_once '../html/footer.php';
?>
<!--AUTOCOMLETE-->
<script src="../public/js/bootstrap3-typeahead.js"></script>    
<script language="JavaScript">
    $(function(){
        var nameIdMap = {};
        $('#colegiado_buscar').typeahead({ 
                source: function (query, process) {
                return $.ajax({
                    dataType: "json",
                    url: 'colegiado.php',
                    data: {query: query},
                    type: 'POST',
                    success: function (json) {
                        process(getOptionsFromJson(json.data));
                    }
                });
            },
           
            minLength: 3,
            //maxItem:15,
            
            updater: function (item) {
                $('#idColegiado').val(nameIdMap[item]);
                return item;
            }
        });
        function getOptionsFromJson(json) {
             
            $.each(json, function (i, v) {
                //console.log(v);
                nameIdMap[v.nombre] = v.id;
            });
            return $.map(json, function (n, i) {
                return n.nombre;
            });
        }
    });  
    
        var lastSelected;
    $(function () {
        //if you have any radio selected by default
        lastSelected = $('[name="esColegiado"]:checked').val();
    });
    $(document).on('click', '[name="esColegiado"]', function () {
        if (lastSelected != $(this).val() && typeof lastSelected != "undefined") {
            var x = document.getElementById("esUnColegiado");
            var y = document.getElementById("noEsUnColegiado");
            //if (x.style.display === "none") {
            if (lastSelected != 'S') {
                x.style.display = "block";
                y.style.display = "none";
            } else {
                x.style.display = "none";
                y.style.display = "block";
            }
            //alert("radio box with value " + $('[name="conFirma"][value="' + lastSelected + '"]').val() + " was deselected");
        }
        lastSelected = $(this).val();
    });

</script>