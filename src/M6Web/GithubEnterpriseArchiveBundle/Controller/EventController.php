<?php

namespace M6Web\GithubEnterpriseArchiveBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations\Route;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Request\ParamFetcher;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

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
     *   description="Get events by day",
     *   statusCodes={
     *     200="OK"
     *   }
     * )
     */
    public function getEventsByDayAction($year, $month, $day, ParamFetcher $paramFetcher)
    {
        list($start, $limit) = $this->getStartAndLimitFromParams($paramFetcher);

        $data = $this->get('m6_web_github_enterprise_archive.file_manager')->getByDate($year, $month, $day, $start, $limit);

        return $this->view($data);
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
     *   description="Get events by month",
     *   statusCodes={
     *     200="OK"
     *   }
     * )
     */
    public function getEventsByMonthAction($year, $month, ParamFetcher $paramFetcher)
    {
        list($start, $limit) = $this->getStartAndLimitFromParams($paramFetcher);

        $data = $this->get('m6_web_github_enterprise_archive.file_manager')->getByDate($year, $month, null, $start, $limit);

        return $this->view($data);
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
}
