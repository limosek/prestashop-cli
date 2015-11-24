#!/usr/bin/env php
<?php

require(__DIR__ . "/inc/common.php");
require(__DIR__ . "/inc/PSWebServiceLibrary.php");
$options=PsCli::init(
		Array("filter:")	
	);
$filter=psFilter::create($options,"filter");

try
{
	$webService = new PrestaShopWebservice(PsCli::$shop_url, PsCli::$shop_key, false);
	$opt = array('resource' => 'combinations');
	$xml = $webService->get($opt);
	$combinations = $xml->children();
	$row=0;
	$rows=Array();
	foreach ($combinations->combinations->combination as $r) {
		$rows[$row]=Array();
		if (PsCli::$progress) {
			PsCli::msg("#");
		}
		$id=(string) $r["id"];
		if (count($filter)>0) {
			$opt['id']=$id;
			$xml = $webService->get($opt);
			$product = json_decode(json_encode($xml->children()->combination));
			if (psFilter::isFiltered($filter,$combination)) {
				if (psCli::$debug) psCli::msg("Filtering combination $id due to filter.\n");
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


