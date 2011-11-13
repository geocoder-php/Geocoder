#!/bin/bash

if [ ! -d "vendor/Buzz" ] ; then
    git clone git://github.com/kriswallsmith/Buzz.git vendor/Buzz
fi

if [ ! -d "vendor/Symfony" ] ; then
    git clone git://github.com/symfony/ClassLoader.git vendor/Symfony/Component/ClassLoader
fi

if [ ! -d "vendor/Guzzle" ] ; then
    git clone git://github.com/guzzle/guzzle.git vendor/Guzzle
fi

if [ ! -d "vendor/Zend" ] ; then
    svn co http://framework.zend.com/svn/framework/standard/trunk/library/Zend/Http vendor/Zend/Http
    svn co http://framework.zend.com/svn/framework/standard/trunk/library/Zend/Loader vendor/Zend/Loader
    svn co http://framework.zend.com/svn/framework/standard/trunk/library/Zend/Uri vendor/Zend/Uri
    svn co http://framework.zend.com/svn/framework/standard/trunk/library/Zend/Validate vendor/Zend/Validate
    wget http://framework.zend.com/svn/framework/standard/trunk/library/Zend/Exception.php -O vendor/Zend/Exception.php
    wget http://framework.zend.com/svn/framework/standard/trunk/library/Zend/Loader.php -O vendor/Zend/Loader.php
    wget http://framework.zend.com/svn/framework/standard/trunk/library/Zend/Uri.php -O vendor/Zend/Uri.php
fi
