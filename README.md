Composer Package Manager
========================

The Composer Package Manager application can be used to create a private
composer repository to manage private packages. Also this application function
as a proxy for packagist.org. Packages can be stored locally for backup purposes.
With this application you can guarantee that deployments on production servers doesn't
depends on the availability of packagist.org or where the used packages are located.
Also your protected against the removal of packages by the vendor when your software depends
on it. You don't want to discover this while you're deploying on production, aren't you?


1) Installing the Composer Package Manager application
----------------------------------

### Download the application

clone from GitHup: https://github.com/jojo1981/cpm.git

### Use Composer to install dependencies

This application uses Composer to manage its dependencies

If you don't have Composer yet, download it following the instructions on
http://getcomposer.org/ or just run the following command:

    curl -s http://getcomposer.org/installer | php

Then, use the `install` command to install the dependencies:

    php composer.phar install

Composer will install all the dependencies into the /vendor directory

IMPORTANT: For windows users, PHP >=5.4.0 must be installed in order to let the git downloader to work
when not triggered by a cli script but by a browser call.

Edit composer.json

change: "php":                           ">=5.3.3",
into:   "php":                           ">=5.4.*",

Run php composer.phar update

2) Configuration
-------------------------------


2) Use the application
-------------------------------