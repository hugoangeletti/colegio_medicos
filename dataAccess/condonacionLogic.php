<?php
class condonacionLogic {

    private $deudaAnualLogic;
    private $planPagoLogic;

    private function getDeudaAnualLogic() {
        if (!$this->deudaAnualLogic) {
            $this->deudaAnualLogic = new colegiadoDeudaAnualLogic();
        }
        return $this->deudaAnualLogic;
    }

    private function getPlanPagoLogic() {
        if (!$this->planPagoLogic) {
            $this->planPagoLogic = new colegiadoPlanPagoLogic();
        }
        return $this->planPagoLogic;
    }

    public function obtenerCondonacionesPorEstado($estado){
        $db = Database::getConnection();
        $sql = "SELECT solicitudcondonacion.Id, solicitudcondonacion.FechaSolicitud, usuario.Usuario,
                responsable.Nombre, solicitudcondonacion.Observacion, solicitudcondonacion.QueCondona,
                solicitudcondonacion.IdColegiado, colegiado.Matricula, persona.Apellido, persona.Nombres,
                tipocondonacion.Nombre AS NombreTipoCondonacion
                FROM solicitudcondonacion
                INNER JOIN colegiado on(colegiado.Id = solicitudcondonacion.IdColegiado)
                INNER JOIN persona ON(persona.Id = colegiado.IdPersona)
                INNER JOIN tipocondonacion ON(tipocondonacion.Id = solicitudcondonacion.IdTipoCondonacion)
                INNER JOIN usuario ON(usuario.Id = solicitudcondonacion.IdUsuario)
                INNER JOIN responsable ON(responsable.Id = solicitudcondonacion.IdResponsableCondonacion)
                WHERE solicitudcondonacion.EstadoCondonacion = ?";

        $resultado = array();
        try {
            $stmt = $db->prepare($sql);
            $stmt->execute([$estado]);
            $dados = $stmt->fetchAll();
            if (count($dados) > 0) {
                $datos = array();
                foreach ($dados as $row) {
                    $queCondona = $row['QueCondona'];
                    if ($queCondona == 'P') {
                        $queCondona = 'Cuotas de Plan de Pagos';
                    } else {
                        $queCondona = 'Cuotas de colegiacion';
                    }
                    $datos[] = array(
                        'idCondonacion' => $row['Id'],
                        'fechaSolicitud' => $row['FechaSolicitud'],
                        'usuario' => $row['Usuario'],
                        'responsable' => $row['Nombre'],
                        'realizo' => $row['Usuario'],
                        'queCondona' => $queCondona,
                        'observacion' => $row['Observacion'],
                        'matricula' => $row['Matricula'],
                        'apellidoNombre' => $row['Apellido'].' '.$row['Nombres'],
                        'idColegiado' => $row['IdColegiado'],
                        'motivo' => $row['NombreTipoCondonacion']
                    );
                }
                $resultado['estado'] = TRUE;
                $resultado['mensaje'] = "OK";
                $resultado['datos'] = $datos;
                $resultado['clase'] = 'alert alert-success';
                $resultado['icono'] = 'glyphicon glyphicon-ok';
            } else {
                $resultado['estado'] = FALSE;
                $resultado['mensaje'] = "No hay Condonaciones";
                $resultado['clase'] = 'alert alert-warning';
                $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
            }
        } catch (PDOException $e) {
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "Error buscando Condonaciones";
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-remove';
        }
        return $resultado;
    }

