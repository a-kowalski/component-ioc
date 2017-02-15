<?php

\Maleficarum\Ioc\Container::register('Default\Included\Via\Definitions\File', function () {
    $object = new \stdClass;
    $object->default_included = true;

    return $object;
});

\Maleficarum\Ioc\Container::register('Default', function () {
    $object = new \stdClass;
    $object->default_global = true;

    return $object;
});
