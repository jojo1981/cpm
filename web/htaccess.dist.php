<?php

header("HTTP/1.0 404 Not Found");
die();

/*
 * Rename this file to .htaccess and remove this content, the block <?php ?>
 */

?>
#
# This file is part of the Composer Package Manager.
#
# (c) Joost Nijhuis <jnijhuis81@gmail.com>
#
# For the full copyright and license information, please view the LICENSE
# file that was distributed with this source code.
#

<IfModule mod_env.c>

    SetEnv APPLICATION_ENV dev

</IfModule>

<IfModule mod_rewrite.c>

    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^(.*)$ index.php [QSA,L]

</IfModule>
