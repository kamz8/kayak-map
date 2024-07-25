# System Wiadomości Systemowych

## Opis
System wiadomości systemowych pozwala na globalne zarządzanie komunikatami aplikacji. Umożliwia wyświetlanie komunikatów informacyjnych, ostrzegawczych oraz błędów.

### Struktura Modułu Vuex

#### modules/system-messages/store/index.js

```javascript
const state = {
    messages: [],
    messageTypes: {
        INFO: 'info',
        WARNING: 'warning',
        ERROR: 'error',
    }
};

const mutations = {
    ADD_MESSAGE(state, message) {
        state.messages.push(message);
    },
    REMOVE_MESSAGE(state, index) {
        state.messages.splice(index, 1);
    }
};

const actions = {
    addMessage({ commit }, message) {
        commit('ADD_MESSAGE', message);
        setTimeout(() => {
            commit('REMOVE_MESSAGE', state.messages.indexOf(message));
        }, 3000); // Usuwa po 3 sekundach
    }
};

const getters = {
    messages: (state) => state.messages,
    messageTypes: (state) => state.messageTypes,
};

export default {
    namespaced: true,
    state,
    mutations,
    actions,
    getters,
};
``` 
# Jak Korzystać z Modułu Komunikatów Systemowych w Kodzie

Aby używać systemu wiadomości systemowych w komponentach Vue, możesz wywoływać zdefiniowane metody helperów.
Aby uzyskać więcej informacji na temat używania helperów, zapoznaj się z [dokumentacją helperów](../AppHelpers.md).

## Przykład Komponentu

```vue
<template>
  <div>
    <v-btn @click="showInfoMessage">Pokaż Wiadomość Informacyjną</v-btn>
    <v-btn @click="showWarningMessage">Pokaż Ostrzeżenie</v-btn>
    <v-btn @click="showErrorMessage">Pokaż Błąd</v-btn>
    <v-alert v-for="(message, index) in messages" :key="index" :type="message.type">
      {{ message.text }}
    </v-alert>
  </div>
</template>

<script>
import { mapGetters } from 'vuex';

export default {
  computed: {
    ...mapGetters('system_messages', ['messages']),
  },
  methods: {
    showInfoMessage() {
      this.$alertInfo('To jest wiadomość informacyjna.');
    },
    showWarningMessage() {
      this.$alertWarning('To jest ostrzeżenie.');
    },
    showErrorMessage() {
      this.$alertError('To jest błąd.');
    },
  },
};
</script>
```


