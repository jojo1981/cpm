<?php
/**
 * This file is part of the Composer Package Manager.
 *
 * (c) Joost Nijhuis <jnijhuis81@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace JoostNijhuis\PackageManagerBundle\Console;

use Composer\Util\Filesystem;

/**
 * JoostNijhuis\PackageManagerBundle\Console\Pid
 */
class Pid
{
    /**
     * @var string
     */
    private $pidFile;

    /**
     * @var bool
     */
    private $isRunning = false;

    /**
     * @var int
     */
    private $pid;

    /**
     * @var Filesystem
     */
    private $fs;

    /**
     * Constructor
     *
     * @param string $name
     * @param string $directory
     * @throws \RuntimeException
     */
    function __construct($name, $directory)
    {
        $this->fs = new Filesystem();
        $this->fs->ensureDirectoryExists($directory);

        $this->pidFile = $directory . DIRECTORY_SEPARATOR . $name . '.pid';

        if (is_writable($this->pidFile) || is_writable($directory)) {
            if (file_exists($this->pidFile)) {
                $this->pid = (int)trim(file_get_contents($this->pidFile));
                if (posix_kill($this->pid, 0)) {
                    $this->isRunning = true;
                }
            }
        } else {
            throw new \RuntimeException(sprintf(
                "Cannot write to pid file %s",
                $this->pidFile
            ));
        }

        if ($this->isRunning === false) {
            $this->pid = getmypid();
            file_put_contents($this->pidFile, $this->pid);
        }
    }

    /**
     * Destructor
     *
     * Needed to remove the pid file after this script is successfully executed
     */
    public function __destruct()
    {
        if (!$this->isRunning
        && file_exists($this->pidFile)
        && is_writeable($this->pidFile)) {
            $this->fs->remove($this->pidFile);
        }
    }

    /**
     * Return true if this script is still running from a previous run
     *
     * @return bool
     */
    public function isRunning()
    {
        return $this->isRunning;
    }

    /**
     * Get the current pid
     *
     * @return int
     */
    public function getPid()
    {
        return $this->pid;
    }
}
