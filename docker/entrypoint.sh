#!/bin/bash
set -e

# Upewniamy się, że katalog build istnieje i ma odpowiednie uprawnienia
sudo mkdir -p $APP_HOME/public/build
sudo chown -R node:node $APP_HOME/public/build
sudo chmod -R 777 $APP_HOME/public/build

# Uruchomienie przekazanego polecenia
exec "$@"
