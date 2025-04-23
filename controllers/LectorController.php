<?php

namespace Coppel\Pck001Back\Controllers;

use Exception;
use Phalcon\DI\DI;
use Coppel\RAC\Controllers\RESTController;
use Coppel\RAC\Exceptions\HTTPException;
use Coppel\Pck001Back\Models as Modelos;

class ApiController extends RESTController
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
        if(!$data->numLector){
            throw new \InvalidArgumentException("El campo tienda, no es valido.")
        }
        try {
            $lector = $data->numLector;

            $response = $this->modelo->validarLector($lector);
        } catch (Exception $ex) {
            $mensaje = $ex->getMessage();
            $this->logger->error('['. __METHOD__ ."] Excepción > $mensaje");

            throw new HTTPException(
                'No fue posible completar su solicitud, intente de nuevo o contacte con el administrador.',
                500, [
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
        $return = null;

        $data = $this->request->getJsonRawBody();
        if(!$data->sCodigo){
            throw new \InvalidArgumentException("El campo codigo, no es valido.")
        }
        if(!$data->sTalla){
            throw new \InvalidArgumentException("El campo talla, no es valido.")
        }
        if(!$data->sPrecio){
            throw new \InvalidArgumentException("El campo precio, no es valido.")
        }
        if(!$data->sTipo){
            throw new \InvalidArgumentException("El campo tipo, no es valido.")
        }

        $hr = 0;
        $bRegresa = false;
        $status = 200;
        $cConsulta = [];
        $cMsjUsuario = [];
        $sCodigo;
        $sTalla;
        $sPrecio;
        $sTipo;
        $iError = 0;
        $iPrecioNuevo = 0;
        $iCodigoAcum = 0;
        $iTallaAcum = 0;

        $msjUsuario = "";

        $query = "select Error,PrecioNuevo,numcodigoacumular,numtallaacumular from pck001cambioprecio({$sCodigo},{$sTalla},{$sPrecio},{$sTipo})";

        $db = DI::getDefault()->get('conexion');

        $statement = $db->prepare($query);
        $statement->execute();

        $fetchData = $statement->fetch();

        if ($fetchData)
        {
            $iPrecioNuevo = $fetchData->tipoguia;
			$iError = $fetchData->cveactualiza;
			$iCodigoAcum = $fetchData->codigoacum;
			$iTallaAcum = $fetchData->tallaacum;
        }

        switch($iError)
        {
            case 0:
                $bRegresa=true;
                break;
            case 1:
                $msjUsuario = "Este codigo se acumulo al <{$iCodigoAcum}-{$iTallaAcum}>Toma nota del codigo-talla para que lo recibas como mercancia no surtible";
                break;
            case 2:
                $msjUsuario = "Este codigo se acumulo al <{$iCodigoAcum}-{$iTallaAcum}> Marca la etiqueta para que hagas un reetiquetado con etiquetas BLANCAS ";
                $bRegresa = true;
                break;
            case 3:
                $msjUsuario = "Codigo-Talla no existe en maestro";
                break;
            case 4:
                $msjUsuario = "Tiene programado cambio de precion a ${$iPrecioNuevo} favor de recibirlo como mercancia no surtible";
                break;
            case 5:
                $msjUsuario = "Tiene programado cambio de precion a ${$iPrecioNuevo}  marca la etiqueta para que despues hagas un reetiquetado con etiquetas BLANCAS";
                $bRegresa = true;
                break;
            case 6:
                $msjUsuario = "Este codigo-talla tiene un precion MAYOR al maximo permitido, REVISAR";
                break;
            case 7:
                $msjUsuario = "Tiene programado cambio de precion a ${$iPrecioNuevo} marca la etiqueta para que despues hagas un reetiquetado con etiquetas AMARILLAS al precio que te dice la computadora";
                $bRegresa = true;
                break;
            default:
                $status = 500;
            break;
        }

        $return = array(
            "bRegresa" => $bRegresa,
            "mensaje" => $msjUsuario,
            "status" => $status
        )

        return $return;
    }
}
