<?php

	\Maleficarum\Ioc\Container::register('Asdfg\Zxcvb', function() {
		$object = new \stdClass;
		$object->qwerty = true;
	
		return $object;
	});
