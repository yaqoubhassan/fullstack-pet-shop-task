<script lang="ts" setup>
import type { TSideNav } from "@/types";
import { ref, watch } from "vue";
import { useRoute } from "vue-router";

defineProps<TSideNav>();

const route = useRoute();

const activeTitle = ref("Dashboard");
watch(route, (to: any) => {
    activeTitle.value = to.meta.title;
});
</script>
<template>
    <v-navigation-drawer
        elevation="5"
        class="!tw-h-full"
        color="#edf5f1"
        permanent
    >
        <v-list class="!tw-pt-0">
            <v-list-item
                v-for="item in navs"
                :key="item.title"
                :active="activeTitle == item.title"
                :elevation="activeTitle == item.title ? '1' : '0'"
                :active-class="
                    activeTitle == item.title ? 'active-side-nav' : ''
                "
                :prepend-icon="item.icon"
                :title="item.title"
                :to="item.link"
                link
                class="!tw-font-extralight"
            ></v-list-item>

            <!-- <v-list-item
                            prepend-icon="mdi-account-box"
                            title="Account"
                        ></v-list-item>
                        <v-list-item
                            prepend-icon="mdi-gavel"
                            title="Admin"
                        ></v-list-item> -->
        </v-list>

        <!-- <template v-slot:append>
                        <div class="pa-2">
                            <v-btn block> Logout </v-btn>
                        </div>
                    </template> -->
    </v-navigation-drawer>
</template>
