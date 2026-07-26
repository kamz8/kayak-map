# 🎨 **DESIGN PLAN - Dashboard Trails Table UI/UX**

> **Projekt:** Kayak Map Dashboard
> **Komponent:** `/dashboard/trails` - Lista szlaków kajakowych
> **Data:** 2025-11-17
> **Status:** 📋 Planning Phase

---

## 📊 **ANALIZA OBECNEGO STANU**

### ✅ **Co już działa dobrze**

**Komponenty UI:**
- ✅ Używa nowoczesnego `UiDataTable` (shadcn-vue style)
- ✅ Integracja z komponentami UI: `UiButton`, `UiBadge`
- ✅ Poprawne formatowanie:
  - Difficulty badges z color coding
  - Length formatowanie (km)
  - Rating z gwiazdkami (v-rating)
- ✅ Podstawowe akcje: View, Edit, Delete
- ✅ Wbudowane wyszukiwanie (search in UiDataTable)

**Struktura danych:**
```javascript
headers: [
  { title: 'Nazwa szlaku', key: 'trail_name', sortable: true },
  { title: 'Rzeka', key: 'river_name', sortable: true },
  { title: 'Długość', key: 'trail_length', sortable: true, align: 'end' },
  { title: 'Trudność', key: 'difficulty', sortable: true },
  { title: 'Ocena', key: 'rating', sortable: true, align: 'center' },
  { title: 'Autor', key: 'author', sortable: true }
]
```

### ❌ **Problemy do rozwiązania**

1. **🔴 KRYTYCZNE:**
   - Używa mock danych zamiast API (`TrailsList.vue:159, 229`)
   - Natywny `confirm()` zamiast `ConfirmDialog` (`TrailsList.vue:221`)
   - Brak eksportu danych (`TrailsList.vue:273`)

2. **🟡 ŚREDNIE:**
   - Brak zaawansowanych filtrów w UI (istnieją w data(), ale nie renderowane)
   - Brak bulk operations (multi-select)
   - Brak statusu szlaku (aktywny/draft/archived)
   - Brak refresh button

3. **🟢 NISKIE:**
   - Brak tooltips dla długich nazw
   - Brak paginacji server-side
   - Brak loading skeletons

---

## 🎯 **PLAN ULEPSZEŃ UI/UX**

### **1. Zaawansowane Filtry**

**Lokalizacja:** Slot `#filters` w `UiDataTable`

**Komponenty filtrów:**

```vue
<template #filters>
  <div class="trails-filters">
    <!-- Difficulty filter -->
    <v-select
      v-model="filters.difficulty"
      :items="difficultyOptions"
      label="Trudność"
      variant="outlined"
      density="compact"
      clearable
      hide-details
      class="filter-select"
      style="max-width: 180px"
    >
      <template #prepend-inner>
        <v-icon size="small">mdi-hiking</v-icon>
      </template>
    </v-select>

    <!-- Rating filter -->
    <v-select
      v-model="filters.minRating"
      :items="ratingOptions"
      label="Min. ocena"
      variant="outlined"
      density="compact"
      clearable
      hide-details
      class="filter-select"
      style="max-width: 140px"
    >
      <template #prepend-inner>
        <v-icon size="small">mdi-star</v-icon>
      </template>
    </v-select>

    <!-- River filter -->
    <v-autocomplete
      v-model="filters.river"
      :items="uniqueRivers"
      label="Rzeka"
      variant="outlined"
      density="compact"
      clearable
      hide-details
      class="filter-select"
      style="max-width: 200px"
    >
      <template #prepend-inner>
        <v-icon size="small">mdi-waves</v-icon>
      </template>
    </v-autocomplete>

    <!-- Status filter -->
    <v-select
      v-model="filters.status"
      :items="statusOptions"
      label="Status"
      variant="outlined"
      density="compact"
      clearable
      hide-details
      class="filter-select"
      style="max-width: 140px"
    >
      <template #prepend-inner>
        <v-icon size="small">mdi-check-circle</v-icon>
      </template>
    </v-select>

    <!-- Clear filters button -->
    <UiButton
      v-if="hasActiveFilters"
      variant="ghost"
      size="sm"
      @click="clearFilters"
    >
      <v-icon start size="small">mdi-filter-remove</v-icon>
      Wyczyść
    </UiButton>
  </div>
</template>
```

**Style dla filtrów:**
```css
.trails-filters {
  display: flex;
  gap: 12px;
  align-items: center;
  flex-wrap: wrap;
}

.filter-select :deep(.v-field) {
  font-size: 13px;
}
```

