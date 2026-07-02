<?php
class intencion_pago_pdo {
    
    const INTENCION_PAGO_INICIADA = '1';
    const INTENCION_PAGO_ENVIADA_A_GIRE = '2';
    const INTENCION_PAGO_APROBADA_POR_GIRE = '3';
    const INTENCION_PAGO_FINALIZDA = '4';

    function obtenerIntencionPagoPorId($idIntencionPago) {
        try {
            // Se asume que conectar() ahora retorna un objeto PDO
            $db = Database::getConnection();

            $sql = "SELECT ip.IdColegiado, ip.FechaInicio, ip.Hash, ip.TotalPago, ip.IdGire, ip.RespuestaGire, ip.IdEstadoIntencionPago, ip.FechaModificacion, ip.Borrado
                FROM intencion_pago ip
                WHERE ip.Id = :id";

            $stmt = $db->prepare($sql);
            $stmt->bindParam(':id', $idIntencionPago, PDO::PARAM_INT);
            $stmt->execute();

            // fetch(PDO::FETCH_ASSOC) nos devuelve un array asociativo directamente
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row) {
                return [
                    'estado' => true,
                    'mensaje' => "OK",
                    'datos' => $row, // PDO ya nos da el array con los nombres de las columnas
                    'clase' => 'alert alert-success',
                    'icono' => 'glyphicon glyphicon-ok'
                ];
            } else {
                return [
                    'estado' => false,
                    'datos' => null,
                    'mensaje' => "No se encontró intención de pago",
                    'clase' => 'alert alert-warning',
                    'icono' => 'glyphicon glyphicon-exclamation-sign'
                ];
            }

        } catch (PDOException $e) {
            // Es buena práctica loguear el error real: error_log($e->getMessage());
            return [
                'estado' => false,
                'mensaje' => "Error buscando intención de pago",
                'clase' => 'alert alert-danger',
                'icono' => 'glyphicon glyphicon-remove'
            ];
        }
    }

    function obtenerReunionesPorPeriodo($idIntencionPago) {
        try {
            $db = Database::getConnection();
            
            $sql = "SELECT ipd.Id, ipd.IdColegiadoDeudaAnualCuota, ipd.Importe
                FROM intencion_pago_detalle ipd
                WHERE ipd.IdIntencioPago = :id";

            $stmt = $db->prepare($sql);
            $stmt->bindParam(':id', $idIntencionPago, PDO::PARAM_INT);
            $stmt->execute();

            // Obtenemos todas las filas
            $rows = $stmt->fetchAll();

            if (count($rows) > 0) {
                return [
                    'estado' => true,
                    'mensaje' => "OK",
                    'datos' => $rows,
                    'clase' => 'alert alert-success',
                    'icono' => 'glyphicon glyphicon-ok'
                ];
            }

            return [
                'estado' => false,
                'datos' => null,
                'mensaje' => "No se encontró el detalle de la intención de pago",
                'clase' => 'alert alert-warning',
                'icono' => 'glyphicon glyphicon-exclamation-sign'
            ];

        } catch (PDOException $e) {
            return [
                'estado' => false,
                'mensaje' => "Error buscando detalle de la intención de pago",
                'clase' => 'alert alert-danger',
                'icono' => 'glyphicon glyphicon-remove'
            ];
        }
    }

    function guardarIntencionPago($idColegiado, $cuotas_seleccionadas, $total_pago) {
        try {
            $db = Database::getConnection();
            
            // Iniciamos la transacción
            $db->beginTransaction();

            $creado = date('YmdHis');
            $hasIntencionPago = hashData($idColegiado.'_'.$creado);

            $sql = "INSERT INTO intencion_pago (IdColegiado, FechaInicio, Hash, TotalPago, IdEstadoIntencionPago)
                    VALUES (:idColegiado, NOW(), :hash, :total_pago, :idEstadoIntencionPago)";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':idColegiado'              => $idColegiado,
                ':hash'                     => $hasIntencionPago,
                ':total_pago'               => (float)$total_pago,
                ':idEstadoIntencionPago'    => self::INTENCION_PAGO_INICIADA
            ]);
            
            // Obtenemos el ID generado para el log
            $idIntencionPago = $db->lastInsertId();

            $sql = "INSERT INTO intencion_pago_detalle (IdIntencioPago, IdColegiadoDeudaAnualCuota, Importe)
                    VALUES (:idIntencionPago, :idColegiadoDeudaAnualCuota, :importe)";
            $stmt = $db->prepare($sql);
            foreach ($cuotas_seleccionadas as $cuota) {
                $stmt->execute([
                    ':idIntencionPago'              => $idIntencionPago,
                    ':idColegiadoDeudaAnualCuota'   => $cuota['idColegiadoDeudaAnualCuota'],
                    ':importe'                      => (float)$cuota['importe']
                ]);
            }
            // Si todo salió bien, confirmamos los cambios
            $db->commit();

            return [
                'estado'             => true,
                'idIntencionPago'    => $idIntencionPago,
                'hashIntencionPago'  => $hashIntencionPago,
                'mensaje'            => "Intención de pago generada con éxito.",
                'clase'              => 'alert alert-success',
                'icono'              => 'glyphicon glyphicon-ok'
            ];

        } catch (Exception $e) {
            if (isset($db)) { $db->rollback(); }
            return [
                'estado'  => false,
                'mensaje' => "Error al guardar: " . $e->getMessage()
            ];
        }
    }

    function obtenerMensaje($codigo_mensaje) {
        switch ($codigo_mensaje) {
            case '2':
                $mensaje = "ERROR: ";
                break;
            
            case '7':
                $mensaje = "ERROR en los parámetros.";
                break;
            
            default:
                $mensaje = "ERROR genérico.";
                break;
        }
        return $mensaje;
    }

    /**
     * Método genérico privado para obtener catálogos simples (Id, Nombre)
     */
    private function obtenerCatalogoSimple($tabla, $nombreEntidad) {
        $tablasPermitidas = ['estado_intencion_pago'];
        if (!in_array($tabla, $tablasPermitidas)) {
            throw new Exception("Tabla no permitida");
        }
        try {
            $db = Database::getConnection();
            // Nota: En PDO, las variables de tabla no pueden ser parámetros (:tabla), 
            // por eso se inyectan directamente en el string. Al ser nombres fijos 
            // definidos por el desarrollador, es seguro.
            $sql = "SELECT Id as id, Nombre as nombre FROM $tabla ORDER BY Nombre";
            
            $stmt = $db->query($sql);
            $datos = $stmt->fetchAll();

            if (count($datos) > 0) {
                return [
                    'estado'  => true,
                    'mensaje' => "OK",
                    'datos'   => $datos,
                    'clase'   => 'alert alert-success',
                    'icono'   => 'glyphicon glyphicon-ok'
                ];
            }

            return [
                'estado'  => false,
                'datos'   => null,
                'mensaje' => "No se encontraron $nombreEntidad",
                'clase'   => 'alert alert-warning',
                'icono'   => 'glyphicon glyphicon-exclamation-sign'
            ];
        } catch (PDOException $e) {
            return [
                'estado'  => false, 
                'mensaje' => "Error buscando $nombreEntidad", 
                'clase'   => 'alert alert-danger', 
                'icono'   => 'glyphicon glyphicon-remove'
            ];
        }
    }

    /* --- Métodos Públicos --- */

    function obtenerEstadosIntencionPago() {
        return $this->obtenerCatalogoSimple('estado_intencion_pago', 'estados');
    }

}
