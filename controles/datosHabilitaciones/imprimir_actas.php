<?php
require_once ('../../dataAccess/config.php');
permisoLogueado();
require_once ('../../html/head.php');
require_once ('../../dataAccess/funcionesConector.php');
require_once ('../../dataAccess/funcionesPhp.php');
require_once ('../../dataAccess/colegiadoLogic.php');
require_once ('../../dataAccess/habilitacionConsultorioLogic.php');
$habilitacionConsultorioLogic = new habilitacionConsultorioLogic();

require_once('../../tcpdf/config/lang/spa.php');
require_once('../../tcpdf/tcpdf.php');

class MYPDF extends TCPDF 
{
        //Page header
        public function Header() 
        {
                // Logo
                $image_file = '../../public/images/logo_colmed1_lg.png';
                $this->Image($image_file, 10, 5, 170, 20, 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
                 // Set font
                $this->SetFont('helvetica', 'B', 20);
                // Title
                $this->Cell(0, 15, '', 0, false, 'C', 0, 'Nota', 0, false, 'M', 'M');

            /*
                //MARCA DE AGUA 
                $bMargin = $this->getBreakMargin();
                $auto_page_break = $this->AutoPageBreak;
                $this->SetAutoPageBreak(false, 0);

                $img_file2 = '../../public/images/fondoCertificadoClaro.jpg';
                $this->Image($img_file2, 15, 25, 180, 180, '', '', 'C', false, 300, '', false, false, 0);
                $this->SetAutoPageBreak($auto_page_break, $bMargin);
                $this->setPageMark();
                //FIN MARCA DE AGUA 
        
             * 
             */
        }

        // Page footer
        public function Footer() {
                // Position at 15 mm from bottom
                //$this->SetY(-15);
                // Set font
                //$this->SetFont('helvetica', 'I', 8);

                //$this->Cell(0, 10, 'Relaciones con la comunidad', 0, false, 'C', 0, '', 0, false, 'T', 'M');
                //$this->Ln(3);
                // Page number
                //$this->Cell(0, 5, 'Pag. '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
        }

}
?>
<?php
$continua = TRUE;
if (isset($_POST['inspecciones'])) {
    $inspecciones = unserialize(stripslashes($_POST["inspecciones"]));
} else {
    $continua = FALSE;
}
if ($continua){
    //armo el html con el certificado
    $pdf = new MYPDF('P', PDF_UNIT, 'A4', true, 'UTF-8', false);
    $pdf->SetPrintHeader(true);
    $pdf->SetPrintFooter(true);
    $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
    $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
    //$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
    $pdf->SetFooterMargin(0);
    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

    //var_dump($inspecciones);exit;
    foreach ($inspecciones as $inspeccion) {
        $idInspectorHabilitacion = $inspeccion['idInspeccion'];
        $resInspeccion = $habilitacionConsultorioLogic->obtenerInspeccionPorId($idInspectorHabilitacion);
        //var_dump($resInspeccion);exit;
        if ($resInspeccion['estado']) {
            $laInspeccion = $resInspeccion['datos'];
            $pdf->SetFont('dejavusans', '', 10);
            $pdf->AddPage();

            $pdf->SetFont('dejavusans', 'B', 14);
            $pdf->MultiCell(0, 10, 'ACTA DE INSPECCIÓN DE CONSULTORIO Nº '.rellenarCeros($idInspectorHabilitacion,8), 0, 'C', false, 1, '10', '');
            $pdf->SetFont('dejavusans', '', 10);
            $pdf->MultiCell(0, 5, 'Lugar y Fecha ….......................................................', 0, 'R', false, 1, '100', '');
            $pdf->Ln(5);
            //ARMAMOS EL HTML
            $pdf->SetFont('dejavusans', '', 10);
            $html = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;El Sr. Inspector Médico que acredita su identidad mediante la credencial respectiva, y cuya firma y 
                sello se inserta al pie de la presente, y en cumplimiento de lo dispuesto por la Resolución Nº 
                3740/78 del Ministerio de Bienestar Social, Decreto Nº 3280/90 y Resoluciones del Consejo Superior 
                del Colegio de Médicos de la Provincia de Buenos Aires Nº 567/04 y del Ministerio de Salud 
                Nº 3057/09, se constituye en la calle:';
            $pdf->writeHTMLCell(0, 20, '', '', $html, 0, 1, 0, true, 'J', true);

            $pdf->SetFont('dejavusans', 'B', 12);
            $pdf->MultiCell(0, 10, $laInspeccion['domicilio'], 0, 'C', false, 1, '10', '');

            $pdf->SetFont('dejavusans', '', 10);
            $html = '<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;y procede a constatar: ..................................................................................................................<br/><br/>
            ..............................................................................................................................................................<br/><br/>
            ..............................................................................................................................................................<br/><br/>
            ..............................................................................................................................................................<br/><br/>
            ..............................................................................................................................................................<br/><br/>
            ..............................................................................................................................................................<br/><br/>
            ..............................................................................................................................................................<br/><br/>
            ..............................................................................................................................................................</p><br/>
            <p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Se hace constar que el Sr. Inspector Médico actuante está facultado para efectuar las inspecciones y diligencias
                que fuera menester para el mejor desempeño de sus funciones necesarias para la habilitación, contralor y 
                fiscalización de los consultorios médicos en toda el área de la Provincia de Buenos Aires, así como para 
                verificar el cumplimiento de las normas establecidas por el Ministerio de Salud sobre el particular, las 
                correspondientes a la Ley de Colegiación (Decreto-Ley 5413/58), los requisitos edilicios y de instalación 
                que determinan las normas para inscripción de consultorio y su correspondiente habilitación (Res. Ministerial 
                1762/58.-
            </p>
            <p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;El mismo deberá hacer entrega de una de las copias de la presente acta al interesado, que firmará la misma, 
                o en su caso el Inspector actuante hará constar su negativa.- 
            </p>
            <p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;En caso de utilizarse por cualquier causa la presente acta, deberá igualmente ser remitida al Colegio 
                de Distrito y Consejo Superior con la firma del Inspector actuante, o de algún miembro de la Mesa Directiva 
                del respectivo Colegio, haciéndose saber las causas de dicha inutilización.-
            </p>
            <p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;A los fines pertinentes y para constancia, se extiende la presente por duplicado, en el lugar y fecha 
                mencionados.- 
            </p>';
            $pdf->writeHTMLCell(0, 5, '', '', $html, 0, 1, 0, true, 'J', true);

            $pdf->SetFont('dejavusans', 'B', 10);
            $pdf->Ln(15);
            $pdf->MultiCell(100, 5, 'Firma …........................................', 0, 'C', false, 0, '10', '');
            $pdf->MultiCell(100, 5, 'Firma …........................................', 0, 'C', false, 0, '100', '');
            $pdf->Ln(5);
            $pdf->MultiCell(100, 5, 'MP Nº: '.$laInspeccion['matriculaColegiado'], 0, 'C', false, 0, '10', '');
            $pdf->MultiCell(100, 5, 'MP Nº: '.$laInspeccion['matriculaInspector'], 0, 'C', false, 0, '100', '');
            $pdf->SetFont('dejavusans', '', 10);
            $pdf->Ln(5);
            $pdf->MultiCell(100, 5, $laInspeccion['apellidoNombreColegiado'], 0, 'C', false, 0, '10', '');
            $pdf->MultiCell(100, 5, $laInspeccion['apellidoNombreInspector'], 0, 'C', false, 0, '100', '');
            $pdf->SetFont('dejavusans', 'B', 10);
            $pdf->Ln(5);
            $pdf->MultiCell(100, 5, 'Profesional Médico', 0, 'C', false, 0, '10', '');
            $pdf->MultiCell(100, 5, 'Inspector Médico', 0, 'C', false, 0, '100', '');
            $pdf->lastPage();

        }
    }
    ob_clean();
    /* Finalmente generamos el PDF */
    $destination = 'I';
    $nombreArchivo = 'Legajo_.pdf';
    $pdf->Output($nombreArchivo, $destination);        
}
