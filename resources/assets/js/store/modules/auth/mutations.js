import * as types from '@/store/types';
import Cookies from "js-cookie";

export default {
  [types.TOGGLE_SPINNER] (state) {
    state.isLoading = !state.isLoading;
  },

  [types.FETCH_USER_SUCCESS] (state, { user }) {
    state.user = user;
  },

  [types.FETCH_USER_FAILURE] (state) {
    state.token = null;
    Cookies.remove("token");
  },

  [types.SAVE_TOKEN] (state, { token, remember }) {
    state.token = token;
    Cookies.set("token", token, { expires: remember ? 7 : null });
  },

  [types.LOGOUT] (state) {
    state.user = null;
    state.token = null;

    Cookies.remove("token");
  },
};
