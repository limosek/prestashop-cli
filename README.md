# Prestashop CLI utils

This software is used to communicate with Prestashop via its WWW API and to get/modify data inside. 
It is not for managing installation of prestashop or automatizing updates. It is used to communicate via WWW interface
with Prestashop and do some usefull things. Data manipulation can be done absolutely externally from Prestashop.
If you know your shell and it's power and you need to bulk edit your Prestashop internal data, this project is probably for you.
Please take care when modifying your data! Always backup your Prestashop before use! It is good practice to create web api key 
with readonly access (or minimal access) to test everything.

# Howto

## Installation ##
Prestasoph-cli needs php5 cli and PHP pear package. It is theoreticaly possible to run this software on Windows using Cygwin
but it is not tested. On debian systems, use:
```
$ sudo apt-get install php5-cli php-pear php5-curl git
$ git clone https://github.com/limosek/prestashop-cli.git
$ cd prestashop-cli
$ {pslist|psget|psprops|psupdate|psdel} [options]
```

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

[list]
; Parameters for listing objects (to get their ids)

[get]
; Parameters for getting objects (to get their data)

[update]
; Parameters for updating objects

[delete]
; Parameters for updating objects


[addresses]
; Parameters for geting specific kind of objects

```

## Use ##

### List all products ###
```
$ ./list products
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
$ ./getprops products
id id_manufacturer id_supplier id_category_default new cache_default_attribute id_default_image id_default_combination id_tax_rules_group position_in_category manufacturer_name quantity type id_shop_default reference supplier_reference location width height depth weight quantity_discount ean13 upc cache_is_pack cache_has_attachments is_virtual on_sale online_only ecotax minimal_quantity price wholesale_price unity unit_price_ratio additional_shipping_cost customizable text_fields uploadable_files active redirect_type id_product_redirected available_for_order available_date condition show_price indexed visibility advanced_stock_management date_add date_upd pack_stock_type meta_description meta_keywords meta_title link_rewrite name description description_short available_now available_later associations 
```

### Get product values ###
```
$ ./get --output-format=csv product 8
"8";"2";"0";"12";"";"80";"95";"80";"1";"1";"Odeli.cz";"0";"simple";"1";"kmast";"";"";"7.000000";"4.500000";"4.500000";"0.060000";"0";"0";"";"0";"0";"0";"0";"0";"0.000000";"1";"0.000000";"0.000000";"";"0.000000";"0.00";"0";"0";"0";"1";"404";"0";"1";"2015-04-15";"new";"1";"1";"both";"0";"2014-05-18 17\:08\:54";"2015-10-07 13\:24\:28";"3";"";"";"";"kostivalova-mast";"Kostivalová mast 100ml";"\<h3\>Informace\</h3\>\n\<p\>Příznivé léčivé vlastnosti kostivalu jsou známé už dlouho. Ve formě masti se používá k tlumení bolesti a urychlení hojení různých poranění – od těžkých pohmožděnin přes bolestivá vymknutí kotníků až po lehčí oděrky. Odborníky ovšem stále překvapuje, jak moc účinný kostival je – masti dávají stále častěji přednost před nejmodernějšími léky.\</p\>\n\<p\>Naražené místo se potře kostivalovou mastí, překryje mikrotenem a zafixuje obinadlem. Během krátké doby zmizí bolest a hematom se mnohem rychleji vstřebá.\</p\>\n\<p\>\</p\>\n\<h3\>Složení\</h3\>\n\<ul\>\<li\>\<span style=\"font-size\:11px\;\"\>Domácí vepřové sádlo\</span\>\</li\>\n\<li\>\<span style=\"font-size\:11px\;\"\>Kostival kořen\</span\>\</li\>\n\<li\>\<span style=\"font-size\:11px\;\"\>Šalvěj\</span\>\</li\>\n\<li\>\<span style=\"font-size\:11px\;\"\>Tymián\</span\>\</li\>\n\</ul\>";"\<p\>Kostivalová mast s blahodárným účinkem na kosti a klouby.\</p\>\n\<p\>\</p\>";"";"";"";
```

### List products with filter ###
You can use one or more filters. There is logical and between filter outputs. 
Filter operators are =,<,>,~,! (equal, less than, bigger than, regexp, not regexp)
```
$ ./list products id_manucacturer=1
$ ./list products 'price>10' id_manucacturer=1 # (logical and)
```

### Update product values ###
```
$ ./update product 8 quantity=10 
$ ./update product 8 quantity=10 price=20 # (set both values)
```

## Output modes ##
Available output modes: cli, csv, env, php
```
$ ./get product --output-mode=csv 8
$ ./get product --output-format=xml 8
$ ./get product --output-format=php 8
$ ./get product --output-format=env 8
```

# Licence

Licenced under GPLv3
