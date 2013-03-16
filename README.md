Composer Package Manager
========================

The Composer Package Manager application can be used to create a private
composer repository to manage private packages. Also this application function
as a proxy for packagist.org. Packages can be stored locally for backup purposes.
With this application you can guarantee that deployments on production servers doesn't
depends on the availability of packagist.org or where the used packages are located.
Also your protected against the removal of packages by the vendor when your software depends
on it. You don't want to discover this while you're deploying on production, aren't you?


1. Installing the Composer Package Manager application
===========

### 1.1 Requirements

GIT should be installed, and accessible from your PATH.
http://git-scm.com

SVN client application should be installed

### 1.2 Download the application

Get from GitHup: https://github.com/jojo1981/cpm.git

    git clone https://github.com/jojo1981/cpm.git

### 1.3 Create directories

The following directories need to be created:

- ./app/tmp
- ./app/logs
- ./app/cache
- ./data/index
- ./data/packages

### 1.4 Set file permissions for the user under which the webserver runs

Make sure that the user under which the webserver runs, mostly `apache` or `www-data` has write access on the following directories:

- ./app/tmp
- ./app/logs
- ./app/cache
- ./data

### 1.5 Check Symfony2 requirements

Run this command in order to check whether all requirements are met.

    php app/check.php

### 1.6 Use Composer to install dependencies

This application uses Composer to manage its dependencies

If you don't have Composer yet, download it following the instructions on
http://getcomposer.org/ or just run the following command:

    curl -s http://getcomposer.org/installer | php
    
the donwloaded `composer.phar` file can be placed into a directory which is listed into your PATH.
Then this application is installed server wide.

Then, use the `install` command to install the dependencies:

    php composer.phar install

Composer will install all the dependencies into the /vendor directory

IMPORTANT: For windows users, PHP >=5.4.0 must be installed in order to let the git downloader to work
when not triggered by a cli script but by a browser call.

Edit composer.json file

change:

    "php": ">=5.3.3",

into:

    "php": ">=5.4.*",

Run the `update` command to update the composer.lock file and install the dependencies:

    php composer.phar update

### 1.7 Setup configuration

Create the `parameters.yml` file by copying ./app/config/parameters.dist.yml to ./app/config/parameters.yml
Create the `.htaccess` by copying ./web/htaccess.dist.php to ./web/.htaccess, this file is responsible for setting the application environemnt in which the
application must run and that all request will go through the front controller: index.php

Edit the new `.htaccess` file and remove the following content form the file:

    <?php
    
    header("HTTP/1.0 404 Not Found");
    die();
    
    /*
    * Rename this file to .htaccess and remove this content, the block <?php ?>
    */
    
    ?>
    
Set application environment by editing `.htaccess` file, change the value after: `SetEnv APPLICATION_ENV`

    SetEnv APPLICATION_ENV prod

Make sure the Apache vhost configuration has the option: `AllowOverride All` in order to use the .htaccess file.

### 1.8 Build bootstrap

    php bin/build_bootstrap

### 1.9 Setup database

1.9.1 Setup database connection

Edit the file `./app/configs/parameters.yml`
Change the database settings:

    parameters:
        database_driver: pdo_mysql
        database_host: localhost
        database_port: ~
        database_name:
        database_user:
        database_password: 
        
The ~ means use default MySQL port (3306), you can set a different port if your database server is listening to a different port.

1.9.2 Build database

Run the following command to generate the database structure:

    php ./app/console doctrine:schema:create

Run the follwing queries on the database:

    INSERT INTO `languages` (`id`, `name`, `code`, `active`, `sortorder`) VALUES ('1', 'Nederlands', 'nl', '1', '1');
    INSERT INTO `languages` (`id`, `name`, `code`, `active`, `sortorder`) VALUES ('2', 'English', 'en', '1', '2');
    INSERT INTO `languages` (`id`, `name`, `code`, `active`, `sortorder`) VALUES ('3', 'Deutsch', 'de', '1', '3');

### 1.10 Apache - VirtualHost

You can add the following VirtualHost to your apache virtualhost configuration file (for instance http-vhosts.conf)

http-vhosts.conf:

    <VirtualHost *:80>

        # the domain name
        ServerName cpm.localhost

        # Change to your email address
        ServerAdmin admin@localhost

        # Change the path to the location of cpm (add /web to the end of the path)
        DocumentRoot "/var/www/cpm/web"
        <Directory "/var/www/cpm/web">
            Options -Indexes FollowSymLinks MultiViews
            AllowOverride All
            Order Deny,Allow
            Deny from all
            Allow from 127.0.0.1
        </Directory>

        ErrorLog "logs/cpm-error.log"
        CustomLog "logs/cpm-access.log" common

    </VirtualHost>

2) Configuration
-------------------------------


2) Use the application
-------------------------------