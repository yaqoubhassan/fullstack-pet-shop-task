import { createRouter, createWebHistory } from "vue-router";

import { useStore } from "vuex";
import ForgotPassword from "../pages/authentication/ForgotPassword.vue";
import AdminCustomers from "../pages/modules/admin/Customers.vue";
import AdminDashboard from "../pages/modules/admin/Dashboard.vue";
import AdminProducts from "../pages/modules/admin/Products.vue";
import AdminShipmentLocator from "../pages/modules/admin/ShipmentLocator.vue";
import AdminTickets from "../pages/modules/admin/Tickets.vue";
import Admin from "../pages/modules/admin/index.vue";
import Home from "../pages/modules/visitor/Home.vue";
import NotFound from "../pages/not-found/index.vue";

const routes = [
    {
        path: "/",
        component: Home,
        name: "home",
        // redirect: { name: "admin-dashboard" },
    },
    {
        path: "/recover-password",
        component: ForgotPassword,
        name: "recover-password",
    },
    {
        path: "/admin",
        beforeEnter: (to: any, from: any) => {
            const store = useStore();
            if (
                // make sure the user is authenticated
                !store.state.token &&
                // ❗️ Avoid an infinite redirect
                to.name !== "home"
            ) {
                // redirect the user to the login page
                return { name: "home" };
            }

            return true;
        },
        component: Admin,
        children: [
            {
                path: "dashboard",
                name: "admin-dashboard",
                meta: { title: "Dashboard" },
                component: AdminDashboard,
            },
            {
                path: "tickets",
                name: "admin-tickets",
                meta: { title: "All Tickets" },
                component: AdminTickets,
            },
            {
                path: "shipment-locator",
                name: "admin-shipment-locator",
                meta: { title: "Shipment Locator" },
                component: AdminShipmentLocator,
            },
            {
                path: "customers",
                name: "admin-customers",
                meta: { title: "Customers" },
                component: AdminCustomers,
            },
            {
                path: "products",
                name: "admin-products",
                meta: { title: "Products" },
                component: AdminProducts,
            },
        ],
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
