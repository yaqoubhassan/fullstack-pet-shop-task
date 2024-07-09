<script lang="ts" setup>
import axios from "axios";
import { reactive, ref } from "vue";
import { useRouter } from "vue-router";
import { useStore } from "vuex";

const router = useRouter();
const store = useStore();

const emit = defineEmits([
    "close-auth-dialog",
    "show-dialog",
    "change-auth-type",
]);

const credentials = reactive({ email: "", password: "" });
const loginError = ref("");
const loading = ref(false);

const login = async () => {
    loginError.value = "";
    loading.value = true;
    try {
        const { email, password } = credentials;
        const { data } = await axios.post(
            "https://pet-shop.buckhill.com.hr/api/v1/admin/login",
            {
                email,
                password,
            }
        );

        store.commit("setToken", data?.data?.token);
        router.push("/admin/dashboard");
        emit("close-auth-dialog");
    } catch (error) {
        const errorData = error as any;
        loginError.value = errorData?.response?.data?.error;
    } finally {
        loading.value = false;
    }
};
</script>

<template>
    <div>
        <v-row
            v-if="Boolean(loginError)"
            class="mb-3 text-center !tw-justify-center text-red"
            >{{ loginError }}
        </v-row>
        <v-row>
            <v-text-field
                label="Email Address*"
                placeholder=""
                variant="outlined"
                v-model="credentials.email"
                :required="true"
                :type="'email'"
            ></v-text-field>
        </v-row>
        <v-row>
            <v-text-field
                label="Password*"
                placeholder=""
                variant="outlined"
                v-model="credentials.password"
                :type="'password'"
                class="mt-5"
            ></v-text-field
        ></v-row>
        <v-row
            ><v-checkbox color="#4EC690" label="Remember me"></v-checkbox
        ></v-row>
        <v-row>
            <v-btn
                color="#4EC690"
                block
                class="text-white"
                @click.stop="login"
                :disabled="
                    Object.values(credentials).some((data) => !Boolean(data)) ||
                    loading
                "
                :loading="loading"
                >LOG IN
            </v-btn>
        </v-row>
        <v-row :justify="'space-between'" class="mt-10">
            <router-link
                @click.stop="emit('close-auth-dialog')"
                class="tw-text-[#1976d2]"
                to="/recover-password"
                >Forgot password?</router-link
            >
            <div
                class="tw-text-[#1976d2] tw-cursor-pointer tw-text-right"
                @click.stop="emit('change-auth-type', 'signup')"
            >
                Don't have an account? Sign up
            </div>
        </v-row>
    </div>
</template>
