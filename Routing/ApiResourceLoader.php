<?php
/**
 * Created by PhpStorm.
 * User: hautruong
 * Date: 5/4/17
 * Time: 2:33 PM
 */

namespace conghau\Bundle\ApiResource\Routing;

use conghau\Bundle\ApiResource\Constant\ApiType;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use conghau\Bundle\ApiResource\Helper\Helper;

class ApiResourceLoader extends Loader
{

    private $loaded = false;
    private $container;

    /**
     * Initializes loader.
     *
     * @param ContainerInterface   $container
     */
    public function __construct(
        ContainerInterface $container
    ) {
        $this->container = $container;
    }

    /**
     * @param mixed $resource
     * @param null  $type
     *
     * @return RouteCollection
     */
    public function load($resource, $type = null)
    {
        // TODO: Implement load() method.
        if (true === $this->loaded) {
            throw new \RuntimeException('Do not add the "TCH_api_resource" loader twice');
        }

        $routes = new RouteCollection();

        $config = $this->container->getParameter('tch_api_resource.config');
        $resources = $config['resources'] ?? [];
        foreach ($resources as $key => $value) {
            $route = $this->createRoute($key, $value['actions']);
            if (is_null($route)) {
                continue;
            }
            $routes->addCollection($route);
        }


        $this->loaded = true;

        return $routes;
    }

    /**
     * Returns whether this class supports the given resource.
     *
     * @param mixed       $resource A resource
     * @param string|null $type The resource type or null if unknown
     *
     * @return bool True if this class supports the given resource, false otherwise
     */
    public function supports($resource, $type = null)
    {
        // TODO: Implement supports() method.
        return 'tch_api_resource' === $type;
    }

    private function createRoute($class, $actions)
    {
        $path = '';
        $methods = ['GET'];
        $routes = new RouteCollection();
        $class = Helper::convertWithDash($class);
        $actions = explode('.', $actions);
        foreach ($actions as $action) {
            switch ($action) {
                case ApiType::API_ACTIONS_SEARCH:
                    $path = "/$class/search";
                    $methods = ['POST'];
                    $defaults = array(
                        '_controller' => 'TCHApiResourceBundle:ApiResource:search',
                    );
                    break;
                case ApiType::API_ACTIONS_CREATE:
                    $path = "/$class/create";
                    $methods = ['POST'];
                    $defaults = array(
                        '_controller' => 'TCHApiResourceBundle:ApiResource:create',
                    );
                    break;
                case ApiType::API_ACTIONS_UPDATE:
                    $path = "/$class/{id}/update";
                    $methods = ['PUT'];
                    $defaults = array(
                        '_controller' => 'TCHApiResourceBundle:ApiResource:update',
                    );
                    break;
                case ApiType::API_ACTIONS_DELETE:
                    $path = "/$class/{id}/delete";
                    $methods = ['DELETE'];
                    $defaults = array(
                        '_controller' => 'TCHApiResourceBundle:ApiResource:delete',
                    );
                    break;
                case ApiType::API_ACTIONS_RETRIEVE:
                    $path = "/$class/{id}/detail";
                    $methods = ['GET'];
                    $defaults = array(
                        '_controller' => 'TCHApiResourceBundle:ApiResource:detail',
                    );
                    break;
            }

            if ('' === $path) {
                continue;
            }

            $route = new Route($path, $defaults, [], [], '', [], $methods);

            // add the new route to the route collection
            $routes->add(sprintf("tch_api_resource_%s_%s", $class, $action), $route);

        }

        return $routes;
    }
}
