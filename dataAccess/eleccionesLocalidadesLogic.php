<?php
class eleccionesLocalidades {

    function obtenerLocalidadesPorIdElecciones($idElecciones) {
        try {
            $db = Database::getConnection();
            $sql="SELECT eleccioneslocalidad.IdEleccionesLocalidad, eleccioneslocalidad.IdElecciones, eleccioneslocalidad.Localidad,
                eleccioneslocalidad.CantidadDelegados, eleccioneslocalidad.CantidadElectores, eleccioneslocalidad.CantidadValidos,
                eleccioneslocalidad.CantidadAnulados, eleccioneslocalidad.CantidadEnBlanco, eleccioneslocalidad.CocienteElectoral,
                eleccioneslocalidad.LocalidadDetalle, zonas.Nombre
                FROM eleccioneslocalidad
                INNER JOIN zonas ON(zonas.Zona = eleccioneslocalidad.Localidad)
                WHERE IdElecciones = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$idElecciones]);
            $rows = $stmt->fetchAll();

            $resultado = array();
            if (count($rows) > 0) {
                $datos = array();
                foreach ($rows as $row) {
                    $item = array (
                            'idEleccionesLocalidad' => $row['IdEleccionesLocalidad'],
                            'idElecciones' => $row['IdElecciones'],
                            'codigoLocalidad' => $row['Localidad'],
                            'cantDelegados' => $row['CantidadDelegados'],
                            'cantElectores' => $row['CantidadElectores'],
                            'cantValidos' => $row['CantidadValidos'],
                            'cantAnulados' => $row['CantidadAnulados'],
                            'cantEnBlanco' => $row['CantidadEnBlanco'],
                            'cociente' => $row['CocienteElectoral'],
                            'localidadDetalle' => $row['Nombre'],
                            'detalle' => $row['LocalidadDetalle']
                        );
                    array_push($datos, $item);
                }
                $resultado['estado'] = true;
                $resultado['mensaje'] = "OK";
                $resultado['datos'] = $datos;
                $resultado['clase'] = 'alert alert-success';
                $resultado['icono'] = 'glyphicon glyphicon-ok';
            } else {
                $resultado['estado'] = false;
                $resultado['mensaje'] = "No hay localidades";
                $resultado['clase'] = 'alert alert-warning';
                $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
            }
        } catch (PDOException $e) {
            $resultado['estado'] = false;
            $resultado['mensaje'] = "Error buscando localidades de las elecciones: " . $e->getMessage();
            $resultado['clase'] = 'alert alert-error';
            $resultado['icono'] = 'glyphicon glyphicon-remove';
        }

        return $resultado;
    }

    function obtenerEleccionesLocalidadPorId($idEleccionesLocalidad) {
        try {
            $db = Database::getConnection();
            $sql="SELECT el.IdEleccionesLocalidad, el.IdElecciones, el.Localidad, el.CantidadDelegados, el.CantidadElectores, el.CantidadValidos, el.CantidadAnulados, el.CantidadEnBlanco, el.CocienteElectoral, el.LocalidadDetalle, z.Nombre
                    FROM eleccioneslocalidad el
                    INNER JOIN zonas z ON z.Zona = el.Localidad
                    WHERE el.IdEleccionesLocalidad = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$idEleccionesLocalidad]);
            $row = $stmt->fetch();

            $resultado = array();
            if ($row) {
                $datos = array (
                            'idEleccionesLocalidad' => $row['IdEleccionesLocalidad'],
                            'idElecciones' => $row['IdElecciones'],
                            'codigoLocalidad' => $row['Localidad'],
                            'cantDelegados' => $row['CantidadDelegados'],
                            'cantElectores' => $row['CantidadElectores'],
                            'cantValidos' => $row['CantidadValidos'],
                            'cantAnulados' => $row['CantidadAnulados'],
                            'cantEnBlanco' => $row['CantidadEnBlanco'],
                            'cociente' => $row['CocienteElectoral'],
                            'localidadDetalle' => $row['Nombre']
                        );

                $resultado['estado'] = true;
                $resultado['mensaje'] = "OK";
                $resultado['datos'] = $datos;
                $resultado['clase'] = 'alert alert-success';
                $resultado['icono'] = 'glyphicon glyphicon-ok';
            } else {
                $resultado['estado'] = false;
                $resultado['mensaje'] = "No hay localidades";
                $resultado['clase'] = 'alert alert-warning';
                $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
            }
        } catch (PDOException $e) {
            $resultado['estado'] = false;
            $resultado['mensaje'] = "Error buscando localidades de las elecciones: " . $e->getMessage();
            $resultado['clase'] = 'alert alert-error';
            $resultado['icono'] = 'glyphicon glyphicon-remove';
        }

        return $resultado;
    }

    function agregarEleccionesLocalidades($idElecciones, $codigoLocalidad, $cantDelegados) {
        try {
            $db = Database::getConnection();
            $sql="INSERT INTO eleccioneslocalidad (IdElecciones, Localidad, CantidadDelegados, LocalidadDetalle)
                VALUES (?, ?, ?, (SELECT Nombre FROM zonas WHERE Zona = ?))";
            $stmt = $db->prepare($sql);
            $stmt->execute([$idElecciones, $codigoLocalidad, $cantDelegados, $codigoLocalidad]);
            $estadoConsulta = TRUE;
            $mensaje = 'Localidad HA SIDO AGREGADO';
        } catch (PDOException $e) {
            $estadoConsulta = FALSE;
            $mensaje = 'ERROR AL AGREGAR Localidad: ' . $e->getMessage();
        }
        $result = array();
        $result['estado'] = $estadoConsulta;
        $result['mensaje'] = $mensaje;
        return $result;
    }

    function editarEleccionesLocalidades($idEleccionesLocalidad, $cantDelegados) {
        try {
            $db = Database::getConnection();
            $sql="UPDATE eleccioneslocalidad
                    SET CantidadDelegados = ?
                    WHERE IdEleccionesLocalidad = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$cantDelegados, $idEleccionesLocalidad]);
            $estadoConsulta = TRUE;
            $mensaje = 'Localidad HA SIDO MODIFICADO';
        } catch (PDOException $e) {
            $estadoConsulta = FALSE;
            $mensaje = 'ERROR AL MODIFICAR Localidad: ' . $e->getMessage();
        }
        $result = array();
        $result['estado'] = $estadoConsulta;
        $result['mensaje'] = $mensaje;
        return $result;
    }

    function borrarEleccionesLocalidades($idEleccionesLocalidad){
        try {
            $db = Database::getConnection();
            $sql="DELETE FROM eleccioneslocalidad WHERE IdEleccionesLocalidad = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$idEleccionesLocalidad]);
            $estadoConsulta = TRUE;
            $mensaje = 'Localidad HA SIDO BORRADO';
        } catch (PDOException $e) {
            $estadoConsulta = FALSE;
            $mensaje = 'ERROR AL BORRAR Localidad: ' . $e->getMessage();
        }
        $result = array();
        $result['estado'] = $estadoConsulta;
        $result['mensaje'] = $mensaje;
        return $result;
    }

    function generarPadronPorLocalidad($codigoLocalidad, $idEleccionesLocalidad, $fechaCorte) {
        try {
            $db = Database::getConnection();
            $db->beginTransaction();
            $resultado = array();
            $periodoActual = PERIODO_ACTUAL;
            $sql = "INSERT INTO eleccioneslocalidadpadron (IdEleccionesLocalidad, IdColegiado, FechaCorte, EstadoTesoreria, AniosCargo, FechaCarga)
                    (SELECT ?, a.Id, ?,
                        if ((SELECT COUNT(*) FROM colegiadodeudaanualcuotas dac INNER JOIN colegiadodeudaanual da ON da.Id = dac.IdColegiadoDeudaAnual WHERE da.IdColegiado = a.Id and da.Periodo < ? AND dac.Estado = 1) = 0, if ((SELECT COUNT(*) FROM colegiadodeudaanualcuotas dac INNER JOIN colegiadodeudaanual da ON da.Id = dac.IdColegiadoDeudaAnual WHERE da.IdColegiado = a.Id and da.Periodo = ? AND dac.FechaVencimiento < ? AND dac.Estado = 1) = 0, 'A', 'D'), 'D') AS EstadoTesoreria,
                        TIMESTAMPDIFF(YEAR, a.FechaMatriculacion, ?) AS CantidadAnios,
                            NOW()

                    FROM colegiado as a
                    INNER JOIN persona p ON p.Id = a.IdPersona
                    LEFT JOIN colegiadodomicilioreal d ON a.Id = d.IdColegiado AND d.IdEstado=1
                    LEFT JOIN localidad l ON l.Id = d.IdLocalidad
                    LEFT JOIN zonas z ON z.Id = l.IdZona
                    LEFT JOIN matriculaexcluida me ON me.matricula = a.matricula
                    WHERE a.estado IN(0, 1, 5, 10)
                    AND me.matricula is null
                    AND z.Zona = ?
                    AND a.FechaMatriculacion <= ?
                    ORDER BY p.apellido, p.nombres, a.matricula)";
            $stmt = $db->prepare($sql);
            $stmt->execute([$idEleccionesLocalidad, $fechaCorte, $periodoActual, $periodoActual, $fechaCorte, $fechaCorte, $codigoLocalidad, $fechaCorte]);
            $resultado['estado'] = TRUE;
            $resultado['mensaje'] = 'EL PADRON FUE GENERADO CON EXITO';
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';

            $db->commit();
            return $resultado;
        } catch (PDOException $e) {
            $db->rollBack();
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "ERROR AL GENERAR PADRON -> " . $e->getMessage();
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-remove';
            return $resultado;
        }
    }

    function actualizarEstadoTesoreria($idEleccionesLocalidad, $fechaCorte) {
        try {
            $db = Database::getConnection();
            $db->beginTransaction();
            $resultado = array();
            $periodoActual = PERIODO_ACTUAL;
            $sql = "UPDATE eleccioneslocalidadpadron elp
                SET elp.EstadoTesoreria = (if (((SELECT COUNT(*) FROM agremiacionesdebito ad WHERE ad.IdColegiado = elp.IdColegiado AND ad.Borrado = 0) > 0), 'A', (if ((SELECT COUNT(*) FROM colegiadodeudaanualcuotas dac INNER JOIN colegiadodeudaanual da ON da.Id = dac.IdColegiadoDeudaAnual WHERE da.IdColegiado = elp.IdColegiado and da.Periodo < ? AND dac.Estado = 1) = 0, if ((SELECT COUNT(*) FROM colegiadodeudaanualcuotas dac INNER JOIN colegiadodeudaanual da ON da.Id = dac.IdColegiadoDeudaAnual WHERE da.IdColegiado = elp.IdColegiado and da.Periodo = ? AND dac.FechaVencimiento < ? AND dac.Estado = 1) = 0, 'A', 'D'), 'D'))))
                WHERE elp.IdEleccionesLocalidad = ?
                AND elp.EstadoTesoreria <> (if (((SELECT COUNT(*) FROM agremiacionesdebito ad WHERE ad.IdColegiado = elp.IdColegiado AND ad.Borrado = 0) > 0), 'A', (if ((SELECT COUNT(*) FROM colegiadodeudaanualcuotas dac INNER JOIN colegiadodeudaanual da ON da.Id = dac.IdColegiadoDeudaAnual WHERE da.IdColegiado = elp.IdColegiado and da.Periodo < ? AND dac.Estado = 1) = 0, if ((SELECT COUNT(*) FROM colegiadodeudaanualcuotas dac INNER JOIN colegiadodeudaanual da ON da.Id = dac.IdColegiadoDeudaAnual WHERE da.IdColegiado = elp.IdColegiado and da.Periodo = ? AND dac.FechaVencimiento < ? AND dac.Estado = 1) = 0, 'A', 'D'), 'D'))))";
            $stmt = $db->prepare($sql);
            $stmt->execute([$periodoActual, $periodoActual, $fechaCorte, $idEleccionesLocalidad, $periodoActual, $periodoActual, $fechaCorte]);
            $resultado['estado'] = TRUE;
            $resultado['mensaje'] = 'EL PADRON FUE ACTUALIZADO CON EXITO';
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';

            $db->commit();
            return $resultado;
        } catch (PDOException $e) {
            $db->rollBack();
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "ERROR AL GENERAR PADRON -> " . $e->getMessage();
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-remove';
            return $resultado;
        }
    }

    function existePadronGenerado($idEleccionesLocalidad, $fechaCorte) {
        try {
            $db = Database::getConnection();
            $sql="SELECT COUNT(*) AS Cantidad
                FROM eleccioneslocalidadpadron elp
                WHERE elp.IdEleccionesLocalidad = ? AND elp.FechaCorte = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$idEleccionesLocalidad, $fechaCorte]);
            $row = $stmt->fetch();
            if ($row && $row['Cantidad'] > 0) {
                return TRUE;
            }
        } catch (PDOException $e) {
            // silently fail
        }
        return FALSE;
    }

    function obtenerColegiadosParaImprimirPadron($idEleccionesLocalidad) {
        try {
            $db = Database::getConnection();
            $sql="SELECT a.matricula, p.apellido, p.nombres, a.estado, d.calle, d.numero, d.piso, d.departamento, l.Nombre AS localidadNombre, d.codigopostal, a.fechamatriculacion, a.Id, elp.EstadoTesoreria, elp.AniosCargo
                FROM eleccioneslocalidadpadron elp
                INNER JOIN colegiado a ON a.Id = elp.IdColegiado
                INNER JOIN persona p ON p.Id = a.IdPersona
                LEFT JOIN colegiadodomicilioreal d ON a.Id = d.IdColegiado AND d.IdEstado=1
                LEFT JOIN localidad l ON l.Id = d.IdLocalidad
                LEFT JOIN matriculaexcluida me ON me.matricula = a.matricula
                WHERE elp.IdEleccionesLocalidad = ?
                ORDER BY p.apellido, p.nombres, a.matricula";
            $stmt = $db->prepare($sql);
            $stmt->execute([$idEleccionesLocalidad]);
            $rows = $stmt->fetchAll();

            $resultado = array();
            if (count($rows) > 0) {
                $datos = array();
                foreach ($rows as $row) {
                    $item = array (
                            'matricula' => $row['matricula'],
                            'apellido' => $row['apellido'],
                            'nombre' => $row['nombres'],
                            'estado' => $row['estado'],
                            'calle' => $row['calle'],
                            'numero' => $row['numero'],
                            'piso' => $row['piso'],
                            'departamento' => $row['departamento'],
                            'localidadNombre' => $row['localidadNombre'],
                            'codigoPostal' => $row['codigopostal'],
                            'fechaMatriculacion' => $row['fechamatriculacion'],
                            'idColegiado' => $row['Id'],
                            'estadoTesoreria' => $row['EstadoTesoreria'],
                            'aniosCargo' => $row['AniosCargo']
                        );
                    array_push($datos, $item);
                }
                $resultado['estado'] = true;
                $resultado['mensaje'] = "OK";
                $resultado['datos'] = $datos;
                $resultado['clase'] = 'alert alert-success';
                $resultado['icono'] = 'glyphicon glyphicon-ok';
            } else {
                $resultado['estado'] = false;
                $resultado['mensaje'] = "No hay colegiados";
                $resultado['clase'] = 'alert alert-warning';
                $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
            }
        } catch (PDOException $e) {
            $resultado['estado'] = false;
            $resultado['mensaje'] = "Error buscando colegiados: " . $e->getMessage();
            $resultado['clase'] = 'alert alert-error';
            $resultado['icono'] = 'glyphicon glyphicon-remove';
        }

        return $resultado;
    }

    function imprimirPadron($idEleccionesLocalidad, $eleccionesLocalidades, $colegiados, $pdf, $pathOrigen) {
        $localidadDetalle = $eleccionesLocalidades['localidadDetalle'];
        define(TITULO_LOCALIDAD, $localidadDetalle);

        $pdf->SetPrintHeader(true);
        $pdf->SetPrintFooter(true);
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetAutoPageBreak(TRUE, 0);
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
        $pdf->SetFont('dejavusans', '', 8);
        $pdf->AddPage();

        $pdf->SetFont('dejavusans', '', 8);
        $cantidad = 0;
        $alturaLinea = 7;
        $pdf->SetXY(0, 40);
        $linea = 1;
        $lineaMaximo = 45;
        $letraAnterior = "";
        foreach ($colegiados as $colegiado){
            $matricula = $colegiado['matricula'];
            $apellidoNombre = $colegiado['apellido'].' '.$colegiado['nombre'];
            $localidadNombre = $colegiado['localidadNombre'];
            $estadoTesoreria = $colegiado['estadoTesoreria'];
            $aniosCargo = $colegiado['aniosCargo'];

            if ($aniosCargo > 10) {
                $codigoAntiguedad = "T";
            } else {
                if ($aniosCargo < 2) {
                    $codigoAntiguedad = "";
                } else {
                    $codigoAntiguedad = "C";
                }
            }

            if ($localidadDetalle == "LA PLATA") {
                if (substr($apellidoNombre, 0, 1) <> $letraAnterior) {
                    if ($letraAnterior <> '') {
                        $pdf->Ln(5);
                        $pdf->MultiCell(0, 0, 'Total de la letra '.$letraAnterior.': '.$totalLetra, 0, 'C', false, 1, '10', '');
                        $linea = $lineaMaximo;
                    }
                    $letraAnterior = substr($apellidoNombre, 0, 1);
                    $totalLetra = 0;
                }
            }

            if ($linea >= $lineaMaximo) {
                $pdf->AddPage();
                $pdf->SetFont('dejavusans', '', 8);
                $linea = 1;
                $pdf->SetXY(0, 40);
            }

            if ($estadoTesoreria == "D") {
                $pos_y = $pdf->GetY() - 2;
                $pdf->Rect(10, $pos_y, 195, 7, 'DF', "", array(220, 220, 200));
            }
            $pdf->MultiCell(20, 0, $matricula, 0, 'R', false, 0, '10', '');
            $pdf->MultiCell(0, 0, $apellidoNombre, 0, 'L', false, 0, '30', '');
            $pdf->MultiCell(0, 0, $localidadNombre, 0, 'L', false, 0, '100', '');
            $pdf->MultiCell(0, 0, $estadoTesoreria, 0, 'L', false, 0, '170', '');
            $pdf->MultiCell(0, 0, $codigoAntiguedad, 0, 'L', false, 1, '190', '');
            $pdf->Ln(2);
            $cantidad += 1;
            $totalLetra += 1;
            $linea += 1;
        }
        if ($cantidad == 0) {
            $pdf->SetXY(0, 70);
            $pdf->SetFont('dejavusans', 'B', 14);
            $pdf->MultiCell(0, 5, 'No hay colegiados para imprimir.', 0, 'L', false, 0, '10', '');
        } else {
            if ($localidadDetalle == "LA PLATA") {
                $pdf->MultiCell(0, 0, 'Total de la letra '.$letraAnterior.': '.$totalLetra, 0, 'C', false, 1, '10', '');
            }
            $pdf->Ln(5);
            $pdf->SetFont('dejavusans', 'B', 10);
            $pdf->MultiCell(0, 5, 'Cantidad de colegiados: '.$cantidad, 0, 'L', false, 0, '10', '');
        }

        $pdf->lastPage();
        $destination = 'F';
        ob_clean();
        $camino = $_SERVER['DOCUMENT_ROOT'];
        $camino .= PATH_PDF;
        $pathArchivo = $pathOrigen.'archivos/padron/'.PERIODO_ACTUAL;
        $nombreArchivo = 'Padron_'.$localidadDetalle.'.pdf';
        if (!file_exists($pathArchivo)) {
            mkdir($pathArchivo, 0777, true);
        }
        if (file_exists($pathArchivo."/".$nombreArchivo)) {
            unlink($pathArchivo."/".$nombreArchivo);
        }

        $pdf->Output($camino.'/archivos/padron/'.PERIODO_ACTUAL.'/'.$nombreArchivo, $destination);

        $resultado['estado'] = TRUE;
        $resultado['mensaje'] = "Planilla generada.";
        $resultado['pathArchivo'] = $pathArchivo;
        $resultado['nombreArchivo'] = $nombreArchivo;

        if (file_exists($pathArchivo.'/'.$nombreArchivo)) {
            $pdf_content = file_get_contents($pathArchivo.'/'.$nombreArchivo);
            $padronPDF = base64_encode($pdf_content);
            $resultado['padronPDF'] = $padronPDF;
        } else {
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = 'PADRON NO EXISTE.';
        }
        return $resultado;
    }
}
