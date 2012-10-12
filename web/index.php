<?php

/**
 * This file is part of the Composer Package Manager.
 *
 * (c) Joost Nijhuis <jnijhuis81@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Symfony\Component\ClassLoader\ApcClassLoader;
use Symfony\Component\HttpFoundation\Request;

defined('APPLICATION_ENV')
|| define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

$loader = require_once __DIR__.'/../app/bootstrap.php.cache';
$debug  = false;

if (APPLICATION_ENV === 'prod') {
    $loader = new ApcClassLoader('joost_nijhuis_composer_package_manager', $loader);
    $loader->register(true);
} elseif (APPLICATION_ENV === 'dev') {
    $debug = true;
}

require_once __DIR__.'/../app/AppKernel.php';

$kernel = new AppKernel(APPLICATION_ENV, $debug);
$kernel->loadClassCache();

if (APPLICATION_ENV == 'prod') {
    $kernel = new AppCache($kernel);
}

$request = Request::createFromGlobals();

$response = $kernel->handle($request);
$response->send();

$kernel->terminate($request, $response);
