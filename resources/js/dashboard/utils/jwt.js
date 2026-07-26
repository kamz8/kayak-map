import { jwtDecode } from 'jwt-decode'

export class JwtUtils {
  static decode(token) {
    try {
      return jwtDecode(token)
    } catch (error) {
      console.error('JWT decode error:', error)
      return null
    }
  }

  static isExpired(token) {
    try {
      const decoded = this.decode(token)
      if (!decoded || !decoded.exp) {
        return true
      }

      const currentTime = Date.now() / 1000
      return decoded.exp < currentTime
    } catch (error) {
      return true
    }
  }

  static isValid(token) {
    if (!token) return false

    try {
      const decoded = this.decode(token)
      if (!decoded) return false

      return !this.isExpired(token)
    } catch (error) {
      return false
    }
  }

  static getUser(token) {
    try {
      const decoded = this.decode(token)
      if (!decoded) return null

      return {
        id: decoded.sub,
        email: decoded.email,
        first_name: decoded.first_name,
        last_name: decoded.last_name,
        full_name: decoded.first_name && decoded.last_name
          ? `${decoded.first_name} ${decoded.last_name}`.trim()
          : decoded.email,
        is_admin: decoded.is_admin || false,
        roles: decoded.roles || [],
        permissions: decoded.permissions || [],
        email_verified_at: decoded.email_verified_at,
        created_at: decoded.iat ? new Date(decoded.iat * 1000).toISOString() : null
      }
    } catch (error) {
      console.error('JWT getUser error:', error)
      return null
    }
  }

  static hasRole(token, roleName) {
    try {
      const user = this.getUser(token)
      if (!user || !user.roles) return false

      return user.roles.some(role =>
        typeof role === 'string' ? role === roleName : role.name === roleName
      )
    } catch (error) {
      return false
    }
  }

  static hasPermission(token, permissionName) {
    try {
      const user = this.getUser(token)
      if (!user || !user.permissions) return false

      return user.permissions.includes(permissionName)
    } catch (error) {
      return false
    }
  }

  static canAccessDashboard(token) {
    try {
      const user = this.getUser(token)
      if (!user) return false

      if (user.is_admin) return true

      return this.hasRole(token, 'Super Admin') || this.hasRole(token, 'Admin')
    } catch (error) {
      return false
    }
  }

  static getTokenExpiration(token) {
    try {
      const decoded = this.decode(token)
      if (!decoded || !decoded.exp) return null

      return new Date(decoded.exp * 1000)
    } catch (error) {
      return null
    }
  }

  static getTimeToExpire(token) {
    try {
      const expiration = this.getTokenExpiration(token)
      if (!expiration) return 0

      return Math.max(0, expiration.getTime() - Date.now())
    } catch (error) {
      return 0
    }
  }
}