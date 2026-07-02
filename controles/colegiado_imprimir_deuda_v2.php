<?php
require_once '../dataAccess/config.php';
permisoLogueado();
require_once '../html/head.php';
require_once '../dataAccess/funcionesConector.php';
require_once '../dataAccess/funcionesPhp.php';
require_once '../dataAccess/colegiadoLogic.php';
require_once '../dataAccess/colegiadoDeudaAnualLogic.php';
require_once '../dataAccess/colegiadoPlanPagoLogic.php';
require_once '../dataAccess/notificacionDeudaLogic.php';

require_once('../tcpdf/config/lang/spa.php');
require_once('../tcpdf/tcpdf.php');

class MYPDF extends TCPDF 
{
        //Page header
        public function Header() 
        {
                // Logo
                $image_file = '../public/images/headerNota200.png';
                $this->Image($image_file, 10, 5, 160, 20, 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
                 // Set font
                $this->SetFont('helvetica', 'B', 20);
                // Title
                $this->Cell(0, 15, '', 0, false, 'C', 0, 'Nota', 0, false, 'M', 'M');
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


$pdf = new MYPDF('P', PDF_UNIT, 'A4', true, 'UTF-8', false);
$pdf->SetPrintHeader(true);
$pdf->SetPrintFooter(true);
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
$pdf->SetFont('dejavusans', '', 8);
$pdf->AddPage();

$html='';
if (isset($_GET['idColegiado'])) {
    $periodoActual = $_SESSION['periodoActual'];
    $idColegiado = $_GET['idColegiado'];
    $tipoPdf = $_POST['tipoPdf'];
    $mailDestino = $_POST['mail'];
    
    include 'genera_nota_deuda.php';    
    $chequera = '../../archivos/NotaDeuda/'.$periodoActual.'/'.$nombreArchivo;    
    if ($tipoPdf == 'F') {
        $envioMail = TRUE;
    } else {
        $envioMail = FALSE;
    }

    if ($envioMail) {
        //enviamos el pdf por mail si tiene contacto
        $destinatario = $colegiado['apellido'].', '.$colegiado['nombre'];
        require_once '../PHPMailer/class.phpmailer.php';
        require_once '../PHPMailer/class.smtp.php';

        $mail = new PHPMailer();
        $mail->IsSMTP();
        $mail->SMTPAuth = true;
        $mail->SMTPSecure = "ssl";
        $mail->Host = "mail.colmed1.org.ar";
        $mail->Port = 465;
        //$mail->Username = "sistemas@colmed1.org.ar";
        //$mail->Password = "@sistemas1";
        $mail->Username = MAIL_MASIVO;
        $mail->Password = MAIL_MASIVO_PASS;

        $mail->From = "noreply@colmed1.org.ar";
        $mail->FromName = "Colegio de Medicos. Distrito I";
        $mail->Subject = "Nota de Deuda - Tesoreria del Colegio de Medicos Distrito I";
        $mail->AltBody = "";
        $mail->MsgHTML("Le enviamos la Nota de Deuda de las cuotas de colegiacion del Colegio de Medicos Distrito I");
        $mail->AddAttachment("../archivos/NotaDeuda/".$periodoActual."/".$nombreArchivo);
        $mail->AddAddress($mailDestino, $destinatario);
        $mail->IsHTML(true);
        //echo $mailDestino .' - '. $matricula .' - '. $destinatario;
        if($mail->Send()) {
            $mailEnviado = TRUE;
        }else{
            $mailEnviado = FALSE;
        }
    }
    if ($envioMail) {
        if ($mailEnviado) {
            require_once ('../html/head.php');
            require_once ('../html/encabezado.php');
        ?>
            <div class="col-md-12">
                <div class="row" style="background-color: #428bca;">
                    <div class="col-md-12"></div>
                </div>
            </div>
            <div class="row">&nbsp;</div>
            <div class="row">
                <div class="col-md-12">
                    <h3>Pagos registrados solicitados por <?php echo $colegiado['nombre'].' '.$colegiado['apellido']; ?>, de cuotas del período <?php echo $periodoActual; ?></h3>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="alert alert-success" role="alert">
                        <span class="glyphicon glyphicon-ok" aria-hidden="true"></span>
                        <span><strong>&nbsp;El mail se envió con éxito al correo: </strong><?php echo $mailDestino; ?></span>
                    </div>        
                </div>
            </div>
        <?php
        } else {
        ?>    
            <div class="row">
                <div class="col-md-12">
                    <div class="alert alert-danger" role="alert">
                        <span class="glyphicon glyphicon-remove-sign" aria-hidden="true"></span>
                        <span><strong>ERROR al enviar el mail al correo: </strong><?php echo $mailDestino; ?><strong>. Vuelva a intentar más tarde.</strong></span>
                    </div>        
                </div>
            </div>
        <?php
        }
        ?>    
        <div class="row">
            <div class="col-md-12 text-center">
                Cierre esta pestaña del navegador.
            </div>
        </div>
        <?php
    } else {
    ?>
        <div class="container-fluid p-3" >
            <embed src='data:application/pdf;base64,<?php echo $chequera; ?>' height="600px" width='100%' type='application/pdf'>   
        </div>
    <?php
    }
} else {
    ?>
        <div class="alert alert-danger" role="alert">
            <span class="glyphicon glyphicon-remove-sign" aria-hidden="true"></span>
            <span><strong>ERROR AL INGRESAR</strong></span>
        </div>        
    <?php
}

