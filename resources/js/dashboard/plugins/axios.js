import axios from 'axios'
import store from '../store/index.js'
import { tokenManager } from '../utils/tokenManager.js'

// Create axios instance
const apiClient = axios.create({
  baseURL: '/api/v1',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    'X-Client-Type': 'web',
    'X-Requested-With': 'XMLHttpRequest'
  }
})

// Request interceptor - add auth token with automatic refresh
apiClient.interceptors.request.use(
  async (config) => {
    try {
      // Let token manager handle token refresh if needed
      return await tokenManager.handleRequest(config)
    } catch (error) {
      // If token handling fails, proceed without token for public endpoints
      console.warn('Token handling failed, proceeding without auth:', error.message)
      return config
    }
  },
  (error) => {
    return Promise.reject(error)
  }
)

// Response interceptor - handle auth errors with automatic retry
apiClient.interceptors.response.use(
  (response) => {
    return response
  },
  async (error) => {
    // Let token manager handle 401 errors with automatic refresh and retry
    return await tokenManager.handleResponse(error)
  }
)

export default apiClient
