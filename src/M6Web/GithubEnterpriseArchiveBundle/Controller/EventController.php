<?php

namespace M6Web\GithubEnterpriseArchiveBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations\Route;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Request\ParamFetcher;

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
     * @param int $year  Year
     * @param int $month Month
     * @param int $day   Day
     *
     * @return \FOS\RestBundle\View\View
     * 
     * @Route(pattern="/events/{year}-{month}-{day}", requirements={"year"="\d{4}", "month"="\d{2}", "day"="\d{2}"})
     * 
     * @ApiDoc(
     *   description="Get events by day",
     *   statusCodes={
     *     200="OK"
     *   }
     * )
     */
    public function getEventsByDayAction($year, $month, $day)
    {
        $data = $this->get('m6_web_github_enterprise_archive.file_manager')->getByDate($year, $month, $day);

        return $this->view($data);
    }

    /**
     * Get events by month
     * 
     * @param int $year  Year
     * @param int $month Month
     *
     * @return \FOS\RestBundle\View\View
     * 
     * @Route(pattern="/events/{year}-{month}", requirements={"year"="\d{4}", "month"="\d{2}"})
     * 
     * @ApiDoc(
     *   description="Get events by month",
     *   statusCodes={
     *     200="OK"
     *   }
     * )
     */
    public function getEventsByMonthAction($year, $month)
    {
        $data = $this->get('m6_web_github_enterprise_archive.file_manager')->getByDate($year, $month);

        return $this->view($data);
    }
}
