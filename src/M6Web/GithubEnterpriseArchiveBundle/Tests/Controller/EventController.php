<?php

namespace M6Web\GithubEnterpriseArchiveBundle\Tests\Controller;

use Symfony\Component\Filesystem\Filesystem;
use atoum\AtoumBundle\Test\Controller\ControllerTest;

/**
* Test of EventController
*
* @author Florent Dubost <fdubost.externe@m6.fr>
*/
class EventController extends ControllerTest
{
    public function testRun()
    {
        $this->init()
            ->checkBadRequest()
            ->checkDisabled()
            ->checkGetByDay()
            ->checkGetByDayWithPagination()
            ->checkGetByMonth()
            ->checkGetByMonthWithPagination()
            ->checkGetByYear()
            ->checkGetByYearWithPagination()
            ->checkEvents()
            ->checkEventsWithPagination();
    }

    protected function init()
    {
        $this->workingDir = __DIR__ . '/../../../../../app/cache/test/data-dir';
        $fs = new Filesystem();
        $fs->mirror(__DIR__.'/../Fixtures/data-dir', $this->workingDir, null, ['override' => true, 'delete' => true]);

        return $this;
    }

    protected function checkBadRequest()
    {
        $this->request(['debug' => true])
            ->GET('/api/events/toto')
                ->hasStatus(404);

        return $this;
    }

    protected function checkDisabled()
    {
        $this->request(['debug' => true])
            ->POST('/api/events/2013-10-01')
                ->hasStatus(405)
            ->POST('/api/events/2013-10')
                ->hasStatus(405)
            ->POST('/api/events/2013')
                ->hasStatus(405)
            ->POST('/api/events')
                ->hasStatus(405);

        return $this;
    }

    protected function checkGetByDay()
    {
        $response = $this->request(['debug' => true])
            ->GET('/api/events/2013-10-03')
                ->hasStatus(200)
                ->getValue();

        $data = json_decode($response->getContent(), true);

        $this->assert
            ->string($data['page'])
                ->isEqualTo(1)
            ->string($data['limit'])
                ->isEqualTo(20)
            ->integer($data['pages'])
            ->array($data['_links'])
                ->hasKeys(['self', 'first', 'last']);

        $json = $data['_embedded']['events'];

        $this->assert
            ->array($json)
                ->hasSize(2)
            ->string($json[0]['created_at'])
                ->isEqualTo('2013-10-03T12:00:00Z')
            ->string($json[0]['type'])
                ->isEqualTo('CommitCommentEvent')
            ->string($json[1]['created_at'])
                ->isEqualTo('2013-10-03T10:00:00Z')
            ->string($json[1]['type'])
                ->isEqualTo('PullRequestEvent');

        $response = $this->request(['debug' => true])
            ->GET('/api/events/2013-10-04')
                ->hasStatus(200)
                ->getValue();

        $json = json_decode($response->getContent(), true)['_embedded']['events'];
        $this->assert
            ->array($json)
                ->isEmpty();

        return $this;
    }

