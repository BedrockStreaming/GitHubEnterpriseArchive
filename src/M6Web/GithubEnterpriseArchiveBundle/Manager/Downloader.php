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
     * Download items
     *
     * @return int Nb of items downloaded
     */
    public function download()
    {
        $lastDate    = $this->manager->getLastSavedDate();
        $itemsToSave = [];

        for ($page = 1; $page <= 10; $page++) {
            $response = $this->client->get('/timeline.json?page='.$page)->send();

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
            $this->manager->saveItem($item);
            $this->eventDispatcher->dispatch(
                GithubEventDownloadedEvent::EVENT_DOWNLOAD,
                new GithubEventDownloadedEvent($item)
            );
        }

        return count($itemsToSave);
    }
}
