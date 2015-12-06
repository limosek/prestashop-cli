# Prestashop CLI utils

This software is used to communicate with Prestashop via its WWW API and to get/modify data inside. 
It is not for managing installation of prestashop or automatizing updates. It is used to communicate via WWW interface
with Prestashop and do some usefull things. Data manipulation can be done absolutely externally from Prestashop.
If you know your shell and it's power and you need to bulk edit your Prestashop internal data, this project is probably for you.
Please take care when modifying your data! Always backup your Prestashop before use! It is good practice to create web api key 
with readonly access (or minimal access) to test everything.

## Contributions are welcome ##
If you found this project usefull, you can help to improve it (yes, it is opensource :) ). Best way is to share your scripts 
into contrib directory of this project. So if you made some script which will do some mass operation with your Prestashop, 
please contact me and I will share it here.
Of course you can send some donations to bitcoin address 1EaKkkLKqC6f9DiMPUfMvbRXWJZBebe1Yx :) . 

# Howto

## Installation ##
Prestasoph-cli needs php5 cli and PHP pear package. It is theoreticaly possible to run this software on Windows using Cygwin
but it is not tested. On debian systems, use:
```
$ sudo apt-get install php5-cli php5-curl git
$ git clone https://github.com/limosek/prestashop-cli.git
$ cd prestashop-cli
$ . env.sh
$ {pslist|psget|psprops|psupdate|psdel|psenable|psdisable} [options]
```
If you use env.sh, autocompletion is working automaticaly so you can use TAB. 
If you run command without parameters, it will show help options.
You can even use --help to get more help.

## Configuration ##
First, enable API access in your Prestashop and create API token.
You can use this documentation: http://doc.prestashop.com/display/PS16/Web+service+one-page+documentation
Next, create file ~/.psclirc with your configuration:
```
[global]
; Global config for all operations
debug=false
shop-url=http://yourshop.domain
shop-key=XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
; Uncomment to enable caching
;cache=true
;cache-dir=/tmp/
;cache-lifetime=3600

[list]
; Parameters for listing objects (to get their ids)

[get]
; Parameters for getting objects (to get their data)

[props]
; Parameters for getting properties of objects

[update]
; Parameters for updating objects

[delete]
; Parameters for updating objects

[addresses]
; Parameters for listing specific kind of objects
; Uncomment next lines and pslist addresses will always return firstname,lastname and city
;properties[]=firstname
;properties[]=lasttname
;properties[]=city

[address]
; Parameters for geting specific kind of object
```

## Use ##

### Available resources ###
In theory, any resource can be fetched/set. See --help option of any command.
```
Available resources:
address                                 The Customer, Manufacturer and Customer addresses
carrier                                 The Carriers
cart_rule                               Cart rules management
cart                                    Customers carts
categorie                               The product categories
combination                             The product combination
configuration                           Shop configuration
contact                                 Shop contacts
content_management_syste                Content management system
countrie                                The countries
currencie                               The currencies
customer_message                        Customer services messages
customer_thread                         Customer services threads
customer                                The e-shops customers
delivery                                Product delivery
employee                                The Employees
group                                   The customers groups
guest                                   The guests
language                                Shop languages
manufacturer                            The product manufacturers
order_carrier                           Details of an order
order_detail                            Details of an order
order_discount                          Discounts of an order
order_historie                          The Order histories
order_invoice                           The Order invoices
order_payment                           The Order payments
order_state                             The Order states
order                                   The Customers orders
price_range                             Price range
product_feature_value                   The product feature values
product_feature                         The product features
product_option_value                    The product options value
product_option                          The product options
product_supplier                        Product Suppliers
product                                 The products
shop_group                              Shop groups from multi-shop feature
shop                                    Shops from multi-shop feature
specific_price_rule                     Specific price management
specific_price                          Specific price management
state                                   The available states of countries
stock_available                         Available quantities
stock_movement_reason                   The stock movement reason
stock_movement                          Stock movements management
stock                                   Stocks
store                                   The stores
supplier                                The product suppliers
supply_order_detail                     Supply Order Details
supply_order_historie                   Supply Order Histories
supply_order_receipt_historie           Supply Order Receipt Histories
supply_order_state                      Supply Order States
supply_order                            Supply Orders
tag                                     The Products tags
tax_rule_group                          Tax rule groups
tax_rule                                Tax rules entity
tax                                     The tax rate
translated_configuration                Shop configuration
warehouse_product_location              Location of products in warehouses
warehouse                               Warehouses
weight_range                            Weight ranges
zone                                    The Countries zones
```

