import { JwtUtils } from './jwt.js'
import axios from 'axios'

export class TokenManager {
  constructor() {
    this.refreshPromise = null
    this.refreshTimer = null
    this.REFRESH_THRESHOLD = 5 * 60 * 1000 // 5 minutes before expiry
    this.isRefreshing = false
    this.failedQueue = []
  }

  /**
   * Initialize token manager with tokens
   */
  initialize(accessToken, refreshToken) {
    this.setTokens(accessToken, refreshToken)
    this.startRefreshTimer()
  }

  /**
   * Set tokens in localStorage and memory
   */
  setTokens(accessToken, refreshToken) {
    if (accessToken) {
      localStorage.setItem('token', accessToken)
      this.accessToken = accessToken
    }

    if (refreshToken) {
      localStorage.setItem('refresh_token', refreshToken)
      this.refreshToken = refreshToken
    }
  }

  /**
   * Get current access token
   */
  getAccessToken() {
    return this.accessToken || localStorage.getItem('token')
  }

  /**
   * Get current refresh token
   */
  getRefreshToken() {
    return this.refreshToken || localStorage.getItem('refresh_token')
  }

  /**
   * Clear all tokens
   */
  clearTokens() {
    localStorage.removeItem('token')
    localStorage.removeItem('refresh_token')
    this.accessToken = null
    this.refreshToken = null
    this.stopRefreshTimer()
  }

  /**
   * Check if access token needs refresh
   */
  shouldRefreshToken() {
    const token = this.getAccessToken()
    if (!token) return false

    const timeToExpire = JwtUtils.getTimeToExpire(token)
    return timeToExpire <= this.REFRESH_THRESHOLD
  }

  /**
   * Start automatic refresh timer
   */
  startRefreshTimer() {
    this.stopRefreshTimer()

    const token = this.getAccessToken()
    if (!token) return

    const timeToExpire = JwtUtils.getTimeToExpire(token)
    const timeToRefresh = Math.max(0, timeToExpire - this.REFRESH_THRESHOLD)

    console.log(`Token refresh scheduled in ${timeToRefresh / 1000 / 60} minutes`)

    this.refreshTimer = setTimeout(() => {
      this.refreshTokens()
    }, timeToRefresh)
  }

  /**
   * Stop automatic refresh timer
   */
  stopRefreshTimer() {
    if (this.refreshTimer) {
      clearTimeout(this.refreshTimer)
      this.refreshTimer = null
    }
  }

  /**
   * Refresh tokens using refresh token
   * RFC 6749 compliant implementation
   */
  async refreshTokens() {
    if (this.isRefreshing) {
      return this.refreshPromise
    }

    const refreshToken = this.getRefreshToken()
    if (!refreshToken) {
      throw new Error('No refresh token available')
    }

    this.isRefreshing = true

    this.refreshPromise = this.performRefresh(refreshToken)
      .then((result) => {
        this.isRefreshing = false
        this.processQueue(null, result.access_token)
        return result
      })
      .catch((error) => {
        this.isRefreshing = false
        this.processQueue(error, null)
        throw error
      })

    return this.refreshPromise
  }

  /**
   * Perform the actual token refresh request
   */
  async performRefresh(refreshToken) {
    try {
      const response = await axios.post('/api/v1/auth/refresh', {
        refresh_token: refreshToken
      }, {
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-Client-Type': 'web',
          'X-Requested-With': 'XMLHttpRequest'
        }
      })

      const { access_token, refresh_token, expires_in } = response.data.data

      // Update tokens
      this.setTokens(access_token, refresh_token)

      // Restart timer for new token
      this.startRefreshTimer()

      console.log('Tokens refreshed successfully')

      return {
        access_token,
        refresh_token,
        expires_in
      }
    } catch (error) {
      console.error('Token refresh failed:', error)

      if (error.response?.status === 401) {
        // Refresh token is invalid, clear all tokens
        this.clearTokens()
        throw new Error('Refresh token expired, please login again')
      }

      throw new Error('Failed to refresh token')
    }
  }

  /**
   * Add failed request to queue during refresh
   */
  addToQueue(resolve, reject) {
    this.failedQueue.push({ resolve, reject })
  }

  /**
   * Process queued requests after refresh
   */
  processQueue(error, token) {
    this.failedQueue.forEach(({ resolve, reject }) => {
      if (error) {
        reject(error)
      } else {
        resolve(token)
      }
    })

    this.failedQueue = []
  }

  /**
   * Handle axios request with token refresh
   */
  async handleRequest(config) {
    const token = this.getAccessToken()

    if (!token) {
      throw new Error('No access token available')
    }

    // Check if token needs refresh
    if (this.shouldRefreshToken()) {
      try {
        await this.refreshTokens()
        // Use the new token
        config.headers.Authorization = `Bearer ${this.getAccessToken()}`
      } catch (error) {
        throw error
      }
    } else {
      config.headers.Authorization = `Bearer ${token}`
    }

    return config
  }

  /**
   * Handle axios response with automatic retry on 401
   */
  async handleResponse(error) {
    const originalRequest = error.config

    if (error.response?.status === 401 && !originalRequest._retry) {
      if (this.isRefreshing) {
        // If already refreshing, queue this request
        return new Promise((resolve, reject) => {
          this.addToQueue((token) => {
            originalRequest.headers.Authorization = `Bearer ${token}`
            resolve(axios(originalRequest))
          }, reject)
        })
      }

      originalRequest._retry = true

      try {
        const result = await this.refreshTokens()
        originalRequest.headers.Authorization = `Bearer ${result.access_token}`
        return axios(originalRequest)
      } catch (refreshError) {
        // Refresh failed, redirect to login
        this.clearTokens()
        window.location.href = '/dashboard/login'
        return Promise.reject(refreshError)
      }
    }

    return Promise.reject(error)
  }

  /**
   * Get token info for debugging
   */
  getTokenInfo() {
    const accessToken = this.getAccessToken()
    const refreshToken = this.getRefreshToken()

    return {
      hasAccessToken: !!accessToken,
      hasRefreshToken: !!refreshToken,
      accessTokenExpiry: accessToken ? JwtUtils.getTokenExpiration(accessToken) : null,
      timeToExpire: accessToken ? JwtUtils.getTimeToExpire(accessToken) : 0,
      shouldRefresh: this.shouldRefreshToken(),
      isRefreshing: this.isRefreshing
    }
  }
}

// Export singleton instance
export const tokenManager = new TokenManager()