<?php

/**
 * This file is part of the Composer Package Manager.
 *
 * (c) Joost Nijhuis <jnijhuis81@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JoostNijhuis\PackageManagerBundle\ComposerRepository;

use JoostNijhuis\PackageManagerBundle\ComposerRepository\Exceptions\FileNotFoundException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Command\Command;
use Composer\Composer;
use Composer\Config;
use Composer\IO\ConsoleIO;
use Composer\IO\NullIO;
use Composer\Factory;
use Composer\Json\JsonFile;
use Composer\Package\Dumper\ArrayDumper;
use Composer\Package\AliasPackage;
use Composer\Package\LinkConstraint\VersionConstraint;
use Composer\Package\PackageInterface;
use Symfony\Component\Console\Helper\HelperSet;
use JoostNijhuis\PackageManagerBundle\Packages\SvnAuthentication;

/**
 * This is the BuildHandler to build the private packages part
 * of the repository. It will build a json output file with all
 * populated packages with there versions and composer information.
 */
class BuildHandler
{

    /**
     * @var string $inputFile
     */
    protected $inputFile;

    /**
     * @var string $outputFile
     */
    protected $outputFile;

    /**
     * @var Command $command
     */
    protected $command;

    /**
     * @var OutputInterface $output
     */
    protected $output;

    /**
     * @var InputInterface $input
     */
    protected $input;

    /**
     * @var HelperSet $helperSet
     */
    protected $helperSet;

    /**
     * @var SvnAuthentication
     */
    protected $svnAuthentication;

    /**
     * Constructor
     *
     * @param string $inputFile  The file to read the configuration from
     * @param string $outputFile The file to write all populated data to
     * @throws FileNotFoundException
     */
    public function __construct($inputFile, $outputFile)
    {
        if (!file_exists($inputFile)) {
            throw new FileNotFoundException(sprintf(
                'File: \'%s\' doesn\'t exists.',
                $inputFile
            ));
        }

        $this->inputFile  = $inputFile;
        $this->outputFile = $outputFile;
    }

