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

use Doctrine\ORM\EntityManager;

/**
 * JoostNijhuis\PackageManagerBundle\Packages\SvnAuthentication
 *
 * Svn Authentication helper class, this class will read svn user credentials
 * from the database and tries to retrieve the right user credentials for a
 * certain svn url.
 */
class SvnAuthentication
{

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * @var \Doctrine\ORM\EntityRepository
     */
    protected $repository;

    /**
     * @var array
     */
    protected $data;

    /**
     * Constructor
     *
     * @param \Doctrine\ORM\EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
        $this->repository = $this->em->getRepository(
            'JoostNijhuis\PackageManagerBundle\Entity\SvnAuthentications'
        );
    }

    /**
     * Try to find the credentials for a svn url
     * returns an array with the keys 'username' and 'password' if a match
     * can be found, if not the return false.
     *
     * @param string $url The url to lookup
     * @return bool|array
     */
    public function getCredentialsForUrl($url)
    {
        $this->getDataFromDatabase();

        $matchedUrl = $this->getClosestMatch($url);

        if ($matchedUrl !== false) {
            return $this->data[$matchedUrl];
        }

        return false;
    }

    /**
     * Read data from the database and save it to the private property data
     * @return void
     */
    protected function getDataFromDatabase()
    {
        $this->data = array();
        $records = $this->repository->findAll();
        foreach ($records as $record) {
            $url = $record->getUrl();
            $this->data[$url]['username'] = $record->getUsername();
            $this->data[$url]['password'] = $record->getPassword();
        }
    }

    /**
     * Tries to find the closest match for an url.
     * for example the url http://svn.example.com/apps/project1
     * matches with the the second url:
     *
     * - http://svn.example.com/sites
     * - http://svn.example.com/apps  <- matches
     * - http://svn.example.com       <- second match will not returned
     * - https://svn.example.com/apps/project1
     *
     * If not match can be made, false will be returned
     *
     * @param $url         The url to lookup and find to closest match for
     * @return bool|string
     */
    protected function getClosestMatch($url)
    {
        $arrMatchesUrls = array();
        foreach ($this->data as $key => $value) {
            if (strpos($url, $key) !== false) {
                $arrMatchesUrls[] = $key;
            }
        }

        if (!empty($arrMatchesUrls)) {
            $retVal = "";
            foreach($arrMatchesUrls as $matchedUrl) {
                if (strlen($matchedUrl) > strlen($retVal)) {
                    $retVal = $matchedUrl;
                }
            }
            return $retVal;
        }

        /* Not closest match found */
        return false;
    }

}
