import { createApp } from 'vue';
import { createPinia } from 'pinia';
import Antd from 'ant-design-vue';
import axios from 'axios';
import 'ant-design-vue/dist/reset.css';
import '../css/app.css';

import App from './main/views/App.vue';
import router from './main/router';

// Configure Axios
axios.defaults.baseURL = window.config.path + '/api';
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Clean duplicate /api prefix from request URLs
axios.interceptors.request.use(
    (config) => {
        if (config.url && config.url.startsWith('/api/')) {
            config.url = config.url.substring(4);
        }
        return config;
    },
    (error) => Promise.reject(error)
);

// Set up authorization token if it exists in localStorage
const token = localStorage.getItem('auth_token');
if (token) {
    axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
}

// Add 401 response interceptor to handle session/token expiry
axios.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response && error.response.status === 401) {
            localStorage.removeItem('auth_token');
            localStorage.removeItem('auth_user');
            delete axios.defaults.headers.common['Authorization'];
            // Redirect to login
            const basePath = window.config.path.startsWith('http') ? new URL(window.config.path).pathname : window.config.path;
            const cleanPath = basePath.endsWith('/') ? basePath.slice(0, -1) : basePath;
            window.location.href = cleanPath + '/admin/login';
        }
        return Promise.reject(error);
    }
);

const app = createApp(App);
app.use(createPinia());
app.use(router);
app.use(Antd);

router.isReady().then(() => {
    app.mount('#app');
});
