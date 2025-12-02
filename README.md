# Phoenix Symfony App

## Instrukcja uruchomienia (Docker)

1. **Wymagania**
   - Docker + Docker Compose

2. **Uruchomienie całości**
   ```bash
   docker-compose up --build
   ```

3. **Dostęp**
   - Symfony: [http://localhost:8000](http://localhost:8000)
   - Phoenix API: [http://localhost:4000](http://localhost:4000)

4. **Przykładowe dane**
   - Phoenix: dane użytkowników importują się automatycznie z pliku CSV przy starcie kontenera (`phoenix_api/priv/repo/users.csv`)
   - Symfony: nie wymaga przykładowych danych

   **Źródła danych do importu:**
   - Imiona:
     - https://dane.gov.pl/pl/dataset/1667,lista-imion-wystepujacych-w-rejestrze-pesel-osoby-zyjace/resource/63929/table
     - https://dane.gov.pl/pl/dataset/1667,lista-imion-wystepujacych-w-rejestrze-pesel-osoby-zyjace/resource/63924/table
   - Nazwiska:
     - https://dane.gov.pl/pl/dataset/1681,nazwiska-osob-zyjacych-wystepujace-w-rejestrze-pesel/resource/63888/table
     - https://dane.gov.pl/pl/dataset/1681,nazwiska-osob-zyjacych-wystepujace-w-rejestrze-pesel/resource/63892/table

5. **Zmienne środowiskowe**
   - Edytuj plik `.env` i/lub `docker-compose.yml` jeśli chcesz zmienić konfigurację (np. hasła, porty).

6. **Testy**
   ```bash
   docker-compose exec symfony php bin/phpunit
   ```

7. **Import przykładowych danych ręcznie**

   PowerShell:
   ```powershell
   Invoke-WebRequest `
       -Uri "http://localhost:4000/api/import" `
       -Method POST `
       -Headers @{ "Content-Type"="application/json"; "X-Import-Key"="SECRET123" }
   ```

   curl:
   ```bash
   curl -X POST "http://localhost:4000/api/import" \
        -H "Content-Type: application/json" \
        -H "X-Import-Key: SECRET123"
   ```
