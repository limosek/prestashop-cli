<?php

class psCli extends StdClass {

	const SHORTOPTS="";
	const LONGOPTS=Array(
			"help",
			"config-file:",
			"shop-url:",
			"shop-key:",
			"language:",
			"long",
			"debug",
			"progress",
			"output-format:"
		);
	const E_URL=2;
	const E_KEY=3;
	const E_MISSOPT=4;

	static $cfgfile;
	static $shop_url;
	static $shop_key;
	static $long=false;
	static $debug=false;
	static $progress=false;
	static $log=false;
	static $oformat="cli";
	static $args=false;

	public function  init($longopts=false,$shortopts=false) {
		self::$log=fopen('php://stderr', 'w+');
		if (is_array($shortopts)) {
			$shortopts=array_merge(self::SHORTOPTS,$shortopts);
		} else {
			$shortopts=self::SHORTOPTS;
		}
		if (is_array($longopts)) {
			$longopts=array_merge(self::LONGOPTS,$longopts);
		} else {
			$longopts=self::LONGOPTS;
		}
		$goptions = getopt($shortopts,$longopts);
		if (array_key_exists("config-file",$goptions)) {
			self::$cfgfile=$goptions["config-file"];
		} else {
			self::$cfgfile=getenv("HOME")."/.psclirc";
		}
		if (file_exists(self::$cfgfile)) {
			$foptions=self::readcfg(self::$cfgfile);
		} else {
			$foptions=Array();
		}
		foreach ($foptions as $opt=>$val) {
			if ($val) $options[$opt]=$val;
		}
		foreach ($goptions as $opt=>$val) {
			if (array_key_exists($opt,$goptions)) {
				$options[$opt]=$goptions[$opt];
			}
		}
		if (array_key_exists("shop-url",$options)) {
			self::$shop_url=$options["shop-url"];
		} else {
			self::error("Shop url not set!",self::E_MISSOPT);
		}
		if (array_key_exists("shop-key",$options)) {
			self::$shop_key=$options["shop-key"];
		} else {
			self::error("Shop key not set!",self::E_MISSOPT);
		}
		if (array_key_exists("long",$options)) {
			self::$long=true;
		}
		if (array_key_exists("debug",$options)) {
			self::$debug=true;
		}
		if (array_key_exists("progress",$options)) {
			self::$progress=true;
		}
		if (array_key_exists("output-format",$options)) {
			self::$oformat=$options["output-format"];
		}
		if (self::$debug) {
			self::msg("Config and CLI options:\n".print_r($options,true));
		}
		return($options);
	}

	public function readcfg($file) {
		 return(parse_ini_file($file,false));
	}

	public function error($txt,$code=1) {
		fputs(self::$log,$txt."\n");
		exit($code);
	}

	public function msg($txt) {
		fputs(self::$log,$txt);
	}
	
	public function args($args) {
		$ret=Array();
		foreach ($args as $idx=>$arg) {
			if ($idx==0) continue;
			if ($arg[0]!="-") {
				$ret[]=$arg;
			}
		}
		return($ret);
	}

}

class psOut extends StdClass {

	public function write($data) {
		switch (psCli::$oformat) {
			case "cli":	self::cli($data); break;
			case "csv":	self::csv($data); break;
			default:	PsCli::error("Unknown output format ".psCli::$oformat);
		}
	}

	public function cli($data) {
		$row=0;
		reset($data);
		foreach($data as $line) {
			foreach ($line as $column) {
				echo "$column ";
			}
			echo "\n";
			$row++;
		}
	}

	public function csv($data) {
		$row=0;
		reset($data);
		foreach($data as $line) {
			if ($row==0) {
				foreach ($line as $column=>$value) {
					echo "\"$column\";";
				}
				reset($line);
				echo "\n";
			}
			foreach ($line as $column=>$value) {
				echo "\"$value\";";
			}
			echo "\n";
			$row++;
		}
	}

}

class psFilter extends StdClass {

	public function create($opts,$name) {
		$filter=Array();
		if (array_key_exists($name,$opts)) {
			if (!is_array($opts[$name])) {
				$opts[$name]=Array($opts[$name]);
			}
		}
		foreach ($opts[$name] as $f) {
			if (preg_match("/(.*)=(.*)/",$f)) {
				preg_match("/(.*)=(.*)/",$f,$farr);
				$filter[$farr[1]]=$farr[2];
			}
		}
		print_r($filter);
		return($filter);
	}

	public function isFiltered($filter,$obj) {
		if (count($filter==0)) return(false);

		reset($filter);
		$fout=false;
		foreach ($filter as $attr=>$val) {		
			if (isset($obj->$attr) && !is_object($obj->$attr)) {
				if ($obj->$attr!=$val) {
					$fout=true;
					$fwhy="$attr=$val";
				}
			}
		}
		return($fout);
	}

}

