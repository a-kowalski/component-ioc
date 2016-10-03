<?php
/**
 * The purpose of this class is to act as a Dependency Injection service provider thus fulfilling the Inversion of Control aspect of Dependency Injection.
 */
namespace Maleficarum\Ioc;

class Container {
    /**
     * Internal storage for the default builder definition file
     * 
     * @var string
     */
    private static $defaultBuilders = null;

    /**
     * Internal storage for namespaces
     *
     * @var array
     */
    private static $namespaces = [];

    /**
     * Internal storage for object initializer closures.
     *
     * @var array
     */
    private static $initializers = [];

    /**
     * Internal storage for available dependencies.
     *
     * @var array
     */
    private static $dependencies = [];

    /**
     * Internal storage for a list of ioc definitions that we either loaded or checked for existence.
     *
     * @var array
     */
    private static $loadedDefinitions = [];

    /**
     * Register a new initializer closure.
     *
     * @param string $name
     * @param \Closure $closure
     *
     * @throws \InvalidArgumentException
     */
    public static function register($name, \Closure $closure) {
        if (!is_string($name)) {
            throw new \InvalidArgumentException('Invalid name argument - not a string. \Maleficarum\Ioc\Container::register()');
        }

        self::$initializers[$name] = $closure;
    }

    /**
     * Fetch a new instance of the specified class.
     *
     * @param string $name
     * @param array $opts
     *
     * @return object
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public static function get($name, array $opts = []) {
        // validate input
        if (!is_string($name)) {
            throw new \InvalidArgumentException('Invalid name argument - not a string. \Maleficarum\Ioc\Container::get()');
        }
        
        // fetch decremental builder names
        $name = self::reduce($name);
        $prefix = $name[count($name) - 1];
        
        // lazy-load IOC definitions for specified namespace (only once)
        if (!in_array($prefix, self::$loadedDefinitions) && isset(self::$namespaces[$prefix])) {
            require_once self::$namespaces[$prefix] . DIRECTORY_SEPARATOR . $prefix . '.php';
            self::$loadedDefinitions[] = $prefix;
        } elseif (is_string(self::$defaultBuilders) && !in_array('*', self::$loadedDefinitions)) {
            require_once self::$defaultBuilders;
            self::$loadedDefinitions[] = '*';
        }

        // attempt to execute builders
        foreach ($name as $builder) {
            if (self::isRegistered($builder)) {
                $init = self::$initializers[$builder];

                return $init(self::$dependencies, array_key_exists('__class', $opts) ? $opts : array_merge($opts, ['__class' => $name[0]]));
            }
        }

        // reaching this point means that no valid builder was found - execute generic ones
        if (!count($opts)) {
            return new $name[0]();
        } else {
            $reflection = new \ReflectionClass($name[0]);

            return $reflection->newInstanceArgs($opts);
        }
    }

    /**
     * Reduce specified name to a list of decremental namespaces.
     *
     * @param string $name
     *
     * @return array
     */
    private static function reduce($name) {
        // initialize
        $result = [];
        $name = explode('\\', $name);
        $index = count($name);

        // create handler results
        while ($index-- > 0) {
            $result[] = implode('\\', array_slice($name, 0, $index + 1));
        }

        // conclude
        return $result;
    }

    /**
     * Check if an object of the specified name can be provided by this container.
     *
     * @param string $name
     *
     * @return bool
     * @throws \InvalidArgumentException
     */
    public static function isRegistered($name) {
        if (!is_string($name)) {
            throw new \InvalidArgumentException('Invalid name argument - not a string. \Maleficarum\Ioc\Container::isRegistered()');
        }

        if (array_key_exists($name, self::$initializers)) {
            return true;
        }

        return false;
    }

    /**
     * Register a new dependency to use inside initializer closures.
     *
     * @param string $name
     * @param mixed $value
     *
     * @throws \InvalidArgumentException
     */
    public static function registerDependency($name, $value) {
        if (!is_string($name)) {
            throw new \InvalidArgumentException('Invalid name argument - not a string. \Maleficarum\Ioc\Container::registerDependency()');
        }

        self::$dependencies[$name] = $value;
    }

    /**
     * Add single namespace with path.
     *
     * @param string $ns
     * @param string $path
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public static function addNamespace($ns, $path) {
        if (!is_string($ns)) {
            throw new \InvalidArgumentException('Invalid namespace argument - not a string. \Maleficarum\Ioc\Container::addNamespace()');
        }

        if (!is_string($path)) {
            throw new \InvalidArgumentException('Invalid path argument - not a string. \Maleficarum\Ioc\Container::addNamespace()');
        }

        if (array_key_exists($ns, self::$namespaces)) {
            throw new \RuntimeException('Namespace with given name already exist. \Maleficarum\Ioc\Container::addNamespace()');
        }

        self::$namespaces[$ns] = $path;
    }
    
    /**
     * Set the path to a file with default builder definitions.
     * 
     * @param $path
     * 
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public static function setDefaultBuilders($path) {
        if (!is_string($path)) {
            throw new \InvalidArgumentException('Invalid path argument - not a string. \Maleficarum\Ioc\Container::setDefaultBuilders()');
        }

        if (!is_null(self::$defaultBuilders)) {
            throw new \RuntimeException('Default builders already set. \Maleficarum\Ioc\Container::setDefaultBuilders()');
        }
        
        self::$defaultBuilders = $path;
    }
}
