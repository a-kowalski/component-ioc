<?php
declare(strict_types = 1);

/**
 * Tests for the \Maleficarum\Ioc\Container class.
 */

namespace Maleficarum\Ioc\Tests;

class ContainerTest extends \PHPUnit\Framework\TestCase
{
    /* ------------------------------------ Fixtures START --------------------------------------------- */
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

        // test dependency
        \Maleficarum\Ioc\Container::registerDependency('Registered\Dependency', $object);

        // register a test class builder
        \Maleficarum\Ioc\Container::register('Registered\Return\Std\Class\With\Values', function ($dep, $opts) {
            $object = new \stdClass;
            $object->testValueString = 'string';
            $object->testValueInteger = 1;
            isset($opts['injectedValue']) and $object->injectedValue = $opts['injectedValue'];
            isset($dep['Registered\Dependency']) and $object->iocDependency = $dep['Registered\Dependency'];

            return $object;
        });
    }
    /* ------------------------------------ Fixtures END ----------------------------------------------- */

    /* ------------------------------------ Method: registerDependency START --------------------------- */
    /**
     * @expectedException \RuntimeException
     */
    public function testRegisterDependencyDuplicatedName() {
        \Maleficarum\Ioc\Container::registerDependency('foo', []);
        \Maleficarum\Ioc\Container::registerDependency('foo', []);
    }
    /* ------------------------------------ Method: registerDependency END ----------------------------- */

    /* ------------------------------------ Method: setDefaultBuilders START --------------------------- */
    /**
     * @expectedException \RuntimeException
     */
    public function testSetDefaultBuildersWithDuplicatePath() {
        \Maleficarum\Ioc\Container::setDefaultBuilders('./test');
    }
    /* ------------------------------------ Method: setDefaultBuilders END ----------------------------- */

    /* ------------------------------------ Method: addNamespace START --------------------------------- */
    /**
     * @expectedException \RuntimeException
     */
    public function testExistingNamespace() {
        \Maleficarum\Ioc\Container::addNamespace('foo', 'bar');
        \Maleficarum\Ioc\Container::addNamespace('foo', 'bar');
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testRegisterDuplicatedName() {
        \Maleficarum\Ioc\Container::register('foo', function () {
            return true;
        });

        \Maleficarum\Ioc\Container::register('foo', function () {
            return true;
        });
    }
    /* ------------------------------------ Method: addNamespace END ----------------------------------- */

    /* ------------------------------------ Method: get START ------------------------------------------ */
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
    
    public function testGetAppended() {
        $object = \Maleficarum\Ioc\Container::get('Namespaced\Appended');
        $this->assertInstanceOf('stdClass', $object);
        $this->assertSame($object->namespaced_appended, true);
    }

    public function testGetAppendedCounter() {
        $object = \Maleficarum\Ioc\Container::get('Namespaced\Appended');
        $this->assertInstanceOf('stdClass', $object);
        $this->assertSame($object->appendCount, 2);
    }
    /* ------------------------------------ Method: get END -------------------------------------------- */

    /* ------------------------------------ Method: append START --------------------------------------- */
    /**
     * @expectedException \RuntimeException
     */
    public function testAppendWithoutMain() {
        \Maleficarum\Ioc\Container::append(uniqid(), function() {});
    }
    /* ------------------------------------ Method: append END ----------------------------------------- */
    
    /* ------------------------------------ Method: isRegistered START --------------------------------- */
    public function testIsRegisteredWithFalseResult() {
        $this->assertFalse(\Maleficarum\Ioc\Container::isRegistered(uniqid()));
    }

    public function testIsRegisteredWithTrueResult() {
        $this->assertTrue(\Maleficarum\Ioc\Container::isRegistered('Registered\Return\Std\Class\With\Values'));
    }
    /* ------------------------------------ Method: isRegistered END ----------------------------------- */
}
