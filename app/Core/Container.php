<?php
declare(strict_types=1);

namespace Core;

class Container {
    private static ?Container $instance = null;
    private array $bindings = [];
    private array $instances = [];
    
    public static function getInstance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function bind(string $abstract, callable $concrete): void {
        $this->bindings[$abstract] = $concrete;
    }
    
    public function singleton(string $abstract, callable $concrete): void {
        $this->bind($abstract, function ($container) use ($abstract, $concrete) {
            if (!isset($this->instances[$abstract])) {
                $this->instances[$abstract] = $concrete($container);
            }
            return $this->instances[$abstract];
        });
    }
    
    public function get(string $abstract): mixed {
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }
        
        if (isset($this->bindings[$abstract])) {
            return $this->bindings[$abstract]($this);
        }
        
        return $this->build($abstract);
    }
    
    public function has(string $abstract): bool {
        return isset($this->bindings[$abstract]) || isset($this->instances[$abstract]);
    }
    
    private function build(string $abstract): mixed {
        if (!class_exists($abstract)) {
            throw new \Exception("Class {$abstract} does not exist");
        }
        
        $reflector = new \ReflectionClass($abstract);
        
        if (!$reflector->isInstantiable()) {
            throw new \Exception("Class {$abstract} is not instantiable");
        }
        
        $constructor = $reflector->getConstructor();
        
        if ($constructor === null) {
            return new $abstract();
        }
        
        $parameters = $constructor->getParameters();
        $dependencies = [];
        
        foreach ($parameters as $parameter) {
            $type = $parameter->getType();
            
            if ($type === null || $type->isBuiltin()) {
                if ($parameter->isDefaultValueAvailable()) {
                    $dependencies[] = $parameter->getDefaultValue();
                } else {
                    throw new \Exception("Cannot resolve parameter {$parameter->getName()} in {$abstract}");
                }
            } else {
                $dependencies[] = $this->get($type->getName());
            }
        }
        
        return $reflector->newInstanceArgs($dependencies);
    }
    
    public function make(string $abstract, array $parameters = []): mixed {
        if (empty($parameters)) {
            return $this->get($abstract);
        }
        
        $reflector = new \ReflectionClass($abstract);
        $constructor = $reflector->getConstructor();
        
        if ($constructor === null) {
            return new $abstract();
        }
        
        $deps = [];
        foreach ($constructor->getParameters() as $param) {
            $name = $param->getName();
            if (array_key_exists($name, $parameters)) {
                $deps[] = $parameters[$name];
            } elseif ($param->getType() && !$param->getType()->isBuiltin()) {
                $deps[] = $this->get($param->getType()->getName());
            } elseif ($param->isDefaultValueAvailable()) {
                $deps[] = $param->getDefaultValue();
            } else {
                throw new \Exception("Cannot resolve parameter {$name}");
            }
        }
        
        return $reflector->newInstanceArgs($deps);
    }
    
    public function call(callable $callable, array $parameters = []): mixed {
        $reflection = new \ReflectionFunction($callable);
        $deps = [];
        
        foreach ($reflection->getParameters() as $param) {
            $name = $param->getName();
            
            if (array_key_exists($name, $parameters)) {
                $deps[] = $parameters[$name];
            } elseif ($param->getType() && !$param->getType()->isBuiltin()) {
                $deps[] = $this->get($param->getType()->getName());
            } elseif ($param->isDefaultValueAvailable()) {
                $deps[] = $param->getDefaultValue();
            }
        }
        
        return $callable(...$deps);
    }
}