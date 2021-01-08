# PS Console

Prestashop cli tools. [View available commands](https://github.com/sas-adilis/ps_console/blob/master/COMMANDS.md)

## Install using PS Console Manager

If your prestashop already installed you could install [PS Console Manager](https://github.com/sas-adilis/ps_console_manager).

## Install Phar version

In your prestashop root directory

```bash

wget https://github.com/sas-adilis/ps_console/raw/master/bin/psc.phar
chmod +x psc
php psc.phar list

```

## Install PHP version

### Requires

Composer, Git

### How to install it

Still in prestashop root directory

```bash

git clone https://github.com/sas-adilis/ps_console.git console
cd console
composer install
php ps.php

```

## Install Docker version

### Requires

Docker

### How to install it

```bash

git clone https://github.com/sas-adilis/ps_console.git console
cd console
docker build -t ps-console .
docker run -ti --rm -v `pwd`:/opt/app --network="host" ps-console
```

Nb. You are mysql host address needs to be 127.0.0.1 instead of localhost in ./app/config/parameters.php
