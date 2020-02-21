import * as types from '@/store/types';
import axios from 'axios';

export default {
  toggleSpinner({ commit }) {
    commit(types.TOGGLE_SPINNER);
  },

  saveToken({ commit }, token) {
    commit(types.SAVE_TOKEN, token);
  },

  removeToken({ commit }) {
    commit(types.LOGOUT);
  },

  async fetchUser({ commit }) {
    try {
      const { data } = await axios.get('/api/admin/me');
      commit(types.FETCH_USER_SUCCESS, { user: data });

      return data;
    } catch (e) {
      commit(types.FETCH_USER_FAILURE);
      return null;
    }
  },

  async login({ dispatch }, credentials) {
    try {
      dispatch('toggleSpinner');

      const { data } = await axios.post('/api/admin/login', credentials);

      dispatch('saveToken', {
        token: data.token,
        remember: credentials.remember,
        is_remember: credentials.remember,
      });

      await dispatch('fetchUser');

      dispatch('toast/showMessage', 'You are Log In!', { root: true });

      return true;
    } catch (e) {
      const error = e.response.data.message;
      dispatch('toast/showError', error, { root: true });

      return false;
    } finally {
      dispatch('toggleSpinner');
    }
  },

  async forgot({ dispatch }, email) {
    try {
      dispatch('toggleSpinner');

      await axios.post('/api/remember/admin', email);
      dispatch('toast/showMessage', 'Password sent!', { root: true });

      return true;
    } catch (e) {
      const error = e.response.data.data;
      dispatch('toast/showError', error, { root: true });

      return false;
    } finally {
      dispatch('toggleSpinner');
    }
  },

  async logout({ commit }) {
    try {
      await axios.post('/api/admin/logout');
    } catch (e) {
      throw e;
    } finally {
      commit(types.LOGOUT);
    }
  },

  async broadcast({ dispatch }, notification) {
    try {
      await axios.post('/api/cms/send-notifications', notification);

      dispatch('toast/showMessage', 'Notification sent!', { root: true });
    } catch (e) {
      throw e;
    }
  },
};
