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

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * JoostNijhuis\PackageManagerBundle\Console\AbstractCommand
 */
abstract class AbstractCommand extends ContainerAwareCommand
{
    /**
     * @var Pid
     */
    private $pid;

    /**
     * {@inheritDoc}
     */
    final protected function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $this->getProcessName();
        $directory = $this->getCacheDirectory();

        $this->pid = new Pid($name, $directory);

        if ($this->mustWait()) {
            $count = 0;
            while ($this->pid->isRunning()) {
                sleep(10);
                $count += 10;
                if ($count >= $this->getTimeOut() && $this->getTimeOut() != 0) {
                    $output->writeln(sprintf(
                        'Script was waiting to start, but time out: %s seconds exceeded',
                        $this->getTimeOut()
                    ));
                    return 1;
                }
            }
            $this->runProcess($input, $output);
        } else {
            if (!$this->pid->isRunning()) {
                $this->runProcess($input, $output);
            } else {
                $output->writeln(sprintf(
                    'Script is still running, PID: %s',
                    $this->pid->getPid()
                ));
                return 2;
            }
        }

        return 0;
    }

    /**
     * Get process name, to use for pid filename
     *
     * @return string
     */
    abstract protected function getProcessName();

    /**
     * Get the cache directory for finding and/or writing the pid
     * file to
     */
    protected function getCacheDirectory()
    {
        $cacheDir = $this->getParameter('kernel.cache_dir');
        $cacheDir .= DIRECTORY_SEPARATOR . 'run';

        return $cacheDir;
    }

    /**
     * Get parameter from the dependency injection container
     */
    protected function getParameter($parameter)
    {
        return $this->getContainer()->getParameter($parameter);
    }

    /**
     * Get the pid object
     *
     * @return Pid
     */
    protected function getPid()
    {
        return $this->pid;
    }

    /**
     * Run process
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    abstract protected function runProcess(
        InputInterface $input,
        OutputInterface $output
    );

    /**
     * Return true if script must wait to process, when
     * false returned the script will stop if it is already running.
     *
     * @return bool
     */
    abstract protected function mustWait();

    /**
     * Get time out in seconds, when returning 0 means
     * not time out. Only used when mustWait will return true.
     *
     * @return int
     */
    abstract protected function getTimeOut();
}