**Opcje filtrów:**
```javascript
computed: {
  difficultyOptions() {
    return [
      { value: 'łatwy', title: '🟢 Łatwy' },
      { value: 'umiarkowany', title: '🟡 Umiarkowany' },
      { value: 'trudny', title: '🔴 Trudny' },
      { value: 'ekspertowy', title: '⚫ Ekspertowy' }
    ]
  },

  ratingOptions() {
    return [
      { value: 4.5, title: '★ 4.5+' },
      { value: 4.0, title: '★ 4.0+' },
      { value: 3.5, title: '★ 3.5+' },
      { value: 3.0, title: '★ 3.0+' }
    ]
  },

  statusOptions() {
    return [
      { value: 'active', title: 'Aktywny' },
      { value: 'draft', title: 'Szkic' },
      { value: 'archived', title: 'Archiwum' }
    ]
  },

  uniqueRivers() {
    return [...new Set(this.trails.map(t => t.river_name))].sort()
  },

  hasActiveFilters() {
    return Object.values(this.filters).some(v => v !== null)
  }
}
```

---

### **2. Header Actions Enhancement**

**Dodaj do slotu `#actions`:**

```vue
<template #actions>
  <div class="trails-header-actions">
    <!-- Bulk actions (when items selected) -->
    <div v-if="hasSelectedTrails" class="bulk-actions">
      <span class="selected-count">
        {{ selectedTrails.length }} zaznaczono
      </span>

      <UiButton
        variant="ghost"
        size="sm"
        @click="bulkExport"
      >
        <v-icon start size="small">mdi-download</v-icon>
        Eksportuj
      </UiButton>

      <UiButton
        variant="ghost"
        size="sm"
        color="error"
        @click="bulkDelete"
      >
        <v-icon start size="small">mdi-delete</v-icon>
        Usuń
      </UiButton>
    </div>

    <!-- Standard actions -->
    <div v-else class="standard-actions">
      <!-- Refresh button -->
      <UiButton
        variant="ghost"
        size="icon"
        @click="refreshTrails"
        :loading="loading"
      >
        <v-icon>mdi-refresh</v-icon>
      </UiButton>

      <!-- Export dropdown -->
      <v-menu offset-y>
        <template #activator="{ props }">
          <UiButton
            v-bind="props"
            variant="outline"
            size="sm"
          >
            <v-icon start size="small">mdi-download</v-icon>
            Eksportuj
            <v-icon end size="small">mdi-chevron-down</v-icon>
          </UiButton>
        </template>

        <v-list density="compact">
          <v-list-item @click="exportTrails('csv')">
            <template #prepend>
              <v-icon size="small">mdi-file-delimited</v-icon>
            </template>
            <v-list-item-title>Eksportuj CSV</v-list-item-title>
          </v-list-item>

          <v-list-item @click="exportTrails('json')">
            <template #prepend>
              <v-icon size="small">mdi-code-json</v-icon>
            </template>
            <v-list-item-title>Eksportuj JSON</v-list-item-title>
          </v-list-item>

          <v-list-item @click="exportTrails('gpx')">
            <template #prepend>
              <v-icon size="small">mdi-map-marker-path</v-icon>
            </template>
            <v-list-item-title>Eksportuj GPX</v-list-item-title>
          </v-list-item>
        </v-list>
      </v-menu>

      <!-- Import GPX button -->
      <UiButton
        variant="outline"
        size="sm"
        @click="openImportDialog"
      >
        <v-icon start size="small">mdi-upload</v-icon>
        Import GPX
      </UiButton>

      <!-- Add trail button -->
      <UiButton
        variant="default"
        size="sm"
        @click="$router.push('/dashboard/trails/create')"
      >
        <v-icon start size="small">mdi-plus</v-icon>
        Dodaj szlak
      </UiButton>
    </div>
  </div>
</template>
```

**Style:**
```css
.trails-header-actions {
  display: flex;
  gap: 8px;
  align-items: center;
}

.bulk-actions,
.standard-actions {
  display: flex;
  gap: 8px;
  align-items: center;
}

.selected-count {
  font-size: 13px;
  font-weight: 500;
  color: hsl(var(--v-theme-primary));
  padding: 0 8px;
}
```

---

### **3. Enhanced Table Columns**

**Dodaj kolumnę Status:**

```javascript
headers: [
  // ... existing headers
  {
    title: 'Status',
    key: 'status',
    sortable: true,
    width: '120px'
  }
]
```

**Custom slot dla statusu:**

```vue
<template #item.status="{ value }">
  <UiBadge :variant="getStatusVariant(value)">
    <v-icon start size="x-small">{{ getStatusIcon(value) }}</v-icon>
    {{ getStatusLabel(value) }}
  </UiBadge>
</template>
```

**Helper methods:**
```javascript
methods: {
  getStatusVariant(status) {
    const variants = {
      active: 'success',
      draft: 'secondary',
      archived: 'outline'
    }
    return variants[status] || 'secondary'
  },

  getStatusIcon(status) {
    const icons = {
      active: 'mdi-check-circle',
      draft: 'mdi-pencil-circle',
      archived: 'mdi-archive'
    }
    return icons[status] || 'mdi-circle-outline'
  },

  getStatusLabel(status) {
    const labels = {
      active: 'Aktywny',
      draft: 'Szkic',
      archived: 'Archiwum'
    }
    return labels[status] || status
  }
}
```

**Dodaj tooltips dla długich nazw:**

