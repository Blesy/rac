<?php

namespace Coppel\Pck001Back\Controllers;

use Exception;
use Phalcon\DI\DI;
use Coppel\RAC\Controllers\RESTController;
use Coppel\RAC\Exceptions\HTTPException;
use Coppel\Pck001Back\Models as Modelos;

class LectorController extends RESTController
{
    private $logger;
    private $modelo;

    public function onConstruct()
    {
        $this->logger = DI::getDefault()->get('logger');
        $this->modelo = new Modelos\LectorModel();
    }

    public function validarLector()
    {
        $response = null;

        $data = $this->request->getJsonRawBody();
        if (!$data->numLector) {
            throw new \InvalidArgumentException("El campo lector, no es valido.");
        }
        try {
            $lector = $data->numLector;

            $response = $this->modelo->validarLector($lector);
        } catch (Exception $ex) {
            $mensaje = $ex->getMessage();
            $this->logger->error('[' . __METHOD__ . "] Excepción > $mensaje");

            throw new HTTPException(
                'No fue posible completar su solicitud, intente de nuevo o contacte con el administrador.',
                500,
                [
                    'dev' => $mensaje,
                    'internalCode' => 'SIE1000',
                    'more' => 'Verificar conexión con la base de datos.'
                ]
            );
        }

        return $response;
    }

    public function ValidaCambioPrecio()
    {

        $response = null;
        $data = $this->request->getJsonRawBody();
        try {
            
            if (!$data->sCodigo) {
                throw new \InvalidArgumentException("El campo codigo, no es valido.");
            }
            if (!$data->sTalla) {
                throw new \InvalidArgumentException("El campo talla, no es valido.");
            }
            if (!$data->sPrecio) {
                throw new \InvalidArgumentException("El campo precio, no es valido.");
            }
            if (!$data->sTipo) {
                throw new \InvalidArgumentException("El campo tipo, no es valido.");
            }
            $response = $this->modelo->ValidaCambioPrecio($data->sCodigo, $data->sTalla, $data->sPrecio, $data->sTipo);
        } catch (Exception $ex) {
            $mensaje = $ex->getMessage();
            $this->logger->error('[' . __METHOD__ . "] Excepción > $mensaje");
            throw new HTTPException(
                'No fue posible completar su solicitud, intente de nuevo o contacte con el administrador.',
                500,
                [
                    'dev' => $mensaje,
                    'internalCode' => 'SIE1000',
                    'more' => 'Verificar conexión con la base de datos.'
                ]
            );
        }
        return $response;

    }
    public function borrarRegistro()
    {
        $response = null;
        $data = $this->request->getJsonRawBody();
        try {
            if (!$data->sSeccion) {
                throw new \InvalidArgumentException("El campo seccion, no es valido.");
            }
            if (!$data->sPosicion) {
                throw new \InvalidArgumentException("El campo Posicion, no es valido.");
            }
            if (!$data->sTerminal) {
                throw new \InvalidArgumentException("El campo Terminal, no es valido.");
            }
            $response = $this->modelo->borrarRegistro(sSeccion: $data->sSeccion,sPosicion: $data->sPosicion, Terminal: $data->sTerminal );
        } catch (Exception $ex) {
            $mensaje = $ex->getMessage();
            $this->logger->error('[' . __METHOD__ . "] Excepción > $mensaje");
            throw new HTTPException(
                'No fue posible completar su solicitud, intente de nuevo o contacte con el administrador.',
                500,
                [
                    'dev' => $mensaje,
                    'internalCode' => 'SIE1000',
                    'more' => 'Verificar conexión con la base de datos.'
                ]
            );
        }
        return $response;
    }

    public function obtenerIdFEmpleado()
    {
        $response = null;
        $data = $this->request->getJsonRawBody();
        try {
            if (!$data->sIdEmpleado && !$data->sNumEmpleado) {
                throw new \InvalidArgumentException("El campo Id empleado, no es valido.");
            }
          
            
            $response = $this->modelo->obtenerIdFEmpleado( idEmpleado: $data->sIdEmpleado,   numEmpleado: $data->sNumEmpleado);
        } catch (Exception $ex) {
            $mensaje = $ex->getMessage();
            $this->logger->error('[' . __METHOD__ . "] Excepción > $mensaje");
            throw new HTTPException(
                'No fue posible completar su solicitud, intente de nuevo o contacte con el administrador.',
                500,
                [
                    'dev' => $mensaje,
                    'internalCode' => 'SIE1000',
                    'more' => 'Verificar conexión con la base de datos.'
                ]
            );
        }
        return $response;
    }
}
