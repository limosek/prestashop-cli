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
                psCli::$apifields[$farr[1]]=1;
                psCli::$apifilter[$farr[1]]=$farr[2];
                $filter["eq"][$farr[1]] = $farr[2];
            } elseif (preg_match("/(.*)>(.*)/", $f)) {
                preg_match("/(.*)>(.*)/", $f, $farr);
                psCli::$apifields[$farr[1]]=1;
                $filter["gt"][$farr[1]] = $farr[2];
            } elseif (preg_match("/(.*)<(.*)/", $f)) {
                preg_match("/(.*)<(.*)/", $f, $farr);
                psCli::$apifields[$farr[1]]=1;
                $filter["lt"][$farr[1]] = $farr[2];
            } elseif (preg_match("/(.*)~(.*)/", $f)) {
                preg_match("/(.*)~(.*)/", $f, $farr);
                psCli::$apifields[$farr[1]]=1;
                $filter["re"][$farr[1]] = $farr[2];
            } elseif (preg_match("/(.*)!(.*)/", $f)) {
                preg_match("/(.*)!(.*)/", $f, $farr);
                psCli::$apifields[$farr[1]]=1;
                $filter["nre"][$farr[1]] = $farr[2];
            } else {
                psCli::$apifields[$f]=1;
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
        foreach (self::$filter as $type => $f) {
            foreach ($f as $attr => $val) {
                if (psGet::getLanguageObj($obj->$attr,$attr)) {
                    $isset=psGet::getLanguageObj($obj->$attr,$attr);
                } else {
                    if (isset($obj->$attr)) {
                        $data=(string) $obj->$attr;
                        $isset=isset($obj->$attr);
                    } else {
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
