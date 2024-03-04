/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

import { createApp } from 'vue';

// Components
import BoardComponent from './components/BoardComponent.vue';
import HomepageComponent from './components/HomepageComponent.vue';
import PrivateMatchComponent from './components/PrivateMatchComponent.vue';
import RandomMatchComponent from './components/RandomMatchComponent.vue';

// Vuetify
import 'vuetify/styles';
import { createVuetify } from 'vuetify';
import * as components from 'vuetify/components';
import * as directives from 'vuetify/directives';

const vuetify = createVuetify({
  components,
  directives
});

/**
 * VUE 2 SYNTAX BELOW
 */

/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

// const files = require.context('./', true, /\.vue$/i)
// files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default))

// Vue.component('example-component', require('./components/ExampleComponent.vue').default);
// Vue.component('board-component', require('./components/BoardComponent.vue').default);


const app = createApp({});

app.use(vuetify);

app.component('board-component', BoardComponent);
app.component('homepage-component', HomepageComponent);
app.component('private-match-component', PrivateMatchComponent);
app.component('random-match-component', RandomMatchComponent);

app.mount('#app');