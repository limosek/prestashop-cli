#!/bin/sh

###########################################################
## This script is used as practical guide to prestashop-cli 
## It will export customers and addresses to csv
## If there is one argument, it is number which is
## added to ids of customer to avoid conflicts.
###########################################################

if [ -n "$1" ]; then
  idshift="$1"
else
  idshift=0
fi

customers=$(pslist customers)

for c in $customers; do
    eval $(psget -Fenv customer $c)
    cid=$(expr $c + $idshift)
    address=$(pslist addresses id_customer=$id | head -1)
    if [ -n "$address" ]; then
      eval $(psget -Fenv address $address)
      echo "$cid;$lastname;$firstname;$address1;$city;$postcode;$phone;$phone_mobile;$email"
    fi
done