    protected function checkGetByDayWithPagination()
    {
        // Page 1

        $response = $this->request(['debug' => true])
            ->GET('/api/events/2013-10-03?page=1&per_page=1')
                ->hasStatus(200)
                ->getValue();

        $data = json_decode($response->getContent(), true);

        $this->assert
            ->string($data['page'])
                ->isEqualTo(1)
            ->string($data['limit'])
                ->isEqualTo(1)
            ->integer($data['pages'])
                ->isEqualTo(2)
            ->array($data['_links'])
                ->isEqualTo(array(
                    'self'  => ['href' => 'http://localhost/api/events/2013-10-03?page=1&per_page=1'],
                    'first' => ['href' => 'http://localhost/api/events/2013-10-03?page=1&per_page=1'],
                    'last'  => ['href' => 'http://localhost/api/events/2013-10-03?page=2&per_page=1'],
                    'next'  => ['href' => 'http://localhost/api/events/2013-10-03?page=2&per_page=1'],
                ));

        $json = $data['_embedded']['events'];

        $this->assert
            ->array($json)
                ->hasSize(1)
            ->string($json[0]['created_at'])
                ->isEqualTo('2013-10-03T12:00:00Z')
            ->string($json[0]['type'])
                ->isEqualTo('CommitCommentEvent');

        // Page 2

        $response = $this->request(['debug' => true])
            ->GET('/api/events/2013-10-03?page=2&per_page=1')
                ->hasStatus(200)
                ->getValue();

        
        $data = json_decode($response->getContent(), true);

        $this->assert
            ->string($data['page'])
                ->isEqualTo(2)
            ->string($data['limit'])
                ->isEqualTo(1)
            ->integer($data['pages'])
                ->isEqualTo(2)
            ->array($data['_links'])
                ->isEqualTo(array(
                    'self'      => ['href' => 'http://localhost/api/events/2013-10-03?page=2&per_page=1'],
                    'first'     => ['href' => 'http://localhost/api/events/2013-10-03?page=1&per_page=1'],
                    'last'      => ['href' => 'http://localhost/api/events/2013-10-03?page=2&per_page=1'],
                    'previous'  => ['href' => 'http://localhost/api/events/2013-10-03?page=1&per_page=1'],
                ));

        $json = $data['_embedded']['events'];

        $this->assert
            ->array($json)
                ->hasSize(1)
            ->string($json[0]['created_at'])
                ->isEqualTo('2013-10-03T10:00:00Z')
            ->string($json[0]['type'])
                ->isEqualTo('PullRequestEvent');

        // Page 3

        $response = $this->request(['debug' => true])
            ->GET('/api/events/2013-10-03?page=3&per_page=1')
                ->hasStatus(200)
                ->getValue();

        $data = json_decode($response->getContent(), true);

        $this->assert
            ->string($data['page'])
                ->isEqualTo(3)
            ->string($data['limit'])
                ->isEqualTo(1)
            ->integer($data['pages'])
                ->isEqualTo(2)
            ->array($data['_links'])
                ->isEqualTo(array(
                    'self'      => ['href' => 'http://localhost/api/events/2013-10-03?page=3&per_page=1'],
                    'first'     => ['href' => 'http://localhost/api/events/2013-10-03?page=1&per_page=1'],
                    'last'      => ['href' => 'http://localhost/api/events/2013-10-03?page=2&per_page=1'],
                    'previous'  => ['href' => 'http://localhost/api/events/2013-10-03?page=2&per_page=1'],
                ));

        $json = $data['_embedded']['events'];

        $this->assert
            ->array($json)
                ->isEmpty();

        return $this;
    }

    protected function checkGetByMonth()
    {
        $response = $this->request(['debug' => true])
            ->GET('/api/events/2013-10')
                ->hasStatus(200)
                ->getValue();

        $data = json_decode($response->getContent(), true);

        $this->assert
            ->string($data['page'])
                ->isEqualTo(1)
            ->string($data['limit'])
                ->isEqualTo(20)
            ->integer($data['pages'])
            ->array($data['_links'])
                ->hasKeys(['self', 'first', 'last']);

        $json = $data['_embedded']['events'];

        $this->assert
            ->array($json)
                ->hasSize(3)
            ->string($json[0]['created_at'])
                ->isEqualTo('2013-10-03T12:00:00Z')
            ->string($json[0]['type'])
                ->isEqualTo('CommitCommentEvent')
            ->string($json[1]['created_at'])
                ->isEqualTo('2013-10-03T10:00:00Z')
            ->string($json[1]['type'])
                ->isEqualTo('PullRequestEvent')
            ->string($json[2]['created_at'])
                ->isEqualTo('2013-10-01T10:00:00Z')
            ->string($json[2]['type'])
                ->isEqualTo('PullRequestEvent');

        $response = $this->request(['debug' => true])
            ->GET('/api/events/2013-09')
                ->hasStatus(200)
                ->getValue();

        $json = json_decode($response->getContent(), true)['_embedded']['events'];
        $this->assert
            ->array($json)
                ->isEmpty();

        return $this;
    }

