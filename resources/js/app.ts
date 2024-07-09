import App from "@/app.vue";
import { createApp } from "vue";
import { aliases, mdi } from "vuetify/iconsets/mdi";
import "./bootstrap";

import router from "@/router";
import store from "@/store";
import "@mdi/font/css/materialdesignicons.css";
import { createVuetify } from "vuetify";
import * as components from "vuetify/components";
import * as directives from "vuetify/directives";
import "vuetify/styles";

const vuetify = createVuetify({
    components,
    directives,
    icons: {
        defaultSet: "mdi",
        aliases,
        sets: {
            mdi,
        },
    },
});

createApp(App).use(vuetify).use(router).use(store).mount("#app");
