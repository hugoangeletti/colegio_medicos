<?php
require_once ('../../dataAccess/config.php');
permisoLogueado();
//require_once ('../../html/head.php');
require_once ('../../dataAccess/funcionesConector.php');
require_once ('../../dataAccess/funcionesPhp.php');
require_once ('../../dataAccess/tipoResolucionLogic.php');
require_once ('../../dataAccess/mesaEntradaEspecialistaLogic.php');
require_once ('../../dataAccess/resolucionesLogic.php');
$resolucionesLogic = new resolucionesLogic();
require_once ('../../dataAccess/colegiadoLogic.php');
require_once ('../../dataAccess/presidenteLogic.php');

require_once('../../tcpdf/config/lang/spa.php');
require_once('../../tcpdf/tcpdf.php');

class MYPDF extends TCPDF 
{
        //Page header
        public function Header() 
        {
                // Logo
                $image_file = '../../public/images/Escudo.jpg';
                $this->Image($image_file, 55, 5, 40, 45, 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
                $this->SetFont('dejavusans', 'B', 18);
                $this->Ln(5);
                $this->MultiCell(200, 200, 'REPÚBLICA ARGENTINA', 0, 'C', false, 1, '15', '160', true);
                

                $img_file2 = '../../public/images/logo-transp.png';
                $this->Image($img_file2, 600, 5, 40, 40, '', '', 'C', false, 300, '', false, false, 0);
                $this->Ln(5);
                $this->MultiCell(200, 200, 'COLEGIO DE MEDICOS', 0, 'C', false, 1, '575', '160', true);
                $this->SetFont('dejavusans', 'B', 16);
                $this->MultiCell(200, 200, 'DE LA PROVINCIA DE BUENOS AIRES', 0, 'C', false, 1, '575', '172', true);
                $this->MultiCell(200, 200, 'DISTRITO I', 0, 'C', false, 1, '575', '182', true);
                $this->SetAutoPageBreak($auto_page_break, $bMargin);
                $this->setPageMark();
                //FIN MARCA DE AGUA 
        
        }

        // Page footer
        public function Footer() {
                // Position at 15 mm from bottom
                //$this->SetY(-10);
                $this->SetY(-15);
                // Set font
                $this->SetFont('dejavusans', '', 8);

                $this->MultiCell(180, 0, 'Este certificado fue emitido en forma online desde el sistema del Colegio de Médicos Pcia.de Bs.As – Distrito I. Debe ser recibido por los organismos que lo requieran. Validez del certificado: 30 días a partir de la fecha de la firma. ', 1, 'L', 0, 0, '', '', true, 0, false, true, 40, 'T');
                //$this->Cell(180, 0, 'La fotocopia de éste certificado no tiene validez', 1, false, 'C', 0, '', 0, false, 'T', 'M');
                //$this->Ln(3);
                // Page number
                //$this->Cell(0, 5, 'Pag. '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
        }

}

$continua = TRUE;
if (isset($_GET['id'])) {
    $idResolucionDetalle = $_GET['id'];
    $resDetalle = $resolucionesLogic->obtenerResolucionDetallePorId($idResolucionDetalle);
    if ($resDetalle['estado']) {
        $resolucionDetalle = $resDetalle['datos'];
        $idResolucion = $resolucionDetalle['idResolucion'];
        $tipo = $resolucionDetalle['tipo'];
        $especialidad = $resolucionDetalle['especialidad'];
        $estado = $resolucionDetalle['estado'];
        $fechaAprobada = $resolucionDetalle['fechaAprobada'];
        $fechaRecertificacion = $resolucionDetalle['fechaRecertificacion'];
        $inciso = $resolucionDetalle['inciso'];
        $idColegiado = $resolucionDetalle['idColegiado'];
        $matricula = $resolucionDetalle['matricula'];
        $apellidoNombre = ucwords(strtolower($resolucionDetalle['apellido'].' '.$resolucionDetalle['nombre']));
        $especialidadDetalle = $resolucionDetalle['especialidadDetalle'];
        $especialidadDetalle = ucwords(strtolower($resolucionDetalle['especialidadDetalle']));
        $tipoEspecialista = $resolucionDetalle['tipoEspecialista'];
        $idTipoResolucion = $resolucionDetalle['idTipoResolucion'];
        $tipoResolucion = $resolucionDetalle['tipoResolucion'];
        $numeroResolucion = $resolucionDetalle['numeroResolucion'];
        $idResolucion = $resolucionDetalle['idResolucion'];
        $sexo = $resolucionDetalle['sexo'];
        if ($sexo == 'F') {
            $profesional = 'a la Doctora';
        } else {
            $profesional = 'al Doctor';
        }
    } else {
        $resultado['mensaje'] = $resDetalle['mensaje'];
        $continua = FALSE;
    }
} else {
    $continua = FALSE;
}

if ($continua){
        /*

!esp:Especialidad = ObtenerEspecialidad(rdet:Especialidad)

Loc:Especialidad = clip(PasarACapitalize(esp:Especialidad)) &' .-'

!obtener vencimiento
relate:paraconsulta.open
clear(paraconsulta)


paraconsulta{prop:sql} = 'select ce.FechaVencimiento, ce.FechaEspecialista, ce.Id, ce.IdResolucionDetalle'|
                        &' from colegiadoespecialista ce'|
                        &' where ce.IdColegiado='& col:Id &' and ce.Especialidad='& rdet:Especialidad|
                        &' and ce.IdResolucionDetalle = '& rdet:Id

if errorcode() then
    stop(fileerror() &' Buscando Loc:IdColegiadoEspecialista')
end

next(paraconsulta)
if ParC:c3 > 0 then
    Loc:IdColegiadoEspecialista = ParC:c3
else
    paraconsulta{prop:sql} = 'select ce.FechaVencimiento, ce.FechaEspecialista, ce.Id, ce.IdResolucionDetalle'|
                            &' from colegiadoespecialista ce'|
                            &' where ce.IdColegiado='& col:Id &' and ce.Especialidad='& rdet:Especialidad|
                            &' and ce.IdResolucionDetalle IS NULL'
    if errorcode() then
        stop(fileerror() &' select ce.FechaVencimiento, ce.FechaEspecialista, ce.Id, ce.IdResolucionDetalle'|
                            &' from colegiadoespecialista ce'|
                            &' where ce.IdColegiado='& col:Id &' and ce.Especialidad='& rdet:Especialidad|
                            &' and ce.IdResolucionDetalle IS NULL')
    end
    next(paraconsulta)
    if ParC:c3 > 0 then
        Loc:IdColegiadoEspecialista = ParC:c3
    else
        paraconsulta{prop:sql} = 'select FechaVencimiento, FechaEspecialista, Id from colegiadoespecialista where IdColegiado='& col:Id &' and Especialidad='& rdet:Especialidad
        if errorcode() then
            stop(fileerror() &' select FechaVencimiento, FechaEspecialista, Id from colegiadoespecialista where IdColegiado='& col:Id &' and Especialidad='& rdet:Especialidad)
        end
        next(paraconsulta)
        Loc:IdColegiadoEspecialista = ParC:c3

    end
end
Loc:Valido = ''
if ObtenerFechaJerarquizadoConsultor(Loc:IdColegiadoEspecialista,'C') <= 0 then

    Loc:Vencimiento = date(sub(ParC:c1,6,2), sub(ParC:c1,9,2), sub(ParC:c1,1,4))
    if Loc:Vencimiento > 0 then
        Loc:FechaEspecialista = date(sub(ParC:c2,6,2), sub(ParC:c2,9,2), sub(ParC:c2,1,4))

        if Loc:FechaEspecialista = date(month(Loc:Vencimiento),day(Loc:Vencimiento),year(Loc:Vencimiento)-5) then
            Loc:Valido = 'Certificada hasta el '& format(Loc:Vencimiento,@d17) &'.-'
        else
            Loc:Valido = 'Recertificada hasta el '& format(Loc:Vencimiento,@d17) &'.-'
        end
    end
end!fin obtener vencimiento

Loc:LaFecha = 'La Plata, '& DAY(rdet:FechaAprobada) &' de '& CLIP(NombreMes(MONTH(rdet:FechaAprobada))) &' de '& YEAR(rdet:FechaAprobada)

relate:paraconsulta.close

<p style = "font-family: Brush Script MT;">
Escribe tu texto aquí
</p>
        */
    $fontfamily = 'style = "font-family: '."' Brush Script MT'".'; font-size: 30px"';
    $html = '<p '.$fontfamily.'>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        El Colegio de Médicos de la Provincia de Buenos Aires autoriza '.$profesional.' <b>'.$apellidoNombre.'</b> Matrícula Provincial Nº <b>'.$matricula.'</b> a utilizar el título de especialista en <b>'.$especialidadDetalle.'</b> en razón de haber cumplimentado los recaudos exigidos en el Reglamento de Especializaciones y del Ejercicio de las mismas, según fija el Decreto 5413/58.
        </p>';                    
    echo $html; exit;

    $pdf = new MYPDF('P', PDF_UNIT, 'A1', true, 'UTF-8', false);
    $pdf->SetPrintHeader(true);
    $pdf->SetPrintFooter(false);
    $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
    $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
    //$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
    $pdf->SetFooterMargin(20);
    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
    
    $pdf->SetFont('dejavusans', '', 10);
    //$pdf->AddPage();
    $pdf->AddPage('L', 'A1');
    $alturaLinea = 6;
    $pdf->Ln(5);
    //imprimir QR
    $style = array(
            'border' => true,
            'vpadding' => 'auto',
            'hpadding' => 'auto',
            'fgcolor' => array(0,0,0),
            'bgcolor' => false, //array(255,255,255)
            'module_width' => 1, // width of a single module in points
            'module_height' => 1 // height of a single module in points
        );
    $codigoQR = 'http://www.colmed1.com.ar/portal/controls/titulo.php?id='.$idResolucionDetalle;
    //$codigoQR = 'http://www.colmed1.com/desarrollo/portal/controls/certificado.php?id='.$idCertificado.'&colegiado='.$idColegiado.'&tipo='.$idTipoCertificado;
    //$pdf->write2DBarcode('http://www.colmed1.com/desarrollo/ws-colmed/certificado.php?id='.$idCertificado, 'QRCODE,Q', 7,62,15,15, $style, 'N');
    $pdf->SetXY(110, 245);
    $pdf->write2DBarcode($codigoQR, 'QRCODE,Q', 32,25,25,25, $style, 'N');

    $pdf->Ln(5);
    //$fontname = $pdf->addTTFfont('../../public/fonts/', 'TrueTypeUnicode', '', 32);
    $pdf->SetFont('dejavusans', '', 40);
    $pdf->SetXY(100, 250);
    $pdf->writeHTMLCell(600, 0, '', '', $html, 0, 1, 0, true, 'J', true);

    $colegiadoLogic = new colegiadoLogic();
    $resFirmante = $colegiadoLogic->obtenerFirmaPorCargo(1); 
    if ($resFirmante['estado']) {
        $firmante = $resFirmante['datos'];
        $presidente = 'Dr. '. ucfirst($firmante['nombre']) .' '. ucfirst($firmante['apellido']);
        $jpgfile1 = '../firma/'.rellenarCeros($firmante['matricula'], 8) .'.jpg';
            
        $pdf->Image($jpgfile1, 600, 400, 150, 150, '', '', 'C', false, 300, '', false, false, 0);
        $pdf->Ln(5);
        $pdf->SetFont('dejavusans', 'B', 25);
        $pdf->MultiCell(200, 200, $presidente, 0, 'C', false, 1, '575', '500', true);
        $pdf->SetFont('dejavusans', 'B', 20);
        $pdf->MultiCell(200, 200, 'Presidente', 0, 'C', false, 1, '575', '512', true);
        $pdf->MultiCell(200, 200, 'Colegio de Médicos - Distrito I', 0, 'C', false, 1, '575', '522', true);

        /*
        $htmlFirma1 = '<td style="text-align:center;" >
                        <img src="'.$jpgfile1.'" border="0" height="200" width="" />
                        <label style="font-size: 30px;">'.$presidente.'</label><br>
                        <label style="font-size: 28px;">Presidente<br>Colegio de Médicos - Distrito I</label>
                    </td>';
        */
    } else {
        $htmlFirma2 = '<td>&nbsp;'.$resFirmante['mensaje'].'</td>';
    }
    //2: secretariogeneral
    $resFirmante = $colegiadoLogic->obtenerFirmaPorCargo(2); 
    if ($resFirmante['estado']) {
        $firmante = $resFirmante['datos'];
        $secretario = 'Dr. '. ucfirst($firmante['nombre']) .' '. ucfirst($firmante['apellido']);
        $jpgfile2 = '../firma/'.rellenarCeros($firmante['matricula'], 8) .'.jpg';

        $pdf->SetFont('dejavusans', 'B', 25);
        $pdf->Image($jpgfile2, 100, 400, 100, 100, '', '', 'C', false, 300, '', false, false, 0);
        $pdf->Ln(5);
        $pdf->MultiCell(200, 200, $secretario, 0, 'C', false, 1, '50', '500', true);
        $pdf->SetFont('dejavusans', 'B', 20);
        $pdf->MultiCell(200, 200, 'Secretario General', 0, 'C', false, 1, '50', '512', true);
        $pdf->MultiCell(200, 200, 'Colegio de Médicos - Distrito I', 0, 'C', false, 1, '50', '522', true);
        
        /*    
        $htmlFirma2 = '<td style="text-align:center;" >
                        <img src="'.$jpgfile2.'" border="0" height="200" width="" />
                        <label style="font-size: 30px;">'.$secretario.'</label><br>
                        <label style="font-size: 28px;">Secretario General<br>Colegio de Médicos - Distrito I</label>
                    </td>';
        */
    } else {
        //$htmlFirma2 = '<td>&nbsp;'.$resFirmante['mensaje'].'</td>';
    }
    /*
    $html = '<table>
            <tr>'
                .$htmlFirma2.
                '<td style="text-align:center;" >
                    <img src="'.$img.'" border="0" height="140" width="" />
                </td>'
                .$htmlFirma1.
            '</tr>
            </table';
    $pdf->Ln(5);
    $pdf->SetFont('dejavusans', '', 36);
    $pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, 'J', true);
    */

    $tipoPdf = 'I';
    
    $destination = $tipoPdf;
    if (!preg_match('/\.pdf$/', $path_to_store_pdf))
    {
        $path_to_store_pdf .= '.pdf';
    }
    ob_clean();

    $camino = $_SERVER['DOCUMENT_ROOT'];
    $camino .= PATH_PDF;
    $nombreArchivo = 'Certificado_'.$matricula.'_'.date('Ymd').date('his').'.pdf';
    $nombreArchivoJpg = 'Certificado_'.$matricula.'_'.date('Ymd').date('his').'.jpg';
    $periodoActual = $_SESSION['periodoActual'];
            
    $estructura = "../../archivos/certificados/".$periodoActual;
    if (!file_exists($estructura)) {
        mkdir($estructura, 0777, true);
    }
    if (file_exists("../../archivos/certificados/".$periodoActual."/".$nombreArchivo)) {
        unlink("../../archivos/certificados/".$periodoActual."/".$nombreArchivo);
    } 

    if ($tipoPdf == 'F') {
        $pdf->Output($camino.'/archivos/certificados/'.$periodoActual.'/'.$nombreArchivo, $destination);        
    } else {
        $pdf->Output($nombreArchivo, $destination);        
    }
} else {
?>
    <div class="row">
        <div class="col-md-12 alert alert-danger">
            <h3><?php echo $resultado['mensaje']; ?></h3>
        </div>
        <div class="row">&nbsp;</div>
        <div class="col-md-12">
            <h3>Cerrar esta pestaña del navegador, el mail fue enviado con éxito.</h3>
        </div>
    </div>
<?php
}
?>
<!--</body>-->

