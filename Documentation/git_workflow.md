# Standard Pracy z Git dla naszego projektu backendowego i vue.js

## Główne Gałęzie
- **main** lub **master**: Gałąź zawierająca stabilny kod gotowy do produkcji.
- **dev**: Gałąź główna rozwoju, gdzie łączy się wszystkie zmiany przed przeniesieniem ich na `main`.

## Gałęzie Funkcjonalności (Feature Branches)
- Tworzone z `develop`.
- Nazwa: `feature/<nazwa-funkcjonalności>`.
- Przykład: `feature/login-system`.

## Gałęzie Poprawy Błędów (Bugfix Branches)
- Tworzone z `develop` lub `main`.
- Nazwa: `bugfix/<nazwa-poprawy>`.
- Przykład: `bugfix/fix-login-bug`.

## Gałęzie Przygotowania do Wersji (Release Branches)
- Tworzone z `develop`.
- Nazwa: `release/<wersja>`.
- Przykład: `release/1.0.0`.

## Gałęzie Naprawy Awarii (Hotfix Branches)
- Tworzone z `main` w przypadku pilnej naprawy błędu na produkcji.
- Nazwa: `hotfix/<nazwa-poprawy>`.
- Przykład: `hotfix/urgent-fix`.

## Workflow

### Tworzenie Nowej Funkcji
1. Przejdź na gałąź `develop`:
    ```bash
    git checkout develop
    git pull
    ```
2. Utwórz nową gałąź funkcjonalności:
    ```bash
    git checkout -b feature/<nazwa-funkcjonalności>
    ```
3. Pracuj nad funkcjonalnością, wykonuj commity i push:
    ```bash
    git add .
    git commit -m "Dodaj nową funkcjonalność"
    git push origin feature/<nazwa-funkcjonalności>
    ```
4. Otwórz pull request do `develop`.

### Integracja Zmian
- Regularnie aktualizuj swoją gałąź funkcjonalności:
    ```bash
    git fetch
    git merge develop
    ```

### Przygotowanie Nowej Wersji
1. Utwórz gałąź `release` z `develop`:
    ```bash
    git checkout develop
    git pull
    git checkout -b release/<wersja>
    ```
2. Dokonaj ostatecznych testów i poprawek.
3. Scal gałąź `release` do `main` i `develop`:
    ```bash
    git checkout main
    git merge release/<wersja>
    git tag -a v<wersja> -m "Wydanie <wersja>"
    git push origin main --tags
    git checkout develop
    git merge release/<wersja>
    ```

### Poprawa Błędów
1. Utwórz gałąź `bugfix` z `develop` lub `hotfix` z `main`:
    ```bash
    git checkout develop
    git pull
    git checkout -b bugfix/<nazwa-poprawy>
    ```
2. Pracuj nad poprawą, wykonuj commity i push.
3. Otwórz pull request do `develop` lub `main`.

### Deployment
- Zmiany w `main` gotowe do wdrożenia na produkcję.

## Narzędzia Wspierające
- **Continuous Integration (CI):** Używaj narzędzi CI (np. GitHub Actions, GitLab CI) do automatycznego testowania kodu przy każdym pushu i pull request.
- **Code Review:** Przeprowadzaj przeglądy kodu dla każdego pull request.
- **Lintery i Testy:** Używaj linterów (np. ESLint dla Vue.js, PHP_CodeSniffer dla Laravel) oraz testów jednostkowych i integracyjnych.

## Przykładowy Workflow

### Tworzenie Nowej Funkcji
```bash
git checkout develop
git checkout -b feature/new-feature
# Praca nad funkcją
git add .
git commit -m "Add new feature"
git push origin feature/new-feature
# Otworzenie pull request do develop
