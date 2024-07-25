import axios from 'axios';

const apiClient = axios.create({
    baseURL: 'https://kayak-map.test/api/v1',
    timeout: 10000,
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
    }
});

export default apiClient;
