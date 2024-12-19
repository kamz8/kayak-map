import axios from 'axios'

const apiClient = axios.create({
    baseURL: `${import.meta.env.VITE_API_URL}/api/v1`,
    timeout: 10000,
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-Client-Type': 'web'
    }
})

apiClient.interceptors.request.use(
    (config) => {
        // Upewniamy się, że config.headers istnieje
        config.headers = config.headers || {};

        // Wymuszamy nagłówek X-Client-Type
        config.headers['X-Client-Type'] = 'web';

        const token = localStorage.getItem('token')
        if (token) {
            config.headers['Authorization'] = `Bearer ${token}`
        }

        // Debugowanie nagłówków
        console.log('Request headers:', config.headers);

        return config
    },
    (error) => {
        return Promise.reject(error)
    }
)

export default apiClient
