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
     *
     * @return array
     */
    public function getByDate($year, $month, $day = null);
}
