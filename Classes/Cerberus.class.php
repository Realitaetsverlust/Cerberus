<?php

namespace Cerberus;

class Cerberus {
    private $rootDir;
    private $action;
    private $command;
    private $isVerbose;

    public function __construct($argv) {
        $this->setRootDir(__DIR__.'..');
        $this->setAction(explode('::', $argv[1])[0]);
        $this->setCommand(explode('::', $argv[1])[1]);
        $this->setIsVerbose(isset($argv[2]));

        var_dump($this);
    }

    public function _validate() {
        
    }

    /**
     * @return mixed
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @param mixed $action
     */
    public function setAction($action)
    {
        $this->action = $action;
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
}