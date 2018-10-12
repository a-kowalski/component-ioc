<?php

\Maleficarum\Ioc\Container::registerBuilder('Namespaced', function ($dep, $opts) {
    $object = isset($opts['__instance']) ? $opts['__instance'] : new \stdClass;
    $object->namespaced_global = true;
    $object->class_name = $opts['__class'];

    return $object;
});