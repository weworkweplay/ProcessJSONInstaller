<?php

namespace JSONInstaller;

class Dependency {

    /**
     * The name of the module, should be equivalent to the modules class name
     * @var string
     */
    public $name;

    /**
     * The url/path to the zip file of the module
     * @var string
     */
    public $zip;

    /**
     * The file name of the json module
     * @var string
     */
    public $json;

    /**
     * Whether it's a core/preexisting module
     * @var boolean
     */
    public $core = false;

    /**
     * Whether this module was skipped at un-/installation
     * not implemented yet
     * @var boolean
     */
    public $skipped = false;

    /**
     * Whether this module should be installed/downloaaded/unzipped
     * regardless if it's already installed or not
     * not implemented yet
     * @var boolean
     */
    public $force = false;

    /**
     * Holding the module instance, if it's a dependency of type JSON
     * @var JSONInstaller\Module
     */
    public $jsonModuleInstance;

    /**
     * The path to where the PW modules reside
     * TODO: this is weird and unclear
     * @var string
     */
    private $installDir = '../modules/';

    /**
     * The path to where the json module files reside
     * TODO: this is weird and unclear
     * @var string
     */
    private $jsonDir;

    public function __construct() {
        $this->jsonDir = dirname(__FILE__) . '/' . $this->installDir;
    }

    /**
     * @return boolean
     */
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

    /**
     * @return boolean
     */
    public function uninstall() {

        if ($this->json) {
            $module = $this->getJsonModuleInstance();
            if ($module->hasDeletableItems($forceDryRun = true)) {
                $module->uninstall();
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

    /**
     * @return boolean
     */
    public function isInstalled() {
        if ($this->json) {
            $module = $this->getJsonModuleInstance();
            return $module->hasDeletableItems($forceDryRun = true);
        } else {
            return wire('modules')->isInstalled($this->name);
        }
    }

    /**
     * @return JSONInstaller\Module
     */
    protected function getJsonModuleInstance() {
        if ($this->jsonModuleInstance) {
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
