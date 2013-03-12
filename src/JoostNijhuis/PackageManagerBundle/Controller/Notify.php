<?php

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
