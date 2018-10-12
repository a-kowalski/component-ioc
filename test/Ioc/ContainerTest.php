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
       
        // test namespaced builder file
        \Maleficarum\Ioc\Container::addNamespace('Namespaced', SRC_PATH . DIRECTORY_SEPARATOR . '__definitions');
        \Maleficarum\Ioc\Container::addNamespace('Namespaced\Subnamespace', SRC_PATH . DIRECTORY_SEPARATOR . '__definitions');

        // test dependency
        $object = new \stdClass;
        $object->dependency = true;
        
        \Maleficarum\Ioc\Container::registerShare('Registered\Share', $object);
        
        // test builder
        \Maleficarum\Ioc\Container::registerBuilder('Registered\Return\Std\Class\With\Values', function($dep, $opts){
            $class = new \stdClass();
            $class->marker = true;
            
            isset($opts['injectedValue']) and $class->injectedValue = $opts['injectedValue'];
            
            return$class;
        });
    }
    /* ------------------------------------ Fixtures END ----------------------------------------------- */

    /* ------------------------------------ Method: isBuilderRegistered START -------------------------- */
    public function testIsRegisteredWithFalseResult() {
        $this->assertFalse(\Maleficarum\Ioc\Container::isBuilderRegistered(uniqid()));
    }

    public function testIsRegisteredWithTrueResult() {
        $this->assertTrue(\Maleficarum\Ioc\Container::isBuilderRegistered('Registered\Return\Std\Class\With\Values'));
    }
    /* ------------------------------------ Method: isBuilderRegistered END ---------------------------- */

    /* ------------------------------------ Method: registerBuilder START ------------------------------ */
    /**
     * @expectedException \RuntimeException
     */
    public function testRegisterDuplicatedName() {
        \Maleficarum\Ioc\Container::registerBuilder('foo', function () { return true; });
        \Maleficarum\Ioc\Container::registerBuilder('foo', function () { return true; });
    }
    /* ------------------------------------ Method: registerBuilder END -------------------------------- */
    
    /* ------------------------------------ Method: registerShare START -------------------------------- */
    /**
     * @expectedException \RuntimeException
     */
    public function testRegisterDependencyDuplicatedName() {
        \Maleficarum\Ioc\Container::registerShare('foo', []);
        \Maleficarum\Ioc\Container::registerShare('foo', []);
    }
    /* ------------------------------------ Method: registerShare END ---------------------------------- */

    /* ------------------------------------ Method: retrieveShare START -------------------------------- */
    /**
     * @expectedException \RuntimeException
     */
    public function testRetrieveShareNoShare() {
        \Maleficarum\Ioc\Container::retrieveShare('bar');
    }
    
    public function testRetrieveShareSuccess() {
        $this->assertInstanceOf('stdClass', \Maleficarum\Ioc\Container::retrieveShare('Registered\Share'));
    }
    
    /* ------------------------------------ Method: retrieveShare END ---------------------------------- */
        
    /* ------------------------------------ Method: addNamespace START --------------------------------- */
    /**
     * @expectedException \RuntimeException
     */
    public function testExistingNamespace() {
        \Maleficarum\Ioc\Container::addNamespace('foo', 'bar');
        \Maleficarum\Ioc\Container::addNamespace('foo', 'bar');
    }
    /* ------------------------------------ Method: addNamespace END ----------------------------------- */
    
    /* ------------------------------------ Method: get START ------------------------------------------ */
    public function testGetNonBuilderClassWithoutConstructorParameters() {
        $this->assertInstanceOf('stdClass', \Maleficarum\Ioc\Container::get('stdClass'));
    }

    public function testGetNonBuilderClassWithConstructorParameters() {
        $dt = \Maleficarum\Ioc\Container::get('DateTime', ['2010-10-10']);
        $this->assertInstanceOf('DateTime', $dt);
        $this->assertSame('2010-10-10', $dt->format('Y-m-d'));
    }
    
    public function testGetDirectlyRegisteredBuilderClassWithoutInjectedValue() {
        $test = \Maleficarum\Ioc\Container::get('Registered\Return\Std\Class\With\Values');
        $this->assertInstanceOf('stdClass', $test);
        $this->assertSame(true, $test->marker);
        $this->assertFalse(property_exists($test,'injectedValue'));
    }
    
    public function testGetDirectlyRegisteredBuilderClassWithInjectedValue() {
        $test = \Maleficarum\Ioc\Container::get('Registered\Return\Std\Class\With\Values', ['injectedValue' => 'testInjectedValue']);
        $this->assertInstanceOf('stdClass', $test);
        $this->assertTrue($test->marker);
        $this->assertSame('testInjectedValue', $test->injectedValue);
    }
    
    public function testGetNamespacedTopLevelBuilder() {
        $test = \Maleficarum\Ioc\Container::get('Namespaced');
        $this->assertTrue($test->namespaced_global);
        $this->assertSame('Namespaced', $test->class_name);
        $this->assertFalse(property_exists($test,'namespace_subnamespace'));
        $this->assertFalse(property_exists($test,'namespace_subnamespace_testClass'));
    }
    
    public function testGetNamespacedFullBuilderTreeExecution() {
        $test = \Maleficarum\Ioc\Container::get('Namespaced\Subnamespace\TestClass');
        $this->assertTrue($test->namespaced_global);
        $this->assertSame('Namespaced\Subnamespace\TestClass', $test->class_name);
        $this->assertTrue($test->namespace_subnamespace);
        $this->assertTrue($test->namespace_subnamespace_testClass);
    }
    
    public function testGetNamespacedExactMatchOnly() {
        $test = \Maleficarum\Ioc\Container::get('Namespaced\Subnamespace\TestClass', [], true);
        $this->assertTrue($test->namespace_subnamespace_testClass);
        $this->assertSame('Namespaced\Subnamespace\TestClass', $test->class_name);
        $this->assertFalse(property_exists($test,'namespace_subnamespace'));
        $this->assertFalse(property_exists($test,'namespaced_global'));
    }
    /* ------------------------------------ Method: get END -------------------------------------------- */
}
