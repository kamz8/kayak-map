/**
 * UI Components - Kayak Map Dashboard UI Kit
 * shadcn/ui inspired components built on Vuetify
 */

// New UI Kit Components (shadcn/ui style)
export { default as UiButton } from './UiButton.vue'
export { default as UiCard } from './UiCard.vue'
export { default as UiInput } from './UiInput.vue'
export { default as UiBadge } from './UiBadge.vue'
export { default as UiBreadcrumb } from './UiBreadcrumb.vue'
export { default as UiDataTable } from './UiDataTable.vue'
export { default as UiAvatar } from './UiAvatar.vue'
export { default as UiDialog } from './UiDialog.vue'
export { default as PermissionSelector } from './PermissionSelector.vue'
export { default as UserSelector } from './UserSelector.vue'

// Legacy Dashboard Components (maintain compatibility)
export { default as ConfirmDialog } from './ConfirmDialog.vue'
export { default as DataTable } from './DataTable.vue'
export { default as FormField } from './FormField.vue'
export { default as Logo } from './Logo.vue'
export { default as StatsCard } from './StatsCard.vue'

// Import components for registration helper
import UiButton from './UiButton.vue'
import UiCard from './UiCard.vue'
import UiInput from './UiInput.vue'
import UiBadge from './UiBadge.vue'
import UiBreadcrumb from './UiBreadcrumb.vue'
import UiDataTable from './UiDataTable.vue'
import UiAvatar from './UiAvatar.vue'
import UiDialog from './UiDialog.vue'
import PermissionSelector from './PermissionSelector.vue'
import UserSelector from './UserSelector.vue'
import ConfirmDialog from './ConfirmDialog.vue'
import DataTable from './DataTable.vue'
import FormField from './FormField.vue'
import Logo from './Logo.vue'
import StatsCard from './StatsCard.vue'

// Helper function for global component registration
export function registerUiComponents(app) {
  // New UI Kit Components
  app.component('UiButton', UiButton)
  app.component('UiCard', UiCard)
  app.component('UiInput', UiInput)
  app.component('UiBadge', UiBadge)
  app.component('UiBreadcrumb', UiBreadcrumb)
  app.component('UiDataTable', UiDataTable)
  app.component('UiAvatar', UiAvatar)
  app.component('UiDialog', UiDialog)
  app.component('PermissionSelector', PermissionSelector)
  app.component('UserSelector', UserSelector)

  // Legacy components (keep for compatibility)
  app.component('ConfirmDialog', ConfirmDialog)
  app.component('DataTable', DataTable)
  app.component('FormField', FormField)
  app.component('Logo', Logo)
  app.component('StatsCard', StatsCard)
}