<?php

namespace JSONInstaller;

class Dependency {
    public $name;
    public $zip;
    public $core = false;
    public $json = false; // not implemented yet
    public $skipped = false; // not implemented yet
    public $force = false; // not implemented yet

    private $installDir = '../modules/';

    public function __construct() {

    }

    public function install() {
        $modules = wire('modules');

        if ($this->json && file_exists($this->jsonDir . $this->json)) {
            $file = $this->jsonDir . $this->json;
            $json = json_decode(file_get_contents($file));
            $slug = substr($this->json, 0, -5);
            $module = Module::createFromJSON($json);
            $module->slug = $slug;
            $this->name = $module->name;
            $module->install();
            return true;
        }

        if ($modules->isInstalled($this->name)) {
            return true;
        }

        if (!$this->core && !file_exists($this->installDir . DIRECTORY_SEPARATOR . $this->name)) {
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
            return true;
        } else {
            return false;
        }
    }
}
