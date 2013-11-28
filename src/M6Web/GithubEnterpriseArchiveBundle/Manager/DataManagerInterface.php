<?php

namespace M6Web\GithubEnterpriseArchiveBundle\Manager;

/**
 * Interface DataManager
 */
interface DataManagerInterface
{
    /**
     * Get last saved Date with YYY-MM-DDTHH:MM:SSZ format
     *
     * @return string
     */
    public function getLastSavedDate();

    /**
     * Save item
     *
     * @param array $item Item to save
     *
     * @return void
     */
    public function saveItem($item);

    /**
     * Get all events of a given date (day or month)
     *
     * @param int $year  Year
     * @param int $month Month
     * @param int $day   Day
     * @param int $start Start index
     * @param int $limit Number of elements to return
     *
     * @return array
     */
    public function getEvents($year = null, $month = null, $day = null, $start = 0, $limit = 10);
}
