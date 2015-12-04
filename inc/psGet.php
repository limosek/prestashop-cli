<?php

class psGet extends psCli {

    public function filterProps($obj) {
        $name = $obj->getName();
        $dom = dom_import_simplexml($obj);

        $obj=parent::filterProps($obj);
        if ($obj->xpath("associations/product_option_values/product_option_value")) {            
            $po=(string) $obj->xpath("associations/product_option_values/product_option_value")[0]->id;
            $obj->addChild("id_product_option",$po);
        }
        return($obj);
    }
    
    public function getValues($obj) {
	$arr=json_decode(json_encode(self::filterProps($obj)));
        $row = Array();
        $properties = array_flip(self::getProperties($arr));
        foreach ($properties as $p => $v) {
            if (!isset($arr->$p)) {
                psOut::msg("List of availbalbe properties: ".join(",",self::getProperties($arr))."\n");
                psOut::error("Property $p unknown!");
            }
            if (!is_object($arr->$p)) {
                $row[$p] = $arr->$p;
            } elseif (isset($arr->$p->language)) {
                $row[$p] = (string) self::getLanguageObj($obj->$p,$p);
            } else {
                $row[$p] = null;
            }
        }
        return($row);
    }

    public function getProperties($obj) {
        return(array_keys(get_object_vars($obj)));
    }
    
    public function getLanguageObj($obj,$path) {
        if (isset($obj->language)) {
            foreach ($obj->language as $o) {
                if ((int) $o["id"] == self::$lang) {
                    return($o);
                }
            }
            return(false);
        } else {
            return(false);
        }
    }

}
