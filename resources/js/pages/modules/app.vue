<template>
    <v-app>
        <v-toolbar prominent class="!tw-h-[80px] !tw-bg-primary">
            <v-container>
                <v-row no-gutters :justify="'space-between'" :align="'center'">
                    <v-col cols="12" lg="3" md="1">
                        <v-row :align="'center'">
                            <v-app-bar-nav-icon
                                color="white"
                                class="lg:!tw-hidden"
                            ></v-app-bar-nav-icon>
                            <v-img
                                lazy-src="https://pet-shop.buckhill.com.hr/img/logo.svg"
                                alt="Logo"
                                max-height="29"
                                max-width="31"
                                src="https://pet-shop.buckhill.com.hr/img/logo.svg" />
                            <v-img
                                lazy-src="https://pet-shop.buckhill.com.hr/img/logo-text.svg"
                                alt="Logo Name"
                                max-height="14"
                                max-width="46"
                                src="https://pet-shop.buckhill.com.hr/img/logo-text.svg"
                        /></v-row>
                    </v-col>
                    <v-col cols="12" lg="6" class="hidden-md-and-down">
                        <v-row
                            :align="'center'"
                            class="tw-space-x-8 tw-text-white"
                        >
                            <v-btn flat size="large"> PRODUCTS </v-btn>
                            <v-btn flat size="large"> PROMOTIONS </v-btn>
                            <v-btn flat size="large"> BLOGS </v-btn>
                        </v-row>
                    </v-col>
                    <v-col cols="12" lg="3">
                        <v-row
                            :align="'center'"
                            :justify="'end'"
                            class="tw-space-x-8"
                        >
                            <v-btn
                                flat
                                size="large"
                                class="!tw-border !tw-border-white !tw-text-white !tw-h-[48px]"
                                prepend-icon="mdi-cart"
                            >
                                CART (0)
                            </v-btn>
                            <v-btn
                                flat
                                size="large"
                                class="!tw-border !tw-border-white !tw-text-white hidden-md-and-down !tw-h-[48px]"
                                @click.stop="showAuthDialog = true"
                            >
                                Login
                            </v-btn>
                        </v-row>
                    </v-col>
                </v-row>
            </v-container>
        </v-toolbar>
        <v-card class="!tw-h-full !tw-rounded-none">
            <v-layout class="!tw-h-full">
                <v-navigation-drawer
                    elevation="5"
                    class="!tw-h-full"
                    color="#edf5f1"
                    permanent
                >
                    <v-list class="!tw-pt-0">
                        <v-list-item
                            v-for="item in nav"
                            :key="item.title"
                            :active="activeTitle == item.title"
                            :elevation="activeTitle == item.title ? '1' : '0'"
                            :active-class="
                                activeTitle == item.title
                                    ? 'active-side-nav'
                                    : ''
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
                <v-main class="!tw-pl-[180px]">
                    <v-container> <router-view /></v-container>
                </v-main>
            </v-layout>
        </v-card>
        <AuthenticationDialog v-model="showAuthDialog" />
    </v-app>
</template>
<script>
import AuthenticationDialog from "../authentication/index.vue";

export default {
    data() {
        return {
            showAuthDialog: false,
            activeTitle: "Dashboard",
            nav: [
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
            ],
        };
    },
    components: {
        AuthenticationDialog,
    },
    watch: {
        $route(to, from) {
            this.activeTitle = to?.meta?.title;
        },
    },
};
</script>