    /**
     * Set the output interface to use for writing messages to.
     * Can be used if this class is used in a Console Command.
     *
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @void
     */
    public function setOutputInterface(OutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * Set the input interface to use for retrieving arguments and/or
     * options. Can be used if this class is used in a Console Command.
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @return void
     */
    public function setInputInterface(InputInterface $input)
    {
        $this->input = $input;
    }

    /**
     * Set the HelperSet which helps the output interface for dialogs etc...
     * Can be used if this class is used in a Console Command.
     *
     * @param \Symfony\Component\Console\Helper\HelperSet $helperSet
     * @return void
     */
    public function setHelperSet(HelperSet $helperSet)
    {
        $this->helperSet = $helperSet;
    }

    /**
     * Inject the SvnAuthentication helper class.
     * If injected this will be used to find user credentials for
     * the svn url's.
     *
     * @param \JoostNijhuis\PackageManagerBundle\Packages\SvnAuthentication $svnAuthentication
     * @return void
     */
    public function setSvnAuthentication(SvnAuthentication $svnAuthentication)
    {
        $this->svnAuthentication = $svnAuthentication;
    }

    /**
     * returns the injected HelperSet or create an
     * new instance on the fly an return that one.
     *
     * @return \Symfony\Component\Console\Helper\HelperSet
     */
    protected function getHelperSet()
    {
        if (!isset($this->helperSet)) {
            $this->helperSet = new HelperSet();
        }

        return $this->helperSet;
    }

    /**
     * Build the repository
     * @return void
     */
    public function buildRepository()
    {
        $file = new JsonFile($this->inputFile);
        if (!$file->exists()) {
            $this->writeln('<error>File not found: '. $this->inputFile .'</error>');
            return 1;
        }

        $config = $file->read();
        $config["require-all"] = true;
        $config = $this->addCredentials($config);

        /* Disable packagist by default */
        unset(Config::$defaultRepositories['packagist']);

        $composer = $this->getComposer($config);
        $packages = $this->selectPackages($composer);
        $this->writeData($packages);
    }

    /**
     * Get a Composer instance, will be factored and use
     * a ConsoleIO or NullIO as input/output handler depends
     * on if the $input and $output interfaces are injected
     *
     * @param array $config        The config array which the Composer
     *                             instance will use
     * @return \Composer\Composer
     */
    protected function getComposer(array $config)
    {
        if (isset($this->input) && isset($this->output)) {
            $io = new ConsoleIO($this->input, $this->output, $this->getHelperSet());
        } else {
            $io = new NullIO();
        }

        try {
            $composer = Factory::create($io, $config);
        } catch (\InvalidArgumentException $e) {
            $this->write($e->getMessage());
            exit(1);
        }

        return $composer;
    }

    /**
     * Select packages by walking through the configured
     * repositories and add the configured packages to the
     * array. Returns an array with all collected packages.
     *
     * @param \Composer\Composer $composer
     * @return array
     */
    protected function selectPackages(Composer $composer)
    {
        $verbose = false;
        if (isset($this->input) && $this->input->hasOption('verbose')) {
            $verbose - $this->input->getOption('verbose');
        }

        $targets = array();
        $selected = array();

        foreach ($composer->getPackage()->getRequires() as $link) {
            $targets[$link->getTarget()] = array(
                'matched' => false,
                'link' => $link,
                'constraint' => $link->getConstraint()
            );
        }

        /* Find packages and add them to the stack */
        $this->writeln('<info>Scanning packages</info>');
        foreach ($composer->getRepositoryManager()->getRepositories() as $repository) {
            foreach ($repository->getPackages() as $package) {
                // skip aliases
                if ($package instanceof AliasPackage) {
                    continue;
                }

                $name = $package->getName();

                // add matching package if not yet selected
                if (!isset($selected[$package->getUniqueName()])) {
                    if ($verbose) {
                        $this->writeln('Selected '.$package->getPrettyName().' ('.$package->getPrettyVersion().')');
                    }
                    $targets[$name]['matched'] = true;
                    $selected[$package->getUniqueName()] = $package;
                }
            }
        }

        // check for unmatched requirements
        foreach ($targets as $package => $target) {
            if (!$target['matched']) {
                $this->writeln('<error>The '.$target['link']->getTarget().' '.$target['link']->getPrettyConstraint().' requirement did not match any package</error>');
            }
        }
        asort($selected, SORT_STRING);

        return $selected;
    }

    /**
     * Write data to the output file set through the constructor
     *
     * @param array $packages
     */
    protected function writeData(array $packages)
    {
        $repo = array('packages' => array());
        $dumper = new ArrayDumper;

        /* @var \Composer\Package\Package $package */
        foreach ($packages as $package) {
            if ($package->getDistUrl() != '') {
                $url = $this->removeCredentialsFromUrl($package->getDistUrl());
                $package->setDistUrl($url);
            }
            if ($package->getSourceUrl() != '') {
                $url = $this->removeCredentialsFromUrl($package->getSourceUrl());
                $package->setSourceUrl($url);
            }

//            if ($package->getSourceType() == 'svn') {
//                if ($package->getSourceReference() != '') {
//                    $reference = $this->removeRevisionFromReference($package->getSourceReference());
//                    $package->setSourceReference($reference);
//                }
//            }
//            if ($package->getDistType() == 'svn') {
//                if ($package->getDistReference() != '') {
//                    $reference = $this->removeRevisionFromReference($package->getDistReference());
//                    $package->setDistReference($reference);
//                }
//            }

            $repo['packages'][$package->getPrettyName()][$package->getPrettyVersion()] = $dumper->dump($package);
        }

        $this->writeln('<info>Writing ' . $this->outputFile . '</info>');
        $repoJson = new JsonFile($this->outputFile);
        $repoJson->write($repo);
    }

    /**
     * Writes a message to the output and adds a newline at the end.
     *
     * @param string|array $messages The message as an array of lines of a single string
     * @param integer      $type     The type of output (0: normal, 1: raw, 2: plain)
     */
    protected function writeln($messages, $type = 0)
    {
        if (isset($this->output)) {
            return $this->output->writeln($messages, $type);
        }
    }

    /**
     * Writes a message to the output.
     *
     * @param string|array $messages The message as an array of lines of a single string
     * @param Boolean      $newline  Whether to add a newline or not
     * @param integer      $type     The type of output (0: normal, 1: raw, 2: plain)
     *
     * @throws \InvalidArgumentException When unknown output type is given
     */
    protected function write($messages, $newline = false, $type = 0)
    {
        if (isset($this->output)) {
            return $this->output->write($messages, $newline, $type);
        }
    }

    /**
     * Add credentials to urls in the passed config array.
     * Works only when the svnAuthentication helper class is injected.
     *
     * The passed config array will be searched for repositories
     * and packages, depending on which item a package or repository
     * there will be search for urls and add credentials to the urls.
     *
     * @param array $config  the array to walk through an add credentials to urls
     * @return array         parsed array with credentials added
     */
    protected function addCredentials(array $config)
    {
        if (!isset($this->svnAuthentication)) {
            return $config;
        }

        if (isset($config['repositories'])) {
            foreach ($config['repositories'] as $key => $repository) {
                if (isset($repository['type']) && $repository['type'] == 'svn') {
                    if (isset($repository['url'])) {
                        $creds = $this->svnAuthentication->getCredentialsForUrl($repository['url']);
                        if ($creds !== false) {
                            $repository['url'] = $this->addCredentialsToUrl(
                                $repository['url'],
                                $creds['username'],
                                $creds['password']
                            );
                            $config['repositories'][$key] = $repository;
                        }
                    }
                }
                if (isset($repository['type']) && $repository['type'] == 'package') {
                    if (isset($repository['package'])) {
                        if (isset($repository['package']['source']) && $repository['package']['source']['type'] == 'svn') {
                            $creds = $this->svnAuthentication->getCredentialsForUrl(
                                $repository['package']['source']['url'] . $repository['package']['source']['reference']);
                            if ($creds !== false) {
                                $repository['package']['source']['url'] = $this->addCredentialsToUrl(
                                    $repository['package']['source']['url'],
                                    $creds['username'],
                                    $creds['password']
                                );
                                $config['repositories'][$key] = $repository;
                            }
                        }
                        if (isset($repository['package']['dist']) && $repository['package']['dist']['type'] == 'svn') {
                            $creds = $this->svnAuthentication->getCredentialsForUrl(
                                $repository['package']['source']['url'] . $repository['package']['dist']['reference']);
                            if ($creds !== false) {
                                $repository['package']['dist']['url'] = $this->addCredentialsToUrl(
                                    $repository['package']['dist']['url'],
                                    $creds['username'],
                                    $creds['password']
                                );
                                $config['repositories'][$key] = $repository;
                            }
                        }
                    }
                }
            }
        }

        return $config;
    }

    /**
     * Add credentials to an url
     *
     * @param $url
     * @param $username
     * @param $password
     * @return string
     */
    protected function addCredentialsToUrl($url, $username, $password)
    {
        $retVal = "";
        $url_parts = parse_url($url);

        $retVal = $url_parts['scheme'] . "://" . $username . ':' . $password . '@' . $url_parts['host'];
        if (isset($url_parts['port'])) {
            $retVal .= ':' . $url_parts['port'];
        }
        if (isset($url_parts['path'])) {
            $retVal .= $url_parts['path'];
        }
        if (isset($url_parts['query'])) {
            $retVal .= '?' . $url_parts['query'];
        }
        if (isset($url_parts['fragment'])) {
            $retVal .= '#' . $url_parts['fragment'];
        }

        return $retVal;
    }

    /**
     * Removes the credentials from an url
     *
     * @param string $url
     * @return string
     */
    protected function removeCredentialsFromUrl($url)
    {
        $retVal = "";
        $url_parts = parse_url($url);

        $retVal = $url_parts['scheme'] . "://" . $url_parts['host'];
        if (isset($url_parts['port'])) {
            $retVal .= ':' . $url_parts['port'];
        }
        if (isset($url_parts['path'])) {
            $retVal .= $url_parts['path'];
        }
        if (isset($url_parts['query'])) {
            $retVal .= '?' . $url_parts['query'];
        }
        if (isset($url_parts['fragment'])) {
            $retVal .= '#' . $url_parts['fragment'];
        }

        return $retVal;
    }

    /**
     * Removes the revision part of the reference
     *
     * @param string $reference
     * @return string
     */
    protected function removeRevisionFromReference($reference)
    {
        $pos = strpos($reference, '/@');
        if ($pos !== false) {
            $reference = substr($reference, 0, $pos);
        }
        $pos = strpos($reference, '@');
        if ($pos !== false) {
            $reference = substr($reference, 0, $pos);
        }

        return $reference;
    }

}
