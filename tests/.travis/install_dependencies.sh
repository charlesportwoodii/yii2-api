#!/bin/sh
set -e

LIBSODIUMVERSION=1.0.13

cd $HOME;
git clone -b $LIBSODIUMVERSION https://github.com/jedisct1/libsodium.git libsodium-$LIBSODIUMVERSION;
cd $HOME/libsodium-$LIBSODIUMVERSION;
./autogen.sh;
./configure
sudo CFLAGS="-fPIC" make install;