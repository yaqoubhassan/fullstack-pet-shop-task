<template>
    <v-dialog v-model="show" width="542">
        <v-card
            flat
            elevation="0"
            class="!tw-px-[20px] !tw-py-[49px] lg:!tw-px-[73px]"
        >
            <v-card-item class="!tw-justify-center">
                <v-container>
                    <v-col
                        :align="'center'"
                        :justify="'center'"
                        class="!tw-bg-primary !tw-h-[92px] !tw-w-[92px] !tw-rounded-full"
                    >
                        <v-img
                            lazy-src="https://pet-shop.buckhill.com.hr/_nuxt/pet-logo.4113e198.svg"
                            alt="Logo"
                            max-height="42"
                            max-width="47"
                            src="https://pet-shop.buckhill.com.hr/_nuxt/pet-logo.4113e198.svg"
                        />
                        <h1 class="text-white">petson.</h1>
                    </v-col>
                    <v-card-title
                        class="!tw-text-center text-h5 mt-3 mb-5"
                        :justify="'center'"
                    >
                        <span v-if="authType === 'login'">Log In</span>
                        <span v-else>Sign up</span>
                    </v-card-title>
                </v-container>
            </v-card-item>
            <Login
                v-if="authType === 'login'"
                @change-auth-type="changeAuthType"
            />
            <Register v-else @change-auth-type="changeAuthType" />

            <!-- <v-card-actions>
                <v-spacer></v-spacer>

                <v-btn
                    text="Close Dialog"
                    @click="isActive.value = false"
                ></v-btn>
            </v-card-actions> -->
        </v-card>
    </v-dialog>
</template>
<script>
import Login from "../authentication/Login.vue";
import Register from "../authentication/Register.vue";
export default {
    props: {
        value: Boolean,
        title: String,
    },
    components: {
        Login,
        Register,
    },
    created: function () {
        this.showTemp = true;
    },
    data() {
        return {
            authType: "login",
        };
    },
    computed: {
        show: {
            get() {
                return this.value;
            },
            set(value) {
                this.$emit("input", value);
                this.authType = "login";
            },
        },
    },
    methods: {
        changeAuthType(authType) {
            this.authType = authType;
        },
    },
};
</script>
