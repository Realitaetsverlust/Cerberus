<?php

namespace Cerberus;

class Cerberus {
    private $rootDir;
    private $filterDir;
    private $filter;
    private $filterPath;
    private $command;
    private $isVerbose;

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

    public function executeFilter() {
        $affectedByFilter = false;
        $linesToModify = [];
        $filterName = $this->getFilter();
        $commandName = $this->getCommand();
        //FIX THIS
        $filter = new debug();
        $files = $this->getGitStatusHistory();

        if(count($files) === 0) {
            echo "No changed files found. Aborting Cerberus";
        }

        //--all Parameter that checks all the files in case git is not available or you simply want everything changed
        foreach($files as $file) {
            echo 'Found '.count($files) . ' files with \'git status\'';
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

                if(preg_match("/(?i)(#{$this->getFilter()}::START).*$/", trim($line), $match)) {
                    $affectedByFilter = true;
                }
            }
        }

        file_put_contents($file, implode($fileContent));
        echo "Replaced {$changedLinesCount} lines in file {$file}.";
    }

    public function validate() {
        return $this->_validateFilter() && $this->_validateCommand();
    }

    private function _validateFilter() {
        if(!file_exists($this->getFilterPath())) {
            echo "The filter {$this->getFilter()} is not defined in {$this->getFilterDir()}";
            return false;
        }
        return true;
    }

    private function _validateCommand() {
        require $this->getFilterPath();

        $class = '\\'.__NAMESPACE__.'\\'.$this->getFilter();

        $filterObject = new $class;

        if(!method_exists($filterObject, $this->getCommand())) {
            echo "The command {$this->getCommand()} is not defined in {$this->getFilterPath()}";
            return false;
        }
        return true;
    }

    public function getGitStatusHistory() {
        $output = array();
        chdir($this->getRootDir());
        exec("git status", $output);
        $filesToSearch = [];

        foreach($output as $line) {
            if(preg_match('/^(modified)\b.*$/', trim($line), $match)) {
                $filesToSearch[] = $this->getRootDir().trim(str_replace('modified:', '', $match[0]));
            }
        }

        return $filesToSearch;
    }

    /**
     * @return mixed
     */
    public function getFilter()
    {
        return $this->filter;
    }

    /**
     * @param mixed $action
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