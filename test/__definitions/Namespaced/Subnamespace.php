<?php

\Maleficarum\Ioc\Container::registerBuilder('Namespaced\Subnamespace', function ($dep, $opts) {
    $object = isset($opts['__instance']) ? $opts['__instance'] : new \stdClass;
    $object->namespace_subnamespace = true;
    $object->class_name = $opts['__class'];

    return $object;
});

\Maleficarum\Ioc\Container::registerBuilder('Namespaced\Subnamespace\TestClass', function ($dep, $opts) {
    $object = isset($opts['__instance']) ? $opts['__instance'] : new \stdClass;
    $object->namespace_subnamespace_testClass = true;
    $object->class_name = $opts['__class'];

    return $object;
});