<?php
class informeContableLogic {

    function obtenerInformePorPeriodo($periodo) {
        try {
            $db = Database::getConnection();
            $sql="SELECT Id, Periodo, MesProcesado, Origen, FechaProceso, IdUsuario, Borrado, Path
                FROM informe_contable
                WHERE Periodo = ?
                ORDER BY MesProcesado";
            $stmt = $db->prepare($sql);
            $stmt->execute([$periodo]);
            $rows = $stmt->fetchAll();

            $resultado = array();
            $datos = array();
            foreach ($rows as $r) {
                $row = array(
                    'id' => $r['Id'],
                    'periodo' => $r['Periodo'],
                    'mes' => $r['MesProcesado'],
                    'origen' => $r['Origen'],
                    'fechaProceso' => $r['FechaProceso'],
                    'idUsuario' => $r['IdUsuario'],
                    'borrado' => $r['Borrado'],
                    'path' => $r['Path']
                );
                array_push($datos, $row);
            }
            $resultado['estado'] = TRUE;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } catch (PDOException $e) {
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "Error buscando Informe Contable";
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }

        return $resultado;
    }

    function obtenerInformeContablePorId($id) {
        $resultado = array();
        try {
            $db = Database::getConnection();
            $sql="SELECT Periodo, MesProcesado, Origen, FechaProceso, Borrado, Path FROM informe_contable WHERE Id = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$id]);
            $row = $stmt->fetch();
            if ($row) {
                $datos = array(
                    'id' => $id,
                    'periodo' => $row['Periodo'],
                    'mes' => $row['MesProcesado'],
                    'origen' => $row['Origen'],
                    'fechaProceso' => $row['FechaProceso'],
                    'path' => $row['Path'],
                    'borrado' => $row['Borrado']
                );
                $resultado['estado'] = true;
                $resultado['mensaje'] = "OK";
                $resultado['datos'] = $datos;
                $resultado['clase'] = 'alert alert-success';
                $resultado['icono'] = 'glyphicon glyphicon-ok';
            } else {
                $resultado['estado'] = false;
                $resultado['mensaje'] = "No se ecntontro el informe_contable";
                $resultado['clase'] = 'alert alert-danger';
                $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
            }
        } catch (PDOException $e) {
            $resultado['estado'] = false;
            $resultado['mensaje'] = "Error buscando informe_contable";
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }

        return $resultado;
    }

