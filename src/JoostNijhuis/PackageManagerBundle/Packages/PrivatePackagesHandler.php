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

/**
 * JoostNijhuis\PackageManagerBundle\Packages\PrivatePackagesHandler
 *
 * This class is responsible for get data from the
 * generated output file which contains all the private packages
 * and/or the packages which are added manual because they are not
 * registered on packagist.org
 */
class PrivatePackagesHandler
{
    /**
     * @var string $file
     */
    protected $file;

    /**
     * Constructor
     *
     * @param string $file      The generated output file (json) with all
     *                          private package data and/or manual added
     *                          packages
     */
    public function __construct($file)
    {
        $this->file = $file;
    }

    /**
     * Returns an array with all packages
     *
     * @return mixed
     */
    public function getPrivatePackages()
    {
        $arrPackages = $this->getPackages();
        return $arrPackages;
    }

    /**
     * Returns a restructured array which is easier to use in
     * the template views.
     *
     * @return array
     */
    public function getDataForTemplate()
    {
        $arrRet = array();
        $arrPackages = $this->getPackages();
        foreach ($arrPackages as $name => $data) {
            $arrRet[] = $this->getPackageData($name, $data);
        }
        return $arrRet;
    }

    /**
     * Get an array with all packages read from the generated output
     * json file
     *
     * @return array
     */
    protected function getPackages()
    {
        $arrData = array();
        if (file_exists($this->file)) {
            $arrData = json_decode(file_get_contents($this->file), true);
            $arrData = $arrData['packages'];
        }

        return $arrData;
    }

    /**
     * Get a structured array which can be used inside views
     *
     * @param string $name         package name
     * @param array $packageData   package data
     * @return array               new structured array
     */
    protected function getPackageData($name, array $packageData)
    {
        $arrRet = array();
        $arrRet['authors'] = array();
        $arrRet['licenses'] = array();
        $arrRet['packagename'] = $name;

        $arrAuthors = array();
        $arrLicenses = array();
        $arrReleases = array();
        foreach ($packageData as $version => $data) {
            if (isset($data['authors'])) {
                foreach ($data['authors'] as $author) {
                    $key = str_replace(' ', '_', strtolower(
                        implode('_', array_values($author)))
                    );
                    $a = array(
                        'name'     => '',
                        'homepage' => '',
                        'email'    => '',
                        'role'     => ''
                    );
                    $arrAuthors[$key] = array_merge($a, $author);
                }
            }

            if (isset($data['license'])) {
                foreach ($data['license'] as $license) {
                    $key = strtolower(str_replace(' ', '_', $license));
                    $arrLicenses[$key] = $license;
                }
            }

            $key = count($arrReleases);
            $arrReleases[$key]['version'] = $version;
            $arrRet['packagedescription'] = (isset($data['description']) ? $data['description'] : '');

            if (isset($data['dist'])) {
                $urlData = $data['dist'];
            } else {
                $urlData = $data['source'];
            }

            if ($urlData['type'] == 'svn') {
                $url = $urlData['url'] . $urlData['reference'];
            } else {
                $url = $urlData['url'];
            }

            $parts = parse_url($url);
            $url = $parts['scheme'] . '://' . $parts['host'] . $parts['path'] . (isset($parts['query']) ? $parts['query'] : '');
            $arrReleases[$key]['url'] = $url;
        }

        $arrRet['releases'] = $arrReleases;

        foreach ($arrAuthors as $author) {
            $arrRet['authors'][] = $author;
        }

        foreach ($arrLicenses as $license) {
            $arrRet['licenses'][] = $license;
        }

        return $arrRet;
    }
}