```vue
<template #item.trail_name="{ value, item }">
  <v-tooltip location="top" :disabled="value.length < 30">
    <template #activator="{ props }">
      <span v-bind="props" class="trail-name">
        {{ value }}
      </span>
    </template>
    {{ value }}
  </v-tooltip>
</template>

<template #item.river_name="{ value }">
  <v-tooltip location="top" :disabled="value.length < 20">
    <template #activator="{ props }">
      <span v-bind="props" class="river-name">
        {{ value }}
      </span>
    </template>
    {{ value }}
  </v-tooltip>
</template>
```

**Style dla nazw:**
```css
.trail-name,
.river-name {
  display: inline-block;
  max-width: 200px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.trail-name {
  font-weight: 500;
  color: hsl(var(--v-theme-primary));
  cursor: pointer;
}

.trail-name:hover {
  text-decoration: underline;
}
```

---

### **4. ConfirmDialog Integration**

**Zastąp natywny confirm():**

**W data():**
```javascript
data() {
  return {
    // ... existing data
    deleteDialog: {
      show: false,
      trail: null,
      loading: false
    }
  }
}
```

**Component w template:**
```vue
<ConfirmDialog
  v-model="deleteDialog.show"
  title="Usunąć szlak?"
  :message="`Czy na pewno chcesz usunąć szlak &quot;${deleteDialog.trail?.trail_name}&quot;?`"
  details="Ta operacja jest nieodwracalna. Wszystkie dane szlaku zostaną trwale usunięte."
  icon="mdi-delete-alert"
  icon-color="error"
  confirm-text="Usuń szlak"
  cancel-text="Anuluj"
  confirm-color="error"
  :loading="deleteDialog.loading"
  @confirm="executeDelete"
  @cancel="deleteDialog.show = false"
/>
```

**Methods:**
```javascript
methods: {
  confirmDeleteTrail(trail) {
    this.deleteDialog.trail = trail
    this.deleteDialog.show = true
  },

  async executeDelete() {
    this.deleteDialog.loading = true

    try {
      const response = await this.$http.delete(
        `/api/v1/dashboard/trails/${this.deleteDialog.trail.id}`
      )

      // Remove from local state
      this.trails = this.trails.filter(
        t => t.id !== this.deleteDialog.trail.id
      )

      this.showSuccess(
        `Szlak "${this.deleteDialog.trail.trail_name}" został usunięty`
      )

      this.deleteDialog.show = false
    } catch (error) {
      console.error('Failed to delete trail:', error)
      this.showError('Nie udało się usunąć szlaku')
    } finally {
      this.deleteDialog.loading = false
      this.deleteDialog.trail = null
    }
  }
}
```

---

### **5. Export Functionality**

**Export methods:**

```javascript
methods: {
  async exportTrails(format = 'csv') {
    try {
      this.showInfo(`Przygotowywanie eksportu ${format.toUpperCase()}...`)

      const dataToExport = this.hasSelectedTrails
        ? this.selectedTrails
        : this.filteredTrails

      switch (format) {
        case 'csv':
          this.exportCSV(dataToExport)
          break
        case 'json':
          this.exportJSON(dataToExport)
          break
        case 'gpx':
          await this.exportGPX(dataToExport)
          break
        default:
          this.showError('Nieznany format eksportu')
      }

      this.showSuccess(`Eksport ${format.toUpperCase()} zakończony`)
    } catch (error) {
      console.error('Export failed:', error)
      this.showError('Nie udało się wyeksportować danych')
    }
  },

  exportCSV(trails) {
    const headers = [
      'ID',
      'Nazwa szlaku',
      'Rzeka',
      'Długość (km)',
      'Trudność',
      'Ocena',
      'Autor',
      'Status',
      'Data utworzenia'
    ]

    const rows = trails.map(trail => [
      trail.id,
      `"${trail.trail_name}"`,
      `"${trail.river_name}"`,
      trail.trail_length,
      trail.difficulty,
      trail.rating,
      `"${trail.author}"`,
      trail.status,
      trail.created_at
    ])

    const csv = [
      headers.join(','),
      ...rows.map(row => row.join(','))
    ].join('\n')

    this.downloadFile(csv, 'trails-export.csv', 'text/csv')
  },

  exportJSON(trails) {
    const json = JSON.stringify(trails, null, 2)
    this.downloadFile(json, 'trails-export.json', 'application/json')
  },

  async exportGPX(trails) {
    // API call to generate GPX file
    const response = await this.$http.post('/api/v1/dashboard/trails/export/gpx', {
      trail_ids: trails.map(t => t.id)
    }, {
      responseType: 'blob'
    })

    const blob = new Blob([response.data], { type: 'application/gpx+xml' })
    const url = window.URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = url
    link.download = `trails-export-${Date.now()}.gpx`
    link.click()
    window.URL.revokeObjectURL(url)
  },

  downloadFile(content, filename, mimeType) {
    const blob = new Blob([content], { type: mimeType })
    const url = window.URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = url
    link.download = filename
    link.click()
    window.URL.revokeObjectURL(url)
  },

  // Bulk export
  bulkExport() {
    if (!this.hasSelectedTrails) return
    this.exportTrails('csv')
  }
}
```

---

