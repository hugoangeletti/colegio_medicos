<?php
class colegiadoLogic {

    public function obtenerColegiadoPorMatricula($matricula) {
        try {
            $db = Database::getConnection();
            $sql = "SELECT c.Id AS idColegiado, c.Matricula AS matricula, c.Tomo AS tomo, c.Folio AS folio,
                           c.FechaMatriculacion AS fechaMatriculacion, c.MatriculaNacional AS matriculaNacional,
                           c.DistritoOrigen AS distritoOrigen, p.Apellido AS apellido, p.Nombres AS nombre,
                           p.Sexo AS sexo, p.NumeroDocumento AS numeroDocumento, p.FechaNacimiento AS fechaNacimiento,
                           tm.Detalle AS detalleMovimiento, tm.DetalleCompleto AS movimientoCompleto,
                           tm.Estado AS tipoEstado, td.Nombre AS tipoDocumento, pa.Nacionalidad AS nacionalidad,
                           c.Estado AS estado, ct.FechaTitulo AS fechaTitulo,
                           ct.Digital AS tituloDigital, c.Hash AS hashColegiado,
                           caa.Id AS idColegiadoAgremiadoAsegurado, cs.Id AS idColegiadoSeguro,
                           c.FechaActualizacion AS fechaActualizacion
                    FROM colegiado c
                    INNER JOIN persona p ON p.Id = c.IdPersona
                    INNER JOIN colegiadotitulo ct ON ct.IdColegiado = c.Id
                    INNER JOIN tipomovimiento tm ON tm.Id = c.Estado
                    INNER JOIN tipodocumento td ON td.IdTipoDocumento = p.TipoDocumento
                    INNER JOIN paises pa ON pa.Id = p.IdPaises
                    LEFT JOIN colegiado_agremiado_asegurado caa ON caa.Matricula = c.Matricula
                    LEFT JOIN colegiado_seguro cs ON cs.Matricula = c.Matricula AND cs.Activo = 1 AND cs.Borrado = 0
                    WHERE c.Matricula = :matricula
                    LIMIT 1";

            $stmt = $db->prepare($sql);
            $stmt->bindParam(':matricula', $matricula, PDO::PARAM_STR);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            $resultado = ['estado' => true];

            if ($row) {
                $idColegiadoAgremiadoAsegurado = $row['idColegiadoAgremiadoAsegurado'];
                $idColegiadoSeguro = $row['idColegiadoSeguro'];
                if (isset($idColegiadoAgremiadoAsegurado) && $idColegiadoAgremiadoAsegurado > 0) {
                    $conSeguro = 'ASEGURADO_AGREMIADO';
                } else {
                    if (isset($idColegiadoSeguro) && $idColegiadoSeguro > 0) {
                        $conSeguro = 'ASEGURADO_COLEGIO';
                    } else {
                        $conSeguro = null;
                    }
                }

                return [
                    'estado' => true,
                    'datos' => [
                        'idColegiado'        => $row['idColegiado'],
                        'matricula'          => $row['matricula'],
                        'tomo'               => $row['tomo'],
                        'folio'              => $row['folio'],
                        'fechaMatriculacion' => $row['fechaMatriculacion'],
                        'matriculaNacional'  => $row['matriculaNacional'],
                        'distritoOrigen'     => $row['distritoOrigen'],
                        'apellido'           => $row['apellido'],
                        'nombre'             => $row['nombre'],
                        'sexo'               => $row['sexo'],
                        'numeroDocumento'    => $row['numeroDocumento'],
                        'fechaNacimiento'    => $row['fechaNacimiento'],
                        'detalleMovimiento'  => $row['detalleMovimiento'],
                        'movimientoCompleto' => $row['movimientoCompleto'],
                        'tipoEstado'         => $row['tipoEstado'],
                        'tipoDocumento'      => $row['tipoDocumento'],
                        'nacionalidad'       => $row['nacionalidad'],
                        'estado'             => $row['estado'],
                        'fechaTitulo'        => $row['fechaTitulo'],
                        'idEstadoMatricular' => $row['estado'],
                        'tituloDigital'      => $row['tituloDigital'],
                        'hashColegiado'      => $row['hashColegiado'],
                        'conSeguro'          => $conSeguro,
                        'fechaActualizacion' => $row['fechaActualizacion']
                    ],
                    'mensaje' => "OK",
                    'clase'   => 'alert alert-success',
                    'icono'   => 'glyphicon glyphicon-ok'
                ];
            }

            return [
                'estado'  => true,
                'datos'   => null,
                'mensaje' => "No hay colegiado",
                'clase'   => 'alert alert-info',
                'icono'   => 'glyphicon glyphicon-exclamation-sign'
            ];

        } catch (PDOException $e) {
            return [
                'estado'  => false,
                'mensaje' => "Error buscando colegiado",
                'clase'   => 'alert alert-danger',
                'icono'   => 'glyphicon glyphicon-remove'
            ];
        }
    }

    public function obtenerColegiadoPorId($idColegiado) {
        try {
            $db = Database::getConnection();
            $sql = "SELECT c.Id AS idColegiado, c.Matricula AS matricula, c.Tomo AS tomo, c.Folio AS folio,
                           c.FechaMatriculacion AS fechaMatriculacion, c.MatriculaNacional AS matriculaNacional,
                           c.DistritoOrigen AS distritoOrigen, p.Apellido AS apellido, p.Nombres AS nombre,
                           p.Sexo AS sexo, p.NumeroDocumento AS numeroDocumento, p.FechaNacimiento AS fechaNacimiento,
                           tm.Detalle AS detalleMovimiento, tm.DetalleCompleto AS movimientoCompleto,
                           tm.Estado AS tipoEstado, td.Nombre AS tipoDocumento, pa.Nacionalidad AS nacionalidad,
                           c.Estado AS estado, ct.FechaTitulo AS fechaTitulo,
                           ct.Digital AS tituloDigital, c.Hash AS hashColegiado,
                           caa.Id AS idColegiadoAgremiadoAsegurado, cs.Id AS idColegiadoSeguro
                    FROM colegiado c
                    INNER JOIN persona p ON p.Id = c.IdPersona
                    INNER JOIN colegiadotitulo ct ON ct.IdColegiado = c.Id
                    INNER JOIN tipomovimiento tm ON tm.Id = c.Estado
                    INNER JOIN tipodocumento td ON td.IdTipoDocumento = p.TipoDocumento
                    INNER JOIN paises pa ON pa.Id = p.IdPaises
                    LEFT JOIN colegiado_agremiado_asegurado caa ON caa.Matricula = c.Matricula
                    LEFT JOIN colegiado_seguro cs ON cs.Matricula = c.Matricula AND cs.Activo = 1 AND cs.Borrado = 0
                    WHERE c.Id = :idColegiado
                    LIMIT 1";

            $stmt = $db->prepare($sql);
            $stmt->bindParam(':idColegiado', $idColegiado, PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row) {
                $idColegiadoAgremiadoAsegurado = $row['idColegiadoAgremiadoAsegurado'];
                $idColegiadoSeguro = $row['idColegiadoSeguro'];
                if (isset($idColegiadoAgremiadoAsegurado) && $idColegiadoAgremiadoAsegurado > 0) {
                    $conSeguro = 'ASEGURADO_AGREMIADO';
                } else {
                    if (isset($idColegiadoSeguro) && $idColegiadoSeguro > 0) {
                        $conSeguro = 'ASEGURADO_COLEGIO';
                    } else {
                        $conSeguro = null;
                    }
                }

                return [
                    'estado' => true,
                    'datos'  => [
                        'idColegiado'        => $row['idColegiado'],
                        'matricula'          => $row['matricula'],
                        'tomo'               => $row['tomo'],
                        'folio'              => $row['folio'],
                        'fechaMatriculacion' => $row['fechaMatriculacion'],
                        'matriculaNacional'  => $row['matriculaNacional'],
                        'distritoOrigen'     => $row['distritoOrigen'],
                        'apellido'           => $row['apellido'],
                        'nombre'             => $row['nombre'],
                        'sexo'               => $row['sexo'],
                        'numeroDocumento'    => $row['numeroDocumento'],
                        'fechaNacimiento'    => $row['fechaNacimiento'],
                        'detalleMovimiento'  => $row['detalleMovimiento'],
                        'movimientoCompleto' => $row['movimientoCompleto'],
                        'tipoEstado'         => $row['tipoEstado'],
                        'tipoDocumento'      => $row['tipoDocumento'],
                        'nacionalidad'       => $row['nacionalidad'],
                        'estado'             => $row['estado'],
                        'fechaTitulo'        => $row['fechaTitulo'],
                        'idEstadoMatricular' => $row['estado'],
                        'tituloDigital'      => $row['tituloDigital'],
                        'hashColegiado'      => $row['hashColegiado'],
                        'conSeguro'          => $conSeguro
                    ],
                    'mensaje' => "OK",
                    'clase'   => 'alert alert-success',
                    'icono'   => 'glyphicon glyphicon-ok'
                ];
            }

            return [
                'estado'  => true,
                'datos'   => null,
                'mensaje' => "No hay colegiado " . $idColegiado,
                'clase'   => 'alert alert-info',
                'icono'   => 'glyphicon glyphicon-exclamation-sign'
            ];

        } catch (PDOException $e) {
            return [
                'estado'  => false,
                'mensaje' => "Error buscando colegiado",
                'clase'   => 'alert alert-danger',
                'icono'   => 'glyphicon glyphicon-remove'
            ];
        }
    }

