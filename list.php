#!/usr/bin/env php
<?php
require(__DIR__ . "/inc/common.php");

try {
    psList::init($argv);
    $args=PsList::args($argv);
    if (count($args)<1) {
        PsOut::error("Error! Enter objects you want to list.\n".basename(__FILE__).
                " {products|orders|combinations|addresses|...} [property...]\n"
                . "Property '*' means everything."
                . "\n");
    }
    $objects=array_shift($args);
    $object=psCli::subobject($objects);
    $properties = array_flip($args);
    $filter=psFilter::create(psList::$filter);
    $webService = new PrestaShopWebservice(psList::$shop_url, psList::$shop_key, false);
    $opt = array('resource' => $objects);
    psOut::progress("Getting data for $objects($object)...");
    $xml = $webService->get($opt);
    $data = $xml->children();
    $row = 0;
    $rows = Array();
    $cnt=sizeof($data->$objects->$object);
    psOut::begin($objects,$object);
    foreach ($data->$objects->$object as $r) {
        $rowdata=Array();
        psOut::progress(false,$row,$cnt);
        $id = (string) $r["id"];
        if (psFilter::$enabled || $properties) {
            $opt['id'] = $id;
            $xml = $webService->get($opt);
            $rowobject = json_decode(json_encode($xml->children()->$object));
            if (psFilter::isFiltered($filter, $rowdata)) {
                if (psCli::$debug)
                    psOut::msg("Filtering $object $id due to filter.\n");
                continue;
            }
            $rowdata=psGet::getValues($rowobject,$properties);
        }
        psOut::write(Array(
            0 => array_merge(
                     Array("id" => (string) $id), $rowdata)
            ));
        $row++;
    }
    psOut::end($objects,$object);
} catch (PrestaShopWebserviceException $e) {
    // Here we are dealing with errors
    $trace = $e->getTrace();
    PsOut::error('Other error<br />' . $e->getMessage());
}


