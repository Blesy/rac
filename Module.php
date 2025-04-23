<?php

namespace Coppel\RAC\Modules;

use PDO;
use Phalcon\DI\DI;
use Phalcon\Mvc\Micro\Collection;

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
            -setLazy(true);

        $collection->setPrefix('/api')
            ->setHandler('\Coppel\Pck001Back\Controllers\ApiController')
            ->setLazy(true);

        $collection->get('/ejemplo', 'holaMundo');
        $lector->get('/validarCambioPrecio', 'ValidaCambioPrecio')
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

            return new PDO(
                "pgsql:host={$config->host};port={$config->port};dbname={$config->dbname};",
                $config->username,
                $config->password, [
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
