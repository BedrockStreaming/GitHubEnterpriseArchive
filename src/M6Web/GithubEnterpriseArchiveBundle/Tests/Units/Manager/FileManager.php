<?php

namespace M6Web\GithubEnterpriseArchiveBundle\Tests\Units\Manager;

use M6Web\GithubEnterpriseArchiveBundle\Manager\FileManager as TestedClass;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class FileManager
 *
 * @author Adrien Samson <asamson.externe@m6.fr>
 */
class FileManager extends \mageekguy\atoum\test
{
    protected $workingDir;

    protected function getFileManager()
    {
        $this->workingDir = __DIR__ . '/../../../../../../app/cache/test/data-dir';
        $fs = new Filesystem();
        $fs->mirror(__DIR__.'/../../Fixtures/data-dir', $this->workingDir, null, ['override' => true, 'delete' => true]);

        return new TestedClass($this->workingDir);
    }

    public function testAll()
    {
        $manager = $this->getFileManager();
        $this->assert->string($manager->getLastSavedDate())->isEqualTo('2013-10-03T12:00:00Z');

        $manager->saveItem(['created_at' => '2013-10-03T13:00:00Z', 'type' => 'CommitCommentEvent']);
        $this->assert
            ->string(file_get_contents($this->workingDir.'/2013-10-03/index.txt'))->isEqualTo('3')
            ->string(file_get_contents($this->workingDir.'/2013-10-03/0003'))
                ->isEqualTo('{"created_at":"2013-10-03T13:00:00Z","type":"CommitCommentEvent"}');

        $manager->saveItem(['created_at' => '2013-10-04T12:00:00Z', 'type' => 'PullRequestEvent']);
        $this->assert
            ->string(file_get_contents($this->workingDir.'/index.txt'))->isEqualTo('2013-10-04')
            ->string(file_get_contents($this->workingDir.'/2013-10-04/index.txt'))->isEqualTo('1')
            ->string(file_get_contents($this->workingDir.'/2013-10-04/0001'))
                ->isEqualTo('{"created_at":"2013-10-04T12:00:00Z","type":"PullRequestEvent"}');

        $this->assert
            ->array($manager->getEvents(2013, 10, 03))
                ->isEqualTo([
                    ['created_at' => '2013-10-03T13:00:00Z', 'type' => 'CommitCommentEvent'],
                    ['created_at' => '2013-10-03T12:00:00Z', 'type' => 'CommitCommentEvent'],
                    ['created_at' => '2013-10-03T10:00:00Z', 'type' => 'PullRequestEvent']
                ])
            ->array($manager->getEvents(2013, 10, 04))
                ->isEqualTo([['created_at' => '2013-10-04T12:00:00Z', 'type' => 'PullRequestEvent']])
            ->array($manager->getEvents(2013, 10))
                ->isEqualTo([
                    ['created_at' => '2013-10-04T12:00:00Z', 'type' => 'PullRequestEvent'],
                    ['created_at' => '2013-10-03T13:00:00Z', 'type' => 'CommitCommentEvent'],
                    ['created_at' => '2013-10-03T12:00:00Z', 'type' => 'CommitCommentEvent'],
                    ['created_at' => '2013-10-03T10:00:00Z', 'type' => 'PullRequestEvent'],
                    ['created_at' => '2013-10-01T10:00:00Z', 'type' => 'PullRequestEvent']
                ]);
    }
}
