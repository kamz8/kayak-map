Wersjonowanie pakietu w projekcie PHP (np. w Laravel) odbywa się zazwyczaj zgodnie z zasadami **Semantic Versioning (SemVer)**. Jest to popularny standard, który zapewnia klarowność i przewidywalność podczas wydawania nowych wersji pakietu.

### Podstawowe zasady Semantic Versioning (SemVer)

Wersja składa się z trzech cyfr: `MAJOR.MINOR.PATCH`.

- **MAJOR**: Zmieniasz, gdy wprowadzasz **niekompatybilne zmiany** w API (breaking changes).
- **MINOR**: Zmieniasz, gdy dodajesz nową funkcjonalność w sposób **kompatybilny wstecz**.
- **PATCH**: Zmieniasz, gdy wprowadzasz **kompatybilne wstecz poprawki** błędów.

Przykład:
- `1.0.0`: Pierwsze stabilne wydanie.
- `1.1.0`: Dodano nową funkcjonalność (kompatybilna wstecz).
- `1.1.1`: Poprawiono błąd.

### Jak wersjonować pakiet?

1. **Ustal wersję początkową:**
    - Jeśli Twój pakiet jest w fazie rozwoju, rozpocznij od wersji **`0.1.0`** (wersja wstępna).
    - Gdy pakiet będzie stabilny i gotowy do produkcji, wydaj wersję **`1.0.0`**.

2. **Dodaj wersję do `composer.json`:**
   W Twoim pliku `composer.json` wersja powinna być zdefiniowana w polu `"version"`. Jeśli tego pola nie ma, Composer automatycznie użyje informacji z Git (o ile używasz Git do kontroli wersji). Przykład:

   ```json
   {
       "name": "kamz8/laravel-overpass",
       "version": "0.1.0-alpha",  // Twoja wersja
       ...
   }
   ```

   Pamiętaj, aby wersję zmieniać ręcznie przy każdym wydaniu.

3. **Tagowanie wersji w Git:**
   Używanie tagów Git jest ważnym krokiem w procesie wersjonowania, zwłaszcza jeśli korzystasz z Composera. Oto jak to zrobić:

    1. **Dodaj zmiany do Git i utwórz commit:**
       ```bash
       git add .
       git commit -m "Wersja 0.1.0 - pierwsze wydanie"
       ```

    2. **Utwórz tag dla wydania:**
       ```bash
       git tag -a v0.1.0 -m "Pierwsza wersja pakietu"
       ```

    3. **Prześlij tag do zdalnego repozytorium (jeśli używasz GitHub lub GitLab):**
       ```bash
       git push origin v0.1.0
       ```

4. **Zarządzanie kolejnymi wydaniami:**
    - Po każdym nowym wydaniu zaktualizuj wersję pakietu w pliku `composer.json` oraz utwórz nowy tag Git.
    - Zawsze staraj się przestrzegać zasad SemVer:
        - Zwiększaj **MAJOR**, jeśli zmieniasz API w sposób niekompatybilny.
        - Zwiększaj **MINOR**, jeśli dodajesz nowe funkcjonalności.
        - Zwiększaj **PATCH**, jeśli wprowadzasz poprawki błędów.

### Instrukcja krok po kroku:

1. **Przygotowanie nowego wydania:**
    - Sprawdź, jakie zmiany wprowadziłeś.
    - Zastanów się, czy nowe funkcje są zgodne wstecz (MINOR) lub jeśli są breaking changes (MAJOR), czy poprawiłeś tylko błędy (PATCH).

2. **Aktualizacja `composer.json`:**
    - Zaktualizuj wersję w polu `"version"`, np.:
        - Zmieniasz wersję z `0.1.0-alpha` na `0.1.1` po poprawkach błędów.
        - Zmieniasz wersję na `0.2.0`, gdy dodajesz nowe funkcjonalności.

3. **Commit i tagowanie:**
    - Upewnij się, że wszystkie zmiany są commitowane:
      ```bash
      git add .
      git commit -m "Wersja 0.2.0 - dodano nowe funkcjonalności"
      ```
    - Utwórz tag:
      ```bash
      git tag -a v0.2.0 -m "Wersja 0.2.0"
      ```
    - Wypchnij zmiany i tag:
      ```bash
      git push origin v0.2.0
      ```

### Dobre praktyki:
- **Dokumentacja zmian**: Używaj pliku `CHANGELOG.md` do dokumentowania najważniejszych zmian w każdej wersji.
- **Automatyzacja**: Możesz skorzystać z narzędzi automatyzujących tworzenie wydań, np. GitHub Actions, aby automatycznie tworzyć wersje i tagi po zrobieniu commitu na główną gałąź.

Wersjonowanie pakietu z użyciem SemVer i Git tagów ułatwia śledzenie zmian i kompatybilności, co jest kluczowe w projektach zewnętrznych.
