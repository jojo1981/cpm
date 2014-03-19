<?php
/**
 * This file is part of the Composer Package Manager.
 *
 * (c) Joost Nijhuis <jnijhuis81@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace JoostNijhuis\PackageManagerBundle\Composer\Repository;

use Composer\Package\Loader\ArrayLoader;
use Composer\Package\Loader\ValidatingArrayLoader;
use Composer\Repository\ArrayRepository;
use Composer\Repository\InvalidRepositoryException;

/**
 * JoostNijhuis\PackageManagerBundle\Composer\Repository\PrivatePackageRepository
 */
class PrivatePackageRepository extends ArrayRepository
{
    /**
     * @var array
     */
    private $data;

    /**
     * Constructor
     *
     * @param string $filename
     */
    public function __construct($filename)
    {
        $this->data = json_decode(
            file_get_contents($filename),
            true
        );
    }

    /**
     * {@inheritDoc}
     */
    protected function initialize()
    {
        parent::initialize();

        $loader = new ValidatingArrayLoader(new ArrayLoader, false);
        if (isset($this->data['packages'])) {
            foreach ($this->data['packages'] as $packageName => $packageData) {
                foreach ($packageData as $version => $data) {
                    try {
                        $package = $loader->load($data);
                        $this->addPackage($package);
                    } catch (\Exception $e) {}
                }
            }
        }
    }
}
