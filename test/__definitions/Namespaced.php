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

\Maleficarum\Ioc\Container::register('Namespaced\Appended', function() {
    $object = new \StdClass;
    $object->namespaced_appended = false;
    $object->appendCount = 0;
    
    return $object;
});

\Maleficarum\Ioc\Container::append('Namespaced\Appended', function($dep, $opts) {
    $object = $opts['__instance'];
    $object->namespaced_appended = true;
    $object->appendCount++;

    return $object;
});

\Maleficarum\Ioc\Container::append('Namespaced\Appended', function($dep, $opts) {
    $object = $opts['__instance'];
    $object->appendCount++;

    return $object;
});