<?php

namespace M6Web\StatHatBundle\Listener;
use M6Web\StatHatBundle\Client\StatHatClient;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;


/**
 * Class EventListener
 *
 * @author Adrien Samson <asamson.externe@m6.fr>
 */
class CountEventListener
{
    protected $client;
    protected $statKey;
    protected $count;
    protected $timestamp;
    protected $expressionLanguage;

    /**
     * Constructor
     *
     * @param StatHatClient      $client
     * @param ExpressionLanguage $expressionLanguage
     * @param string             $statKey
     * @param int                $count
     * @param null               $timestamp
     */
    public function __construct(StatHatClient  $client, ExpressionLanguage $expressionLanguage, $statKey, $count = 1, $timestamp = null)
    {
        $this->client             = $client;
        $this->expressionLanguage = $expressionLanguage;
        $this->statKey            = $statKey;
        $this->count              = $count;
        $this->timestamp          = $timestamp;
    }

    /**
     * @param Event $event
     *
     * @return void
     */
    public function onEvent(Event $event)
    {
        $statKey   = $this->evaluate($this->statKey, $event);
        $count     = $this->evaluate($this->count, $event);
        $timestamp = $this->evaluate($this->timestamp, $event);

        $this->client->publishCount($statKey, $count, $timestamp);
    }

    protected function evaluate($expr, Event $event)
    {
        if (!preg_match('/^expr\((?P<expression>.+)\)$/', $expr, $matches)) {
            return $expr;
        }

        return $this->expressionLanguage->evaluate($matches['expression'], ['event' => $event]);
    }
}
