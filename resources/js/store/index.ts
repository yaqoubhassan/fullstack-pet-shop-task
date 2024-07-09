import type { InjectionKey } from "vue";
import { createStore, Store } from "vuex";
import createPersistedState from "vuex-persistedstate";

// Create a new store instance.
type State = {
    token: string | null;
};

export const storeKey: InjectionKey<Store<State>> = Symbol();

const store = createStore({
    state(): State {
        return {
            token: null,
        };
    },
    mutations: {
        setToken(state, payload: string): void {
            console.log("we load", payload);
            state.token = payload;
        },
    },
    plugins: [createPersistedState()],
});

export default store;
