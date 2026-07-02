<?php
?>
<div class="row">
    <form id="formColegiado" name="formColegiado" method="POST" onSubmit="" action="<?php echo $link_form_origen; ?>">
        <div class="row">
            <div class="col-md-3" style="text-align: right;">
                <label>Matr&iacute;cula o Apellido y Nombre *</label>
            </div>
            <div class="col-md-7">
                <input class="form-control" autofocus autocomplete="OFF" type="text" name="colegiado_buscar" id="colegiado_buscar" placeholder="Ingrese Matrícula o Apellido del colegiao" required=""/>
                <input type="hidden" name="idColegiado" id="idColegiado" required="" />
            </div>
            <div class="col-md-2">
                <button type="submit"  class="btn btn-success">Confirma colegiado</button>
                <input type="hidden" name="origen" id="origen" value="nuevo" />
            </div>
        </div>
    </form>
</div>

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
</script>    
    
