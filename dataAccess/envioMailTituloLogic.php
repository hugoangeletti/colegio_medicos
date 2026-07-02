<?php
class envioMailTituloLogic {

    public function  obtenerEnvioMailTitulo()
{
    try {
        $db = Database::getConnection();
        $sql="SELECT Id FROM enviomailtitulo WHERE Estado = 'A' LIMIT 1";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $row = $stmt->fetch();

        $resultado = array();
        if ($row) {
            $resultado['estado'] = true;
            $resultado['mensaje'] = "OK";
            $resultado['idEnvioMailTitulo'] = $row['Id'];
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = false;
            $resultado['mensaje'] = "No hay enviomailtitulo";
            $resultado['clase'] = 'alert alert-info';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando enviomailtitulo: " . $e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }

    return $resultado;
}

    public function  obtenerTitulosParaEnviar($anio, $rango)
{
    try {
        $db = Database::getConnection();
        $sql="SELECT distinct tituloespecialista.IdTituloEspecialista, especialidad.Especialidad, resoluciondetalle.TipoEspecialista,
                resoluciondetalle.IdColegiado, colegiado.Matricula, persona.Apellido, persona.Nombres, persona.Sexo,
                colegiadocontacto.CorreoElectronico
            FROM tituloespecialista
            INNER JOIN resoluciondetalle ON(resoluciondetalle.Id = tituloespecialista.IdResolucionDetalle)
            INNER JOIN colegiado ON(colegiado.Id = resoluciondetalle.IdColegiado)
            INNER JOIN persona ON(persona.Id = colegiado.IdPersona)
            INNER JOIN colegiadocontacto ON(colegiadocontacto.IdColegiado = colegiado.Id
                AND colegiadocontacto.IdEstado = 1
                AND colegiadocontacto.CorreoElectronico is not null
                AND UPPER(colegiadocontacto.CorreoElectronico) <> 'NR'
                AND colegiadocontacto.CorreoElectronico <> '')
            INNER JOIN tipomovimiento ON(tipomovimiento.Id = colegiado.Estado)
            INNER JOIN especialidad ON(especialidad.Id = resoluciondetalle.Especialidad)
            LEFT JOIN enviomailtitulocolegiado ON(enviomailtitulocolegiado.IdColegiado = resoluciondetalle.IdColegiado)
            LEFT JOIN enviomailtitulo ON(enviomailtitulo.Id = enviomailtitulocolegiado.IdEnvioMailTitulo
                    AND enviomailtitulo.Estado = 'A')
            LEFT JOIN enviomaildiariocolegiado ON(enviomaildiariocolegiado.IdColegiado = resoluciondetalle.IdColegiado
                AND enviomaildiariocolegiado.IdReferencia = tituloespecialista.IdTituloEspecialista)
            WHERE tipomovimiento.Estado = 'A'
                    AND year(resoluciondetalle.FechaAprobada) >= ?
                    AND tituloespecialista.FechaEntrega is null
                    AND enviomailtitulocolegiado.Id is null
            ORDER BY colegiado.Matricula
            LIMIT ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$anio, $rango]);
        $rows = $stmt->fetchAll();

        $resultado = array();
        if (count($rows) > 0) {
            $resultado['cantidad'] = count($rows);
            $datos = array();
            foreach ($rows as $row) {
                $item = array (
                        'idReferencia' => $row['IdTituloEspecialista'],
                        'especialidad' => $row['Especialidad'],
                        'tipoTitulo' => $row['TipoEspecialista'],
                        'idColegiado' => $row['IdColegiado'],
                        'matricula' => $row['Matricula'],
                        'sexo' => $row['Sexo'],
                        'apellido' => $row['Apellido'],
                        'nombres' => $row['Nombres'],
                        'mail' => $row['CorreoElectronico']
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
            $resultado['mensaje'] = "No hay Titulo a Enviar";
            $resultado['clase'] = 'alert alert-info';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando Titulos a Enviar: " . $e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }

    return $resultado;
}

    public function guardarTituloEnviadoColegiado($idEnvioMailTitulo, $idColegiado)
{
    try {
        $db = Database::getConnection();
        $sql = "INSERT INTO enviomailtitulocolegiado
            (IdEnvioMailTitulo, IdColegiado, FechaEnvio)
            VALUES (?, ?, NOW())";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idEnvioMailTitulo, $idColegiado]);
        $resultado['estado'] = true;
        $resultado['mensaje'] = "OK";
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error agregando envio titulo colegiado: " . $e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }

    return $resultado;
}
}
