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
     * @param string $rootDir
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
    public function getByDate($year, $month, $day)
    {
        $data = [];
        $dir  = sprintf('%s/%d-%02d-%02d', $this->rootDir, $year, $month, $day);

        if (!$this->fs->exists($dir)) {
            return $data;
        }

        $files = Finder::create()
            ->in($dir)
            ->notName('index.txt')
            ->sortByName();

        foreach ($files as $file) {
            $data[] = json_decode(file_get_contents($file->getRealPath()));
        }

        return array_reverse($data);
    }
}

