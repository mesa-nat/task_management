import { createApp } from 'vue';
import App from './App.vue';
import axios from 'axios';

// Set the base URL for all Axios requests
axios.defaults.baseURL = 'http://localhost:8000';

createApp(App).mount('#app');