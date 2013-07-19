Install ICU/INTL
========================

This project requires ICU / INTL v.49 this is not installed by default.

1. To install for Windows
===========

1. Download the PHP INTL module from: http://pecl.php.net/package/intl (5.3, no ZTS. 2.0.1)
2. Extract the file and copy php_intl.dll to the C:\Program Files (x86)\Zend\ZendServer\lib\phpext directory (if file already exists, backup it first!)
3. Download ICU4C (http://site.icu-project.org/download/49#TOC-ICU4C-Download) (icu4c-49_1_2-Win32-mscv10.zip)
4. Extract this file copy the following file icu\bin\icu*.dll (all icu DLL files) to C:\Program Files (x86)\Zend\ZendServer\bin
5. Restart Apache

2. To install for Mac OSX
===========

1. Make sure you have [XCode](https://developer.apple.com/xcode) installed including the command line tools

2. Download ICU4C (v49) source http://download.icu-project.org/files/icu4c/49.1.2/icu4c-49_1_2-src.tgz

3. Extract this archive:

        tar -xvzf icu4c-49_1_2-src.tgz

4. Configure and compile and install the ICU package

        cd icu/source
        chmod +x runConfigureICU configure install-sh
        ./runConfigureICU MacOSX --with-library-bits=32
        make
        sudo make install

5. Compile the PHP Intl module

intl requires "autoconf", to install execute:

Install autoconf

    sudo port install autoconf

Compile intl

    sudo CFLAGS="-arch i386" pecl install intl

`When asked for the header files specify: "/usr/local"`

3. To install for Mac OSX
===========

To install on Linux

g++ compiler etc.

    apt-get install build-essential autoconf
    cd /tmp
    wget http://download.icu-project.org/files/icu4c/49.1.2/icu4c-49_1_2-src.tgz
    tar -xvzf icu4c-49_1_2-src.tgz
    cd icu/source
    ./configure
    make
    make install
    /usr/local/zend/bin/pecl install intl
    /etc/init.d/apache2 restart
    
`When asked for the header files specify: "/usr/local"`

    /etc/init.d/apache2 restart