    public function obtenerCondonacionPorId($idCondonacion){
        $db = Database::getConnection();
        $sql = "SELECT solicitudcondonacion.Id, solicitudcondonacion.FechaSolicitud, usuario.Usuario,
                responsable.Nombre, solicitudcondonacion.Observacion, solicitudcondonacion.QueCondona,
                solicitudcondonacion.IdColegiado, solicitudcondonacion.EstadoCondonacion,
                colegiado.Matricula, persona.Apellido, persona.Nombres,
                tipocondonacion.Nombre AS NombreTipoCondonacion
                FROM solicitudcondonacion
                INNER JOIN colegiado on(colegiado.Id = solicitudcondonacion.IdColegiado)
                INNER JOIN persona ON(persona.Id = colegiado.IdPersona)
                INNER JOIN tipocondonacion ON(tipocondonacion.Id = solicitudcondonacion.IdTipoCondonacion)
                INNER JOIN usuario ON(usuario.Id = solicitudcondonacion.IdUsuario)
                INNER JOIN responsable ON(responsable.Id = solicitudcondonacion.IdResponsableCondonacion)
                WHERE solicitudcondonacion.Id = ?";

        $resultado = array();
        try {
            $stmt = $db->prepare($sql);
            $stmt->execute([$idCondonacion]);
            $row = $stmt->fetch();
            if ($row) {
                $datos = array();
                $datos['idCondonacion'] = $row['Id'];
                $datos['fechaSolicitud'] = $row['FechaSolicitud'];
                $datos['realizo'] = $row['Usuario'];
                $datos['responsable'] = $row['Nombre'];
                $datos['observacion'] = $row['Observacion'];
                $datos['queCondona'] = $row['QueCondona'];
                $datos['idColegiado'] = $row['IdColegiado'];
                $datos['estado'] = $row['EstadoCondonacion'];
                $datos['matricula'] = $row['Matricula'];
                $datos['apellidoNombre'] = trim($row['Apellido']).' '.trim($row['Nombres']);
                $datos['motivo'] = $row['NombreTipoCondonacion'];
                $resultado['estado'] = TRUE;
                $resultado['mensaje'] = "OK";
                $resultado['datos'] = $datos;
                $resultado['clase'] = 'alert alert-success';
                $resultado['icono'] = 'glyphicon glyphicon-ok';
            } else {
                $resultado['estado'] = FALSE;
                $resultado['mensaje'] = "No se encontro la condonacion";
                $resultado['clase'] = 'alert alert-info';
                $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
            }
        } catch (PDOException $e) {
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "Error buscando condonacion";
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-remove';
        }
        return $resultado;
    }

    public function obtenerCondonacionDetalle($idCondonacion){
        $db = Database::getConnection();
        $sql = "SELECT solicitudcondonaciondetalle.Id, colegiadodeudaanual.Periodo,
                colegiadodeudaanualcuotas.Cuota AS CuotaColegiacion,
                colegiadodeudaanualcuotas.Importe AS ImporteColegiacion,
                colegiadodeudaanualcuotas.FechaVencimiento,
                planpagoscuotas.IdPlanPagos,
                planpagoscuotas.Cuota AS CuotaPP,
                planpagoscuotas.Importe AS ImportePP,
                planpagoscuotas.Vencimiento
                FROM solicitudcondonaciondetalle
                LEFT JOIN colegiadodeudaanualcuotas ON(colegiadodeudaanualcuotas.Id = solicitudcondonaciondetalle.IdColegiadoDeudaCondonada)
                LEFT JOIN colegiadodeudaanual ON(colegiadodeudaanual.Id = colegiadodeudaanualcuotas.IdColegiadoDeudaAnual)
                LEFT JOIN planpagoscuotas ON(planpagoscuotas.Id = solicitudcondonaciondetalle.IdColegiadoDeudaCondonadaAnterior)
                WHERE solicitudcondonaciondetalle.IdSolicitudCondonacion = ?";

        $resultado = array();
        try {
            $stmt = $db->prepare($sql);
            $stmt->execute([$idCondonacion]);
            $dados = $stmt->fetchAll();
            if (count($dados) > 0) {
                $datos = array();
                foreach ($dados as $row) {
                    $periodo = $row['Periodo'];
                    $cuotaColegiacion = $row['CuotaColegiacion'];
                    $importeColegiacion = $row['ImporteColegiacion'];
                    $vencimientoColegiacion = $row['FechaVencimiento'];
                    $idPlanPago = $row['IdPlanPagos'];
                    $cuotaPP = $row['CuotaPP'];
                    $importePP = $row['ImportePP'];
                    $vencimientoPP = $row['Vencimiento'];
                    if ($periodo == NULL) {
                        $queCondona = 'Cuotas de Plan de Pagos';
                        $laCuota = $idPlanPago.'-'.$cuotaPP;
                        $importe = $importePP;
                        $vencimiento = $vencimientoPP;
                    } else {
                        $queCondona = 'Cuotas de colegiacion';
                        $laCuota = $periodo.'-'.$cuotaColegiacion;
                        $importe = $importeColegiacion;
                        $vencimiento = $vencimientoColegiacion;
                    }
                    $datos[] = array(
                        'idCondonacionDetalle' => $row['Id'],
                        'queCondona' => $queCondona,
                        'laCuota' => $laCuota,
                        'importe' => $importe,
                        'vencimiento' => $vencimiento
                    );
                }
                $resultado['estado'] = TRUE;
                $resultado['mensaje'] = "OK";
                $resultado['datos'] = $datos;
                $resultado['clase'] = 'alert alert-success';
                $resultado['icono'] = 'glyphicon glyphicon-ok';
            } else {
                $resultado['estado'] = FALSE;
                $resultado['mensaje'] = "No hay Detalle de Condonacion";
                $resultado['clase'] = 'alert alert-warning';
                $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
            }
        } catch (PDOException $e) {
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "Error buscando Detalle de la Condonacion";
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-remove';
        }
        return $resultado;
    }

