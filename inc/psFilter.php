<?php

class psFilter extends StdClass {
    
    static $enabled=false;
    static $filter=false;
    static $objects;
    static $filterop="AND";
    const F_NA="@N/A@";

    public function addapifilter($prop, $value) {
        if (array_key_exists(self::$objects, psCli::$propfeatures) && array_key_exists($prop, psCli::$propfeatures[self::$objects]) && (psCli::$propfeatures[self::$objects][$prop] & psCli::P_VIRTUAL)) {
            return;
        } else {            
            if (!array_key_exists($prop,psCli::$apifilter)) {
              if (self::$filterop == "AND") {
		psCli::$apifilter[$prop] = $value;
	      }
	    } else {
	      psCli::$apifilter[$prop] = self::F_NA;
	    }
        }
    }

    public function addapifield($prop) {
        if (array_key_exists(self::$objects, psCli::$propfeatures) && array_key_exists($prop, psCli::$propfeatures[self::$objects]) && (psCli::$propfeatures[self::$objects][$prop] & psCli::P_VIRTUAL)) {
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
	    if (preg_match("/(.*)%<(.*)/", $f)) {
                preg_match("/(.*)%<(.*)/", $f, $farr);
                self::addapifield($farr[1]);
                $filter["dlt"][$farr[1]] = $farr[2];
            } elseif (preg_match("/(.*)%>(.*)/", $f)) {
                preg_match("/(.*)%>(.*)/", $f, $farr);
                self::addapifield($farr[1]);
                $filter["dgt"][$farr[1]] = $farr[2];
            } elseif (preg_match("/(.*)>(.*)/", $f)) {
                preg_match("/(.*)>(.*)/", $f, $farr);
                self::addapifield($farr[1]);
                $filter["gt"][$farr[1]] = $farr[2];
            } elseif (preg_match("/(.*)<(.*)/", $f)) {
                preg_match("/(.*)<(.*)/", $f, $farr);
                self::addapifield($farr[1]);
                $filter["lt"][$farr[1]] = $farr[2];
            } elseif (preg_match("/(.*)!~(.*)/", $f)) {
                preg_match("/(.*)!~(.*)/", $f, $farr);
                self::addapifield($farr[1]);
                $filter["nre"][$farr[1]] = $farr[2];
            } elseif (preg_match("/(.*)!=(.*)/", $f)) {
                preg_match("/(.*)!=(.*)/", $f, $farr);
                self::addapifield($farr[1]);
                $filter["neq"][$farr[1]] = $farr[2];
	    } elseif (preg_match("/(.*)~(.*)/", $f)) {
                preg_match("/(.*)~(.*)/", $f, $farr);
                self::addapifield($farr[1]);
                $filter["re"][$farr[1]] = $farr[2];
            } elseif (preg_match("/(.*)=(.*)/", $f)) {
                preg_match("/(.*)=(.*)/", $f, $farr);
                self::addapifield($farr[1]);
                self::addapifilter($farr[1],$farr[2]);
                $filter["eq"][$farr[1]] = $farr[2];
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

        $fand = true;
        $for = false;
        $name=$obj->getName();
        foreach (self::$filter as $type => $f) {
            foreach ($f as $attr => $fval) {
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
                            if ($data == $fval) {
                                $fand &= true;
                                $for |= true;
                                $fwhy = "$attr=$fval";
                            } else {
				$fand &= false;
                                $for |= false;
                                $fwhy = "$attr=$fval";
                            }
                            break;
                        case "gt":
                            if ($data > $fval) {
                                $fand &= true;
                                $for |= true;
                                $fwhy = "$attr>$fval";
                            } else {
				$fand &= false;
                                $for |= false;
                                $fwhy = "$attr>$fval";
                            }
                            break;
                        case "lt":
                            if ($data < $fval) {
                                $fand &= true;
                                $for |= true;
                                $fwhy = "$attr<$fval";
                            } else {
				$fand &= false;
                                $for |= false;
                                $fwhy = "$attr<$fval";
                            }
                            break;
                        case "dgt":
                            if (New DateTime($data) > New DateTime($fval)) {
                                $fand &= true;
                                $for |= true;
                                $fwhy = "$attr%>$fval";
                            } else {
				$fand &= false;
                                $for |= false;
                                $fwhy = "$attr%>$fval";
                            }
                            break;
                        case "dlt":
                            if (New DateTime($data) < New DateTime($fval)) {
                                $fand &= true;
                                $for |= true;
                                $fwhy = "$attr%<$fval";
                            } else {
				$fand &= false;
                                $for |= false;
                                $fwhy = "$attr%<$fval";
                            }
                            break;
                        case "re":
                            if (preg_match("/$fval/", $data)) {
                                $fand &= true;
                                $for |= true;
                                $fwhy = "$attr~$fval";
                            } else {
				$fand &= false;
                                $for |= false;
                                $fwhy = "$attr~$fval";
                            }
                            break;
                        case "nre":
                            if (!preg_match("/$fval/", $data)) {
                                $fand &= true;
                                $for |= true;
                                $fwhy = "$attr!$fval";
                            } else {
				$fand &= false;
                                $for |= false;
                                $fwhy = "$attr!$fval";
                            }
			case "neq":
                            if (!($data == $fval)) {
                                $fand &= true;
                                $for |= true;
                                $fwhy = "$attr!=$fval";
                            } else {
				$fand &= false;
                                $for |= false;
                                $fwhy = "$attr!=$fval";
                            }
                            break;
                    }
                }
            }
        }
        if (self::$filterop == "AND") {
	  $fout=!$fand;
        } else {
	  $fout=!$for;
        }
        if (psCli::$debug && $fout) {
            psOut::msg("Filtering object due to $fwhy\n");
        }
        return($fout);
    }

}
