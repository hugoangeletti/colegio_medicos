<?php
class busquedaLogic {

    public function obtenerConsultorios($calle, $lateral, $numero) {
        try {
            if (empty($calle)) {
                return [
                    'estado'  => false,
                    'mensaje' => "FALTAN PARAMETROS",
                    'clase'   => 'alert alert-warning',
                    'icono'   => 'glyphicon glyphicon-exclamation-sign'
                ];
            }

            $db = Database::getConnection();

            $params = [':calleCC' => $calle . '%', ':calleCP' => $calle . '%'];

            $conLateralCC = $conLateralCP = "";
            if (!empty($lateral)) {
                $conLateralCC = " AND cc.Lateral LIKE :lateralCC";
                $conLateralCP = " AND cp.Lateral LIKE :lateralCP";
                $params[':lateralCC'] = $lateral . '%';
                $params[':lateralCP'] = $lateral . '%';
            }

            $conNumeroCC = $conNumeroCP = "";
            if (!empty($numero)) {
                $conNumeroCC = " AND cc.Numero LIKE :numeroCC";
                $conNumeroCP = " AND cp.Numero LIKE :numeroCP";
                $params[':numeroCC'] = $numero . '%';
                $params[':numeroCP'] = $numero . '%';
            }

            $sql = "(SELECT cc.Id AS id, cc.Calle AS calle, cc.Lateral AS lateral, cc.Numero AS numero,
                            l.Nombre AS localidad, c.Matricula AS matricula,
                            p.Apellido AS apellido, p.Nombres AS nombres,
                            cc.Estado AS estadoRaw, cc.FechaBaja AS fechaBaja,
                            cc.FechaHabilitacion AS fechaHabilitacion, cc.Observacion AS observacion,
                            'CONSULTORIO' AS origen
                     FROM colegiadoconsultorio cc
                     LEFT JOIN localidad l ON l.Id = cc.IdLocalidad
                     INNER JOIN colegiado c ON c.Id = cc.IdColegiado
                     INNER JOIN persona p ON p.Id = c.IdPersona
                     WHERE cc.Calle LIKE :calleCC{$conLateralCC}{$conNumeroCC})

                    UNION

                    (SELECT cp.Id AS id, cp.Calle AS calle, cp.Lateral AS lateral, cp.Numero AS numero,
                            l.Nombre AS localidad, c.Matricula AS matricula,
                            p.Apellido AS apellido, p.Nombres AS nombres,
                            cp.IdEstado AS estadoRaw, cp.FechaBaja AS fechaBaja,
                            cp.FechaCarga AS fechaHabilitacion, '' AS observacion,
                            'DOMICILIOPROFESIONAL' AS origen
                     FROM colegiadodomicilioprofesional cp
                     LEFT JOIN localidad l ON l.Id = cp.IdLocalidad
                     INNER JOIN colegiado c ON c.Id = cp.IdColegiado
                     INNER JOIN persona p ON p.Id = c.IdPersona
                     WHERE cp.Calle LIKE :calleCP{$conLateralCP}{$conNumeroCP})";

            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $filas = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $datos = [];
            foreach ($filas as $row) {
                $estadoVal = $row['estadoRaw'];
                if ($estadoVal == 'B' || $estadoVal == '2') {
                    $estadoVal = 'BAJA con fecha: ';
                    if (!empty($row['fechaBaja'])) {
                        $estadoVal .= cambiarFechaFormatoParaMostrar(substr($row['fechaBaja'], 0, 10));
                    }
                } else {
                    $estadoVal = 'ACTIVO';
                }
                $datos[] = [
                    'id'               => $row['id'],
                    'calle'            => $row['calle'],
                    'lateral'          => $row['lateral'],
                    'numero'           => $row['numero'],
                    'localidad'        => $row['localidad'],
                    'matricula'        => $row['matricula'],
                    'apellidoNombre'   => trim($row['apellido']) . ' ' . trim($row['nombres']),
                    'estado'           => $estadoVal,
                    'fechaHabilitacion'=> $row['fechaHabilitacion'],
                    'observacion'      => $row['observacion'],
                    'origen'           => $row['origen']
                ];
            }

            return [
                'estado'  => true,
                'mensaje' => "OK",
                'datos'   => $datos,
                'clase'   => 'alert alert-success',
                'icono'   => 'glyphicon glyphicon-ok'
            ];

        } catch (PDOException $e) {
            return [
                'estado'  => false,
                'mensaje' => "Error buscando Consultorios",
                'clase'   => 'alert alert-danger',
                'icono'   => 'glyphicon glyphicon-exclamation-sign'
            ];
        }
    }
}
