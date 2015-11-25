<?php

class psList extends psCli {
    const LONGOPTS = Array("filter=");
    static $filter;
    
    public function init($argv, $longopts = false, $shortopts = false) {
        $options=parent::init($argv,self::LONGOPTS);
        self::$filter = self::getarg("filter", $options);
        return($options);
    }
}
