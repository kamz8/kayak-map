# PatternCard Component

Nowoczesny komponent karty z wzorem diagonalnym inspirowany design systemem Tailwind UI. Komponent dziedziczy po `v-sheet` z Vuetify i automatycznie dostosowuje się do aktualnego theme (dark/light).

## Funkcje

- ✅ Automatyczne dostosowanie do theme (dark/light)
- ✅ Różne warianty kolorystyczne
- ✅ Wzór diagonalny SVG
- ✅ Obsługa kliknięć
- ✅ Różne pozycje contentu
- ✅ Integracja z Vuetify
- ✅ Hover effects
- ✅ Elevation opcjonalna

## Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `theme` | String | `'dark'` | Theme komponentu (dark/light) |
| `color` | String | `null` | Kolor tła (overrides variant) |
| `rounded` | String/Number/Boolean | `'xl'` | Border radius |
| `elevation` | String/Number | `0` | Material elevation |
| `variant` | String | `'default'` | Wariant kolorystyczny |
| `patternStrokeWidth` | String/Number | `0.5` | Grubość linii wzoru |
| `clickable` | Boolean | `false` | Czy karta jest klikalna |
| `elevated` | Boolean | `false` | Czy dodać shadow |
| `contentPosition` | String | `'center'` | Pozycja contentu |
| `minHeight` | String/Number | `'200px'` | Minimalna wysokość |

## Warianty

- `default` - Standardowy wygląd
- `primary` - Niebieski akcent
- `secondary` - Szary akcent  
- `success` - Zielony akcent
- `warning` - Pomarańczowy akcent
- `error` - Czerwony akcent

## Pozycje contentu

- `center` - Wyśrodkowany
- `top` - Na górze
- `bottom` - Na dole
- `left` - Po lewej
- `right` - Po prawej

## Events

| Event | Description |
|-------|-------------|
| `click` | Emitowany gdy `clickable=true` i karta zostanie kliknięta |

## Przykłady użycia

### Podstawowe użycie

```vue
<PatternCard>
  <h3>Tytuł karty</h3>
  <p>Treść karty</p>
</PatternCard>
```

### Karta statystyk

```vue
<PatternCard variant="primary" content-position="top">
  <div class="d-flex justify-space-between align-center mb-2">
    <h3>Szlaki</h3>
    <v-icon>mdi-map-marker-path</v-icon>
  </div>
  <div class="stat-number">187</div>
  <div class="stat-subtitle">Aktywne szlaki</div>
</PatternCard>
```

### Klikalna karta z akcjami

```vue
<PatternCard 
  variant="default" 
  clickable 
  elevated
  @click="handleClick"
>
  <h3>Szybkie akcje</h3>
  <div class="action-buttons">
    <v-btn @click.stop="action1">Akcja 1</v-btn>
    <v-btn @click.stop="action2">Akcja 2</v-btn>
  </div>
</PatternCard>
```

### Custom kolor i rozmiar

```vue
<PatternCard 
  color="#2c3e50"
  min-height="300px"
  pattern-stroke-width="1"
  rounded="lg"
>
  <div>Custom styled card</div>
</PatternCard>
```

### Theme-aware

```vue
<!-- Automatycznie dostosowuje się do theme -->
<PatternCard :theme="$vuetify.theme.current.dark ? 'dark' : 'light'">
  <div>Responsive to theme</div>
</PatternCard>
```

## Styling

Komponent automatycznie dostosowuje kolory do theme:

**Dark Theme:**
- Tło: ciemne odcienie
- Wzór: białe linie z opacity
- Border: subtelne obramowania

**Light Theme:**  
- Tło: jasne odcienie
- Wzór: czarne linie z opacity
- Border: delikatne obramowania

## Integracja z Vuetify

PatternCard dziedziczy po `v-sheet` i przyjmuje wszystkie jego props:

```vue
<PatternCard
  v-bind="$attrs"
  max-width="400"
  class="mx-auto"
  tile
>
  <div>Vuetify compatible</div>
</PatternCard>
```