<?php

/**
 * This file is part of the Composer Package Manager.
 *
 * (c) Joost Nijhuis <jnijhuis81@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JoostNijhuis\PackageManagerBundle\ComposerRepository\Exceptions;

/**
 * Exception class which can be thrown in case the
 * \JoostNijhuis\PackageManagerBundle\ComposerRepository\BuildHander
 * can't find a file on which he depends on.
 */
class FileNotFoundException extends \Exception
{

}
