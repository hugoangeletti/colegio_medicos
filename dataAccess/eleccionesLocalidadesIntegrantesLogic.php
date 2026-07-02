<?php
class eleccionesLocalidadesIntegrantesLogic {

    private $deudaAnualLogic;
    private function getDeudaAnualLogic() {
        if (!$this->deudaAnualLogic) {
            $this->deudaAnualLogic = new colegiadoDeudaAnualLogic();
        }
        return $this->deudaAnualLogic;
    }

    public function obtenerIntegrantesPorIdEleccionesLocalidadLista($idEleccionesLocalidadLista) {
    try {
        $db = Database::getConnection();
        $sql="SELECT eleccioneslocalidadlistaintegrantes.IdELListaIntegrante, eleccioneslocalidadlistaintegrantes.Matricula,
            eleccioneslocalidadlistaintegrantes.Cargo, eleccioneslocalidadlistaintegrantes.Orden,
            eleccioneslocalidadlistaintegrantes.Estado, CONCAT(persona.Apellido, ' ', persona.Nombres) AS ApellidoNombre,
            colegiado.Id
            FROM eleccioneslocalidadlistaintegrantes
            INNER JOIN colegiado ON(colegiado.Matricula = eleccioneslocalidadlistaintegrantes.Matricula)
            INNER JOIN persona ON(persona.Id = colegiado.IdPersona)
            WHERE eleccioneslocalidadlistaintegrantes.IdELLista = ?
            AND eleccioneslocalidadlistaintegrantes.Estado = 'A'
            ORDER BY eleccioneslocalidadlistaintegrantes.Cargo DESC, eleccioneslocalidadlistaintegrantes.Orden";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idEleccionesLocalidadLista]);
        $rows = $stmt->fetchAll();

        $resultado = array();
        if (count($rows) > 0) {
            $datos = array();
            foreach ($rows as $row) {
                $item = array (
                        'idEleccionesLocalidadIntegrante' => $row['IdELListaIntegrante'],
                        'matricula' => $row['Matricula'],
                        'apellidoNombre' => $row['ApellidoNombre'],
                        'cargo' => $row['Cargo'],
                        'orden' => $row['Orden'],
                        'estado' => $row['Estado'],
                        'idColegiado' => $row['Id']
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
            $resultado['mensaje'] = "No hay integrantes de la lista";
            $resultado['clase'] = 'alert alert-warning';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando integrantes de la lista: " . $e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }

    return $resultado;
}

    public function obtenerEleccionesLocalidadListaIntegrantesPorId($idEleccionesLocalidadListaIntegrante) {
    try {
        $db = Database::getConnection();
        $sql="SELECT eleccioneslocalidadlistaintegrantes.IdELLista, eleccioneslocalidadlistaintegrantes.Matricula,
            eleccioneslocalidadlistaintegrantes.Cargo, eleccioneslocalidadlistaintegrantes.Orden,
            eleccioneslocalidadlistaintegrantes.Estado, CONCAT(persona.Apellido, ' ', persona.Nombres) AS ApellidoNombre,
            colegiado.Id
            FROM eleccioneslocalidadlistaintegrantes
            LEFT JOIN colegiado ON(colegiado.Matricula = eleccioneslocalidadlistaintegrantes.Matricula)
            LEFT JOIN persona ON(persona.Id = colegiado.IdPersona)
            WHERE eleccioneslocalidadlistaintegrantes.IdELListaIntegrante = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idEleccionesLocalidadListaIntegrante]);
        $row = $stmt->fetch();

        $resultado = array();
        if ($row) {
            $datos = array (
                        'idEleccionesLocalidad' => $row['IdELLista'],
                        'matricula' => $row['Matricula'],
                        'apellidoNombre' => $row['ApellidoNombre'],
                        'cargo' => $row['Cargo'],
                        'orden' => $row['Orden'],
                        'estado' => $row['Estado'],
                        'idColegiado' => $row['Id']
                    );

            $resultado['estado'] = true;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = false;
            $resultado['mensaje'] = "No hay listas";
            $resultado['clase'] = 'alert alert-warning';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando listas de la localidad: " . $e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }

    return $resultado;
}

    public function agregarEleccionesLocalidadesListaIntegrantes($idEleccionesLocalidadLista, $matricula, $apellidoNombre, $cargo, $orden) {
    try {
        $db = Database::getConnection();
        $sql="INSERT INTO eleccioneslocalidadlistaintegrantes (IdELLista, Matricula, ApellidoNombre, Cargo, Orden)
            VALUES (?, ?, ?, ?, ?)";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idEleccionesLocalidadLista, $matricula, $apellidoNombre, $cargo, $orden]);
        $estadoConsulta = TRUE;
        $mensaje = 'Integrante HA SIDO AGREGADO';
    } catch (PDOException $e) {
        $estadoConsulta = FALSE;
        $mensaje = 'ERROR AL AGREGAR Integrante: ' . $e->getMessage();
    }
    $result = array();
    $result['estado'] = $estadoConsulta;
    $result['mensaje'] = $mensaje;
    return $result;
}

    public function editarEleccionesLocalidadesListaIntegrante($idEleccionesLocalidadListaIntegrante, $matricula, $apellidoNombre, $cargo, $orden) {
    try {
        $db = Database::getConnection();
        $sql="UPDATE eleccioneslocalidadlistaintegrantes
                SET Matricula = ?, ApellidoNombre = ?, Cargo = ?, Orden = ?
                WHERE IdELListaIntegrante = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$matricula, $apellidoNombre, $cargo, $orden, $idEleccionesLocalidadListaIntegrante]);
        $estadoConsulta = TRUE;
        $mensaje = 'Integrante HA SIDO MODIFICADO';
    } catch (PDOException $e) {
        $estadoConsulta = FALSE;
        $mensaje = 'ERROR AL MODIFICAR Integrante: ' . $e->getMessage();
    }
    $result = array();
    $result['estado'] = $estadoConsulta;
    $result['mensaje'] = $mensaje;
    return $result;
}

    public function borrarEleccionesLocalidadesListaIntegrante($idEleccionesLocalidadListaIntegrante){
    try {
        $db = Database::getConnection();
        $sql="UPDATE eleccioneslocalidadlistaintegrantes
              SET Estado = 'B'
              WHERE IdELListaIntegrante = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idEleccionesLocalidadListaIntegrante]);
        $estadoConsulta = TRUE;
        $mensaje = 'Integrante HA SIDO BORRADO';
    } catch (PDOException $e) {
        $estadoConsulta = FALSE;
        $mensaje = 'ERROR AL BORRAR Integrante: ' . $e->getMessage();
    }
    $result = array();
    $result['estado'] = $estadoConsulta;
    $result['mensaje'] = $mensaje;
    return $result;
}

