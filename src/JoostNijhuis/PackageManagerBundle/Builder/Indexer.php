<?php

namespace JoostNijhuis\PackageManagerBundle\Builder;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Helper\HelperSet;

/**
 * JoostNijhuis\PackageManagerBundle\Builder\Indexer
 */
class Indexer
{

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
     * @var string
     */
    protected $providerUrlFormat;

    protected $index;

    protected $baseUrl;

    protected $cacheDir;

    protected $cacheIndex;

    protected $cachePos = 0;

    protected $fileSystem;

    protected $start;

    public function __construct($cacheDir, $baseUrl = null)
    {
        $this->cacheDir = $cacheDir;
        $this->fileSystem = new Filesystem();
        if (empty($baseUrl)) {
            $baseUrl = 'http://packagist.org/';
        } else {
            if (substr($baseUrl, -1) != '/') {
                $baseUrl .= '/';
            }
        }
        $this->baseUrl = $baseUrl;
    }

    public function index()
    {
        $this->start = microtime(true);
        $url = $this->baseUrl . 'packages.json';
        $this->output->writeln('Get content from: ' . $url);
        $this->index['main']['urls'][] = $url;
        $content = $this->getFileContentWithCurl($url, true);

        if ($content) {
            $content = json_decode($content, true);
            if (isset($content['providers-url'])) {
                $this->providerUrlFormat = $content['providers-url'];
            }
            foreach ($content as $root => $data) {
                switch ($root) {
                    case 'includes':
                    case 'providers-includes':
                    case 'provider-includes':
                        $this->index[$root]['urls'] = $this->extractUrls($data);
                        foreach($this->index[$root]['urls'] as $url){
                            $this->getFileContentWithCurl($url, true);
                            $this->output->writeln('Get content from: ' . $url);
                        }
                        break;
                }
            }
        }

        foreach ($this->index as $root => $data) {
            if ($root != 'main') {
                foreach ($data['urls'] as $index => $url) {
                    $content = json_decode($this->getFileContent($url), true);
                    $parse = false;
                    switch ($root) {
                        case 'provider-includes':
                            $parse = true;
                        /* no break */
                        case 'providers-includes':
                            $this->index[$root]['providers']['urls'] = $this->extractUrls(
                                $content['providers'],
                                $parse
                            );
                            foreach ($this->index[$root]['providers']['urls'] as $url) {
                                $this->getFileContentWithCurl($url, true);
                                $this->output->writeln('Get content from: ' . $url);
                            }
                            break;
                    }
                }
            }
        }
        $timeTaken = microtime(true) - $this->start;
        $timeTakenMinutes = $timeTaken / 60;
        $this->output->writeln('Take about: ' . $timeTakenMinutes . ' minutes.');
    }

    protected function extractUrls($content, $parse = false)
    {
        $retValue = array();

        foreach ($content as $fileName => $data) {
            $hash = array_shift($data);
            if ($parse) {
                $fileName = str_replace(
                    array('%package%', '%hash%'),
                    array($fileName, $hash),
                    $this->providerUrlFormat
                );
            } else {
                if (strpos($fileName, '%hash%') !== false) {
                    $fileName = str_replace('%hash%', $hash, $fileName);
                }
            }
            if (strpos($fileName, '/') === 0){
                $fileName = substr($fileName, 1);
            }
            $retValue[] = $this->baseUrl . $fileName;
        }

        return $retValue;
    }

    /**
     * Get the file content with curl, if succeeded return the
     * file content if not returns false.
     *
     * @param string $url   The url to get the content from
     * @param bool $save
     * @throws \RuntimeException
     * @return bool|string  The content or false in case no content can be returned
     */
    protected function getFileContentWithCurl($url, $save = false)
    {
        $ch = curl_init();

        // set URL and other appropriate options
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);

        // grab URL and pass it to the browser
        $content = curl_exec($ch);
        $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_status != 200) {
            throw new \RuntimeException(
                'Could not download from url: ' . $url
            );
        }

        if ($save) {
            $fileName = $this->cacheDir . parse_url($url, PHP_URL_PATH);
            $this->fileSystem->mkdir(dirname($fileName));
            file_put_contents($fileName, $content);
        }

        return $content;
    }

    protected function getFileContent($url)
    {
        $fileName = $this->cacheDir . parse_url($url, PHP_URL_PATH);
        if (file_exists($fileName) === false) {
            throw new \RuntimeException(
                'Could not find file: ' . $fileName
            );
        }

        return file_get_contents($fileName);
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

}
