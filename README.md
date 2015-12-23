![logo_buckaroo.png](https://bitbucket.org/repo/oXxajd/images/1156190506-logo_buckaroo.png)
## Composer installatie ##
* Voeg toe aan je composer.json bij "repositories": 

```
#!json

        {
            "type": "vcs",
            "url": "git@bitbucket.org:tig-team/tig-extension-tig-buckaroo-magento2.git"
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
