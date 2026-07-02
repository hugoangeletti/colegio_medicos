<?php
class presidenteMesaLogic {

    public function obtenerPresidentesMesaParaNotificar($anio, $rango) {
    $db = Database::getConnection();
    $sql="SELECT elmtm.IdELMTurnoMatricula, c.Id, c.Matricula, p.Apellido, p.Nombres, p.Sexo,
        cc.CorreoElectronico, elm.Detalle, elm.Fecha, elt.HoraDesde, elt.HoraHasta
        FROM eleccioneslocalidadmesaturnomatricula elmtm
        INNER JOIN eleccioneslocalidadmesaturno elmt ON(elmt.IdELMTurno = elmtm.IdELMTurno)
        INNER JOIN eleccioneslocalidadturno elt ON(elt.IdELTurno = elmt.IdELTurno)
        INNER JOIN eleccioneslocalidadmesa elm ON(elm.IdELMesa = elmt.IdELMesa)
        INNER JOIN eleccioneslocalidad el ON(el.IdEleccionesLocalidad = elm.IdEleccionesLocalidad)
        INNER JOIN elecciones e ON(e.IdElecciones = el.IdElecciones)
        INNER JOIN colegiado c ON(c.Id = elmtm.IdColegiado AND c.Estado IN(1, 5, 10))
        INNER JOIN persona p ON(p.Id = c.IdPersona)
        INNER JOIN colegiadocontacto cc ON(c.Id = cc.idColegiado AND cc.idEstado = 1)
        LEFT JOIN enviomaildiariocolegiado ON(enviomaildiariocolegiado.IdColegiado = elmtm.IdColegiado
            AND enviomaildiariocolegiado.IdReferencia = elmtm.IdELMTurnoMatricula)
        WHERE e.Anio = ? AND e.Estado = 'A'
            AND enviomaildiariocolegiado.Id IS NULL
        LIMIT ?";
    $resultado = array();
    try {
        $stmt = $db->prepare($sql);
        $stmt->execute([$anio, $rango]);
        $rows = $stmt->fetchAll();
        if (count($rows) > 0) {
            $resultado['cantidad'] = count($rows);
            $datos = array();
            foreach ($rows as $r) {
                $fecha     = $r['Fecha'];
                $horaDesde = $r['HoraDesde'];
                $horaHasta = $r['HoraHasta'];
                /*
                if ($r['Sexo'] == 'M') {
                    $sexo = 'Masculino';
                } else {
                    $sexo = 'Femenino';
                }
                */
                $dia = substr($fecha, 8, 2);
                $mes = obtenerMes(substr($fecha, 5, 2));
                $laFecha = $dia.' de '.$mes;
                $row = array (
                    'idReferencia' => $r['IdELMTurnoMatricula'],
                    'idColegiado'  => $r['Id'],
                    'matricula'    => $r['Matricula'],
                    'apellido'     => $r['Apellido'],
                    'nombres'      => $r['Nombres'],
                    'sexo'         => $r['Sexo'],
                    'mail'         => $r['CorreoElectronico'],
                    'mesa'         => $r['Detalle'],
                    'fecha'        => $laFecha,
                    'hora'         => substr($horaDesde, 0, 5).' a '.substr($horaHasta, 0, 5)
                 );
                array_push($datos, $row);
            }
            $resultado['estado'] = true;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = false;
            $resultado['mensaje'] = "No hay personas";
            $resultado['clase'] = 'alert alert-info';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando personas";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}
}
