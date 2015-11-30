<?php

class psUpdate extends StdClass {
    
    static $update;

    public function create($fstr) {
        $update = Array();
        if ($fstr) {
            if (!is_array($fstr)) {
                $fstr = Array($fstr);
            }
        } else {
            return(false);
        }
        foreach ($fstr as $f) {
            if (preg_match("/(.*)=(.*)/", $f)) {
                preg_match("/(.*)=(.*)/", $f, $farr);
                $update["set"][$farr[1]] = $farr[2];
            } else {
                psOut::error("Bad update syntax!");
            }
        }
        self::$update=$update;
        return($update);
    }

    public function update($obj) {
        foreach (self::$update as $op => $list) {
            foreach ($list as $prop => $val) {
                switch ($op) {
                    case "set":
                        $obj->$prop = $val;
                        break;
                }
            }
        }
        return($obj);
    }

}
