<?php
function hashData($string) {
    $string = sha1($string);
    return $string;
}

function sumarRestarSobreFecha($fecha, $cant, $tipo = 'day', $accion = '+') {
    $nuevafecha = strtotime($accion . $cant . ' ' . $tipo, strtotime($fecha));
    $nuevafecha = date('Y-m-d', $nuevafecha);

    return $nuevafecha;
}

function rellenarCeros($entero, $largo) {
    // Limpiamos por si se encontraran errores de tipo en las variables
    $entero = (int) $entero;
    $largo = (int) $largo;

    $relleno = '';

    /**
     * Determinamos la cantidad de caracteres utilizados por $entero
     * Si este valor es mayor o igual que $largo, devolvemos el $entero
     * De lo contrario, rellenamos con ceros a la izquierda del número
     * */
    if (strlen($entero) < $largo) {
        $relleno = str_pad((int) $entero, $largo, "0", STR_PAD_LEFT);
        return $relleno;
    }
    return $relleno . $entero;
}

function cambiarFechaaformatoBD($fecha)
{ // pasa de dd/mm/AAAA a AAAA-mm-dd

    if ($fecha!="")
    {
        $fechaaux=explode('/',$fecha);
        $fecha=$fechaaux[2]."-".$fechaaux[1]."-".$fechaaux[0];

    }
    else
    {
        $fecha='0000-00-00';
    }
    return $fecha;
}

function cambiarFechaFormatoParaMostrar($fecha)
{ // pasa del AAAA-mm-dd a dd/mm/AAAA

    if ($fecha!="" && $fecha !="0000-00-00")
    {
    $fechaaux=explode('-',$fecha);
    $fecha=$fechaaux[2]."/".$fechaaux[1]."/".$fechaaux[0];

    }
    else
    {
        $fecha="";
    }
    return $fecha;
}

function NombreDeLaSemana($diaSemana){
    switch ($diaSemana) {
        case 0:
            $diaSemanaName = 'Domingo';
            break;
        case 1:
            $diaSemanaName = 'Lunes';
            break;
        case 2:
            $diaSemanaName = 'Martes';
            break;
        case 3:
            $diaSemanaName = 'Miércoles';
            break;
        case 4:
            $diaSemanaName = 'Jueves';
            break;
        case 5:
            $diaSemanaName = 'Viernes';
            break;
        case 6:
            $diaSemanaName = 'Sábado';
            break;

        default:
            $diaSemanaName = $diaSemana;
            break;
        
    }
    
    return $diaSemanaName;
}


function calcular_edad($fecha_nac)
{
    if ($fecha_nac!="")
    {    
    
	$dia=date("j");
	$mes=date("n");
	$anno=date("Y");

	//descomponer fecha de nacimiento
	$dia_nac=substr($fecha_nac, 8, 2);
	$mes_nac=substr($fecha_nac, 5, 2);
	$anno_nac=substr($fecha_nac, 0, 4);

	if($mes_nac>$mes){
		$calc_edad= $anno-$anno_nac-1;
	}else{
		if($mes==$mes_nac AND $dia_nac>$dia){
			$calc_edad= $anno-$anno_nac-1;  
		}else{
			$calc_edad= $anno-$anno_nac;
		}
	}
	return $calc_edad. " Años";
    }
    else {
     return "";   
    }
}

function calcular_antiguedad($fechaCalculo, $fechaBase)
{
    if ($fechaCalculo!="")
    {    
	$dia=substr($fechaBase, 8, 2);
	$mes=substr($fechaBase, 5, 2);
	$anno=substr($fechaBase, 0, 4);

	//descomponer fecha 
	$dia_nac=substr($fechaCalculo, 8, 2);
	$mes_nac=substr($fechaCalculo, 5, 2);
	$anno_nac=substr($fechaCalculo, 0, 4);

	if($mes_nac>$mes){
            $calc_edad = $anno-$anno_nac-1;
	}else{
            if($mes==$mes_nac AND $dia_nac>$dia){
                $calc_edad = $anno-$anno_nac-1;  
            }else{
                $calc_edad = $anno-$anno_nac;
            }
	}
        if ($calc_edad < 5) {
            $antiguedad = 1;
        } else {
            $antiguedad = 2;
        }
	return $antiguedad;
    }
    else {
        return NULL;   
    }
}

function obtenerMes($mes)
{
    switch ($mes) {
        case 1:
            $mesRes = 'Enero';
            break;
        case 2:
            $mesRes = 'Febrero';
            break;
        case 3:
            $mesRes = 'Marzo';
            break;
        case 4:
            $mesRes = 'Abril';
            break;
        case 5:
            $mesRes = 'Mayo';
            break;
        case 6:
            $mesRes = 'Junio';
            break;
        case 7:
            $mesRes = 'Julio';
            break;
        case 8:
            $mesRes = 'Agosto';
            break;
        case 9:
            $mesRes = 'Septiembre';
            break;
        case 10:
            $mesRes = 'Octubre';
            break;
        case 11:
            $mesRes = 'Noviembre';
            break;
        case 12:
            $mesRes = 'Diciembre';
            break;

        default:
            $mesRes = '';
            break;
    }

    return $mesRes;
}

function obtenerNumeroRomano($numero)
{
    switch ($numero) {
        case 1:
            $romano = 'I';
            break;
        case 2:
            $romano = 'II';
            break;
        case 3:
            $romano = 'III';
            break;
        case 4:
            $romano = 'IV';
            break;
        case 5:
            $romano = 'V';
            break;
        case 6:
            $romano = 'VI';
            break;
        case 7:
            $romano = 'VII';
            break;
        case 8:
            $romano = 'VIII';
            break;
        case 9:
            $romano = 'IX';
            break;
        case 10:
            $romano = 'X';
            break;

        default:
            $romano = '';
            break;
    }

    return $romano;
}

function obtenerDetalleIncisoEspecialistaArt8($inciso)
{
    switch ($inciso) {
        case 'a':
            $detalle = '';
            break;
        case 'b':
            $detalle = 'Universitario';
            break;
        case 'c':
            $detalle = 'CONFEMECO';
            break;
        case 'd':
            $detalle = 'Por puntaje';
            break;
        case 'e':
            $detalle = 'Curso Superior';
            break;
        case 'f':
            $detalle = 'Residencia';
            break;

        default:
            $detalle = '';
            break;
    }

    return $detalle;
}

function obtenrTipoPago($detalleTipoPago)
{
    switch ($detalleTipoPago) {
        case 1:
            $detalle = '1';
            break;
        case 2:
            $detalle = 'Plan de Pagos';
            break;
        case 3:
            $detalle = 'Cuota Colegiación';
            break;
        case 4:
            $detalle = 'Nota de deuda';
            break;
        case 5:
            $detalle = '5';
            break;
        case 6:
            $detalle = '6';
            break;
        case 7:
            $detalle = 'Cursos';
            break;
        case 8:
            $detalle = 'Pago Total';
            break;
        case 9:
            $detalle = '9';
            break;

        default:
            $detalle = $detalleTipoPago;
            break;

    }
    return $detalle;
}

function ultmioDiaDelMes($fecha) {
    $date = new DateTime($fecha);
    $date->modify('last day of this month');
    $ultimoDia = $date->format('Y-m-d');

    return $ultimoDia;
}

function formatearTexto($texto) {
    // Convierte a minúsculas y pone en mayúscula cada palabra (multibyte safe)
    return mb_convert_case($texto, MB_CASE_TITLE, "UTF-8");
}