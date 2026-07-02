<?php
class cajaDiariaLogic {

    public function obtenerCajasDiarias($anio) {
        try {
            $db = Database::getConnection();
            $sql = "SELECT cd.Id AS idCajaDiaria,
                           cd.FechaApertura AS fechaApertura,
                           cd.HoraApertura AS horaApertura,
                           cd.TotalRecaudacion AS totalRecaudacion,
                           cd.FechaCierre AS fechaCierre,
                           cd.Estado AS estado
                    FROM cajadiaria cd
                    WHERE SUBSTR(cd.FechaApertura, 1, 4) = :anio";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':anio', $anio, PDO::PARAM_STR);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (count($rows) > 0) {
                return [
                    'estado' => true,
                    'mensaje' => "OK",
                    'datos' => $rows,
                    'clase' => 'alert alert-success',
                    'icono' => 'glyphicon glyphicon-ok'
                ];
            }
            return [
                'estado' => false,
                'datos' => null,
                'mensaje' => "No existen caja diaria para el año seleccionado",
                'clase' => 'alert alert-warning',
                'icono' => 'glyphicon glyphicon-exclamation-sign'
            ];
        } catch (PDOException $e) {
            return [
                'estado' => false,
                'mensaje' => "Error buscando cajas diarias: " . $e->getMessage(),
                'clase' => 'alert alert-danger',
                'icono' => 'glyphicon glyphicon-remove'
            ];
        }
    }

    public function obtenerCajaDiariaPorId($idCajaDiaria) {
        try {
            $db = Database::getConnection();
            $sql = "SELECT cd.Id AS idCajaDiaria,
                           cd.FechaApertura AS fechaApertura,
                           cd.HoraApertura AS horaApertura,
                           cd.SaldoInicial AS saldoInicial,
                           cd.TotalRecaudacion AS totalRecaudacion,
                           cd.DiferenciaImporte AS diferenciaImporte,
                           cd.FechaCierre AS fechaCierre,
                           cd.SaldoFinal AS saldoFinal,
                           cd.IdUsuarioApertura AS idUsuarioApertura,
                           cd.IdUsuarioCierre AS idUsuarioCierre,
                           cd.Estado AS estado
                    FROM cajadiaria cd
                    WHERE cd.Id = :idCajaDiaria";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':idCajaDiaria', $idCajaDiaria, PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                return [
                    'estado' => true,
                    'mensaje' => "OK",
                    'datos' => $row,
                    'clase' => 'alert alert-success',
                    'icono' => 'glyphicon glyphicon-ok'
                ];
            }
            return [
                'estado' => false,
                'mensaje' => "Error buscando cajas diarias",
                'clase' => 'alert alert-danger',
                'icono' => 'glyphicon glyphicon-remove'
            ];
        } catch (PDOException $e) {
            return [
                'estado' => false,
                'mensaje' => "Error buscando cajas diarias: " . $e->getMessage(),
                'clase' => 'alert alert-danger',
                'icono' => 'glyphicon glyphicon-remove'
            ];
        }
    }

    public function obtenerCajaAbierta() {
        try {
            $db = Database::getConnection();
            $sql = "SELECT MAX(cd.Id) AS idCajaDiaria, cd.FechaApertura AS fechaApertura
                    FROM cajadiaria cd
                    WHERE Estado = 'A'";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $idCajaDiaria = $row ? $row['idCajaDiaria'] : null;
            $fechaApertura = $row ? $row['fechaApertura'] : null;
            if (isset($idCajaDiaria) && $idCajaDiaria > 0) {
                return [
                    'estado' => true,
                    'datos' => [
                        'idCajaDiaria' => $idCajaDiaria,
                        'fechaApertura' => $fechaApertura
                    ]
                ];
            }
            return [
                'estado' => true,
                'datos' => null
            ];
        } catch (PDOException $e) {
            return [
                'estado' => false
            ];
        }
    }

    public function abririCajaDiaria($fechaCaja, $saldoInicial) {
        try {
            $db = Database::getConnection();
            $sql = "INSERT INTO cajadiaria (FechaApertura, HoraApertura, SaldoInicial, IdUsuarioApertura, Estado)
                    VALUES(:fechaCaja, time(NOW()), :saldoInicial, :idUsuario, 'A')";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':fechaCaja'    => $fechaCaja,
                ':saldoInicial' => $saldoInicial,
                ':idUsuario'    => $_SESSION['user_id']
            ]);
            $idCajaDiaria = $db->lastInsertId();
            return [
                'estado' => true,
                'idCajaDiaria' => $idCajaDiaria,
                'mensaje' => 'OK',
                'clase' => 'alert alert-success',
                'icono' => 'glyphicon glyphicon-ok'
            ];
        } catch (PDOException $e) {
            return [
                'estado' => false,
                'idCajaDiaria' => null,
                'mensaje' => 'ERROR al abrir cajadiaria: ' . $e->getMessage(),
                'clase' => 'alert alert-danger',
                'icono' => 'glyphicon glyphicon-remove'
            ];
        }
    }

    public function cerrarCajaDiaria($idCajaDiaria, $totalRecaudacion) {
        try {
            $db = Database::getConnection();
            $sql = "UPDATE cajadiaria
                    SET FechaCierre = date(now()),
                        HoraCierre = time(NOW()),
                        TotalRecaudacion = :totalRecaudacion,
                        SaldoFinal = (SaldoInicial + :totalRecaudacion2),
                        IdUsuarioCierre = :idUsuario,
                        Estado = 'C'
                    WHERE Id = :idCajaDiaria";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':totalRecaudacion'  => $totalRecaudacion,
                ':totalRecaudacion2' => $totalRecaudacion,
                ':idUsuario'         => $_SESSION['user_id'],
                ':idCajaDiaria'      => $idCajaDiaria
            ]);
            return [
                'estado' => true,
                'idCajaDiaria' => $idCajaDiaria,
                'mensaje' => 'OK',
                'clase' => 'alert alert-success',
                'icono' => 'glyphicon glyphicon-ok'
            ];
        } catch (PDOException $e) {
            return [
                'estado' => false,
                'idCajaDiaria' => null,
                'mensaje' => 'ERROR al cerrar cajadiaria: ' . $e->getMessage(),
                'clase' => 'alert alert-danger',
                'icono' => 'glyphicon glyphicon-remove'
            ];
        }
    }

    public function generarReciboCajaDiariaOtrosIngresos($tipoRecibo, $nombre, $cuit, $domicilio, $concepto, $importe, $tipoPago, $idFormaPago, $idBanco, $comprobante, $intereses) {
        $continua = true;
        $mensaje = '';

        $resCajaDiaria = $this->obtenerCajaAbierta();
        if ($resCajaDiaria['estado'] && isset($resCajaDiaria['datos']['idCajaDiaria'])) {
            $idCajaDiaria = $resCajaDiaria['datos']['idCajaDiaria'];
        } else {
            $continua = false;
            $mensaje .= 'No hay caja del día abierta, debe ir a generar una caja. ';
        }

        $resRecibo = $this->ObtenerNumeroComprobante('RE');
        if ($resRecibo['numeroRecibo']) {
            $numeroRecibo = $resRecibo['numeroRecibo'];
        } else {
            $continua = false;
            $mensaje .= 'No se pudo obtener el numeroRecibo por tipoComprobante. ';
        }

        if ($continua) {
            try {
                $db = Database::getConnection();
                $db->beginTransaction();

                $sql = "INSERT INTO cajadiariamovimiento (IdCajaDiaria, Fecha, Hora, Monto, IdUsuario, Tipo, Numero, Estado)
                        VALUES(:idCajaDiaria, date(NOW()), time(NOW()), :importe, :idUsuario, 'RE', :numero, 'I')";
                $stmt = $db->prepare($sql);
                $stmt->execute([
                    ':idCajaDiaria' => $idCajaDiaria,
                    ':importe'      => $importe,
                    ':idUsuario'    => $_SESSION['user_id'],
                    ':numero'       => $numeroRecibo
                ]);
                $idCajaDiariaMovimiento = $db->lastInsertId();

                $sql = "INSERT INTO cajadiariamovimientootro (IdCajaDiariaMovimiento, Descripcion, Domicilio, CUIT)
                        VALUES(:idMovimiento, :nombre, :domicilio, :cuit)";
                $stmt = $db->prepare($sql);
                $stmt->execute([
                    ':idMovimiento' => $idCajaDiariaMovimiento,
                    ':nombre'       => $nombre,
                    ':domicilio'    => $domicilio,
                    ':cuit'         => $cuit
                ]);

                $sql = "INSERT INTO cajadiariamovimientodetalle (IdCajaDiariaMovimiento, CodigoPago, Monto, Concepto)
                        VALUES(:idMovimiento, :tipoPago, :importe, :concepto)";
                $stmt = $db->prepare($sql);
                $stmt->execute([
                    ':idMovimiento' => $idCajaDiariaMovimiento,
                    ':tipoPago'     => $tipoPago,
                    ':importe'      => $importe,
                    ':concepto'     => $concepto
                ]);

                $sql = "INSERT INTO cajadiariamovimientopago (IdCajaDiariaMovimiento, IdFormaPago, IdBanco, Monto, Detalle, Intereses)
                        VALUES(:idMovimiento, :idFormaPago, :idBanco, :importe, :comprobante, :intereses)";
                $stmt = $db->prepare($sql);
                $stmt->execute([
                    ':idMovimiento' => $idCajaDiariaMovimiento,
                    ':idFormaPago'  => $idFormaPago,
                    ':idBanco'      => $idBanco,
                    ':importe'      => $importe,
                    ':comprobante'  => $comprobante,
                    ':intereses'    => $intereses
                ]);

                if ($tipoRecibo == "FIRMA") {
                    $sql = "UPDATE constanciafirma
                            SET IdCajaDiariaMovimiento = :idMovimiento
                            WHERE Fecha = DATE(NOW()) AND IdCajaDiariaMovimiento IS NULL";
                    $stmt = $db->prepare($sql);
                    $stmt->execute([':idMovimiento' => $idCajaDiariaMovimiento]);
                }

                $db->commit();
                return [
                    'estado' => true,
                    'mensaje' => "OK",
                    'clase' => 'alert alert-success',
                    'icono' => 'glyphicon glyphicon-ok',
                    'idCajaDiariaMovimiento' => $idCajaDiariaMovimiento
                ];
            } catch (PDOException $e) {
                $db->rollBack();
                return [
                    'estado' => false,
                    'mensaje' => "ERROR AL AGREGAR cajadiaria: " . $e->getMessage() . ' (Generar del Recibo por Cajas Diarias, manualmente)',
                    'clase' => 'alert alert-danger',
                    'icono' => 'glyphicon glyphicon-remove'
                ];
            }
        } else {
            return [
                'estado' => false,
                'mensaje' => "ERROR AL OBTENER cajadiaria: " . $mensaje,
                'clase' => 'alert alert-danger',
                'icono' => 'glyphicon glyphicon-remove'
            ];
        }
    }

    public function generarReciboCajaDiaria($idColegiado, $tipoRecibo, $generarRecibo, $generarReciboPP, $conRecargo, $idAsistente, $idFormaPago, $idBanco, $comprobante, $intereses) {
        $continua = true;
        $mensaje = '';

        $resCajaDiaria = $this->obtenerCajaAbierta();
        if ($resCajaDiaria['estado'] && isset($resCajaDiaria['datos']['idCajaDiaria'])) {
            $idCajaDiaria = $resCajaDiaria['datos']['idCajaDiaria'];
        } else {
            $continua = false;
            $mensaje .= 'No hay caja del día abierta, debe ir a generar una caja. ';
        }

        $resRecibo = $this->ObtenerNumeroComprobante('RE');
        if ($resRecibo['numeroRecibo']) {
            $numeroRecibo = $resRecibo['numeroRecibo'];
        } else {
            $continua = false;
            $mensaje .= 'No se pudo obtener el numeroRecibo por tipoComprobante. ';
        }

        if ($continua) {
            try {
                $db = Database::getConnection();
                $db->beginTransaction();
                $resultado = [];

                $sql = "INSERT INTO cajadiariamovimiento (IdCajaDiaria, Fecha, Hora, Monto, IdUsuario, Tipo, Numero, IdColegiado, Estado, IdAsistente)
                        VALUES(:idCajaDiaria, date(NOW()), time(NOW()), 0, :idUsuario, 'RE', :numero, :idColegiado, 'I', :idAsistente)";
                $stmt = $db->prepare($sql);
                $stmt->execute([
                    ':idCajaDiaria' => $idCajaDiaria,
                    ':idUsuario'    => $_SESSION['user_id'],
                    ':numero'       => $numeroRecibo,
                    ':idColegiado'  => $idColegiado,
                    ':idAsistente'  => $idAsistente
                ]);
                $idCajaDiariaMovimiento = $db->lastInsertId();
                $totalDeuda = 0;

                if (isset($generarRecibo)) {
                    foreach ($generarRecibo as $row) {
                        switch ($tipoRecibo) {
                            case 'ESPECIALISTAS':
                                $recargo = 0;
                                $idMesaEntrada = $row;
                                $indice = $idMesaEntrada;
                                $resMesa = obtenerMesaEntradaPorId($idMesaEntrada);
                                if ($resMesa['estado']) {
                                    $mesa = $resMesa['datos'];
                                    $monto = $mesa['importe'];
                                    $codigoPago = $mesa['idTipoPago'];
                                    $periodo = null;
                                    $cuota = null;
                                } else {
                                    $monto = 0;
                                    $codigoPago = null;
                                }
                                break;

                            case 'CUOTAS':
                                echo $row;
                                $id = explode('_', $row);
                                $monto = $id[1];
                                if (isset($id[2]) && $id[2] == 'PT') {
                                    $indice = intval(substr($id[0], 1, 7));
                                    $periodo = $_SESSION['periodoActual'];
                                    $cuota = 0;
                                    $importeOriginal = $monto;
                                    $codigoPago = 1;
                                    $recargo = 0;
                                    $sql = "UPDATE colegiadodeudaanualtotal cdat
                                            INNER JOIN colegiadodeudaanual cda ON cda.Id = cdat.IdColegiadoDeudaAnual
                                            INNER JOIN colegiadodeudaanualcuotas cdac ON cdac.IdColegiadoDeudaAnual = cda.Id
                                            SET cdat.IdEstado = 2,
                                                cdat.FechaPago = date(NOW()),
                                                cdat.FechaActualizacion = date(NOW()),
                                                cdac.Estado = 8,
                                                cdac.FechaPago = date(NOW()),
                                                cdac.FechaActualizacion = date(NOW())
                                            WHERE cdat.Id = :indice";
                                    $stmt = $db->prepare($sql);
                                    $stmt->execute([':indice' => $indice]);
                                    if ($stmt->rowCount() == 0 && $db->errorCode() != '00000') {
                                        $continua = false;
                                    }
                                } else {
                                    $indice = $id[0];
                                    $resCuota = obtenerColegiadoDeudaAnualCuotaPorId($indice);
                                    if ($resCuota['estado']) {
                                        $periodo = $resCuota['datos']['periodo'];
                                        $cuota = $resCuota['datos']['cuota'];
                                        $importeOriginal = $resCuota['datos']['importe'];
                                        if ($periodo == $_SESSION['periodoActual']) {
                                            $codigoPago = 1;
                                        } else {
                                            $codigoPago = 3;
                                        }
                                        if ($conRecargo == 'SI' && $monto > $importeOriginal) {
                                            $recargo = $monto - $importeOriginal;
                                        } else {
                                            $recargo = 0;
                                        }

                                        if ($cuota > 0) {
                                            $sql = "UPDATE colegiadodeudaanualcuotas cdac
                                                    SET cdac.FechaPago = date(NOW()),
                                                        cdac.Estado = 2,
                                                        cdac.FechaActualizacion = date(NOW())
                                                    WHERE cdac.Id = :indice";
                                        } else {
                                            $sql = "UPDATE colegiadodeudaanualtotal cdat
                                                    SET cdat.IdEstado = 2,
                                                        cdat.FechaPago = date(NOW()),
                                                        cdat.FechaActualizacion = date(NOW())
                                                    WHERE cdat.Id = :indice";
                                        }
                                        $stmt = $db->prepare($sql);
                                        $stmt->execute([':indice' => $indice]);
                                        if ($db->errorCode() != '00000') {
                                            $continua = false;
                                        }
                                    } else {
                                        $monto = 0;
                                        $codigoPago = null;
                                    }
                                }
                                break;

                            case 'CURSOS':
                                $id = explode('_', $row);
                                $indice = $id[0];
                                $monto = $id[1];
                                $cursos_pdo = new cursos_pdo();
                                $resCuota = $cursos_pdo->obtenerCursosAsistenteCuotaPorId($indice);
                                if ($resCuota['estado']) {
                                    $cuota = $resCuota['datos']['cuota'];
                                    $importe = $resCuota['datos']['importe'];
                                    $codigoPago = 10;
                                    $recargo = 0;
                                    $periodo = null;

                                    $sql = "UPDATE cursosasistentecuotas cac
                                            SET cac.FechaPago = date(NOW()),
                                                cac.Recibo = :numeroRecibo,
                                                cac.FechaActualizacion = date(NOW())
                                            WHERE cac.Id = :indice";
                                    $stmt = $db->prepare($sql);
                                    $stmt->execute([
                                        ':numeroRecibo' => $numeroRecibo,
                                        ':indice'       => $indice
                                    ]);
                                    if ($db->errorCode() != '00000') {
                                        $continua = false;
                                    }
                                } else {
                                    $monto = 0;
                                    $codigoPago = null;
                                }
                                break;

                            case 'TIPO_PAGO':
                                $id = explode('_', $row);
                                $codigoPago = $id[0];
                                $monto = $id[1];
                                $indice = null;
                                $periodo = null;
                                $cuota = null;
                                $recargo = null;
                                break;

                            case 'DEVOLUCION':
                                $id = explode('_', $row);
                                $codigoPago = $id[0];
                                $monto = $id[1];
                                $indice = null;
                                $periodo = null;
                                $cuota = null;
                                $recargo = null;
                                break;

                            default:
                                break;
                        }

                        $totalDeuda += $monto;
                        if (isset($codigoPago)) {
                            $sql = "INSERT INTO cajadiariamovimientodetalle (IdCajaDiariaMovimiento, CodigoPago, Indice, Monto, Periodo, Cuota, Recargo)
                                    VALUES(:idMovimiento, :codigoPago, :indice, :monto, :periodo, :cuota, :recargo)";
                            $stmt = $db->prepare($sql);
                            $stmt->execute([
                                ':idMovimiento' => $idCajaDiariaMovimiento,
                                ':codigoPago'   => $codigoPago,
                                ':indice'       => $indice,
                                ':monto'        => $monto,
                                ':periodo'      => $periodo,
                                ':cuota'        => $cuota,
                                ':recargo'      => $recargo
                            ]);
                            if ($db->errorCode() != '00000') {
                                $resultado = [
                                    'estado' => false,
                                    'mensaje' => "ERROR AL AGREGAR cajadiariamovimientodetalle",
                                    'clase' => 'alert alert-danger',
                                    'icono' => 'glyphicon glyphicon-remove'
                                ];
                                break;
                            } else {
                                $resultado['estado'] = true;
                            }
                        } else {
                            $resultado = [
                                'estado' => false,
                                'mensaje' => "ERROR AL AGREGAR cajadiariamovimiento",
                                'clase' => 'alert alert-danger',
                                'icono' => 'glyphicon glyphicon-remove'
                            ];
                            break;
                        }
                    }
                }

                if (isset($generarReciboPP)) {
                    foreach ($generarReciboPP as $row) {
                        switch ($tipoRecibo) {
                            case 'CUOTAS':
                                $idPlaPagoCuota = $row;
                                $indice = $idPlaPagoCuota;
                                $resMesa = obtenerPlanPagoCuotaPorId($idPlaPagoCuota);
                                if ($resMesa['estado']) {
                                    $mesa = $resMesa['datos'];
                                    $monto = $mesa['importeActualizado'];
                                    $codigoPago = 2;
                                    $periodo = null;
                                    $cuota = $mesa['cuota'];
                                    $sql = "UPDATE planpagoscuotas ppc
                                            SET ppc.FechaPago = date(NOW()),
                                                ppc.IdTipoEstadoCuota = 2,
                                                ppc.FechaActualizacion = date(NOW())
                                            WHERE ppc.Id = :idPlaPagoCuota";
                                    $stmt = $db->prepare($sql);
                                    $stmt->execute([':idPlaPagoCuota' => $idPlaPagoCuota]);
                                    if ($db->errorCode() != '00000') {
                                        $continua = false;
                                    }
                                } else {
                                    $monto = 0;
                                    $codigoPago = null;
                                }
                                break;

                            default:
                                break;
                        }

                        $totalDeuda += $monto;
                        if (isset($codigoPago)) {
                            $sql = "INSERT INTO cajadiariamovimientodetalle (IdCajaDiariaMovimiento, CodigoPago, Indice, Monto, Periodo, Cuota)
                                    VALUES(:idMovimiento, :codigoPago, :indice, :monto, :periodo, :cuota)";
                            $stmt = $db->prepare($sql);
                            $stmt->execute([
                                ':idMovimiento' => $idCajaDiariaMovimiento,
                                ':codigoPago'   => $codigoPago,
                                ':indice'       => $indice,
                                ':monto'        => $monto,
                                ':periodo'      => $periodo,
                                ':cuota'        => $cuota
                            ]);
                            if ($db->errorCode() != '00000') {
                                $resultado = [
                                    'estado' => false,
                                    'mensaje' => "ERROR AL AGREGAR cajadiariamovimientodetalle",
                                    'clase' => 'alert alert-danger',
                                    'icono' => 'glyphicon glyphicon-remove'
                                ];
                                break;
                            } else {
                                $resultado['estado'] = true;
                            }
                        } else {
                            $resultado = [
                                'estado' => false,
                                'mensaje' => "ERROR AL AGREGAR cajadiariamovimiento",
                                'clase' => 'alert alert-danger',
                                'icono' => 'glyphicon glyphicon-remove'
                            ];
                            break;
                        }
                    }
                }

                $totalDeuda += $intereses;

                $sql = "INSERT INTO cajadiariamovimientopago (IdCajaDiariaMovimiento, IdFormaPago, IdBanco, Monto, Detalle, Intereses)
                        VALUES(:idMovimiento, :idFormaPago, :idBanco, :totalDeuda, :comprobante, :intereses)";
                $stmt = $db->prepare($sql);
                $stmt->execute([
                    ':idMovimiento' => $idCajaDiariaMovimiento,
                    ':idFormaPago'  => $idFormaPago,
                    ':idBanco'      => $idBanco,
                    ':totalDeuda'   => $totalDeuda,
                    ':comprobante'  => $comprobante,
                    ':intereses'    => $intereses
                ]);
                if ($db->errorCode() == '00000') {
                    $resultado['estado'] = true;
                    $resultado['mensaje'] = "OK";
                    $resultado['clase'] = 'alert alert-success';
                    $resultado['icono'] = 'glyphicon glyphicon-ok';
                } else {
                    $resultado = [
                        'estado' => false,
                        'mensaje' => "ERROR AL AGREGAR cajadiariamovimientopago",
                        'clase' => 'alert alert-danger',
                        'icono' => 'glyphicon glyphicon-remove'
                    ];
                }

                if ($resultado['estado']) {
                    if ($totalDeuda > 0) {
                        $sql = "UPDATE cajadiariamovimiento
                                SET Monto = :totalDeuda
                                WHERE Id = :idMovimiento";
                        $stmt = $db->prepare($sql);
                        $stmt->execute([
                            ':totalDeuda'   => $totalDeuda,
                            ':idMovimiento' => $idCajaDiariaMovimiento
                        ]);

                        foreach ($generarRecibo as $row) {
                            switch ($tipoRecibo) {
                                case 'ESPECIALISTAS':
                                    $idMesaEntrada = $row;
                                    $sql = "UPDATE mesaentrada
                                            SET Pagado = 1
                                            WHERE IdMesaEntrada = :idMesaEntrada";
                                    $stmt = $db->prepare($sql);
                                    $stmt->execute([':idMesaEntrada' => $idMesaEntrada]);
                                    break;
                            }
                        }
                    }
                    $resultado['idCajaDiariaMovimiento'] = $idCajaDiariaMovimiento;
                    $db->commit();
                    return $resultado;
                } else {
                    $db->rollBack();
                    $resultado['mensaje'] .= ' (Generar del Recibo por Cajas Diarias, manualmente)';
                    return $resultado;
                }
            } catch (PDOException $e) {
                $db->rollBack();
                return [
                    'estado' => false,
                    'mensaje' => "Error: " . $e->getMessage(),
                    'clase' => 'alert alert-danger',
                    'icono' => 'glyphicon glyphicon-remove'
                ];
            }
        } else {
            return [
                'estado' => false,
                'mensaje' => "ERROR AL OBTENER cajadiaria: " . $mensaje,
                'clase' => 'alert alert-danger',
                'icono' => 'glyphicon glyphicon-remove'
            ];
        }
    }

    public function generarDevolucionCajaDiaria($idColegiado, $tipoPago, $idFormaPago, $importe) {
        $continua = true;
        $mensaje = '';

        $resCajaDiaria = $this->obtenerCajaAbierta();
        if ($resCajaDiaria['estado'] && isset($resCajaDiaria['datos']['idCajaDiaria'])) {
            $idCajaDiaria = $resCajaDiaria['datos']['idCajaDiaria'];
        } else {
            $continua = false;
            $mensaje .= 'No hay caja del día abierta, debe ir a generar una caja. ';
        }

        $resRecibo = $this->ObtenerNumeroComprobante('NC');
        if ($resRecibo['numeroRecibo']) {
            $numeroRecibo = $resRecibo['numeroRecibo'];
        } else {
            $continua = false;
            $mensaje .= 'No se pudo obtener el numeroRecibo por tipoComprobante. ';
        }

        if ($continua) {
            try {
                $db = Database::getConnection();
                $db->beginTransaction();

                $sql = "INSERT INTO cajadiariamovimiento (IdCajaDiaria, Fecha, Hora, Monto, IdUsuario, Tipo, Numero, IdColegiado, Estado, IdAsistente)
                        VALUES(:idCajaDiaria, date(NOW()), time(NOW()), :importe, :idUsuario, 'NC', :numero, :idColegiado, 'I', NULL)";
                $stmt = $db->prepare($sql);
                $stmt->execute([
                    ':idCajaDiaria' => $idCajaDiaria,
                    ':importe'      => $importe,
                    ':idUsuario'    => $_SESSION['user_id'],
                    ':numero'       => $numeroRecibo,
                    ':idColegiado'  => $idColegiado
                ]);
                $idCajaDiariaMovimiento = $db->lastInsertId();

                $sql = "INSERT INTO cajadiariamovimientodetalle (IdCajaDiariaMovimiento, CodigoPago, Monto)
                        VALUES(:idMovimiento, :tipoPago, :importe)";
                $stmt = $db->prepare($sql);
                $stmt->execute([
                    ':idMovimiento' => $idCajaDiariaMovimiento,
                    ':tipoPago'     => $tipoPago,
                    ':importe'      => $importe
                ]);

                $sql = "INSERT INTO cajadiariamovimientopago (IdCajaDiariaMovimiento, IdFormaPago, Monto)
                        VALUES(:idMovimiento, :idFormaPago, :importe)";
                $stmt = $db->prepare($sql);
                $stmt->execute([
                    ':idMovimiento' => $idCajaDiariaMovimiento,
                    ':idFormaPago'  => $idFormaPago,
                    ':importe'      => $importe
                ]);

                $db->commit();
                return [
                    'estado' => true,
                    'mensaje' => "OK",
                    'clase' => 'alert alert-success',
                    'icono' => 'glyphicon glyphicon-ok',
                    'idCajaDiariaMovimiento' => $idCajaDiariaMovimiento
                ];
            } catch (PDOException $e) {
                $db->rollBack();
                return [
                    'estado' => false,
                    'mensaje' => "ERROR AL AGREGAR cajadiaria: " . $e->getMessage() . ' (Generar del Recibo por Cajas Diarias, manualmente)',
                    'clase' => 'alert alert-danger',
                    'icono' => 'glyphicon glyphicon-remove'
                ];
            }
        } else {
            return [
                'estado' => false,
                'mensaje' => "ERROR AL OBTENER cajadiaria: " . $mensaje,
                'clase' => 'alert alert-danger',
                'icono' => 'glyphicon glyphicon-remove'
            ];
        }
    }

    public function anularReciboCajaDiaria($idCajaDiariaMovimiento) {
        try {
            $db = Database::getConnection();
            $db->beginTransaction();

            $sql = "SELECT Id AS id,
                           CodigoPago AS codigoPago,
                           Indice AS indice,
                           Periodo AS periodo,
                           Cuota AS cuota
                    FROM cajadiariamovimientodetalle
                    WHERE IdCajaDiariaMovimiento = :idMovimiento";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':idMovimiento', $idCajaDiariaMovimiento, PDO::PARAM_INT);
            $stmt->execute();
            $detalles = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $continua = true;
            foreach ($detalles as $detalle) {
                if (!$continua) break;
                $codigoPago = $detalle['codigoPago'];
                $indice = $detalle['indice'];
                $cuota = $detalle['cuota'];

                switch ($codigoPago) {
                    case '72':
                    case '38':
                    case '59':
                    case '37':
                    case '82':
                    case '52':
                    case '55':
                    case '61':
                        $sql1 = "UPDATE mesaentrada
                                SET Pagado = 0
                                WHERE IdMesaEntrada = :indice";
                        break;

                    case '1':
                    case '3':
                        if ($cuota > 0) {
                            $sql1 = "UPDATE colegiadodeudaanualcuotas cdac
                                    SET cdac.FechaPago = '0000-00-00',
                                        cdac.Estado = 1,
                                        cdac.FechaActualizacion = NULL
                                    WHERE cdac.Id = :indice";
                        } else {
                            $sql1 = "UPDATE colegiadodeudaanualtotal cdat
                                    SET cdat.IdEstado = 1,
                                        cdat.FechaPago = NULL,
                                        cdat.FechaActualizacion = NULL
                                    WHERE cdat.Id = :indice";
                        }
                        break;

                    case '2':
                        $sql1 = "UPDATE planpagoscuotas ppc
                                SET ppc.FechaPago = NULL,
                                    ppc.Estado = '',
                                    ppc.IdTipoEstadoCuota = 1,
                                    ppc.FechaActualizacion = NULL
                                WHERE ppc.Id = :indice";
                        break;

                    case '10':
                        $sql1 = "UPDATE cursosasistentecuotas cac
                                SET cac.FechaPago = NULL,
                                    cac.Recibo = 0,
                                    cac.FechaActualizacion = NULL
                                WHERE cac.Id = :indice";
                        break;

                    case '62':
                        $indice = $idCajaDiariaMovimiento;
                        $sql1 = "UPDATE constanciafirma
                                SET IdCajaDiariaMovimiento = NULL
                                WHERE IdCajaDiariaMovimiento = :indice";
                        break;

                    default:
                        $sql1 = null;
                        break;
                }

                if (isset($sql1)) {
                    $stmt1 = $db->prepare($sql1);
                    $stmt1->execute([':indice' => $indice]);
                    if ($db->errorCode() != '00000') {
                        $continua = false;
                    }
                }
            }

            if ($continua) {
                $sql = "UPDATE cajadiariamovimiento
                        SET Estado = 'A'
                        WHERE Id = :idMovimiento";
                $stmt = $db->prepare($sql);
                $stmt->execute([':idMovimiento' => $idCajaDiariaMovimiento]);
                $db->commit();
                return [
                    'estado' => true,
                    'mensaje' => "OK - RECIBO ANULADO",
                    'clase' => 'alert alert-success',
                    'icono' => 'glyphicon glyphicon-ok'
                ];
            } else {
                $db->rollBack();
                return [
                    'estado' => false,
                    'mensaje' => "ERROR AL ANULAR RECIBO",
                    'clase' => 'alert alert-danger',
                    'icono' => 'glyphicon glyphicon-remove'
                ];
            }
        } catch (PDOException $e) {
            $db->rollBack();
            return [
                'estado' => false,
                'mensaje' => "Error: " . $e->getMessage(),
                'clase' => 'alert alert-danger',
                'icono' => 'glyphicon glyphicon-remove'
            ];
        }
    }

    public function ObtenerNumeroComprobante($tipoComprobante) {
        try {
            $db = Database::getConnection();
            $sql = "SELECT MAX(Numero) AS maxNumero
                    FROM cajadiariamovimiento
                    WHERE Tipo = :tipoComprobante";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':tipoComprobante', $tipoComprobante, PDO::PARAM_STR);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $numeroRecibo = $row ? $row['maxNumero'] : null;
            if (isset($numeroRecibo) && $numeroRecibo > 0) {
                return ['numeroRecibo' => $numeroRecibo + 1];
            }
            return ['numeroRecibo' => null];
        } catch (PDOException $e) {
            return ['numeroRecibo' => null];
        }
    }

    public function obtenerCajaDiariaMovimientos($idCajaDiaria) {
        try {
            $db = Database::getConnection();
            $sql = "SELECT cdm.Id AS idCajaDiariaMovimiento,
                           cdm.Fecha AS fechaPago,
                           cdm.Hora AS horaPago,
                           cdm.Monto AS monto,
                           cdm.Tipo AS tipo,
                           cdm.Numero AS numero,
                           cdm.IdAsistente AS idAsistente,
                           cdm.IdColegiado AS idColegiado,
                           u.Usuario AS usuario,
                           cdm.Estado AS estado,
                           IF (cdm.IdAsistente IS NOT NULL,
                               ca.ApellidoNombre,
                               IF (cdm.IdColegiado IS NOT NULL, CONCAT(p.Apellido, ' ', p.Nombres), cdmo.Descripcion))
                           AS apellidoNombre,
                           CASE
                               WHEN cdm.IdAsistente IS NOT NULL THEN if (ca.IdColegiado IS NOT NULL, c1.Matricula, NULL)
                               WHEN cdm.IdColegiado IS NOT NULL THEN c.Matricula
                           END AS matricula,
                           fp.Detalle AS formaDePago,
                           b.Nombre AS nombreBanco,
                           cmp.Detalle AS comprobante,
                           cmp.Intereses AS intereses,
                           if ((cmr.Id IS NULL AND cmr1.Id IS NULL), (
                               CASE
                                   WHEN cdm.IdAsistente IS NOT NULL THEN cc1.CorreoElectronico
                                   WHEN cdm.IdColegiado IS NOT NULL THEN cc.CorreoElectronico
                                   ELSE 'NR'
                               END), NULL) AS correoElectronico
                    FROM cajadiariamovimiento cdm
                    LEFT JOIN colegiado c ON c.Id = cdm.IdColegiado
                    LEFT JOIN persona p ON p.Id = c.IdPersona
                    LEFT JOIN colegiadocontacto cc ON (cc.IdColegiado = c.Id AND cc.IdEstado = 1)
                    LEFT JOIN colegiadomailrechazado cmr ON cmr.IdColegiado = c.Id
                    LEFT JOIN cursosasistente ca ON ca.Id = cdm.IdAsistente
                    LEFT JOIN cursos cur ON cur.Id = ca.IdCursos
                    LEFT JOIN colegiado c1 ON c1.Id = ca.IdColegiado
                    LEFT JOIN persona p1 ON p1.Id = c1.IdPersona
                    LEFT JOIN colegiadocontacto cc1 ON (cc1.IdColegiado = c1.Id AND cc1.IdEstado = 1)
                    LEFT JOIN colegiadomailrechazado cmr1 ON cmr1.IdColegiado = c1.Id
                    LEFT JOIN cajadiariamovimientootro cdmo ON cdmo.IdCajaDiariaMovimiento = cdm.Id
                    LEFT JOIN usuario u ON u.Id = cdm.IdUsuario
                    LEFT JOIN cajadiariamovimientopago cmp ON cmp.IdCajaDiariaMovimiento = cdm.Id
                    LEFT JOIN formapago fp ON fp.Id = cmp.IdFormaPago
                    LEFT JOIN banco b ON b.Id = cmp.IdBanco
                    WHERE cdm.IdCajaDiaria = :idCajaDiaria
                    ORDER BY cdm.Id";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':idCajaDiaria', $idCajaDiaria, PDO::PARAM_INT);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (count($rows) > 0) {
                return [
                    'estado' => true,
                    'mensaje' => "OK",
                    'datos' => $rows,
                    'clase' => 'alert alert-success',
                    'icono' => 'glyphicon glyphicon-ok'
                ];
            }
            return [
                'estado' => false,
                'datos' => null,
                'mensaje' => "No existen movimientos en la caja diaria",
                'clase' => 'alert alert-warning',
                'icono' => 'glyphicon glyphicon-exclamation-sign'
            ];
        } catch (PDOException $e) {
            return [
                'estado' => false,
                'mensaje' => "Error buscando movimientos en la caja diaria: " . $e->getMessage(),
                'clase' => 'alert alert-danger',
                'icono' => 'glyphicon glyphicon-remove'
            ];
        }
    }

    public function obtenerCajaDiariaResumenCuenta($idCajaDiaria) {
        try {
            $db = Database::getConnection();
            $sql = "SELECT tp.Detalle AS concepto,
                           cdmd.CodigoPago AS codigoPago,
                           tp.CuentaContable AS cuentaContable,
                           SUM(cdmd.Monto) AS totalConcepto
                    FROM cajadiariamovimientodetalle cdmd
                    INNER JOIN cajadiariamovimiento cdm ON cdm.Id = cdmd.IdCajaDiariaMovimiento
                    INNER JOIN tipopago tp ON tp.Id = cdmd.CodigoPago
                    WHERE cdm.IdCajaDiaria = :idCajaDiaria
                    AND cdm.Estado <> 'A'
                    GROUP BY tp.Detalle, cdmd.CodigoPago, tp.CuentaContable";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':idCajaDiaria', $idCajaDiaria, PDO::PARAM_INT);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (count($rows) > 0) {
                return [
                    'estado' => true,
                    'mensaje' => "OK",
                    'datos' => $rows,
                    'clase' => 'alert alert-success',
                    'icono' => 'glyphicon glyphicon-ok'
                ];
            }
            return [
                'estado' => false,
                'datos' => null,
                'mensaje' => "No existen movimientos en la caja diaria",
                'clase' => 'alert alert-warning',
                'icono' => 'glyphicon glyphicon-exclamation-sign'
            ];
        } catch (PDOException $e) {
            return [
                'estado' => false,
                'mensaje' => "Error buscando movimientos en la caja diaria: " . $e->getMessage(),
                'clase' => 'alert alert-danger',
                'icono' => 'glyphicon glyphicon-remove'
            ];
        }
    }

    public function obtenerCajaPorFormaPago($idCajaDiaria) {
        try {
            $db = Database::getConnection();
            $sql = "SELECT fp.Detalle AS formaDePago,
                           SUM(cdmp.Monto) AS totalConcepto
                    FROM cajadiariamovimientopago cdmp
                    INNER JOIN cajadiariamovimiento cdm ON cdm.Id = cdmp.IdCajaDiariaMovimiento
                    INNER JOIN formapago fp ON fp.Id = cdmp.IdFormaPago
                    WHERE cdm.IdCajaDiaria = :idCajaDiaria
                    AND cdm.Estado <> 'A'
                    GROUP BY fp.OrdenReporte, fp.Detalle";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':idCajaDiaria', $idCajaDiaria, PDO::PARAM_INT);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (count($rows) > 0) {
                return [
                    'estado' => true,
                    'mensaje' => "OK",
                    'datos' => $rows,
                    'clase' => 'alert alert-success',
                    'icono' => 'glyphicon glyphicon-ok'
                ];
            }
            return [
                'estado' => false,
                'datos' => null,
                'mensaje' => "No existen movimientos en la caja diaria",
                'clase' => 'alert alert-warning',
                'icono' => 'glyphicon glyphicon-exclamation-sign'
            ];
        } catch (PDOException $e) {
            return [
                'estado' => false,
                'mensaje' => "Error buscando movimientos en la caja diaria: " . $e->getMessage(),
                'clase' => 'alert alert-danger',
                'icono' => 'glyphicon glyphicon-remove'
            ];
        }
    }

    public function obtenerTotalRecaudacion($idCajaDiaria) {
        try {
            $db = Database::getConnection();
            $sql = "SELECT SUM(cdm.Monto) AS totalRecaudacion,
                           COUNT(cdm.Id) AS cantidadComprobantes
                    FROM cajadiariamovimiento cdm
                    WHERE cdm.IdCajaDiaria = :idCajaDiaria";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':idCajaDiaria', $idCajaDiaria, PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                $totalRecaudacion = $row['totalRecaudacion'];
                if (!isset($totalRecaudacion)) {
                    $totalRecaudacion = 0.00;
                }
                return [
                    'totalRecaudacion'     => $totalRecaudacion,
                    'cantidadComprobantes' => $row['cantidadComprobantes']
                ];
            }
            return ['totalRecaudacion' => 0, 'cantidadComprobantes' => 0];
        } catch (PDOException $e) {
            return null;
        }
    }

    public function obtenerCajaDiariaMovimientoPorId($idCajaDiariaMovimiento) {
        try {
            $db = Database::getConnection();
            $sql = "SELECT cdm.IdCajaDiaria AS idCajaDiaria,
                           cdm.Fecha AS fechaPago,
                           cdm.Hora AS horaPago,
                           cdm.Monto AS monto,
                           cdm.Tipo AS tipoRecibo,
                           cdm.Numero AS numeroRecibo,
                           cdm.IdAsistente AS idAsistente,
                           cdm.IdColegiado AS idColegiado,
                           u.Usuario AS usuario,
                           cdm.Estado AS estadoRecibo,
                           IF (cdm.IdAsistente IS NOT NULL,
                               ca.ApellidoNombre,
                               IF (cdm.IdColegiado IS NOT NULL, CONCAT(p.Apellido, ' ', p.Nombres), cdmo.Descripcion))
                           AS apellidoNombre,
                           CASE
                               WHEN cdm.IdColegiado IS NOT NULL THEN (SELECT CONCAT(cdr.Calle, ' - ', cdr.Numero, ' (', l.Nombre,')')
                                                                       FROM colegiadodomicilioreal cdr
                                                                       LEFT JOIN localidad l ON l.Id = cdr.idLocalidad
                                                                       WHERE cdr.idColegiado = c.Id AND cdr.idEstado = 1)
                               WHEN (cdm.IdAsistente IS NULL AND cdm.IdColegiado IS NULL) THEN cdmo.Domicilio
                           END AS domicilio,
                           CASE
                               WHEN cdm.IdAsistente IS NOT NULL THEN if (ca.IdColegiado IS NOT NULL, c1.Matricula, NULL)
                               WHEN cdm.IdColegiado IS NOT NULL THEN c.Matricula
                           END AS matricula,
                           cdmo.CUIT AS cuit
                    FROM cajadiariamovimiento cdm
                    LEFT JOIN colegiado c ON c.Id = cdm.IdColegiado
                    LEFT JOIN persona p ON p.Id = c.IdPersona
                    LEFT JOIN cursosasistente ca ON ca.Id = cdm.IdAsistente
                    LEFT JOIN cursos cur ON cur.Id = ca.IdCursos
                    LEFT JOIN colegiado c1 ON c1.Id = ca.IdColegiado
                    LEFT JOIN persona p1 ON p1.Id = c1.IdPersona
                    LEFT JOIN cajadiariamovimientootro cdmo ON cdmo.IdCajaDiariaMovimiento = cdm.Id
                    LEFT JOIN usuario u ON u.Id = cdm.IdUsuario
                    WHERE cdm.Id = :idMovimiento";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':idMovimiento', $idCajaDiariaMovimiento, PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                return [
                    'estado' => true,
                    'mensaje' => "OK",
                    'datos' => $row,
                    'clase' => 'alert alert-success',
                    'icono' => 'glyphicon glyphicon-ok'
                ];
            }
            return [
                'estado' => false,
                'mensaje' => "Error buscando Movimiento",
                'clase' => 'alert alert-danger',
                'icono' => 'glyphicon glyphicon-remove'
            ];
        } catch (PDOException $e) {
            return [
                'estado' => false,
                'mensaje' => "Error buscando Movimiento: " . $e->getMessage(),
                'clase' => 'alert alert-danger',
                'icono' => 'glyphicon glyphicon-remove'
            ];
        }
    }

    public function obtenerCajaDiariaMovimientoDetallePorId($idCajaDiariaMovimiento) {
        try {
            $db = Database::getConnection();
            $sql = "SELECT cdmd.Id AS idCajaDiariaMovimientoDetalle,
                           cdmd.CodigoPago AS codigoPago,
                           cdmd.Indice AS indice,
                           cdmd.Monto AS monto,
                           cdmd.Periodo AS periodo,
                           cdmd.Cuota AS cuota,
                           cdmd.Condonacion AS condonacion,
                           cdmd.Recargo AS recargo,
                           tp.Detalle AS tipoPago,
                           cdmd.Concepto AS concepto,
                           (CASE
                               WHEN cdmd.CodigoPago IN('72', '38', '59', '37', '82', '52', '55', '61') THEN (SELECT e.Especialidad FROM mesaentradaespecialidad mee INNER JOIN mesaentrada me ON me.IdMesaEntrada = mee.IdMesaEntrada INNER JOIN especialidad e ON(e.Id = mee.IdEspecialidad) INNER JOIN tipoespecialista tes ON(tes.IdTipoEspecialista = mee.IdTipoEspecialista) WHERE mee.IdMesaEntrada = cdmd.Indice)
                               WHEN cdmd.CodigoPago = '10' THEN (SELECT CONCAT(cur.Titulo, ' - ', cac.DetalleCuota) FROM cursos cur INNER JOIN cursosasistente ca ON ca.IdCursos = cur.Id INNER JOIN cursosasistentecuotas cac ON cac.IdCursosAsistente = ca.Id WHERE cac.Id = cdmd.Indice)
                               ELSE ''
                           END) AS detalleExtra
                    FROM cajadiariamovimientodetalle cdmd
                    INNER JOIN tipopago tp ON tp.Id = cdmd.CodigoPago
                    WHERE cdmd.IdCajaDiariaMovimiento = :idMovimiento";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':idMovimiento', $idCajaDiariaMovimiento, PDO::PARAM_INT);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (count($rows) > 0) {
                // build detalle as tipoPago + concepto (preserving original logic)
                foreach ($rows as &$r) {
                    $r['detalle'] = $r['tipoPago'] . $r['concepto'];
                }
                unset($r);
                return [
                    'estado' => true,
                    'mensaje' => "OK",
                    'datos' => $rows,
                    'clase' => 'alert alert-success',
                    'icono' => 'glyphicon glyphicon-ok'
                ];
            }
            return [
                'estado' => false,
                'datos' => null,
                'mensaje' => "No existe detalle para el recibo seleccionado",
                'clase' => 'alert alert-warning',
                'icono' => 'glyphicon glyphicon-exclamation-sign'
            ];
        } catch (PDOException $e) {
            return [
                'estado' => false,
                'mensaje' => "Error buscando detalle para el recibo seleccionado: " . $e->getMessage(),
                'clase' => 'alert alert-danger',
                'icono' => 'glyphicon glyphicon-remove'
            ];
        }
    }

    public function marcarEnviaMailCajadiariaMovimiento($idCajaDiariaMovimiento) {
        try {
            $db = Database::getConnection();
            $sql = "UPDATE cajadiariamovimiento
                    SET EnviaMail = 1
                    WHERE Id = :idMovimiento";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':idMovimiento', $idCajaDiariaMovimiento, PDO::PARAM_INT);
            $stmt->execute();
            return [
                'estado' => true,
                'mensaje' => 'OK',
                'clase' => 'alert alert-success',
                'icono' => 'glyphicon glyphicon-ok'
            ];
        } catch (PDOException $e) {
            return [
                'estado' => false,
                'idCajaDiaria' => null,
                'mensaje' => 'ERROR al marcar EnviaMail: ' . $e->getMessage(),
                'clase' => 'alert alert-danger',
                'icono' => 'glyphicon glyphicon-remove'
            ];
        }
    }

    public function obtenerCajaDiariaMovimientoFormaPagoPorId($idCajaDiariaMovimiento) {
        try {
            $db = Database::getConnection();
            $sql = "SELECT p.Id AS idCajaDiariaMovimientoFormaPago,
                           p.Monto AS monto,
                           p.Detalle AS detalle,
                           fp.Leyenda AS formaDePago,
                           b.Nombre AS bancoNombre,
                           p.Intereses AS intereses
                    FROM cajadiariamovimientopago p
                    INNER JOIN formapago fp ON fp.Id = p.IdFormaPago
                    LEFT JOIN banco b ON b.Id = p.IdBanco
                    WHERE p.IdCajaDiariaMovimiento = :idMovimiento";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':idMovimiento', $idCajaDiariaMovimiento, PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                return [
                    'estado' => true,
                    'mensaje' => "OK",
                    'datos' => $row,
                    'clase' => 'alert alert-success',
                    'icono' => 'glyphicon glyphicon-ok'
                ];
            }
            return [
                'estado' => false,
                'mensaje' => "Error buscando Forma de Pago",
                'clase' => 'alert alert-danger',
                'icono' => 'glyphicon glyphicon-remove'
            ];
        } catch (PDOException $e) {
            return [
                'estado' => false,
                'mensaje' => "Error buscando Forma de Pago: " . $e->getMessage(),
                'clase' => 'alert alert-danger',
                'icono' => 'glyphicon glyphicon-remove'
            ];
        }
    }
}
