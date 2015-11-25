#!/usr/bin/env php
<?php
require(__DIR__ . "/inc/common.php");

try {
    psGet::init($argv);
    $orderids = PsGet::args($argv);
    $opt = array('resource' => 'orders');
    $webService = new PrestaShopWebservice(PsCli::$shop_url, PsCli::$shop_key, false);
    $row = 0;
    $rows = Array();
    foreach ($orderids as $id) {
        $rows[$row] = Array();
        if (PsOut::$progress) {
            PsOut::msg("#");
        }
        $opt['id'] = $id;
        $xml = $webService->get($opt);
        $order = json_decode(json_encode($xml->children()->order));
        if (psGet::isarg("list-properties")) {
            $rows[0]=array_flip(psGet::listProperties($order));
            psOut::write($rows);
            exit;
        }
        $rows[$row]=psGet::getValues($order);
        $rows[$row] = array_merge( Array("id" => (string) $id), $rows[$row]);
        $row++;
    }
    psOut::write($rows);
} catch (PrestaShopWebserviceException $e) {
    // Here we are dealing with errors
    $trace = $e->getTrace();
    PsCli::error('Other error<br />' . $e->getMessage());
}


