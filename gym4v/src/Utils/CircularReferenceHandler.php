<?php

namespace App\Utils;

class CircularReferenceHandler
{
    public function handle($object)
    {
        return method_exists($object, 'getId') ? $object->getId() : null;
    }
}

