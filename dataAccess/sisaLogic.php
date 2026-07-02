<?php
class sisaLogic {

    public function obtenerSisaExportacionPorId($idSisaExportacion) {
    try {
        $db = Database::getConnection();
        $resultado = array();
        $sql = "SELECT a.Id, a.FechaProceso, a.PeriodoProceso, a.NombreArchivoColegiados, a.NombreArchivoEspecialistas, a.PathArchivos
                FROM sisa_exportacion a
                WHERE a.Id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idSisaExportacion]);
        $rows = $stmt->fetchAll();
        $resultado['cantidad'] = count($rows);
        if ($resultado['cantidad'] > 0) {
            $row = $rows[0];
            $datos = array (
                    'idSisaExportacion' => $row['Id'],
                    'fechaProceso' => $row['FechaProceso'],
                    'periodoProceso' => $row['PeriodoProceso'],
                    'nombreArchivoColegiados' => $row['NombreArchivoColegiados'],
                    'nombreArchivoEspecialistas' => $row['NombreArchivoEspecialistas'],
                    'pathArchivo' => $row['PathArchivos']
                    );
            $resultado['estado'] = true;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = false;
            $resultado['mensaje'] = "No se encontro sisa exportacion";
            $resultado['clase'] = 'alert alert-info';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando sisa exportacion: " . $e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function obtenerSisaExportaciones($anio) {
    try {
        $db = Database::getConnection();
        $resultado = array();
        $sql = "SELECT a.Id, a.FechaProceso, a.PeriodoProceso, a.NombreArchivoColegiados, a.NombreArchivoEspecialistas, a.PathArchivos, a.Borrado
            FROM sisa_exportacion a
            WHERE SUBSTR(a.PeriodoProceso, 1, 4) = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$anio]);
        $rows = $stmt->fetchAll();
        $resultado['cantidad'] = count($rows);
        if ($resultado['cantidad'] > 0) {
            $datos = array();
            foreach ($rows as $row) {
                $item = array (
                    'idSisaExportacion' => $row['Id'],
                    'fechaProceso' => $row['FechaProceso'],
                    'periodoProceso' => $row['PeriodoProceso'],
                    'nombreArchivoColegiados' => $row['NombreArchivoColegiados'],
                    'nombreArchivoEspecialistas' => $row['NombreArchivoEspecialistas'],
                    'pathArchivo' => $row['PathArchivos'],
                    'borrado' => $row['Borrado']
                    );
                array_push($datos, $item);
            }
            $resultado['estado'] = true;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = false;
            $resultado['mensaje'] = "No se encontro sisa exportacion";
            $resultado['clase'] = 'alert alert-info';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando sisa exportacion: " . $e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function obtenerColegiadoParaExportacion($inicio, $limite) {
    try {
        $db = Database::getConnection();
        $resultado = array();
        $sql = "SELECT p.Nombres, p.Apellido, p.TipoDocumento, p.NumeroDocumento, p.Sexo, p.FechaNacimiento, pa.Codigo, c.Estado, (SELECT MAX(cm.FechaDesde) FROM colegiadomovimiento cm WHERE cm.IdColegiado = c.Id AND cm.IdMovimiento = 7) AS FechaFallecimiento, i.Codigo AS CodigoInstitucion, (SELECT ct.FechaTitulo FROM colegiadotitulo ct WHERE c.Id = ct.IdColegiado LIMIT 1) AS FechaTitulo, c.Matricula, c.FechaMatriculacion, c.Tomo, c.Folio, tm.Estado AS CodigoEstado, tm.Temporalidad, (SELECT MAX(cm.FechaDesde) FROM colegiadomovimiento cm WHERE c.Id = cm.IdColegiado AND tm.Estado IN('C', 'F')) AS FechaMovimiento, tm.MotivoInactividad, cdr.Calle, cdr.Numero, cdr.Piso, cdr.Departamento, cdr.idLocalidad, cdr.CodigoPostal, cc.TelefonoFijo, cc.TelefonoMovil, cc.CorreoElectronico, sl.Codigo AS CodigoLocalidad, sl.CodigoPartido, sl.CodigoProvincia
                FROM colegiado c
                INNER JOIN persona p ON p.Id = c.IdPersona
                LEFT JOIN paises pa ON pa.Id = p.IdPaises
                LEFT JOIN colegiadodomicilioreal cdr ON cdr.IdColegiado = c.Id AND cdr.idEstado = 1
                LEFT JOIN sisa_localidad sl ON sl.IdLocalidad = cdr.idLocalidad
                INNER JOIN colegiadocontacto cc ON cc.IdColegiado = c.Id and cc.idEstado = 1
                INNER JOIN universidad u ON u.Id = (SELECT ct.IdUniversidad FROM colegiadotitulo ct WHERE c.Id = ct.IdColegiado LIMIT 1)
                LEFT JOIN institucionformadora i ON i.Institucion = u.Nombre
                INNER JOIN tipomovimiento tm ON tm.Id = c.Estado
                WHERE p.NumeroDocumento >= 1000000
                ORDER BY p.NumeroDocumento
                LIMIT ?, ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$inicio, $limite]);
        $rows = $stmt->fetchAll();
        $resultado['cantidad'] = count($rows);
        if ($resultado['cantidad'] > 0) {
            $datos = array();
            foreach ($rows as $row) {
                $item = array (
                    'nombre' => $row['Nombres'],
                    'apellido' => $row['Apellido'],
                    'tipoDocumento' => $row['TipoDocumento'],
                    'numeroDocumento' => $row['NumeroDocumento'],
                    'sexo' => $row['Sexo'],
                    'fechaNacimiento' => $row['FechaNacimiento'],
                    'codigoPais' => $row['Codigo'],
                    'estadoMatricular' => $row['Estado'],
                    'fechaFallecimiento' => $row['FechaFallecimiento'],
                    'codigoInstitucionFormadora' => $row['CodigoInstitucion'],
                    'fechaTitulo' => $row['FechaTitulo'],
                    'matricula' => $row['Matricula'],
                    'fechaMatriculacion' => $row['FechaMatriculacion'],
                    'tomo' => $row['Tomo'],
                    'folio' => $row['Folio'],
                    'codigoEstado' => $row['CodigoEstado'],
                    'temporalidad' => $row['Temporalidad'],
                    'fechaUltimoMovimiento' => $row['FechaMovimiento'],
                    'motivoInactividad' => $row['MotivoInactividad'],
                    'calle' => $row['Calle'],
                    'numero' => $row['Numero'],
                    'piso' => $row['Piso'],
                    'departamento' => $row['Departamento'],
                    'idLocalidad' => $row['idLocalidad'],
                    'codigoPostal' => $row['CodigoPostal'],
                    'telefono1' => $row['TelefonoFijo'],
                    'telefono2' => $row['TelefonoMovil'],
                    'correoElectronico' => $row['CorreoElectronico'],
                    'codigoLocalidadSisa' => $row['CodigoLocalidad'],
                    'codigoPartidoSisa' => $row['CodigoPartido'],
                    'codigoProvinciaSisa' => $row['CodigoProvincia']
                    );
                array_push($datos, $item);
            }
            $resultado['estado'] = true;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = false;
            $resultado['mensaje'] = "No se encontro sisa exportacion";
            $resultado['clase'] = 'alert alert-info';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando sisa exportacion: " . $e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function obtenerEspecialistasParaExportacion($inicio, $limite) {
    try {
        $db = Database::getConnection();
        $resultado = array();
        $sql = "SELECT p.TipoDocumento, p.NumeroDocumento, c.Matricula, e.CodigoRes62707, ce.FechaEspecialista, ce.FechaVencimiento
                FROM colegiadoespecialista ce
                INNER JOIN colegiado c ON c.Id = ce.IdColegiado
                INNER  JOIN persona p ON p.Id = c.IdPersona
                INNER JOIN especialidad e ON e.Id = ce.Especialidad
                where p.NumeroDocumento >= 1000000
                AND e.IdTipoEspecialidad <> 3
                AND e.CodigoRes62707 IS NOT NULL
                AND ce.Estado = 'A'
                ORDER BY p.NumeroDocumento
                LIMIT ?, ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$inicio, $limite]);
        $rows = $stmt->fetchAll();
        $resultado['cantidad'] = count($rows);
        if ($resultado['cantidad'] > 0) {
            $datos = array();
            foreach ($rows as $row) {
                $item = array (
                    'tipoDocumento' => $row['TipoDocumento'],
                    'numeroDocumento' => $row['NumeroDocumento'],
                    'matricula' => $row['Matricula'],
                    'codigoRes62707' => $row['CodigoRes62707'],
                    'fechaEspecialista' => $row['FechaEspecialista'],
                    'fechaVencimiento' => $row['FechaVencimiento']
                    );
                array_push($datos, $item);
            }
            $resultado['estado'] = true;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = false;
            $resultado['mensaje'] = "No se encontro sisa exportacion";
            $resultado['clase'] = 'alert alert-info';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando sisa exportacion: " . $e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function agregarSisaExportacion($periodoProceso, $nombreArchivoColegiados, $nombreArchivoEspecialistas, $path) {
    try {
        $db = Database::getConnection();
        $resultado = array();
        $sql="INSERT INTO sisa_exportacion
                (FechaProceso, PeriodoProceso, IdUsuario, NombreArchivoColegiados, NombreArchivoEspecialistas, PathArchivos)
                VALUE (NOW(), ?, 1, ?, ?, ?)";
        $stmt = $db->prepare($sql);
        $stmt->execute([$periodoProceso, $nombreArchivoColegiados, $nombreArchivoEspecialistas, $path]);
        $idSisaExportacion = $db->lastInsertId();
        $resultado['estado'] = TRUE;
        $resultado['mensaje'] = "OK";
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
        $resultado['idSisaExportacion'] = $idSisaExportacion;
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "ERROR AL INSERTAR sisa_exportacion -> " . $e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function siExisteExportacion($periodoProceso) {
    try {
        $db = Database::getConnection();
        $sql = "SELECT a.Id FROM sisa_exportacion a WHERE a.PeriodoProceso = ? AND a.Borrado = 0";
        $stmt = $db->prepare($sql);
        $stmt->execute([$periodoProceso]);
        $rows = $stmt->fetchAll();
        if (count($rows) > 0) {
            return TRUE;
        } else {
            return FALSE;
        }
    } catch (PDOException $e) {
        return FALSE;
    }
}
}
