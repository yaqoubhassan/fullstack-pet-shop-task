<template>
    <v-app>
        <div>Products</div>
        <Breadcrumb :crumbs="crumbs" />
        <v-card color="#fff" elevation="1">
            <div class="tw-flex tw-justify-between tw-items-center">
                <div class="tw-px-4">
                    <v-card-subtitle class="!tw-pl-0"
                        >All products</v-card-subtitle
                    >
                </div>
                <div class="tw-flex tw-items-center">
                    <div class="tw-px-4 mb-3">
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
                        <v-card-subtitle>Filter</v-card-subtitle>
                    </div>
                </div>
            </div>
            <v-divider class="border-opacity-100"></v-divider>
            <div class="">
                <v-data-table :headers="headers" :items="vegetables">
                    <template v-slot:item.calories="{ value }">
                        {{ value }}AAA
                    </template></v-data-table
                >
            </div>
            <!-- <v-card-item class="!tw-flex">
                <v-card-subtitle>All products</v-card-subtitle>
                <v-card-subtitle>All products</v-card-subtitle>
            </v-card-item> -->
        </v-card>
    </v-app>
</template>
<script>
import Breadcrumb from "../../../components/Breadcrumb.vue";

export default {
    data() {
        return {
            showAuthDialog: false,
            crumbs: [
                {
                    title: "",
                    disabled: false,
                },
            ],
            headers: [
                { title: "Image", key: "image" },
                { title: "Name", key: "name" },
                { title: "Brand", key: "brand" },
                { title: "Category", key: "category" },
                { title: "Date created", key: "date" },
            ],
            vegetables: [
                {
                    name: "Spinach",
                    image: 23,
                    brand: 0.4,
                    category: 3.6,
                    date: 2.9,
                },
                {
                    name: "Acorn Squash",
                    image: 40,
                    brand: 0.1,
                    category: 10,
                    date: 1,
                },
                {
                    name: "Okra",
                    image: 33,
                    brand: 0.2,
                    category: 7.5,
                    date: 1.9,
                },
            ],
        };
    },
    components: {
        Breadcrumb,
    },
};
</script>