    public function obtenerCantidadIntegrantesPorCargo($idEleccionesLocalidadLista, $cargo){
    try {
        $db = Database::getConnection();
        $sql="SELECT COUNT(eleccioneslocalidadlistaintegrantes.IdELListaIntegrante) AS Cantidad
                FROM eleccioneslocalidadlistaintegrantes
                WHERE eleccioneslocalidadlistaintegrantes.IdELLista = ?
                AND eleccioneslocalidadlistaintegrantes.Cargo = ?
                AND eleccioneslocalidadlistaintegrantes.Estado = 'A'";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idEleccionesLocalidadLista, $cargo]);
        $row = $stmt->fetch();

        $resultado = array();
        $resultado['estado'] = true;
        $resultado['mensaje'] = "OK";
        $resultado['cantidad'] = $row ? $row['Cantidad'] : 0;
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando listas de la localidad: " . $e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }

    return $resultado;
}

    public function obtenerObservacionesIntegrante($idEleccionesLocalidadListaIntegrante) {
    try {
        $db = Database::getConnection();
        $sql="SELECT eleccioneslocalidadlistaintegrantes.IdELLista, eleccioneslocalidadlistaintegrantes.Matricula,
            eleccioneslocalidadlistaintegrantes.Cargo, eleccioneslocalidadlistaintegrantes.Orden,
            tipomovimiento.Estado, CONCAT(persona.Apellido, ' ', persona.Nombres) AS ApellidoNombre,
            colegiado.FechaMatriculacion, zonas.Zona, colegiado.Id, zonas.Nombre AS ZonaNombre
            FROM eleccioneslocalidadlistaintegrantes
            INNER JOIN colegiado ON(colegiado.Matricula = eleccioneslocalidadlistaintegrantes.Matricula)
            INNER JOIN persona ON(persona.Id = colegiado.IdPersona)
            INNER JOIN tipomovimiento ON(tipomovimiento.Id = colegiado.Estado)
            INNER JOIN colegiadodomicilioreal ON(colegiadodomicilioreal.idColegiado = colegiado.Id and colegiadodomicilioreal.idEstado = 1)
            INNER JOIN localidad ON(localidad.Id = colegiadodomicilioreal.idLocalidad)
            INNER JOIN zonas ON(zonas.Id = localidad.idZona)
            WHERE eleccioneslocalidadlistaintegrantes.IdELListaIntegrante = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idEleccionesLocalidadListaIntegrante]);
        $row = $stmt->fetch();

        $resultado = array();
        if ($row) {
            $idColegiado = $row['Id'];
            $fechaMatriculacion = $row['FechaMatriculacion'];
            $periodoActual = $_SESSION['periodoActual'];
            $resEstadoTeso = $this->getDeudaAnualLogic()->estadoTesoreriaPorColegiado($idColegiado, $periodoActual);
            if ($resEstadoTeso['estado']){
                $codigo = $resEstadoTeso['codigoDeudor'];
                $resEstadoTesoreria = $this->getDeudaAnualLogic()->estadoTesoreria($codigo);
                if ($resEstadoTesoreria['estado']){
                    $estadoTesoreria = $resEstadoTesoreria['estadoTesoreria'];
                } else {
                    $estadoTesoreria = $resEstadoTesoreria['mensaje'];
                }
            } else {
                $estadoTesoreria = $resEstadoTeso['mensaje'];
            }

            $aniosColegiado = calcular_edad($fechaMatriculacion);
            $laAntiguedad = explode(" ", $aniosColegiado);
            $edad = $laAntiguedad[0];
            $antiguedad = 'Menos de 2 años';
            if (2<= $edad && $edad<=10) {
                $antiguedad = 'C (Más de 2 años)';
            } elseif ($edad>10) {
                $antiguedad = 'T (Más de 10 años)';
            }

            $datos = array (
                        'idEleccionesLocalidad' => $row['IdELLista'],
                        'matricula' => $row['Matricula'],
                        'apellidoNombre' => $row['ApellidoNombre'],
                        'cargo' => $row['Cargo'],
                        'orden' => $row['Orden'],
                        'estadoMatricular' => $row['Estado'],
                        'estadoTesoreria' => $estadoTesoreria,
                        'antiguedad' => $antiguedad,
                        'zona' => $row['Zona'],
                        'zonaNombre' => $row['ZonaNombre']
                    );

            $resultado['estado'] = true;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = false;
            $resultado['mensaje'] = "No hay listas";
            $resultado['clase'] = 'alert alert-warning';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando listas de la localidad: " . $e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }

    return $resultado;
}

    public function matriculaExisteEnLista($matricula, $idEleccionesLocalidadLista) {
    try {
        $db = Database::getConnection();
        $sql="SELECT eleccioneslocalidadlista.IdELLista, eleccioneslocalidad.Localidad,
            eleccioneslocalidad.LocalidadDetalle, eleccioneslocalidadlista.Nombre,
            eleccioneslocalidadlista.TipoLista, eleccioneslocalidadlistaintegrantes.Cargo,
            eleccioneslocalidadlistaintegrantes.Orden
            FROM eleccioneslocalidadlistaintegrantes
            LEFT JOIN eleccioneslocalidadlista ON(eleccioneslocalidadlista.IdELLista = eleccioneslocalidadlistaintegrantes.IdELLista)
            LEFT JOIN eleccioneslocalidad ON(eleccioneslocalidad.IdEleccionesLocalidad = eleccioneslocalidadlista.IdEleccionesLocalidad)
            LEFT JOIN elecciones ON(elecciones.IdElecciones = eleccioneslocalidad.IdElecciones)
            WHERE Matricula = ?
            AND eleccioneslocalidadlistaintegrantes.Estado = 'A'
            AND elecciones.Anio = YEAR(NOW())";
        $stmt = $db->prepare($sql);
        $stmt->execute([$matricula]);
        $rows = $stmt->fetchAll();

        $resultado = array();
        if (count($rows) > 0) {
            $otraLista = '';
            $resultado['estado'] = false;
            foreach ($rows as $row) {
                $id = $row['IdELLista'];
                $localidadDetalle = $row['LocalidadDetalle'];
                $listaNombre = $row['Nombre'];
                $cargo = $row['Cargo'];
                $orden = $row['Orden'];
                if ($id <> $idEleccionesLocalidadLista) {
                    if ($otraLista <> '') {
                        $otraLista .= '<br>';
                    }
                    switch ($cargo) {
                        case 'T':
                            $cargo = 'Titular';
                            break;
                        case 'S':
                            $cargo = 'Suplente';
                            break;
                        default:
                            break;
                    }
                    $otraLista .= 'Lista: <b>'.$listaNombre.'</b> de <b>'.$localidadDetalle.'</b> Cargo: <b>'.$cargo.'</b> Orden: <b>'.$orden.'</b>';
                    $resultado['estado'] = true;
                }
            }
            $resultado['otraLista'] = $otraLista;
            $resultado['mensaje'] = "OK";
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = false;
            $resultado['mensaje'] = "No hay integrantes de la lista";
            $resultado['clase'] = 'alert alert-warning';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando integrantes de la lista: " . $e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }

    return $resultado;
}
}
