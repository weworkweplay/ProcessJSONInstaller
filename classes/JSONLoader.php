<?php

namespace JSONInstaller;
/**
 * Singleton
 */
class JSONLoader {

    private static $instance;

    public $dir;
    private $modules;

    private function __construct() {
        $this->dir = dirname(__FILE__) . '/../modules';
    }

    public static function create() {
        if (self::$instance) {
            return self::$instance;
        }
        return self::$instance = new self();
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
        if (!$dir) {
            $dir = $instance->dir;
        }
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
        if(isset($modules[$slug])) {
            return $modules[$slug];
        } else {
            return false;
        }
    }
    
    public static function filenameToSlug($filename) {
        return substr($filename, 0, -5);
    }

}
