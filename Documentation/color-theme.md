# Dokumentacja Kolorów dla Aplikacji Kajakowej

## Wprowadzenie

Ta dokumentacja zawiera szczegółowy opis kolorów używanych w aplikacji kajakowej. Kolory te zostały dobrane w taki sposób, aby odzwierciedlały elementy związane z kajakowaniem, takie jak rzeki, niebo, słońce i piękna natura.

## Paleta Kolorów

### Kolor nieba

- **Jasny błękit**:
    - Hex: `#87CEEB`
    - Nazwa: Sky Blue
    - Kolor: ![#87CEEB](https://via.placeholder.com/15/87CEEB/000000?text=+)

### Kolor rzeki

- **Niebieski**:
    - Hex: `#4682B4`
    - Nazwa: Steel Blue
    - Kolor: ![#4682B4](https://via.placeholder.com/15/4682B4/000000?text=+)
- **Głęboki niebieski**:
    - Hex: `#1E90FF`
    - Nazwa: Dodger Blue
    - Kolor: ![#1E90FF](https://via.placeholder.com/15/1E90FF/000000?text=+)

### Kolor słońca

- **Żółty**:
    - Hex: `#FFD700`
    - Nazwa: Gold
    - Kolor: ![#FFD700](https://via.placeholder.com/15/FFD700/000000?text=+)
- **Pomarańczowy**:
    - Hex: `#FFA500`
    - Nazwa: Orange
    - Kolor: ![#FFA500](https://via.placeholder.com/15/FFA500/000000?text=+)

### Kolor natury

- **Jasna zieleń**:
    - Hex: `#32CD32`
    - Nazwa: Lime Green
    - Kolor: ![#32CD32](https://via.placeholder.com/15/32CD32/000000?text=+)
- **Ciemna zieleń**:
    - Hex: `#006400`
    - Nazwa: Dark Green
    - Kolor: ![#006400](https://via.placeholder.com/15/006400/000000?text=+)

### Kolor kajaka

- **Czerwony**:
    - Hex: `#FF0000`
    - Nazwa: Strong Red
    - Kolor: ![#FF0000](https://via.placeholder.com/15/FF0000/000000?text=+)
- **Żółty**:
    - Hex: `#FFD700`
    - Nazwa: Gold
    - Kolor: ![#FFD700](https://via.placeholder.com/15/FFD700/000000?text=+)
- **Pomarańczowy**:
    - Hex: `#FFA500`
    - Nazwa: Orange
    - Kolor: ![#FFA500](https://via.placeholder.com/15/FFA500/000000?text=+)

### Kolor piasku

- **Piaskowy**:
    - Hex: `#F4A460`
    - Nazwa: Sandy Brown
    - Kolor: ![#F4A460](https://via.placeholder.com/15/F4A460/000000?text=+)
- **Beżowy**:
    - Hex: `#F5DEB3`
    - Nazwa: Wheat
    - Kolor: ![#F5DEB3](https://via.placeholder.com/15/F5DEB3/000000?text=+)

## Przykładowe użycie kolorów

### Użycie kolorów w stylach CSS

```css
:root {
  --sky-blue: #87CEEB;
  --steel-blue: #4682B4;
  --dodger-blue: #1E90FF;
  --gold: #FFD700;
  --orange: #FFA500;
  --lime-green: #32CD32;
  --dark-green: #006400;
  --strong-red: #FF0000;
  --sandy-brown: #F4A460;
  --wheat: #F5DEB3;
}

body {
  background-color: var(--sky-blue); /* Tło w kolorze nieba */
}

.header {
  background-color: var(--dodger-blue); /* Tło nagłówka w kolorze głębokiego niebieskiego */
}

.footer {
  background-color: var(--steel-blue); /* Tło stopki w kolorze niebieskim */
}

.button {
  background-color: var(--strong-red); /* Przycisk w mocnym czerwonym kolorze */
  color: white;
}

.button:hover {
  background-color: var(--dark-green); /* Kolor przycisku przy najechaniu myszką */
}

.card {
  background-color: var(--wheat); /* Tło kart w kolorze piasku */
  border: 1px solid var(--sandy-brown); /* Obramowanie kart w kolorze piaskowym */
}
