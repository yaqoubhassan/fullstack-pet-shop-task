<script lang="ts" setup>
import Breadcrumb from "@/components/Breadcrumb.vue";
import type { Crumb, TableHeader } from "@/types";
import { ref } from "vue";

const crumbs: Crumb[] = [
    {
        title: "",
        disabled: false,
    },
];
const headers: TableHeader[] = [
    { title: "Image", key: "image" },
    { title: "Name", key: "name" },
    { title: "Brand", key: "brand" },
    { title: "Category", key: "category" },
    { title: "Date created", key: "date" },
];
const products = ref([]);
</script>
<template>
    <div>
        <div class="tw-text-[24px]">Products</div>
        <Breadcrumb :crumbs="crumbs" />
        <v-card color="#fff" flat class="!tw-border-[2px] !tw-border-[#ebebeb]">
            <div class="tw-flex tw-justify-between tw-items-center">
                <div class="tw-px-4">
                    <v-card-subtitle class="!tw-pl-0 !tw-text-[16px]"
                        >All products</v-card-subtitle
                    >
                </div>
                <div class="tw-flex tw-items-center">
                    <div class="tw-px-4 py-4">
                        <v-btn
                            size="small"
                            color="#4EC690"
                            class="text-white"
                            prepend-icon="mdi mdi-plus"
                            >ADD NEW PRODUCT</v-btn
                        >
                    </div>
                    <v-divider class="border-opacity-100" vertical></v-divider>
                    <div class="tw-px-4">
                        <v-menu>
                            <template v-slot:activator="{ props }">
                                <v-card-subtitle
                                    color="primary"
                                    class="tw-cursor-pointer"
                                    dark
                                    v-bind="props"
                                    append-icon=""
                                >
                                    Filter
                                    <v-icon
                                        class="mdi mdi-chevron-down"
                                    ></v-icon>
                                </v-card-subtitle>
                            </template>

                            <v-list>
                                <v-list-item
                                    v-for="(item, index) in [
                                        { title: 'Filter A' },
                                    ]"
                                    :key="index"
                                >
                                    <v-list-item-title>{{
                                        item.title
                                    }}</v-list-item-title>
                                </v-list-item>
                            </v-list>
                        </v-menu>
                    </div>
                </div>
            </div>
            <v-divider class="border-opacity-100"></v-divider>
            <div class="">
                <v-data-table
                    :headers="headers"
                    :items="products"
                    :last-icon="'false'"
                    :first-icon="'false'"
                    :items-per-page-text="'Rows per page'"
                >
                </v-data-table>
            </div>
        </v-card>
    </div>
</template>