### **6. Multi-Select (Bulk Operations)**

**Enable selection in UiDataTable:**

```vue
<UiDataTable
  v-model:selected="selectedTrails"
  title="Lista szlaków"
  :headers="headers"
  :items="filteredTrails"
  :loading="loading"
  :actions="{ view: true, edit: true, delete: true }"
  show-select
  @view="viewTrail"
  @edit="editTrail"
  @delete="confirmDeleteTrail"
>
```

**Bulk delete:**

```javascript
data() {
  return {
    selectedTrails: [],
    bulkDeleteDialog: {
      show: false,
      loading: false
    }
  }
},

methods: {
  async bulkDelete() {
    if (!this.hasSelectedTrails) return

    this.bulkDeleteDialog.show = true
  },

  async executeBulkDelete() {
    this.bulkDeleteDialog.loading = true

    try {
      const trailIds = this.selectedTrails.map(t => t.id)

      await this.$http.delete('/api/v1/dashboard/trails/bulk-delete', {
        data: { trail_ids: trailIds }
      })

      // Remove from local state
      this.trails = this.trails.filter(
        t => !trailIds.includes(t.id)
      )

      this.showSuccess(
        `Usunięto ${trailIds.length} szlaków`
      )

      this.selectedTrails = []
      this.bulkDeleteDialog.show = false
    } catch (error) {
      console.error('Bulk delete failed:', error)
      this.showError('Nie udało się usunąć szlaków')
    } finally {
      this.bulkDeleteDialog.loading = false
    }
  }
}
```

**Bulk delete dialog:**
```vue
<ConfirmDialog
  v-model="bulkDeleteDialog.show"
  title="Usunąć zaznaczone szlaki?"
  :message="`Czy na pewno chcesz usunąć ${selectedTrails.length} zaznaczonych szlaków?`"
  details="Ta operacja jest nieodwracalna. Wszystkie dane zostaną trwale usunięte."
  icon="mdi-delete-alert"
  icon-color="error"
  confirm-text="Usuń wszystkie"
  :loading="bulkDeleteDialog.loading"
  @confirm="executeBulkDelete"
  @cancel="bulkDeleteDialog.show = false"
/>
```

---

## 🎨 **DESIGN TOKENS & THEME**

### **Color Coding System**

**Difficulty (Trudność):**
```javascript
const DIFFICULTY_CONFIG = {
  'łatwy': {
    variant: 'success',
    label: 'Łatwy',
    color: '#4caf50',
    icon: 'mdi-chevron-up'
  },
  'umiarkowany': {
    variant: 'warning',
    label: 'Umiarkowany',
    color: '#ff9800',
    icon: 'mdi-chevron-double-up'
  },
  'trudny': {
    variant: 'destructive',
    label: 'Trudny',
    color: '#f44336',
    icon: 'mdi-chevron-triple-up'
  },
  'ekspertowy': {
    variant: 'secondary',
    label: 'Ekspertowy',
    color: '#9e9e9e',
    icon: 'mdi-skull'
  }
}
```

**Status:**
```javascript
const STATUS_CONFIG = {
  active: {
    variant: 'success',
    label: 'Aktywny',
    icon: 'mdi-check-circle'
  },
  draft: {
    variant: 'secondary',
    label: 'Szkic',
    icon: 'mdi-pencil-circle'
  },
  archived: {
    variant: 'outline',
    label: 'Archiwum',
    icon: 'mdi-archive'
  }
}
```

**Rating Colors:**
```javascript
getRatingColor(rating) {
  if (rating >= 4.5) return 'success'
  if (rating >= 4.0) return 'primary'
  if (rating >= 3.5) return 'warning'
  return 'grey'
}
```

---

## 📱 **RESPONSIVE DESIGN**

### **Mobile Optimizations**

```css
@media (max-width: 960px) {
  .trails-filters {
    flex-direction: column;
    align-items: stretch;
  }

  .filter-select {
    max-width: 100% !important;
  }

  .trails-header-actions {
    flex-direction: column;
    align-items: stretch;
  }

  .standard-actions {
    flex-wrap: wrap;
  }
}

@media (max-width: 600px) {
  /* Hide less important columns on mobile */
  :deep(.v-data-table) {
    .hide-on-mobile {
      display: none !important;
    }
  }
}
```

**Headers adjustment:**
```javascript
headers: [
  { title: 'Nazwa szlaku', key: 'trail_name', sortable: true },
  { title: 'Rzeka', key: 'river_name', sortable: true, class: 'hide-on-mobile' },
  { title: 'Długość', key: 'trail_length', sortable: true, align: 'end' },
  { title: 'Trudność', key: 'difficulty', sortable: true },
  { title: 'Ocena', key: 'rating', sortable: true, align: 'center', class: 'hide-on-mobile' },
  { title: 'Autor', key: 'author', sortable: true, class: 'hide-on-mobile' },
  { title: 'Status', key: 'status', sortable: true }
]
```

---

## 🔌 **API INTEGRATION**

### **Endpoint Requirements**

