#!/bin/bash

function installOrUpdate
{
    echo "Installing/Updating $1"

    if [ ! -d "$1" ] ; then
        git clone $2 $1
    fi

    cd $1
    git fetch -q origin
    git reset --hard $3
    cd -
}

installOrUpdate "vendor/Symfony/Component/ClassLoader" "http://github.com/symfony/ClassLoader.git" "origin/master"
installOrUpdate "vendor/Symfony/Component/EventDispatcher" "http://github.com/symfony/EventDispatcher.git" "origin/master"
installOrUpdate "vendor/Buzz" "http://github.com/kriswallsmith/Buzz.git" "origin/master"
installOrUpdate "vendor/Guzzle" "http://github.com/guzzle/guzzle.git" "origin/master"

if [ ! -d "vendor/Zend" ] ; then
    svn co http://framework.zend.com/svn/framework/standard/trunk/library/Zend/Http vendor/Zend/Http
    svn co http://framework.zend.com/svn/framework/standard/trunk/library/Zend/Loader vendor/Zend/Loader
    svn co http://framework.zend.com/svn/framework/standard/trunk/library/Zend/Uri vendor/Zend/Uri
    svn co http://framework.zend.com/svn/framework/standard/trunk/library/Zend/Validate vendor/Zend/Validate
    wget http://framework.zend.com/svn/framework/standard/trunk/library/Zend/Exception.php -O vendor/Zend/Exception.php
    wget http://framework.zend.com/svn/framework/standard/trunk/library/Zend/Loader.php -O vendor/Zend/Loader.php
    wget http://framework.zend.com/svn/framework/standard/trunk/library/Zend/Uri.php -O vendor/Zend/Uri.php
fi
