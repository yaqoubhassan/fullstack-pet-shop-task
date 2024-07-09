<script lang="ts" setup>
import Breadcrumb from "@/components/Breadcrumb.vue";
import type { Crumb, TableHeader } from "@/types";
import { formatDate } from "@/utils/date";
import axios from "axios";
import { onMounted, ref } from "vue";
import { useStore } from "vuex";

type TCustomer = {
    name: string;
    email: string;
    phone_number: string;
    address: string;
    first_name: string;
    last_name: string;
    created_date: string;
    date: string;
    is_marketing: number;
};

const store = useStore();
const token = store.state.token;

const crumbs: Crumb[] = [
    {
        title: "",
        disabled: false,
    },
];
const headers: TableHeader[] = [
    { title: "Name", key: "name" },
    { title: "Email", key: "email" },
    { title: "Phone", key: "phone_number" },
    { title: "Address", key: "address" },
    { title: "Date created", key: "date" },
    { title: "Marketing preferences", key: "is_marketing" },
    { title: "", key: "action" },
];
const loading = ref(false);
const customers = ref<TCustomer[]>([]);

const getCustomers = async () => {
    try {
        loading.value = true;
        const { data } = await axios.get(
            "https://pet-shop.buckhill.com.hr/api/v1/admin/user-listing",
            { headers: { Authorization: `Bearer ${token}` } }
        );
        customers.value = (data.data ?? []).map((item: TCustomer) => ({
            ...item,
            name: `${item.first_name} ${item.last_name}`,
            date: formatDate(item.created_date, "MMM DD, YYYY"),
        }));
    } catch (error) {
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    getCustomers();
});
</script>
<template>
    <div>
        <div class="tw-text-[24px]">Customers</div>
        <Breadcrumb :crumbs="crumbs" />

        <v-card
            color="#fff"
            flat
            class="!tw-border-[2px] !tw-border-[#ebebeb]"
            :loading="loading"
        >
            <div class="tw-flex tw-justify-between tw-items-center">
                <div class="tw-px-4">
                    <v-card-subtitle class="!tw-pl-0 !tw-text-[16px]"
                        >All customers</v-card-subtitle
                    >
                </div>
                <div class="tw-flex tw-items-center">
                    <div class="tw-px-4 py-4">
                        <v-menu>
                            <template v-slot:activator="{ props }">
                                <v-btn
                                    size="small"
                                    color="#4EC690"
                                    class="text-white"
                                    prepend-icon="mdi mdi-plus"
                                    v-bind="props"
                                >
                                    ADD NEW CUSTOMER
                                </v-btn>
                            </template>
                            <v-list>
                                <v-list-item> Hello world </v-list-item>
                            </v-list>
                        </v-menu>
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
                                        { title: 'Key 1' },
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
                    :items="customers"
                    :last-icon="'false'"
                    :first-icon="'false'"
                    no-data-text="No customers available"
                    :items-per-page-text="'Rows per page'"
                >
                    <template v-slot:item.name="{ value }">
                        <div class="!tw-flex !tw-space-x-3 !tw-items-center">
                            <v-avatar class="mr-3" size="30">
                                <img
                                    src="https://avatars0.githubusercontent.com/u/9064066?v=4&s=460"
                                    alt="John"
                                />
                            </v-avatar>
                            {{ value }}
                        </div>
                    </template>
                    <template v-slot:item.is_marketing="{ value }">
                        <v-chip :color="value === 1 ? '#4EC690' : '#F09E00'">{{
                            value === 1 ? "Yes" : "No"
                        }}</v-chip>
                    </template>
                    <template v-slot:item.action="{}">
                        <v-btn icon flat>
                            <v-icon>mdi-dots-vertical</v-icon>
                        </v-btn>
                    </template>
                </v-data-table>
            </div>
        </v-card>
    </div>
</template>
