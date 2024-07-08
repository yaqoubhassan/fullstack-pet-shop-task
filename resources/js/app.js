import { createApp } from "vue";
import { aliases, mdi } from "vuetify/iconsets/mdi";
import "./bootstrap";
import App from "./pages/modules/app.vue";

import "@mdi/font/css/materialdesignicons.css";
import { createVuetify } from "vuetify";
import * as components from "vuetify/components";
import * as directives from "vuetify/directives";
import "vuetify/styles";
import router from "./router";

const customTheme = {
    dark: false,
    colors: {
        primary: "#4EC690",
        "primary--text": "#FF00FF",
    },
};
const vuetify = createVuetify({
    components,
    directives,
    // theme: {
    //     defaultTheme: "customTheme",
    //     themes: {
    //         customTheme,
    //     },
    // },
    icons: {
        defaultSet: "mdi",
        aliases,
        sets: {
            mdi,
        },
    },
    //
});

createApp(App).use(vuetify).use(router).mount("#app");
