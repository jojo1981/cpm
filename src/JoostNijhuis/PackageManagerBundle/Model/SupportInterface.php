<?php
/**
 * This file is part of the Composer Package Manager.
 *
 * (c) Joost Nijhuis <jnijhuis81@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace JoostNijhuis\PackageManagerBundle\Model;

/**
 * JoostNijhuis\PackageManagerBundle\Model\SupportInterface
 */
interface SupportInterface
{
    /**
     * Get id
     *
     * @return int
     */
    public function getId();

    /**
     * Set email address.
     *
     * @param string $emailAddress
     * @return SupportInterface
     */
    public function setEmailAddress($emailAddress);

    /**
     * Get email address.
     *
     * @return string
     */
    public function getEmailAddress();

    /**
     * Set issue tracker url.
     *
     * @param string $issueTrackerUrl
     * @return SupportInterface
     */
    public function setIssueTrackerUrl($issueTrackerUrl);

    /**
     * Get issue tracker url.
     *
     * @return string
     */
    public function getIssueTrackerUrl();

    /**
     * Set forum url.
     *
     * @param string $forumUrl
     * @return SupportInterface
     */
    public function setForumUrl($forumUrl);

    /**
     * Get forum url.
     *
     * @return string
     */
    public function getForumUrl();

    /**
     * Set wiki url.
     *
     * @param string $wikiUrl
     * @return SupportInterface
     */
    public function setWikiUrl($wikiUrl);

    /**
     * Get wiki url.
     *
     * @return string
     */
    public function getWikiUrl();

    /**
     * Set IRC channel.
     *
     * @param string $ircChannel
     * @return SupportInterface
     */
    public function setIrcChannel($ircChannel);

    /**
     * Get IRC channel.
     *
     * @return string
     */
    public function getIrcChannel();

    /**
     * Set source url.
     *
     * @param string $sourceUrl
     * @return SupportInterface
     */
    public function setSourceUrl($sourceUrl);

    /**
     * Get source url.
     *
     * @return string
     */
    public function getSourceUrl();
}
