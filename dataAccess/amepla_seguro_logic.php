<?php
class amepla_seguro_logic {

    public function obtenerMatriculasWsAmepla($procesoAnio, $procesoMes) {
        ini_set('xdebug.var_display_max_depth', -1);
        ini_set('xdebug.var_display_max_children', -1);
        ini_set('xdebug.var_display_max_data', -1);
        set_time_limit(0);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://sistemas.amepla.org.ar/servicios/padron/seguro/completo/');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_HTTPHEADER, []);
        $respuesta = curl_exec($ch);
        $err = curl_error($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($err) {
            return [
                'estado'  => false,
                'mensaje' => "Error buscando colegiados -> " . $err,
                'clase'   => 'alert alert-danger',
                'icono'   => 'glyphicon glyphicon-remove'
            ];
        }

        switch ($http_code) {
            case 200:
                $rta = json_decode($respuesta, true);
                $matriculas = $rta['matriculas'];
                $serial_number = $rta['serial_number'];
                if ($procesoAnio == substr($serial_number, 0, 4) && $procesoMes == substr($serial_number, 4, 2)) {
                    return [
                        'estado'  => false,
                        'mensaje' => "El lote ya fue procesado",
                        'clase'   => 'alert alert-warning',
                        'icono'   => 'glyphicon glyphicon-exclamation-sign'
                    ];
                }
                $datos = [];
                foreach ($matriculas as $value) {
                    $datos[] = ['matricula' => $value];
                }
                return [
                    'estado'        => true,
                    'datos'         => $datos,
                    'serial_number' => $serial_number,
                    'mensaje'       => "OK",
                    'clase'         => 'alert alert-success',
                    'icono'         => 'glyphicon glyphicon-ok'
                ];

            case 400:
                return [
                    'estado'  => false,
                    'mensaje' => "Error buscando colegiados -> Respuesta del servicio errónea",
                    'clase'   => 'alert alert-danger',
                    'icono'   => 'glyphicon glyphicon-remove'
                ];

            default:
                return [
                    'estado'  => false,
                    'mensaje' => "Código HTTP inesperado: " . $http_code,
                    'clase'   => 'alert alert-danger',
                    'icono'   => 'glyphicon glyphicon-remove'
                ];
        }
    }
}
