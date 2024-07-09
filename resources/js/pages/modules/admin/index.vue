<script lang="ts" setup>
import SideNav from "@/components/SideNav.vue";
import type { TNav } from "@/types";

const navs: TNav[] = [
    {
        title: "Dashboard",
        link: "/admin/dashboard",
        icon: "mdi-home",
    },
    {
        title: "All Tickets",
        link: "/admin/tickets",
        icon: "mdi-account",
    },
    {
        title: "Shipment Locator",
        link: "/admin/shipment-locator",
        icon: "mdi-map-marker",
    },
    {
        title: "Customers",
        link: "/admin/customers",
        icon: "mdi-account-multiple-outline",
    },
    {
        title: "Products",
        link: "/admin/products",
        icon: "mdi-folder-outline",
    },
];
</script>

<template>
    <div>
        <SideNav :navs="navs" />
        <router-view />
    </div>
</template>
