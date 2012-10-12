Composer Package Manager
========================

The Composer Package Manager application can be used to create a private
composer repository to manage private packages. Also this application function
as a proxy for packagist.org. Packages can be stored locally for backup purposes.
With this application you can garantee that deploymetns on production servers doesn't
depends on the availability of packagist.org or where the used packages are located.
Also your protected agains the removal of packages by the vendor when your software depends
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

2) Configuration
-------------------------------


2) Use the application
-------------------------------