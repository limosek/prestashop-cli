<?php

class psCli extends StdClass {

    const SHORTOPTS = "hvu:k:";
    const LONGOPTS = Array(
        "help",
        "config-file=",
        "shop-url=",
        "shop-key=",
        "language=",
        "long",
        "debug",
        "verbose",
        "progress",
        "output-format="
    );
    const E_URL = 2;
    const E_KEY = 3;
    const E_MISSOPT = 4;

    static $cfgfile;
    static $shop_url;
    static $shop_key;
    static $lang;
    static $long = false;
    static $debug = false;
    static $verbose = false;
    static $options = false;
    static $args = false;

    /**
     * 
     * @param array $argv Arguments to parse
     * @param array $longopts Long options for extension else false
     * @param array $shortopts Short options for extension else false
     * @return array Array of parsed options
     */
    public function init($argv, $longopts = false, $shortopts = false) {
        psOut::$log = fopen('php://stderr', 'w+');
        if (is_array($shortopts)) {
            $shortopts = array_merge(self::SHORTOPTS, $shortopts);
        } else {
            $shortopts = self::SHORTOPTS;
        }
        if (is_array($longopts)) {
            $longopts = array_merge(self::LONGOPTS, $longopts);
        } else {
            $longopts = self::LONGOPTS;
        }
        $o = New Console_Getopt;
        $opts = $o->getopt($argv, $shortopts, $longopts);
        if (PEAR::isError($opts)) {
            self::help();
            self::error('Error: ' . $opts->getMessage());
        }
        $goptions = $opts[0];
        $options["args"] = $opts[1];
        self::$args = $opts[1];
        $goptions = self::condense_arguments($opts);
        self::$cfgfile = self::getarg("config-file", $goptions, getenv("HOME") . "/.psclirc");
        if (file_exists(self::$cfgfile)) {
            $foptions = self::readcfg(self::$cfgfile);
        } else {
            $foptions = Array();
        }
        foreach ($foptions as $opt => $val) {
            if ($val)
                $options[$opt] = $val;
        }
        foreach ($goptions as $opt => $val) {
            if (array_key_exists($opt, $goptions)) {
                $options[$opt] = $goptions[$opt];
            }
        }

        self::$shop_url = self::getarg("shop-url|u", $options);
        self::$shop_key = self::getarg("shop-key|k", $options);
        if (!self::$shop_url) {
            self::error("Shop url not set!", self::E_MISSOPT);
        }
        if (!self::$shop_key) {
            self::error("Shop key not set!", self::E_MISSOPT);
        }
        self::$lang = self::getarg("language|L", $options,1);
        self::$long = self::isarg("long|l", $options);
        self::$debug = self::isarg("debug|d", $options);
        self::$verbose = self::isarg("verbose|v", $options);
        psOut::$progress = self::isarg("progress|p", $options);
        psOut::$oformat = self::getarg("output-format", $options, "cli");
        if (self::$debug) {
            //self::msg("Available options:\n" . print_r($longopts, true));
            //self::msg("Getopt options:\n" . print_r($opts, true));
            psOut::msg("Config and CLI options:\n" . print_r($options, true));
        }
        self::$options = $options;
        if (self::isarg("help|h", $options)) {
            self::help();
            exit;
        }
        return($options);
    }

    private function condense_arguments($params) {
        $new_params = array();
        foreach ($params[0] as $param) {
            $name = $param[0];
            $value = $param[1];
            if (array_key_exists($name, $new_params)) {
                if (is_array($new_params[$name])) {
                    array_push($new_params[$name], $param[1]);
                } else {
                    $new_params[$name] = Array($new_params[$name], $value);
                }
            } else {
                $new_params[$name] = $value;
            }
        }
        return $new_params;
    }

    static function getarg($args, $options = null, $default = false) {
        if ($options === null)
            $options = self::$options;
        $opts = preg_split("/\|/", $args);
        foreach ($opts as $arg) {
            if (array_key_exists($arg, $options)) {
                return($options[$arg]);
            } elseif (array_key_exists("--" . $arg, $options)) {
                return($options["--" . $arg]);
            } elseif (array_key_exists("-" . $arg, $options)) {
                return($options["-" . $arg]);
            }
        }
        return($default);
    }

    static function isarg($arg, $options = null) {
        if ($options === null)
            $options = self::$options;
        $opts = preg_split("/\|/", $arg);
        foreach ($opts as $arg) {
            if (array_key_exists($arg, $options)) {
                return(true);
            } elseif (array_key_exists("--" . $arg, $options)) {
                return(true);
            }
        }
        return(false);
    }

    public function readcfg($file) {
        return(parse_ini_file($file, false));
    }

    public function args($args) {
        $ret = Array();
        foreach ($args as $idx => $arg) {
            if ($idx == 0)
                continue;
            if ($arg[0] != "-") {
                $ret[] = $arg;
            }
        }
        return($ret);
    }

    public function help() {
        psOut::msg("Common options:\n");
        psOut::msg("--help        This help\n");
        psOut::msg("--verbose     Be verbose\n");
        psOut::msg("--debug       Enable debug output\n");
    }

}
