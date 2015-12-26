#!/bin/sh

###########################################################
## This script is used as practical guide to prestashop-cli 
## It will export orders to import as invoices
## Arguments are filter(s) to match orders
###########################################################

orders=$(pslist orders)
eval $(pslist -Fenvarr orders "$@" invoice_number invoice_date reference)

for o in $orders; do
  eval $(pslist -Fenvarr order_details id_order=$o)
done


eval $(pslist -Fenvarr orders "$@" invoice_number invoice_date reference)

for o in $orders; do
    eval $(psget -Fenv customer $c)
    cid=$(expr $c + $idshift)
    address=$(pslist addresses id_customer=$id | head -1)
    if [ -n "$address" ]; then
      eval $(psget -Fenv address $address)
      echo "$cid;$lastname;$firstname;$address1;$city;$postcode;$phone;$phone_mobile;$email"
    fi
done