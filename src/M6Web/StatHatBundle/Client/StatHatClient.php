<?php

namespace M6Web\StatHatBundle\Client;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class StatHat
 *
 * @author Adrien Samson <asamson.externe@m6.fr>
 */
class StatHatClient
{
    protected $ezKey;

    /**
     * Constructor
     *
     * @param string $ezKey
     */
    public function __construct($ezKey)
    {
        $this->ezKey = $ezKey;
    }

    /**
     * Increment Count
     *
     * @param string   $statKey
     * @param int      $count
     * @param int|null $timestamp
     *
     * @return void
     */
    public function publishCount($statKey, $count = 1, $timestamp = null)
    {
        $data = array(
            'ezkey' => $this->ezKey,
            'stat'  => $statKey,
            'count' => $count,
        );
        if (!is_null($timestamp)) {
            $data['t'] = $timestamp;
        }

        $this->sendData($data);
    }

    protected function sendData($data)
    {
        $content = http_build_query($data);
        $request = Request::create(
            'http://api.stathat.com/ez', 'POST',
            [], [], [],
            ['HTTP_CONNECTION' => 'Close', 'CONTENT_LENGTH' => strlen($content)],
            $content
        );

        $fp = fsockopen('api.stathat.com', 80, $errno, $errstr, 1);
        fwrite($fp, (string) $request);
        fclose($fp);
    }
}
