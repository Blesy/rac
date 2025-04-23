<?php

namespace Coppel\RAC\Utils;

use Phalcon\DI\DI;

class EncryptionUtil
{
    public function onConstruct()
    {
    }


    public function getEnc($enString, $keyString, $ivstring)
    {
        $di = DI::getDefault();

        $url = $di->get('config')->ENSERVICE . "/decrypt";
        $logger = new \Katzgrau\KLogger\Logger('logs');


        if (empty($url)) {
            throw new \Exception("No se encontró el valor del URL");
        }
       
        $data = [
            "encryptedString" => $enString,
            "keyString" => $keyString,
            "ivstring" => $ivstring,
        ];


        $jsonBody = false; 
        try{
            $jsonBody = json_encode($data);

        }catch(\Exception $ex){
            throw new \Exception("No se pudo codificar la data: ". $data);
        }
        
        if ($jsonBody === false) {
            throw new \Exception("Error al codificar JSON");
        }

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonBody);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json",
        ]);


        $response = curl_exec($ch);
        if ($response === false) {
            $error = curl_error($ch);
            curl_close(handle: $ch);
            $logger->error($error); 

            throw new \Exception("Error al realizar la solicitud: " . $error);
        }

        curl_close($ch);

        $dataResp = json_decode($response, true);


        if ($dataResp === null && json_last_error() !== JSON_ERROR_NONE) {
            $logger->error(json_last_error_msg()); 
            throw new \Exception("Error al decodificar JSON ");
        }

        if (!isset($dataResp['data'])) {
            throw new \Exception("No se encontró el campo 'data' en la respuesta: " . $response);
        }
        return $dataResp['data'];
    }
}

