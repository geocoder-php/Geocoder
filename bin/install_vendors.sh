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
