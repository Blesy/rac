<?php

namespace Coppel\Pck001Back\Models;

use Phalcon\DI\DI;
use Phalcon\Mvc\Model;

class LectorModel extends Model
{
    public function validarLector($numLector)
    {
        $db = DI::getDefault()->get('conexion');
        $query = "SELECT 'hola mundo!' AS saludo, NOW() AS fecha;";

        $statement = $db->prepare($query);
        $statement->execute();

        return $statement->fetch();
    }

}
