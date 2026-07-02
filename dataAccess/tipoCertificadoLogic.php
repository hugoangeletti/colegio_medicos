<?php
class tipoCertificadoLogic {

    public function obtenerTipoCertificadoFiltrado($codigoDeudor) {
    //condicionantes por codigoDeudor
    if ($codigoDeudor > '1') {
        $filtro = "AND DeudaPeriodosAnteriores = 'S'";
    } else {
        if ($codigoDeudor == '1') {
            $filtro = "AND DeudaPeriodoActual = 'S'";
        } else {
            $filtro = "";
        }
    }

    $resultado = array();
    try {
        $db = Database::getConnection();
        $sql = "SELECT Id, Detalle, ImprimeConDeuda, ImprimirSinFotoFirma, ParaExterior
            FROM tipocertificado
            WHERE Estado = 'A' " . $filtro . "
            ORDER BY Detalle";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll();
        $datos = array();
        foreach ($rows as $row) {
            $datos[] = array(
                'id' => $row['Id'],
                'nombre' => $row['Detalle'],
                'imprimeConDeuda' => $row['ImprimeConDeuda'],
                'imprimirSinFotoFirma' => $row['ImprimirSinFotoFirma'],
                'paraExterior' => $row['ParaExterior']
            );
        }
        $resultado['estado'] = TRUE;
        $resultado['mensaje'] = "OK";
        $resultado['datos'] = $datos;
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando Tipo de Certificado: " . $e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
    }
    return $resultado;
}

    public function obtenerTipoCertificadoPorId($id) {
    $resultado = array();
    try {
        $db = Database::getConnection();
        $sql = "SELECT * FROM tipocertificado WHERE Id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if ($row) {
            $datos = array(
                'id' => $row['Id'],
                'detalle' => $row['Detalle'],
                'imprimeConDeuda' => $row['ImprimeConDeuda'],
                'estado' => $row['Estado'],
                'deudaPeriodoActual' => $row['DeudaPeriodoActual'],
                'deudaPeriodosAnteriores' => $row['DeudaPeriodosAnteriores'],
                'conFirma' => $row['ConFirma'],
                'muestraDestino' => $row['MuestraDestino'],
                'imprimirSinFotoFirma' => $row['ImprimirSinFotoFirma'],
                'paraExterior' => $row['ParaExterior']
            );
            $resultado['estado'] = true;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = false;
            $resultado['mensaje'] = "No se encontro Tipo Certificado";
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando Tipo Certificado: " . $e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
    }
    return $resultado;
}
}
