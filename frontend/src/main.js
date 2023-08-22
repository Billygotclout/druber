import "./assets/main.css";

import { createApp } from "vue";
import { createPinia } from "pinia";
import VueGoogleMaps from "@fawmi/vue-google-maps";

import App from "./App.vue";
import router from "./router";

const app = createApp(App);

app.use(createPinia());
app.use(router);
app.use(VueGoogleMaps, {
  load: {
    key: "AIzaSyA4GChW9NnuVZW5ek3GqiHomP7T3n_7Ves",
    libraries: "places", // necessary for places input
  },
});

app.mount("#app");
