<?php

class psGet extends psCli {

    public function getValues($obj, $properties = false) {
        $row = Array();
        if (!$properties) {
            if (!self::$properties) {
                $properties = self::listProperties($obj);
            } else {
                $properties = self::$properties;
            }
        }
        if (array_key_exists("*", $properties)) {
            $properties = self::listProperties($obj);
        }
        foreach ($properties as $p => $v) {
            if (!isset($obj->$p)) {
                psOut::msg("List of availbalbe properties: ".join(",",array_flip(self::listProperties($obj)))."\n");
                psOut::error("Property $p unknown!");
            }
            if (!is_object($obj->$p)) {
                $row[$p] = $obj->$p;
            } elseif (isset($obj->$p->language)) {
                $row[$p] = $obj->$p->language[psCli::$lang];
            } else {
                $row[$p] = null;
            }
        }

        return($row);
    }

    public function listProperties($obj) {
        return(array_flip(array_keys(get_object_vars($obj))));
    }

}
