#!/bin/sh
set -e
# check to see if Disque folder is empty
if [ ! -d "$HOME/disque/src" ]; then
  cd $HOME;
  git clone https://github.com/antirez/disque -b 1.0-rc1 --depth 10;
  cd disque;
  make;
else
  echo 'Using cached directory for Disque';
fi


# check if libsodium is already installed
#if [ ! -d "$HOME/libsodium/lib" ]; then
  rm -rf $HOME/libsodium;
  cd $HOME;
  git clone https://github.com/jedisct1/libsodium --branch stable --depth 10;
  cd libsodium;
  ./autogen.sh;
  ./configure --prefix=$HOME/libsodium-lib;
  make;
  make install;
#else
#  echo 'Using cached directory.'
#fi

#if [ ! -d "$HOME/libsodium-php/modules" ]; then
  rm -rf $HOME/libsodium-php;
  cd $HOME;
  git clone https://github.com/jedisct1/libsodium-php -b 1.0.6;
  cd libsodium-php;
  phpize;
  ./configure --with-libsodium=$HOME/libsodium-lib;
  make;
  make install;
#else
#  cd $HOME/libsodium;
#  make install;
#fi

echo "extension = libsodium.so" >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini