<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesPhp.php');

$continua = TRUE;
$mensaje = "";
?>
<div class="panel panel-info">
    <div class="panel-heading">
        <div class="row">
            <div class="col-md-4">
                <h4>Listado de especialistas</h4>
            </div>
        </div>
    </div>
    <div class="panel-body">
        <div class="row">
            <form id="formEspecialistas" autocomplete="off" name="formEspecialistas" method="POST" onSubmit="" target="_blank" action="especialidades_listados_imprimir.php">
                <div class="row">
                    <div class="col-md-6">
                        <label for="especialidad_buscar">Especialidad solicitada: </label>
                        <input class="form-control" type="text" name="especialidad_buscar" id="especialidad_buscar" placeholder="Ingrese el nombre de la Especialidad solicitada" />
                        <input type="hidden" name="idEspecialidad" id="idEspecialidad" />
                    </div>
                </div>
                <div class="row">&nbsp;</div>
                <div class="row">
                    <div class="col-md-6">
                        <label class="control-label">Datos solicitados *</label>
                        <br>
                        <label class="radio-inline"><input type="radio" name="datosSolicitados" value="SOLO_FECHAS" checked >Solo fechas</label>
                        <label class="radio-inline"><input type="radio" name="datosSolicitados" value="FECHA_DOMICILIO" >Fechas y domicilio</label>
                        <label class="radio-inline"><input type="radio" name="datosSolicitados" value="DOMICILIO_CONTACTO" >Domicilio y contacto</label>
                        <label class="radio-inline"><input type="radio" name="datosSolicitados" value="SOLO_CONTACTO" >Solo contacto</label>
                    </div>
                    <div class="col-md-6">
                        <label class="control-label">Tipos de especialistas *</label>
                        <br>
                        <label class="radio-inline"><input type="radio" name="tipoEspecialista" value="TODOS" checked >Todos</label>
                        <label class="radio-inline"><input type="radio" name="tipoEspecialista" value="ESPECIALISTA" >Especialistas</label>
                        <label class="radio-inline"><input type="radio" name="tipoEspecialista" value="JERARQUIZADO" >Jerarquizados</label>
                        <label class="radio-inline"><input type="radio" name="tipoEspecialista" value="CONSULTOR" >Consultores</label>
                    </div>
                </div>
                <div class="row">&nbsp;</div>
                <div class="row">
                    <div class="col-md-6">
                        <label class="control-label">Estado matricular *</label>
                        <br>
                        <label class="radio-inline"><input type="radio" name="estadoMatricular" value="ACTIVOS" checked >Activos</label>
                        <label class="radio-inline"><input type="radio" name="estadoMatricular" value="ACTIVOS_INSCRIPTOS" >Activos e Incriptos</label>
                        <label class="radio-inline"><input type="radio" name="estadoMatricular" value="TODOS" >Todos</label>
                    </div>
                    <div class="col-md-6">
                        <label class="control-label">Colegiados en *</label>
                        <br>
                        <label class="radio-inline"><input type="radio" name="colegiadoEn" value="TODOS" checked >Todos los distritos</label>
                        <label class="radio-inline"><input type="radio" name="colegiadoEn" value="DISTRITO_I" >Distrito I</label>
                    </div>
                </div>
                <!--<div class="row">&nbsp;</div>
                <div class="row">
                    <div class="col-md-4">
                        <label class="control-label">Con caducidad *</label>
                        <br>
                        <label class="radio"><input type="radio" name="conCaducidad" value="SI" >Si</label>
                        <label class="radio"><input type="radio" name="conCaducidad" value="NO" >No</label>
                        <label class="radio"><input type="radio" name="conCaducidad" value="TODOS" >Todos</label>
                        <label class="radio"><input type="radio" name="conCaducidad" value="VENCIDOS_AL" >Vencidos al</label>
                        <input class="form-control" type="date" name="fechaCaducidad" id="fechaCaducidad" max="<?php echo date('Y-m-d'); ?>" >
                        <label class="radio"><input type="radio" name="entreFecas" value="ENTRE_FECHAS" >Vencidos al</label>
                        <input class="form-control" type="date" name="fechaDesde" id="fechaDesde" max="<?php echo date('Y-m-d'); ?>" >
                        <input class="form-control" type="date" name="fechaHasta" id="fechaHasta" max="<?php echo date('Y-m-d'); ?>" >
                    </div>
                </div>-->
                <div class="row">&nbsp;</div>
                <div class="row">
                    <div class="col-md-12 text-center">
                        <button type="submit"  class="btn btn-success">Generar listado</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
<?php
require_once '../html/footer.php';
?>
<!--AUTOCOMLETE-->
<script src="../public/js/bootstrap3-typeahead.js"></script>    
<script language="JavaScript">
    $(function(){
        var nameIdMap = {};
        $('#especialidad_buscar').typeahead({ 
                source: function (query, process) {
                return $.ajax({
                    dataType: "json",
                    url: 'especialidad.php?todos',
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
                $('#idEspecialidad').val(nameIdMap[item]);
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
    
</script>