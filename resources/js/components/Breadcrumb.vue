<script lang="ts" setup>
import type { Crumb } from "@/types";

const props = defineProps<{
    crumbs: Crumb[];
}>();
console.log("props", props);
</script>

<template>
    <div class="mb-6">
        <v-breadcrumbs
            class="!tw-pl-0 !tw-pt-0 !tw-text-[14px]"
            active-color="blue"
            :items="[
                {
                    title: 'Dashboard',
                    active: true,
                    href: '/admin/dashboard',
                },
                ...crumbs,
            ]"
            divider="/"
        ></v-breadcrumbs>
        <v-divider class="border-opacity-100"></v-divider>
    </div>
</template>
