<?php
require_once ('../../dataAccess/config.php');
permisoLogueado();
require_once ('../../html/head.php');
require_once ('../../dataAccess/funcionesConector.php');
require_once ('../../dataAccess/funcionesPhp.php');
require_once ('../../dataAccess/registroDNU260Logic.php');
$registroDNU260Logic = new registroDNU260Logic();
require_once ('../../dataAccess/colegiadoLogic.php');

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

                //MARCA DE AGUA 
                $bMargin = $this->getBreakMargin();
                $auto_page_break = $this->AutoPageBreak;
                $this->SetAutoPageBreak(false, 0);

                $img_file2 = '../../public/images/fondoCertificadoClaro.jpg';
                $this->Image($img_file2, 15, 25, 180, 180, '', '', 'C', false, 300, '', false, false, 0);
                $this->SetAutoPageBreak($auto_page_break, $bMargin);
                $this->setPageMark();
                //FIN MARCA DE AGUA 
        
        }

        // Page footer
        public function Footer() {
                // Position at 15 mm from bottom
                $this->SetY(-10);
                // Set font
                $this->SetFont('dejavusans', 'B', 10);

                $this->Cell(0, 10, 'La fotocopia de éste certificado no tiene validez', 0, false, 'C', 0, '', 0, false, 'T', 'M');
                $this->Ln(3);
                // Page number
                //$this->Cell(0, 5, 'Pag. '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
        }

}
?>
<?php
$continua = TRUE;
if (isset($_GET['id'])) {
    $idRegistro = $_GET['id'];
    $resRegistro = $registroDNU260Logic->obtenerRegistroPorId($idRegistro);
    if ($resRegistro['estado']) {
        $registro = $resRegistro['datos'];
        
    } else {
        $resultado['mensaje'] = $resRegistro['mensaje'];
        $continua = FALSE;
    }
} else {
    $continua = FALSE;
}
//guardar log de la emision del certificado y obtener el numero de emision, $idCertificado
$resCertificado = $registroDNU260Logic->agregarRegistroCertificado($idRegistro);
if ($resCertificado['estado']) {
    $idCertificado = $resCertificado['idCertificado'];
} else {
    $continua = FALSE;
}
        

if (!$continua){
    $resultado['mensaje'] = "ERROR AL GENERAR EL CERTIFICADO";
    $resultado['icono'] = "glyphicon glyphicon-remove";
    $resultado['clase'] = "alert alert-error";
}

