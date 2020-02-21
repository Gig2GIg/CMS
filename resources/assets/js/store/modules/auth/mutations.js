import * as types from '@/store/types';
import Cookies from "js-cookie";

export default {
  [types.TOGGLE_SPINNER] (state) {
    state.isLoading = !state.isLoading;
  },

  [types.FETCH_USER_SUCCESS] (state, { user }) {
    state.user = user;
    localStorage.setItem('user', JSON.stringify(user));
  },

  [types.FETCH_USER_FAILURE] (state) {
    state.token = null;
    state.is_remember = null;
    Cookies.remove("token");    
    localStorage.removeItem('token');
    localStorage.removeItem('user');
    localStorage.removeItem('is_remember');
    
  },

  [types.SAVE_TOKEN] (state, { token, remember, is_remember }) {
    state.token = token;
    state.is_remember = is_remember;    
    Cookies.set("token", token, { expires: remember ? 365 : 1 });
    localStorage.setItem('token', token);
    localStorage.setItem('is_remember', is_remember ? 1 : 0);
  },

  [types.LOGOUT] (state) {
    state.user = null;
    state.token = null;
    state.is_remember = null;
    Cookies.remove("token");
    localStorage.removeItem('token');
    localStorage.removeItem('is_remember');
    localStorage.removeItem('user');    
  },
};
