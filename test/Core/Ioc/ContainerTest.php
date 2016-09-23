<?php
/**
 * Tests for the \Maleficarum\Ioc\Container class.
 */

namespace Maleficarum\Ioc\Test;

class ContainerTest extends \Maleficarum\Ioc\Test\IocTestCase {
	/**
	 * FIXTURES
	 */
	
	public static function setUpBeforeClass() {
		// execute parent functionality
		parent::setUpBeforeClass();
		
		// register a test dependency
		$object = new \stdClass;
		$object->dependency = true;

		\Maleficarum\Ioc\Container::addNamespace('Default', SRC_PATH . DIRECTORY_SEPARATOR . 'Ioc');
		\Maleficarum\Ioc\Container::registerDependency('Default\Registered\Dependency', $object);
		
		// register a test class builder
		\Maleficarum\Ioc\Container::register('Default\Return\Std\Class\With\Values', function($dep, $opts) {
			$object = new \stdClass;
			$object->testValueString = 'string';
			$object->testValueInteger = 1;
			isset($opts['injectedValue']) and $object->injectedValue = $opts['injectedValue'];
			isset($dep['Default\Registered\Dependency']) and $object->iocDependency = $dep['Default\Registered\Dependency'];
			
			return $object;
		});
	}

	/**
	 * TESTS
	 */
	
	/** METHOD: \Maleficarum\Ioc\Container::addNamespace() */

	public function testValidNamespace() {
        \Maleficarum\Ioc\Container::addNamespace('foo', 'bar');

        $definedNamespaces = $this->getDefinedNamespaces();

        $this->assertArrayHasKey('foo', $definedNamespaces);
        $this->assertEquals('bar', $definedNamespaces['foo']);
	}

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

    public function invalidDataProvider()
    {
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

    private function getDefinedNamespaces()
    {
        $namespaces = new \ReflectionProperty('Maleficarum\Ioc\Container', 'namespaces');
        $namespaces->setAccessible(true);
        $definedNamespaces = $namespaces->getValue();
        $namespaces->setAccessible(false);

        return $definedNamespaces;
    }

	/** METHOD: \Maleficarum\Ioc\Container::register() */
	
	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testRegisterIncorrectName() {
		\Maleficarum\Ioc\Container::register(null, function() {return true;});
	}

	/**
	 * @expectedException \PHPUnit_Framework_Error
	 */
	public function testRegisterIncorrectClosure() {
		\Maleficarum\Ioc\Container::register('testClass', null);
	}
	
	/** METHOD: \Maleficarum\Ioc\Container::get() */
	
	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testGetWithIncorrectName() {
		\Maleficarum\Ioc\Container::get(null);
	}

	public function testGetDefault() {
		$this->assertInstanceOf('stdClass', \Maleficarum\Ioc\Container::get('stdClass'));
	}
	
	public function testGetWithValues() {
		$object = \Maleficarum\Ioc\Container::get('Default\Return\Std\Class\With\Values');
		$this->assertInstanceOf('stdClass', $object);
		$this->assertSame($object->testValueString, 'string');
		$this->assertSame($object->testValueInteger, 1);
	}
	
	public function testGetWithInjectedValues() {
		$object = \Maleficarum\Ioc\Container::get('Default\Return\Std\Class\With\Values', ['injectedValue' => true]);
		$this->assertInstanceOf('stdClass', $object);
		$this->assertSame($object->injectedValue, true);
	}
	
	public function testGetWithInjectedDependency() {
		$object = \Maleficarum\Ioc\Container::get('Default\Return\Std\Class\With\Values');
		$this->assertInstanceOf('stdClass', $object->iocDependency);
		$this->assertSame($object->iocDependency->dependency, true);
	}
	
	public function testGetIncludedViaFile() {
		$object = \Maleficarum\Ioc\Container::get('Default\Included\Via\Definitions\File');
		$this->assertInstanceOf('stdClass', $object);
		$this->assertSame($object->included, true);
	}
	
	public function testGetCreatedByParent() {
		$object = \Maleficarum\Ioc\Container::get('Default\Non\Existent\Builder\With\Parent');
		$this->assertInstanceOf('stdClass', $object);
		$this->assertSame($object->global, true);
	}

	/** METHOD: \Maleficarum\Ioc\Container::isRegistered() */
	
	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testIsregisteredWithIncorrectName() {
		\Maleficarum\Ioc\Container::isRegistered(null);
	}
	
	public function testIsregisteredWithFalseResult() {
		$this->assertFalse(\Maleficarum\Ioc\Container::isRegistered(uniqid()));
	}
	
	public function testIsregisteredWithTrueResult() {
		$this->assertTrue(\Maleficarum\Ioc\Container::isRegistered('Default\Return\Std\Class\With\Values'));
	}
}