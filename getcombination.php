#!/usr/bin/env php
<?php

require(__DIR__ . "/inc/common.php");
require(__DIR__ . "/inc/PSWebServiceLibrary.php");
$options=PsCli::init(
		Array("property:")
	);
$combinationids=PsCli::args($argv);
if (count($combinationids)==0) {
	PsCli::error("You have to enter combination ids as argument!");
}
$property=Array();
if (array_key_exists("property",$options)) {
	if (!is_array($options["property"])) {
		$options["property"]=Array($options["property"]);
	}
	foreach ($options["property"] as $p) {
		$property[$p]=1;
	}
}

try
{
	$opt = array('resource' => 'combinations');
	$webService = new PrestaShopWebservice(PsCli::$shop_url, PsCli::$shop_key, false);
	$row=0;
	$rows=Array();
	foreach ($combinationids as $id) {
		$rows[$row]=Array();
		if (PsCli::$progress) {
			PsCli::msg("#");
		}
		$opt['id']=$id;
		$xml = $webService->get($opt);
		$combination = json_decode(json_encode($xml->children()->combination));
		$rows[$row]["id"]=(string) $id;
		foreach (get_object_vars($combination) as $p=>$v) {
			if (count($property)>0) {
				if (array_key_exists($p,$property)) {
					$rows[$row][$p]=$v;
				}
			} else {
				if (!is_object($v)) {
					$rows[$row][$p]=$v;
				}
			}
		}
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


