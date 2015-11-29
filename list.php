#!/usr/bin/env php
<?php
require(__DIR__ . "/inc/common.php");

try {
    psList::init($argv);
    $args = PsList::args($argv);
    if (count($args) < 1) {
        PsOut::error("Error! Enter objects you want to list.\n" . basename(__FILE__)
                . " {".join("|",array_keys(psCli::$resources))."} "
                . "[filter]... [id]...\n"
                . "Filter(s) can be either id of object or\n"
                . "property=value to match exactly\n"
                . "property>value to match objects with property bigger than value\n"
                . "property<value to match objects with property lower than value\n"
                . "property~value to match objects with property matched by regexp\n"
                . "property to match any value in property"
                );
    }
    $objects = array_shift($args);
    psList::readcfg(Array("global","list",$objects));
    $object = psCli::subobject($objects);
    $properties = psList::$properties;
    $ids=Array();
    $filter=Array();
    foreach ($args as $arg) {
        if (is_numeric($arg)) {
            $ids[$arg]=$arg;
        } else {
                $filter[] = $arg;
        }
    }
    if (sizeof($filter)>0) {
        psFilter::create($filter);
    }
    if (!psFilter::$enabled && count(psList::$properties)>1) {
        psOut::error("You cannot get any properties without filter.");
    }
    $webService = new PrestaShopWebservice(psList::$shop_url, psList::$shop_key, false);
    $opt = array('resource' => $objects);
    psOut::progress("Getting data for $objects($object)...");
    $xml = $webService->get($opt);
    $data = $xml->children();
    $row = 0;
    $rows = Array();
    $cnt = sizeof($data->$objects->$object);
    psOut::begin($objects, $object);
    foreach ($data->$objects->$object as $r) {
        $rowdata = Array();
        $id = (string) $r["id"];
        if (sizeof($ids)>0 && !array_key_exists($id,$ids)) {
            continue;
        }
        if (psFilter::$enabled) {
            psOut::progress(false, $row, $cnt);
            $opt['id'] = $id;
            $xml = $webService->get($opt);
            $rowobject = json_decode(json_encode($xml->children()->$object));
            if (psFilter::$enabled && psFilter::isFiltered($rowobject)) {
                if (psCli::$debug)
                    psOut::msg("Filtering $object $id due to filter.\n");
                continue;
            }
            $rowdata = psGet::getValues($rowobject, psList::$properties);
            psOut::write(Array(
                0 => $rowdata
            ));
        } else {
            psOut::write(Array(
                0 => Array("id" => (string) $id)
            ));
        }
        $row++;
    }
    psOut::end($objects, $object);
} catch (PrestaShopWebserviceException $e) {
    // Here we are dealing with errors
    $trace = $e->getTrace();
    PsOut::error('Other error<br />' . $e->getMessage());
}


