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
     * @param array $item
     *
     * @return void
     */
    public function saveItem($item);

    /**
     * Get all events of a given date (day or month)
     *
     * @param int $year
     * @param int $month
     * @param int $day
     *
     * @return array
     */
    public function getByDate($year, $month, $day = null);
}
