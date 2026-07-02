    <div class="col-md12"><h6>Tesorería -> Cuotas de colegiación -> Imprimir chequera</h6></div>
    <div class="container-fluid p-3" style="background-color: #8699a4 ; color: white">
        <div class="row">
            <div class="col-md-5">
                <h5><?php echo $_SESSION['apellidoNombre']; ?></h5>
                <h5>M.P. <?php echo $_SESSION['matricula']; ?></h5>
            </div>
            <div class="col-md-5">
            </div>
            <div class="col-md-2">
                <a href="cuotasColegiacion.php?id=<?php echo $idColegiado; ?>" class="btn btn-dark">Volver</a>
            </div>
        </div>
    </div>
    <div class="container-fluid p-3" >
       <embed src='data:application/pdf;base64,<?php echo $chequera; ?>' height="600px" width='100%' type='application/pdf'>   
    </div> 
