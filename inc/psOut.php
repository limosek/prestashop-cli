<?php

class psOut extends StdClass {
    static $progress = false;
    static $log = false;
    static $oformat = "cli";
    static $base64 = false;
    static $htmlescape = false;
    static $escape = true;
    static $buffered = false;
    static $context = false;
    static $category = "row";
    static $csvsep = ";";
    static $first;
    static $delchars;
    const ESCAPECHARS = "\n\r\":|<>&;()[]*\$#!";
    
    public function write($data) {
        foreach ($data as $k=>$v) {
            if (array_key_exists("*",psCli::$properties)) continue;
            if (!array_key_exists($k,psCli::$properties)) {
                unset($data[$k]);
            }
        }
        switch (self::$oformat) {
            case "cli": self::cli($data);
                break;
            case "cli2": self::cli2($data);
                break;
            case "ml": self::multiline($data);
                break;
            case "csv": self::csv($data);
                break;
            case "env": self::env($data);
                break;
            case "envarr": self::envarr($data);
                break;
	    case "psadd": self::psadd($data);
                break;
            case "psupdate": self::psupdate($data);
                break;
	    case "envid": self::envid($data);
                break;
            case "xml": self::xml($data);
                break;
            case "php": self::php($data);
                break;
            case "export": self::export($data);
                break;
            default: self::error("Unknown output format " . self::$oformat);
        }
        self::$first=false;
    }
    
    public function begin($context=false,$category=false) {
        self::$context=$context;
        self::$category=$category;
        self::$first=true;
        switch (self::$oformat) {
            case "xml": 
                if (self::$context) { echo "<".self::$context.">\n"; }
                break;
	    case "psadd":
		$options="";
		if (self::$base64) $options="--base64";
		if (self::$htmlescape) $options="--htmlescape";
                echo "psadd $options ".self::$context." ";
                break;
	    case "psupdate":
		$options="";
		if (self::$base64) $options="--base64";
		if (self::$htmlescape) $options="--htmlescape";
                echo "psupdate $options ".self::$context." ";
                break;
            case "envarr":
		echo "declare -A $context; ";
		break;
        }
    }

    public function end($data) {
        switch (self::$oformat) {
            case "xml": 
                if (self::$context) { echo "</".self::$context.">\n"; }
                break;
            case "env":
            case "envarr":
                echo "\n";
		break;
        }
    }

    public function error($txt, $code = 1) {
        fputs(self::$log, $txt . "\n");
        exit($code);
    }

    public function msg($txt) {
        fputs(self::$log, $txt);
    }
    
    public function flush() {
        ob_end_flush();
    }
    
    public function progress($msg=false,$num=false,$cnt=false) {
        if (self::$progress) {
            if ($msg) {
                psOut::msg("Progress: $msg\n");
            } else {
                psOut::msg(sprintf("Progress: %d of %d objects...    \n",$num,$cnt));
            }
            flush();
        }
    }
    
    public function slashes($str) {
	if (self::$escape) {
	  return(addcslashes($str,self::ESCAPECHARS));
        } else {
	  return($str);
        }
    }
    
    public function csvslashes($str) {
        return(addcslashes($str,"\n\r\""));
    }
    
    public function envslashes($str) {
        return(addcslashes($str,"\n\r\"".self::$csvsep));
    }
    
    public function encode($var) {
	if (self::$delchars) {
	   $var=strtr($var,self::$delchars," ");
	}
        if (self::$htmlescape) {
            $var=htmlspecialchars($var);
        }
        if (self::$base64) {
            $var=base64_encode($var);
        }
	return($var);
    }
    
    public function expvar($var) {
        if (is_array($var) || is_object($var)) {
            return(self::encode(print_r($var,true)));
        } else {
            return(self::encode($var));
        }
    }

    public function cli2($data) {
            foreach ($data as $column) {
                echo '"'.self::slashes(self::expvar($column)).'"';
		if (!($column===end($data))) echo " ";
            }
            echo "\n";
    }
    
    public function cli($data) {
            foreach ($data as $column) {
                echo self::slashes(self::expvar($column));
		if (!($column===end($data))) echo " ";
            }
            echo "\n";
    }
    
    public function multiline($data) {
            foreach ($data as $column=>$value) {
                echo $column . ":" . self::slashes(self::expvar($value))."\n";
            }
            echo "\n";
    }

    public function csv($data) {
        foreach ($data as $column => $value) {
            echo '"' .  self::csvslashes(self::expvar($value)) . '"'.self::$csvsep;
        }
        echo "\n";
    }

    public function env($data) {
        foreach ($data as $column => $value) {
            echo sprintf("%s='%s'; ", $column, self::envslashes(self::expvar($value)));
        }
    }

    public function envarr($data) {
        foreach ($data as $column => $value) {
            echo sprintf("%s[%s,%s]='%s'; ", self::$context, $data["id"], $column, self::envslashes(self::expvar($value)));
        }
    }

    public function psadd($data) {
        foreach ($data as $column => $value) {
	    if ($column=="id") continue;
            echo sprintf("%d %s='%s'", $column, self::slashes(self::expvar($value)));
        }
        echo "\n";
    }
    
    public function psupdate($data) {
        foreach ($data as $column => $value) {
	    if ($column=="id") continue;
            echo sprintf("%d %s='%s'", $data["id"], $column, self::slashes(self::expvar($value)));
        }
        echo "\n";
    }

    public function php($data) {
        var_export($data);
    }
    
    public function export($data) {
        echo base64_encode(serialize($data))."\n";
    }
    
    public function xml($data) {
        $row = self::$category;
        if ($row) echo " <$row>\n";
        foreach ($data as $column => $value) {
            echo sprintf("  <%s>%s</%s>\n", $column, self::expvar($value), $column);
        }
        if ($row) echo " </$row>\n";
    }
    
    public function help() {
        self::msg("Output formats:\n");
        self::msg("cli      - Output suitable for next CLI parsing\n");
        self::msg("cli2     - Output suitable for next CLI parsing (fields enclosed in quotes)\n");
	self::msg("pscli    - Output as pscli command to recreate object(s)\n");
        self::msg("ml       - Multiline output suitable for next CLI parsing\n");
        self::msg("csv      - CSV output (use --csv-separator to set separtor)\n");
        self::msg("xml      - XML output\n");
        self::msg("env      - Output suitable for environment setting\n");
	self::msg("envarr   - Output suitable for environment setting (as bash array)\n");
    }

}
