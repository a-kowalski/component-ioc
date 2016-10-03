<?php
/**
 * Tests for the \Maleficarum\Ioc\Container class.
 */

namespace Maleficarum\Ioc\Test;

use Maleficarum\Ioc\Container;

class ContainerTest extends \Maleficarum\Ioc\Test\TestCase {
	/**
	 * FIXTURES
	 */
	
	public static function setUpBeforeClass() {
		// execute parent functionality
		parent::setUpBeforeClass();
		
		// register a test dependency
		$object = new \stdClass;
		$object->dependency = true;

		// test default builder file
		\Maleficarum\Ioc\Container::setDefaultBuilders(SRC_PATH . DIRECTORY_SEPARATOR . '__definitions' . DIRECTORY_SEPARATOR . 'Default.php');
		
		// test namespaced builder file
		\Maleficarum\Ioc\Container::addNamespace('Namespaced', SRC_PATH . DIRECTORY_SEPARATOR . '__definitions');
		
		// test tependecy
		\Maleficarum\Ioc\Container::registerDependency('Registered\Dependency', $object);
		
		// register a test class builder
		\Maleficarum\Ioc\Container::register('Registered\Return\Std\Class\With\Values', function($dep, $opts) {
			$object = new \stdClass;
			$object->testValueString = 'string';
			$object->testValueInteger = 1;
			isset($opts['injectedValue']) and $object->injectedValue = $opts['injectedValue'];
			isset($dep['Registered\Dependency']) and $object->iocDependency = $dep['Registered\Dependency'];
			
			return $object;
		});
	}

	/**
	 * DATA PROVIDERS
	 */

	public function invalidDataProvider() {
		return [
			[null],
			[[]],
			[(new \stdClass())],
			[true],
			[false],
			[0],
			[1.0]
		];
	}
	
	/**
	 * TESTS
	 */

	/** METHOD: \Maleficarum\Ioc\Container::registerDependency() */

	/**
	 * @expectedException \InvalidArgumentException
	 * @dataProvider invalidDataProvider
	 */
	public function testRegisterDependencyWithIncorrectName($name) {
		\Maleficarum\Ioc\Container::registerDependency($name, []);
	}

	/** METHOD: \Maleficarum\Ioc\Container::setDefaultBuilders() */

	/**
	 * @expectedException \InvalidArgumentException
	 * @dataProvider invalidDataProvider
	 */
	public function testSetDefaultBuildersWithIncorrectPath($path) {
		\Maleficarum\Ioc\Container::setDefaultBuilders($path);
	}

	/**
	 * @expectedException \RuntimeException
	 */
	public function testSetDefaultBuildersWithDuplicatePath() {
		\Maleficarum\Ioc\Container::setDefaultBuilders('./test');
	}
	
	/** METHOD: \Maleficarum\Ioc\Container::addNamespace() */
	
    /**
     * @expectedException \RuntimeException
     */
    public function testExistingNamespace() {
        \Maleficarum\Ioc\Container::addNamespace('foo', 'bar');
        \Maleficarum\Ioc\Container::addNamespace('foo', 'bar');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @dataProvider invalidDataProvider
     */
    public function testInvalidNamespace($ns) {
        \Maleficarum\Ioc\Container::addNamespace($ns, 'bar');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @dataProvider invalidDataProvider
     */
    public function testInvalidPath($path) {
        \Maleficarum\Ioc\Container::addNamespace('foo', $path);
    }

	/** METHOD: \Maleficarum\Ioc\Container::register() */
	
	/**
	 * @expectedException \InvalidArgumentException
	 * @dataProvider invalidDataProvider
	 */
	public function testRegisterIncorrectName($name) {
		\Maleficarum\Ioc\Container::register($name, function() {return true;});
	}

	/**
	 * @expectedException \TypeError
	 * @dataProvider invalidDataProvider
	 */
	public function testRegisterIncorrectClosure($builder) {
		\Maleficarum\Ioc\Container::register('testClass', $builder);
	}
	
	/** METHOD: \Maleficarum\Ioc\Container::get() */
	
	/**
	 * @expectedException \InvalidArgumentException
	 * @dataProvider invalidDataProvider
	 */
	public function testGetWithIncorrectName($name) {
		\Maleficarum\Ioc\Container::get($name);
	}

	public function testGetDefault() {
		$this->assertInstanceOf('stdClass', \Maleficarum\Ioc\Container::get('stdClass'));
	}
	
	public function testGetWithValues() {
		$object = \Maleficarum\Ioc\Container::get('Registered\Return\Std\Class\With\Values');
		$this->assertInstanceOf('stdClass', $object);
		$this->assertSame($object->testValueString, 'string');
		$this->assertSame($object->testValueInteger, 1);
	}
	
	public function testGetWithInjectedValues() {
		$object = \Maleficarum\Ioc\Container::get('Registered\Return\Std\Class\With\Values', ['injectedValue' => true]);
		$this->assertInstanceOf('stdClass', $object);
		$this->assertSame($object->injectedValue, true);
	}
	
	public function testGetWithInjectedDependency() {
		$object = \Maleficarum\Ioc\Container::get('Registered\Return\Std\Class\With\Values');
		$this->assertInstanceOf('stdClass', $object->iocDependency);
		$this->assertSame($object->iocDependency->dependency, true);
	}
	
	public function testGetClassDefinedByNamespacedBuilder() {
		$object = \Maleficarum\Ioc\Container::get('Namespaced\Included\Via\Definitions\File');
		$this->assertInstanceOf('stdClass', $object);
		$this->assertSame($object->namespaced_included, true);
	}
	
	public function testGetParentDefinedByNamespacedBuilder() {
		$object = \Maleficarum\Ioc\Container::get('Namespaced\Non\Existent\Builder\With\Parent');
		$this->assertInstanceOf('stdClass', $object);
		$this->assertSame($object->namespaced_global, true);
	}

	public function testGetClassDefinedByDefaultBuilder() {
		$object = \Maleficarum\Ioc\Container::get('Default\Included\Via\Definitions\File');
		$this->assertInstanceOf('stdClass', $object);
		$this->assertSame($object->default_included, true);
	}

	public function testGetParentDefinedByDefaultBuilder() {
		$object = \Maleficarum\Ioc\Container::get('Default\Non\Existent\Builder\With\Parent');
		$this->assertInstanceOf('stdClass', $object);
		$this->assertSame($object->default_global, true);
	}

	/** METHOD: \Maleficarum\Ioc\Container::isRegistered() */
	
	/**
	 * @expectedException \InvalidArgumentException
	 * @dataProvider invalidDataProvider
	 */
	public function testIsregisteredWithIncorrectName($name) {
		\Maleficarum\Ioc\Container::isRegistered($name);
	}
	
	public function testIsregisteredWithFalseResult() {
		$this->assertFalse(\Maleficarum\Ioc\Container::isRegistered(uniqid()));
	}
	
	public function testIsregisteredWithTrueResult() {
		$this->assertTrue(\Maleficarum\Ioc\Container::isRegistered('Registered\Return\Std\Class\With\Values'));
	}
}