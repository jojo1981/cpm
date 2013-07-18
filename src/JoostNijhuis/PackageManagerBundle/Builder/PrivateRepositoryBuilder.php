<?php
/**
 * This file is part of the Composer Package Manager.
 *
 * (c) Joost Nijhuis <jnijhuis81@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace JoostNijhuis\PackageManagerBundle\Builder;

use JoostNijhuis\PackageManagerBundle\Builder\Exception\FileNotFoundException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Command\Command;
use Composer\Composer;
use Composer\Config;
use Composer\IO\NullIO;
use Composer\Factory;
use Composer\Json\JsonFile;
use Composer\Package\Dumper\ArrayDumper;
use Composer\Package\AliasPackage;
use Composer\Package\Package;
use Composer\DependencyResolver\Pool;
use Composer\Package\LinkConstraint\MultiConstraint;
use Composer\Package\Link;
use Composer\Repository\ComposerRepository;
use Composer\Repository\PlatformRepository;
use JoostNijhuis\PackageManagerBundle\Packages\SvnAuthentication;
use JoostNijhuis\PackageManagerBundle\Builder\Config\Config as ParseConfig;
use Symfony\Component\Filesystem\Filesystem;

/**
 * JoostNijhuis\PackageManagerBundle\Builder\PrivateRepositoryBuilder
 *
 * This is the BuildHandler to build the private packages part
 * of the repository. It will build a json output file with all
 * populated packages with there versions and composer information.
 */
class PrivateRepositoryBuilder
{
    /**
     * @var ParseConfig
     */
    protected $config;

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
     * @var int
     */
    protected $rememberVerbosityLevel;

