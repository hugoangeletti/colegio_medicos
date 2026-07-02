<?php
$link = 'mesa_entrada_listado.php';
if ($accedePor == 'notas') {
    $link = 'mesa_entrada_notas_listado.php';
} else {
    if ($accedePor == 'NUEVA_MATRICULA') {
    ?>
        <button onclick="window.close()" class="btn btn-primary">Cerrar esta pestaña</button>
        <?php
        $link = NULL;
    }
}
if (isset($link)) {
?>
    <form method="POST" action="<?php echo $link; ?>">
        <button type="submit"  class="btn btn-info " >Volver</button>
        <?php 
        switch ($accedePor) {
            case 'FECHA':
                ?>
                <input type="hidden" name="fechaIngreso" id="fechaIngreso" value="<?php echo $fechaIngreso ?>">
                <?php
                break;
            
            case 'FECHA_TIPO':
                ?>
                <input type="hidden" name="fechaIngreso" id="fechaIngreso" value="<?php echo $fechaIngreso ?>">
                <input type="hidden" name="idTipoMesaEntradaSeleccionada" id="idTipoMesaEntradaSeleccionada" value="<?php echo $idTipoMesaEntrada ?>">
                <?php
                break;
            
            case 'COLEGIADO':
                ?>
                <input type="hidden" name="idColegiado" id="idColegiado" value="<?php echo $idColegiado ?>">
                <?php
                break;
            
            case 'OTRO':
                ?>
                <input type="hidden" name="idRemitente" id="idRemitente" value="<?php echo $idRemitente ?>">
                <?php
                break;
            
            default:
                // code...
                break;
        }
        ?>
    </form>
<?php
}
?>