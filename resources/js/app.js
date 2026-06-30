

import { createApp } from 'vue';
import App from '@/components/App.vue';
import router from '@/router/index.js';
import VueKonva from 'vue-konva';

createApp(App).use(router).use(VueKonva).mount('#app');
