<?php
/**
 * This file is part of the Composer Package Manager.
 *
 * (c) Joost Nijhuis <jnijhuis81@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace JoostNijhuis\PackageManagerBundle\Twig;

use Symfony\Component\DependencyInjection\ContainerAware;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\FormBuilder;

/**
 * JoostNijhuis\PackageManagerBundle\Twig\LanguageSwitcher
 *
 * LanguageSwitcher class to show a language selection form
 * Also available a service which can be retrieved from the
 * dependency injection container
 */
class LanguageSwitcher extends ContainerAware
{
    /**
     * Generate a language selection form and retrieve the
     * rendered view as a string or as a response object
     *
     * @param bool $returnResponse if set to true a response instance will be returned
     * @return string|Response
     */
    public function getLanguageSwitcher($returnResponse = false)
    {
        $request = $this->getRequest();
        
        $em = $this->getDoctrine()->getManager();
        $languagesRepository = $em->getRepository('JoostNijhuis\PackageManagerBundle\Entity\Language');
        $arrLanguages = $languagesRepository->getForSelectBox();
        
        $form = $this->createFormBuilder(null, array('csrf_protection' => false))
            ->add('Language', 'choice', array(
                'choices' => $arrLanguages,
                'data' => $request->getLocale(),
                'label' => 'f.language_switcher.choose.language'))
            ->getForm();

        if ($returnResponse) {
            return $this->render(
                'JoostNijhuisPackageManagerBundle::partials\languages_form.html.twig',
                array(
                    'form' => $form->createView()
                )
            );
        } else {
            return $this->renderView(
                'JoostNijhuisPackageManagerBundle::partials\languages_form.html.twig',
                array(
                    'form' => $form->createView()
                )
            );
        }
    }
    
    /**
     * Returns a rendered view.
     *
     * @param string $view       The view name
     * @param array  $parameters An array of parameters to pass to the view
     *
     * @return string The renderer view
     */
    protected function renderView($view, array $parameters = array())
    {
        return $this->container->get('templating')->render($view, $parameters);
    }

    /**
     * Renders a view.
     *
     * @param string   $view       The view name
     * @param array    $parameters An array of parameters to pass to the view
     * @param Response $response   A response instance
     *
     * @return Response A Response instance
     */
    public function render($view, array $parameters = array(), Response $response = null)
    {
        return $this->container->get('templating')->renderResponse(
            $view,
            $parameters,
            $response
        );
    }
    
    /**
     * Creates and returns a form builder instance
     *
     * @param mixed $data    The initial data for the form
     * @param array $options Options for the form
     *
     * @return FormBuilder
     */
    protected function createFormBuilder(
        $data = null,
        array $options = array()
    ) {
        return $this->container->get('form.factory')->createBuilder(
            'form',
            $data,
            $options
        );
    }
    
    /**
     * Shortcut to return the request service.
     *
     * @return Request
     */
    protected function getRequest()
    {
        return $this->container->get('request');
    }
    
    /**
     * Shortcut to return the Doctrine Registry service.
     *
     * @return Registry
     * @throws \LogicException If DoctrineBundle is not available
     */
    protected function getDoctrine()
    {
        if (!$this->container->has('doctrine')) {
            throw new \LogicException(
                'The DoctrineBundle is not registered in your application.'
            );
        }

        return $this->container->get('doctrine');
    }
    
    /**
     * Shortcut to return the translator service.
     *
     * @return Translator
     */
    protected function getTranslator()
    {
        return $this->container->get('translator');
    }
}