**GET /api/v1/dashboard/trails**
```javascript
// Query params
{
  page: 1,
  per_page: 10,
  sort_by: 'created_at',
  sort_order: 'desc',
  difficulty: 'łatwy',
  min_rating: 4.0,
  river_name: 'Wisła',
  status: 'active',
  search: 'kraków'
}

// Response
{
  data: [
    {
      id: 1,
      trail_name: "Wisła - Kraków do Tynca",
      river_name: "Wisła",
      trail_length: 12.5,
      difficulty: "łatwy",
      rating: 4.2,
      author: "Jan Kowalski",
      status: "active",
      created_at: "2024-01-15T10:30:00Z",
      updated_at: "2024-01-15T10:30:00Z"
    }
  ],
  meta: {
    current_page: 1,
    per_page: 10,
    total: 164,
    last_page: 17
  }
}
```

**DELETE /api/v1/dashboard/trails/{id}**
```javascript
// Response
{
  message: "Trail deleted successfully"
}
```

**DELETE /api/v1/dashboard/trails/bulk-delete**
```javascript
// Request
{
  trail_ids: [1, 2, 3]
}

// Response
{
  message: "3 trails deleted successfully",
  deleted_count: 3
}
```

**POST /api/v1/dashboard/trails/export/gpx**
```javascript
// Request
{
  trail_ids: [1, 2, 3]
}

// Response: GPX file (binary)
```

### **API Integration Implementation**

```javascript
methods: {
  async fetchTrails() {
    this.loading = true
    this.error = null

    try {
      const params = {
        page: this.internalPage,
        per_page: this.internalItemsPerPage,
        ...this.buildFilterParams()
      }

      const response = await this.$http.get('/api/v1/dashboard/trails', { params })

      this.trails = response.data.data
      this.totalItems = response.data.meta.total

    } catch (error) {
      console.error('Failed to fetch trails:', error)
      this.error = error.message || 'Nie udało się pobrać szlaków'
      this.showError('Nie udało się pobrać listy szlaków')
    } finally {
      this.loading = false
    }
  },

  buildFilterParams() {
    const params = {}

    if (this.filters.difficulty) {
      params.difficulty = this.filters.difficulty
    }

    if (this.filters.minRating) {
      params.min_rating = this.filters.minRating
    }

    if (this.filters.river) {
      params.river_name = this.filters.river
    }

    if (this.filters.status) {
      params.status = this.filters.status
    }

    return params
  }
}
```

---

## ✅ **IMPLEMENTATION CHECKLIST**

### **Phase 1: Core Functionality (Priority: HIGH)**
- [ ] Integracja z API endpoints dla trails
- [ ] Zastąpienie mock danych prawdziwymi
- [ ] Server-side pagination
- [ ] Zaawansowane filtry UI
- [ ] ConfirmDialog integration

### **Phase 2: User Experience (Priority: MEDIUM)**
- [ ] Export CSV/JSON
- [ ] Multi-select i bulk operations
- [ ] Status column
- [ ] Refresh button
- [ ] Tooltips dla długich nazw

### **Phase 3: Advanced Features (Priority: LOW)**
- [ ] GPX export
- [ ] Import GPX dialog
- [ ] Loading skeletons
- [ ] Advanced sorting
- [ ] Mobile responsive optimizations

---

## 📝 **CODE EXAMPLE - Complete Component**

