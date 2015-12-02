<?php

class psCli extends StdClass {

    static $shortopts = "hvu:k:F:";
    static $longopts = Array(
        "help",
        "config-file=",
        "shop-url=",
        "shop-key=",
        "language=",
        "long",
        "debug",
        "verbose",
        "progress",
        "output-format=",
        "properties=",
        "base64",
        "buffered",
        "dry"
    );
    static $resources = Array(
        'addresses' => 'The Customer, Manufacturer and Customer addresses',
        'carriers' => 'The Carriers',
        'cart_rules' => 'Cart rules management',
        'carts' => 'Customers carts',
        'categories' => 'The product categories',
        'combinations' => 'The product combination',
        'configurations' => 'Shop configuration',
        'contacts' => 'Shop contacts',
        'content_management_system' => 'Content management system',
        'countries' => 'The countries',
        'currencies' => 'The currencies',
        'customer_messages' => 'Customer services messages',
        'customer_threads' => 'Customer services threads',
        'customers' => 'The e-shops customers',
        'deliveries' => 'Product delivery',
        'employees' => 'The Employees',
        'groups' => 'The customers groups',
        'guests' => 'The guests',
        'languages' => 'Shop languages',
        'manufacturers' => 'The product manufacturers',
        'order_carriers' => 'Details of an order',
        'order_details' => 'Details of an order',
        'order_discounts' => 'Discounts of an order',
        'order_histories' => 'The Order histories',
        'order_invoices' => 'The Order invoices',
        'order_payments' => 'The Order payments',
        'order_states' => 'The Order states',
        'orders' => 'The Customers orders',
        'price_ranges' => 'Price range',
        'product_feature_values' => 'The product feature values',
        'product_features' => 'The product features',
        'product_option_values' => 'The product options value',
        'product_options' => 'The product options',
        'product_suppliers' => 'Product Suppliers',
        'products' => 'The products',
        'shop_groups' => 'Shop groups from multi-shop feature',
        'shops' => 'Shops from multi-shop feature',
        'specific_price_rules' => 'Specific price management',
        'specific_prices' => 'Specific price management',
        'states' => 'The available states of countries',
        'stock_availables' => 'Available quantities',
        'stock_movement_reasons' => 'The stock movement reason',
        'stock_movements' => 'Stock movements management',
        'stocks' => 'Stocks',
        'stores' => 'The stores',
        'suppliers' => 'The product suppliers',
        'supply_order_details' => 'Supply Order Details',
        'supply_order_histories' => 'Supply Order Histories',
        'supply_order_receipt_histories' => 'Supply Order Receipt Histories',
        'supply_order_states' => 'Supply Order States',
        'supply_orders' => 'Supply Orders',
        'tags' => 'The Products tags',
        'tax_rule_groups' => 'Tax rule groups',
        'tax_rules' => 'Tax rules entity',
        'taxes' => 'The tax rate',
        'translated_configurations' => 'Shop configuration',
        'warehouse_product_locations' => 'Location of products in warehouses',
        'warehouses' => 'Warehouses',
        'weight_ranges' => 'Weight ranges',
        'zones' => 'The Countries zones',
    );

    const E_URL = 2;
    const E_KEY = 3;
    const E_MISSOPT = 4;
    
    const P_DEFAULT = -1;
    const P_CFG = -2;
    const P_FILTER = -3;

    static $cfgfile;
    static $shop_url;
    static $shop_key;
    static $lang;
    static $long = false;
    static $debug = false;
    static $verbose = false;
    static $options = false;
    static $goptions = false;
    static $foptions = false;
    static $properties;
    static $apifields = Array("id" => 1);
    static $apifilter = Array();
    static $dry;
    static $args = false;

