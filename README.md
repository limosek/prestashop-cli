# Prestashop CLI utils

This software is used to communicate with Prestashop via its API and to get/modify data inside. 

# Howto

## Configuration ##
First, enable API access in your Prestashop and create API token. Next, create file ~/.psclirc with configuration:

```
[global]

debug=false
shop-url=http://yourshop.domain
shop-key=XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX

```

## Use ##
```
$ ./listroducts.php
```

# Licence

Licenced under GPLv3
