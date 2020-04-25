# PS Console

Prestashop cli tools.

## Install Phar version

### Manual installation

Download master file at https://github.com/sas-adilis/ps_console/raw/master/bin/ps.phar and upload in prestashop root directory.

### Installation via SSH

download the file from github in your prestashop root directory ( or from the release page )

```bash
wget https://github.com/sas-adilis/ps_console/raw/master/bin/ps.phar
```

Add execution mode

```bash
chmod +x ps.phar
```

Run the console

```bash
./ps.phar
```

You can also add the phar globaly by adding it in your /usr/local/bin directory

```bash
sudo mv ps.phar /user/local/bin/ps
```

Then run it with ( Only work in Prestashop root directories )

```bash
ps
```

## Install PHP version

### Requires

Composer
Git

### How to install it

Login to your hosting with ssh and go the root directory of your prestashop

Clone the github repository in the directory console

```bash
git clone https://github.com/sas-adilis/ps_console.git console
```

Go into the directory and run composer install

```bash
cd console
composer install
```

Then everything is installed and you can run the console with

```bash
php ps.php
```
