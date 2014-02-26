<?php

namespace WWWP;

class Dependency
{
    public $name;
    public $zip;
    public $core = false;

    private $installDir = '../modules/';

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
            $zipPath = $this->installDir . $this->name . '.zip';
            file_put_contents($zipPath, fopen($this->zip, 'r'));
            $zip = new \ZipArchive;
            if ($zip->open($zipPath)) {
                $foldername = $zip->getNameIndex(0);
                $zip->extractTo($this->installDir);
                $zip->close();
                rename($this->installDir . $foldername, $this->installDir . $this->name);
                unlink($zipPath);
            }
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
