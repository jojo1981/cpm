<?php

/**
 * This file is part of the Composer Package Manager.
 *
 * (c) Joost Nijhuis <jnijhuis81@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JoostNijhuis\PackageManagerBundle\Packages;

use Composer\Downloader\SvnDownloader as BaseSvnDownloader;
use Composer\Package\PackageInterface;

/**
 * JoostNijhuis\PackageManagerBundle\Packages\SvnDownloader
 *
 * This class is a wrapper around the Composer SvnDownloader
 * and add the possibility to use credentials
 */
class SvnDownloader extends BaseSvnDownloader
{

    /**
     * @var string
     */
    protected $username;

    /**
     * @var string
     */
    protected $password;

    /**
     * Set the svn username to use for retrieving the package from svn
     *
     * @param string $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * Set the svn password to use for retrieving the package from svn
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * {@inheritDoc}
     */
    public function doDownload(PackageInterface $package, $path)
    {
        $url =  $package->getSourceUrl();
        $ref =  $package->getSourceReference();

        $this->io->write("    Checking out ".$package->getSourceReference());
        $this->execute($url, $this->getCommand('svn co'), sprintf("%s/%s", $url, $ref), null, $path);
    }

    /**
     * {@inheritDoc}
     */
    public function doUpdate(PackageInterface $initial, PackageInterface $target, $path)
    {
        $url = $target->getSourceUrl();
        $ref = $target->getSourceReference();

        $this->io->write("    Checking out " . $ref);
        $this->execute($url, $this->getCommand('svn switch'), sprintf("%s/%s", $url, $ref), $path);
    }

    /**
     * {@inheritDoc}
     */
    public function getLocalChanges($path)
    {
        $output = null;
        $this->process->execute($this->getCommand('svn status') . ' --ignore-externals', $output, $path);

        return preg_match('{^ *[^X ] +}m', $output) ? $output : null;
    }

    /**
     * {@inheritDoc}
     */
    protected function getCommitLogs($fromReference, $toReference, $path)
    {
        $output = '';
        $command = sprintf('cd %s && ' . $this->getCommand('svn log') .  ' -r%s:%s --incremental', escapeshellarg($path), $fromReference, $toReference);

        if (0 !== $this->process->execute($command, $output)) {
            throw new \RuntimeException('Failed to execute ' . $command . "\n\n" . $this->process->getErrorOutput());
        }

        return $output;
    }

    /**
     * Return the parsed command, if credentials set they will be added to
     * the command call string and returned, if no credentials are set the original
     * command call string will be returned
     *
     * @param string $command The command string for which the credentials need to be added
     * @return string         The parsed command string or the same string as passed to this method
     */
    protected function getCommand($command)
    {
        $retVal = $command;
        if (!empty($this->username)) {
            $retVal .= ' --username=' . $this->username;
        }
        if (!empty($this->password)) {
            $retVal .=  ' --password=' . $this->password;
        }

        return $retVal;
    }

}
