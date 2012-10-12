<?php

/**
 * This file is part of the Composer Package Manager.
 *
 * (c) Joost Nijhuis <jnijhuis81@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JoostNijhuis\PackageManagerBundle\Packagist;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * This abstract class is a helper on which you can build your own
 * cache driver which can be injected into the CacheHandler instance.
 */
abstract class CacheDriverAbstract implements CacheDriverInterface
{

    /**
     * @var \Symfony\Component\Console\Output\OutputInterface $output
     */
    private $output;

    /**
     * @var \Symfony\Component\Console\Input\InputInterface $input
     */
    private $input;

    /**
     * Set the Output handler to use for writing output
     *
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    public function setOutputInterface(OutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * Set the Input handler to use for getting input
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input
     */
    public function setInputInterface(InputInterface $input)
    {
        $this->input = $input;
    }

    /**
     * Write text to the output interface set with the setOutputInterface method.
     *
     * @param string $text The text to let written by the output handler
     * @param bool $force  Will force to write the output even if the output handler
     *                     is set into the mode quit
     */
    protected function writeToOutput($text, $force = false)
    {
        if (isset($this->output)) {
            if ($force) {
                $oldVerbose = $this->output->getVerbosity();
                $this->output->setVerbosity(OutputInterface::VERBOSITY_NORMAL);
            }

            $this->output->writeln($text);

            if ($force) {
                $this->output->setVerbosity($oldVerbose);
            }
        }
    }

}