    /**
     * Constructor
     *
     * @param ParseConfig $config
     * @throws FileNotFoundException
     */
    public function __construct(ParseConfig $config)
    {
        $this->config = $config;
        $inputFile = $this->config->getPrivatePackagesConfigFile();
        $outputFile = $this->config->getPrivatePackagesFile();

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
     * @param OutputInterface $output
     */
    public function setOutputInterface(OutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * Set the input interface to use for retrieving arguments and/or
     * options. Can be used if this class is used in a Console Command.
     *
     * @param InputInterface $input
     */
    public function setInputInterface(InputInterface $input)
    {
        $this->input = $input;
    }

    /**
     * Set the HelperSet which helps the output interface for dialogs etc...
     * Can be used if this class is used in a Console Command.
     *
     * @param null|HelperSet $helperSet
     * @return void
     */
    public function setHelperSet(HelperSet $helperSet = null)
    {
        $this->helperSet = $helperSet;
    }

    /**
     * Inject the SvnAuthentication helper class.
     * If injected this will be used to find user credentials for
     * the svn url's.
     *
     * @param SvnAuthentication $svnAuthentication
     */
    public function setSvnAuthentication(SvnAuthentication $svnAuthentication)
    {
        $this->svnAuthentication = $svnAuthentication;
    }

    /**
     * returns the injected HelperSet or create an
     * new instance on the fly an return that one.
     *
     * @return HelperSet
     */
    protected function getHelperSet()
    {
        if (!$this->helperSet instanceof HelperSet) {
            $this->helperSet = new HelperSet();
        }

        return $this->helperSet;
    }

    /**
     * Method for suppressing the output
     */
    protected function suppressOutput()
    {
        /* Save current verbosity level */
        $this->rememberVerbosityLevel = $this->output->getVerbosity();

        /* set verbosity level to quit, this to suppress the output */
        $this->output->setVerbosity(OutputInterface::VERBOSITY_QUIET);
    }

    /**
     * Restore verbosity level
     */
    protected function restoreVerbosityLevel()
    {
        /* Restore output verbosity level */
        $this->output->setVerbosity($this->rememberVerbosityLevel);
    }

    /**
     * Build the repository
     */
    public function buildRepository()
    {
        $this->suppressOutput();

        $file = new JsonFile($this->inputFile);
        if (!$file->exists()) {
            $this->writeLine(
                '<error>File not found: '. $this->inputFile .'</error>'
            );

            return 1;
        }

        $config = $file->read();
        if (isset($config['repositories'])) {
            foreach ($config['repositories'] as $index => $repository)
            {
                if (isset($repository['type']) && $repository['type'] == 'package') {
                    $config['repositories'][$index]['package']['name'] = strtolower($config['repositories'][$index]['package']['name']);
                }
            }
        }

        $config["require-all"] = true;
        $config = $this->addCredentials($config);

        $config = array_merge($config, $this->config->getComposerConfig());

        /* Disable packagist by default */
        unset(Config::$defaultRepositories['packagist']);

        $requireAll = isset($config['require-all']) && true === $config['require-all'];
        $requireDependencies = isset($config['require-dependencies']) && true === $config['require-dependencies'];
        if (!$requireAll && !isset($config['require'])) {
            $this->output->writeln('No explicit requires defined, enabling require-all');
            $requireAll = true;
        }

        $composer = $this->getComposer($config);
        $packages = $this->selectPackages($composer, $requireAll, $requireDependencies);
        $this->writeData($packages);

        $fs = new Filesystem();
        $fs->remove($config['config']['cache-dir']);

        $this->restoreVerbosityLevel();
    }

    /**
     * Get a Composer instance, will be factored and use
     * a ConsoleIO or NullIO as input/output handler depends
     * on if the $input and $output interfaces are injected
     *
     * @param array $config        The config array which the Composer
     *                             instance will use
     * @return Composer
     */
    protected function getComposer(array $config)
    {
        $io = new NullIO();

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
     * @param Composer $composer
     * @param bool $requireAll
     * @param bool $requireDependencies
     * @return array
     */
    private function selectPackages(Composer $composer, $requireAll, $requireDependencies)
    {
        $selected = array();

        // run over all packages and store matching ones
        $this->output->writeln('<info>Scanning packages</info>');

        $repositories = $composer->getRepositoryManager()->getRepositories();
        $pool = new Pool('dev');
        foreach ($repositories as $repository) {
            $pool->addRepository($repository);
        }

        if ($requireAll) {
            $links = array();

            foreach ($repositories as $repository) {
                // collect links for composer repository with providers
                if ($repository instanceof ComposerRepository && $repository->hasProviders()) {
                    foreach ($repository->getProviderNames() as $name) {
                        $links[] = new Link('__root__', $name, new MultiConstraint(array()), 'requires', '*');
                    }
                } else {
                    // process other repos directly
                    foreach ($repository->getPackages() as $package) {
                        // skip aliases
                        if ($package instanceof AliasPackage) {
                            continue;
                        }

                        // add matching package if not yet selected
                        if (!isset($selected[$package->getUniqueName()])) {
                            if ($this->output->getVerbosity() == OutputInterface::VERBOSITY_VERBOSE) {
                                $this->output->writeln('Selected '.$package->getPrettyName().' ('.$package->getPrettyVersion().')');
                            }
                            $selected[$package->getUniqueName()] = $package;
                        }
                    }
                }
            }
        } else {
            $links = array_values($composer->getPackage()->getRequires());
        }

        // process links if any
        $dependencyLinks = array();

        $i = 0;
        while (isset($links[$i])) {
            /** @var Link $link */
            $link = $links[$i];
            $i++;
            $name = $link->getTarget();
            $matches = $pool->whatProvides($name, $link->getConstraint());

            foreach ($matches as $index => $package) {
                // skip aliases
                if ($package instanceof AliasPackage) {
                    $package = $package->getAliasOf();
                }

                // add matching package if not yet selected
                if (!isset($selected[$package->getUniqueName()])) {
                    if ($this->output->getVerbosity() == OutputInterface::VERBOSITY_VERBOSE) {
                        $this->output->writeln(sprintf(
                            'Selected %s (%s)',
                            $package->getPrettyName(),
                            $package->getPrettyVersion()
                        ));
                    }
                    $selected[$package->getUniqueName()] = $package;

                    if (!$requireAll && $requireDependencies) {
                        // append non-platform dependencies
                        /** @var Link $dependencyLink */
                        foreach ($package->getRequires() as $dependencyLink) {
                            $target = $dependencyLink->getTarget();
                            if (!preg_match(PlatformRepository::PLATFORM_PACKAGE_REGEX, $target)) {
                                $linkId = $target.' '.$dependencyLink->getConstraint();
                                // prevent loading multiple times the same link
                                if (!isset($dependencyLinks[$linkId])) {
                                    $links[] = $dependencyLink;
                                    $dependencyLinks[$linkId] = true;
                                }
                            }
                        }
                    }
                }
            }

            if (!$matches) {
                $this->output->writeln(sprintf(
                    '<error>The %s %s requirement did not match any package</error>',
                    $name,
                    $link->getPrettyConstraint()
                ));
            }
        }
        ksort($selected, SORT_STRING);

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
        $dumper = new ArrayDumper();

        /* @var Package $package */
        foreach ($packages as $package) {
            if ($package->getDistUrl() != '') {
                $url = $this->removeCredentialsFromUrl($package->getDistUrl());
                $package->setDistUrl($url);
            }
            if ($package->getSourceUrl() != '') {
                $url = $this->removeCredentialsFromUrl($package->getSourceUrl());
                $package->setSourceUrl($url);
            }
            $repo['packages'][$package->getPrettyName()][$package->getPrettyVersion()] = $dumper->dump($package);
        }

        $this->writeLine('<info>Writing ' . $this->outputFile . '</info>');
        $repoJson = new JsonFile($this->outputFile);
        $repoJson->write($repo);
    }

    /**
     * Writes a message to the output and adds a newline at the end.
     *
     * @param string|array $messages The message as an array of lines of a single string
     * @param integer      $type     The type of output (0: normal, 1: raw, 2: plain)
     */
    protected function writeLine($messages, $type = 0)
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
     * @param string $url
     * @param string $username
     * @param string $password
     * @return string
     */
    protected function addCredentialsToUrl($url, $username, $password)
    {
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
