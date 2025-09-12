# Rozwiązanie problemu z uprawnieniami Laravel storage w środowisku Docker

## Aktualna sytuacja
Problem pojawia się, ponieważ katalog storage jest montowany z hosta do kontenera (`/home/services/kayak-prod/storage`), a pliki należą do użytkownika root zamiast www-data.

## Rozwiązanie krótkoterminowe

1. Na serwerze host wykonaj:
```bash
# Zmień właściciela
sudo chown -R www-data:www-data /home/services/kayak-prod/storage

# Ustaw uprawnienia
sudo chmod -R 775 /home/services/kayak-prod/storage

# Stwórz wymagane katalogi
sudo mkdir -p /home/services/kayak-prod/storage/logs
sudo mkdir -p /home/services/kayak-prod/storage/framework/{cache,sessions,Pages}

# Ustaw uprawnienia dla nowych katalogów
sudo chown -R www-data:www-data /home/services/kayak-prod/storage/*
```

2. Zrestartuj kontener:
```bash
docker restart kayak-app-prod
```

## Jak unikać tego w przyszłości

1. Podczas deploymentu:
    - Dodaj skrypt, który automatycznie ustawia uprawnienia po wdrożeniu
    - Umieść go w pipeline CI/CD lub w skrypcie post-deploy

2. W Dockerfile:
```dockerfile
# Dodaj na końcu Dockerfile
RUN chown -R www-data:www-data /var/www/html/storage
```

3. W docker-compose:
    - Możesz użyć user: www-data dla serwisów
   ```yaml
   services:
     app:
       user: "www-data:www-data"
   ```

4. Stwórz skrypt pomocniczy `fix-permissions.sh`:
```bash
#!/bin/bash
sudo chown -R www-data:www-data /home/services/kayak-prod/storage
sudo chmod -R 775 /home/services/kayak-prod/storage
sudo mkdir -p /home/services/kayak-prod/storage/{logs,framework/{cache,sessions,Pages}}
sudo chown -R www-data:www-data /home/services/kayak-prod/storage/*
```

## Lista kontrolna przy wdrożeniu
1. Sprawdź uprawnienia katalogów na hoście
2. Upewnij się, że wszystkie wymagane podkatalogi istnieją
3. Zweryfikuj właściciela plików (powinien być www-data)
4. Sprawdź uprawnienia (775)
5. Przetestuj zapisy do logów po wdrożeniu

## Monitorowanie
- Regularnie sprawdzaj logi błędów
- Ustaw monitoring na błędy 500
- Skonfiguruj alerty przy problemach z zapisem do storage
