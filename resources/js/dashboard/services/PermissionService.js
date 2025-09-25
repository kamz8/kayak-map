/**
 * Permission Service - ACL System dla Dashboard
 * Obsługuje sprawdzanie uprawnień i ról użytkowników
 */
export class PermissionService {
  constructor(store) {
    this.store = store
  }

  /**
   * Sprawdza czy użytkownik ma określone uprawnienie
   * @param {string} permission - Nazwa uprawnienia (np. 'users.create')
   * @returns {boolean}
   */
  can(permission) {
    const user = this.store.getters['auth/user']

    if (!user) {
      return false
    }

    // Super Admin bypasses all permission checks
    if (this.isSuperAdmin(user)) {
      return true
    }

    // Check if user has the specific permission
    return this.hasPermission(user, permission)
  }

  /**
   * Sprawdza czy użytkownik ma którąkolwiek z podanych uprawnień
   * @param {string[]} permissions - Array uprawnień
   * @returns {boolean}
   */
  canAny(permissions) {
    return permissions.some(permission => this.can(permission))
  }

  /**
   * Sprawdza czy użytkownik ma wszystkie podane uprawnienia
   * @param {string[]} permissions - Array uprawnień
   * @returns {boolean}
   */
  canAll(permissions) {
    return permissions.every(permission => this.can(permission))
  }

  /**
   * Sprawdza czy użytkownik ma określoną rolę
   * @param {string} role - Nazwa roli (np. 'Admin')
   * @returns {boolean}
   */
  hasRole(role) {
    const user = this.store.getters['auth/user']

    if (!user || !user.roles) {
      return false
    }

    return user.roles.some(userRole => userRole.name === role)
  }

  /**
   * Sprawdza czy użytkownik ma którąkolwiek z podanych ról
   * @param {string[]} roles - Array ról
   * @returns {boolean}
   */
  hasAnyRole(roles) {
    return roles.some(role => this.hasRole(role))
  }

  /**
   * Sprawdza czy użytkownik ma wszystkie podane role
   * @param {string[]} roles - Array ról
   * @returns {boolean}
   */
  hasAllRoles(roles) {
    return roles.every(role => this.hasRole(role))
  }

  /**
   * Sprawdza czy użytkownik jest Super Admin
   * @param {Object} user - User object
   * @returns {boolean}
   */
  isSuperAdmin(user = null) {
    const currentUser = user || this.store.getters['auth/user']

    if (!currentUser) {
      return false
    }

    return this.hasRole('Super Admin')
  }

  /**
   * Sprawdza czy użytkownik jest Admin (Super Admin lub Admin)
   * @returns {boolean}
   */
  isAdmin() {
    return this.hasAnyRole(['Super Admin', 'Admin'])
  }

  /**
   * Sprawdza czy użytkownik ma dostęp do dashboard
   * @returns {boolean}
   */
  canAccessDashboard() {
    return this.can('dashboard.view') || this.isSuperAdmin()
  }

  /**
   * Zwraca listę ról użytkownika
   * @returns {Array}
   */
  getUserRoles() {
    const user = this.store.getters['auth/user']
    return user?.roles || []
  }

  /**
   * Zwraca listę uprawnień użytkownika
   * @returns {Array}
   */
  getUserPermissions() {
    const user = this.store.getters['auth/user']

    if (!user || !user.roles) {
      return []
    }

    // Flatten all permissions from all roles
    const permissions = user.roles.reduce((acc, role) => {
      if (role.permissions) {
        acc.push(...role.permissions)
      }
      return acc
    }, [])

    // Remove duplicates
    return permissions.filter((permission, index, self) =>
      index === self.findIndex(p => p.name === permission.name)
    )
  }

  /**
   * Sprawdza czy użytkownik ma określone uprawnienie
   * @private
   * @param {Object} user - User object
   * @param {string} permission - Permission name
   * @returns {boolean}
   */
  hasPermission(user, permission) {
    if (!user.roles) {
      return false
    }

    return user.roles.some(role => {
      return role.permissions && role.permissions.some(perm => perm.name === permission)
    })
  }

  /**
   * Utility function - sprawdza czy użytkownik może wykonać akcję CRUD
   * @param {string} resource - Nazwa zasobu (np. 'users', 'trails')
   * @param {string} action - Akcja (view, create, update, delete)
   * @returns {boolean}
   */
  canManage(resource, action) {
    return this.can(`${resource}.${action}`)
  }

  /**
   * Sprawdza czy użytkownik może zarządzać użytkownikami
   * @returns {Object} - Object z boolean values dla każdej akcji
   */
  getUserManagementPermissions() {
    return {
      view: this.can('users.view'),
      create: this.can('users.create'),
      update: this.can('users.update'),
      delete: this.can('users.delete'),
      assignRoles: this.can('users.assign_roles'),
      revokeRoles: this.can('users.revoke_roles')
    }
  }

  /**
   * Sprawdza uprawnienia dla trails
   * @returns {Object}
   */
  getTrailPermissions() {
    return {
      view: this.can('trails.view'),
      create: this.can('trails.create'),
      update: this.can('trails.update'),
      delete: this.can('trails.delete'),
      publish: this.can('trails.publish'),
      unpublish: this.can('trails.unpublish')
    }
  }

  /**
   * Sprawdza uprawnienia dla ról
   * @returns {Object}
   */
  getRolePermissions() {
    return {
      view: this.can('roles.view'),
      create: this.can('roles.create'),
      update: this.can('roles.update'),
      delete: this.can('roles.delete'),
      assignPermissions: this.can('roles.assign_permissions'),
      revokePermissions: this.can('roles.revoke_permissions')
    }
  }

  /**
   * Debug method - zwraca pełne informacje o uprawnieniach użytkownika
   * @returns {Object}
   */
  getDebugInfo() {
    const user = this.store.getters['auth/user']

    return {
      user: user?.email || 'Not authenticated',
      isSuperAdmin: this.isSuperAdmin(),
      isAdmin: this.isAdmin(),
      roles: this.getUserRoles().map(role => role.name),
      permissions: this.getUserPermissions().map(perm => perm.name),
      canAccessDashboard: this.canAccessDashboard()
    }
  }
}

// Export singleton factory
let permissionService = null

export function createPermissionService(store) {
  if (!permissionService) {
    permissionService = new PermissionService(store)
  }
  return permissionService
}

export function getPermissionService() {
  if (!permissionService) {
    throw new Error('PermissionService not initialized. Call createPermissionService(store) first.')
  }
  return permissionService
}

export default PermissionService