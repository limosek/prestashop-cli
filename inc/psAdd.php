<?php

class psAdd extends stdClass {

    static $add;

    public function decode($var) {        
        if (psOut::$base64) {
            $var=base64_decode($var);
        } else {
	    $var=psUpdate::stripslashes($var);
	}
	if (psOut::$htmlescape) {
            $var=htmlspecialchars_decode($var);
        }
	return($var);
    }

    public function create($fstr) {
        $add = Array();
        if ($fstr) {
            if (!is_array($fstr)) {
                $fstr = Array($fstr);
            }
        } else {
            return(false);
        }
        foreach ($fstr as $f) {
	    if (preg_match("/(\w*)=(.*)/", $f)) {
                preg_match("/(\w*)=(.*)/", $f, $farr);
                $add["set"][$farr[1]] = self::decode($farr[2]);
            } else {
                psOut::error("Bad add syntax!");
            }
        }
        self::$add = $add;
        return($add);
    }

    public function add($obj) {
        $name = $obj->getName();
        foreach (self::$add as $op => $list) {
            foreach ($list as $prop => $val) {
                if (isset($obj->$prop->language)) {
                    switch ($op) {
                        case "set":
                            self::addLanguageObj($obj->$prop, $prop, $val);
                            $chg[] = $prop;
                            break;
                        default:
                            psOut::error("Bad operation!");
                            break;
                    }
                } else {
                    switch ($op) {
                        case "set":
                            $obj->$prop = $val;
                            $chg[] = $prop;
                            break;
			default:
			    psOut::error("Bad operation!");
			    break;
                    }
                }
            }
        }
        return(psUpdate::filterProps($obj));
    }
    
    public function addLanguageObj($obj, $path, $value) {
	$i=0;
        foreach ($obj->language as $o) {
            if ((int) $o["id"] == psCli::$lang) {
                 $obj->language[$i]=$value;
                 $changed=true;
            }
            $i++;
         }
         if (!$changed) {
                psOut::error("Bad language object?");
        }
	return($obj);
    }
}