    /**
     * 
     * @param array $argv Arguments to parse
     * @param array $contexts Contexts to read from cfg file
     * @return array Array of parsed options
     */
    public function init($argv, $contexts=false) {
        psOut::$log = fopen('php://stderr', 'w+');
        if (isset(parent::$shortopts) && is_array(parent::$shortopts)) {
            $shortopts = array_merge(self::$shortopts, parent::$shortopts);
        } else {
            $shortopts = self::$shortopts;
        }
        if (isset(parent::$longopts) && is_array(parent::$longopts)) {
            $longopts = array_merge(self::$longopts, parent::$longopts);
        } else {
            $longopts = self::$longopts;
        }
        $o = New Console_Getopt;
        $opts = $o->getopt($argv, $shortopts, $longopts);
        if (PEAR::isError($opts)) {
            self::help();
            psOut::error('Error: ' . $opts->getMessage());
        }
        self::$args = $opts[1];
        self::$goptions = self::condense_arguments($opts);
        self::$cfgfile = self::getarg("config-file", self::$goptions, getenv("HOME") . "/.psclirc");
        if (array_key_exists("--help",self::$goptions)) {
            self::help();
            exit;
        }
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

    public function readcfg($contexts) {
        if (file_exists(self::$cfgfile)) {
            $allfoptions = parse_ini_file(self::$cfgfile, true);
            $foptions=Array();
            if (!$contexts) $contexts=Array("global");
            foreach ($contexts as $context) {
                if (array_key_exists($context,$allfoptions)) {
                    $foptions = array_merge($foptions,$allfoptions[$context]);
                }
            }
        } else {
            $foptions = Array();
        }
        $options=Array();
        foreach ($foptions as $opt => $val) {
            if ($val)
                $options[$opt] = $val;
        }
        foreach (self::$goptions as $opt => $val) {
            if (array_key_exists($opt, self::$goptions)) {
                $options[$opt] = self::$goptions[$opt];
            }
        }

        self::$shop_url = self::getarg("shop-url|u", $options);
        self::$shop_key = self::getarg("shop-key|k", $options);
        if (!self::$shop_url) {
            psOut::error("Shop url not set!", self::E_MISSOPT);
        }
        if (!self::$shop_key) {
            self::error("Shop key not set!", self::E_MISSOPT);
        }
        self::$lang = self::getarg("language|L", $options,1);
        self::$long = self::isarg("long|l", $options);
        psOut::$buffered = self::isarg("buffered", $options);
        if (psOut::$buffered) {
            ob_start();
        }
        self::$debug = self::isarg("debug|d", $options);
        self::$dry= self::isarg("dry", $options);
        self::$verbose = self::isarg("verbose|v", $options);
        psOut::$progress = self::isarg("progress|p", $options);
        psOut::$base64 = self::isarg("base64", $options);
        psOut::$oformat = self::getarg("output-format|F", $options, "cli");
        self::$properties = self::reverseProps(self::getarg("properties", $options,Array(1=>"id")));
        if (!is_array(self::$properties)) {
            self::$properties=Array(self::$properties => self::P_CFG);
        }
        if (array_key_exists("args",$foptions)) {
            self::$args=Array_merge(self::$args,$foptions["args"]);
        }
        if (self::$debug) {
            psOut::msg("Config file sections:\n" . print_r($contexts, true));
            psOut::msg("Config and CLI options:\n" . print_r($options, true));
            psOut::msg("CLI arguments:\n" . print_r(self::$args, true));
            psOut::msg("Properties to get:\n" . print_r(self::$properties, true));
        }
        self::$options = $options;
        return($options);
    }
    
    public function reverseProps($properties) {
        $out=Array();
        if (is_array($properties)) {
            foreach ($properties as $p) {
                $out[$p]=self::P_CFG;
            }
        } else {
            return(Array($properties => self::P_CFG));
        }
        return($out);
    }
    
    public function subobject($objects) {
	switch ($objects) {
            case "addresses":
                return("address");
                break;
            case "taxes":
                return("tax");
                break;
             case "deliveries":
                return("delivery");
                break;
            case "categories":
                return("category");
                break;
            default:
                return(substr($objects,0,-1));
                break;
        }
    }
    
    public function upobject($object) {
        switch ($object) {
            case "address":
                $ret="addresses";
                break;
            case "tax":
                $ret="taxes";
                break;
            case "delivery":
                $ret="deliveries";
                break;
            case "category":
                $ret="categories";
                break;
            default:
                $ret=$object."s";
                break;
        }
        if (!array_key_exists($ret,self::$resources)) {
	    psOut::error("Bad resource $object! See help.");
	} else {
	    return($ret);
	}
    }
    
    public function helpResource() {
	$ret="";
	foreach (self::$resources as $r=>$d) {
	    $ret.=sprintf("%-40s%s\n",self::subobject($r),$d);
	}
	return($ret);
    }

    public function helpResources() {
	$ret="";
	foreach (self::$resources as $r=>$d) {
	    $ret.=sprintf("%-40s%s\n",$r,$d);
	}
	return($ret);
    }
    
    public function help() {
        psOut::msg("\nCommon options:\n");
        psOut::msg("--help                  This help\n");
        psOut::msg("--buffered              Buffered output\n");
        psOut::msg("--progress              See progress messages\n");
        psOut::msg("--debug                 Enable debug output\n");
        psOut::msg("--output-format=x       Set output format\n");
        psOut::msg("--base64                Use base64 in output\n");
        psOut::msg("--language              Set id of language to use for text operations. Defaults to 1.\n");
        psOut::msg("--dry                   Do not update anything. Just simulate.\n\n");
        psOut::msg("Available resources:\n");
        if (PSCMD=="list") {
            psOut::msg(psCli::helpResources()."\n");
        } else {
            psOut::msg(psCli::helpResource()."\n");
        }
        psOut::help();
        psOut::msg("To see command specific help, run it without parameters.\n\n");
    }

}
