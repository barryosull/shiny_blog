<?php
/**
 * ShinyBlog
 *
 * by Simon Samtleben <simon@nekudo.com>
 *
 * A simple and clean Blog/CMS application.
 * For more information visit: https://github.com/nekudo/shiny_blog
 */

declare(strict_types=1);
namespace Nekudo\ShinyBlog;

use Exception;
use RuntimeException;
use FastRoute;
use FastRoute\RouteCollector;
use Nekudo\ShinyBlog\Responder\HttpResponder;
use Nekudo\ShinyBlog\Responder\NotFoundResponder;
use Psr\SimpleCache\CacheInterface;;

class ShinyBlog
{
    /** @var array $config */
    protected $config;

    /** @var FastRoute\Dispatcher $dispatcher */
    protected $dispatcher;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * ShinyBlog main method.
     * Dispatches and handles requests.
     * @param CacheInterface $cache
     */
    public function run()
    {
        try {
            $this->setRoutes();
            $this->dispatch();
        } catch (Exception $e) {
            $responder = new HttpResponder($this->config);
            $responder->error($e->getMessage());
        }
    }

    /**
     * Defines blog and page routes.
     *
     * @throws RuntimeException
     */
    protected function setRoutes()
    {
        if (empty($this->config['routes'])) {
            throw new RuntimeException('No routes defined in configuration file.');
        }
        $this->dispatcher = FastRoute\simpleDispatcher(function (RouteCollector $collector) {
            foreach ($this->config['routes'] as $routeName => $route) {
                if (empty($route['method']) || empty($route['route']) || empty($route['action'])) {
                    throw new RuntimeException('Invalid route in configuration.');
                }
                $collector->addRoute($route['method'], $route['route'], $route['action']);
            }
        });
    }

    /**
     * Tries to find a route matching the current request. If found the defined action is called.
     */
    protected function dispatch()
    {
        $httpMethod = $_SERVER['REQUEST_METHOD'];
        $uri = rawurldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
        $routeInfo = $this->dispatcher->dispatch($httpMethod, $uri);
        if (!isset($routeInfo[0])) {
            throw new RuntimeException('Could not dispatch request.');
        }
        switch ($routeInfo[0]) {
            case FastRoute\Dispatcher::NOT_FOUND:
                $responder = new NotFoundResponder($this->config);
                $responder->__invoke();
                break;
            case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
                $responder = new HttpResponder($this->config);
                $responder->methodNotAllowed();
                break;
            case FastRoute\Dispatcher::FOUND:
                $handler = $routeInfo[1];
                $arguments = $routeInfo[2];
                $this->runAction($handler, $arguments);
                break;
            default:
                throw new RuntimeException('Could not dispatch request.');
        }
    }

    /**
     * Calls an action.
     *
     * @param string $actionName
     * @param array $arguments
     */
    protected function runAction(string $actionName, array $arguments = [])
    {
        $actionNamespace = "\\Nekudo\\ShinyBlog\\Controller\\Http";
        $actionController = "Show".ucfirst($actionName);

        $actionClassPath = $actionNamespace."\\".$actionController;
        
        if (!class_exists($actionClassPath)) {
            throw new RuntimeException('Invalid action.');
        }

        $action = new $actionClassPath($this->config);
        $action->__invoke($arguments);
    }
}
