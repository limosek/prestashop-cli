#!/usr/bin/env php
<?php
require(__DIR__ . "/inc/common.php");

try {
    psList::init($argv);
    $filter=psFilter::create(psList::$filter);
    $webService = new PrestaShopWebservice(psList::$shop_url, psList::$shop_key, false);
    $opt = array('resource' => 'orders');
    $xml = $webService->get($opt);
    $orders = $xml->children();
    $row = 0;
    $rows = Array();
    $cnt=sizeof($orders->orders->order);
    foreach ($orders->orders->order as $r) {
        $rows[$row] = Array();
        psOut::progress($row,$cnt);
        $id = (string) $r["id"];
        if (psFilter::$enabled) {
            $opt['id'] = $id;
            $xml = $webService->get($opt);
            $order = json_decode(json_encode($xml->children()->order));
            if (psFilter::isFiltered($filter, $order)) {
                if (psCli::$debug)
                    psOut::msg("Filtering order $id due to filter.\n");
                continue;
            }
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


