import { createApp } from 'vue';
import { createPinia } from 'pinia';
import Antd from 'ant-design-vue';
import axios from 'axios';
import 'ant-design-vue/dist/reset.css';
import '../css/app.css';
import './main/assets/vuexy-theme.css';

import App from './main/views/App.vue';
import router from './main/router';

// Configure Axios
const appRawPath = window.config?.path || '';
axios.defaults.baseURL = appRawPath ? (appRawPath.endsWith('/') ? appRawPath.slice(0, -1) : appRawPath) + '/api' : '/api';
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

axios.interceptors.request.use(
    (config) => {
        if (config.url && config.url.startsWith('/api/')) {
            config.url = config.url.substring(5);
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
            // Only redirect if we're not already on the login page
            const currentPath = window.location.pathname;
            const rawConfigPath = window.config?.path || '/';
            const basePath = rawConfigPath.startsWith('http') ? new URL(rawConfigPath).pathname : rawConfigPath;
            const cleanPath = basePath.endsWith('/') ? basePath.slice(0, -1) : basePath;
            const loginPath = cleanPath + '/admin/login';
            if (currentPath !== loginPath) {
                window.location.href = loginPath;
            }
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
