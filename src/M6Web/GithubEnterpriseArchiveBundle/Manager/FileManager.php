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
     * {@inheritdoc}
     */
    public function getLastSavedDate()
    {
        $dir = '2000-01-01';
        if ($this->fs->exists($this->rootDir.'/index.txt')) {
            $dir = file_get_contents($this->rootDir.'/index.txt');
        }
        $lastDate = $dir.'T00:00:00Z';
        if ($this->fs->exists($this->rootDir.'/'.$dir.'/index.txt')) {
            $index = file_get_contents($this->rootDir.'/'.$dir.'/index.txt');
            if ($this->fs->exists(sprintf('%s/%s/%04d', $this->rootDir, $dir, $index))) {
                $data     = file_get_contents(sprintf('%s/%s/%04d', $this->rootDir, $dir, $index));
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

        $this->fs->dumpFile(sprintf('%s/%s/%04d', $this->rootDir, $dir, $index), json_encode($item));
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

        if ($this->fs->exists($this->rootDir.'/index.txt')) {
            $lastDir = file_get_contents($this->rootDir.'/index.txt');
        }
        if ($lastDir < $dir) {
            $this->fs->dumpFile($this->rootDir.'/index.txt', $dir);
        }

        if (!$this->fs->exists($this->rootDir.'/'.$dir)) {
            $this->fs->mkdir($this->rootDir.'/'.$dir);
        }

        if ($this->fs->exists($this->rootDir.'/'.$dir.'/index.txt')) {
            $index = (int) file_get_contents($this->rootDir.'/'.$dir.'/index.txt');
        }

        $index++;

        $this->fs->dumpFile($this->rootDir.'/'.$dir.'/index.txt', $index);

        return $index;
    }

    /**
     * {@inheritdoc}
     */
    public function getEvents($year = null, $month = null, $day = null, $start = 0, $limit = 20)
    {
        $data = [];

        $globPattern = sprintf("%s/", $this->rootDir);

        if ($year) {
            $globPattern .= sprintf("%d", $year);
            if ($month) {
                $globPattern .= sprintf("-%02d", $month);
                if ($day) {
                    $globPattern .= sprintf("-%02d", $day);
                }
            }
        }

        $globPattern .= '*';

        $dirs       = glob($globPattern, GLOB_ONLYDIR);
        $dirsNumber = count($dirs);

        $i = $counter = 0;

        // Ends if all directories are parsed or if we have enough data
        while ((count($data) < $limit) && ($i < $dirsNumber)) {
            $dir = $dirs[$dirsNumber - $i++ - 1];

            // If it is not a valid directory, skip the directory
            if (!$this->fs->exists($dir) || !$this->fs->exists($dir . '/index.txt')) {
                continue;
            }

            $index = (int) file_get_contents($dir . '/index.txt');

            // If we are not at start index, next directory
            if (($counter + $index) < $start) {
                $counter += $index;
                continue;
            }

            $files = Finder::create()
                ->in($dir)
                ->notName('index.txt')
                ->sortByName();

            $files       = array_values(iterator_to_array($files->getIterator()));
            $j           = 0;
            $filesNumber = count($files);

            // Ends if all files are parsed or if we have enough data
            while ((count($data) < $limit) && ($j < $filesNumber)) {
                $file = $files[$filesNumber - $j - 1];

                // If we are not at start index, next file
                if (($counter + $j++) < $start) {
                    continue;
                }

                $data[] = json_decode(file_get_contents($file->getRealPath()), true);
            }

            $counter += $index;
        }

        return $data;
    }
}

