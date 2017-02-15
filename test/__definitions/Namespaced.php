<?php

\Maleficarum\Ioc\Container::register('Namespaced\Included\Via\Definitions\File', function () {
    $object = new \stdClass;
    $object->namespaced_included = true;

    return $object;
});

\Maleficarum\Ioc\Container::register('Namespaced', function () {
    $object = new \stdClass;
    $object->namespaced_global = true;

    return $object;
});
