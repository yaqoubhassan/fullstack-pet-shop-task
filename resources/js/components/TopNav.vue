<script lang="ts" setup>
import { ref, watchEffect } from "vue";
import { useRouter } from "vue-router";
import { useStore } from "vuex";

const router = useRouter();

const store = useStore();

const token = ref(store?.state?.token);

const emit = defineEmits(["show-dialog"]);

const showDialog = (value: boolean) => {
    emit("show-dialog", value);
};
const logout = () => {
    router.push("/");
    store.commit("setToken", null);
};

watchEffect(() => {
    const stateToken = store?.state?.token;
    token.value = stateToken;
});
</script>

<template>
    <v-toolbar prominent class="!tw-h-[80px] !tw-bg-primary">
        <v-container>
            <v-row no-gutters :justify="'space-between'" :align="'center'">
                <v-col cols="12" lg="2" md="1">
                    <v-row :align="'center'">
                        <v-app-bar-nav-icon
                            color="white"
                            class="lg:!tw-hidden"
                        ></v-app-bar-nav-icon>
                        <v-img
                            lazy-src="https://pet-shop.buckhill.com.hr/img/logo.svg"
                            alt="Logo"
                            max-height="29"
                            max-width="31"
                            src="https://pet-shop.buckhill.com.hr/img/logo.svg" />
                        <v-img
                            lazy-src="https://pet-shop.buckhill.com.hr/img/logo-text.svg"
                            alt="Logo Name"
                            max-height="14"
                            max-width="46"
                            src="https://pet-shop.buckhill.com.hr/img/logo-text.svg"
                    /></v-row>
                </v-col>
                <v-col cols="12" lg="6" class="hidden-md-and-down">
                    <v-row :align="'center'" class="tw-space-x-8 tw-text-white">
                        <v-btn flat size="large"> PRODUCTS </v-btn>
                        <v-btn flat size="large"> PROMOTIONS </v-btn>
                        <v-btn flat size="large"> BLOGS </v-btn>
                    </v-row>
                </v-col>
                <v-col cols="12" lg="4">
                    <v-row
                        :align="'center'"
                        :justify="'end'"
                        class="tw-space-x-5"
                    >
                        <v-btn
                            flat
                            size="large"
                            class="!tw-border !tw-border-white !tw-text-white !tw-h-[48px]"
                            prepend-icon="mdi-cart"
                        >
                            CART (0)
                        </v-btn>
                        <v-btn
                            flat
                            size="large"
                            class="!tw-border !tw-border-white !tw-text-white hidden-md-and-down !tw-h-[48px]"
                            @click.stop="
                                !Boolean(token) ? showDialog(true) : logout()
                            "
                        >
                            {{ !Boolean(token) ? "Login" : "Logout" }}
                        </v-btn>
                        <v-avatar
                            class="!tw-border !tw-border-white"
                            v-if="Boolean(token)"
                            ><img
                                src="https://avatars0.githubusercontent.com/u/9064066?v=4&s=460"
                                alt="John"
                        /></v-avatar>
                    </v-row>
                </v-col>
            </v-row>
        </v-container>
    </v-toolbar>
</template>
