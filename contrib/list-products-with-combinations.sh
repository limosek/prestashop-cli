#!/bin/sh

pslist combinations id id_product id_product_option price| while read id id_product id_product_option price; do
    product=$(psget product $id_product name price)
    options=$(psget product_option_value $id_product_option name)
    echo $product $options
done

