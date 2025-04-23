<?php

namespace Coppel\Pck001Back\Models;

use Phalcon\DI\DI;
use Phalcon\Mvc\Model;

class ApiModel extends Model
{
    public function holaMundo()
    {
        $db = DI::getDefault()->get('conexion');
        $query = "SELECT 'hola mundo!' AS saludo, NOW() AS fecha;";

        $statement = $db->prepare($query);
        $statement->execute();

        return $statement->fetch();
    }
}
