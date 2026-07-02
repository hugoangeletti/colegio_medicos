<?php
class colegiacionAnualLogic {

    public function obtenerColegiacionAnual() {
    $resultado = array();
    try {
        $db = Database::getConnection();
        $sql = "SELECT * FROM colegiacion_anual WHERE Borrado = 0 ORDER BY Periodo DESC, Antiguedad";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $datos = $stmt->fetchAll();
        if (count($datos) > 0) {
            $rows = array();
            foreach ($datos as $row) {
                $rows[] = array(
                    'idColegiacionAnual' => $row['Id'],
                    'periodo' => $row['Periodo'],
                    'antiguedad' => $row['Antiguedad'],
                    'importe' => $row['Importe'],
                    'vencimientoCuotaUno' => $row['PrimerVencimiento'],
                    'cuotas' => $row['Cuotas'],
                    'pagoTotal' => $row['PagoTotal'],
                    'vencimientoPagoTotal' => $row['VencimientoPagoTotal'],
                    'idUsuario' => $row['IdUsuario'],
                    'fechaCarga' => $row['FechaCarga'],
                    'borrado' => $row['Borrado']
                );
            }
            $resultado['estado'] = TRUE;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $rows;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = FALSE;
            $resultado['datos'] = NULL;
            $resultado['mensaje'] = "NO SE ENCONTRARON DATOS DE COLEGIACION ANUAL";
            $resultado['clase'] = 'alert alert-warning';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando DATOS DE COLEGIACION ANUAL";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }

    return $resultado;
}

    public function obtenerColegiacionAnualPorId($idColegiacionAnual) {
    $resultado = array();
    try {
        $db = Database::getConnection();
        $sql = "SELECT * FROM colegiacion_anual WHERE Id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idColegiacionAnual]);
        $row = $stmt->fetch();
        if ($row) {
            $datos = array(
                'idColegiacionAnual' => $row['Id'],
                'periodo' => $row['Periodo'],
                'antiguedad' => $row['Antiguedad'],
                'importe' => $row['Importe'],
                'vencimientoCuotaUno' => $row['PrimerVencimiento'],
                'cuotas' => $row['Cuotas'],
                'pagoTotal' => $row['PagoTotal'],
                'vencimientoPagoTotal' => $row['VencimientoPagoTotal'],
                'idUsuario' => $row['IdUsuario'],
                'fechaCarga' => $row['FechaCarga'],
                'borrado' => $row['Borrado']
            );
            $resultado['estado'] = TRUE;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = FALSE;
            $resultado['datos'] = NULL;
            $resultado['mensaje'] = "NO SE ENCONTRO COLEGIACION ANUAL";
            $resultado['clase'] = 'alert alert-warning';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando COLEGIACION ANUAL";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }

    return $resultado;
}

    public function obtenerColegiacionAnualPorPeriodo($periodoActual, $antiguedad) {
    if (isset($antiguedad)) {
        $conAntiguedad = " AND Antiguedad = ".$antiguedad;
    } else {
        $conAntiguedad = "";
    }
    $resultado = array();
    try {
        $db = Database::getConnection();
        $sql = "SELECT * FROM colegiacion_anual
            WHERE Periodo = ? AND Borrado = 0 ".$conAntiguedad."
            ORDER BY Antiguedad";
        $stmt = $db->prepare($sql);
        $stmt->execute([$periodoActual]);
        $datos = $stmt->fetchAll();
        if (count($datos) > 0) {
            $rows = array();
            foreach ($datos as $row) {
                $rows[] = array(
                    'idColegiacionAnual' => $row['Id'],
                    'periodo' => $row['Periodo'],
                    'antiguedad' => $row['Antiguedad'],
                    'importe' => $row['Importe'],
                    'vencimientoCuotaUno' => $row['PrimerVencimiento'],
                    'cuotas' => $row['Cuotas'],
                    'pagoTotal' => $row['PagoTotal'],
                    'vencimientoPagoTotal' => $row['VencimientoPagoTotal'],
                    'idUsuario' => $row['IdUsuario'],
                    'fechaCarga' => $row['FechaCarga'],
                    'borrado' => $row['Borrado']
                );
            }
            $resultado['estado'] = TRUE;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $rows;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = FALSE;
            $resultado['datos'] = NULL;
            $resultado['mensaje'] = "NO SE ENCONTRARON DATOS DE COLEGIACION ANUAL";
            $resultado['clase'] = 'alert alert-warning';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando DATOS DE COLEGIACION ANUAL";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }

    return $resultado;
}

    public function obtenerColegiacionAnualCuotas($periodoActual) {
    $resultado = array();
    try {
        $db = Database::getConnection();
        $sql = "SELECT ca.Antiguedad, cac.Cuota, cac.Importe, cac.FechaVencimiento
            FROM colegiacion_anual_cuota cac
            INNER JOIN colegiacion_anual ca ON ( ca.Periodo = ? AND ca.Id = cac.IdColegiacionAnual)
            WHERE cac.Borrado = 0";
        $stmt = $db->prepare($sql);
        $stmt->execute([$periodoActual]);
        $datos = $stmt->fetchAll();
        $cuotasLiquidar = array();
        foreach ($datos as $row) {
            $cuotasLiquidar[] = array(
                'antiguedad' => $row['Antiguedad'],
                'cuota' => $row['Cuota'],
                'importe' => $row['Importe'],
                'fechaVencimiento' => $row['FechaVencimiento']
            );
        }
        $resultado['estado'] = TRUE;
        $resultado['mensaje'] = "OK";
        $resultado['datos'] = $cuotasLiquidar;
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "ERROR AL BUSCAR CUOTAS colegiacion_anual_cuotas";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function obtenerPagoTotal($periodo) {
    $resultado = array();
    try {
        $db = Database::getConnection();
        $sql = "SELECT PagoTotal, VencimientoPagoTotal
            FROM colegiacion_anual
            WHERE Periodo = ? AND Borrado = 0
            ORDER BY Antiguedad";
        $stmt = $db->prepare($sql);
        $stmt->execute([$periodo]);
        $datos = $stmt->fetchAll();
        if (count($datos) > 0) {
            $rows = array();
            foreach ($datos as $row) {
                $rows[] = array(
                    'importe' => $row['PagoTotal'],
                    'vencimiento' => $row['VencimientoPagoTotal']
                );
            }
            $resultado['estado'] = TRUE;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $rows;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = FALSE;
            $resultado['datos'] = NULL;
            $resultado['mensaje'] = "NO SE ENCONTRARON DATOS DE PAGO TOTAL";
            $resultado['clase'] = 'alert alert-warning';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando DATOS DE PAGO TOTAL";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }

    return $resultado;
}

    public function obtenerCuotasAgregar($periodo, $cuotaInicio) {
    $resultado = array();
    try {
        $db = Database::getConnection();
        $sql = "SELECT ca.Antiguedad AS antiguedad, ca.Importe AS importeTotal, cac.Cuota AS cuota, cac.Importe AS importe, cac.FechaVencimiento AS vencimiento
            FROM colegiacion_anual ca
            INNER JOIN colegiacion_anual_cuota cac ON cac.IdColegiacionAnual = ca.Id
            WHERE ca.Periodo = ? AND cac.Cuota >= ?
            ORDER BY ca.Antiguedad, cac.Cuota";
        $stmt = $db->prepare($sql);
        $stmt->execute([$periodo, $cuotaInicio]);
        $datos = $stmt->fetchAll();
        if (count($datos) > 0) {
            $rows = array();
            foreach ($datos as $row) {
                $rows[] = array(
                    'antiguedad' => $row['antiguedad'],
                    'importeTotal' => $row['importeTotal'],
                    'cuota' => $row['cuota'],
                    'importe' => $row['importe'],
                    'vencimiento' => $row['vencimiento']
                );
            }
            $resultado['estado'] = TRUE;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $rows;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = FALSE;
            $resultado['datos'] = NULL;
            $resultado['mensaje'] = "NO SE ENCONTRARON DATOS DE COLEGIACION ANUAL";
            $resultado['clase'] = 'alert alert-warning';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando DATOS DE COLEGIACION ANUAL";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }

    return $resultado;
}

    public function agregarColegiacionAnual($periodo, $cuotas, $antiguedad, $importe, $vencimientoCuotaUno, $pagoTotal, $vencimientoPagoTotal) {
    $resultado = array();
    try {
        $db = Database::getConnection();
        $sql = "INSERT INTO colegiacion_anual
                (Periodo, Antiguedad, Cuotas, Importe, PrimerVencimiento, PagoTotal, VencimientoPagoTotal, IdUsuario, FechaCarga)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $db->prepare($sql);
        $stmt->execute([$periodo, $antiguedad, $cuotas, $importe, $vencimientoCuotaUno, $pagoTotal, $vencimientoPagoTotal, $_SESSION['user_id']]);
        $resultado['estado'] = TRUE;
        $resultado['mensaje'] = "SE REGISTRO COLEGIACION ANUAL";
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "ERROR AL REGISTRAR COLEGIACION ANUAL " . $e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }

    return $resultado;
}

    public function editarColegiacionAnual($idColegiacionAnual, $periodo, $cuotas, $antiguedad, $importe, $vencimientoCuotaUno, $pagoTotal, $vencimientoPagoTotal) {
    $resultado = array();
    try {
        $db = Database::getConnection();
        $sql = "UPDATE colegiacion_anual
                SET Periodo = ?, Antiguedad = ?, Cuotas = ?, Importe = ?, PrimerVencimiento = ?, PagoTotal = ?, VencimientoPagoTotal = ?, IdUsuario = ?, FechaCarga = NOW()
                WHERE Id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$periodo, $antiguedad, $cuotas, $importe, $vencimientoCuotaUno, $pagoTotal, $vencimientoPagoTotal, $_SESSION['user_id'], $idColegiacionAnual]);
        $resultado['estado'] = TRUE;
        $resultado['mensaje'] = "SE MODIFICO COLEGIACION ANUAL";
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "ERROR AL MODIFICAR COLEGIACION ANUAL " . $e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }

    return $resultado;
}

    public function generarColegiacionAnual($idColegiado, $antiguedad, $estadoMatricular, $datosColegiacion, $descuentaPagos, $cuotasVerificar, $conect, $cuotasLiquidar) {
    //agrega colegiacion anual y cuotas
    $periodoActual = $datosColegiacion['periodo'];
    try {
        $db = Database::getConnection();
        $db->beginTransaction();
        $resultado = array();
        $resultado['estado'] = TRUE;

        $importeTotal = $datosColegiacion['importe'];
        $importeDescontar = 0;

        if (!isset($cuotasLiquidar)) {
            $resCuotasColegiacion = $this->obtenerColegiacionAnualCuotas($periodoActual);
            if ($resCuotasColegiacion['estado']) {
                $cuotasLiquidar = $resCuotasColegiacion['datos'];
            } else {
                $resultado['estado'] = FALSE;
                $resultado['mensaje'] = "ERROR AL BUSCAR CUOTAS colegiacion_anual_cuotas";
                $resultado['clase'] = 'alert alert-danger';
                $resultado['icono'] = 'glyphicon glyphicon-remove';
            }
        }

        if ($resultado['estado']) {
            //se agrega colegiadodeudaanual
            $sql = "INSERT INTO colegiadodeudaanual
                    (IdColegiado, Periodo, Importe, Cuotas, Antiguedad, EstadoMatricular, FechaCreacion, ImporteDescuento)
                    VALUE (?, ?, ?, ?, ?, ?, date(now()), ?)";
            $stmt = $db->prepare($sql);
            $stmt->execute([$idColegiado, $periodoActual, $datosColegiacion['importe'], $datosColegiacion['cuotas'], $antiguedad, $estadoMatricular, $importeDescontar]);
            $idColegiadoDeudaAnual = $db->lastInsertId();

            $sql = "INSERT INTO log_tabla
                (Tabla, IdTabla, Fecha, TipoMovimiento, IdUsuario)
                VALUES ('colegiadodeudaanual', ?, now(), 'alta', ?)";
            $stmt = $db->prepare($sql);
            $stmt->execute([$idColegiadoDeudaAnual, $_SESSION['user_id']]);

            $resultado['idColegiadoDeudaAnual'] = $idColegiadoDeudaAnual;
            $resultado['estado'] = TRUE;
            //agrego las cuotas
            foreach ($cuotasLiquidar as $datosCuota) {
                //si no es de la misma antiguedad lo salteo
                if ($antiguedad <> $datosCuota['antiguedad']) { continue; }

                $primerVencimiento = $datosCuota['fechaVencimiento'];
                $segundoVencimiento = $datosCuota['fechaVencimiento'];
                $cuota = $datosCuota['cuota'];
                $importe = $datosCuota['importe'];
                $recargo = $datosCuota['importe'];
                $estadoCuota = 1;
                if ($primerVencimiento <= date('Y-m-d')) {
                    $estadoCuota = 5;
                }
                $sql1 = "INSERT INTO colegiadodeudaanualcuotas
                        (IdColegiadoDeudaAnual, Cuota, Importe, FechaVencimiento, Recargo, SegundoVencimiento, Estado)
                        VALUE (?, ?, ?, ?, ?, ?, ?)";
                $stmt1 = $db->prepare($sql1);
                $stmt1->execute([$idColegiadoDeudaAnual, $cuota, $importe, $primerVencimiento, $recargo, $segundoVencimiento, $estadoCuota]);
                if ($db->errorCode() != '00000') {
                    $resultado['estado'] = FALSE;
                    $resultado['mensaje'] .= "ERROR AL AGREGAR CUOTAS DE COLEGIACION";
                    $resultado['clase'] = 'alert alert-danger';
                    $resultado['icono'] = 'glyphicon glyphicon-remove';
                }
            }

            //se inserta el pago total si no esta vencido
            if ($resultado['estado']) {
                if ($datosColegiacion['vencimientoPagoTotal'] > date('Y-m-d')) {
                    if ($importeDescontar > 0) {
                        $importePagoTotal = ($datosColegiacion['importe'] - $importeDescontar) * 0.90;
                    } else {
                        $importePagoTotal = $datosColegiacion['pagoTotal'];
                    }

                    $sql1 = "INSERT INTO colegiadodeudaanualtotal
                            (IdColegiadoDeudaAnual, Importe, FechaVencimiento, IdEstado)
                            VALUE (?, ?, ?, ?)";
                    $stmt1 = $db->prepare($sql1);
                    $stmt1->execute([$idColegiadoDeudaAnual, $importePagoTotal, $datosColegiacion['vencimientoPagoTotal'], $estadoCuota]);
                    if ($db->errorCode() != '00000') {
                        $resultado['estado'] = FALSE;
                        $resultado['mensaje'] .= "ERROR AL AGREGAR PAGO TOTAL DE COLEGIACION";
                        $resultado['clase'] = 'alert alert-danger';
                        $resultado['icono'] = 'glyphicon glyphicon-remove';
                    }
                }
            } else {
                $resultado['estado'] = FALSE;
                $resultado['mensaje'] .= "ERROR AL BUSCAR CUOTAS DE COLEGIACION";
                $resultado['clase'] = 'alert alert-danger';
                $resultado['icono'] = 'glyphicon glyphicon-remove';
            }
        }

        if ($resultado['estado']) {
            $resultado['estado'] = TRUE;
            $resultado['mensaje'] = 'SE GENERO LA DEUDA ANUAL CORRECTAMENTE';
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
            $resultado['idColegiadoDeudaAnual'] = $idColegiadoDeudaAnual;
            $db->commit();
            return $resultado;
        } else {
            $db->rollBack();
            return $resultado;
        }
    } catch (PDOException $e) {
        $db->rollBack();
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "Error: " . $e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
        return $resultado;
    }
}

    public function regenerarColegiacionAnual_2021($idColegiadoDeudaAnual, $cuotas, $pagoTotal) {
    //agrega colegiacion anual y cuotas
    try {
        $db = Database::getConnection();
        $db->beginTransaction();
        $resultado = array();
        $resultado['estado'] = TRUE;
        $resultado['mensaje'] = "";
        foreach ($cuotas as $cuotaGenera) {
            $estadoCuota = 1;
            $cuota = $cuotaGenera['cuota'];
            $vencimiento = $cuotaGenera['vencimiento'];
            $importe = $cuotaGenera['importe'];
            $importeTotal = $cuotaGenera['importeTotal'];
            //si la fecha de vencimiento de la cuota es dentro de 10 dias, entonces no se cobra
            if ($vencimiento <= date('Y-m-d')) {
                $estadoCuota = 5;
            }

            $segundoVencimiento = $vencimiento;
            $recargo = $importe;
            $sql1 = "INSERT INTO colegiadodeudaanualcuotas
                (IdColegiadoDeudaAnual, Cuota, Importe, FechaVencimiento, Recargo, SegundoVencimiento, Estado)
                VALUE (?, ?, ?, ?, ?, ?, ?)";
            $stmt1 = $db->prepare($sql1);
            $stmt1->execute([$idColegiadoDeudaAnual, $cuota, $importe, $vencimiento, $recargo, $vencimiento, $estadoCuota]);
            if ($db->errorCode() != '00000') {
                $resultado['estado'] = FALSE;
                $resultado['mensaje'] .= "ERROR AL AGREGAR CUOTAS DE COLEGIACION";
                $resultado['clase'] = 'alert alert-danger';
                $resultado['icono'] = 'glyphicon glyphicon-remove';
            }
        }

        //carga el importe total en colegiadodeudaanual
        if ($resultado['estado']) {
            $sql1 = "UPDATE colegiadodeudaanual
                SET Importe = ?, FechaCreacion = DATE(NOW())
                WHERE Id = ?";
            $stmt1 = $db->prepare($sql1);
            $stmt1->execute([$importeTotal, $idColegiadoDeudaAnual]);
            if ($db->errorCode() != '00000') {
                $resultado['estado'] = FALSE;
                $resultado['mensaje'] .= "ERROR AL ACTUALIZAR EL TOTAL DE COLEGIACION";
                $resultado['clase'] = 'alert alert-danger';
                $resultado['icono'] = 'glyphicon glyphicon-remove';
            }
        }

        //se inserta el pago total si no esta vencido
        if ($resultado['estado']) {
            if ($pagoTotal['vencimiento'] > date('Y-m-d')) {
                $sql1 = "INSERT INTO colegiadodeudaanualtotal
                            (IdColegiadoDeudaAnual, Importe, FechaVencimiento, IdEstado)
                            VALUE (?, ?, ?, ?)";
                $stmt1 = $db->prepare($sql1);
                $stmt1->execute([$idColegiadoDeudaAnual, $pagoTotal['importe'], $pagoTotal['vencimiento'], $estadoCuota]);
                if ($db->errorCode() != '00000') {
                    $resultado['estado'] = FALSE;
                    $resultado['mensaje'] .= "ERROR AL AGREGAR PAGO TOTAL DE COLEGIACION";
                    $resultado['clase'] = 'alert alert-danger';
                    $resultado['icono'] = 'glyphicon glyphicon-remove';
                }
            }
        } else {
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] .= "ERROR AL BUSCAR CUOTAS DE COLEGIACION";
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-remove';
        }

        if ($resultado['estado']) {
            $resultado['estado'] = TRUE;
            $resultado['mensaje'] = 'SE GENERO LA DEUDA ANUAL CORRECTAMENTE';
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
            $resultado['idColegiadoDeudaAnual'] = $idColegiadoDeudaAnual;
            $db->commit();
            return $resultado;
        } else {
            $db->rollBack();
            return $resultado;
        }
    } catch (PDOException $e) {
        $db->rollBack();
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "Error: " . $e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
        return $resultado;
    }
}

    public function generarColegiacionAnual_DividiendoPorCuotas($idColegiado, $antiguedad, $estadoMatricular, $datosColegiacion, $descuentaPagos, $cuotasVerificar, $conect) {
    //agrega colegiacion anual y cuotas
    $periodoActual = $datosColegiacion['periodo'];
    try {
        $db = Database::getConnection();
        $db->beginTransaction();
        $resultado = array();

        $importeTotal = $datosColegiacion['importe'];
        $importeDescontar = 0;

        //se agrega colegiadodeudaanual
        $sql = "INSERT INTO colegiadodeudaanual
                (IdColegiado, Periodo, Importe, Cuotas, Antiguedad, EstadoMatricular, FechaCreacion, ImporteDescuento)
                VALUE (?, ?, ?, ?, ?, ?, date(now()), ?)";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idColegiado, $periodoActual, $datosColegiacion['importe'], $datosColegiacion['cuotas'], $antiguedad, $estadoMatricular, $importeDescontar]);
        $idColegiadoDeudaAnual = $db->lastInsertId();

        $sql = "INSERT INTO log_tabla
            (Tabla, IdTabla, Fecha, TipoMovimiento, IdUsuario)
            VALUES ('colegiadodeudaanual', ?, now(), 'alta', ?)";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idColegiadoDeudaAnual, $_SESSION['user_id']]);

        $resultado['idColegiadoDeudaAnual'] = $idColegiadoDeudaAnual;
        $resultado['estado'] = TRUE;
        //agrego las cuotas
        $primerVencimiento = new DateTime($datosColegiacion['vencimientoCuotaUno']);
        $importeAnual = $importeTotal;
        $importe = $importeTotal / $datosColegiacion['cuotas'];

        $importeRedondeado = round($importe, -1);
        $importeRedondeadoTotal = $importeRedondeado * $datosColegiacion['cuotas'];
        $importeUltimaCuota = $importeRedondeado + ($importeAnual - $importeRedondeadoTotal);
        $importe = $importeRedondeado;
        $cuota = 1;
        while ($cuota <= $datosColegiacion['cuotas'] && $resultado['estado']) {
            $estadoCuota = 1;

            if ($primerVencimiento->format('Y-m-d') <= date('Y-m-d')) {
                $estadoCuota = 5;
            }

            $segundoVencimiento = $primerVencimiento->format('Y-m-d');
            if ($cuota == $datosColegiacion['cuotas']) {
                $importe = $importeUltimaCuota;
            }
            $recargo = $importe;
            $sql1 = "INSERT INTO colegiadodeudaanualcuotas
                    (IdColegiadoDeudaAnual, Cuota, Importe, FechaVencimiento, Recargo, SegundoVencimiento, Estado)
                    VALUE (?, ?, ?, ?, ?, ?, ?)";
            $stmt1 = $db->prepare($sql1);
            $stmt1->execute([$idColegiadoDeudaAnual, $cuota, $importe, $primerVencimiento->format('Y-m-d'), $recargo, $segundoVencimiento, $estadoCuota]);
            if ($db->errorCode() != '00000') {
                $resultado['estado'] = FALSE;
                $resultado['mensaje'] .= "ERROR AL AGREGAR CUOTAS DE COLEGIACION";
                $resultado['clase'] = 'alert alert-danger';
                $resultado['icono'] = 'glyphicon glyphicon-remove';
            }

            $cuota++;
            $primerVencimiento->add(new DateInterval('P1D'));
            $primerVencimiento->modify('last day of this month');
        }

        //se inserta el pago total si no esta vencido
        if ($resultado['estado']) {
            if ($datosColegiacion['vencimientoPagoTotal'] > date('Y-m-d')) {
                if ($importeDescontar > 0) {
                    $importePagoTotal = ($datosColegiacion['importe'] - $importeDescontar) * 0.90;
                } else {
                    $importePagoTotal = $datosColegiacion['pagoTotal'];
                }

                $sql1 = "INSERT INTO colegiadodeudaanualtotal
                        (IdColegiadoDeudaAnual, Importe, FechaVencimiento, IdEstado)
                        VALUE (?, ?, ?, ?)";
                $stmt1 = $db->prepare($sql1);
                $stmt1->execute([$idColegiadoDeudaAnual, $importePagoTotal, $datosColegiacion['vencimientoPagoTotal'], $estadoCuota]);
                if ($db->errorCode() != '00000') {
                    $resultado['estado'] = FALSE;
                    $resultado['mensaje'] .= "ERROR AL AGREGAR PAGO TOTAL DE COLEGIACION";
                    $resultado['clase'] = 'alert alert-danger';
                    $resultado['icono'] = 'glyphicon glyphicon-remove';
                }
            }
        } else {
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] .= "ERROR AL BUSCAR CUOTAS DE COLEGIACION";
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-remove';
        }

        if ($resultado['estado']) {
            $resultado['estado'] = TRUE;
            $resultado['mensaje'] = 'SE GENERO LA DEUDA ANUAL CORRECTAMENTE';
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
            $resultado['idColegiadoDeudaAnual'] = $idColegiadoDeudaAnual;
            $db->commit();
            return $resultado;
        } else {
            $db->rollBack();
            return $resultado;
        }
    } catch (PDOException $e) {
        $db->rollBack();
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "Error: " . $e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
        return $resultado;
    }
}
}
