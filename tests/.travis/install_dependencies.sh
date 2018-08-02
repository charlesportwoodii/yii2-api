#!/bin/sh
set -e

LIBSODIUMVERSION=1.0.16

cd $HOME;
git clone -b $LIBSODIUMVERSION https://github.com/jedisct1/libsodium.git libsodium-$LIBSODIUMVERSION;
cd $HOME/libsodium-$LIBSODIUMVERSION;
./autogen.sh;
./configure;
sudo make install;