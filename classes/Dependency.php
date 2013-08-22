<?php

namespace WWWP;

class Dependency
{
    public $name;
    public $github;
    public $core = false;

    private $installDir = '../site/modules/';

    public function __construct()
    {

    }

    public function install()
    {
        $modules = wire('modules');
        $success = false;

        if ($modules->isInstalled($this->name)) {
            return true;
        }

        if (!$core && !file_exists($this->installDir . DIRECTORY_SEPARATOR . $this->name)) {
            $cwd = getcwd();

            chdir($this->installDir);
            exec('git clone ' . $this->github, $output);

            chdir($cwd);
        }

        $modules->resetCache();

        if ($module = $modules->get($this->name)) {
            try {
                if (is_callable(array($module, 'install'))) {
                    $success = $module->install();
                }
            } catch (WireException $e) {
                $success = false;
            }
        }

        return (bool) $success;
    }

}