#!/usr/bin/env php
<?php
require(__DIR__ . "/inc/common.php");

try {
    psGet::init($argv);
    $productids = PsGet::args($argv);
    $opt = array('resource' => 'products');
    $webService = new PrestaShopWebservice(PsCli::$shop_url, PsCli::$shop_key, false);
    $row = 0;
    $rows = Array();
    foreach ($productids as $id) {
        $rows[$row] = Array();
        if (PsOut::$progress) {
            PsOut::msg("#");
        }
        $opt['id'] = $id;
        $xml = $webService->get($opt);
        $product = json_decode(json_encode($xml->children()->product));
        if (psGet::isarg("list-properties")) {
            $rows[0]=array_flip(psGet::listProperties($product));
            psOut::write($rows);
            exit;
        }
        $rows[$row]=psGet::getValues($product);
        $rows[$row]["id"] = (string) $id;
        $row++;
    }
    psOut::write($rows);
} catch (PrestaShopWebserviceException $e) {
    // Here we are dealing with errors
    $trace = $e->getTrace();
    PsCli::error('Other error<br />' . $e->getMessage());
}


