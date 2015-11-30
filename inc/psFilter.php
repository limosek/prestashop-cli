<?php

class psFilter extends StdClass {
    
    static $enabled=false;
    static $filter=false;

    public function create($fstr) {
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
                $filter["eq"][$farr[1]] = $farr[2];
            } elseif (preg_match("/(.*)>(.*)/", $f)) {
                preg_match("/(.*)>(.*)/", $f, $farr);
                $filter["gt"][$farr[1]] = $farr[2];
            } elseif (preg_match("/(.*)<(.*)/", $f)) {
                preg_match("/(.*)<(.*)/", $f, $farr);
                $filter["lt"][$farr[1]] = $farr[2];
            } elseif (preg_match("/(.*)~(.*)/", $f)) {
                preg_match("/(.*)~(.*)/", $f, $farr);
                $filter["re"][$farr[1]] = $farr[2];
            } elseif (preg_match("/(.*)!(.*)/", $f)) {
                preg_match("/(.*)!(.*)/", $f, $farr);
                $filter["nre"][$farr[1]] = $farr[2];
            } else {
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

        reset(self::$filter);
        $fout = false;
        foreach (self::$filter as $type => $f) {
            foreach ($f as $attr => $val) {
                if (isset($obj->$attr->language)) {
                    $data=$obj->$attr->language[psCli::$lang];
                    $isset=array_key_exists(psCli::$lang,$obj->$attr->language);
                } else {
                    if (isset($obj->$attr)) {
                        $data=$obj->$attr;
                        $isset=isset($obj->$attr);
                    } else {
                        psOut::error("Filter property $attr unknown! Available properties: ".join(",",psGet::getProperties($obj)));
                    }
                }
                if ($isset && !is_object($data)) {
                    switch ($type) {
                        case "eq":
                            if (!$data == $val) {
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
