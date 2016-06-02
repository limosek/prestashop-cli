# Contrib scripts of Prestashop CLI #

This directory contains demo scripts how this tools can be used. Feel free to contact me if you want to contribute!
Here are some basic scripts. Please, this tools **are NOT** optimized for speed. Api calls can be very slow according to direct DB access.
Be sure to use cache for API calls. (see main README how to enable it).

## export-objects.sh ##
To export all kind of objects, use this tool. Data in CSV will be base64 encoded.
```
$ export-objects.sh products products.csv base64

```

## import-objects.sh ##
To export all kind of objects, use this tool. Data in CSV will be base64 encoded. Be afraid! This is not backup! 
Products will be cloned!
```
$ import-objects.sh product products.csv base64

```

## link-category-to-groups.sh ##

Did you create many new categories and you want to link many customers groups for them? Here is solution.
This scipt will link category(ies) to group(s). Parameters are regexp of category and regexp of group.
Unfortunately there is no API call for linking categories and groups so this script will output SQL code which has to
be inserted into your database manualy.

### Example 1 - link all categories wich contains '2015' to all groups which contains 'discount' ###

```
$ link-category-to-groups.sh 2015 discount >/tmp/import.sql
BEGIN;
DELETE FROM ps_category_group WHERE id_category=15 AND id_group=1;
INSERT INTO ps_category_group (id_category,id_group) VALUES (15,1);
DELETE FROM ps_category_group WHERE id_category=15 AND id_group=2;
INSERT INTO ps_category_group (id_category,id_group) VALUES (15,2);
DELETE FROM ps_category_group WHERE id_category=15 AND id_group=3;
INSERT INTO ps_category_group (id_category,id_group) VALUES (15,3);
DELETE FROM ps_category_group WHERE id_category=15 AND id_group=4;
INSERT INTO ps_category_group (id_category,id_group) VALUES (15,4);
DELETE FROM ps_category_group WHERE id_category=15 AND id_group=5;
INSERT INTO ps_category_group (id_category,id_group) VALUES (15,5);
DELETE FROM ps_category_group WHERE id_category=15 AND id_group=6;
INSERT INTO ps_category_group (id_category,id_group) VALUES (15,6);
DELETE FROM ps_category_group WHERE id_category=15 AND id_group=7;
INSERT INTO ps_category_group (id_category,id_group) VALUES (15,7);
DELETE FROM ps_category_group WHERE id_category=15 AND id_group=8;
INSERT INTO ps_category_group (id_category,id_group) VALUES (15,8);
DELETE FROM ps_category_group WHERE id_category=15 AND id_group=9;
INSERT INTO ps_category_group (id_category,id_group) VALUES (15,9);
DELETE FROM ps_category_group WHERE id_category=15 AND id_group=10;
INSERT INTO ps_category_group (id_category,id_group) VALUES (15,10);
COMMIT;
$ link-category-to-groups.sh 2015 discount | mysql -uprestashop_db_user -pprestashop_db_pw -hprestashop_db_host

``` 

### Example 2 - All customers will see all categories ###

```
$ link-category-to-groups.sh '.*' '.*'

``` 

## list-products-with-taxrule.sh ##
Sometime it is hard to find all products and their tax rules. Maybe when you deployed your shop, you used bad tax (VAT, without VAT, etc.)
This script will shou you all produts with given taxrule. If you use taxrule 0, it means without tax. If you do not enter any parameters, it will show you
all tax rules and their products.

### Example ###
```
$ pslist tax_rule_groups id name
1 CZ Standard Rate \(21%\)
2 CZ Reduced Rate \(15%\)
3 DE Standard Rate \(19%\)
4 DE Reduced Rate \(7%\)
5 DE Foodstuff Rate \(7%\)
6 DE Books Rate \(7%\)

$ list-products-with-taxrule.sh
####### Products with tax rule 1 ( CZ Standard Rate \(21%\) ):
8 Kostivalová mast
13 Bylinná mast Extra
32 Řepíková mast 100ml
####### Products with tax rule 2 ( CZ Reduced Rate \(15%\) ):
####### Products with tax rule 3 ( DE Standard Rate \(19%\) ):
####### Products with tax rule 4 ( DE Reduced Rate \(7%\) ):
####### Products with tax rule 5 ( DE Foodstuff Rate \(7%\) ):
####### Products with tax rule 6 ( DE Books Rate \(7%\) ):
####### Products without tax:
33 Měsíčková mast
67 Třezalkový olej
24 Dekorativní mýdlo transparentní \"bylinka\"
26 Dekorativní mýdlo \"hvězdička\"
```

## update-products-change-tax.sh ##
This script will find all products with given taxrule and result of it is set of commands to update their prices.
So if you want to change tax from **CZ rate** to **No tax**, this script can compute new prices and updates products to not use rules.
Parameters are taxrule_to_change new_taxrule multiply 
By default this script only outputs commands. You can see results and if it looks good, you can apply it.

### Example 1 - change all products to not use taxrule and change prices by multiply it by 1.21 ###
```
$ update-products-change-tax.sh 1 0 1.21
psupdate product 8 id_tax_rules_group=1 price*=1.21
psupdate combination 80 price*=1.21
psupdate combination 81 price*=1.21
psupdate combination 82 price*=1.21
psupdate combination 90 price*=1.21
psupdate product 13 id_tax_rules_group=1 price*=1.21

$ update-products-change-tax.sh 1 0 1.21 | sh

```

## export-xml-zbozi_cz.sh ##
This script is used to create xml feed compatible with new format of zbozi.cz xml feed.
'''
$ export-xml-zbozi_cz.sh http://odeli.cz >feed.xml
'''
To create better filter which products to export, use two pass execution. First for selecting objects, second to export:
```
$ ids=$(pslist products id_category_default=1)
$ ids2=$(pslist products id_category_default=3)
$ export-xml-zbozi_cz.sh http://odeli.cz $ids $ids2 >feed.xml
```

