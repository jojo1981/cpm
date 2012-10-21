<?php

/*
 * This file is part of the Composer Package Manager.
 *
 * (c) Joost Nijhuis <jnijhuis81@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JoostNijhuis\PackageManagerBundle\Twig\Extension;

use JoostNijhuis\PackageManagerBundle\Twig\LanguageSwitcher;

/**
 * A Twig Extension class which provide some extra functionality
 * to the twig template which will be used inside this bundle
 */
class PackageManagerTwigExtension extends \Twig_Extension_Core
{

    /**
     * @var \JoostNijhuis\PackageManagerBundle\Twig\LanguageSwitcher
     */
    protected $languageSwitcher;

    /**
     * @var array The bundle configuration settings read from the config.yml file
     */
    protected $config;

    /**
     * Constructor
     *
     * @param \JoostNijhuis\PackageManagerBundle\Twig\LanguageSwitcher $languageSwitcher
     * @param array $config The bundle configuration settings read from the config.yml file
     */
    public function __construct(
        LanguageSwitcher $languageSwitcher,
        array $config = array()
    ) {
        $this->languageSwitcher = $languageSwitcher;
        $this->config = $config;
    }

    /**
     * Returns the name of this Twig Extension which will be used
     * by the Twig Template Engine
     *
     * @return string
     */
    public function getName()
    {
        return 'joost_nijhuis_package_manager_twig_extension';
    }

    /**
     * This method will be called by the Twig Template Engine
     * for setting up the Twig Environment, the filters returned
     * here will be added to the filters stack.
     * These filters can be used in the twig templates
     *
     * @return array
     */
    public function getFilters()
    {
        return array(
            'var_dump'  => new \Twig_Filter_Function('var_dump'),
            'highlight' => new \Twig_Filter_Method($this, 'highlight')
        );
    }

    /**
     * This method will be called by the Twig Template Engine
     * for setting up the Twig Environment, the functions returned
     * here will be added to the functions stack.
     * These functions can be used in the twig templates
     *
     * @return array
     */
    public function getFunctions()
    {
        return array(
            'print_a' => new \Twig_Function_Method($this, 'print_a'), 
            'language_selector' => new \Twig_Function_Method($this, 'languageSelector', array('is_safe' => array('html'))), 
            'get_class' => new \Twig_Function_Method($this, 'get_class')
        );
    }

    /**
     * This method will be called by the Twig Template Engine
     * for setting up the Twig Environment, the globals returned
     * here will be added to the globals stack.
     * These globals can be used in the twig templates
     *
     * @return array
     */
    public function getGlobals()
    {
        $appName = $this->config['app_name'];
        $companyName = $this->config['company_name'];

        $retVal = array(
            'appName'     => $appName,
            'companyName' => $companyName
        );

        if (isset($this->config['company_url'])) {
            $retVal['CompanyUrl'] = $this->config['company_url'];
        }

        return $retVal;
    }

    /**
     * Highlight filter which can be used in Twig templates.
     * The passed sentence will be parsed while using a regular
     * expression to determine which need to be highlighted
     *
     * @param string $sentence raw string to parse
     * @param string $expr     the regular expression to determine which need to be highlighted
     * @return string mixed    parsed string will be returned
     */
    public function highlight($sentence, $expr)
    {
        return preg_replace(
            '/(' . $expr . ')/',
            '<span style="color:red">\1</span>', 
            $sentence
        );
    }

    /**
     * A wrapper for the PHP print_r function which will
     * force the output to be placed inside a HTML pre tag
     *
     * @param $data
     * @return string
     */
    public function print_a($data)
    {
        return '<pre>' . print_r($data, true) . '</pre>';
    }

    /**
     * Returns the name of the class of an object
     * @link http://php.net/manual/en/function.get-class.php
     * @param object $object
     * @return string the name of the class of which object is an
     *                instance. Returns false if object is not an
     *                object.
     */
    public function get_class($object)
    {
        return get_class($object);
    }

    /**
     * Get the language switcher form
     *
     * @return string the html content which can be echoed inside a Twig template
     *                does not need to use the raw filter because this will not be escaped
     */
    public function languageSelector()
    {
        return $this->languageSwitcher->getLanguageSwitcher();
    }

}
