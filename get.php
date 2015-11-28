#!/usr/bin/env php
<?php
require(__DIR__ . "/inc/common.php");

try {
    psGet::init($argv);
    $args=PsGet::args($argv);
    if (count($args)<1) {
        PsOut::error("Error! Enter object[s] you want to get.\n".basename(__FILE__).
                " {product|order|combination|address|...} id [id...]\n"
                . "Property '*' means everything."
                . "\n");
    }
    $object=array_shift($args);
    $objects=psCli::upobject($object);
    $objectids = $args;
    if (psCli::getarg("properties")) {
        $properties = array_flip(psCli::getarg("properties"));
    } else {
        $properties=false;
    }
    $webService = new PrestaShopWebservice(psList::$shop_url, psList::$shop_key, false);
    $opt = array('resource' => $objects);
    psOut::progress("Getting data for $objects($object)...");
    $xml = $webService->get($opt);
    $data = $xml->children();
    $row = 0;
    $rows = Array();
    $cnt=sizeof($objectids);
    psOut::begin($object);
    foreach ($objectids as $id) {
        $rowdata=Array();
        psOut::progress(false,$row,$cnt);
        $opt['id'] = $id;
        $xml = $webService->get($opt);
        $rowobject = json_decode(json_encode($xml->children()->$object));
        $rowdata=psGet::getValues($rowobject,$properties);
        psOut::write(Array(
            0 => array_merge(
                     Array("id" => (string) $id), $rowdata)
            ));
        $row++;
    }
    psOut::end($object);
} catch (PrestaShopWebserviceException $e) {
    // Here we are dealing with errors
    $trace = $e->getTrace();
    PsCli::error('Other error<br />' . $e->getMessage());
}


