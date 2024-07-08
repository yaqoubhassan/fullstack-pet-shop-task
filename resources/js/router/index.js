import { createRouter, createWebHistory } from "vue-router";

import AdminCustomers from "../pages/modules/admin/Customers.vue";
import AdminDashboard from "../pages/modules/admin/Dashboard.vue";
import AdminProducts from "../pages/modules/admin/Products.vue";
import AdminShipmentLocator from "../pages/modules/admin/ShipmentLocator.vue";
import AdminTickets from "../pages/modules/admin/Tickets.vue";
import NotFound from "../pages/not-found/index.vue";

const routes = [
    {
        path: "/",
        redirect: { name: "admin-dashboard" },
    },
    {
        path: "/admin/dashboard",
        name: "admin-dashboard",
        meta: { title: "Dashboard" },
        component: AdminDashboard,
    },
    {
        path: "/admin/tickets",
        name: "admin-tickets",
        meta: { title: "All Tickets" },
        component: AdminTickets,
    },
    {
        path: "/admin/shipment-locator",
        name: "admin-shipment-locator",
        meta: { title: "Shipment Locator" },
        component: AdminShipmentLocator,
    },
    {
        path: "/admin/customers",
        name: "admin-customers",
        meta: { title: "Customers" },
        component: AdminCustomers,
    },
    {
        path: "/admin/products",
        name: "admin-products",
        meta: { title: "Products" },
        component: AdminProducts,
    },
    {
        path: "/:pathMatch(.*)*",
        component: NotFound,
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

export default router;
