<?php

namespace M6Web\GithubEnterpriseArchiveBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class EventController
 *
 * @author Adrien Samson <asamson.externe@m6.fr>
 */
class EventController extends Controller
{
    /**
     * Get events by day
     * 
     * @param int $year  Year
     * @param int $month Month
     * @param int $day   Day
     *
     * @return JsonResponse
     */
    public function getEventsByDayAction($year, $month, $day)
    {
        $data = $this->get('m6_web_github_enterprise_archive.file_manager')->getByDate($year, $month, $day);

        return new JsonResponse($data);
    }

    /**
     * Get events by month
     * 
     * @param int $year  Year
     * @param int $month Month
     *
     * @return JsonResponse
     */
    public function getEventsByMonthAction($year, $month)
    {
        $data = $this->get('m6_web_github_enterprise_archive.file_manager')->getByDate($year, $month);

        return new JsonResponse($data);
    }
}
