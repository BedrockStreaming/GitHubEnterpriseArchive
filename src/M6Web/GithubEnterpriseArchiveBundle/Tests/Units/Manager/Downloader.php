<?php

namespace M6Web\GithubEnterpriseArchiveBundle\Tests\Units\Manager;

use M6Web\GithubEnterpriseArchiveBundle\Manager\Downloader as TestedClass;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * Class Downloader
 *
 * @author Adrien Samson <asamson.externe@m6.fr>
 */
class Downloader extends \mageekguy\atoum\test
{
    protected $data = [
        '/timeline.json?page=1' => '[{"created_at":"2013-10-02T12:00:00Z","type":"PullRequestEvent"},{"created_at":"2013-10-01T11:00:00Z","type":"CommitCommentEvent"}]',
        '/timeline.json?page=2' => '[{"created_at":"2013-10-01T10:00:00Z","type":"PullRequestEvent"}]',
    ];

    public function testDownload()
    {
        $guzzleMock = new \mock\Guzzle\Http\Client();
        $data = $this->data;
        $self = $this;
        $guzzleMock->getMockController()->get = function ($url) use ($data, $self) {
            $self->mockGenerator->orphanize('__construct');
            $requestMock = new \mock\Guzzle\Http\Message\Request();
            $requestMock->getMockController()->send = function () use ($url, $data, $self) {
                $self->mockGenerator->orphanize('__construct');
                $responseMock = new \mock\Guzzle\Http\Message\Response();
                $responseMock->getMockController()->getBody = $data[$url];
                $responseMock->getMockController()->getContentType = 'application/json; charset=utf-8';
                return $responseMock;
            };
            return $requestMock;
        };
        $dataManagerMock = new \mock\M6Web\GithubEnterpriseArchiveBundle\Manager\DataManagerInterface();
        $dataManagerMock->getMockController()->getLastSavedDate = '2013-10-01T10:00:00Z';

        $downloader = new TestedClass($guzzleMock, $dataManagerMock, new EventDispatcher());

        $this->assert
            ->integer($downloader->download())->isEqualTo(2)
            ->mock($dataManagerMock)
                ->call('saveItem')
                    ->withArguments(['created_at' => '2013-10-01T11:00:00Z', 'type' => 'CommitCommentEvent'])->once()
                    ->withArguments(['created_at' => '2013-10-02T12:00:00Z', 'type' => 'PullRequestEvent'])->once();
    }
}