    function generarInformeContable($periodo, $mesProcesado, $origen) {
        try {
            $db = Database::getConnection();
            $db->beginTransaction();
            $resultado = array();
            if (isset($origen)) {
                $sql="SELECT Id FROM informe_contable
                     WHERE Periodo = ? AND MesProcesado = ? AND Origen = ?";
                $stmt = $db->prepare($sql);
                $stmt->execute([$periodo, $mesProcesado, $origen]);
            } else {
                $sql="SELECT count(Id) FROM informe_contable
                     WHERE Periodo = ? AND MesProcesado = ?";
                $stmt = $db->prepare($sql);
                $stmt->execute([$periodo, $mesProcesado]);
            }
            $row = $stmt->fetch();
            $id = $row ? $row[0] : null;

            if (isset($id) && $id > 0) {
                $resultado['estado'] = TRUE;
                $resultado['mensaje'] = "El informe_contable del período ".$periodo." ya está procesado";
                $resultado['clase'] = 'alert alert-danger';
                $resultado['icono'] = 'glyphicon glyphicon-remove';
            } else {
                //ahora se genera cada origen
                $fechaDesde = substr($mesProcesado, 0, 4).'-'.substr($mesProcesado, 4, 2).'-01';
                $date = new DateTime($fechaDesde);
                $date->modify('last day of this month');
                $fechaHasta = $date->format('Y-m-d');

                //LIQUIDACION
                $origen = 'Liquidacion';
                $periodo = PERIODO_ACTUAL;
                $sql="INSERT INTO informe_contable
                    (Periodo, MesProcesado, Origen, FechaProceso, IdUsuario)
                    VALUES (?, ?, ?, NOW(), ?)";
                $stmt = $db->prepare($sql);
                $stmt->execute([$periodo, $mesProcesado, $origen, $_SESSION['user_id']]);
                $idInforme = $db->lastInsertId();
                $resultado['estado'] = TRUE;

                $sql_liq = "SELECT c.Matricula, p.Apellido, p.Nombres, da.Importe, da.FechaCreacion
                    FROM colegiadodeudaanual da
                    INNER JOIN colegiado c ON c.Id = da.IdColegiado
                    INNER JOIN persona p ON p.Id = c.IdPersona
                    WHERE da.Periodo = ?
                    AND da.Estado <> 'B'
                    AND da.FechaCreacion BETWEEN ? AND ?";
                $stmt_liq = $db->prepare($sql_liq);
                $stmt_liq->execute([$periodo, $fechaDesde, $fechaHasta]);
                $rows_liq = $stmt_liq->fetchAll();

                foreach ($rows_liq as $r_liq) {
                    $matricula = $r_liq['Matricula'];
                    $apellido = $r_liq['Apellido'];
                    $nombre = $r_liq['Nombres'];
                    $importe = $r_liq['Importe'];
                    $fechaCreacion = $r_liq['FechaCreacion'];

                    $tipoComprobante = 'FC ';
                    $numeroComprobante = PERIODO_ACTUAL.rellenarCeros($matricula, 8);
                    $apellidoNombre = trim($apellido).' '.trim($nombre);

                    $sql_det = "INSERT INTO informe_contable_detalle (IdInformeContable, Recibo, FechaApertura, Importe, Periodo, Matricula, ApellidoNombre)
                        VALUES (?, ?, ?, ?, ?, ?, ?)";
                    $stmt_det = $db->prepare($sql_det);
                    $stmt_det->execute([$idInforme, $matricula, $fechaCreacion, $importe, $periodo, $matricula, $apellidoNombre]);
                    $idInformeDetalle = $db->lastInsertId();

                    $clienteNombre = $this->agregarEspaciosDerecha($apellidoNombre, 40);
                    $cliente = rellenarCeros($matricula, 6);
                    $importe_linea = number_format($importe, 2, '.', '');
                    $importe_linea = str_pad($importe_linea, 16, "0", STR_PAD_LEFT);
                    $fecha = substr($fechaHasta, 0, 4).substr($fechaHasta, 5, 2).substr($fechaHasta, 8, 2);

                    $linea_archivo = $tipoComprobante.'X'.$numeroComprobante.'        '.$fecha.$cliente.$clienteNombre.'  2  3                                      CC             '.$importe_linea.'      1   C';
                    $sql_cab = "INSERT INTO informe_contable_cabecera (IdInformeContableDetalle, LineaArchivo) VALUES (?, ?)";
                    $stmt_cab = $db->prepare($sql_cab);
                    $stmt_cab->execute([$idInformeDetalle, $linea_archivo]);

                    $detalle = 'PERIODO ACTUAL ('.$periodo.')                             ';
                    $linea_archivo = $tipoComprobante.'X'.$numeroComprobante.'        '.$fecha.$cliente.'C'.'111                    '.'1               '.'                '.$detalle.$importe_linea.'        '.'        '.'                '.'                '.$importe_linea.'                '.'                '.'                '.'    '.'                '.'3'.'  '.'                '.'   '.'                          '.'        '.$importe_linea;

                    $sql_item = "INSERT INTO informe_contable_item (IdInformeContableDetalle, LineaArchivo) VALUES (?, ?)";
                    $stmt_item = $db->prepare($sql_item);
                    $stmt_item->execute([$idInformeDetalle, $linea_archivo]);
                } //fin foreach liq
                //FIN LIQUIDACION

                /* MEDIOS DE PAGO */
                if ($resultado['estado']) {
                    $origen = 'MediosDePago';
                    $periodo = PERIODO_ACTUAL;
                    $sql="INSERT INTO informe_contable
                        (Periodo, MesProcesado, Origen, FechaProceso, IdUsuario)
                        VALUES (?, ?, ?, NOW(), ?)";
                    $stmt = $db->prepare($sql);
                    $stmt->execute([$periodo, $mesProcesado, $origen, $_SESSION['user_id']]);
                    $idInforme = $db->lastInsertId();
                    $resultado['estado'] = TRUE;

                    $sql_mdp = "SELECT lp.CodigoCaja, cob.FechaApertura, cd.TipoPago, cd.Importe, if (cd.TipoPago = 3, (SELECT (cd.Importe - dac.Importe) FROM colegiadodeudaanualcuotas dac WHERE dac.Id = cd.Recibo), if (cd.TipoPago = 4, (cd.Importe - (SELECT SUM(dac.Importe) FROM notificacioncolegiadodeuda ncd INNER JOIN  colegiadodeudaanualcuotas dac ON dac.Id = ncd.IdColegiadoDeudaAnualCuota WHERE ncd.IdNotificacionColegiado = cd.Recibo)), 0)) AS Punitorio, cd.FechaPago, cd.Recibo, cd.Cuota, cd.Periodo, cd.IdColegiado, cd.IdAsistente, c.Matricula, p.Apellido, p.Nombres, ca.ApellidoNombre
                            FROM cobranzadetalle cd
                            INNER JOIN cobranza cob ON cob.Id = cd.IdLoteCobranza
                            INNER JOIN lugarpago lp ON lp.Id = cob.IdLugarPago
                            LEFT JOIN colegiado c ON c.Id = cd.IdColegiado
                            LEFT JOIN persona p ON p.Id = c.IdPersona
                            LEFT JOIN cursosasistente ca ON ca.Id = cd.IdAsistente
                            WHERE cob.FechaApertura BETWEEN ? AND ? AND cob.Estado = 'C'
                            ORDER BY cob.FechaApertura";
                    $stmt_mdp = $db->prepare($sql_mdp);
                    $stmt_mdp->execute([$fechaDesde, $fechaHasta]);
                    $rows_mdp = $stmt_mdp->fetchAll();

                    foreach ($rows_mdp as $r_mdp) {
                        if (!$resultado['estado']) break;
                        $codigoCaja = $r_mdp['CodigoCaja'];
                        $fechaApertura = $r_mdp['FechaApertura'];
                        $tipoPago = $r_mdp['TipoPago'];
                        $importe = $r_mdp['Importe'];
                        $punitorio = $r_mdp['Punitorio'];
                        $fechaPago = $r_mdp['FechaPago'];
                        $recibo = $r_mdp['Recibo'];
                        $cuota = $r_mdp['Cuota'];
                        $periodo = $r_mdp['Periodo'];
                        $idColegiado = $r_mdp['IdColegiado'];
                        $idAsistente = $r_mdp['IdAsistente'];
                        $matricula = $r_mdp['Matricula'];
                        $apellido = $r_mdp['Apellido'];
                        $nombre = $r_mdp['Nombres'];
                        $apellidoNombre = $r_mdp['ApellidoNombre'];

                        if (!isset($apellidoNombre) || $apellidoNombre == "") {
                            $apellidoNombre = trim($apellido).' '.trim($nombre);
                        }
                        $codigoCaja = $this->agregarEspaciosDerecha($codigoCaja, 15);
                        if ($tipoPago == 7) {
                            $matricula = $idAsistente;
                        }
                        if (!isset($cuota)) {
                            $cuota = 0;
                        }

                        $sql_det = "INSERT INTO informe_contable_detalle (IdInformeContable, TipoPago, Recibo, CodigoCaja, FechaApertura, Importe, Punitorio, FechaPago, Cuota, Periodo, Matricula, ApellidoNombre)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                        $stmt_det = $db->prepare($sql_det);
                        $stmt_det->execute([$idInforme, $tipoPago, $recibo, $codigoCaja, $fechaApertura, $importe, $punitorio, $fechaPago, $cuota, $periodo, $matricula, $apellidoNombre]);
                        $idInformeDetalle = $db->lastInsertId();

                        if (!isset($apellidoNombre) || $apellidoNombre == "") {
                            $codigoCliente = '000000';
                            $matricula = 0;
                            $clienteNombre = 'Matriculado No Especificado             ';
                        } else {
                            $cliente = trim($apellidoNombre);
                            $clienteNombre = $this->agregarEspaciosDerecha($apellidoNombre, 40);
                        }
                        $linea_archivo = array();
                        $codigoCliente = rellenarCeros($matricula, 6);
                        $puntoVenta = rellenarCeros($periodo, 4);
                        $numeroComprobante = rellenarCeros($cuota, 2).rellenarCeros($matricula, 6);
                        $fechaComprobante = substr($fechaPago, 0, 4).substr($fechaPago, 5, 2).substr($fechaPago, 8, 2);
                        $cuit = rellenarCeros($matricula, 15);
                        $condicionVenta = 'CC ';
                        $tipoCliente = 'C   ';
                        $tipoItem = 'C';
                        $precioUnitario = number_format($importe, 2, '.', '');
                        $precioUnitario = str_pad($precioUnitario, 16, "0", STR_PAD_LEFT);
                        $importePunitorio = number_format($punitorio, 2, '.', '');
                        $importePunitorio = str_pad($importePunitorio, 16, "0", STR_PAD_LEFT);
                        $importeRenglon = $precioUnitario;
                        switch ($tipoPago) {
                            case '3': //Cuota de Colegiacion
                            case '4': //Nota de deuda
                                if ($punitorio > 0) {
                                    $importeTotal = $importePunitorio;
                                    $reciboTipo = 'PU ';
                                    $condicionVenta = 'CC ';
                                    $codigoConcepto = '193                    ';
                                    $descripcion = 'Punitorios por pago fuera de termino.             ';
                                    if ($tipoPago == 4) {
                                        $puntoVenta = rellenarCeros(PERIODO_ACTUAL, 4);
                                        $numeroComprobante = rellenarCeros($recibo, 8);
                                    }
                                    $linea_archivo['punitorio_recibo_cabecera'] = $reciboTipo.'X'.$puntoVenta.$numeroComprobante.'        '.$fechaComprobante.$codigoCliente.$clienteNombre.'  '.'2  '.'3'.'           '.$cuit.'    '.'    '.'    '.$condicionVenta.'    '.'        '.$importeTotal.'  '.'  '.'  '.'1   '.$tipoItem;
                                    $linea_archivo['punitorio_recibo_item'] = $reciboTipo.'X'.$puntoVenta.$numeroComprobante.'        '.$fechaComprobante.$codigoCliente.$tipoItem.$codigoConcepto.'1               '.'                '.$descripcion.$importeTotal.'        '.'        '.'                '.'                '.$importeTotal.'                '.'                '.'                '.'    '.'                '.'3'.'  '.'                '.'   '.'                          '.'        '.$importeTotal;

                                    $reciboTipo = 'RCP';
                                    $importeTotal = '-'.substr($importePunitorio, 1, 15);
                                    $linea_archivo['punitorio_pago_cabecera'] = $reciboTipo.'X'.$puntoVenta.$numeroComprobante.'        '.$fechaComprobante.$codigoCliente.$clienteNombre.'  '.'2  '.'3'.'           '.$cuit.'    '.'    '.'    '.$condicionVenta.'    '.'        '.$importeTotal.'  '.'  '.'  '.'1   '.$tipoItem;
                                    $linea_archivo['punitorio_pago_item'] = $reciboTipo.'X'.$puntoVenta.$numeroComprobante.'        '.$fechaComprobante.$codigoCliente.$tipoItem.$codigoConcepto.'1               '.'                '.$descripcion.$importeTotal.'        '.'        '.'                '.'                '.$importeTotal.'                '.'                '.'                '.'    '.'                '.'3'.'  '.'                '.'   '.'                          '.'        '.$importeTotal;

                                    $importeTotal = $precioUnitario;
                                    $linea_archivo['punitorio_pago_recibo'] = $reciboTipo.'X'.$puntoVenta.$numeroComprobante.'        '.$fechaComprobante.$codigoCliente.'1'.'1  '.'UNI'.$codigoCaja.'   '.'        '.$importeTotal.'        '.'   '.'    '.'   '.' '.'               '.'                         '.'                         '.'                              '.'                              '.'        '.'   '.'                         '.'                              '.$importeTotal;
                                }

                                if ($importe > 0) {
                                    $importeTotal = $precioUnitario;
                                    if ($tipoPago == 3 && $periodo == PERIODO_ACTUAL) {
                                        if ($codigoCliente == '      ') {
                                            $reciboTipo = 'FC ';
                                            $puntoVenta = '9999';
                                            $numeroComprobante = rellenarCeros($recibo, 8);
                                            $codigoConcepto = rellenarCeros($periodo, 4).'                   ';
                                            $descripcion = '                                                  ';
                                            $linea_archivo['factura_sin_cliente_cabecera'] = $reciboTipo.'X'.$puntoVenta.$numeroComprobante.'        '.$fechaComprobante.$codigoCliente.$clienteNombre.'  '.'2  '.'3'.'           '.$cuit.'    '.'    '.'    '.$condicionVenta.'    '.'        '.$importeTotal.'  '.'  '.'  '.'1   '.$tipoItem;
                                            $linea_archivo['factura_sin_cliente_item'] = $reciboTipo.'X'.$puntoVenta.$numeroComprobante.'        '.$fechaComprobante.$codigoCliente.$tipoItem.$codigoConcepto.'1               '.'                '.$descripcion.$importeTotal.'        '.'        '.'                '.'                '.$importeTotal.'                '.'                '.'                '.'    '.'                '.'3'.'  '.'                '.'   '.'                          '.'        '.$importeTotal;
                                        }

                                        if ($punitorio > 0) {
                                            $cuotaPura = $importe - $punitorio;
                                        } else {
                                            $cuotaPura = $importe;
                                        }
                                        $precioUnitario = number_format($cuotaPura, 2, '.', '');
                                        $precioUnitario = str_pad($precioUnitario, 16, "0", STR_PAD_LEFT);
                                        $importeTotal = '-'.substr($precioUnitario, 1, 15);
                                        $importeRenglon = $precioUnitario;

                                        $reciboTipo = 'RC ';
                                        $condicionVenta = '   ';
                                        $codigoConcepto = rellenarCeros($periodo, 4).'                   ';
                                        $descripcion = 'PERIODO ACTUAL ('.rellenarCeros($periodo, 4).')                             ';
                                    } else {
                                        $reciboTipo = 'RC ';
                                        $condicionVenta = '   ';
                                        if ($periodo < (PERIODO_ACTUAL - 1)) {
                                            $codigoConcepto = '192                    ';
                                        } else {
                                            $codigoConcepto = rellenarCeros($periodo, 4).'                   ';
                                        }
                                        $importeTotal = '-'.substr($precioUnitario, 1, 15);
                                        $descripcion = 'Periodos Anteriores.                              ';
                                    }
                                } else {
                                    echo 'importe <= 0 : '.$puntoVenta.$numeroComprobante.'<br>';
                                }
                                break;

                            case '2':
                                $reciboTipo = 'RC ';
                                $codigoCliente = '999999';
                                $condicionVenta = '   ';
                                $tipoItem = 'C';
                                $tipoCliente = 'C   ';
                                $codigoConcepto = '191                    ';
                                $importeTotal = '-'.substr($precioUnitario, 1, 15);
                                $descripcion = 'Plan de pagos.                                    ';
                                break;

                            case '7':
                                $reciboTipo = 'RC ';
                                $codigoCliente = '999998';
                                $condicionVenta = '   ';
                                $tipoItem = 'C';
                                $tipoCliente = 'C   ';
                                $codigoConcepto = '411                    ';
                                $importeTotal = '-'.substr($precioUnitario, 1, 15);
                                $descripcion = 'Cursos.                                           ';
                                break;

                            case '8':
                                $reciboTipo = 'RC ';
                                $codigoCliente = '999998';
                                $condicionVenta = '   ';
                                $tipoItem = 'C';
                                $tipoCliente = 'C   ';
                                $codigoConcepto = rellenarCeros($periodo, 4).'                   ';
                                $importeTotal = '-'.substr($precioUnitario, 1, 15);
                                $descripcion = 'PAGO TOTAL PERIODO ACTUAL ('.rellenarCeros($periodo, 4).')                  ';
                                $numeroComprobante = '80'.rellenarCeros($matricula, 6);
                                break;

                            default:
                                $resultado['estado'] = FALSE;
                                $resultado['mensaje'] = "TipoPago no identificado (".$tipoPago.")";
                                break;
                        }
                        $linea_archivo['recibo_pago_cabecera'] = $reciboTipo.'X'.$puntoVenta.$numeroComprobante.'        '.$fechaComprobante.$codigoCliente.$clienteNombre.'  '.'2  '.'3'.'           '.$cuit.'    '.'    '.'    '.$condicionVenta.'    '.'        '.$importeTotal.'  '.'  '.'  '.'1   '.$tipoItem;
                        $linea_archivo['recibo_pago_item'] = $reciboTipo.'X'.$puntoVenta.$numeroComprobante.'        '.$fechaComprobante.$codigoCliente.$tipoItem.$codigoConcepto.'1               '.'                '.$descripcion.$importeTotal.'        '.'        '.'                '.'                '.$importeTotal.'                '.'                '.'                '.'    '.'                '.'3'.'  '.'                '.'   '.'                          '.'        '.$importeTotal;
                        $importeTotal = $precioUnitario;
                        $linea_archivo['recibo_pago_recibo'] = $reciboTipo.'X'.$puntoVenta.$numeroComprobante.'        '.$fechaComprobante.$codigoCliente.'1'.'1  '.'UNI'.$codigoCaja.'   '.'        '.$importeTotal.'        '.'   '.'    '.'   '.' '.'               '.'                         '.'                         '.'                              '.'                              '.'        '.'   '.'                         '.'                              '.$importeTotal;

                        foreach ($linea_archivo as $key => $value) {
                            switch ($key) {
                                case 'punitorio_recibo_cabecera':
                                case 'punitorio_pago_cabecera':
                                case 'factura_sin_cliente_cabecera':
                                case 'recibo_pago_cabecera':
                                    $sql_cab = "INSERT INTO informe_contable_cabecera (IdInformeContableDetalle, LineaArchivo) VALUES (?, ?)";
                                    $stmt_linea = $db->prepare($sql_cab);
                                    $stmt_linea->execute([$idInformeDetalle, $value]);
                                    break;
                                case 'punitorio_recibo_item':
                                case 'punitorio_pago_item':
                                case 'factura_sin_cliente_item':
                                case 'recibo_pago_item':
                                    $sql_item = "INSERT INTO informe_contable_item (IdInformeContableDetalle, LineaArchivo) VALUES (?, ?)";
                                    $stmt_linea = $db->prepare($sql_item);
                                    $stmt_linea->execute([$idInformeDetalle, $value]);
                                    break;
                                case 'punitorio_pago_recibo':
                                case 'recibo_pago_recibo':
                                    $sql_item = "INSERT INTO informe_contable_pago (IdInformeContableDetalle, LineaArchivo) VALUES (?, ?)";
                                    $stmt_linea = $db->prepare($sql_item);
                                    $stmt_linea->execute([$idInformeDetalle, $value]);
                                    break;
                                default:
                                    break;
                            }
                        }
                    } //fin foreach mdp
                }
                /* FIN MEDIOS DE PAGO */

                /* CAJAS DIARIAS */
                if ($resultado['estado']) {
                    $origen = 'Cajas';
                    $periodo = PERIODO_ACTUAL;
                    $sql="INSERT INTO informe_contable
                        (Periodo, MesProcesado, Origen, FechaProceso, IdUsuario)
                        VALUES (?, ?, ?, NOW(), ?)";
                    $stmt = $db->prepare($sql);
                    $stmt->execute([$periodo, $mesProcesado, $origen, $_SESSION['user_id']]);
                    $idInforme = $db->lastInsertId();
                    $resultado['estado'] = TRUE;

                    $sql_mdp = "SELECT 'REC' AS CodigoCaja, cd.FechaApertura, cdmd.CodigoPago AS IdTipoPago, tp.CodigoConcepto, tp.Detalle, cdmd.Monto, cdmd.Recargo AS Punitorio, cdm.Fecha, cdm.Numero AS Recibo, cdmd.Indice, cdmd.Cuota, cdmd.Periodo, cdm.IdColegiado, cdm.IdAsistente, c.Matricula, p.Apellido, p.Nombres, ca.ApellidoNombre
                            FROM cajadiariamovimientodetalle cdmd
                            INNER JOIN cajadiariamovimiento cdm ON cdm.Id = cdmd.IdCajaDiariaMovimiento
                            INNER JOIN cajadiaria cd ON cd.Id = cdm.IdCajaDiaria
                            LEFT JOIN tipopago tp ON tp.Id = cdmd.CodigoPago
                            LEFT JOIN colegiado c ON c.Id = cdm.IdColegiado
                            LEFT JOIN persona p ON p.Id = c.IdPersona
                            LEFT JOIN cursosasistente ca ON ca.Id = cdm.IdAsistente
                            WHERE cd.FechaApertura BETWEEN ? AND ? AND cdm.Estado <> 'A'
                            AND cd.Estado = 'C'
                            ORDER BY cdm.Fecha, cdm.Numero, cdmd.id";
                    $stmt_mdp = $db->prepare($sql_mdp);
                    $stmt_mdp->execute([$fechaDesde, $fechaHasta]);
                    $rows_cajas = $stmt_mdp->fetchAll();

                    foreach ($rows_cajas as $r_caja) {
                        if (!$resultado['estado']) break;
                        $codigoCaja = $r_caja['CodigoCaja'];
                        $fechaApertura = $r_caja['FechaApertura'];
                        $idTipoPago = $r_caja['IdTipoPago'];
                        $codigoConcepto = $r_caja['CodigoConcepto'];
                        $descripcion = $r_caja['Detalle'];
                        $importe = $r_caja['Monto'];
                        $punitorio = $r_caja['Punitorio'];
                        $fechaPago = $r_caja['Fecha'];
                        $recibo = $r_caja['Recibo'];
                        $indice = $r_caja['Indice'];
                        $cuota = $r_caja['Cuota'];
                        $periodo = $r_caja['Periodo'];
                        $idColegiado = $r_caja['IdColegiado'];
                        $idAsistente = $r_caja['IdAsistente'];
                        $matricula = $r_caja['Matricula'];
                        $apellido = $r_caja['Apellido'];
                        $nombre = $r_caja['Nombres'];
                        $apellidoNombre = $r_caja['ApellidoNombre'];

                        if (!isset($apellidoNombre) || $apellidoNombre == "") {
                            $apellidoNombre = trim($apellido).' '.trim($nombre);
                        }
                        $codigoCaja = $this->agregarEspaciosDerecha($codigoCaja, 15);
                        if ($idTipoPago == 10) {
                            $matricula = $idAsistente;
                        }
                        if (!isset($cuota)) {
                            $cuota = 0;
                        }

                        $sql_det = "INSERT INTO informe_contable_detalle (IdInformeContable, TipoPago, Recibo, CodigoCaja, FechaApertura, Importe, Punitorio, FechaPago, Cuota, Periodo, Matricula, ApellidoNombre)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                        $stmt_det = $db->prepare($sql_det);
                        $stmt_det->execute([$idInforme, $idTipoPago, $recibo, $codigoCaja, $fechaApertura, $importe, $punitorio, $fechaPago, $cuota, $periodo, $matricula, $apellidoNombre]);
                        $idInformeDetalle = $db->lastInsertId();

                        if (!isset($apellidoNombre) || $apellidoNombre == "") {
                            $codigoCliente = '000000';
                            $matricula = 0;
                            $clienteNombre = 'Matriculado No Especificado             ';
                        } else {
                            $cliente = trim($apellidoNombre);
                            $clienteNombre = $this->agregarEspaciosDerecha($apellidoNombre, 40);
                        }
                        $linea_archivo = array();
                        $codigoCliente = rellenarCeros($matricula, 6);
                        $puntoVenta = rellenarCeros($periodo, 4);
                        $numeroComprobante = rellenarCeros($cuota, 2).rellenarCeros($matricula, 6);
                        $fechaComprobante = substr($fechaPago, 0, 4).substr($fechaPago, 5, 2).substr($fechaPago, 8, 2);
                        $cuit = rellenarCeros($matricula, 15);
                        $condicionVenta = 'CC ';
                        $tipoCliente = 'C   ';
                        $tipoItem = 'C';

                        if ($importe < 0) {
                            $condicionVenta = '   ';
                            $recibo_nota_credito = 'NC ';
                            $puntoVenta = '9999';
                            $numeroComprobante = rellenarCeros($recibo, 8);
                            $precioUnitario = $importe * (-1);
                            $precioUnitario = number_format($precioUnitario, 2, '.', '');
                            $precioUnitario = str_pad($precioUnitario, 16, "0", STR_PAD_LEFT);
                            $importeTotal = '-'.substr($precioUnitario, 1, 15);
                            $descripcion = $this->agregarEspaciosDerecha($descripcion, 50);
                            $codigoConcepto = $this->agregarEspaciosDerecha($codigoConcepto, 23);
                        } else {
                            $importePunitorio = number_format($punitorio, 2, '.', '');
                            $importePunitorio = str_pad($importePunitorio, 16, "0", STR_PAD_LEFT);
                            $precioUnitario = number_format($importe, 2, '.', '');
                            $precioUnitario = str_pad($precioUnitario, 16, "0", STR_PAD_LEFT);
                            $importeTotal = $precioUnitario;
                            $importeRenglon = $precioUnitario;
                            $recibo_nota_credito = 'RC ';
                            switch ($idTipoPago) {
                                case '1':
                                case '3':
                                    if ($punitorio > 0) {
                                        $reciboTipo = 'PU ';
                                        $condicionVenta = 'CC ';
                                        $codigoConcepto = '193                    ';
                                        $descripcion = 'Punitorios por pago fuera de termino.             ';
                                        if ($tipoPago == 4) {
                                            $puntoVenta = rellenarCeros(PERIODO_ACTUAL, 4);
                                            $numeroComprobante = rellenarCeros($recibo, 8);
                                        }
                                        $linea_archivo['punitorio_recibo_cabecera'] = $reciboTipo.'X'.$puntoVenta.$numeroComprobante.'        '.$fechaComprobante.$codigoCliente.$clienteNombre.'  '.'2  '.'3'.'           '.$cuit.'    '.'    '.'    '.$condicionVenta.'    '.'        '.$importePunitorio.'  '.'  '.'  '.'1   '.$tipoItem;
                                        $linea_archivo['punitorio_recibo_item'] = $reciboTipo.'X'.$puntoVenta.$numeroComprobante.'        '.$fechaComprobante.$codigoCliente.$tipoItem.$codigoConcepto.'1               '.'                '.$descripcion.$importePunitorio.'        '.'        '.'                '.'                '.$importePunitorio.'                '.'                '.'                '.'    '.'                '.'3'.'  '.'                '.'   '.'                          '.'        '.$importePunitorio;

                                        $reciboTipo = 'RCP';
                                        $linea_archivo['punitorio_pago_recibo'] = $reciboTipo.'X'.$puntoVenta.$numeroComprobante.'        '.$fechaComprobante.$codigoCliente.'1'.'1  '.'UNI'.$codigoCaja.'   '.'        '.$importePunitorio.'        '.'   '.'    '.'   '.' '.'               '.'                         '.'                         '.'                              '.'                              '.'        '.'   '.'                         '.'                              '.$importePunitorio;
                                        $importePunitorio = '-'.substr($importePunitorio, 1, 15);
                                        $linea_archivo['punitorio_pago_cabecera'] = $reciboTipo.'X'.$puntoVenta.$numeroComprobante.'        '.$fechaComprobante.$codigoCliente.$clienteNombre.'  '.'2  '.'3'.'           '.$cuit.'    '.'    '.'    '.$condicionVenta.'    '.'        '.$importePunitorio.'  '.'  '.'  '.'1   '.$tipoItem;
                                        $linea_archivo['punitorio_pago_item'] = $reciboTipo.'X'.$puntoVenta.$numeroComprobante.'        '.$fechaComprobante.$codigoCliente.$tipoItem.$codigoConcepto.'1               '.'                '.$descripcion.$importePunitorio.'        '.'        '.'                '.'                '.$importePunitorio.'                '.'                '.'                '.'    '.'                '.'3'.'  '.'                '.'   '.'                          '.'        '.$importePunitorio;
                                    }

                                    if ($tipoPago == 3 && $periodo == PERIODO_ACTUAL) {
                                        if ($codigoCliente == '      ') {
                                            $reciboTipo = 'FC ';
                                            $puntoVenta = '9999';
                                            $numeroComprobante = rellenarCeros($recibo, 8);
                                            $codigoConcepto = rellenarCeros($periodo, 4).'                   ';
                                            $descripcion = '                                                  ';
                                            $linea_archivo['factura_sin_cliente_cabecera'] = $reciboTipo.'X'.$puntoVenta.$numeroComprobante.'        '.$fechaComprobante.$codigoCliente.$clienteNombre.'  '.'2  '.'3'.'           '.$cuit.'    '.'    '.'    '.$condicionVenta.'    '.'        '.$importeTotal.'  '.'  '.'  '.'1   '.$tipoItem;
                                            $linea_archivo['factura_sin_cliente_item'] = $reciboTipo.'X'.$puntoVenta.$numeroComprobante.'        '.$fechaComprobante.$codigoCliente.$tipoItem.$codigoConcepto.'1               '.'                '.$descripcion.$importeTotal.'        '.'        '.'                '.'                '.$importeTotal.'                '.'                '.'                '.'    '.'                '.'3'.'  '.'                '.'   '.'                          '.'        '.$importeTotal;
                                        }

                                        $condicionVenta = '   ';
                                        $codigoConcepto = rellenarCeros($periodo, 4).'                   ';
                                        $descripcion = 'PERIODO ACTUAL ('.rellenarCeros($periodo, 4).')                             ';
                                        if ($cuota == 0) {
                                            $codigoCliente = '999998';
                                            $codigoConcepto = rellenarCeros($periodo, 4).'                   ';
                                            $descripcion = 'PAGO TOTAL PERIODO ACTUAL ('.rellenarCeros($periodo, 4).')                  ';
                                            $numeroComprobante = '80'.rellenarCeros($matricula, 6);
                                        }
                                    } else {
                                        $condicionVenta = '   ';
                                        if ($periodo < (PERIODO_ACTUAL - 1)) {
                                            $codigoConcepto = '192                    ';
                                        } else {
                                            $codigoConcepto = rellenarCeros($periodo, 4).'                   ';
                                        }
                                        $importeTotal = '-'.substr($precioUnitario, 1, 15);
                                        $descripcion = 'Periodos Anteriores.                              ';
                                    }
                                    if ($punitorio > 0) {
                                        $cuotaPura = $importe - $punitorio;
                                    } else {
                                        $cuotaPura = $importe;
                                    }
                                    $precioUnitario = number_format($cuotaPura, 2, '.', '');
                                    $precioUnitario = str_pad($precioUnitario, 16, "0", STR_PAD_LEFT);
                                    $importeTotal = '-'.substr($precioUnitario, 1, 15);
                                    $importeRenglon = $precioUnitario;
                                    break;

                                default:
                                    $puntoVenta = '9000';
                                    if ($idTipoPago == '2') {
                                        $codigoCliente = '999999';
                                        $codigoConcepto = '191                    ';
                                        $importeTotal = '-'.substr($precioUnitario, 1, 15);
                                        $descripcion = 'Plan de pagos.                                    ';
                                    } else {
                                        if ($idTipoPago == '10') {
                                            $codigoCliente = '999998';
                                            $codigoConcepto = '411                    ';
                                            $descripcion = 'Cursos.                                           ';
                                        } else {
                                            $numeroComprobante = rellenarCeros($idTipoPago, 2).rellenarCeros($recibo, 6);
                                            $clienteNombre = $this->agregarEspaciosDerecha('Conceptos Varios', 40);
                                            $codigoCliente = '999998';
                                            $descripcion = $this->agregarEspaciosDerecha($descripcion, 50);
                                            $codigoConcepto = $this->agregarEspaciosDerecha($codigoConcepto, 23);
                                        }
                                    }
                                    $condicionVenta = 'CC ';
                                    $reciboTipo = 'FC ';
                                    $linea_archivo['factura_sin_cliente_cabecera'] = $reciboTipo.'X'.$puntoVenta.$numeroComprobante.'        '.$fechaComprobante.$codigoCliente.$clienteNombre.'  '.'2  '.'3'.'           '.$cuit.'    '.'    '.'    '.$condicionVenta.'    '.'        '.$importeTotal.'  '.'  '.'  '.'1   '.$tipoItem;
                                    $linea_archivo['factura_sin_cliente_item'] = $reciboTipo.'X'.$puntoVenta.$numeroComprobante.'        '.$fechaComprobante.$codigoCliente.$tipoItem.$codigoConcepto.'1               '.'                '.$descripcion.$importeTotal.'        '.'        '.'                '.'                '.$importeTotal.'                '.'                '.'                '.'    '.'                '.'3'.'  '.'                '.'   '.'                          '.'        '.$importeTotal;
                                    $recibo_nota_credito = 'RC ';
                                    $condicionVenta = '   ';
                                    $importeTotal = '-'.substr($precioUnitario, 1, 15);
                                    break;
                            }
                        }
                        $linea_archivo['recibo_pago_cabecera'] = $recibo_nota_credito.'X'.$puntoVenta.$numeroComprobante.'        '.$fechaComprobante.$codigoCliente.$clienteNombre.'  '.'2  '.'3'.'           '.$cuit.'    '.'    '.'    '.$condicionVenta.'    '.'        '.$importeTotal.'  '.'  '.'  '.'1   '.$tipoItem;
                        $linea_archivo['recibo_pago_item'] = $recibo_nota_credito.'X'.$puntoVenta.$numeroComprobante.'        '.$fechaComprobante.$codigoCliente.$tipoItem.$codigoConcepto.'1               '.'                '.$descripcion.$importeTotal.'        '.'        '.'                '.'                '.$importeTotal.'                '.'                '.'                '.'    '.'                '.'3'.'  '.'                '.'   '.'                          '.'        '.$importeTotal;
                        if ($recibo_nota_credito <> 'NC ') {
                            $importeTotal = $precioUnitario;
                            $linea_archivo['recibo_pago_recibo'] = $recibo_nota_credito.'X'.$puntoVenta.$numeroComprobante.'        '.$fechaComprobante.$codigoCliente.'1'.'1  '.'UNI'.$codigoCaja.'   '.'        '.$importeTotal.'        '.'   '.'    '.'   '.' '.'               '.'                         '.'                         '.'                              '.'                              '.'        '.'   '.'                         '.'                              '.$importeTotal;
                        }

                        foreach ($linea_archivo as $key => $value) {
                            switch ($key) {
                                case 'punitorio_recibo_cabecera':
                                case 'punitorio_pago_cabecera':
                                case 'factura_sin_cliente_cabecera':
                                case 'recibo_pago_cabecera':
                                    $sql_cab = "INSERT INTO informe_contable_cabecera (IdInformeContableDetalle, LineaArchivo) VALUES (?, ?)";
                                    $stmt_linea = $db->prepare($sql_cab);
                                    $stmt_linea->execute([$idInformeDetalle, $value]);
                                    break;
                                case 'punitorio_recibo_item':
                                case 'punitorio_pago_item':
                                case 'factura_sin_cliente_item':
                                case 'recibo_pago_item':
                                    $sql_item = "INSERT INTO informe_contable_item (IdInformeContableDetalle, LineaArchivo) VALUES (?, ?)";
                                    $stmt_linea = $db->prepare($sql_item);
                                    $stmt_linea->execute([$idInformeDetalle, $value]);
                                    break;
                                case 'punitorio_pago_recibo':
                                case 'recibo_pago_recibo':
                                    $sql_item = "INSERT INTO informe_contable_pago (IdInformeContableDetalle, LineaArchivo) VALUES (?, ?)";
                                    $stmt_linea = $db->prepare($sql_item);
                                    $stmt_linea->execute([$idInformeDetalle, $value]);
                                    break;
                                default:
                                    break;
                            }
                        }
                    } //fin foreach cajas
                }
                /* FIN CAJAS DIARIAS */
            }

            if ($resultado['estado']) {
                $resultado['mensaje'] = 'EL informe_contable HA SIDO AGREGADO CORRECTAMENTE';
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
            $resultado['mensaje'] = "Error: ".$e->getMessage();
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-remove';
            return $resultado;
        }
    }

    function obtenerInformesPorMesProcesado($mesProcesado) {
        try {
            $db = Database::getConnection();
            $sql="SELECT a.Id, a.Periodo, a.Origen, a.Path
                FROM informe_contable a
                WHERE a.MesProcesado = ? AND a.Borrado = 0";
            $stmt = $db->prepare($sql);
            $stmt->execute([$mesProcesado]);
            $rows = $stmt->fetchAll();

            $resultado = array();
            $datos = array();
            foreach ($rows as $r) {
                $row = array(
                    'id' => $r['Id'],
                    'periodo' => $r['Periodo'],
                    'origen' => $r['Origen'],
                    'path' => $r['Path']
                );
                array_push($datos, $row);
            }
            $resultado['estado'] = TRUE;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } catch (PDOException $e) {
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "Error buscando Informe Contable";
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }

        return $resultado;
    }

    function obtenerInformeCabeceraPorIdInforme($idInforme) {
        try {
            $db = Database::getConnection();
            $sql="SELECT a.LineaArchivo
                FROM informe_contable_cabecera a
                INNER JOIN informe_contable_detalle b ON b.Id = a.IdInformeContableDetalle
                WHERE b.IdInformeContable = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$idInforme]);
            $rows = $stmt->fetchAll();

            $resultado = array();
            $datos = array();
            foreach ($rows as $r) {
                array_push($datos, ['linea_archivo' => $r['LineaArchivo']]);
            }
            $resultado['estado'] = TRUE;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } catch (PDOException $e) {
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "Error buscando Informe Contable";
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }

