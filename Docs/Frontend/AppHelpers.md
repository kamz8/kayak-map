# Helpery Aplikacji

## Opis
Plik helperów zawiera metody pomocnicze, które mogą być używane w całej aplikacji. Te metody są rejestrowane globalnie i mogą być wywoływane przez `this.nazwa`.

## Instalacja

```javascript
import { useStore } from 'vuex';
import { useMoment } from 'vue-moment';

export default {
  install(app) {
    app.config.globalProperties.$alert = function ({ type, text }) {
      const store = useStore();
      store.dispatch('system_messages/addMessage', { type, text });
    };
    app.config.globalProperties.$alertInfo = function (text) {
      const store = useStore();
      const messageType = store.getters['system_messages/messageTypes'].INFO;
      this.$alert({ type: messageType, text });
    };
    app.config.globalProperties.$alertWarning = function (text) {
      const store = useStore();
      const messageType = store.getters['system_messages/messageTypes'].WARNING;
      this.$alert({ type: messageType, text });
    };
    app.config.globalProperties.$alertError = function (text) {
      const store = useStore();
      const messageType = store.getters['system_messages/messageTypes'].ERROR;
      this.$alert({ type: messageType, text });
    };
    app.config.globalProperties.$logMessage = function (message) {
      console.log(message);
    };
    app.config.globalProperties.$formatDate = function (date) {
      return this.$moment(date).format('LL'); // Użyj polskiego formatu daty
    };
  }
};
```

## Obecne Helpery

| Helper        | Opis                            | Parametry           | Przykład użycia                               |
|---------------|---------------------------------|---------------------|----------------------------------------------|
| `$alert`      | Wyświetla komunikat systemowy   | `type` (String): Typ wiadomości (`INFO`, `WARNING`, `ERROR`)<br>`text` (String): Treść wiadomości | ```javascript<br>this.$alert({ type: 'INFO', text: 'To jest informacja.' });<br>``` |
| `$alertInfo`  | Wyświetla komunikat informacyjny| `text` (String): Treść wiadomości | ```javascript<br>this.$alertInfo('To jest informacja.');<br>``` |
| `$alertWarning`| Wyświetla komunikat ostrzegawczy| `text` (String): Treść wiadomości | ```javascript<br>this.$alertWarning('To jest ostrzeżenie.');<br>``` |
| `$alertError` | Wyświetla komunikat o błędzie   | `text` (String): Treść wiadomości | ```javascript<br>this.$alertError('To jest błąd.');<br>``` |
| `$logMessage` | Loguje wiadomość do konsoli     | `message` (String): Wiadomość do zalogowania | ```javascript<br>this.$logMessage('To jest logowana wiadomość.');<br>``` |
| `$formatDate` | Formatuje datę                  | `date` (Date): Data do sformatowania | ```javascript<br>this.$formatDate(new Date());<br>``` |

Przykład użycia:

```javascript

this.$alertError('To jest błąd.');

$logMessage
```

Loguje wiadomość do konsoli.

Parametry:

    message (String): Wiadomość do zalogowania.

Przykład użycia:

javascript

this.$logMessage('To jest logowana wiadomość.');

$formatDate

Formatuje datę.

Parametry:

    date (Date): Data do sformatowania.

Przykład użycia:

javascript

this.$formatDate(new Date());

Tworzenie Nowego Helpera

Aby dodać nowy helper do pliku, postępuj zgodnie z poniższymi krokami:

    Dodaj funkcję helpera do sekcji install:
    Dodaj nową funkcję do obiektu app.config.globalProperties.

    Przykład:

    javascript

app.config.globalProperties.$newHelper = function (param) {
  // Implementacja helpera
};

Dodaj opis nowego helpera w dokumentacji:
Opisz nową funkcję, jej parametry oraz przykłady użycia.

Przykład:

markdown

### $newHelper
Opis nowego helpera.

**Parametry:**
- `param` (Typ): Opis parametru.

**Przykład użycia:**
```javascript
this.$newHelper(param);

Zaktualizuj instalację:
Upewnij się, że nowa funkcja jest dodana do instalacji pluginu.

Przykład:

javascript

    app.config.globalProperties.$newHelper = function (param) {
      // Implementacja helpera
    };

Przykład Dodania Nowego Helpera

Załóżmy, że chcemy dodać helper, który zwraca bieżący rok:

    Dodaj funkcję do sekcji install:

    javascript

app.config.globalProperties.$currentYear = function () {
  return new Date().getFullYear();
};

Dodaj opis nowego helpera w dokumentacji:

markdown

### $currentYear
Zwraca bieżący rok.

**Przykład użycia:**
```javascript
this.$currentYear(); // Zwróci np. 2024

Zaktualizuj instalację:

javascript

app.config.globalProperties.$currentYear = function () {
  return new Date().getFullYear();
};

