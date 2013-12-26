<?php

namespace M6Web\GithubEnterpriseArchiveBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations\Route;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Request\ParamFetcher;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Hateoas\Representation\PaginatedRepresentation;
use Hateoas\Representation\CollectionRepresentation;

/**
 * Class EventController
 *
 * @author Adrien Samson <asamson.externe@m6.fr>
 */
class EventController extends FOSRestController
{
    /**
     * Get events by day
     *
     * @param int          $year         Year
     * @param int          $month        Month
     * @param int          $day          Day
     * @param ParamFetcher $paramFetcher ParamFetcher
     *
     * @return \FOS\RestBundle\View\View
     *
     * @Route(pattern="/events/{year}-{month}-{day}", requirements={"year"="\d{4}", "month"="\d{2}", "day"="\d{2}"})
     *
     * @QueryParam(name="page", requirements="\d+", default="1", description="Current page index")
     * @QueryParam(name="per_page", requirements="\d+", default="20", description="Number of elements displayed per page")
     *
     * @ApiDoc(
     *   section="Events",
     *   description="Get events by day",
     *   statusCodes={
     *     200="OK"
     *   }
     * )
     */
    public function getEventsByDayAction($year, $month, $day, ParamFetcher $paramFetcher)
    {
        list($start, $limit) = $this->getStartAndLimitFromParams($paramFetcher);

        $manager    = $this->get('m6_web_github_enterprise_archive.file_manager');
        $data       = $manager->getEvents($year, $month, $day, $start, $limit);
        $eventCount = $manager->getEventCount($year, $month, $day);

        return $this->view($this->getPaginatedData($data, $eventCount, $paramFetcher));
    }

    /**
     * Get events by month
     *
     * @param int          $year         Year
     * @param int          $month        Month
     * @param ParamFetcher $paramFetcher ParamFetcher
     *
     * @return \FOS\RestBundle\View\View
     *
     * @Route(pattern="/events/{year}-{month}", requirements={"year"="\d{4}", "month"="\d{2}"})
     *
     * @QueryParam(name="page", requirements="\d+", default="1", description="Current page index")
     * @QueryParam(name="per_page", requirements="\d+", default="20", description="Number of elements displayed per page")
     *
     * @ApiDoc(
     *   section="Events",
     *   description="Get events by month",
     *   statusCodes={
     *     200="OK"
     *   }
     * )
     */
    public function getEventsByMonthAction($year, $month, ParamFetcher $paramFetcher)
    {
        list($start, $limit) = $this->getStartAndLimitFromParams($paramFetcher);

        $manager    = $this->get('m6_web_github_enterprise_archive.file_manager');
        $data       = $manager->getEvents($year, $month, null, $start, $limit);
        $eventCount = $manager->getEventCount($year, $month);

        return $this->view($this->getPaginatedData($data, $eventCount, $paramFetcher));
    }

    /**
     * Get events by year
     *
     * @param int          $year         Year
     * @param ParamFetcher $paramFetcher ParamFetcher
     *
     * @return \FOS\RestBundle\View\View
     *
     * @Route(pattern="/events/{year}", requirements={"year"="\d{4}"})
     *
     * @QueryParam(name="page", requirements="\d+", default="1", description="Current page index")
     * @QueryParam(name="per_page", requirements="\d+", default="20", description="Number of elements displayed per page")
     *
     * @ApiDoc(
     *   section="Events",
     *   description="Get events by year",
     *   statusCodes={
     *     200="OK"
     *   }
     * )
     */
    public function getEventsByYearAction($year, ParamFetcher $paramFetcher)
    {
        list($start, $limit) = $this->getStartAndLimitFromParams($paramFetcher);

        $manager    = $this->get('m6_web_github_enterprise_archive.file_manager');
        $data       = $manager->getEvents($year, null, null, $start, $limit);
        $eventCount = $manager->getEventCount($year);

        return $this->view($this->getPaginatedData($data, $eventCount, $paramFetcher));
    }

    /**
     * Get events
     *
     * @param ParamFetcher $paramFetcher ParamFetcher
     *
     * @return \FOS\RestBundle\View\View
     *
     * @Route(pattern="/events")
     *
     * @QueryParam(name="page", requirements="\d+", default="1", description="Current page index")
     * @QueryParam(name="per_page", requirements="\d+", default="20", description="Number of elements displayed per page")
     *
     * @ApiDoc(
     *   section="Events",
     *   description="Get all events",
     *   statusCodes={
     *     200="OK"
     *   }
     * )
     */
    public function getEventsAction(ParamFetcher $paramFetcher)
    {
        list($start, $limit) = $this->getStartAndLimitFromParams($paramFetcher);

        $manager    = $this->get('m6_web_github_enterprise_archive.file_manager');
        $data       = $manager->getEvents(null, null, null, $start, $limit);
        $eventCount = $manager->getEventCount();

        return $this->view($this->getPaginatedData($data, $eventCount, $paramFetcher));
    }

    /**
     * Get start and limit from page and perPage
     *
     * @param int $page       Page number
     * @param int $perPage    Items per page
     * @param int $maxPerPage Max items per page
     *
     * @return array(start, limit)
     */
    private function getStartAndLimit($page, $perPage = 10, $maxPerPage = 100)
    {
        $page    = max($page, 1);
        $perPage = max(min($perPage, $maxPerPage), 1);
        $start   = ($page - 1) * $perPage;

        return array($start, $perPage);
    }

    /**
     * Get start and limit from ParamFetcher
     *
     * @param ParamFetcher $paramFetcher ParamFetcher
     *
     * @return array(start, limit)
     */
    private function getStartAndLimitFromParams(ParamFetcher $paramFetcher)
    {
        return $this->getStartAndLimit((int) $paramFetcher->get('page'), (int) $paramFetcher->get('per_page'));
    }

    /**
     * Get events data with pagination data
     * 
     * @param array        $data         Events data
     * @param int          $eventCount   Total events number
     * @param ParamFetcher $paramFetcher ParamFetcher
     * 
     * @return array
     */
    private function getPaginatedData($data, $eventCount, ParamFetcher $paramFetcher)
    {
        $pageCount = ceil($eventCount / $paramFetcher->get('per_page'));

        $paginatedCollection = new PaginatedRepresentation(
            new CollectionRepresentation($data, 'events', 'events'),
            $this->getRequest()->get('_route'), // route
            $this->getRequest()->attributes->get('_route_params'), // route parameters
            $paramFetcher->get('page'), // page
            $paramFetcher->get('per_page'), // limit
            $pageCount, // total pages
            'page', // page route parameter name
            'per_page', // limit route parameter name
            true // generate absolute URIs
        );

        $json = $this->get('hateoas')->serialize($paginatedCollection, 'json');

        return json_decode($json, true);
    }
}