    protected function checkGetByMonthWithPagination()
    {
        // Page 1

        $response = $this->request(['debug' => true])
            ->GET('/api/events/2013-10?page=1&per_page=2')
                ->hasStatus(200)
                ->getValue();

        $data = json_decode($response->getContent(), true);

        $this->assert
            ->string($data['page'])
                ->isEqualTo(1)
            ->string($data['limit'])
                ->isEqualTo(2)
            ->integer($data['pages'])
                ->isEqualTo(2)
            ->array($data['_links'])
                ->isEqualTo(array(
                    'self'  => ['href' => 'http://localhost/api/events/2013-10?page=1&per_page=2'],
                    'first' => ['href' => 'http://localhost/api/events/2013-10?page=1&per_page=2'],
                    'last'  => ['href' => 'http://localhost/api/events/2013-10?page=2&per_page=2'],
                    'next'  => ['href' => 'http://localhost/api/events/2013-10?page=2&per_page=2'],
                ));

        $json = $data['_embedded']['events'];

        $this->assert
            ->array($json)
                ->hasSize(2)
            ->string($json[0]['created_at'])
                ->isEqualTo('2013-10-03T12:00:00Z')
            ->string($json[0]['type'])
                ->isEqualTo('CommitCommentEvent')
            ->string($json[1]['created_at'])
                ->isEqualTo('2013-10-03T10:00:00Z')
            ->string($json[1]['type'])
                ->isEqualTo('PullRequestEvent');

        // Page 2

        $response = $this->request(['debug' => true])
            ->GET('/api/events/2013-10?page=2&per_page=2')
                ->hasStatus(200)
                ->getValue();

        $data = json_decode($response->getContent(), true);

        $this->assert
            ->string($data['page'])
                ->isEqualTo(2)
            ->string($data['limit'])
                ->isEqualTo(2)
            ->integer($data['pages'])
                ->isEqualTo(2)
            ->array($data['_links'])
                ->isEqualTo(array(
                    'self'      => ['href' => 'http://localhost/api/events/2013-10?page=2&per_page=2'],
                    'first'     => ['href' => 'http://localhost/api/events/2013-10?page=1&per_page=2'],
                    'last'      => ['href' => 'http://localhost/api/events/2013-10?page=2&per_page=2'],
                    'previous'  => ['href' => 'http://localhost/api/events/2013-10?page=1&per_page=2'],
                ));

        $json = $data['_embedded']['events'];

        $this->assert
            ->array($json)
                ->hasSize(1)
            ->string($json[0]['created_at'])
                ->isEqualTo('2013-10-01T10:00:00Z')
            ->string($json[0]['type'])
                ->isEqualTo('PullRequestEvent');

        // Page 3

        $response = $this->request(['debug' => true])
            ->GET('/api/events/2013-10?page=3&per_page=2')
                ->hasStatus(200)
                ->getValue();

        $data = json_decode($response->getContent(), true);

        $this->assert
            ->string($data['page'])
                ->isEqualTo(3)
            ->string($data['limit'])
                ->isEqualTo(2)
            ->integer($data['pages'])
                ->isEqualTo(2)
            ->array($data['_links'])
                ->isEqualTo(array(
                    'self'      => ['href' => 'http://localhost/api/events/2013-10?page=3&per_page=2'],
                    'first'     => ['href' => 'http://localhost/api/events/2013-10?page=1&per_page=2'],
                    'last'      => ['href' => 'http://localhost/api/events/2013-10?page=2&per_page=2'],
                    'previous'  => ['href' => 'http://localhost/api/events/2013-10?page=2&per_page=2'],
                ));

        $json = $data['_embedded']['events'];

        $this->assert
            ->array($json)
                ->isEmpty();

        return $this;
    }

    protected function checkGetByYear()
    {
        $response = $this->request(['debug' => true])
            ->GET('/api/events/2013')
                ->hasStatus(200)
                ->getValue();

        $data = json_decode($response->getContent(), true);

        $this->assert
            ->string($data['page'])
                ->isEqualTo(1)
            ->string($data['limit'])
                ->isEqualTo(20)
            ->integer($data['pages'])
            ->array($data['_links'])
                ->hasKeys(['self', 'first', 'last']);

        $json = $data['_embedded']['events'];

        $this->assert
            ->array($json)
                ->hasSize(4)
            ->string($json[0]['created_at'])
                ->isEqualTo('2013-11-01T10:00:00Z')
            ->string($json[0]['type'])
                ->isEqualTo('PullRequestEvent')
            ->string($json[1]['created_at'])
                ->isEqualTo('2013-10-03T12:00:00Z')
            ->string($json[1]['type'])
                ->isEqualTo('CommitCommentEvent')
            ->string($json[2]['created_at'])
                ->isEqualTo('2013-10-03T10:00:00Z')
            ->string($json[2]['type'])
                ->isEqualTo('PullRequestEvent')
            ->string($json[3]['created_at'])
                ->isEqualTo('2013-10-01T10:00:00Z')
            ->string($json[3]['type'])
                ->isEqualTo('PullRequestEvent');

        $response = $this->request(['debug' => true])
            ->GET('/api/events/2011')
                ->hasStatus(200)
                ->getValue();

        $json = json_decode($response->getContent(), true)['_embedded']['events'];
        $this->assert
            ->array($json)
                ->isEmpty();

        return $this;
    }

