#!/bin/bash

###########################################################
## This script is used as practical guide to prestashop-cli 
## It will scan all products and combinations and set reference [if empty]
###########################################################

getcref(){
    echo "odeli-c-$i"
}

getpref(){
    echo "odeli-p-$i"
}

# Get all combination ids into variable. 
combinations=$(pslist combinations "$@")

# Products with price=0 are combinations only for me
products=$(pslist products active=1 "$@")

i=100

for c in $combinations; do
  	echo psupdate combination $c reference=$(getcref)
  	i=$(expr $i + 1)
done

for p in $products; do
	echo psupdate product $p reference=$(getpref)
	i=$(expr $i + 1)
done

