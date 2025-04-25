<?php

namespace Coppel\Pck001Back\Models;

use InvalidArgumentException;
use Phalcon\DI\DI;
use Phalcon\Mvc\Model;

class LectorModel extends Model
{
    private $logger;

    public function onConstruct()
    {
        $this->logger = DI::getDefault()->get('logger');
    }
    public function validarLector($numLector)
    {
        $db = DI::getDefault()->get('conexion');
        $query = "SELECT 'hola mundo!' AS saludo, NOW() AS fecha;";

        $statement = $db->prepare($query);
        $statement->execute();

        return $statement->fetch();
    }

    public function ValidaCambioPrecio($sCodigo, $sTalla, $sPrecio, $sTipo)
    {

        $bRegresa = false;
        $status = 200;
        $cConsulta = [];
        $cMsjUsuario = [];
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

        if ($fetchData) {
            $iPrecioNuevo = $fetchData->tipoguia;
            $iError = $fetchData->cveactualiza;
            $iCodigoAcum = $fetchData->codigoacum;
            $iTallaAcum = $fetchData->tallaacum;
        }

        switch ($iError) {
            case 0:
                $bRegresa = true;
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


        return [
            "bRegresa" => $bRegresa,
            "mensaje" => $msjUsuario,
            "status" => $status
        ];

    }
    public function borrarRegistro($sSeccion, $sPosicion, $Terminal): bool //Pendiente de probar 
    {
        $query = "select pck001Borracodigo('$sSeccion','$sPosicion','$Terminal')";

        $db = DI::getDefault()->get('conexion');

        $statement = $db->prepare($query);
        $statement->execute();

        $fetchData = $statement->fetch();

        if ($fetchData == false) {
            throw new \Exception("Error al borrar registro, Consulta # 2.");
        }

        if ($fetchData['pck001Borracodigo'] == 1) {
            $this->logger->error($query);
            return false;
        }
        return true;
    }


    /**
     * Función paraotener el identificador del empleado ( previa idenNumEmpleado)
     */
    public function obtenerIdFEmpleado($idEmpleado, $numEmpleado) 
    {
        $condicion = '';
        if ($idEmpleado) {
            $condicion = "identificacion = $idEmpleado";
        } else {
            $condicion = "numEmpleado = $numEmpleado";
        }
        $query = "select identificacion, nomempleado, numempleado FROM maeempleados WHERE " . $condicion;


        $db = DI::getDefault()->get('conexion');

        $statement = $db->prepare($query);
        $statement->execute();
        $datos = $statement->fetch();

        
        if ($datos == false) {
            throw new \Exception("Error obtener los datos");
            
        }
         if (!$datos['nomempleado'] || $datos['nomempleado'] == "" ) {
            throw new \Exception(" - No se encontraron los datos del usuario ( id: $idEmpleado , numEmpleado: $numEmpleado)");   
         }
        
        
        $datosEmpleado = [
            "nomEmpleado" => $datos['nomempleado']
            ,
            'idEmpleado' => $datos['identificacion']
            ,
            'numEmpleado' => $datos['numempleado']
        ];


       
        return $datosEmpleado;
    }
    public function buscarGuiaTraspaso( $cGuia): bool  //No hay datos en bodega sobre la tabla que consume movGuiaTraspasos
    {
        $query = "select nomGuia from movGuiaTraspasos where btrim(nomguia)=('$cGuia')";

        $db = DI::getDefault()->get('conexion');

        $statement = $db->prepare($query);

        try{
        $statement->execute();

        $fetchData = $statement->fetch();

        if ($fetchData == false) {
            throw new \Exception( $query ."No existe informacion de este traspaso para realizar su recepcion, favor de checar");
        }

        if ($fetchData['nomguia'] ) {
            return true;
        }
        return false;
        }
        catch (\Exception $e) {
            $this->logger->error(''. $e->getMessage());
            throw new \Exception("Error al ejecutar la consulta #14");  

        }
        
       
    }

    public function buscarGuiaTransferencia( $cGuia): bool  
    {
        $query = "select nomGuia from movGuiaTransferencias where btrim(nomguia)=('$cGuia')";

        $db = DI::getDefault()->get('conexion');

        $statement = $db->prepare($query);
       
        try{

            $statement->execute();

            $fetchData = $statement->fetch();
            
            if ($fetchData == false) {
                throw new \Exception( $query ."No existe informacion de este transferencia para realizar su recepcion, favor de checar");
            }

            if ($fetchData['nomguia'] ) {
                return true;
            }
            return false;

        }
        catch(\Exception $e){
            $this->logger->error(''. $e->getMessage());
            throw new \Exception("Error al ejecutar la consulta #15");     
        }

     
    }
    public function buscarGuiaCrossBodega( $cGuia): bool  //sin informacion en movGuiaCrossBodega
    {
        
        $query = "select nomGuia from movGuiaCrossBodega where btrim(nomguia)=('$cGuia')";

        $db = DI::getDefault()->get('conexion');

        $statement = $db->prepare($query);
       
        try{

            $statement->execute();

            $fetchData = $statement->fetch();
            
            if ($fetchData == false) {
                throw new \Exception( $query ."No existe informacion de este crossdocking para realizar su recepcion, favor de checar");
            }

            if ($fetchData['nomguia'] ) {
                return true;
            }
            return false;

        }
        catch(\Exception $e){
            $this->logger->error(''. $e->getMessage());
            throw new \Exception("Error al ejecutar la consulta #16");     
        }

     
    }
    public function obtenerBodEnvia( $cLetra): array
    {
         
        $query = "select numbodega from catbodegas where letrabodega=('$cLetra')";

        $db = DI::getDefault()->get('conexion');
          
        $statement = $db->prepare($query);
        $statement->execute();
        $datos = $statement->fetch();

        try{
        if ($datos == false) {
            throw new InvalidArgumentException("Ingresar una letra correcta");
        }        
        }
        catch(\Exception $e ){
            $this->logger->error(''. $e->getMessage());
            throw new \Exception("Error al ejecutar consulta # 23");     
        }
        catch(InvalidArgumentException $e) {
            throw new InvalidArgumentException("Ingresar una letra correcta");

        }
        $datosBodega = [
            "Bodega" => $datos['numbodega']
           
        ];
        


       
        return $datosBodega;
     
    }
    

}
