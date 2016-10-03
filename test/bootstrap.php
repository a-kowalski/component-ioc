<?php

/**
 * Define any consts that are used by the tested code.
 */
define('SRC_PATH', realpath(dirname(__FILE__)));

/**
 * Add the default worker test case to use within this test suite.
 */
require_once SRC_PATH . DIRECTORY_SEPARATOR . '__bootstrap/TestCase.php';