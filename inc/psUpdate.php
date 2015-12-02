<?php

class psUpdate extends stdClass {
    
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
            if (preg_match("/(\w*)\+=(.*)/", $f)) {
                preg_match("/(\w*)\+=(.*)/", $f, $farr);
                $update["plus"][$farr[1]] = $farr[2];
            } elseif (preg_match("/(\w*)\*=(.*)/", $f)) {
                preg_match("/(\w*)\*=(.*)/", $f, $farr);
                $update["multiply"][$farr[1]] = $farr[2];
            } elseif (preg_match("/(\w*)=(.*)/", $f)) {
                preg_match("/(\w*)=(.*)/", $f, $farr);
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
                if (isset($obj->$prop->language)) {
                    switch ($op) {
                        case "set":
                            self::updateLanguageObj($obj->$prop, $prop, $val);
                            break;
                    }
                } else {
                    switch ($op) {
                        case "set":
                            $obj->$prop = $val;
                            break;
                        case "plus":
                            $obj->$prop += $val;
                            break;
                        case "multiply":
                            $obj->$prop *= $val;
                            break;
                    }
                }
            }
        }
        return($obj);
    }

    public function updateLanguageObj($obj,$path,$value) {
        if (isset($obj->language)) {
            $data=$obj->xpath(sprintf("//$path/language[@id=%d]",psCli::$lang));
            if ($data) {
                $data[0][0]=$value;
                return($obj);
            } else {
                psOut::error("Error updating language string!");
            }
        } else {
            return(false);
        }
    }

}
