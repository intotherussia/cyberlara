import axios from 'axios';

// Настройка Axios
window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.withCredentials = true;

// Настройка для CSRF токена
const token = document.head.querySelector('meta[name="csrf-token"]');
if (token) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
} else {
    console.warn('CSRF token not found: https://laravel.com/docs/csrf#csrf-x-csrf-token');
}

// Базовый URL для API
window.axios.defaults.baseURL = import.meta.env.VITE_APP_URL || '/';

console.log('✅ Axios initialized');
