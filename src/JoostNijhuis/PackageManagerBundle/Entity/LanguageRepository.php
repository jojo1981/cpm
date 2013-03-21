<?php

/**
 * This file is part of the Composer Package Manager.
 *
 * (c) Joost Nijhuis <jnijhuis81@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JoostNijhuis\PackageManagerBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * JoostNijhuis\PackageManagerBundle\Entity\LanguageRepository
 */
class LanguageRepository extends EntityRepository
{

    /**
     * Return key => value list for a select box.
     *
     * @return array:
     */
    public function getForSelectBox()
    {
        $retVal = array();
        $data = $this->createQueryBuilder('l')
            ->select('l.code, l.name')
            ->where('l.active = 1')
            ->orderBy('l.sort_order', 'ASC')
            ->getQuery()
            ->getArrayResult();
        
        foreach ($data as $index => $record) {
            $retVal[$record['code']] = $record['name'];
        }
        
        return $retVal;
    }

    /**
     * Get all available locale, the locales for
     * all active languages
     *
     * @return array
     */
    public function getAvailableLocales()
    {
        $retVal = array();
        $data = $this->createQueryBuilder('l')
            ->select('l.code')
            ->where('l.active = 1')
            ->orderBy('l.sort_order', 'ASC')
            ->getQuery()
            ->getArrayResult();
        
        foreach ($data as $index => $record) {
            $retVal[] = $record['code'];
        }
        
        return $retVal;
    }

}
