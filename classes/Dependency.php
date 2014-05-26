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
     * Whether it's a core/preexisting module
     * @var boolean
     */
    public $core = false;

    /**
     * Whether this module was skipped at un-/installation
     * TODO: not implemented yet
     * @var boolean
     */
    public $skipped = false;

    /**
     * Whether this module should be installed/downloaaded/unzipped
     * regardless if it's already installed or not
     * TODO: not implemented yet
     * @var boolean
     */
    public $force = false;

    /**
     * The path to where the PW modules reside
     * TODO: this is weird and unclear
     * @var string
     */
    private $installDir = '../modules/';

    public function __construct() {}

    /**
     * @return boolean
     */
    public function install() {

        $modules = wire('modules');

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
        return false;
        // disabled PW modules uninstallation for now
        // $modules = wire('modules');
        // if ($modules->isInstalled($this->name)) {
        //     // This is fabulously flaky, because a PW module can be comprised
        //     // of submodules with different names. Also: if the name in the JSON
        //     // file is not the exact of the PW module, this also fails.
        //     $module = $modules->get($this->name);
        //     try {
        //         $modules->uninstall($this->name);
        //         return true;
        //     } catch (WireException $e) {
        //         return false;
        //     }
        // } else {
        //     return false;
        // }
    }

    /**
     * @return boolean
     */
    public function isInstalled() {
        return false;
    }
}
