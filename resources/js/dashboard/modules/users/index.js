// Users Module - Main exports
export { default as usersRoutes } from './router/index.js'
export { default as usersStore } from './store/index.js'

// Export lazy-loaded page components (for external use)
export const UsersIndex = () => import('./Pages/UsersIndex.vue')
export const UserCreate = () => import('./Pages/UserCreate.vue')
export const UserEdit = () => import('./Pages/UserEdit.vue')

// Export components for potential reuse (lazy-loaded to avoid circular deps)
export const UserForm = () => import('./components/UserForm.vue')
export const UserRoleManager = () => import('./components/UserRoleManager.vue')
export const UserStatusBadge = () => import('./components/UserStatusBadge.vue')
export const UserFilters = () => import('./components/UserFilters.vue')