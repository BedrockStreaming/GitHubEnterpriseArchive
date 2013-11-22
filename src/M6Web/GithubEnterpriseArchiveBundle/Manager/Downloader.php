<?php

namespace M6Web\GithubEnterpriseArchiveBundle\Manager;

use Guzzle\Http\Client;
use M6Web\GithubEnterpriseArchiveBundle\Event\GithubEventDownloadedEvent;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * Class Downloader
 *
 * @author Adrien Samson <asamson.externe@m6.fr>
 */
class Downloader
{
    protected $client;
    protected $manager;
    protected $eventDispatcher;

    /**
     * Constructor
     *
     * @param Client               $client
     * @param DataManagerInterface $manager
     * @param EventDispatcher      $eventDispatcher
     */
    public function __construct(Client $client, DataManagerInterface $manager, EventDispatcher $eventDispatcher)
    {
        $this->client          = $client;
        $this->manager         = $manager;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Get HTTP Client
     *
     * @return Client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Get HTTP Manager
     *
     * @return DataManagerInterface
     */
    public function getManager()
    {
        return $this->manager;
    }

    /**
     * Get HTTP EventDispatcher
     *
     * @return EventDispatcher
     */
    public function getEventDispatcher()
    {
        return $this->eventDispatcher;
    }

    /**
     * Download items
     *
     * @return int Nb of items downloaded
     */
    public function download()
    {
        $lastDate    = $this->getManager()->getLastSavedDate();
        $itemsToSave = [];

        for ($page = 1; $page <= 10; $page++) {
            $response = $this->getClient()->get('/timeline.json?page='.$page)->send();

            if ($response->getContentType() !== 'application/json; charset=utf-8') {
                throw new \RuntimeException('Bad content type received');
            }

            $data = json_decode($response->getBody(), true);

            foreach ($data as $item) {
                if ($item['created_at'] <= $lastDate) {
                    break 2;
                }
                $itemsToSave[] = $item;
            }
        }

        foreach (array_reverse($itemsToSave) as $item) {
            $this->getManager()->saveItem($item);
            $this->getEventDispatcher()->dispatch(
                GithubEventDownloadedEvent::EVENT_DOWNLOAD,
                new GithubEventDownloadedEvent($item)
            );
        }

        return count($itemsToSave);
    }
}
