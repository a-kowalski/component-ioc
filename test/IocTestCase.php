<?php
/**
 * This is a "common ground" class for all test suites for this project.
 */
namespace Maleficarum\Ioc\Test;

class IocTestCase extends \PHPUnit\Framework\TestCase {
	/**
	 * ATTRIBUTES
	 */

	private static $context = null;
	
	/**
	 * FIXTURES
	 */
	
	/**
	 * Executed before each test suite begins.
	 */
	public function setUp() {
		// set current context
		$this->setContext($this->getName());
		
		// attempt to load suite specific ioc defs
		$path = SRC_PATH . DIRECTORY_SEPARATOR . 'Ioc' . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, str_replace('Test\\', '', static::class)).'.ioc.php';
		if (is_readable($path)) {
			require $path;
		}
	}

	/**
	 * Executed after each test suite gets done.
	 */
	public static function tearDownAfterClass() {
		// clean up any ioc defs that were created by this test suite
		$initializers = new \ReflectionProperty("Maleficarum\Ioc\Container", "initializers");
		$initializers->setAccessible(true);
		$initializers->setValue([]);
		$initializers->setAccessible(false);

		$dependencies = new \ReflectionProperty("Maleficarum\Ioc\Container", "dependencies");
		$dependencies->setAccessible(true);
		$dependencies->setValue([]);
		$dependencies->setAccessible(false);

		$loadedDefinitions = new \ReflectionProperty("Maleficarum\Ioc\Container", "loadedDefinitions");
		$loadedDefinitions->setAccessible(true);
		$loadedDefinitions->setValue([]);
		$loadedDefinitions->setAccessible(false);

		$namespaces = new \ReflectionProperty("Maleficarum\Ioc\Container", "namespaces");
		$namespaces->setAccessible(true);
		$namespaces->setValue([]);
		$namespaces->setAccessible(false);
	}

	/**
	 * HELPERS
	 */
	
	protected function getContext() {
		return self::$context;
	}
	
	protected function setContext($context) {
		self::$context = $context;
		return $this;
	}
}