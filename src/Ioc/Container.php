<?php
/**
 * The purpose of this class is to act as a Dependency Injection service provider thus fulfilling the Inversion of Control aspect of Dependency Injection.
 */
declare (strict_types=1);

namespace Maleficarum\Ioc;

class Container {
    /* ------------------------------------ Class Property START --------------------------------------- */
    
    /**
     * This attribute stores a list of namespace definitions that should have their builder autoloaded.
     * 
     * @Example:
     * [
     *      // This\Is\A\Namespace (will load file /tmp/Ioc/This.php)
     *      'This' => '/tmp/Ioc',
     *         
     *      // That\Is\A\Namespace\As|Well (will load file /tmp/Ioc/That.php)
     *      'That => '/tmp/Ioc',
     *         
     *      // Another\Namespace\Definition (will load file /tmp/Ioc/Another/Namespace.php)
     *      'Another\Namespace' => '/tmp/Ioc'
     * ]
     * @var array
     */
    private static $namespaces = [];

    /**
     * This attribute stores all builders defined in the container. Each builder is stored under an index the represents either a full class name (with namespace)
     * or a partial namespace.
     * 
     * @Example:
     * [
     *      // full class name (with namespace)
     *      'Data\Product\Entity' => function() {},
     *         
     *      // partial class namespace (will be called for all classes that share this namespace section)
     *      'Data\Product' => function() {},
     *         
     *      // entire namespace builder (will be called for all classes in that namespace)
     *      'Data' => function() {}
     * ]
     * @var array
     */
    private static $builders = [];

    /**
     * This attributes stores all defined shared instances. Those can be registered using registerShare() and retrieved using retrieveShare().
     * 
     * @var array
     */
    private static $shares = [];

    /**
     * This attributes acts as a registry for autoloaded definitions - this is used to ensue that we do not attempt to reload any loaded builder files.
     * 
     * @var array
     */
    private static $loadedDefinitions = [];
    
    /* ------------------------------------ Class Property END ----------------------------------------- */
    
    /* ------------------------------------ Class Methods START ---------------------------------------- */

    /**
     * Check if a builder with the specified name has been registered.
     * 
     * @param string $name
     * @return bool
     */
    public static function isBuilderRegistered(string $name) : bool {
        return array_key_exists($name, self::$builders);
    }

    /**
     * Register a new builder function.
     * 
     * @param string $name
     * @param \Closure $closure
     * @return void
     */
    public static function registerBuilder(string $name, \Closure $closure) {
        if (self::isBuilderRegistered($name)) {
            throw new \RuntimeException(sprintf('Another closure with given name is already registered. \%s::registerBuilder()', static::class));
        }

        self::$builders[$name] = $closure;
    }

    /**
     * Register a new share - these will be passed to builder functions whenever one is called.
     * 
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public static function registerShare(string $name, $value) {
        if (array_key_exists($name, self::$shares)) {
            throw new \RuntimeException(sprintf('SHare with given name is already registered. \%s::registerShare()', static::class));
        }

        self::$shares[$name] = $value;
    }

    /**
     * Fetch a registered dependency.
     * 
     * @param string $name
     * @return mixed
     */
    public static function retrieveShare(string $name) {
        if (!array_key_exists($name, self::$shares)) {
            throw new \RuntimeException(sprintf('Share with given name is not registered. \%s::retrieveShare()', static::class));
        }

        return self::$shares[$name];
    }

    /**
     * Add single namespace with path.
     * 
     * @param string $ns
     * @param string $path
     * @return void
     */
    public static function addNamespace(string $ns, string $path) {
        if (array_key_exists($ns, self::$namespaces)) {
            throw new \RuntimeException(sprintf('Namespace with given name already exist. \%s::addNamespace()', static::class));
        }

        self::$namespaces[$ns] = $path;
    }
    
    /**
     * Fetch a new instance of the specified class.
     *
     * @param string $name
     * @param array $opts
     * @param bool $exactMatch
     * @return object
     */
    public static function get(string $name, array $opts = [], bool $exactMatch = false): Object {
        // fetch decremental builder names
        $nameTree = self::reduce($name);
        
        // lazy-load IOC definitions for specified namespace (only once)
        foreach ($nameTree as $ns) self::includeFile($ns);
        
        // initialize the instance
        $instance = null;
        
        // if exact match was requested - skip tree builders, just attempt to call the exact one
        $exactMatch and $nameTree = [$name];
        
        // attempt to execute builders
        foreach ($nameTree as $builder) {
            if (self::isBuilderRegistered($builder)) {
                $init = self::$builders[$builder];

                // create desired instance
                $opts = array_key_exists('__class', $opts) ? $opts : array_merge($opts, ['__class' => $name]);
                $opts['__instance'] = $instance;
                $instance = $init(self::$shares, $opts);
            }
        }

        // at this point we either have the object (builder were available) or not
        if (!is_null($instance)) {
            return $instance;
        }
        
        // reaching this point means that no valid builder was found - execute generic ones
        if (empty($opts)) {
            return new $name();
        }
        
        return (new \ReflectionClass($name))->newInstanceArgs($opts);
    }

    /**
     * Reduce specified name to a list of decremental namespaces.
     *
     * @param string $name
     * @return array
     */
    private static function reduce(string $name) : array {
        $delimiter = '\\';
        $name = explode($delimiter, $name);
        
        // create handler results
        $result = [];
        $index = 0;
        while ($index++ < count($name)) {
            $result[] = implode($delimiter, array_slice($name, 0, $index));
        }

        return $result;
    }

    /**
     * Attempt to include specified namespace builders.
     *
     * @param string $prefix
     * @return void
     */
    private static function includeFile(string $prefix) {
        if (!in_array($prefix, self::$loadedDefinitions, true) && isset(self::$namespaces[$prefix])) {
            // get the main path as defined for this namespace prefix
            $path = self::$namespaces[$prefix];
            
            // convert namespace prefix into local path 
            $localPath = str_replace('\\', \DIRECTORY_SEPARATOR, $prefix);
            
            // include builder definitions file
            require_once $path . DIRECTORY_SEPARATOR . $localPath . '.php';
            
            // mark this namespace prefix as loaded - this will make the ioc container skip file loading on subsequent calls
            self::$loadedDefinitions[] = $prefix;
        }
    }
    
    /* ------------------------------------ Class Methods END ------------------------------------------ */
}
