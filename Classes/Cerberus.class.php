<?php

namespace Cerberus;

class Cerberus {
    private $rootDir;
    private $filterDir;
    private $filter;
    private $filterPath;
    private $command;
    private $isVerbose;

    /**
     * Cerberus constructor.
     * @param array $argv Command line arguments
     */
    public function __construct($argv) {
        $this->setFilterDir(__DIR__.'/../Filters/');
        $this->setFilter(explode('::', $argv[1])[0]);
        $this->setCommand(explode('::', $argv[1])[1]);
        $this->setRootDir($argv[2]);
        $this->setFilterPath($this->getFilterDir().$this->getFilter().'.php');

        if(!$this->validate()) {
            exit();
        }

        $this->executeFilter();
    }

    /**
     * Execute function. Doesn't take any params, pulls everyone from this instance which is set in the constructor.
     */
    public function executeFilter() {
        $this->writeStdOut('#######################');
        $this->writeStdOut('#  Starting Cerberus! #');
        $this->writeStdOut('#   Woof woof woof!   #');
        $this->writeStdOut('#######################');
        $this->writeStdOut('');
        $this->writeStdOut('');

        $affectedByFilter = false;
        $filterName = '\\'.__NAMESPACE__.'\\'.$this->getFilter();
        $commandName = $this->getCommand();
        $filter = new $filterName();
        $files = $this->getGitStatusHistory();

        if(count($files) === 0) {
            $this->writeStdOut("No changed files found. Aborting Cerberus");
        }

        $this->writeStdOut('Found '.count($files) . ' files with \'git status\'');

        //@TODO: implement --all Parameter that checks all the files in case git is not available or you simply want everything changed
        foreach($files as $file) {
            $this->writeStdOut("Processing {$file}");
            $fileContent = file($file);
            $changedLinesCount = 0;
            foreach($fileContent as $lineNr => $line) {
                if(preg_match("/(?i)(#{$this->getFilter()}::END).*$/", trim($line), $match)) {
                    $affectedByFilter = false;
                }

                if($affectedByFilter) {
                    $fileContent[$lineNr] = $filter->$commandName($line);
                    $changedLinesCount++;
                }

                //We have to check for the START last otherwise the line itself would be changed too.
                if(preg_match("/(?i)(#{$this->getFilter()}::START).*$/", trim($line), $match)) {
                    $affectedByFilter = true;
                }
            }
            file_put_contents($file, implode($fileContent));
            $this->writeStdOut( "Replaced {$changedLinesCount} lines in file {$file}.");
        }
    }

    /**
     * Executes two filters
     * @return bool
     */
    public function validate() {
        return $this->_validateFilter() && $this->_validateCommand();
    }

    /**
     * Validate the existence of te filter by checking if a file with that name exists
     * @return bool
     */
    private function _validateFilter() {
        if(!file_exists($this->getFilterPath())) {
            $this->writeStdOut( "The filter {$this->getFilter()} is not defined in {$this->getFilterDir()}");
            return false;
        }
        return true;
    }

    /**
     * Instanciates a class, then checks if the command exists as a method in that class
     * @return bool
     */
    private function _validateCommand() {
        require $this->getFilterPath();

        $class = '\\'.__NAMESPACE__.'\\'.$this->getFilter();

        $filterObject = new $class;

        if(!method_exists($filterObject, $this->getCommand())) {
            $this->writeStdOut( "The command {$this->getCommand()} is not defined in {$this->getFilterPath()}");
            return false;
        }
        return true;
    }

    /**
     * fetches the output of 'git status', returning only the paths to classes that are new or modified
     * @return array
     */
    public function getGitStatusHistory() {
        $output = array();
        chdir($this->getRootDir());
        exec("git status", $output);
        $filesToSearch = [];

        foreach($output as $line) {
            if(preg_match('/^(modified)\b.*$/', trim($line), $match)) {
                $filesToSearch[] = $this->getRootDir().trim(str_replace('modified:', '', $match[0]));
            }

            if(preg_match('/^(new file)\b.*$/', trim($line), $match)) {
                $filesToSearch[] = $this->getRootDir().trim(str_replace('new file:', '', $match[0]));
            }
        }

        return $filesToSearch;
    }

    /**
     * write to stdout
     *
     * @param string $sMessage log message
     *
     * @return void
     */
    public function writeStdOut($message) {
        file_put_contents('php://stdout', $message.PHP_EOL);
    }

    /**
     * @return mixed
     */
    public function getFilter()
    {
        return $this->filter;
    }

    /**
     * @param mixed $filter
     */
    public function setFilter($filter)
    {
        $this->filter = $filter;
    }

    /**
     * @return mixed
     */
    public function getCommand()
    {
        return $this->command;
    }

    /**
     * @param mixed $command
     */
    public function setCommand($command)
    {
        $this->command = $command;
    }

    /**
     * @return bool
     */
    public function isVerbose()
    {
        return $this->isVerbose;
    }

    /**
     * @param bool $isVerbose
     */
    public function setIsVerbose($isVerbose)
    {
        $this->isVerbose = $isVerbose;
    }

    /**
     * @param bool $rootDir
     */
    public function setRootDir($rootDir)
    {
        $this->rootDir = $rootDir;
    }

    /**
     * @param bool $filterDir
     */
    public function setFilterDir($filterDir)
    {
        $this->filterDir = $filterDir;
    }

    /**
     * @return mixed
     */
    public function getRootDir()
    {
        return $this->rootDir;
    }

    /**
     * @return mixed
     */
    public function getFilterDir()
    {
        return $this->filterDir;
    }

    /**
     * @return mixed
     */
    public function getFilterPath()
    {
        return $this->filterPath;
    }

    /**
     * @param mixed $filterPath
     */
    public function setFilterPath($filterPath)
    {
        $this->filterPath = $filterPath;
    }


}