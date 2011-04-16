<?php

namespace ActiveMongo\Cursor;

use \ActiveMongo\Exception;

abstract class ActiveMongo\Cursor\Interface implements Iterator
{
    abstract public function __construct($collection, $query);

    function getReference($class)
    {
        throw new Exception("This cursor doesn't support reference");
    }

}
