<?php

declare(strict_types=1);

namespace Trees;

use Trees\Container\Container;
use Trees\Exception\ContainerException;
use Trees\Exception\HttpException;
use Trees\Http\Request;
use Trees\Http\Response;
use Trees\Router\Router;
use Trees\Router\Route;
use Trees\View\View;
use Trees\Database\Database;
use Trees\Exception\TreesException;
use Trees\Exception\Handler\ExceptionHandler;

/**
 * =======================================
 * ***************************************
 * ========== Trees Class ================
 * ***************************************
 * =======================================
 */

class Trees
{
    private static ?self $instance = null;
    private Container $container;
    private Request $request;
    private Response $response;
    private Router $router;
    private Config $config;
    private Database $database;
    private View $view;
    private ExceptionHandler $exceptionHandler;
    private array $providers = [];
    private bool $booted = false;

    /**
     * @throws ContainerException
     * @throws TreesException
     */
    public function __construct()
    {
        require __DIR__ . "/dump_function.php";
        require __DIR__ . "/functions.php";
        require __DIR__ . "/other_function.php";
        require ROOT_PATH . "/utils/function.php";
        $this->container = new Container();
        $this->loadConfiguration();
        $this->initializeCore();
        $this->loadEssentialProviders();
        $this->bootProviders();

        $this->request = $this->container->get(Request::class);
        $this->response = $this->container->get(Response::class);
        $this->router = $this->container->get(Router::class);
    }

    public static function getInstance(): self
    {
        return self::$instance ??= new self();
    }

    /**
     * @throws TreesException
     */
    private function initializeCore(): void
    {
        $this->container->singleton(Request::class, function () {
            return Request::createFromGlobals();
        });
        $this->container->singleton(Response::class);
        $this->container->singleton(Router::class);

        $this->initializeDatabase();
        $this->exceptionHandler = new ExceptionHandler($_ENV['APP_ENV'] ?? 'production');
    }

    /**
     * @throws TreesException
     */
    private function initializeDatabase(): void
    {
        $default = $this->config->get('database.default');
        $connection = $this->config->get("database.connections.{$default}");

        if (!Database::init($default, $connection)) {
            throw new TreesException("Failed to initialize database connection");
        }
        $this->database = new Database($default, $connection);
    }

    private function loadConfiguration(): void
    {
        $this->config = new Config();

        $this->config->loadMultiple([
            $this->getConfigPath('app'),
            $this->getConfigPath('database'),
        ]);
    }

    private function loadEssentialProviders(): void
    {
        $providers = require $this->getConfigPath('providers');
        foreach ($providers as $provider) {
            $this->registerProvider(new $provider($this->container));
        }
    }

    public function registerProvider($provider): void
    {
        $this->providers[] = $provider;
        $provider->register();
    }

    private function bootProviders(): void
    {
        foreach ($this->providers as $provider) {
            $provider->boot();
        }
        $this->booted = true;
    }

    /**
     * @throws TreesException
     */
    public function run(): void
    {
        if (!$this->booted) {
            throw new TreesException("Application not booted properly");
        }

        try {
            $this->verifyKey();
            $this->dispatch();
        } catch (\Throwable $e) {
            $this->handleException($e);
        }
    }

    private function handleException(\Throwable $e): void
    {
        $this->exceptionHandler->handleHttpException($e, $this->request);
    }

    /**
     * @throws TreesException
     */
    private function verifyKey(): void
    {
        if (empty(env('APP_KEY'))) {
            throw new TreesException(
                "Application key missing. Generate one with 'php trees generate:key'",
                500
            );
        }
    }

    /**
     * @throws TreesException
     * @throws HttpException
     */
    private function dispatch(): void
    {
        $route = $this->router->resolve($this->request);
        $this->processMiddleware($route);
        $this->executeHandler($route);
        $this->response->send();
    }

    private function processMiddleware(Route $route): void
    {
        foreach ($route->getMiddleware() as $middleware) {
            $this->executeMiddleware($middleware);
        }
    }

    private function executeMiddleware($middleware): void
    {
        if (is_callable($middleware)) {
            $middleware($this->request, $this->response);
            return;
        }

        if (class_exists($middleware)) {
            $instance = new $middleware();
            if (method_exists($instance, 'handle')) {
                $instance->handle($this->request, $this->response);
            }
        }
    }

    /**
     * @throws TreesException
     */
    private function executeHandler(Route $route): void
    {
        $handler = $route->getHandler();
        $params = $route->getParams();

        if (is_callable($handler)) {
            $handler($this->request, $this->response, ...array_values($params));
            return;
        }

        if (is_array($handler) && count($handler) === 2) {
            [$controllerClass, $method] = $handler;
            $this->invokeController($controllerClass, $method, $params);
            return;
        }

        throw new TreesException('Invalid route handler', 500);
    }

    /**
     * @throws TreesException
     */
    private function invokeController(string $controllerClass, string $method, array $params): void
    {
        if (!class_exists($controllerClass)) {
            throw new TreesException("Controller {$controllerClass} not found", 500);
        }

        $controller = new $controllerClass();

        if (!method_exists($controller, $method)) {
            throw new TreesException("Method {$method} not found in {$controllerClass}", 500);
        }

        $controller->$method($this->request, $this->response, ...array_values($params));
    }

    private function getConfigPath(string $name): string
    {
        return config_path("{$name}.php");
    }

    public function getRouter(): Router
    {
        return $this->router;
    }

    // Accessors for core components
    public function getConfig(): Config { return $this->config; }
    public function getRequest(): Request { return $this->request; }
    public function getResponse(): Response { return $this->response; }
    public function getDatabase(): Database { return $this->database; }
    public function getView(): View { return $this->view; }

    private function __clone() {}
    public function __wakeup() {}
}