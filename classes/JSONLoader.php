<?php

namespace JSONInstaller;

class JSONLoader {

    /**
     * Singleton
     */
    private static $instance;

    public $dir;
    private $modules;

    private function __construct() {
        $this->dir = dirname(__FILE__) . '/../modules';
    }

    public static function create() {
        return (self::$instance) ? self::$instance : self::$instance = new self();
    }

    public function getModules() {
        if ($this->modules) {
            return $this->modules;
        }

        $this->modules = array();
        $tmp = scandir($this->dir);

        foreach ($tmp as $file) {
            if (strpos($file, '.json') > 0) {
                self::loadModule($file, $this->dir);
            }
        }

        return $this->modules;
    }

    public static function loadModule($file, $dir = null) {
        $instance = self::create();
        $dir = is_null($dir) ? $instance->dir : $dir;
        $fullPath = $dir . DIRECTORY_SEPARATOR . $file;

        if (!file_exists($fullPath)) {
            return false;
        }

        $slug = self::filenameToSlug($file);

        // return if module has already been loaded
        if(isset($instance->modules[$slug])) {
            return $instance->modules[$slug];
        }

        $json = json_decode(file_get_contents($fullPath));

        // this line is necessary to add the module to the list of all modules
        // prevention of curcular reference, see JSONLoader::isModuleLoaded()
        $instance->modules[$slug] = $slug;

        $module = Module::createFromJSON($json);
        $instance->modules[$slug] = $module;
        $module->slug = $slug;

        return $module;
    }

    public function getModule($slug) {
        $modules = $this->getModules();
        return !empty($modules[$slug]) ? $modules[$slug] : false;
    }

    public static function filenameToSlug($filename) {
        if (strpos($filename, '.json') === -1) {
            throw new Exception('This is not a valid module');
            die;
        }

        return wire('sanitizer')->pageName(str_replace('.json', '', $filename));
    }
}
