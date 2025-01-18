#!/bin/bash

# Definicja ścieżki głównej
STORAGE_PATH="/home/services/kayak-prod/storage"

# Zmień właściciela
chown -R www-data:www-data $STORAGE_PATH

# Ustaw uprawnienia
chmod -R 775 $STORAGE_PATH

# Stwórz wymagane katalogi
mkdir -p $STORAGE_PATH/logs
mkdir -p $STORAGE_PATH/framework/{cache,sessions,views}

# Ustaw uprawnienia dla katalogów framework
chmod -R 775 $STORAGE_PATH/framework/cache
chmod -R 775 $STORAGE_PATH/framework/sessions
chmod -R 775 $STORAGE_PATH/framework/views
chmod -R 775 $STORAGE_PATH/logs

echo "Uprawnienia zostały zaktualizowane pomyślnie"