    public function obtenerResponsables(){
        $db = Database::getConnection();
        $sql = "SELECT * FROM responsable WHERE Estado = 'A' ORDER BY Id desc";

        $resultado = array();
        try {
            $stmt = $db->prepare($sql);
            $stmt->execute();
            $dados = $stmt->fetchAll();
            if (count($dados) > 0) {
                $datos = array();
                foreach ($dados as $row) {
                    $datos[] = array(
                        'id' => $row['Id'],
                        'nombre' => $row['Nombre']
                    );
                }
                $resultado['estado'] = TRUE;
                $resultado['mensaje'] = "OK";
                $resultado['datos'] = $datos;
                $resultado['clase'] = 'alert alert-success';
                $resultado['icono'] = 'glyphicon glyphicon-ok';
            } else {
                $resultado['estado'] = FALSE;
                $resultado['mensaje'] = "No hay Responsables";
                $resultado['clase'] = 'alert alert-warning';
                $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
            }
        } catch (PDOException $e) {
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "Error buscando Responsables";
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-remove';
        }
        return $resultado;
    }

    public function obtenerTipoCondonacion(){
        $db = Database::getConnection();
        $sql = "SELECT * FROM tipocondonacion ORDER BY Nombre";

        $resultado = array();
        try {
            $stmt = $db->prepare($sql);
            $stmt->execute();
            $dados = $stmt->fetchAll();
            if (count($dados) > 0) {
                $datos = array();
                foreach ($dados as $row) {
                    $datos[] = array(
                        'id' => $row['Id'],
                        'nombre' => $row['Nombre']
                    );
                }
                $resultado['estado'] = TRUE;
                $resultado['mensaje'] = "OK";
                $resultado['datos'] = $datos;
                $resultado['clase'] = 'alert alert-success';
                $resultado['icono'] = 'glyphicon glyphicon-ok';
            } else {
                $resultado['estado'] = FALSE;
                $resultado['mensaje'] = "No hay Motivos cargados";
                $resultado['clase'] = 'alert alert-warning';
                $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
            }
        } catch (PDOException $e) {
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "Error buscando Motivos";
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-remove';
        }
        return $resultado;
    }

