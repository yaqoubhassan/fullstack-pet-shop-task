<script lang="ts" setup>
import { ref } from "vue";
import logoImage from "/public/images/green-logo.png";

const logo = ref(logoImage);
</script>
<template>
    <div class="mt-10">
        <v-footer class="!tw-bg-[#ecf5f1] py-10">
            <v-container>
                <v-row class="!tw-items-center">
                    <v-col>
                        <v-img
                            max-height="90"
                            max-width="99"
                            :lazy-src="logo"
                            alt="Tips"
                            :src="logo"
                        />
                    </v-col>
                    <v-col class="!-tw-space-y-3">
                        <v-col class="!tw-text-[20px] !tw-font-[500]"
                            >Petson</v-col
                        >
                        <v-col class="!tw-text-[16px] !tw-font-light"
                            >About us</v-col
                        >
                        <v-col class="!tw-text-[16px] !tw-font-light"
                            >Blog</v-col
                        >
                        <v-col class="!tw-text-[16px] !tw-font-light"
                            >Store locator</v-col
                        >
                    </v-col>
                    <v-col class="!-tw-space-y-3">
                        <v-col class="!tw-text-[20px] !tw-font-[500]"
                            >Contact</v-col
                        >
                        <v-col class="!tw-text-[16px] !tw-font-light"
                            >contact@petson.com</v-col
                        >
                        <v-col class="!tw-text-[16px] !tw-font-light"
                            >+385 000 000</v-col
                        >
                        <v-col class="!tw-text-[16px] !tw-font-light"
                            >35 Shnauzer avenue, Dogville AL</v-col
                        >
                    </v-col>
                    <v-col class="!-tw-space-y-3">
                        <v-col class="!tw-text-[20px] !tw-font-[500]"
                            >Information</v-col
                        >
                        <v-col class="!tw-text-[16px] !tw-font-light"
                            >Ordering and shipping</v-col
                        >
                        <v-col class="!tw-text-[16px] !tw-font-light"
                            >FAQ</v-col
                        >
                        <v-col class="!tw-text-[16px] !tw-font-light"
                            >GDPR statement</v-col
                        >
                    </v-col>
                    <v-col class="!-tw-space-y-3">
                        <v-col class="!tw-text-[16px] !tw-font-light"
                            >If you can't ind answer to your question, please
                            contact our
                            <a
                                class="!tw-text-[#1976D2]"
                                href="mailto:contact@petson.com"
                                >customer service</a
                            >
                        </v-col>
                    </v-col>
                </v-row>
            </v-container>
        </v-footer>
    </div>
</template>
