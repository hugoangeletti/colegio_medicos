<button type="button" class="btn btn-danger" data-toggle="modal" data-target="#novedadesModal">Novedades</button>

<!-- Modal -->
<div id="novedadesModal" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header alert alert-info">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">NOVEDADES</h4>
      </div>
      <div class="modal-body">
          <p>
              <?php 
              if ($novedades) {
                  if ($resObservaciones['estado']) {
                      ?>
                        <h4>Observaciones</h4>
                        <table>
                            <thead>
<!--                                <th>Fecha</th>
                                <th>Observaciones</th>
                                <th>Realizó</th>-->
                            </thead>
                            <tbody>
                                <?php
                                foreach ($resObservaciones['datos'] as $observacion) {
                                    ?>
                                    <tr>
                                        <td><?php echo cambiarFechaFormatoParaMostrar($observacion['fechaCarga']); ?></td>
                                        <td><?php echo $observacion['observaciones']; ?></td>
                                        <td><?php echo $observacion['nombreUsuario']; ?></td>
                                    </tr>
                                <?php
                                }
                                ?>
                            </tbody>
                        </table>
                <?php
                  }
              }
              ?>
          </p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
      </div>
    </div>

  </div>
</div>        
