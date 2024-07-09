<script lang="ts" setup>
import Footer from "@/components/Footer.vue";
import TopNav from "@/components/TopNav.vue";
import AuthenticationDialog from "@/pages/authentication/index.vue";
import { ref, watchEffect } from "vue";
import { useStore } from "vuex";

const store = useStore();
const token = ref(store?.state?.token);

const showAuthDialog = ref(false);

const showDialog = (value: boolean = true): void => {
    showAuthDialog.value = value;
};

watchEffect(() => {
    const stateToken = store?.state?.token;
    token.value = stateToken;
});
</script>
<template>
    <v-app>
        <TopNav @show-dialog="showDialog" />
        <v-card flat class="!tw-h-full !tw-rounded-none">
            <v-layout class="!tw-h-full">
                <v-main class="!tw-pl-[180px] !tw-bg-[#f9f9f9]">
                    <v-container> <router-view /></v-container>
                </v-main>
            </v-layout>
        </v-card>
        <Footer v-if="!Boolean(token)" />
        <AuthenticationDialog
            v-model="showAuthDialog"
            @show-dialog="showDialog"
        />
    </v-app>
</template>