    protected function checkGetByYearWithPagination()
    {
        // Page 1

        $response = $this->request(['debug' => true])
            ->GET('/api/events/2013?page=1&per_page=2')
                ->hasStatus(200)
                ->getValue();

        $data = json_decode($response->getContent(), true);

        $this->assert
            ->string($data['page'])
                ->isEqualTo(1)
            ->string($data['limit'])
                ->isEqualTo(2)
            ->integer($data['pages'])
                ->isEqualTo(2)
            ->array($data['_links'])
                ->isEqualTo(array(
                    'self'  => ['href' => 'http://localhost/api/events/2013?page=1&per_page=2'],
                    'first' => ['href' => 'http://localhost/api/events/2013?page=1&per_page=2'],
                    'last'  => ['href' => 'http://localhost/api/events/2013?page=2&per_page=2'],
                    'next'  => ['href' => 'http://localhost/api/events/2013?page=2&per_page=2'],
                ));

        $json = $data['_embedded']['events'];

        $this->assert
            ->array($json)
                ->hasSize(2)
            ->string($json[0]['created_at'])
                ->isEqualTo('2013-11-01T10:00:00Z')
            ->string($json[0]['type'])
                ->isEqualTo('PullRequestEvent')
            ->string($json[1]['created_at'])
                ->isEqualTo('2013-10-03T12:00:00Z')
            ->string($json[1]['type'])
                ->isEqualTo('CommitCommentEvent');

        // Page 2

        $response = $this->request(['debug' => true])
            ->GET('/api/events/2013?page=2&per_page=2')
                ->hasStatus(200)
                ->getValue();

        $data = json_decode($response->getContent(), true);

        $this->assert
            ->string($data['page'])
                ->isEqualTo(2)
            ->string($data['limit'])
                ->isEqualTo(2)
            ->integer($data['pages'])
                ->isEqualTo(2)
            ->array($data['_links'])
                ->isEqualTo(array(
                    'self'      => ['href' => 'http://localhost/api/events/2013?page=2&per_page=2'],
                    'first'     => ['href' => 'http://localhost/api/events/2013?page=1&per_page=2'],
                    'last'      => ['href' => 'http://localhost/api/events/2013?page=2&per_page=2'],
                    'previous'  => ['href' => 'http://localhost/api/events/2013?page=1&per_page=2'],
                ));

        $json = $data['_embedded']['events'];

        $this->assert
            ->array($json)
                ->hasSize(2)
            ->string($json[0]['created_at'])
                ->isEqualTo('2013-10-03T10:00:00Z')
            ->string($json[0]['type'])
                ->isEqualTo('PullRequestEvent')
            ->string($json[1]['created_at'])
                ->isEqualTo('2013-10-01T10:00:00Z')
            ->string($json[1]['type'])
                ->isEqualTo('PullRequestEvent');

        // Page 3

        $response = $this->request(['debug' => true])
            ->GET('/api/events/2013?page=3&per_page=2')
                ->hasStatus(200)
                ->getValue();

        $data = json_decode($response->getContent(), true);

        $this->assert
            ->string($data['page'])
                ->isEqualTo(3)
            ->string($data['limit'])
                ->isEqualTo(2)
            ->integer($data['pages'])
                ->isEqualTo(2)
            ->array($data['_links'])
                ->isEqualTo(array(
                    'self'      => ['href' => 'http://localhost/api/events/2013?page=3&per_page=2'],
                    'first'     => ['href' => 'http://localhost/api/events/2013?page=1&per_page=2'],
                    'last'      => ['href' => 'http://localhost/api/events/2013?page=2&per_page=2'],
                    'previous'  => ['href' => 'http://localhost/api/events/2013?page=2&per_page=2'],
                ));

        $json = $data['_embedded']['events'];

        $this->assert
            ->array($json)
                ->isEmpty();

        return $this;
    }

    protected function checkEvents()
    {
        $response = $this->request(['debug' => true])
            ->GET('/api/events')
                ->hasStatus(200)
                ->getValue();

        $data = json_decode($response->getContent(), true);

        $this->assert
            ->string($data['page'])
                ->isEqualTo(1)
            ->string($data['limit'])
                ->isEqualTo(20)
            ->integer($data['pages'])
            ->array($data['_links'])
                ->hasKeys(['self', 'first', 'last']);

        $json = $data['_embedded']['events'];

        $this->assert
            ->array($json)
                ->hasSize(5)
            ->string($json[0]['created_at'])
                ->isEqualTo('2013-11-01T10:00:00Z')
            ->string($json[0]['type'])
                ->isEqualTo('PullRequestEvent')
            ->string($json[1]['created_at'])
                ->isEqualTo('2013-10-03T12:00:00Z')
            ->string($json[1]['type'])
                ->isEqualTo('CommitCommentEvent')
            ->string($json[2]['created_at'])
                ->isEqualTo('2013-10-03T10:00:00Z')
            ->string($json[2]['type'])
                ->isEqualTo('PullRequestEvent')
            ->string($json[3]['created_at'])
                ->isEqualTo('2013-10-01T10:00:00Z')
            ->string($json[3]['type'])
                ->isEqualTo('PullRequestEvent')
            ->string($json[4]['created_at'])
                ->isEqualTo('2012-10-02T10:00:00Z')
            ->string($json[4]['type'])
                ->isEqualTo('PullRequestEvent');

        return $this;
    }