### List all products ###
```
$ ./pslist products
8 
13 
32 
33 
67 
24 
26 
```

### List all properties for product ###
```
$ ./psprops product
id
id_manufacturer
id_supplier
id_category_default
new
cache_default_attribute
id_default_image
id_default_combination
id_tax_rules_group
position_in_category
manufacturer_name
quantity
type
id_shop_default
reference
supplier_reference
location
width
height
depth
weight
quantity_discount
ean13
upc
cache_is_pack
cache_has_attachments
is_virtual
on_sale
online_only
ecotax
minimal_quantity
price
wholesale_price
unity
unit_price_ratio
additional_shipping_cost
customizable
text_fields
uploadable_files
active
redirect_type
id_product_redirected
available_for_order
available_date
condition
show_price
indexed
visibility
advanced_stock_management
date_add
date_upd
pack_stock_type
meta_description
meta_keywords
meta_title
link_rewrite
name
description
description_short
available_now
available_later
```

### Languages ###
By default, all operations are made with default languageid 1.
If you want to operate on other language, use --language=id
To see available languages, run
```
$ ./pslist languages name iso_code active active=1
```

### List product with filter and more fields ###
You can use one or more filters. There is logical and between filter outputs. 
Filter operators are =,<,>,~,!=,!~ (equal, less than, bigger than, regexp, not equal, not regexp)
Do not forget to use '' due to shell special characters!
By default only ids are returned. To return more properties, use property name without filter.
```
#                   Only_new   Include_price_in_output   Include_name_in_output    Filter: price has to be bigger than 10
$ ./pslist products condition=new      price                     name                      'price>10'
$ ./pslist products id_manucacturer=1
$ ./pslist products 'price>10' id_manucacturer=1 # (logical and)
$ (./pslist products 'price>10'; ./pslist products id_manucacturer=1) # (logical or)
```

### Get product values ###
```
$ ./psget --output-format=csv product 8
"8";"2";"0";"12";"";"80";"95";"80";"1";"1";"Odeli.cz";"0";"simple";"1";"kmast";"";"";"7.000000";"4.500000";"4.500000";"0.060000";"0";"0";"";"0";"0";"0";"0";"0";"0.000000";"1";"0.000000";"0.000000";"";"0.000000";"0.00";"0";"0";"0";"1";"404";"0";"1";"2015-04-15";"new";"1";"1";"both";"0";"2014-05-18 17\:08\:54";"2015-10-07 13\:24\:28";"3";"";"";"";"kostivalova-mast";"Kostivalová mast 100ml";"\<h3\>Informace\</h3\>\n\<p\>Příznivé léčivé vlastnosti kostivalu jsou známé už dlouho. Ve formě masti se používá k tlumení bolesti a urychlení hojení různých poranění – od těžkých pohmožděnin přes bolestivá vymknutí kotníků až po lehčí oděrky. Odborníky ovšem stále překvapuje, jak moc účinný kostival je – masti dávají stále častěji přednost před nejmodernějšími léky.\</p\>\n\<p\>Naražené místo se potře kostivalovou mastí, překryje mikrotenem a zafixuje obinadlem. Během krátké doby zmizí bolest a hematom se mnohem rychleji vstřebá.\</p\>\n\<p\>\</p\>\n\<h3\>Složení\</h3\>\n\<ul\>\<li\>\<span style=\"font-size\:11px\;\"\>Domácí vepřové sádlo\</span\>\</li\>\n\<li\>\<span style=\"font-size\:11px\;\"\>Kostival kořen\</span\>\</li\>\n\<li\>\<span style=\"font-size\:11px\;\"\>Šalvěj\</span\>\</li\>\n\<li\>\<span style=\"font-size\:11px\;\"\>Tymián\</span\>\</li\>\n\</ul\>";"\<p\>Kostivalová mast s blahodárným účinkem na kosti a klouby.\</p\>\n\<p\>\</p\>";"";"";"";
```

### Update values for object ###
We always update only single object. If you want to update more objects, use shell scripting.
```
$ ./psupdate product 8 quantity=10 
$ ./psupdate product 8 quantity=10 price=20 # (set both values)
```

### Enable or disable object ###
```
$ ./psenable product 8
$ ./psdisable product 8
```

### Delete object ###
```
$ ./psenable product 8
$ ./psdisable product 8

## Output modes ##
Available output modes: cli, cli2, csv, env, php, ml
```
$ ./get product --output-mode=csv 8
$ ./get product --output-format=xml 8
$ ./get product --output-format=php 8
$ ./get product --output-format=env 8
```

# Licence

Licenced under GPLv3