    public function obtenerColegiadoPorHash($hashColegiado) {
        try {
            $db = Database::getConnection();
            $sql = "SELECT c.Id AS idColegiado, c.Matricula AS matricula, c.Tomo AS tomo, c.Folio AS folio,
                           c.FechaMatriculacion AS fechaMatriculacion, c.MatriculaNacional AS matriculaNacional,
                           c.DistritoOrigen AS distritoOrigen, p.Apellido AS apellido, p.Nombres AS nombre,
                           p.Sexo AS sexo, p.NumeroDocumento AS numeroDocumento, p.FechaNacimiento AS fechaNacimiento,
                           tm.Detalle AS detalleMovimiento, tm.DetalleCompleto AS movimientoCompleto,
                           tm.Estado AS tipoEstado, td.Nombre AS tipoDocumento, pa.Nacionalidad AS nacionalidad,
                           c.Estado AS estado, ct.FechaTitulo AS fechaTitulo,
                           ct.Digital AS tituloDigital, c.Hash AS hashColegiadoCol,
                           caa.Id AS idColegiadoAgremiadoAsegurado, cs.Id AS idColegiadoSeguro
                    FROM colegiado c
                    INNER JOIN persona p ON p.Id = c.IdPersona
                    INNER JOIN colegiadotitulo ct ON ct.IdColegiado = c.Id
                    INNER JOIN tipomovimiento tm ON tm.Id = c.Estado
                    INNER JOIN tipodocumento td ON td.IdTipoDocumento = p.TipoDocumento
                    INNER JOIN paises pa ON pa.Id = p.IdPaises
                    LEFT JOIN colegiado_agremiado_asegurado caa ON caa.Matricula = c.Matricula
                    LEFT JOIN colegiado_seguro cs ON cs.Matricula = c.Matricula AND cs.Activo = 1 AND cs.Borrado = 0
                    WHERE c.Hash = :hashColegiado
                    LIMIT 1";

            $stmt = $db->prepare($sql);
            $stmt->bindParam(':hashColegiado', $hashColegiado, PDO::PARAM_STR);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row) {
                $idColegiadoAgremiadoAsegurado = $row['idColegiadoAgremiadoAsegurado'];
                $idColegiadoSeguro = $row['idColegiadoSeguro'];
                if (isset($idColegiadoAgremiadoAsegurado) && $idColegiadoAgremiadoAsegurado > 0) {
                    $conSeguro = 'ASEGURADO_AGREMIADO';
                } else {
                    if (isset($idColegiadoSeguro) && $idColegiadoSeguro > 0) {
                        $conSeguro = 'ASEGURADO_COLEGIO';
                    } else {
                        $conSeguro = null;
                    }
                }

                return [
                    'estado' => true,
                    'datos'  => [
                        'idColegiado'        => $row['idColegiado'],
                        'matricula'          => $row['matricula'],
                        'tomo'               => $row['tomo'],
                        'folio'              => $row['folio'],
                        'fechaMatriculacion' => $row['fechaMatriculacion'],
                        'matriculaNacional'  => $row['matriculaNacional'],
                        'distritoOrigen'     => $row['distritoOrigen'],
                        'apellido'           => $row['apellido'],
                        'nombre'             => $row['nombre'],
                        'sexo'               => $row['sexo'],
                        'numeroDocumento'    => $row['numeroDocumento'],
                        'fechaNacimiento'    => $row['fechaNacimiento'],
                        'detalleMovimiento'  => $row['detalleMovimiento'],
                        'movimientoCompleto' => $row['movimientoCompleto'],
                        'tipoEstado'         => $row['tipoEstado'],
                        'tipoDocumento'      => $row['tipoDocumento'],
                        'nacionalidad'       => $row['nacionalidad'],
                        'estado'             => $row['estado'],
                        'fechaTitulo'        => $row['fechaTitulo'],
                        'idEstadoMatricular' => $row['estado'],
                        'tituloDigital'      => $row['tituloDigital'],
                        'hashColegiado'      => $row['hashColegiadoCol'],
                        'conSeguro'          => $conSeguro
                    ],
                    'mensaje' => "OK",
                    'clase'   => 'alert alert-success',
                    'icono'   => 'glyphicon glyphicon-ok'
                ];
            }

            return [
                'estado'  => true,
                'datos'   => null,
                'mensaje' => "No hay colegiado",
                'clase'   => 'alert alert-info',
                'icono'   => 'glyphicon glyphicon-exclamation-sign'
            ];

        } catch (PDOException $e) {
            return [
                'estado'  => false,
                'mensaje' => "Error buscando colegiado",
                'clase'   => 'alert alert-danger',
                'icono'   => 'glyphicon glyphicon-remove'
            ];
        }
    }

    public function obtenerIdColegiado($matricula) {
        try {
            $db = Database::getConnection();
            $sql = "SELECT colegiado.Id AS idColegiado
                    FROM colegiado
                    WHERE colegiado.Matricula = :matricula";

            $stmt = $db->prepare($sql);
            $stmt->bindParam(':matricula', $matricula, PDO::PARAM_STR);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row) {
                return [
                    'idColegiado' => $row['idColegiado'],
                    'estado'      => true,
                    'mensaje'     => "OK",
                    'clase'       => 'alert alert-success',
                    'icono'       => 'glyphicon glyphicon-ok'
                ];
            }

            return [
                'estado'  => false,
                'mensaje' => "No hay colegiado " . $matricula,
                'clase'   => 'alert alert-info',
                'icono'   => 'glyphicon glyphicon-exclamation-sign'
            ];

        } catch (PDOException $e) {
            return [
                'estado'  => false,
                'mensaje' => "Error buscando colegiado",
                'clase'   => 'alert alert-danger',
                'icono'   => 'glyphicon glyphicon-remove'
            ];
        }
    }

    public function obtenerColegiadosActivos($matricula) {
        try {
            $db = Database::getConnection();

            if (isset($matricula) && $matricula > 0) {
                $where = "WHERE c.Matricula = :matricula";
            } else {
                $where = "WHERE tm.Estado = 'A'";
            }

            $sql = "SELECT c.Id AS idColegiado, c.Matricula AS matricula, p.Apellido AS apellido,
                           p.Nombres AS nombre, p.NumeroDocumento AS numeroDocumento, c.Estado AS estadoMatricular
                    FROM colegiado c
                    INNER JOIN persona p ON p.Id = c.IdPersona
                    INNER JOIN tipomovimiento tm ON tm.Id = c.Estado
                    " . $where . "
                    ORDER BY c.Matricula";

            $stmt = $db->prepare($sql);

            if (isset($matricula) && $matricula > 0) {
                $stmt->bindParam(':matricula', $matricula, PDO::PARAM_INT);
            }

            $stmt->execute();
            $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $rows = [];
            foreach ($datos as $r) {
                $rows[] = [
                    'idColegiado'     => $r['idColegiado'],
                    'matricula'       => $r['matricula'],
                    'apellido'        => trim($r['apellido']),
                    'nombre'          => trim($r['nombre']),
                    'numeroDocumento' => $r['numeroDocumento'],
                    'estadoMatricular' => $r['estadoMatricular']
                ];
            }

            return [
                'estado'  => true,
                'mensaje' => "OK",
                'datos'   => $rows,
                'clase'   => 'alert alert-success',
                'icono'   => 'glyphicon glyphicon-ok'
            ];

        } catch (PDOException $e) {
            return [
                'estado'  => false,
                'mensaje' => "Error buscando Matriculas activas",
                'clase'   => 'alert alert-danger',
                'icono'   => 'glyphicon glyphicon-remove'
            ];
        }
    }

    public function obtenerColegiadosAutocompletar($tipo) {
        try {
            $db = Database::getConnection();

            if ($tipo == 'activos') {
                $activos = "INNER JOIN tipomovimiento tm ON(tm.Id = c.Estado AND tm.Estado NOT IN('F', 'J'))";
            } else {
                $activos = "";
            }

            $sql = "SELECT c.Id AS idColegiado, c.Matricula AS matricula, p.Apellido AS apellido,
                           p.Nombres AS nombres, p.NumeroDocumento AS numeroDocumento
                    FROM colegiado c
                    INNER JOIN persona p ON(p.Id = c.IdPersona)
                    " . $activos . "
                    ORDER BY p.Apellido, p.Nombres";

            $stmt = $db->prepare($sql);
            $stmt->execute();
            $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $rows = [];
            foreach ($datos as $r) {
                $rows[] = [
                    'id'     => $r['idColegiado'],
                    'nombre' => $r['matricula'] . ' - ' . trim($r['apellido']) . " " . trim($r['nombres']) . " (DNI " . $r['numeroDocumento'] . ")"
                ];
            }

            return [
                'estado'  => true,
                'mensaje' => "OK",
                'datos'   => $rows,
                'clase'   => 'alert alert-success',
                'icono'   => 'glyphicon glyphicon-ok'
            ];

        } catch (PDOException $e) {
            return [
                'estado'  => false,
                'mensaje' => "Error buscando colegiados",
                'clase'   => 'alert alert-danger',
                'icono'   => 'glyphicon glyphicon-remove'
            ];
        }
    }

    public function agregarColegiado($tomo, $folio, $fechaMatriculacion, $matricula, $estado, $apellido, $nombres, $sexo, $tipoDocumento,
            $numeroDocumento, $fechaNacimiento, $idPaises, $matriculaNacional, $distritoOrigen, $calle, $numero, $piso, $depto,
            $lateral, $idLocalidad, $codigoPostal, $telefonoFijo, $telefonoMovil, $mail, $idTipoTitulo, $fechaTitulo, $idUniversidad, $tituloDigital) {

        try {
            $db = Database::getConnection();
            $db->beginTransaction();

            $sql = "INSERT INTO persona
                    (Apellido, Nombres, Sexo, TipoDocumento, NumeroDocumento, FechaNacimiento, IdPaises, FechaCarga)
                    VALUES (:apellido, :nombres, :sexo, :tipoDocumento, :numeroDocumento, :fechaNacimiento, :idPaises, DATE(NOW()))";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':apellido'        => $apellido,
                ':nombres'         => $nombres,
                ':sexo'            => $sexo,
                ':tipoDocumento'   => $tipoDocumento,
                ':numeroDocumento' => $numeroDocumento,
                ':fechaNacimiento' => $fechaNacimiento,
                ':idPaises'        => $idPaises
            ]);

            $idPersona = $db->lastInsertId();

            $sql = "INSERT INTO log_tabla
                    (Tabla, IdTabla, Fecha, TipoMovimiento, IdUsuario)
                    VALUES ('persona', :idTabla, now(), 'alta', :idUsuario)";
            $stmt = $db->prepare($sql);
            $stmt->execute([':idTabla' => $idPersona, ':idUsuario' => $_SESSION['user_id']]);

            $resultado = [
                'estado'    => true,
                'idPersona' => $idPersona,
                'mensaje'   => 'LA PERSONA HA SIDO AGREGADA',
                'clase'     => 'alert alert-success',
                'icono'     => 'glyphicon glyphicon-ok'
            ];

            if ($resultado['estado']) {
                $hashColegiado = hashData($matricula . $idPersona);
                $sql = "INSERT INTO colegiado
                        (Matricula, Tomo, Folio, FechaMatriculacion, Estado, MatriculaNacional, FechaCarga, IdPersona, DistritoOrigen, Hash)
                        VALUES (:matricula, :tomo, :folio, :fechaMatriculacion, :estado, :matriculaNacional, DATE(NOW()), :idPersona, :distritoOrigen, :hash)";
                $stmt = $db->prepare($sql);
                $stmt->execute([
                    ':matricula'         => $matricula,
                    ':tomo'              => $tomo,
                    ':folio'             => $folio,
                    ':fechaMatriculacion' => $fechaMatriculacion,
                    ':estado'            => $estado,
                    ':matriculaNacional' => $matriculaNacional,
                    ':idPersona'         => $idPersona,
                    ':distritoOrigen'    => $distritoOrigen,
                    ':hash'              => $hashColegiado
                ]);

                $idColegiado = $db->lastInsertId();

                $sql = "INSERT INTO log_tabla
                        (Tabla, IdTabla, Fecha, TipoMovimiento, IdUsuario)
                        VALUES ('colegiado', :idTabla, now(), 'alta', :idUsuario)";
                $stmt = $db->prepare($sql);
                $stmt->execute([':idTabla' => $idColegiado, ':idUsuario' => $_SESSION['user_id']]);

                if ($resultado['estado']) {
                    $calle = mb_strtoupper($calle, 'UTF-8');
                    if (isset($lateral)) {
                        $lateral = mb_strtoupper($lateral, 'UTF-8');
                    }
                    $sql = "INSERT INTO colegiadodomicilioreal
                            (idColegiado, Calle, Lateral, Numero, Piso, Departamento, idLocalidad, CodigoPostal, idEstado, FechaCarga, idUsuario, idOrigen)
                            VALUE (:idColegiado, :calle, :lateral, :numero, :piso, :depto, :idLocalidad, :codigoPostal, 1, date(now()), :idUsuario, 2)";
                    $stmt = $db->prepare($sql);
                    $stmt->execute([
                        ':idColegiado'  => $idColegiado,
                        ':calle'        => $calle,
                        ':lateral'      => $lateral,
                        ':numero'       => $numero,
                        ':piso'         => $piso,
                        ':depto'        => $depto,
                        ':idLocalidad'  => $idLocalidad,
                        ':codigoPostal' => $codigoPostal,
                        ':idUsuario'    => $_SESSION['user_id']
                    ]);

                    $idColegiadoDomicilio = $db->lastInsertId();

                    $sql = "INSERT INTO log_tabla
                            (Tabla, IdTabla, Fecha, TipoMovimiento, IdUsuario)
                            VALUES ('colegiadodomicilioreal', :idTabla, now(), 'alta', :idUsuario)";
                    $stmt = $db->prepare($sql);
                    $stmt->execute([':idTabla' => $idColegiadoDomicilio, ':idUsuario' => $_SESSION['user_id']]);
                    $resultado['idColegiadoDomicilio'] = $idColegiadoDomicilio;
                }

                if ($resultado['estado']) {
                    $sql = "INSERT INTO colegiadocontacto
                            (IdColegiado, TelefonoFijo, TelefonoMovil, CorreoElectronico, IdEstado, FechaCarga, IdUsuario, IdOrigen)
                            VALUE (:idColegiado, :telefonoFijo, :telefonoMovil, :mail, 1, date(now()), :idUsuario, 2)";
                    $stmt = $db->prepare($sql);
                    $stmt->execute([
                        ':idColegiado'  => $idColegiado,
                        ':telefonoFijo' => $telefonoFijo,
                        ':telefonoMovil' => $telefonoMovil,
                        ':mail'         => $mail,
                        ':idUsuario'    => $_SESSION['user_id']
                    ]);

                    $idColegiadoContacto = $db->lastInsertId();

                    $sql = "INSERT INTO log_tabla
                            (Tabla, IdTabla, Fecha, TipoMovimiento, IdUsuario)
                            VALUES ('colegiadocontacto', :idTabla, now(), 'alta', :idUsuario)";
                    $stmt = $db->prepare($sql);
                    $stmt->execute([':idTabla' => $idColegiadoContacto, ':idUsuario' => $_SESSION['user_id']]);
                    $resultado['idColegiadoContacto'] = $idColegiadoContacto;
                }

                if ($resultado['estado']) {
                    $sql = "INSERT INTO colegiadotitulo
                            (IdColegiado, IdTipoTitulo, IdUniversidad, FechaTitulo, FechaCarga, IdUsuario, Digital)
                            VALUE (:idColegiado, :idTipoTitulo, :idUniversidad, :fechaTitulo, date(now()), :idUsuario, :tituloDigital)";
                    $stmt = $db->prepare($sql);
                    $stmt->execute([
                        ':idColegiado'  => $idColegiado,
                        ':idTipoTitulo' => $idTipoTitulo,
                        ':idUniversidad' => $idUniversidad,
                        ':fechaTitulo'  => $fechaTitulo,
                        ':idUsuario'    => $_SESSION['user_id'],
                        ':tituloDigital' => $tituloDigital
                    ]);

                    $idColegiadoTitulo = $db->lastInsertId();

                    $sql = "INSERT INTO log_tabla
                            (Tabla, IdTabla, Fecha, TipoMovimiento, IdUsuario)
                            VALUES ('colegiadotitulo', :idTabla, now(), 'alta', :idUsuario)";
                    $stmt = $db->prepare($sql);
                    $stmt->execute([':idTabla' => $idColegiadoTitulo, ':idUsuario' => $_SESSION['user_id']]);
                    $resultado['idColegiadoTitulo'] = $idColegiadoTitulo;
                }
            }

            if ($resultado['estado']) {
                $resultado['mensaje'] = 'EL COLEGIADO HA SIDO AGREGADO CORRECTAMENTE';
                $resultado['clase']   = 'alert alert-success';
                $resultado['icono']   = 'glyphicon glyphicon-ok';
                $resultado['idColegiado'] = $idColegiado;
                $db->commit();
                return $resultado;
            } else {
                $db->rollBack();
                return $resultado;
            }

        } catch (PDOException $e) {
            $db->rollBack();
            return [
                'estado'  => false,
                'mensaje' => "Error: " . $e->getMessage(),
                'clase'   => 'alert alert-danger',
                'icono'   => 'glyphicon glyphicon-remove'
            ];
        }
    }

    public function actualizarEstado($idColegiado, $idTipoMovimiento) {
        try {
            $db = Database::getConnection();

            $sql = "UPDATE colegiado
                    SET Estado = :estado
                    WHERE Id = :idColegiado";
            $stmt = $db->prepare($sql);
            $stmt->execute([':estado' => $idTipoMovimiento, ':idColegiado' => $idColegiado]);

            $sql = "INSERT INTO log_tabla
                    (Tabla, IdTabla, Fecha, TipoMovimiento, IdUsuario)
                    VALUES ('colegiado', :idTabla, now(), 'modificaEstado', :idUsuario)";
            $stmt = $db->prepare($sql);
            $stmt->execute([':idTabla' => $idColegiado, ':idUsuario' => $_SESSION['user_id']]);

            return [
                'estado'  => true,
                'mensaje' => 'ESTADO ACTUALIZADO',
                'clase'   => 'alert alert-success',
                'icono'   => 'glyphicon glyphicon-ok'
            ];

        } catch (PDOException $e) {
            return [
                'estado'  => false,
                'mensaje' => "Error: " . $e->getMessage(),
                'clase'   => 'alert alert-danger',
                'icono'   => 'glyphicon glyphicon-remove'
            ];
        }
    }

    public function cantidadColegiadosPorAntiguedad($fechaCalculo) {
        try {
            $db = Database::getConnection();
            $sql = "(SELECT 1 AS antiguedad, COUNT(c.Id) AS cantidad
                    FROM colegiado c
                    INNER JOIN colegiadotitulo ct ON ct.IdColegiado = c.Id
                    WHERE c.Estado IN(0, 1, 5, 10)
                    AND ct.FechaTitulo >= :fechaCalculo1)

                    UNION

                    (SELECT 2 AS antiguedad, COUNT(c.Id) AS cantidad
                    FROM colegiado c
                    INNER JOIN colegiadotitulo ct ON ct.IdColegiado = c.Id
                    WHERE c.Estado IN(0, 1, 5, 10)
                    AND ct.FechaTitulo < :fechaCalculo2)";

            $stmt = $db->prepare($sql);
            $stmt->execute([':fechaCalculo1' => $fechaCalculo, ':fechaCalculo2' => $fechaCalculo]);
            $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $rows = [];
            foreach ($datos as $r) {
                $rows[] = [
                    'antiguedad' => $r['antiguedad'],
                    'cantidad'   => $r['cantidad']
                ];
            }

            return [
                'estado'  => true,
                'mensaje' => "OK",
                'datos'   => $rows
            ];

        } catch (PDOException $e) {
            return [
                'estado'  => false,
                'mensaje' => "Error buscando colegiados"
            ];
        }
    }

    public function obtenerTitulosPorColegiado($idColegiado) {
        try {
            $db = Database::getConnection();
            $sql = "SELECT ct.IdColegiadoTitulo AS idColegiadoTitulo, ct.FechaTitulo AS fechaTitulo,
                           tt.Nombre AS tipoTitulo, u.Nombre AS universidad,
                           ct.IdTipoTitulo AS idTipoTitulo, ct.IdUniversidad AS idUniversidad,
                           ct.Digital AS digital, p.Pais AS nombrePais
                    FROM colegiadotitulo ct
                    INNER JOIN tipotitulo tt ON tt.IdTipoTitulo = ct.IdTipoTitulo
                    INNER JOIN universidad u ON u.Id = ct.IdUniversidad
                    LEFT JOIN paises p ON p.Id = u.IdPaises
                    WHERE ct.IdColegiado = :idColegiado LIMIT 1";

            $stmt = $db->prepare($sql);
            $stmt->bindParam(':idColegiado', $idColegiado, PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row) {
                return [
                    'estado'  => true,
                    'datos'   => [
                        'idColegiadoTitulo' => $row['idColegiadoTitulo'],
                        'fechaTitulo'       => $row['fechaTitulo'],
                        'tipoTitulo'        => $row['tipoTitulo'],
                        'universidad'       => $row['universidad'],
                        'idTipoTitulo'      => $row['idTipoTitulo'],
                        'idUniversidad'     => $row['idUniversidad'],
                        'digital'           => $row['digital'],
                        'nombrePais'        => $row['nombrePais']
                    ],
                    'mensaje' => "OK",
                    'clase'   => 'alert alert-success',
                    'icono'   => 'glyphicon glyphicon-ok'
                ];
            }

            return [
                'estado'  => false,
                'datos'   => null,
                'mensaje' => "No hay título para el colegiado " . $idColegiado,
                'clase'   => 'alert alert-info',
                'icono'   => 'glyphicon glyphicon-exclamation-sign'
            ];

        } catch (PDOException $e) {
            return [
                'estado'  => false,
                'mensaje' => "Error buscando título",
                'clase'   => 'alert alert-danger',
                'icono'   => 'glyphicon glyphicon-remove'
            ];
        }
    }

    public function obtenerTitulosPorIdColegiadoTitulo($idColegiadoTitulo) {
        try {
            $db = Database::getConnection();
            $sql = "SELECT ct.IdColegiado AS idColegiado, ct.FechaTitulo AS fechaTitulo,
                           tt.Nombre AS tipoTitulo, u.Nombre AS universidad,
                           ct.IdTipoTitulo AS idTipoTitulo, ct.IdUniversidad AS idUniversidad,
                           ct.Digital AS tituloDigital
                    FROM colegiadotitulo ct
                    INNER JOIN tipotitulo tt ON(tt.IdTipoTitulo = ct.IdTipoTitulo)
                    INNER JOIN universidad u ON(u.Id = ct.IdUniversidad)
                    WHERE ct.IdColegiadoTitulo = :idColegiadoTitulo";

            $stmt = $db->prepare($sql);
            $stmt->bindParam(':idColegiadoTitulo', $idColegiadoTitulo, PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row) {
                return [
                    'estado'  => true,
                    'datos'   => [
                        'idColegiadoTitulo' => $idColegiadoTitulo,
                        'idColegiado'       => $row['idColegiado'],
                        'fechaTitulo'       => $row['fechaTitulo'],
                        'tipoTitulo'        => $row['tipoTitulo'],
                        'universidad'       => $row['universidad'],
                        'idTipoTitulo'      => $row['idTipoTitulo'],
                        'idUniversidad'     => $row['idUniversidad'],
                        'tituloDigital'     => $row['tituloDigital']
                    ],
                    'mensaje' => "OK",
                    'clase'   => 'alert alert-success',
                    'icono'   => 'glyphicon glyphicon-ok'
                ];
            }

            return [
                'estado'  => true,
                'datos'   => null,
                'mensaje' => "No hay colegiadoTitulo " . $idColegiadoTitulo,
                'clase'   => 'alert alert-info',
                'icono'   => 'glyphicon glyphicon-exclamation-sign'
            ];

        } catch (PDOException $e) {
            return [
                'estado'  => false,
                'mensaje' => "Error buscando colegiado",
                'clase'   => 'alert alert-danger',
                'icono'   => 'glyphicon glyphicon-remove'
            ];
        }
    }

    public function generarDeudaAnual_anterior2020($idColegiado, $fechaTitulo, $estado) {
        $periodoActual = $_SESSION['periodoActual'];
        $fechaDesde = $periodoActual . '-06-30';
        $fechaHasta = ($periodoActual + 1) . '-04-30';
        $fechaTituloMinima = $periodoActual . '-05-31';

        if (date('Y-m-d') > $fechaDesde && date('Y-m-d') <= $fechaHasta) {
            try {
                $db = Database::getConnection();
                $db->beginTransaction();

                $resultado = ['estado' => true];

                if ($fechaTitulo > $fechaTituloMinima) {
                    $antiguedad = 1;
                } else {
                    $antiguedad = calcular_antiguedad($fechaTitulo, $fechaTituloMinima);
                }

                $sql = "SELECT Id AS idValorAnualColegiacion, Valor AS importeTotal,
                               Cuotas AS cuotas, PagoTotal AS pagoTotal, VtoPagoTotal AS vtoPagoTotal
                        FROM valoranualcolegiacion WHERE Periodo = :periodo AND Antiguedad = :antiguedad";
                $stmt = $db->prepare($sql);
                $stmt->execute([':periodo' => $periodoActual, ':antiguedad' => $antiguedad]);
                $rowVAC = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($rowVAC) {
                    $idValorAnualColegiacion = $rowVAC['idValorAnualColegiacion'];
                    $importeTotal = $rowVAC['importeTotal'];
                    $cuotas = $rowVAC['cuotas'];
                    $pagoTotal = $rowVAC['pagoTotal'];
                    $vtoPagoTotal = $rowVAC['vtoPagoTotal'];

                    $sql = "INSERT INTO colegiadodeudaanual
                            (IdColegiado, Periodo, Importe, Cuotas, Antiguedad, EstadoMatricular, FechaCreacion, Estado)
                            VALUE (:idColegiado, :periodo, :importe, :cuotas, :antiguedad, :estadoMatricular, date(now()), 'A')";
                    $stmt = $db->prepare($sql);
                    $stmt->execute([
                        ':idColegiado'      => $idColegiado,
                        ':periodo'          => $periodoActual,
                        ':importe'          => $importeTotal,
                        ':cuotas'           => $cuotas,
                        ':antiguedad'       => $antiguedad,
                        ':estadoMatricular' => $estado
                    ]);

                    $idColegiadoDeudaAnual = $db->lastInsertId();

                    $sql = "INSERT INTO log_tabla
                            (Tabla, IdTabla, Fecha, TipoMovimiento, IdUsuario)
                            VALUES ('colegiadodeudaanual', :idTabla, now(), 'alta', :idUsuario)";
                    $stmt = $db->prepare($sql);
                    $stmt->execute([':idTabla' => $idColegiadoDeudaAnual, ':idUsuario' => $_SESSION['user_id']]);
                    $resultado['idColegiadoDeudaAnual'] = $idColegiadoDeudaAnual;

                    $sql = "SELECT Id AS idValorCuotaColegiacion, Cuota AS cuota, ValorColegiacion AS importe,
                                   FechaVencimiento AS fechaVencimiento, SegundoVencimiento AS segundoVencimiento,
                                   Recargo AS recargo
                            FROM valorcuotacolegiacion WHERE IdValorAnualColegiacion = :idValorAnualColegiacion";
                    $stmt = $db->prepare($sql);
                    $stmt->execute([':idValorAnualColegiacion' => $idValorAnualColegiacion]);
                    $cuotaRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    if (count($cuotaRows) > 0) {
                        foreach ($cuotaRows as $cuotaRow) {
                            if (!$resultado['estado']) break;

                            $primerVencimiento = $cuotaRow['fechaVencimiento'];
                            $segundoVencimiento = $cuotaRow['segundoVencimiento'];
                            $recargo = $cuotaRow['recargo'];
                            $importe = $cuotaRow['importe'];

                            $estadoCuota = 1;
                            if ($primerVencimiento <= sumarRestarSobreFecha(date('Y-m-d'), 10, 'day', '+')) {
                                $estadoCuota = 5;
                            }

                            if (!isset($recargo)) {
                                $recargo = $importe;
                            }

                            $sql1 = "INSERT INTO colegiadodeudaanualcuotas
                                    (IdColegiadoDeudaAnual, Cuota, Importe, FechaVencimiento, Recargo, SegundoVencimiento, Estado)
                                    VALUE (:idColegiadoDeudaAnual, :cuota, :importe, :fechaVencimiento, :recargo, :segundoVencimiento, :estadoCuota)";
                            $stmt1 = $db->prepare($sql1);
                            $stmt1->execute([
                                ':idColegiadoDeudaAnual' => $idColegiadoDeudaAnual,
                                ':cuota'                 => $cuotaRow['cuota'],
                                ':importe'               => $importe,
                                ':fechaVencimiento'      => $primerVencimiento,
                                ':recargo'               => $recargo,
                                ':segundoVencimiento'    => $segundoVencimiento,
                                ':estadoCuota'           => $estadoCuota
                            ]);
                        }

                        if ($resultado['estado'] && $vtoPagoTotal > date('Y-m-d')) {
                            $sql1 = "INSERT INTO colegiadodeudaanualtotal
                                    (IdColegiadoDeudaAnual, Importe, FechaVencimiento, IdEstado)
                                    VALUE (:idColegiadoDeudaAnual, :importe, :fechaVencimiento, :idEstado)";
                            $stmt1 = $db->prepare($sql1);
                            $stmt1->execute([
                                ':idColegiadoDeudaAnual' => $idColegiadoDeudaAnual,
                                ':importe'               => $pagoTotal,
                                ':fechaVencimiento'      => $vtoPagoTotal,
                                ':idEstado'              => $estadoCuota
                            ]);
                        }
                    }
                } else {
                    $resultado['estado']  = true;
                    $resultado['mensaje'] = "NO SE GENERO LA DEUDA ANUAL, NO HAY PERIODO PARA LIQUIDAR";
                    $resultado['clase']   = 'alert alert-info';
                    $resultado['icono']   = 'glyphicon glyphicon-info-sign';
                }

                if ($resultado['estado']) {
                    $resultado['estado']               = true;
                    $resultado['mensaje']              = 'SE GENERO LA DEUDA ANUAL CORRECTAMENTE';
                    $resultado['clase']                = 'alert alert-success';
                    $resultado['icono']                = 'glyphicon glyphicon-ok';
                    $resultado['idColegiadoDeudaAnual'] = $idColegiadoDeudaAnual;
                    $db->commit();
                    return $resultado;
                } else {
                    $db->rollBack();
                    return $resultado;
                }

            } catch (PDOException $e) {
                $db->rollBack();
                return [
                    'estado'  => false,
                    'mensaje' => "Error: " . $e->getMessage(),
                    'clase'   => 'alert alert-danger',
                    'icono'   => 'glyphicon glyphicon-remove'
                ];
            }
        } else {
            return [
                'estado'  => false,
                'mensaje' => 'NO SE GENERA DEUDA, ESTA FUERA DEL PERIODO VIGENTE',
                'clase'   => 'alert alert-info',
                'icono'   => 'glyphicon glyphicon-info-sign'
            ];
        }
    }

    public function agregarColegiadoTitulo($idColegiado, $idTipoTitulo, $fechaTitulo, $idUniversidad) {
        try {
            $db = Database::getConnection();
            $sql = "INSERT INTO colegiadotitulo
                    (IdColegiado, IdTipoTitulo, IdUniversidad, FechaTitulo, FechaCarga, IdUsuario)
                    VALUE (:idColegiado, :idTipoTitulo, :idUniversidad, :fechaTitulo, date(now()), :idUsuario)";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':idColegiado'  => $idColegiado,
                ':idTipoTitulo' => $idTipoTitulo,
                ':idUniversidad' => $idUniversidad,
                ':fechaTitulo'  => $fechaTitulo,
                ':idUsuario'    => $_SESSION['user_id']
            ]);

            $db->lastInsertId();

            return [
                'estado'  => true,
                'mensaje' => "COLEGIADO TITULO SE AGREGO CON EXITO",
                'clase'   => 'alert alert-success',
                'icono'   => 'glyphicon glyphicon-ok'
            ];

        } catch (PDOException $e) {
            return [
                'estado'  => false,
                'mensaje' => "ERROR AL AGREGAR COLEGIADO TITULO",
                'clase'   => 'alert alert-danger',
                'icono'   => 'glyphicon glyphicon-remove'
            ];
        }
    }

    public function obtenerDetalleTipoEstado($tipoEstado) {
        switch ($tipoEstado) {
            case 'A':
                $estado = 'Activo';
                break;
            case 'C':
                $estado = 'Baja';
                break;
            case 'I':
                $estado = 'Inscripto al Distrito I';
                break;
            default:
                $estado = '';
                break;
        }
        return $estado;
    }

    public function obtenerFirmaPorCargo($idCargo) {
        try {
            $db = Database::getConnection();
            $sql = "SELECT cc.Nombre AS nombreCargo, c.Matricula AS matricula,
                           p.Apellido AS apellido, p.Nombres AS nombre
                    FROM colegiadocargo cc2
                    INNER JOIN cargocolegio cc ON(cc.IdCargo = cc2.IdCargoColegio)
                    INNER JOIN colegiado c ON(c.Id = cc2.IdColegiado)
                    INNER JOIN persona p ON(p.Id = c.IdPersona)
                    WHERE cc2.IdCargoColegio = :idCargo
                    AND DATE(NOW()) BETWEEN cc2.FechaMesaDesde AND cc2.FechaMesaHasta
                    ORDER BY cc2.IdCargoColegio DESC
                    LIMIT 1";

            $stmt = $db->prepare($sql);
            $stmt->bindParam(':idCargo', $idCargo, PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row) {
                return [
                    'estado'  => true,
                    'datos'   => [
                        'nombreCargo' => $row['nombreCargo'],
                        'matricula'   => $row['matricula'],
                        'apellido'    => $row['apellido'],
                        'nombre'      => $row['nombre']
                    ],
                    'mensaje' => "OK",
                    'clase'   => 'alert alert-success',
                    'icono'   => 'glyphicon glyphicon-ok'
                ];
            }

            return [
                'estado'  => true,
                'datos'   => null,
                'mensaje' => "No hay Secretario General",
                'clase'   => 'alert alert-info',
                'icono'   => 'glyphicon glyphicon-exclamation-sign'
            ];

        } catch (PDOException $e) {
            return [
                'estado'  => false,
                'mensaje' => "Error buscando Secretario General",
                'clase'   => 'alert alert-danger',
                'icono'   => 'glyphicon glyphicon-remove'
            ];
        }
    }

    public function obtenerNuevoTomoFolioMatricula() {
        try {
            $db = Database::getConnection();
            $sql = "SELECT colegiado.Tomo AS tomo, colegiado.Folio AS folio, colegiado.Matricula AS matricula
                    FROM colegiado
                    WHERE colegiado.DistritoOrigen = 1
                    ORDER BY colegiado.Matricula DESC
                    LIMIT 1";

            $stmt = $db->prepare($sql);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row) {
                $matricula = $row['matricula'];
                $matricula++;
                if ($matricula > 199999) {
                    $matricula = 1010000;
                }

                return [
                    'estado'  => true,
                    'datos'   => [
                        'tomo'      => $row['tomo'],
                        'folio'     => $row['folio'],
                        'matricula' => $matricula
                    ],
                    'mensaje' => "OK",
                    'clase'   => 'alert alert-success',
                    'icono'   => 'glyphicon glyphicon-ok'
                ];
            }

            return [
                'estado'  => true,
                'datos'   => null,
                'mensaje' => "No se encontro ultimo tomo y folio",
                'clase'   => 'alert alert-info',
                'icono'   => 'glyphicon glyphicon-exclamation-sign'
            ];

        } catch (PDOException $e) {
            return [
                'estado'  => false,
                'mensaje' => "Error buscando ultimo tomo y folio",
                'clase'   => 'alert alert-danger',
                'icono'   => 'glyphicon glyphicon-remove'
            ];
        }
    }

    public function obtenerNuevoTomoFolioOtroDistrito() {
        try {
            $db = Database::getConnection();
            $sql = "SELECT colegiado.Tomo AS tomo, colegiado.Folio AS folio
                    FROM colegiado
                    WHERE colegiado.DistritoOrigen != 1
                    ORDER BY colegiado.Tomo DESC, colegiado.Folio DESC
                    LIMIT 1";

            $stmt = $db->prepare($sql);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row) {
                return [
                    'estado'  => true,
                    'datos'   => [
                        'tomo'  => $row['tomo'],
                        'folio' => $row['folio']
                    ],
                    'mensaje' => "OK",
                    'clase'   => 'alert alert-success',
                    'icono'   => 'glyphicon glyphicon-ok'
                ];
            }

            return [
                'estado'  => true,
                'datos'   => null,
                'mensaje' => "No se encontro ultimo tomo y folio",
                'clase'   => 'alert alert-info',
                'icono'   => 'glyphicon glyphicon-exclamation-sign'
            ];

        } catch (PDOException $e) {
            return [
                'estado'  => false,
                'mensaje' => "Error buscando ultimo tomo y folio",
                'clase'   => 'alert alert-danger',
                'icono'   => 'glyphicon glyphicon-remove'
            ];
        }
    }

    public function matriculaExiste($matricula) {
        try {
            $db = Database::getConnection();
            $sql = "SELECT COUNT(Id) AS cantidad FROM colegiado WHERE Matricula = :matricula";

            $stmt = $db->prepare($sql);
            $stmt->bindParam(':matricula', $matricula, PDO::PARAM_STR);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $cantidad = $row['cantidad'];

            if ($cantidad > 0) {
                return [
                    'estado'  => true,
                    'mensaje' => "Numero de Matricula YA EXISTE EN LA BASE DE DATOS"
                ];
            }

            return [
                'estado'  => false,
                'mensaje' => "No existe Numero de Matricula"
            ];

        } catch (PDOException $e) {
            return [
                'estado'  => false,
                'mensaje' => "Error al buscar el Numero de Matricula"
            ];
        }
    }

    public function obtenerColegiadoNota($idColegiado) {
        try {
            $db = Database::getConnection();
            $sql = "SELECT Id AS idColegiadoNota, Nota AS nota, IdUsuario AS idUsuario, FechaCarga AS fechaCarga
                    FROM colegiadonota WHERE colegiadonota.IdColegiado = :idColegiado";

            $stmt = $db->prepare($sql);
            $stmt->bindParam(':idColegiado', $idColegiado, PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row) {
                return [
                    'estado'  => true,
                    'datos'   => [
                        'idColegiadoNota' => $row['idColegiadoNota'],
                        'nota'            => $row['nota'],
                        'idUsuario'       => $row['idUsuario'],
                        'fechaCarga'      => $row['fechaCarga']
                    ],
                    'mensaje' => "OK",
                    'clase'   => 'alert alert-success',
                    'icono'   => 'glyphicon glyphicon-ok'
                ];
            }

            return [
                'estado'  => true,
                'datos'   => [],
                'mensaje' => "No hay nota " . $idColegiado,
                'clase'   => 'alert alert-info',
                'icono'   => 'glyphicon glyphicon-exclamation-sign'
            ];

        } catch (PDOException $e) {
            return [
                'estado'  => false,
                'mensaje' => "Error buscando nota",
                'clase'   => 'alert alert-danger',
                'icono'   => 'glyphicon glyphicon-remove'
            ];
        }
    }

    public function agregarColegiadoNota($idColegiado, $nota) {
        try {
            $db = Database::getConnection();
            $sql = "INSERT INTO colegiadonota
                    (IdColegiado, Nota, IdUsuario, FechaCarga)
                    VALUES (:idColegiado, :nota, :idUsuario, NOW())";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':idColegiado' => $idColegiado,
                ':nota'        => $nota,
                ':idUsuario'   => $_SESSION['user_id']
            ]);

            $db->lastInsertId();

            return [
                'estado'  => true,
                'mensaje' => "COLEGIADO NOTA SE AGREGO CON EXITO",
                'clase'   => 'alert alert-success',
                'icono'   => 'glyphicon glyphicon-ok'
            ];

        } catch (PDOException $e) {
            return [
                'estado'  => false,
                'mensaje' => "ERROR AL AGREGAR COLEGIADO NOTA",
                'clase'   => 'alert alert-danger',
                'icono'   => 'glyphicon glyphicon-remove'
            ];
        }
    }

    public function editarColegiadoNota($idColegiadoNota, $nota) {
        try {
            $db = Database::getConnection();
            $sql = "UPDATE colegiadonota
                    SET Nota = :nota, IdUsuario = :idUsuario, FechaCarga = NOW()
                    WHERE Id = :idColegiadoNota";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':nota'            => $nota,
                ':idUsuario'       => $_SESSION['user_id'],
                ':idColegiadoNota' => $idColegiadoNota
            ]);

            return [
                'estado'  => true,
                'mensaje' => "COLEGIADO NOTA SE AGREGO CON EXITO",
                'clase'   => 'alert alert-success',
                'icono'   => 'glyphicon glyphicon-ok'
            ];

        } catch (PDOException $e) {
            return [
                'estado'  => false,
                'mensaje' => "ERROR AL AGREGAR COLEGIADO NOTA",
                'clase'   => 'alert alert-danger',
                'icono'   => 'glyphicon glyphicon-remove'
            ];
        }
    }

    public function obtenerColegiadosPaginacion($inicio, $limite, $buscar, $orden) {
        try {
            $db = Database::getConnection();

            if (isset($buscar)) {
                if (is_numeric($buscar)) {
                    $conBusqueda = "WHERE (colegiado.Matricula = " . $buscar . " or persona.NumeroDocumento = " . $buscar . ")";
                } else {
                    $conBusqueda = "WHERE (persona.Apellido like '" . $buscar . "%')";
                }
            } else {
                $conBusqueda = " ";
            }

            $ordenado = "ORDER BY colegiado.Matricula";
            if (isset($orden) && $orden == 'A') {
                $ordenado = "ORDER BY persona.Apellido, persona.Nombres";
            }

            $sql = "SELECT colegiado.Id AS idColegiado, colegiado.Matricula AS matricula,
                           persona.Apellido AS apellido, persona.Nombres AS nombres,
                           persona.NumeroDocumento AS numeroDocumento, tm.Detalle AS tipoMovimiento,
                           cc.TelefonoFijo AS telefono1, cc.TelefonoMovil AS telefono2, cc.CorreoElectronico AS mail
                    FROM colegiado
                    INNER JOIN persona ON(persona.Id = colegiado.IdPersona)
                    INNER JOIN tipomovimiento tm ON(tm.Id = colegiado.Estado)
                    INNER JOIN colegiadocontacto cc ON (cc.IdColegiado = colegiado.Id AND cc.IdEstado = 1)
                    " . $conBusqueda . " " . $ordenado . "
                    LIMIT " . $inicio . ", " . $limite . "";

            $stmt = $db->prepare($sql);
            $stmt->execute();
            $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $rows = [];
            foreach ($datos as $r) {
                $rows[] = [
                    'idColegiado'     => $r['idColegiado'],
                    'matricula'       => $r['matricula'],
                    'apellidoNombre'  => trim($r['apellido']) . ', ' . trim($r['nombres']),
                    'numeroDocumento' => $r['numeroDocumento'],
                    'tipoMovimiento'  => $r['tipoMovimiento'],
                    'telefono1'       => $r['telefono1'],
                    'telefono2'       => $r['telefono2'],
                    'mail'            => $r['mail']
                ];
            }

            return [
                'estado'  => true,
                'mensaje' => "OK",
                'datos'   => $rows
            ];

        } catch (PDOException $e) {
            return [
                'estado'  => false,
                'mensaje' => "Error buscando colegiados"
            ];
        }
    }

    public function obtenerCantidadMatriculasPaginacion($buscar) {
        try {
            $db = Database::getConnection();

            if (isset($buscar)) {
                if (is_numeric($buscar)) {
                    $conBusqueda = "WHERE (colegiado.Matricula = " . $buscar . " or persona.NumeroDocumento = " . $buscar . ")";
                } else {
                    $conBusqueda = "WHERE (persona.Apellido like '" . $buscar . "%')";
                }
            } else {
                $conBusqueda = " ";
            }

            $sql = "SELECT COUNT(colegiado.Id) AS cantidad
                    FROM colegiado
                    INNER JOIN persona ON(persona.Id = colegiado.IdPersona) " . $conBusqueda;

            $stmt = $db->prepare($sql);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            return [
                'estado'   => true,
                'mensaje'  => 'OK',
                'cantidad' => $row['cantidad']
            ];

        } catch (PDOException $e) {
            return [
                'estado'  => false,
                'mensaje' => "Error buscando reclamos"
            ];
        }
    }

    public function obtenerColegiadoParaLiquidacion($periodo, $fechaCalculoAntiguedad) {
        try {
            $db = Database::getConnection();
            $sql = "SELECT c.Id AS idColegiado, c.Matricula AS matricula, c.Estado AS estado,
                           ct.FechaTitulo AS fechaTitulo,
                           TIMESTAMPDIFF(YEAR, ct.FechaTitulo, :fechaCalculo) AS antiguedad
                    FROM colegiado c
                    INNER JOIN colegiadotitulo ct ON ct.IdColegiado = c.Id
                    LEFT JOIN colegiadodeudaanual cda ON (cda.IdColegiado = c.Id AND cda.Periodo = :periodo)
                    WHERE c.Estado in(0, 1, 5, 10) AND cda.Id IS NULL
                    ORDER BY ct.FechaTitulo";

            $stmt = $db->prepare($sql);
            $stmt->execute([':fechaCalculo' => $fechaCalculoAntiguedad, ':periodo' => $periodo]);
            $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $rows = [];
            foreach ($datos as $r) {
                $antiguedad = $r['antiguedad'];
                if ($antiguedad < 5) {
                    $antiguedad = 1;
                } else {
                    $antiguedad = 2;
                }
                $rows[] = [
                    'idColegiado' => $r['idColegiado'],
                    'matricula'   => $r['matricula'],
                    'estado'      => $r['estado'],
                    'fechaTitulo' => $r['fechaTitulo'],
                    'antiguedad'  => $antiguedad
                ];
            }

            return [
                'estado'  => true,
                'mensaje' => "OK",
                'datos'   => $rows
            ];

        } catch (PDOException $e) {
            return [
                'estado'  => false,
                'mensaje' => "Error buscando colegiados"
            ];
        }
    }

    public function obtenerMatriculaPorIdColegiado($idColegiado) {
        try {
            $db = Database::getConnection();
            $sql = "SELECT colegiado.Tomo AS tomo, colegiado.Folio AS folio,
                           colegiado.FechaMatriculacion AS fechaMatriculacion,
                           colegiado.MatriculaNacional AS matriculaNacional
                    FROM colegiado
                    WHERE Id = :idColegiado";

            $stmt = $db->prepare($sql);
            $stmt->bindParam(':idColegiado', $idColegiado, PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row) {
                return [
                    'estado'  => true,
                    'datos'   => [
                        'tomo'               => $row['tomo'],
                        'folio'              => $row['folio'],
                        'fechaMatriculacion' => $row['fechaMatriculacion'],
                        'matriculaNacional'  => $row['matriculaNacional']
                    ],
                    'mensaje' => "OK",
                    'clase'   => 'alert alert-success',
                    'icono'   => 'glyphicon glyphicon-ok'
                ];
            }

            return [
                'estado'  => true,
                'datos'   => null,
                'mensaje' => "No hay colegiado " . $idColegiado,
                'clase'   => 'alert alert-info',
                'icono'   => 'glyphicon glyphicon-exclamation-sign'
            ];

        } catch (PDOException $e) {
            return [
                'estado'  => false,
                'mensaje' => "Error buscando colegiado",
                'clase'   => 'alert alert-danger',
                'icono'   => 'glyphicon glyphicon-remove'
            ];
        }
    }

    public function modificarMatricula($idColegiado, $fechaMatriculacion, $tomo, $folio, $matriculaNacional, $datosAnteriores) {
        try {
            $db = Database::getConnection();
            $db->beginTransaction();

            $sql = "UPDATE colegiado
                    SET FechaMatriculacion = :fechaMatriculacion, Tomo = :tomo, Folio = :folio, MatriculaNacional = :matriculaNacional
                    WHERE Id = :idColegiado";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':fechaMatriculacion' => $fechaMatriculacion,
                ':tomo'               => $tomo,
                ':folio'              => $folio,
                ':matriculaNacional'  => $matriculaNacional,
                ':idColegiado'        => $idColegiado
            ]);

            $sql = "INSERT INTO log_tabla
                    (Tabla, IdTabla, Fecha, TipoMovimiento, IdUsuario, Datos)
                    VALUES ('colegiado', :idTabla, now(), 'modificacion', :idUsuario, :datos)";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':idTabla'  => $idColegiado,
                ':idUsuario' => $_SESSION['user_id'],
                ':datos'    => serialize($datosAnteriores)
            ]);

            $resultado = [
                'estado'  => true,
                'mensaje' => 'EL COLEGIADO HA SIDO ACTUALIZADO CORRECTAMENTE',
                'clase'   => 'alert alert-success',
                'icono'   => 'glyphicon glyphicon-ok'
            ];

            $db->commit();
            return $resultado;

        } catch (PDOException $e) {
            $db->rollBack();
            return [
                'estado'  => false,
                'mensaje' => "Error: " . $e->getMessage(),
                'clase'   => 'alert alert-danger',
                'icono'   => 'glyphicon glyphicon-remove'
            ];
        }
    }

    public function modificarTitulo($idColegiadoTitulo, $idColegiado, $idTipoTitulo, $fechaTitulo, $idUniversidad, $datosAnteriores, $tituloDigital) {
        try {
            $db = Database::getConnection();
            $db->beginTransaction();

            if (isset($idColegiadoTitulo) && $idColegiadoTitulo <> "") {
                $accion = "MODIFICAR";
                $sql = "UPDATE colegiadotitulo
                        SET IdTipoTitulo = :idTipoTitulo, FechaTitulo = :fechaTitulo, IdUniversidad = :idUniversidad, Digital = :tituloDigital
                        WHERE IdColegiadoTitulo = :idColegiadoTitulo";
                $stmt = $db->prepare($sql);
                $stmt->execute([
                    ':idTipoTitulo'      => $idTipoTitulo,
                    ':fechaTitulo'       => $fechaTitulo,
                    ':idUniversidad'     => $idUniversidad,
                    ':tituloDigital'     => $tituloDigital,
                    ':idColegiadoTitulo' => $idColegiadoTitulo
                ]);
            } else {
                $accion = "AGREGAR";
                $sql = "INSERT INTO colegiadotitulo (IdColegiado, IdTipoTitulo, FechaTitulo, IdUniversidad, Digital)
                        VALUES(:idColegiado, :idTipoTitulo, :fechaTitulo, :idUniversidad, :tituloDigital)";
                $stmt = $db->prepare($sql);
                $stmt->execute([
                    ':idColegiado'   => $idColegiado,
                    ':idTipoTitulo'  => $idTipoTitulo,
                    ':fechaTitulo'   => $fechaTitulo,
                    ':idUniversidad' => $idUniversidad,
                    ':tituloDigital' => $tituloDigital
                ]);
            }

            if ($accion == "MODIFICAR") {
                $tipoMovimiento = 'modificacion';
            } else {
                $idColegiadoTitulo = $db->lastInsertId();
                $datosAnteriores = [];
                $tipoMovimiento = 'alta';
            }

            $sql = "INSERT INTO log_tabla
                    (Tabla, IdTabla, Fecha, TipoMovimiento, IdUsuario, Datos)
                    VALUES ('colegiadotitulo', :idTabla, now(), :tipoMovimiento, :idUsuario, :datos)";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':idTabla'        => $idColegiadoTitulo,
                ':tipoMovimiento' => $tipoMovimiento,
                ':idUsuario'      => $_SESSION['user_id'],
                ':datos'          => serialize($datosAnteriores)
            ]);

            $resultado = [
                'estado'  => true,
                'mensaje' => 'EL TITULO HA SIDO ACTUALIZADO CORRECTAMENTE',
                'clase'   => 'alert alert-success',
                'icono'   => 'glyphicon glyphicon-ok'
            ];

            $db->commit();
            return $resultado;

        } catch (PDOException $e) {
            $db->rollBack();
            return [
                'estado'  => false,
                'mensaje' => "Error: " . $e->getMessage(),
                'clase'   => 'alert alert-danger',
                'icono'   => 'glyphicon glyphicon-remove'
            ];
        }
    }

    public function tieneCorreoRechazado($idColegiado) {
        try {
            $db = Database::getConnection();
            $sql = "SELECT Id AS idRechazado
                    FROM colegiadomailrechazado
                    WHERE IdColegiado = :idColegiado";

            $stmt = $db->prepare($sql);
            $stmt->bindParam(':idColegiado', $idColegiado, PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row && isset($row['idRechazado'])) {
                return true;
            }

            return false;

        } catch (PDOException $e) {
            return false;
        }
    }
}