    protected function checkEventsWithPagination()
    {
        // Page 1

        $response = $this->request(['debug' => true])
            ->GET('/api/events?page=1&per_page=4')
                ->hasStatus(200)
                ->getValue();

        $data = json_decode($response->getContent(), true);

        $this->assert
            ->string($data['page'])
                ->isEqualTo(1)
            ->string($data['limit'])
                ->isEqualTo(4)
            ->integer($data['pages'])
                ->isEqualTo(2)
            ->array($data['_links'])
                ->isEqualTo(array(
                    'self'  => ['href' => 'http://localhost/api/events?page=1&per_page=4'],
                    'first' => ['href' => 'http://localhost/api/events?page=1&per_page=4'],
                    'last'  => ['href' => 'http://localhost/api/events?page=2&per_page=4'],
                    'next'  => ['href' => 'http://localhost/api/events?page=2&per_page=4'],
                ));

        $json = $data['_embedded']['events'];

        $this->assert
            ->array($json)
                ->hasSize(4)
            ->string($json[0]['created_at'])
                ->isEqualTo('2013-11-01T10:00:00Z')
            ->string($json[0]['type'])
                ->isEqualTo('PullRequestEvent')
            ->string($json[1]['created_at'])
                ->isEqualTo('2013-10-03T12:00:00Z')
            ->string($json[1]['type'])
                ->isEqualTo('CommitCommentEvent')
            ->string($json[2]['created_at'])
                ->isEqualTo('2013-10-03T10:00:00Z')
            ->string($json[2]['type'])
                ->isEqualTo('PullRequestEvent')
            ->string($json[3]['created_at'])
                ->isEqualTo('2013-10-01T10:00:00Z')
            ->string($json[3]['type'])
                ->isEqualTo('PullRequestEvent');

        // Page 2

        $response = $this->request(['debug' => true])
            ->GET('/api/events?page=2&per_page=4')
                ->hasStatus(200)
                ->getValue();

        $data = json_decode($response->getContent(), true);

        $this->assert
            ->string($data['page'])
                ->isEqualTo(2)
            ->string($data['limit'])
                ->isEqualTo(4)
            ->integer($data['pages'])
                ->isEqualTo(2)
            ->array($data['_links'])
                ->isEqualTo(array(
                    'self'      => ['href' => 'http://localhost/api/events?page=2&per_page=4'],
                    'first'     => ['href' => 'http://localhost/api/events?page=1&per_page=4'],
                    'last'      => ['href' => 'http://localhost/api/events?page=2&per_page=4'],
                    'previous'  => ['href' => 'http://localhost/api/events?page=1&per_page=4'],
                ));

        $json = $data['_embedded']['events'];

        $this->assert
            ->array($json)
                ->hasSize(1)
            ->string($json[0]['created_at'])
                ->isEqualTo('2012-10-02T10:00:00Z')
            ->string($json[0]['type'])
                ->isEqualTo('PullRequestEvent');

        // Page 3

        $response = $this->request(['debug' => true])
            ->GET('/api/events?page=3&per_page=4')
                ->hasStatus(200)
                ->getValue();

        $data = json_decode($response->getContent(), true);

        $this->assert
            ->string($data['page'])
                ->isEqualTo(3)
            ->string($data['limit'])
                ->isEqualTo(4)
            ->integer($data['pages'])
                ->isEqualTo(2)
            ->array($data['_links'])
                ->isEqualTo(array(
                    'self'      => ['href' => 'http://localhost/api/events?page=3&per_page=4'],
                    'first'     => ['href' => 'http://localhost/api/events?page=1&per_page=4'],
                    'last'      => ['href' => 'http://localhost/api/events?page=2&per_page=4'],
                    'previous'  => ['href' => 'http://localhost/api/events?page=2&per_page=4'],
                ));

        $json = $data['_embedded']['events'];

        $this->assert
            ->array($json)
                ->isEmpty();

        return $this;
    }
}
