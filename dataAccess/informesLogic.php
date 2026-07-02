<?php
class informesLogic {

	function informeAnualPorTipoEspecialista($periodoProcesar) {
        try {
            $db = Database::getConnection();
            $fechaDesde = $periodoProcesar.'-05-01';
            $fechaHasta = ($periodoProcesar + 1).'-04-30';

            $sql = "(SELECT te.Nombre, COUNT(a.IdColegiado) as Cantidad
                FROM colegiadoespecialista a
                INNER JOIN especialidad b ON a.especialidad = b.id
                LEFT JOIN tipoespecialista te ON te.IdTipoEspecialista = a.IdTipoEspecialista
                WHERE a.FechaEspecialista BETWEEN ? AND ?
                GROUP BY te.Nombre)

                UNION

                (SELECT 'Consultores' AS Nombre, COUNT(a.IdColegiado) AS CantConsultor
                FROM colegiadoespecialista a
                INNER JOIN colegiadoespecialistatipo d ON d.IdColegiadoEspecialista = a.Id
                WHERE d.TipoEspecialista = 'C'
                AND d.Fecha BETWEEN ? AND ?)

                UNION

                (SELECT 'Jerarquizados' AS Nombre, COUNT(a.IdColegiado) AS CantJerarquizado
                FROM colegiadoespecialista a
                INNER JOIN colegiadoespecialistatipo d ON d.IdColegiadoEspecialista = a.Id
                WHERE d.TipoEspecialista = 'J'
                AND d.Fecha BETWEEN ? AND ?)

                UNION

                (SELECT 'Recertificaciones' AS Nombre, COUNT(a.IdColegiado) AS Cantidad
                FROM colegiadoespecialista a
                INNER JOIN especialidad b on(a.especialidad=b.id)
                WHERE a.FechaRecertificacion BETWEEN ? AND ?)";
            $stmt = $db->prepare($sql);
            $stmt->execute([$fechaDesde, $fechaHasta, $fechaDesde, $fechaHasta, $fechaDesde, $fechaHasta, $fechaDesde, $fechaHasta]);
            $rows = $stmt->fetchAll();

            $resultado = array();
            $datos = array();
            foreach ($rows as $row) {
                $item = array (
                    'tipoEspecialista' => $row['Nombre'],
                    'cantidad' => $row['Cantidad']
                );
                array_push($datos, $item);
            }
            $resultado['estado'] = TRUE;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } catch (PDOException $e) {
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "Error buscando especialistas: " . $e->getMessage();
            $resultado['clase'] = 'alert alert-error';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }

        return $resultado;
	}

	function obtenerMatriculadosPorUniversidad($fechaDesde, $fechaHasta) {
        try {
            $db = Database::getConnection();
            $sql = "SELECT c.Matricula, p.Apellido, p.Nombres, c.FechaMatriculacion, ct.FechaTitulo, u.Nombre AS NombreUniversidad, pa.Pais
                FROM colegiado c
                INNER JOIN persona p ON p.Id = c.IdPersona
                INNER JOIN colegiadotitulo ct ON ct.IdColegiado = c.Id
                INNER JOIN universidad u ON u.Id = ct.IdUniversidad
                LEFT JOIN paises pa ON pa.Id = u.IdPaises
                WHERE c.FechaMatriculacion BETWEEN ? AND ?
                AND c.DistritoOrigen = 1
                ORDER BY u.Nombre, p.Apellido, p.Nombres";
            $stmt = $db->prepare($sql);
            $stmt->execute([$fechaDesde, $fechaHasta]);
            $rows = $stmt->fetchAll();

            $resultado = array();
            $datos = array();
            foreach ($rows as $row) {
                $item = array (
                    'matricula' => $row['Matricula'],
                    'apellidoNombre' => $row['Apellido'].' '.$row['Nombres'],
                    'fechaMatriculacion' => $row['FechaMatriculacion'],
                    'fechaTitulo' => $row['FechaTitulo'],
                    'nombreUniversidad' => $row['NombreUniversidad'],
                    'paisUniversidad' => $row['Pais']
                );
                array_push($datos, $item);
            }
            $resultado['estado'] = TRUE;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } catch (PDOException $e) {
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "Error buscando matriculados: " . $e->getMessage();
            $resultado['clase'] = 'alert alert-error';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }

        return $resultado;
	}


}
