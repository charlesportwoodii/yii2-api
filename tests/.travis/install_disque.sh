#!/bin/sh
set -e
# check to see if Disque folder is empty
if [ ! -d "$HOME/disque" ]; then
  cd $HOME;
  git clone https://github.com/antirez/disque -b 1.0-rc1 --depth 10;
  cd disque;
  make;
else
  echo 'Using cached directory.';
fi
