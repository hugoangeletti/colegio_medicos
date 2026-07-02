<?php
if (isset($_GET['fileTitulo']) && $_GET['fileTitulo']) {
	$fileTitulo = $_GET['fileTitulo'];
	$imagenTitulo = fopen("ftp://webcolmed:web.2017@192.168.2.50:21/Titulos/".$fileTitulo, "r");
	if ($imagenTitulo) {
		header('Content-type: application/pdf');

		fpassthru($imagenTitulo);  
		fclose ($fileTitulo);
	} else {
	    echo 'No se encontro el titulo, '.$fileTitulo;
	}
} else {
	echo 'Mal ingreso';
}