<?php

namespace JSONInstaller;

class I18N {
    public static function get() {
        // get arguments of this method
        $args = func_get_args();

        // internationalize the firs arg (actual string)
        // relative to this file (__FILE__)
        // this would make all language strings go into one file,
        // instead of into seperate files for this module, but that's OK
        $args[0] = __($args[0], __FILE__);

        if(count($args) > 1) {
            // if there are more arguments use the sprintf function
            // to replace each instance of %s with each argument
            return call_user_func_array('sprintf', $args);
        } else {
            // just gt the actual internationalized string
            return $args[0];
        }
    }
}


