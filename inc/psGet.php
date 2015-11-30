<?php

class psGet extends psCli {

    public function getValues($obj, $properties = false) {
	$arr=json_decode(json_encode($obj));
        $row = Array();
        if (!$properties) {
            if (!self::$properties) {
                $properties = self::getProperties($arr);
            } else {
                $properties = self::$properties;
            }
        }
        if (array_key_exists("*", $properties)) {
            $properties = array_flip(self::getProperties($arr));
        }
        foreach ($properties as $p => $v) {
            if (!isset($arr->$p)) {
                psOut::msg("List of availbalbe properties: ".join(",",self::getProperties($arr))."\n");
                psOut::error("Property $p unknown!");
            }
            if (!is_object($arr->$p)) {
                $row[$p] = $arr->$p;
            } elseif (isset($arr->$p->language)) {
                $row[$p] = $arr->$p->language[psCli::$lang];
            } else {
                $row[$p] = null;
            }
        }
        return($row);
    }

    public function getProperties($obj) {
        return(array_keys(get_object_vars($obj)));
    }

}