```vue
<template>
  <div class="trails-list">
    <UiDataTable
      v-model:selected="selectedTrails"
      v-model:page="internalPage"
      v-model:items-per-page="internalItemsPerPage"
      title="Lista szlaków"
      :headers="headers"
      :items="trails"
      :loading="loading"
      :total-items="totalItems"
      :actions="{ view: true, edit: true, delete: true }"
      show-select
      @view="viewTrail"
      @edit="editTrail"
      @delete="confirmDeleteTrail"
      @update:page="fetchTrails"
      @update:items-per-page="fetchTrails"
    >
      <!-- Header actions -->
      <template #actions>
        <div class="trails-header-actions">
          <!-- Bulk actions -->
          <div v-if="hasSelectedTrails" class="bulk-actions">
            <span class="selected-count">
              {{ selectedTrails.length }} zaznaczono
            </span>

            <UiButton variant="ghost" size="sm" @click="bulkExport">
              <v-icon start size="small">mdi-download</v-icon>
              Eksportuj
            </UiButton>

            <UiButton variant="ghost" size="sm" color="error" @click="bulkDelete">
              <v-icon start size="small">mdi-delete</v-icon>
              Usuń
            </UiButton>
          </div>

          <!-- Standard actions -->
          <div v-else class="standard-actions">
            <UiButton variant="ghost" size="icon" @click="refreshTrails" :loading="loading">
              <v-icon>mdi-refresh</v-icon>
            </UiButton>

            <v-menu offset-y>
              <template #activator="{ props }">
                <UiButton v-bind="props" variant="outline" size="sm">
                  <v-icon start size="small">mdi-download</v-icon>
                  Eksportuj
                  <v-icon end size="small">mdi-chevron-down</v-icon>
                </UiButton>
              </template>

              <v-list density="compact">
                <v-list-item @click="exportTrails('csv')">
                  <template #prepend>
                    <v-icon size="small">mdi-file-delimited</v-icon>
                  </template>
                  <v-list-item-title>Eksportuj CSV</v-list-item-title>
                </v-list-item>
                <v-list-item @click="exportTrails('json')">
                  <template #prepend>
                    <v-icon size="small">mdi-code-json</v-icon>
                  </template>
                  <v-list-item-title>Eksportuj JSON</v-list-item-title>
                </v-list-item>
              </v-list>
            </v-menu>

            <UiButton variant="default" size="sm" @click="$router.push('/dashboard/trails/create')">
              <v-icon start size="small">mdi-plus</v-icon>
              Dodaj szlak
            </UiButton>
          </div>
        </div>
      </template>

      <!-- Filters -->
      <template #filters>
        <div class="trails-filters">
          <v-select
            v-model="filters.difficulty"
            :items="difficultyOptions"
            label="Trudność"
            variant="outlined"
            density="compact"
            clearable
            hide-details
            style="max-width: 180px"
          >
            <template #prepend-inner>
              <v-icon size="small">mdi-hiking</v-icon>
            </template>
          </v-select>

          <v-select
            v-model="filters.minRating"
            :items="ratingOptions"
            label="Min. ocena"
            variant="outlined"
            density="compact"
            clearable
            hide-details
            style="max-width: 140px"
          >
            <template #prepend-inner>
              <v-icon size="small">mdi-star</v-icon>
            </template>
          </v-select>

          <v-autocomplete
            v-model="filters.river"
            :items="uniqueRivers"
            label="Rzeka"
            variant="outlined"
            density="compact"
            clearable
            hide-details
            style="max-width: 200px"
          >
            <template #prepend-inner>
              <v-icon size="small">mdi-waves</v-icon>
            </template>
          </v-autocomplete>

          <v-select
            v-model="filters.status"
            :items="statusOptions"
            label="Status"
            variant="outlined"
            density="compact"
            clearable
            hide-details
            style="max-width: 140px"
          >
            <template #prepend-inner>
              <v-icon size="small">mdi-check-circle</v-icon>
            </template>
          </v-select>

          <UiButton
            v-if="hasActiveFilters"
            variant="ghost"
            size="sm"
            @click="clearFilters"
          >
            <v-icon start size="small">mdi-filter-remove</v-icon>
            Wyczyść
          </UiButton>
        </div>
      </template>

      <!-- Custom columns -->
      <template #item.trail_name="{ value }">
        <v-tooltip location="top" :disabled="value.length < 30">
          <template #activator="{ props }">
            <span v-bind="props" class="trail-name">{{ value }}</span>
          </template>
          {{ value }}
        </v-tooltip>
      </template>

      <template #item.difficulty="{ value }">
        <UiBadge :variant="getDifficultyVariant(value)">
          {{ getDifficultyLabel(value) }}
        </UiBadge>
      </template>

      <template #item.trail_length="{ value }">
        <span class="font-weight-medium">{{ formatLength(value) }}</span>
      </template>

      <template #item.rating="{ value }">
        <div class="d-flex align-center">
          <v-rating
            :model-value="value"
            color="warning"
            size="small"
            half-increments
            readonly
            density="compact"
          />
          <span class="text-caption ms-2">({{ formatRating(value) }})</span>
        </div>
      </template>

      <template #item.status="{ value }">
        <UiBadge :variant="getStatusVariant(value)">
          <v-icon start size="x-small">{{ getStatusIcon(value) }}</v-icon>
          {{ getStatusLabel(value) }}
        </UiBadge>
      </template>
    </UiDataTable>

    <!-- Delete confirmation dialog -->
    <ConfirmDialog
      v-model="deleteDialog.show"
      title="Usunąć szlak?"
      :message="`Czy na pewno chcesz usunąć szlak &quot;${deleteDialog.trail?.trail_name}&quot;?`"
      details="Ta operacja jest nieodwracalna. Wszystkie dane szlaku zostaną trwale usunięte."
      icon="mdi-delete-alert"
      icon-color="error"
      confirm-text="Usuń szlak"
      :loading="deleteDialog.loading"
      @confirm="executeDelete"
      @cancel="deleteDialog.show = false"
    />

    <!-- Bulk delete dialog -->
    <ConfirmDialog
      v-model="bulkDeleteDialog.show"
      title="Usunąć zaznaczone szlaki?"
      :message="`Czy na pewno chcesz usunąć ${selectedTrails.length} zaznaczonych szlaków?`"
      details="Ta operacja jest nieodwracalna. Wszystkie dane zostaną trwale usunięte."
      icon="mdi-delete-alert"
      icon-color="error"
      confirm-text="Usuń wszystkie"
      :loading="bulkDeleteDialog.loading"
      @confirm="executeBulkDelete"
      @cancel="bulkDeleteDialog.show = false"
    />
  </div>
</template>

<script>
import { UiDataTable, UiButton, UiBadge, ConfirmDialog } from '@/dashboard/components/ui'
import { mapActions } from 'vuex'

const DIFFICULTY_CONFIG = {
  'łatwy': { variant: 'success', label: 'Łatwy' },
  'umiarkowany': { variant: 'warning', label: 'Umiarkowany' },
  'trudny': { variant: 'destructive', label: 'Trudny' },
  'ekspertowy': { variant: 'secondary', label: 'Ekspertowy' }
}

const STATUS_CONFIG = {
  active: { variant: 'success', label: 'Aktywny', icon: 'mdi-check-circle' },
  draft: { variant: 'secondary', label: 'Szkic', icon: 'mdi-pencil-circle' },
  archived: { variant: 'outline', label: 'Archiwum', icon: 'mdi-archive' }
}

export default {
  name: 'DashboardTrailsList',
  components: { UiDataTable, UiButton, UiBadge, ConfirmDialog },

  data() {
    return {
      loading: false,
      trails: [],
      totalItems: 0,
      internalPage: 1,
      internalItemsPerPage: 10,
      selectedTrails: [],
      filters: {
        difficulty: null,
        minRating: null,
        river: null,
        status: null
      },
      deleteDialog: {
        show: false,
        trail: null,
        loading: false
      },
      bulkDeleteDialog: {
        show: false,
        loading: false
      }
    }
  },

  computed: {
    headers() {
      return [
        { title: 'Nazwa szlaku', key: 'trail_name', sortable: true },
        { title: 'Rzeka', key: 'river_name', sortable: true },
        { title: 'Długość', key: 'trail_length', sortable: true, align: 'end' },
        { title: 'Trudność', key: 'difficulty', sortable: true },
        { title: 'Ocena', key: 'rating', sortable: true, align: 'center' },
        { title: 'Autor', key: 'author', sortable: true },
        { title: 'Status', key: 'status', sortable: true, width: '120px' }
      ]
    },

    difficultyOptions() {
      return Object.keys(DIFFICULTY_CONFIG).map(key => ({
        value: key,
        title: DIFFICULTY_CONFIG[key].label
      }))
    },

    ratingOptions() {
      return [
        { value: 4.5, title: '★ 4.5+' },
        { value: 4.0, title: '★ 4.0+' },
        { value: 3.5, title: '★ 3.5+' },
        { value: 3.0, title: '★ 3.0+' }
      ]
    },

    statusOptions() {
      return Object.keys(STATUS_CONFIG).map(key => ({
        value: key,
        title: STATUS_CONFIG[key].label
      }))
    },

    uniqueRivers() {
      return [...new Set(this.trails.map(t => t.river_name))].sort()
    },

    hasActiveFilters() {
      return Object.values(this.filters).some(v => v !== null)
    },

    hasSelectedTrails() {
      return this.selectedTrails.length > 0
    }
  },

  watch: {
    filters: {
      handler() {
        this.internalPage = 1
        this.fetchTrails()
      },
      deep: true
    }
  },

  async created() {
    await this.fetchTrails()
  },

  methods: {
    ...mapActions('ui', ['showSuccess', 'showError', 'showInfo']),

    async fetchTrails() {
      this.loading = true

      try {
        const params = {
          page: this.internalPage,
          per_page: this.internalItemsPerPage,
          ...this.buildFilterParams()
        }

        const response = await this.$http.get('/api/v1/dashboard/trails', { params })

        this.trails = response.data.data
        this.totalItems = response.data.meta.total
      } catch (error) {
        console.error('Failed to fetch trails:', error)
        this.showError('Nie udało się pobrać listy szlaków')
      } finally {
        this.loading = false
      }
    },

    buildFilterParams() {
      const params = {}

      if (this.filters.difficulty) params.difficulty = this.filters.difficulty
      if (this.filters.minRating) params.min_rating = this.filters.minRating
      if (this.filters.river) params.river_name = this.filters.river
      if (this.filters.status) params.status = this.filters.status

      return params
    },

    async viewTrail(trail) {
      this.$router.push(`/dashboard/trails/${trail.id}`)
    },

    async editTrail(trail) {
      this.$router.push(`/dashboard/trails/${trail.id}/edit`)
    },

    confirmDeleteTrail(trail) {
      this.deleteDialog.trail = trail
      this.deleteDialog.show = true
    },

    async executeDelete() {
      this.deleteDialog.loading = true

      try {
        await this.$http.delete(`/api/v1/dashboard/trails/${this.deleteDialog.trail.id}`)

        this.trails = this.trails.filter(t => t.id !== this.deleteDialog.trail.id)
        this.showSuccess(`Szlak "${this.deleteDialog.trail.trail_name}" został usunięty`)
        this.deleteDialog.show = false
      } catch (error) {
        console.error('Failed to delete trail:', error)
        this.showError('Nie udało się usunąć szlaku')
      } finally {
        this.deleteDialog.loading = false
        this.deleteDialog.trail = null
      }
    },

    async bulkDelete() {
      if (!this.hasSelectedTrails) return
      this.bulkDeleteDialog.show = true
    },

    async executeBulkDelete() {
      this.bulkDeleteDialog.loading = true

      try {
        const trailIds = this.selectedTrails.map(t => t.id)

        await this.$http.delete('/api/v1/dashboard/trails/bulk-delete', {
          data: { trail_ids: trailIds }
        })

        this.trails = this.trails.filter(t => !trailIds.includes(t.id))
        this.showSuccess(`Usunięto ${trailIds.length} szlaków`)
        this.selectedTrails = []
        this.bulkDeleteDialog.show = false
      } catch (error) {
        console.error('Bulk delete failed:', error)
        this.showError('Nie udało się usunąć szlaków')
      } finally {
        this.bulkDeleteDialog.loading = false
      }
    },

    async exportTrails(format = 'csv') {
      try {
        this.showInfo(`Przygotowywanie eksportu ${format.toUpperCase()}...`)

        const dataToExport = this.hasSelectedTrails ? this.selectedTrails : this.trails

        if (format === 'csv') {
          this.exportCSV(dataToExport)
        } else if (format === 'json') {
          this.exportJSON(dataToExport)
        }

        this.showSuccess(`Eksport ${format.toUpperCase()} zakończony`)
      } catch (error) {
        console.error('Export failed:', error)
        this.showError('Nie udało się wyeksportować danych')
      }
    },

    exportCSV(trails) {
      const headers = ['ID', 'Nazwa szlaku', 'Rzeka', 'Długość (km)', 'Trudność', 'Ocena', 'Autor', 'Status']
      const rows = trails.map(trail => [
        trail.id,
        `"${trail.trail_name}"`,
        `"${trail.river_name}"`,
        trail.trail_length,
        trail.difficulty,
        trail.rating,
        `"${trail.author}"`,
        trail.status
      ])

      const csv = [headers.join(','), ...rows.map(row => row.join(','))].join('\n')
      this.downloadFile(csv, 'trails-export.csv', 'text/csv')
    },

    exportJSON(trails) {
      const json = JSON.stringify(trails, null, 2)
      this.downloadFile(json, 'trails-export.json', 'application/json')
    },

    downloadFile(content, filename, mimeType) {
      const blob = new Blob([content], { type: mimeType })
      const url = window.URL.createObjectURL(blob)
      const link = document.createElement('a')
      link.href = url
      link.download = filename
      link.click()
      window.URL.revokeObjectURL(url)
    },

    bulkExport() {
      if (!this.hasSelectedTrails) return
      this.exportTrails('csv')
    },

    async refreshTrails() {
      await this.fetchTrails()
      this.showInfo('Lista szlaków została odświeżona')
    },

    clearFilters() {
      this.filters = {
        difficulty: null,
        minRating: null,
        river: null,
        status: null
      }
    },

    getDifficultyVariant(difficulty) {
      return DIFFICULTY_CONFIG[difficulty]?.variant || 'secondary'
    },

    getDifficultyLabel(difficulty) {
      return DIFFICULTY_CONFIG[difficulty]?.label || difficulty
    },

    getStatusVariant(status) {
      return STATUS_CONFIG[status]?.variant || 'secondary'
    },

    getStatusIcon(status) {
      return STATUS_CONFIG[status]?.icon || 'mdi-circle-outline'
    },

    getStatusLabel(status) {
      return STATUS_CONFIG[status]?.label || status
    },

    formatLength(length) {
      return `${length} km`
    },

    formatRating(rating) {
      return Number(rating).toFixed(1)
    }
  }
}
</script>

<style scoped>
.trails-list {
  width: 100%;
}

.trails-header-actions {
  display: flex;
  gap: 8px;
  align-items: center;
}

.bulk-actions,
.standard-actions {
  display: flex;
  gap: 8px;
  align-items: center;
}

.selected-count {
  font-size: 13px;
  font-weight: 500;
  color: hsl(var(--v-theme-primary));
  padding: 0 8px;
}

.trails-filters {
  display: flex;
  gap: 12px;
  align-items: center;
  flex-wrap: wrap;
}

.trail-name {
  display: inline-block;
  max-width: 200px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  font-weight: 500;
  color: hsl(var(--v-theme-primary));
  cursor: pointer;
}

.trail-name:hover {
  text-decoration: underline;
}

@media (max-width: 960px) {
  .trails-filters {
    flex-direction: column;
    align-items: stretch;
  }

  .trails-header-actions {
    flex-direction: column;
    align-items: stretch;
  }

  .standard-actions {
    flex-wrap: wrap;
  }
}
</style>
```

---

## 🚀 **NASTĘPNE KROKI**

### **Immediate Actions:**
1. ✅ Zatwierdzić design plan z zespołem
2. ⏳ Utworzyć backend endpoints API
3. ⏳ Zaimplementować frontend według planu
4. ⏳ Testy integracyjne
5. ⏳ Code review i deployment

### **Timeline Estimate:**
- **Backend API:** 1-2 dni
- **Frontend Implementation:** 2-3 dni
- **Testing & Polish:** 1 dzień
- **Total:** ~5 dni pracy

---

**Dokument przygotowany:** 2025-11-17
**Autor:** Claude Code AI
**Status:** ✅ Ready for Implementation