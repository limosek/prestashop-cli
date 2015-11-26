<?php

class psList extends psCli {
    static $longopts = Array("filter=");
    static $filter;
    
    public function init($argv, $longopts = false, $shortopts = false) {
        $options=parent::init($argv,self::$longopts);
        self::$filter = self::getarg("filter", $options);
        return($options);
    }
}
