<?php

namespace JSONInstaller;

class Dependency {
    public $name;
    public $zip;
    public $json;
    public $core = false;
    public $skipped = false; // not implemented yet
    public $force = false; // not implemented yet
    public $jsonModule;

    protected $jsonModuleInstance;

    // TODO: this is weird
    private $installDir = '../modules/';
    private $jsonDir;

    public function __construct() {
        $this->jsonDir = dirname(__FILE__) . '/' . $this->installDir;
    }

    public function install() {
        $modules = wire('modules');

        if ($this->json && file_exists($this->jsonDir . $this->json)) {
            $module = $this->getJsonModuleInstance();
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

    public function uninstall() {

        if ($this->json) {
            $module = $this->getJsonModuleInstance();
            if($module->hasDeletableItems($forceDryRun = true)) {
                $module->uninstall();
                \ChromePhp::log($module->name, "uninstalled");
                return true;
            }
            return false;
        } else {
            $modules = wire('modules');
            if ($modules->isInstalled($this->name)) {
                $module = $modules->get($this->name);
                try {
                    $modules->uninstall($this->name);
                    return true;
                } catch (WireException $e) {
                    return false;
                }
            } else {
                return false;
            }
        }
    }

    public function isInstalled() {
        if ($this->json) {
            $module = $this->getJsonModuleInstance();
            return $module->hasDeletableItems($forceDryRun = true);
        } else {
            return wire('modules')->isInstalled($this->name);
        }
    }

    protected function getJsonModuleInstance() {
        if($this->jsonModuleInstance) {
            return $this->jsonModuleInstance;
        } else {
            $file = $this->jsonDir . $this->json;
            $json = json_decode(file_get_contents($file));
            $slug = substr($this->json, 0, -5);
            $this->jsonModuleInstance = Module::createFromJSON($json);
            $this->jsonModuleInstance->slug = $slug;
            return $this->jsonModuleInstance;
        }

    }
}