    public function agregarColegiadoCondonacion($idColegiado, $idResponsable, $idTipoCondonacion, $observaciones, $todas, $lasCuotas, $lasCuotasPP){
        $db = Database::getConnection();
        try {
            $db->beginTransaction();
            $sql = "INSERT INTO solicitudcondonacion (FechaSolicitud, IdTipoCondonacion, IdUsuario, IdResponsableCondonacion,
                EstadoCondonacion, Observacion, QueCondona, IdColegiado)
                VALUES (date(now()), ?, ?, ?, 'C', ?, 'T', ?)";
            $stmt = $db->prepare($sql);
            $stmt->execute([$idTipoCondonacion, $_SESSION['user_id'], $idResponsable, $observaciones, $idColegiado]);
            $resultado = array();
            $resultado['estado'] = TRUE;
            $resultado['mensaje'] = 'LA CONDONACION SE GENERO CORRECTAMENTE';
            $idCondonacion = $db->lastInsertId();

            if ($todas == 'S') {
                $resDeuda = $this->getDeudaAnualLogic()->obtenerColegiadoDeudaAnualAPagar($idColegiado);
                if ($resDeuda['estado']) {
                    $i = 0;
                    foreach ($resDeuda['datos'] as $value) {
                        $lasCuotas[$i] = $value['idColegiadoDeudaAnualCuota'];
                        $i++;
                    }
                } else {
                    $lasCuotas = array();
                }
            }

            $hayCuotas = 0;
            foreach ($lasCuotas as $value) {
                $sql = "INSERT INTO solicitudcondonaciondetalle (IdSolicitudCondonacion, IdColegiadoDeudaCondonada)
                        VALUES (?, ?)";
                $stmt = $db->prepare($sql);
                $stmt->execute([$idCondonacion, $value]);
                if ($stmt->rowCount() == 0) {
                    $resultado['estado'] = FALSE;
                    $resultado['mensaje'] = "ERROR AL GENERAR EL DETALLE";
                    $resultado['clase'] = 'alert alert-danger';
                    $resultado['icono'] = 'glyphicon glyphicon-remove';
                    break;
                }
                $hayCuotas++;
            }
            if ($resultado['estado'] && $hayCuotas > 0) {
                $sql = "UPDATE colegiadodeudaanualcuotas, solicitudcondonaciondetalle
                        SET colegiadodeudaanualcuotas.Estado=4
                        WHERE solicitudcondonaciondetalle.IdSolicitudCondonacion = ?
                        AND solicitudcondonaciondetalle.IdColegiadoDeudaCondonada = colegiadodeudaanualcuotas.id";
                $stmt = $db->prepare($sql);
                $stmt->execute([$idCondonacion]);
            }
            if ($resultado['estado']) {
                if ($todas == 'S') {
                    $resDeudaPP = $this->getPlanPagoLogic()->obtenerDeudaPlanPagosPorIdColegiado($idColegiado);
                    if ($resDeudaPP['estado']) {
                        $i = 0;
                        foreach ($resDeudaPP['datos'] as $value) {
                            $lasCuotasPP[$i] = $value['idPlanPagosCuotas'];
                            $i++;
                        }
                    } else {
                        $lasCuotasPP = array();
                    }
                }
                $hayCuotas = 0;
                foreach ($lasCuotasPP as $value) {
                    $sql = "INSERT INTO solicitudcondonaciondetalle (IdSolicitudCondonacion, IdColegiadoDeudaCondonadaAnterior)
                            VALUES (?, ?)";
                    $stmt = $db->prepare($sql);
                    $stmt->execute([$idCondonacion, $value]);
                    if ($stmt->rowCount() == 0) {
                        $resultado['estado'] = FALSE;
                        $resultado['mensaje'] = "ERROR AL GENERAR EL DETALLE";
                        $resultado['clase'] = 'alert alert-danger';
                        $resultado['icono'] = 'glyphicon glyphicon-remove';
                        break;
                    }
                    $hayCuotas++;
                }
                if ($resultado['estado'] && $hayCuotas > 0) {
                    $sql = "UPDATE planpagoscuotas, solicitudcondonaciondetalle
                            SET planpagoscuotas.IdTipoEstadoCuota=4
                            WHERE solicitudcondonaciondetalle.IdSolicitudCondonacion = ?
                            AND solicitudcondonaciondetalle.IdColegiadoDeudaCondonadaAnterior = planpagoscuotas.id";
                    $stmt = $db->prepare($sql);
                    $stmt->execute([$idCondonacion]);
                }
            }

            if ($resultado['estado']) {
                $resultado['mensaje'] .= '('.$idCondonacion.')';
                $resultado['idCondonacion'] = $idCondonacion;
                $resultado['clase'] = 'alert alert-success';
                $resultado['icono'] = 'glyphicon glyphicon-ok';
                $db->commit();
                return $resultado;
            } else {
                $db->rollBack();
                return $resultado;
            }

        } catch (PDOException $e) {
            $db->rollBack();
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "ERROR AL GENERAR PLAN DE PAGOS";
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-remove';
            return $resultado;
        }
    }

    public function anularCondonacion($idCondonacion){
        $db = Database::getConnection();
        try {
            $db->beginTransaction();
            $resultado['estado'] = TRUE;

            $sql = "UPDATE colegiadodeudaanualcuotas, solicitudcondonaciondetalle
                    SET colegiadodeudaanualcuotas.Estado = 1
                    WHERE solicitudcondonaciondetalle.IdSolicitudCondonacion = ?
                    AND solicitudcondonaciondetalle.IdColegiadoDeudaCondonada = colegiadodeudaanualcuotas.id";
            $stmt = $db->prepare($sql);
            $stmt->execute([$idCondonacion]);

            if ($resultado['estado']) {
                $sql = "UPDATE planpagoscuotas, solicitudcondonaciondetalle
                        SET planpagoscuotas.IdTipoEstadoCuota = 1
                        WHERE solicitudcondonaciondetalle.IdSolicitudCondonacion = ?
                        AND solicitudcondonaciondetalle.IdColegiadoDeudaCondonadaAnterior = planpagoscuotas.id";
                $stmt = $db->prepare($sql);
                $stmt->execute([$idCondonacion]);

                if ($resultado['estado']) {
                    $sql = "UPDATE solicitudcondonacion SET EstadoCondonacion = 'B' WHERE Id = ?";
                    $stmt = $db->prepare($sql);
                    $stmt->execute([$idCondonacion]);

                    if ($resultado['estado']) {
                        $sql = "DELETE FROM solicitudcondonaciondetalle WHERE IdSolicitudCondonacion = ?";
                        $stmt = $db->prepare($sql);
                        $stmt->execute([$idCondonacion]);
                    }
                }
            }

            if ($resultado['estado']) {
                $resultado['mensaje'] = "OK";
                $resultado['clase'] = 'alert alert-success';
                $resultado['icono'] = 'glyphicon glyphicon-ok';
                $db->commit();
                return $resultado;
            } else {
                $db->rollBack();
                return $resultado;
            }

        } catch (PDOException $e) {
            $db->rollBack();
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "ERROR AL ANULAR CONDONACION";
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-remove';
            return $resultado;
        }
    }
}
