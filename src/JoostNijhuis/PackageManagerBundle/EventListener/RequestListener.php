<?php
/**
 * This file is part of the Composer Package Manager.
 *
 * (c) Joost Nijhuis <jnijhuis81@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace JoostNijhuis\PackageManagerBundle\EventListener;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * JoostNijhuis\PackageManagerBundle\EventListener\RequestListener
 *
 * This request listener will manipulate the route variable
 * if a certain route is matched. If there is a dollar sign in the
 * file variable we will strip the dollar sign until the end from that
 * variable and manipulate te route params so the controller gets the
 * right information without knowing we will do this here.
 */
class RequestListener
{
    
    /**
     * Will be triggered by the Symfony2 kernel using the Event Dispatcher
     *
     * @param GetResponseEvent $e
     */
    public function onKernelRequest(GetResponseEvent $e)
    {
        if (HttpKernelInterface::MASTER_REQUEST !== $e->getRequestType()) {
            return;
        }

        /** @var Request $request */
        $request = $e->getRequest();
        $uri = $request->getRequestUri();
        $routeParams = $request->attributes->all();

        /* The shortest route name to match */
        $routeName = "joostnijhuis_packagemanager_packages_index";

        if (strpos($routeParams['_route'], '$routeName') === 0) {
            /* Route is matched */
            if (strpos($uri, '$') !== false) {
                /* uri contains a dollar sign  */
                $routeParams = $request->attributes->all();
                if (isset($routeParams['file'])) {
                    /* Param is set, so strip this value */
                    $request->attributes->set(
                        'file',
                        $this->stripFromDollarSign($routeParams['file'])
                    );
                }
                if (isset($routeParams['_route_params']['file'])) {
                    /* route param is set, so strip this value */
                    $routeParams['_route_params']['file'] = $this->stripFromDollarSign(
                        $routeParams['_route_params']['file']
                    );
                    $request->attributes->set(
                        '_route_params',
                        $routeParams['_route_params']
                    );
                }
            }
        }
    }

    /**
     * @param $text
     * @return string
     */
    protected function stripFromDollarSign($text)
    {
        if (($pos = strpos($text, '$')) !== false) {
            $text = substr($text, 0, $pos);
        }

        return $text;
    }

}
