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

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * JoostNijhuis\PackageManagerBundle\EventListener\LocaleListener
 *
 * This class will be used to set the right locale
 * form route params first, if not exists then try to retrieve
 * it from the session if not exists try to use the preferred
 * language from the browser if no match can be made then use
 * the default locale.
 *
 * Also the listener prevents using not existing locales and
 * throws a NotFoundHttpException (404)
 *
 * The available locales will read from the database
 */
class LocaleListener
{
    
    /**
     * @var ContainerInterface The DIC from which we can retrieve other services
     */
    protected $container;
    
    /**
     * @var array will hold all available locales read from the database
     */
    protected $availableLocales;

    /**
     * Constructor
     *
     * Make this class Container Aware by c'tor injection
     * We need the whole container because if the use of the
     * Request object
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

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
        
        if (!$this->container->has('session')) {
            return;
        }

        $request = $e->getRequest();
        $session = $e->getRequest()->getSession();
        $locale = $this->getLocale($request, $session);
        
        /* Set locale in the session and the request object */
        $session->set('locale', $locale);
        $request->setlocale($locale);
    }

    /**
     * Read all available locales from the language database table
     * Will be read from the database only once when this method is called
     * the first time
     *
     * @return array
     */
    protected function getAvailableLocales()
    {
        if (isset($this->availableLocales)) {
            return $this->availableLocales;
        }

        if ($this->container->has('doctrine.orm.default_entity_manager')) {
            /* @var $em Doctrine\ORM\EntityManager */
            $em = $this->container->get('doctrine.orm.default_entity_manager');
            $languageRepository = $em->getRepository('JoostNijhuis\PackageManagerBundle\Entity\Language');
            $this->availableLocales = $languageRepository->getAvailableLocales();
        }

        return $this->availableLocales;
    }

    /**
     * Try to retrieve the locale, return false if no locale
     * can be determined.
     *
     * @param Request $request
     * @param Session $session
     * @return string
     */
    protected function getLocale(Request $request, Session $session)
    {
        /* Try to get the locale from the request params if set */
        $locale = $this->getLocaleFromRequestParams($request);

        /* Do we have a locale? NO, then try to get it from the session */
        if ($locale === false) {
            $locale = $this->getLocaleFromSession($session);
        }

        /* Do we have a locale? NO, then try to get the locale by the browser preferred languages */
        if ($locale === false) {
            $locale = $this->getPreferredOrDefaultLanguage($request);
        }

        return $locale;
    }

    /**
     * Try to get the locale from the request params if set and
     * it's not a valid locale then throw the exception.
     * return false if no locale is found in the request params.
     *
     * @param Request $request
     * @return bool|string
     * @throws NotFoundHttpException
     */
    protected function getLocaleFromRequestParams(Request $request)
    {
        $locale = false;
        $routeParams = $request->attributes->all();
        if (!empty($routeParams['_route_params']['_locale'])) {
            /* locale parameter is in the request object */
            if (!in_array($routeParams['_route_params']['_locale'], $this->getAvailableLocales())) {
                /* No valid locale parameter in the request object */
                throw new NotFoundHttpException(sprintf(
                    'No valid locale or no available locale in request, locale submitted locale: %s',
                    $routeParams['_route_params']['_locale']
                ));
            }
            $locale = $routeParams['_route_params']['_locale'];
        }

        return $locale;
    }

    /**
     * Try to retrieve the locale from the session, returns false
     * if the locale can not be found in the session
     *
     * @param Session $session
     * @return bool|string
     */
    protected function getLocaleFromSession(Session $session)
    {
        $locale = false;

        if ($session->has('locale')) {
            $locale = $session->get('locale');
        }

        return $locale;
    }

    /**
     * Try to get the locale from the browser preferred languages
     * If no match can made the default locale configured in the config.yml
     * file will be returned. If no default locale is configured in the
     * config.yml file the string 'en' will be returned
     *
     * @param Request $request
     * @return string
     */
    protected function getPreferredOrDefaultLanguage(Request $request)
    {
        $locale = $request->getPreferredLanguage($this->getAvailableLocales());
        if (empty($locale)) {
            /* No preferred languages found, so use the request default locale */
            $locale = $request->getlocale();
        }

        return $locale;
    }

}