        return $resultado;
    }

    function obtenerInformeItemsPorIdInforme($idInforme) {
        try {
            $db = Database::getConnection();
            $sql="SELECT a.LineaArchivo
                FROM informe_contable_item a
                INNER JOIN informe_contable_detalle b ON b.Id = a.IdInformeContableDetalle
                WHERE b.IdInformeContable = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$idInforme]);
            $rows = $stmt->fetchAll();

            $resultado = array();
            $datos = array();
            foreach ($rows as $r) {
                array_push($datos, ['linea_archivo' => $r['LineaArchivo']]);
            }
            $resultado['estado'] = TRUE;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } catch (PDOException $e) {
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "Error buscando Informe Contable";
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }

        return $resultado;
    }

    function obtenerInformePagosPorIdInforme($idInforme) {
        try {
            $db = Database::getConnection();
            $sql="SELECT a.LineaArchivo
                FROM informe_contable_pago a
                INNER JOIN informe_contable_detalle b ON b.Id = a.IdInformeContableDetalle
                WHERE b.IdInformeContable = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$idInforme]);
            $rows = $stmt->fetchAll();

            $resultado = array();
            $datos = array();
            foreach ($rows as $r) {
                array_push($datos, ['linea_archivo' => $r['LineaArchivo']]);
            }
            $resultado['estado'] = TRUE;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } catch (PDOException $e) {
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "Error buscando Informe Contable";
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }

        return $resultado;
    }

    function obtenerMesesYaProcesadosDelPeriodo($periodo) {
        try {
            $db = Database::getConnection();
            $sql="SELECT a.MesProcesado
                FROM informe_contable a
                WHERE a.Periodo = ?
                GROUP BY a.MesProcesado";
            $stmt = $db->prepare($sql);
            $stmt->execute([$periodo]);
            $rows = $stmt->fetchAll();

            $resultado = array();
            foreach ($rows as $r) {
                array_push($resultado, $r['MesProcesado']);
            }
        } catch (PDOException $e) {
            $resultado = array();
        }
        return $resultado;
    }

    function guardarNombreArchivo($idInforme, $pathFinal) {
        try {
            $db = Database::getConnection();
            $db->beginTransaction();
            $resultado = array();

            $sql="UPDATE informe_contable
                SET Path = ?
                WHERE Id = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$pathFinal, $idInforme]);
            $resultado['estado'] = TRUE;
            $resultado['mensaje'] = 'EL informe_contable HA SIDO ACTUALIZADO CORRECTAMENTE';
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
            $db->commit();
            return $resultado;
        } catch (PDOException $e) {
            $db->rollBack();
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "ERROR AL AGREGAR PATH y NOMBRE DE ARCHIVO EN informe_contable";
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-remove';
            return $resultado;
        }
    }

    function agregarInformeDetalle($idInforme, $tipoComprobante, $numeroComprobante, $fechaPago, $cliente, $codigoC, $concepto, $detalle, $importe, $lineaBejerman) {
        try {
            $db = Database::getConnection();
            $db->beginTransaction();
            $resultado = array();

            $sql="INSERT INTO informe_contable_detalle
                (idInformeContable, TipoComprobante, NumeroComprobante, FechaPago, Cliente, CodigoC, Concepto, Detalle, Importe, LineaBejerman)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $db->prepare($sql);
            $stmt->execute([$idInforme, $tipoComprobante, $numeroComprobante, $fechaPago, $cliente, $codigoC, $concepto, $detalle, $importe, $lineaBejerman]);
            $idDetalle = $db->lastInsertId();
            $resultado['estado'] = TRUE;
            $resultado['mensaje'] = 'EL informe_contable HA SIDO AGREGADO CORRECTAMENTE';
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
            $resultado['idDetalle'] = $idDetalle;
            $db->commit();
            return $resultado;
        } catch (PDOException $e) {
            $db->rollBack();
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "ERROR AL AGREGAR informe_contable";
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-remove';
            return $resultado;
        }
    }

    function procesarInformeTXT($archivo, $origen) {
        try {
            $db = Database::getConnection();
            $db->beginTransaction();
            $resultado = array();

            $sql="INSERT INTO informe_contable
                (Periodo, MesProcesado, Origen, FechaProceso, IdUsuario)
                VALUES (?, ?, ?, DATE(NOW()), ?)";
            $stmt = $db->prepare($sql);
            $stmt->execute([$periodo, $mesProcesado, $origen, $_SESSION['user_id']]);
            $idInforme = $db->lastInsertId();
            $resultado['estado'] = TRUE;
            $resultado['mensaje'] = 'EL informe_contable HA SIDO AGREGADO CORRECTAMENTE';
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
            $resultado['idInforme'] = $idInforme;
            $db->commit();
            return $resultado;
        } catch (PDOException $e) {
            $db->rollBack();
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "ERROR AL AGREGAR informe_contable";
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-remove';
            return $resultado;
        }
    }

    function contar_caracteres_en_array($string, $array_de_caracteres) {
        $conteo_global = [];
        foreach ($array_de_caracteres as $caracter) {
            preg_match_all('/' . preg_quote($caracter, '/') . '/', $string, $coincidencias);
            $conteo_global[$caracter] = count($coincidencias[0]);
        }
        return $conteo_global;
    }

    function agregarEspaciosDerecha($stringRecibido, $rellenoEspacios) {
        $array_de_caracteres = ['Ñ', 'ñ', 'á', 'Á', 'é', 'É', 'í', 'Í', 'ó', 'Ó', 'ú', 'Ú', 'ü', 'Ü', '´', 'ö', 'Ö', 'È', 'è', 'Ò', 'ò'];

        if (strlen($stringRecibido) > $rellenoEspacios) {
            $stringRecibido = substr($stringRecibido, 0, 40);
        }
        $conteo_caracteres = $this->contar_caracteres_en_array($stringRecibido, $array_de_caracteres);
        if (sizeof($conteo_caracteres) > 0) {
            $cantidadCaracteres = 0;
            foreach ($conteo_caracteres as $key => $value) {
                $cantidadCaracteres += $value;
            }
            $rellenoEspacios += $cantidadCaracteres;
        }

        $respuesta = str_pad($stringRecibido, $rellenoEspacios, " ", STR_PAD_RIGHT);

        return $respuesta;
    }


}
