import Vue from 'vue';
import Base from './base';
import axios from 'axios';
import Routes from './routes';
import VueRouter from 'vue-router';
import VueJsonPretty from 'vue-json-pretty';
import 'vue-json-pretty/lib/styles.css';
import moment from 'moment-timezone';

window.Popper = require('popper.js').default;
require('bootstrap');

let token = document.head.querySelector('meta[name="csrf-token"]');

if (token) {
    axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
}

Vue.use(VueRouter);

moment.tz.setDefault(Telescope.timezone);

window.Telescope.basePath = '/' + window.Telescope.path;

let routerBasePath = window.Telescope.basePath + '/';

if (window.Telescope.path === '' || window.Telescope.path === '/') {
    routerBasePath = '/';
    window.Telescope.basePath = '';
}

axios.interceptors.request.use((config) => {
    if (!/telescope-api\/(session|login|logout|applications)/.test(config.url) && /\?/.test(config.url)) {
        config.url += '&application_uuid=' + Telescope.currentAppUuid
    }
    return config;
}, (error) => {
    return Promise.reject(error);
});

const router = new VueRouter({
    routes: Routes,
    mode: 'history',
    base: routerBasePath,
});

router.beforeEach(async (to, from, next) => {
    if (to.name == 'login' || to.name == 'logout') {
        return next()
    }

    const { status, data } = await axios.get(Telescope.basePath + '/telescope-api/session')
        .catch((err) => {
            return Promise.resolve(err.response);
        })

    if (status != 200) {
        next({ name: 'login' })
    }

    Telescope.session.user = data.user

    next()
});

Vue.component('vue-json-pretty', VueJsonPretty);
Vue.component('related-entries', require('./components/RelatedEntries.vue').default);
Vue.component('index-screen', require('./components/IndexScreen.vue').default);
Vue.component('preview-screen', require('./components/PreviewScreen.vue').default);
Vue.component('alert', require('./components/Alert.vue').default);
Vue.component('copy-clipboard', require('./components/CopyClipboard.vue').default);

Vue.mixin(Base);

new Vue({
    el: '#telescope',

    router,

    data() {
        return {
            alert: {
                type: null,
                autoClose: 0,
                message: '',
                confirmationProceed: null,
                confirmationCancel: null
            },

            states: {
                ddOpen: false,
                ready: false,
                applicationRetrieved: false
            },

            autoLoadsNewEntries: localStorage.autoLoadsNewEntries === '1',

            recording: Telescope.recording,

            applications: {},

            currentApplication: null,
        };
    },

    created() {
        window.addEventListener('keydown', this.keydownListener);
    },

    destroyed() {
        window.removeEventListener('keydown', this.keydownListener);
    },

    async beforeUpdate() {
        if (!this.states.ready) return;
        if (this.states.applicationRetrieved && this.currentApplication != null) return;

        await this.retrieveApp()

        let uuid = sessionStorage.getItem('telescope.currentApplication')
        if (!uuid) {
            uuid = Object.keys(this.applications)[0]
        }

        this.selectApp(uuid)
    },

    async mounted() {
        this.states.ready = true
    },

    computed: {
        isAuthenticated() {
            return this.$route.name !== 'login';
        },

        isFullPageLayout() {
            return this.$route.meta.fullPage || false;
        },

        hasApplications() {
            return ("0" in Object.keys(this.applications));
        },
    },

    methods: {
        async retrieveApp() {
            if (!this.isAuthenticated) {
                return;
            }

            const response = await axios.get(Telescope.basePath + '/telescope-api/applications')
            this.applications = response.data
            this.states.applicationRetrieved = true
        },

        selectApp(uuid) {
            this.states.ddOpen = false;
            if (uuid == Telescope.currentAppUuid) {
                return;
            }
            if (!uuid) {
                uuid = Telescope.currentAppUuid;
            }
            sessionStorage.setItem('telescope.currentApplication', uuid)
            Telescope.currentAppUuid = uuid;
            this.currentApplication = this.applications[uuid]

            this.$children[this.$children.length - 1].$refs.indexScreen.reload()
        },

        autoLoadNewEntries() {
            if (!this.autoLoadsNewEntries) {
                this.autoLoadsNewEntries = true;
                localStorage.autoLoadsNewEntries = 1;
            } else {
                this.autoLoadsNewEntries = false;
                localStorage.autoLoadsNewEntries = 0;
            }
        },

        toggleRecording() {
            axios.post(Telescope.basePath + '/telescope-api/toggle-recording');

            window.Telescope.recording = !Telescope.recording;
            this.recording = !this.recording;
        },

        clearEntries(shouldConfirm = true) {
            if (shouldConfirm && !confirm('Are you sure you want to delete all Telescope data?')) {
                return;
            }

            axios.delete(Telescope.basePath + '/telescope-api/entries').then((response) => location.reload());
        },

        keydownListener(event) {
            if (event.metaKey && event.key === 'k') {
                this.clearEntries(false);
            }
        },
    },
});
