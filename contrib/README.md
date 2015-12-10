# Contrib scripts of Prestashop CLI #

This directory contains demo scripts how this tools can be used. Feel free to contact me if you want to contribute!
Here are some basic scripts. Please, this tools **are NOT** optimized for speed. Api calls can be very slow according to direct DB access.
Be sure to use cache for API calls. (see main README how to enable it).

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

## list-products-with-combinations.sh ##
This script will list all products, their combination, prices and quantities. Sometime it is hard to find all combinations
of product via web and to check prices. You can see all this informations in one text file:

### Example ###
```
$ list-products-with-combinations.sh
"Kostivalová mast" 76.000000 "Balení":Dárková dóza 50ml s \"frost\" efektem¸+ bílé víčko 0
"Kostivalová mast" 32.000000 "Balení":Dóza 15ml dvouplášťová, bílá 0
"Kostivalová mast" 99.000000 "Balení":Dóza 100ml průhledná + bílé víčko 0
"Kostivalová mast" 49.000000 "Balení":Dárková dóza 30ml s \"frost\" efektem 0
"Levandulový sprchový gel" 79.000000 "Balení":Lahvička 200ml průhledná 19
...
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


