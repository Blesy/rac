<?php

namespace Coppel\RAC\Modules;

use PDO;
use Phalcon\DI\DI;
use Phalcon\Mvc\Micro\Collection;

use Coppel\RAC\Utils\EncryptionUtil;

class Module implements IModule
{
    public function registerLoader($loader)
    {
        $loader->setNamespaces([
            'Coppel\Pck001Back\Controllers' => __DIR__ . '/controllers/',
            'Coppel\Pck001Back\Models' => __DIR__ . '/models/',
            'Coppel\RAC\Utils' => __DIR__ . '/utils/'
        ], true);
    }

    public function getCollections()
    {
        $collection = new Collection();

        $lector = new Collection();

        $lector->setPrefix('/api/lector')
            ->setHandler('\Coppel\Pck001Back\Controllers\LectorController')
            ->setLazy(true);

        $collection->setPrefix('/api')
            ->setHandler('\Coppel\Pck001Back\Controllers\ApiController')
            ->setLazy(true);

        $collection->get('/ejemplo', 'holaMundo');
        $lector->post('/validarCambioPrecio', 'ValidaCambioPrecio');
        $lector->post('/borrarRegistro', 'borrarRegistro');
        $lector->post('/validar','validarLector');
        // postman localhost/api/lector/validar  POST , validarLector()

        // curl -X POST localhost/api/lector/validar '{numLector:9912839213}'
        return [
            $collection,
            $lector
        ];
    }

    public function registerServices()
    {
        $di = DI::getDefault();

        $di->set('conexion', function () use ($di) {
            $config = $di->get('config')->db;
            // Instanciar el servicio de encriptación
            $encryptionService = new EncryptionUtil();
            // Llamar a getEnc
            $pwd = $encryptionService->getEnc($config->ENC_STR, $config->KEY_STR, $config->IV_STR);

            return new PDO(
                "pgsql:host={$config->host};port={$config->port};dbname={$config->dbname};",
                $config->username,
                $pwd,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]
            );
        });

        $di->set('logger', function () {
            return new \Katzgrau\KLogger\Logger('logs');
        });
    }
}
