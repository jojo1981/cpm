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

use Doctrine\ORM\Mapping as ORM;

/**
 * JoostNijhuis\PackageManagerBundle\Model\Support
 */
abstract class Support implements SupportInterface
{
    /**
     * Support id.
     *
     * @var int
     * @ORM\Id
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * Email address for support.
     *
     * @var string
     * @ORM\Column(name="email_address", type="string", length=255, nullable=true)
     */
    protected $emailAddress;

    /**
     * URL to the Issue Tracker.
     *
     * @var string
     * @ORM\Column(name="issue_tracker_url", type="string", length=255, nullable=true)
     */
    protected $issueTrackerUrl;

    /**
     * URL to the Forum.
     *
     * @var string
     * @ORM\Column(name="forum_url", type="string", length=255, nullable=true)
     */
    protected $forumUrl;

    /**
     * URL to the Wiki.
     *
     * @var string
     * @ORM\Column(name="wiki_url", type="string", length=255, nullable=true)
     */
    protected $wikiUrl;

    /**
     * IRC channel for support, as irc://server/channel.
     *
     * @var string
     * @ORM\Column(name="irc_channel", type="string", length=255, nullable=true)
     */
    protected $ircChannel;

    /**
     * URL to browse or download the sources.
     *
     * @var string
     * @ORM\Column(name="source_url", type="string", length=255, nullable=true)
     */
    protected $sourceUrl;

    /**
     * {@inheritDoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritDoc}
     */
    public function setEmailAddress($emailAddress)
    {
        $this->emailAddress = $emailAddress;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getEmailAddress()
    {
        return $this->emailAddress;
    }

    /**
     * {@inheritDoc}
     */
    public function setIssueTrackerUrl($issueTrackerUrl)
    {
        $this->issueTrackerUrl = $issueTrackerUrl;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getIssueTrackerUrl()
    {
        return $this->issueTrackerUrl;
    }

    /**
     * {@inheritDoc}
     */
    public function setForumUrl($forumUrl)
    {
        $this->forumUrl = $forumUrl;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getForumUrl()
    {
        return $this->forumUrl;
    }

    /**
     * {@inheritDoc}
     */
    public function setWikiUrl($wikiUrl)
    {
        $this->wikiUrl = $wikiUrl;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getWikiUrl()
    {
        return $this->wikiUrl;
    }

    /**
     * {@inheritDoc}
     */
    public function setIrcChannel($ircChannel)
    {
        $this->ircChannel = $ircChannel;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getIrcChannel()
    {
        return $this->ircChannel;
    }

    /**
     * {@inheritDoc}
     */
    public function setSourceUrl($sourceUrl)
    {
        $this->sourceUrl = $sourceUrl;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getSourceUrl()
    {
        return $this->sourceUrl;
    }
}
