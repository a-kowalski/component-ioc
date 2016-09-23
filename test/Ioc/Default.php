<?php

	\Maleficarum\Ioc\Container::register('Default\Included\Via\Definitions\File', function() {
		$object = new \stdClass;
		$object->included = true;
		
		return $object;
	});
	
	\Maleficarum\Ioc\Container::register('Default', function() {
		$object = new \stdClass;
		$object->global = true;
	
		return $object;
	});