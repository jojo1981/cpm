<?php
/**
 * This file is part of the Composer Package Manager.
 *
 * (c) Joost Nijhuis <jnijhuis81@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace JoostNijhuis\PackageManagerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bridge\Monolog\Logger;

/**
 * JoostNijhuis\PackageManagerBundle\Controller\Notify
 */
class Notify extends Controller
{

    /**
     * @Route("/notify")
     */
    public function notifyAction(Request $request)
    {
        /** @var Logger $logger */
        $logger = $this->get('logger');

        $content = $request->getContent();
        $logger->addInfo($content);

        return new Response('Thanks!');
    }
}
