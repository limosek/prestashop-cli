#!/usr/bin/env php
<?php

require(__DIR__ . "/inc/common.php");
require(__DIR__ . "/inc/PSWebServiceLibrary.php");
$options=PsCli::init(
		Array("filter:")	
	);
$filter=Array();
if (array_key_exists("filter",$options)) {
	if (!is_array($options["filter"])) {
		$options["filter"]=Array($options["filter"]);
	}
	foreach ($options["filter"] as $f) {
		if (preg_match("/(.*)=(.*)/",$f)) {
			preg_match("/(.*)=(.*)/",$f,$farr);
			$filter[$farr[1]]=$farr[2];
		}
	}
}

try
{
	$webService = new PrestaShopWebservice(PsCli::$shop_url, PsCli::$shop_key, false);
	$opt = array('resource' => 'products');
	$xml = $webService->get($opt);
	$products = $xml->children();
	$row=0;
	$rows=Array();
	foreach ($products->products->product as $r) {
		$rows[$row]=Array();
		if (PsCli::$progress) {
			PsCli::msg("#");
		}
		$id=(string) $r["id"];
		if (count($filter)>0) {
			$opt['id']=$id;
			$xml = $webService->get($opt);
			$product = json_decode(json_encode($xml->children()->product));
			$fout=false;
			reset($filter);
			foreach ($filter as $attr=>$val) {		
				if (isset($product->$attr) && !is_object($product->$attr)) {
					if ($product->$attr!=$val) {
						$fout=true;
						$fwhy="$attr=$val";
					}
				}
			}
			if ($fout) {
				if (psCli::$debug) psCli::msg("Filtering product $id due to filter $fwhy.\n");
				continue;
			}
		}
		$rows[$row]["id"]=(string) $id;
		$row++;
	}
	psOut::write($rows);

}
catch (PrestaShopWebserviceException $e)
{
	// Here we are dealing with errors
	$trace = $e->getTrace();
	if ($trace[0]['args'][0] == 404) echo PsCli::error('Bad Shop url?',PsCli::E_URL);
	else if ($trace[0]['args'][0] == 401) PsCli::error('Bad auth key',PsCli::E_KEY);
	else PsCli::error('Other error<br />'.$e->getMessage());
}


