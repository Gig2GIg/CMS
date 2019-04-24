import * as types from '@/store/types';
import axios from 'axios';

export default {
  toggleSpinner({ commit }) {
    commit(types.TOGGLE_SPINNER);
  },

  async fetch({ commit }) {
    commit(types.FETCH_CLIENTS_FAILURE);
    return;
    
    try {
      const { data } = await axios.get('/api/v1/clients');
      commit(types.FETCH_CLIENTS_SUCCESS, data);
    } catch (e) {
      commit(types.FETCH_CLIENTS_FAILURE);
    }
  },

  async broadcast({ dispatch }, message) {
    try {
      dispatch('toggleSpinner');

      await axios.post(`/api/v1/notifications/send`, {
        message,
        type: 'client',
      });

      dispatch('toast/showMessage', 'Broadcast sent.', { root: true });
    } catch(e) {
      dispatch('toast/showError', 'The broadcast could not be sent.', { root: true });
    } finally {
      dispatch('toggleSpinner');
    }
  },

  async notify({ dispatch }, { client, message }) {
    try {
      dispatch('toggleSpinner');

      await axios.post(`/api/v1/users/${client.id}/send`, { message });

      dispatch('toast/showMessage', 'Notification sent.', { root: true });
    } catch(e) {
      dispatch('toast/showError', 'The notification could not be sent.', { root: true });
    } finally {
      dispatch('toggleSpinner');
    }
  },

  async destroy({ dispatch, commit }, client) {
    try {
      dispatch('toggleSpinner');

      await axios.delete(`/api/v1/users/${client.id}`);
      commit(types.DELETE_CLIENT, client);

      dispatch('toast/showMessage', 'Client deleted.', { root: true });
    } catch(e) {
      dispatch('toast/showError', 'Something went wrong.', { root: true });
    } finally {
      dispatch('toggleSpinner');
    }
  },
};
