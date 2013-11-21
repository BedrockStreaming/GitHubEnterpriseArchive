<?php

namespace M6Web\GithubEnterpriseArchiveBundle\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Class ItemDownloadedEvent
 *
 * @author Adrien Samson <asamson.externe@m6.fr>
 */
class GithubEventDownloadedEvent extends Event
{
    const EVENT_DOWNLOAD = 'm6_web_github_enterprise_archive.event_download';

    protected $githubEvent;

    /**
     * Constructor
     *
     * @param array $githubEvent
     */
    public function __construct($githubEvent)
    {
        $this->githubEvent = $githubEvent;
    }

    /**
     * Get Github event
     *
     * @return array
     */
    public function getGithubEvent()
    {
        return $this->githubEvent;
    }

    /**
     * Get Github event creation date
     *
     * @return \DateTime
     */
    public function getGithubEventCreatedAt()
    {
        return new \DateTime($this->githubEvent['created_at']);
    }
}
