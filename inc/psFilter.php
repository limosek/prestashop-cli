<?php

class psFilter extends StdClass {
    
    static $enabled=false;
    static $filter=false;
    static $objects;

    public function addapifilter($prop, $value) {
        if (array_key_exists(self::$objects, psCli::$propfeatures) && array_key_exists($prop, psCli::$propfeatures[self::$objects]) && (psCli::$propfeatures[self::$objects][$prop] && psCli::P_VIRTUAL)) {
            return;
        } else {
            psCli::$apifilter[$prop] = $value;
        }
    }

    public function addapifield($prop) {
        if (array_key_exists(self::$objects, psCli::$propfeatures) && array_key_exists($prop, psCli::$propfeatures[self::$objects]) && (psCli::$propfeatures[self::$objects][$prop] && psCli::P_VIRTUAL)) {
            return;
        } else {
            psCli::$apifields[$prop] = 1;
        }
    }
    
    public function create($fstr,$objects) {
        self::$objects=$objects;
        $filter = Array();
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
                self::addapifield($farr[1]);
                self::addapifilter($farr[1],$farr[2]);
                $filter["eq"][$farr[1]] = $farr[2];
            } elseif (preg_match("/(.*)>(.*)/", $f)) {
                preg_match("/(.*)>(.*)/", $f, $farr);
                self::addapifield($farr[1]);
                $filter["gt"][$farr[1]] = $farr[2];
            } elseif (preg_match("/(.*)<(.*)/", $f)) {
                preg_match("/(.*)<(.*)/", $f, $farr);
                self::addapifield($farr[1]);
                $filter["lt"][$farr[1]] = $farr[2];
            } elseif (preg_match("/(.*)~(.*)/", $f)) {
                preg_match("/(.*)~(.*)/", $f, $farr);
                self::addapifield($farr[1]);
                $filter["re"][$farr[1]] = $farr[2];
            } elseif (preg_match("/(.*)!(.*)/", $f)) {
                preg_match("/(.*)!(.*)/", $f, $farr);
                self::addapifield($farr[1]);
                $filter["nre"][$farr[1]] = $farr[2];
            } else {
                self::addapifield($f);
                psCli::$properties[$f]=1;
                $filter["re"][$f] = "(.*)";
            }
        }
        self::$enabled=true;
        self::$filter=$filter;
        return($filter);
    }

    public function isFiltered($obj) {
        if (!is_array(self::$filter) || count(self::$filter) == 0)
            return(false);

        $fout = false;
        $name=$obj->getName();
        foreach (self::$filter as $type => $f) {
            foreach ($f as $attr => $val) {
                if (psGet::getLanguageObj($obj->$attr,$attr)) {
                    $isset=psGet::getLanguageObj($obj->$attr,$attr);
                    $data=(string) $isset[0];
                } else {
                    if (isset($obj->$attr)) {
                        $data=(string) $obj->$attr;
                        $isset=isset($obj->$attr);
                    } else {
                        if (array_key_exists($name,psCli::$propfeatures)
                                && array_key_exists($attr,psCli::$propfeatures[$name])
                                && psCli::$propfeatures[$name][$attr] && psCli::P_VIRTUAL) {
                            continue;
                        }
                        psOut::error("Filter property $attr unknown! Available properties: ".join(",",psGet::getProperties($obj)));
                    }
                }
                if ($isset) {
                    switch ($type) {
                        case "eq":
                            if (!($data == $val)) {
                                $fout = true;
                                $fwhy = "$attr=$val";
                            }
                            break;
                        case "gt":
                            if (!($data > $val)) {
                                $fout = true;
                                $fwhy = "$attr>$val";
                            }
                            break;
                        case "lt":
                            if (!($data < $val)) {
                                $fout = true;
                                $fwhy = "$attr<$val";
                            }
                            break;
                        case "re":
                            if (!preg_match("/$val/", $data)) {
                                $fout = true;
                                $fwhy = "$attr~$val";
                            }
                            break;
                        case "nre":
                            if (preg_match("/$val/", $data)) {
                                $fout = true;
                                $fwhy = "$attr~$val";
                            }
                            break;
                    }
                }
            }
        }
        if (psCli::$debug && $fout) {
            psOut::msg("Filtering object due to $fwhy\n");
        }
        return($fout);
    }

}
