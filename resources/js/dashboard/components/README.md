# Dashboard Components - Global UI Kit

## 📋 Przegląd

**Globalne komponenty UI** dla całego Dashboard. To nie są moduły funkcjonalne, ale **reusable components** używane przez wszystkie moduły.

## 🎨 UI Kit Structure

```
resources/js/dashboard/components/
└── ui/                           # Global UI Components
    ├── DataTable.vue            # Tabela z akcjami CRUD
    ├── FormField.vue            # Pole formularza z walidacją
    ├── StatsCard.vue            # Karta statystyk
    ├── ConfirmDialog.vue        # Dialog potwierdzenia
    └── index.js                 # Export wszystkich komponentów
```

## 🔧 Komponenty

### DataTable
**Uniwersalna tabela z akcjami**
```vue
<DataTable
  title="Lista szlaków"
  :headers="headers"
  :items="trails"
  :actions="{ view: true, edit: true, delete: true }"
  @edit="editTrail"
  @delete="deleteTrail"
/>
```

### FormField  
**Pole formularza z walidacją**
```vue
<FormField
  v-model="form.email"
  type="email"
  label="Adres email"
  required
  :rules="emailRules"
/>
```

### StatsCard
**Karta statystyk dashboard**
```vue
<StatsCard
  title="Szlaki"
  :value="125"
  icon="mdi-map"
  color="primary"
/>
```

### ConfirmDialog
**Dialog potwierdzenia akcji**
```vue
<ConfirmDialog
  v-model="showDialog"
  title="Usuń szlak"
  message="Czy na pewno chcesz usunąć ten szlak?"
  @confirm="deleteTrail"
/>
```

## 📦 Import

### Pojedyncze komponenty:
```javascript
import { DataTable } from '@ui'
import { FormField, StatsCard } from '@ui'
```

### Wszystkie komponenty:
```javascript
import * as UI from '@ui'
// UI.DataTable, UI.FormField, etc.
```

## 🎯 Zasady

### ✅ Co to SĄ globalne komponenty:
- **Reusable UI elements** używane w wielu modułach
- **Vuetify-based** komponenty z jednolitym stylingiem
- **Generic functionality** (tabele, formularze, dialogi)
- **Cross-module** - używane przez auth, trails, users, etc.

### ❌ Co to NIE SĄ globalne komponenty:
- **Moduły funkcjonalne** (auth, trails, users)
- **Business logic** specifyczne dla konkretnej funkcjonalności
- **Page components** (LoginView, TrailsList)
- **Module-specific** komponenty

## 🔗 Integracja z Modułami

```javascript
// W module auth
import { FormField } from '@ui'

// W module trails  
import { DataTable, ConfirmDialog } from '@ui'

// W module users
import { DataTable, FormField, StatsCard } from '@ui'
```

## 🚀 Dodawanie Nowych Komponentów

### 1. Utwórz komponent:
```bash
# W components/ui/
touch NewComponent.vue
```

### 2. Zaimplementuj komponent:
```vue
<!-- NewComponent.vue -->
<template>
  <!-- Vuetify-based template -->
</template>

<script>
export default {
  name: 'NewComponent',
  // component logic
}
</script>
```

### 3. Dodaj do index.js:
```javascript
// components/ui/index.js
export { default as NewComponent } from './NewComponent.vue'
```

### 4. Używaj w modułach:
```javascript
import { NewComponent } from '@ui'
```

---

**Global UI Kit v1.0**  
*Reusable Vuetify Components*  
*Cross-Module Design System*