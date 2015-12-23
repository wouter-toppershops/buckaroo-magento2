## Composer installatie ##
* Voeg toe aan je composer.json bij "repositories": 

```
#!json

        {
            "type": "vcs",
            "url": "git@tig.plan.io:tig-tigbuckaroomagento2.tigmagento2buckaroo.git"
        }
```
* composer require tig/buckaroo dev-develop
* bin/magento module:enable TIG_Buckaroo
* bin/magento setup:upgrade
* mf
* Als je geen mf hebt: 
```
#!bash

bin/magento cache:flush && rm -rf var/cache/ && rm -rf var/page_cache/ && rm -rf var/generation/ && rm-rf var/di/
```