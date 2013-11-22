<?php

namespace M6Web\GithubEnterpriseArchiveBundle\Manager;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

/**
 * Class FileManager
 *
 * @author Adrien Samson <asamson.externe@m6.fr>
 */
class FileManager implements DataManagerInterface
{
    protected $rootDir;
    protected $fs;
    protected $indexes;

    /**
     * Constructor
     *
     * @param string $rootDir Root directory
     */
    public function __construct($rootDir)
    {
        $this->rootDir = $rootDir;
        $this->fs      = new Filesystem();
    }

    /**
     * Get root directory
     *
     * @return string
     */
    public function getRootDirectory()
    {
        return $this->rootDir;
    }

    /**
     * Get filesystem
     *
     * @return string
     */
    public function getFilesystem()
    {
        return $this->fs;
    }

    /**
     * {@inheritdoc}
     */
    public function getLastSavedDate()
    {
        $dir = '2000-01-01';
        if ($this->getFilesystem()->exists($this->getRootDirectory().'/index.txt')) {
            $dir = file_get_contents($this->getRootDirectory().'/index.txt');
        }
        $lastDate = $dir.'T00:00:00Z';
        if ($this->getFilesystem()->exists($this->getRootDirectory().'/'.$dir.'/index.txt')) {
            $index = file_get_contents($this->getRootDirectory().'/'.$dir.'/index.txt');
            if ($this->getFilesystem()->exists(sprintf('%s/%s/%04d', $this->getRootDirectory(), $dir, $index))) {
                $data     = file_get_contents(sprintf('%s/%s/%04d', $this->getRootDirectory(), $dir, $index));
                $lastDate = json_decode($data, true)['created_at'];
            }
        }

        return $lastDate;
    }

    /**
     * {@inheritdoc}
     */
    public function saveItem($item)
    {
        $dir = substr($item['created_at'], 0, 10);

        $index = $this->nextIndex($dir);

        $this->getFilesystem()->dumpFile(sprintf('%s/%s/%04d', $this->getRootDirectory(), $dir, $index), json_encode($item));
    }

    /**
     * Get the next index of a directory
     * 
     * @param string $dir Directory
     * 
     * @return int
     */
    protected function nextIndex($dir)
    {
        $index   = 0;
        $lastDir = '2000-01-01';

        if ($this->getFilesystem()->exists($this->getRootDirectory().'/index.txt')) {
            $lastDir = file_get_contents($this->getRootDirectory().'/index.txt');
        }

        if ($lastDir < $dir) {
            $this->getFilesystem()->dumpFile($this->getRootDirectory().'/index.txt', $dir);
        }

        if (!$this->getFilesystem()->exists($this->getRootDirectory().'/'.$dir)) {
            $this->getFilesystem()->mkdir($this->getRootDirectory().'/'.$dir);
        }

        if ($this->getFilesystem()->exists($this->getRootDirectory().'/'.$dir.'/index.txt')) {
            $index = (int) file_get_contents($this->getRootDirectory().'/'.$dir.'/index.txt');
        }

        $index++;

        $this->getFilesystem()->dumpFile($this->getRootDirectory().'/'.$dir.'/index.txt', $index);

        return $index;
    }

    /**
     * {@inheritdoc}
     */
    public function getByDate($year, $month, $day = null)
    {
        $data = [];

        if (!$day) {
            $time = strtotime(sprintf('%d-%02d-01', $year, $month));
            for ($d = 1; $d < date('t', $time); $d++) {
                $dirs[] = sprintf('%s/%d-%02d-%02d', $this->getRootDirectory(), $year, $month, $d);
            }
        } else {
            $dirs[] = sprintf('%s/%d-%02d-%02d', $this->getRootDirectory(), $year, $month, $day);
        }

        foreach ($dirs as $dir) {
            if (!$this->getFilesystem()->exists($dir)) {
                continue;
            }

            $files = Finder::create()
                ->in($dir)
                ->notName('index.txt')
                ->sortByName();

            foreach ($files as $file) {
                $data[] = json_decode(file_get_contents($file->getRealPath()));
            }
        }

        return array_reverse($data);
    }
}