?>
<!--<body onLoad="document.forms['myForm'].submit()">-->
    <?php
    if ($continua){
        //armo el html con el certificado
        $conFirma = "S";
        if (isset($_GET['envia']) && $_GET['envia'] == "S") {
            $enviaMail = 'S';
            $mailDestino = trim($registro['mail']);
            //$mailDestino = "hugo.ang@gmail.com";
        } else {
            $enviaMail = "N";
            $mailDestino = NULL;
        }
        $tipoDocumento = $registro['tipoDocumento'];
        IF ($tipoDocumento == "OTRO") {
            $tipoDocumento = "DNI provisorio";
        }
        $numeroDocumento = $registro['numeroDocumento'];
        $numeroPasaporte = $registro['numeroPasaporte'];
        $domicilioProfesional = $registro['domicilioProfesional'];
        $localidadProfesional = $registro['localidadProfesional'];
        $sexo = $registro['sexo'];
        $numero = $registro['numero'];
        // insertamos la foto y firma
        $foto = @fopen ("ftp://webcolmed:web.2017@192.168.2.50:21/FotosRegistro/".$numero, "rb");
        if ($foto) {
            $contents=stream_get_contents($foto);
            fclose ($foto);

            $fotoVer = base64_encode($contents);
            $tieneFotoFirma = TRUE;
        } 
        $tieneFotoFirma = TRUE;

        //ARMAMOS EL HTML
        $conNota = TRUE;
        if ($sexo <> 'F') {
            $profesional = 'el&nbsp; <b>Dr.'.trim($registro['apellido'].' '.trim($registro['nombre'])).'</b>';
        } else {
            $profesional = 'la&nbsp; <b>Dra.'.trim($registro['apellido'].' '.trim($registro['nombre'])).'</b>';
        }
        $html = 'El Colegio de Médicos de la Pcia. de Bs.As. Distrito I, deja constancia que '.$profesional.'; '.trim($tipoDocumento).
            ': <b>'.$numeroDocumento.'</b> Pasaporte: <b>'.$numeroPasaporte.'</b> se encuentra registrado bajo el Nº <b>'.$numero.' - DNU 260/2020 </b> para ejercer la profesión de Médico por el plazo perentorio de 60 días solamente en el lugar declarado y '.
            'mientras se encuentre vigente el DNU 260/2020 y la Resolución de Consejo Superior del Colegio de Médicos de la Provincia '.
            'de Buenos Aires Nº 997/2020, dentro de los términos del Decreto Ley 5413/58.'.
            '<br><br>Para ser presentado únicamente en el lugar declarado: <b>'.strtoupper($domicilioProfesional).'</b>';

        $pdf = new MYPDF('P', PDF_UNIT, 'A4', true, 'UTF-8', false);
        $pdf->SetPrintHeader(true);
        $pdf->SetPrintFooter(true);
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
        $pdf->AddPage();

        $alturaLinea = 6;
        //imprimo la planilla
        $pdf->Ln(5);
        $pdf->SetFont('dejavusans', '', 10);
        if ($tieneFotoFirma) {
            $pic = 'data://text/plain;base64,' . base64_encode($contents);
            $pdf->Image($pic , 170 ,25, 25 , 25,'JPG');
            $pdf->Ln(25);
        }
        $pdf->MultiCell(0, $alturaLinea, 'Nº '.rellenarCeros($idCertificado, 8), 0, 'L', false, 0, '', '');
        $pdf->MultiCell(0, $alturaLinea, 'La Plata, '.date('d').' de '.obtenerMes(date('m')).' de '.date('Y'), 0, 'R', false, 1, '50', '');
        $pdf->Ln(5);

        ////
        $pdf->SetFont('dejavusans', '', 9);
        $pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, 'J', true);
        $pdf->Ln(5);
        $pdf->SetFont('dejavusans', '', 8);
        $pdf->MultiCell(0, 1, 'Realizó: '.$_SESSION['user_entidad']['nombreUsuario'], 0, 'L', false, 1, '', '', true);                    

        //imprimo sello oval
        $pdf->Ln(15);
        $img = '../../public/images/SELLO.png';
        if ($conFirma == 'S') {
            //imprimo sello y firma
            //1: presidente
            $colegiadoLogic = new colegiadoLogic();
            $resFirmante = $colegiadoLogic->obtenerFirmaPorCargo(1); 
            if ($resFirmante['estado']) {
                $firmante = $resFirmante['datos'];
                $presidente = 'Dr. '. ucfirst($firmante['nombre']) .' '. ucfirst($firmante['apellido']);
//                    $fileFirma = rellenarCeros($firmante['matricula'], 8) .'.bmp';
//                    $firma = fopen ("ftp://webcolmed:web.2017@192.168.2.50:21/Firmas/".$fileFirma, "rb");
//                    if (!$firma) {
//                        echo "<p>No puedo abrir el archivo para lectura</p>";
//                        exit;
//                    }
//                    $contents=stream_get_contents($firma);
//                    fclose ($firma);
//                    $firmaVer = base64_encode($contents);
                $jpgfile1 = '../firma/'.rellenarCeros($firmante['matricula'], 8) .'.jpg';

                $htmlFirma1 = '<td style="text-align:center;" >
                                <img src="'.$jpgfile1.'" border="0" height="120" width="" />
                                <label style="font-size: 10px;">'.$presidente.'</label><br>
                                <label style="font-size: 8px;">Presidente<br>Colegio de Médicos - Distrito I</label>
                            </td>';
            } else {
                $htmlFirma2 = '<td>&nbsp;'.$resFirmante['mensaje'].'</td>';
            }
            //2: secretariogeneral
            $resFirmante = $colegiadoLogic->obtenerFirmaPorCargo(2); 
            if ($resFirmante['estado']) {
                $firmante = $resFirmante['datos'];
                $secretario = 'Dr. '. ucfirst($firmante['nombre']) .' '. ucfirst($firmante['apellido']);
//                    $fileFirma = rellenarCeros($firmante['matricula'], 8) .'.bmp';
//                    $firma = fopen ("ftp://webcolmed:web.2017@192.168.2.50:21/Firmas/".$fileFirma, "rb");
//                    if (!$firma) {
//                        echo "<p>No puedo abrir el archivo para lectura</p>";
//                        exit;
//                    }
//                    $contents=stream_get_contents($firma);
//                    fclose ($firma);
//                    $firmaVer = base64_encode($contents);
                $jpgfile2 = '../firma/'.rellenarCeros($firmante['matricula'], 8) .'.jpg';

                $htmlFirma2 = '<td style="text-align:center;" >
                                <img src="'.$jpgfile2.'" border="0" height="120" width="" />
                                <label style="font-size: 10px;">'.$secretario.'</label><br>
                                <label style="font-size: 8px;">Secretario General<br>Colegio de Médicos - Distrito I</label>
                            </td>';
            } else {
                $htmlFirma2 = '<td>&nbsp;'.$resFirmante['mensaje'].'</td>';
            }
        } else {
            //$pdf->Ln(75);
            $htmlFirma2 = '';
            $htmlFirma1 = '';
        }
        $html = '<table>
                <tr>'
                    .$htmlFirma2.
                    '<td style="text-align:center;" >
                        <img src="'.$img.'" border="0" height="140" width="" />
                    </td>'
                    .$htmlFirma1.
                '</tr>
                </table';
        $pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, 'J', true);
            
        if ($enviaMail == 'S' && $conFirma == 'S') {
            $tipoPdf = 'F';
        } else {
            $tipoPdf = 'I';
        }

        $destination = $tipoPdf; //'F';
        if (!preg_match('/\.pdf$/', $path_to_store_pdf))
        {
            $path_to_store_pdf .= '.pdf';
        }
        ob_clean();

        $camino = $_SERVER['DOCUMENT_ROOT'];
        $camino .= PATH_PDF;
        $nombreArchivo = 'Certificado_'.$numero.'_'.date('Ymd').date('his').'.pdf';
        $nombreArchivoJpg = 'Certificado_'.$numero.'_'.date('Ymd').date('his').'.jpg';
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
            $envioMail = TRUE;
        } else {
            $pdf->Output($nombreArchivo, $destination);        
            $envioMail = FALSE;
        }
        if ($envioMail && isset($mailDestino)) {
            //enviamos el pdf por mail si tiene contacto
            $destinatario = $registro['apellido'].', '.$registro['nombre'];
            //$mailDestino = $mail;
            require_once '../../PHPMailer/class.phpmailer.php';
            require_once '../../PHPMailer/class.smtp.php';

            $mail = new PHPMailer();
            $mail->IsSMTP();
            $mail->SMTPAuth = true;
            $mail->SMTPSecure = "ssl";
            $mail->Host = "mail.colmed1.org.ar";
            $mail->Port = 465;
            //$mail->Username = "sistemas@colmed1.org.ar";
            //$mail->Password = "@sistemas1";
            $mail->Username = 'noreply@colmed1.org.ar';
            $mail->Password = '11edaef3b5f4b1091b4ebec3355a3210';

            $mail->From = "noreply@colmed1.org.ar";
            $mail->FromName = "Colegio de Medicos. Distrito I";
            $mail->Subject = "Certificado Registro DNU 260/2020";
            $mail->AltBody = "";
            $mail->MsgHTML("Estimado/a se le envia Certificado de Registro DNU 260/2020 para ser presentado unicamente en el lugar de trabajo declarado<br>".
                    "Saludo atentemente.");
            $mail->AddAttachment("../../archivos/certificados/".$periodoActual."/".$nombreArchivo);
            $mail->AddAddress($mailDestino, $destinatario);
            $mail->IsHTML(true);
            if($mail->Send()) {
                $mailEnviado = TRUE;
            }else{
                $mailEnviado = FALSE;
            }

        }
        if ($envioMail) {
            require_once ('../../html/head.php');
            require_once ('../../html/encabezado.php');
            if ($mailEnviado) {
            ?>
                <div class="col-md-12">
                    <div class="row" style="background-color: #428bca;">
                        <div class="col-md-12"></div>
                    </div>
                </div>
                <div class="row">&nbsp;</div>
                <div class="row">
                    <div class="col-md-12">
                        <h3>Certificado solicitado por <?php echo $registro['nombre'].' '.$registro['apellido']; ?></h3>
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
        }
            
    } else {
    ?>
        <div id="pagina">
            <h2>Se produjo un error al buscar al colegiado</h2>
        </div>
    <?php
    }
    ?>
    <!--BOTON VOLVER -->
    <div class="row">
        <div class="col-md-3" id="volver">
            <h3>Cerrar esta pestaña del navegador.</h3>
        </div>
    </div>


