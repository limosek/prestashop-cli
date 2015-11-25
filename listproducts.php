#!/usr/bin/env php
<?php
require(__DIR__ . "/inc/common.php");

try {
    psList::init($argv);
    $properties = array_flip(PsGet::args($argv));
    $filter=psFilter::create(psList::$filter);
    $webService = new PrestaShopWebservice(psList::$shop_url, psList::$shop_key, false);
    $opt = array('resource' => 'products');
    $xml = $webService->get($opt);
    $products = $xml->children();
    $row = 0;
    $rows = Array();
    $cnt=sizeof($products->products->product);
    foreach ($products->products->product as $r) {
        $rows[$row] = Array();
        psOut::progress($row,$cnt);
        $id = (string) $r["id"];
        if (psFilter::$enabled || $properties) {
            $opt['id'] = $id;
            $xml = $webService->get($opt);
            $product = json_decode(json_encode($xml->children()->product));
            if (psFilter::isFiltered($filter, $product)) {
                if (psCli::$debug)
                    psOut::msg("Filtering product $id due to filter.\n");
                continue;
            }
            $rows[$row]=psGet::getValues($product,$properties);
        }
        $rows[$row] = array_merge( Array("id" => (string) $id), $rows[$row]);
        $row++;
    }
    psOut::write($rows);
} catch (PrestaShopWebserviceException $e) {
    // Here we are dealing with errors
    $trace = $e->getTrace();
    PsCli::error('Other error<br />' . $e->getMessage());
}


