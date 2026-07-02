<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/colegiadoLogic.php');
require_once ('../dataAccess/colegiadoDomicilioLogic.php');
$colegiadoDomicilioLogic = new colegiadoDomicilioLogic();
require_once ('../dataAccess/colegiadoDeudaAnualLogic.php');
$colegiadoDeudaAnualLogic = new colegiadoDeudaAnualLogic();

require_once('../tcpdf/config/lang/spa.php');
require_once('../tcpdf/tcpdf.php');
set_time_limit(0);

class MYPDF extends TCPDF 
{
    //Page header
    public function Header() 
    {
            // Logo
            $image_file = '../public/images/logo_colmed1_lg.png';
            $this->Image($image_file, 10, 10, 90, 12, 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
            //$this->Image($image_file, 10, 10, 15, '', 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
             // Set font
            $this->SetFont('helvetica', 'B', 15);
            // Title
            $this->Cell(0, 15, 'Listado de Consejeros ('.cambiarFechaFormatoParaMostrar(date('Y-m-d')).')', 0, false, 'C', 0, '', 0, false, 'M', 'M');
    }

    // Page footer
    public function Footer() {
            // Position at 15 mm from bottom
            $this->SetY(-15);
            // Set font
            $this->SetFont('helvetica', 'I', 8);

            //$this->Cell(0, 10, 'Relaciones con la comunidad', 0, false, 'C', 0, '', 0, false, 'T', 'M');
            //$this->Ln(3);
            // Page number
            $this->Cell(0, 10, 'Pag. '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
}
//if (isset($_GET['idColegiado'])) {
    $colegiadoLogic = new colegiadoLogic();
    $resColegiado = $colegiadoLogic->obtenerColegiadoPorId($idColegiado);
    if ($resColegiado['estado'] && $resColegiado['datos']) {
        $colegiado = $resColegiado['datos'];
        $resDomicilio = $colegiadoDomicilioLogic->obtenerColegiadoDomicilioPorIdColegiado($idColegiado);
        if ($resDomicilio['estado']) {
            $pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, 'A4', true, 'UTF-8', false);
            $pdf->SetPrintHeader(true);
            $pdf->SetPrintFooter(true);

            // set header and footer fonts
            $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
            $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

            // set default monospaced font
            $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

            // set margins
            $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
            $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
            define ('PDF_MARGIN_FOOTER', 8);
            $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

            // set auto page breaks
            $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

            // set image scale factor
            $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
            $pdf->AddPage();
            $pdf->SetFont('dejavusans', '', 10);

            $html='';
            $domicilio = $resDomicilio['datos'];
            $calle = $domicilio['calle'];
            $numero = $domicilio['numero'];
            $lateral = $domicilio['lateral'];
            $piso = $domicilio['piso'];
            $depto = $domicilio['depto'];
            $localidad = $domicilio['nombreLocalidad'];
            $codigoPostal = $domicilio['codigoPostal'];
            $html .='<div>Señor<br>
                Presidente del<br>
                Colegio de Médicos. Distrito I<br>
                '.$nombrePresidente.'<br>
                <p style="text-underline-position: alphabetic">
                S             /              D</p><br>
                Tengo el agrado de dirigirme a usted, a los efectos<br>
                de informarle mi nuevo domicilio real:<br><br><br>
            </div>
            <div>
                <table border="1" cellspacing="0" cellpadding="4">
                    <tr>
                        <td width="35%">Calle:</td>
                        <td>'.$calle.'</td>
                    </tr>
                    <tr>
                        <td width="35%">Número:</td>
                        <td>'.$numero.'</td>
                    </tr>
                    <tr>
                        <td width="35%">Lateral:</td>
                        <td>'.$lateral.'</td>
                    </tr>
                    <tr>
                        <td width="35%">Piso:</td>
                        <td>'.$piso.'</td>
                    </tr>
                    <tr>
                        <td width="35%">Departamento:</td>
                        <td>'.$depto.'</td>
                    </tr>
                    <tr>
                        <td width="35%">Localidad:</td>
                        <td>'.$localidad.'</td>
                    </tr>
                    <tr>
                        <td width="35%">Código Postal:</td>
                        <td>'.$codigoPostal.'</td>
                    </tr>
                </table>
            </div>
            <div>Sin otro particular saludo atentamente.-</div>
            <div>
                <div>__________________________________<br></div>
                <div>'.$colegiado["apellido"].', '.$colegiado["nombre"].'<br></div>
                <div><label>M.P.:&nbsp; </label>'.$colegiado["matricula"].'</div>
            </div>';
            $resEstadoTeso = $colegiadoDeudaAnualLogic->estadoTesoreriaPorColegiado($idColegiado, $periodoActual);
            if ($resEstadoTeso['estado']){
                $codigo = $resEstadoTeso['codigoDeudor'];
                $resEstadoTesoreria = $colegiadoDeudaAnualLogic->estadoTesoreria($codigo);
                if ($resEstadoTesoreria['estado']){
                    $html .'<div>
                            <div><label>Situación con Tesoreria:&nbsp; </label>'.$resEstadoTesoreria['estadoTesoreria'].'</div>
                        </div>';
                } else {
                    $html .= 'buscando estadoTesoreria'.$resEstadoTesoreria['mensaje'];
                }
            } else {
                    $html .= 'buscando estadoTesoreriaColegiado'.$resEstadoTeso['mensaje'];
            }            
            $pdf->writeHTML($html, true, false, false, false, '');
            $pdf->lastPage();

            $destination='CambioDomicilio_'.$matricula.'.pdf';
            if (!preg_match('/\.pdf$/', $path_to_store_pdf))
            {
                   $path_to_store_pdf .= '.pdf';
            }
            ob_clean();
            if ($destination == 'D')
            {
                   echo $this->view->pdf->Output($path_to_store_pdf, $destination);
                   exit();
            }

            $pdf->Output('CambioDomicilio_'.$matricula.'.pdf', 'I');        

        } else {
            echo "<span><strong>".$resDomicilio['mensaje']."</strong></span>";
        }
    } else {
        echo "<span><strong>".$resColegiado['mensaje']."</strong></span>";
    }
//}
