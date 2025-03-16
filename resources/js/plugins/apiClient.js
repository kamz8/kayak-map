import axios from 'axios';

// Ustawienie wartości domyślnej dla VITE_API_URL
const apiUrl = import.meta.env.VITE_API_URL || 'https://wartkinurt.pl';

// Budowanie pełnego URL do API
const baseURL = `${apiUrl}/api/v1`;

const apiClient = axios.create({
    baseURL: baseURL,
    timeout: 10000,
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-Client-Type': 'web',
    },
});

apiClient.interceptors.request.use(
    (config) => {
        config.headers = config.headers || {};
        config.headers['X-Client-Type'] = 'web';

        const token = localStorage.getItem('token');
        if (token) {
            config.headers['Authorization'] = `Bearer ${token}`;
        }

        return config;
    },
    (error) => {
        return Promise.reject(error);
    }
);

export default apiClient;
