import * as types from '@/store/types';
import axios from 'axios';

export default {
  toggleSpinner({ commit }) {
    commit(types.TOGGLE_SPINNER);
  },

  async fetch({ commit }) {
    try {
      const { data } = await axios.get('/api/v1/sellers');
      commit(types.FETCH_RENTERS_SUCCESS, data);
    } catch (e) {
      commit(types.FETCH_RENTERS_FAILURE);
    }
  },

  async broadcast({ dispatch }, message) {
    try {
      dispatch('toggleSpinner');

      await axios.post(`/api/v1/notifications/send`, {
        message,
        type: 'seller',
      });

      dispatch('toast/showMessage', 'Broadcast sent.', { root: true });
    } catch(e) {
      dispatch('toast/showError', 'The broadcast could not be sent.', { root: true });
    } finally {
      dispatch('toggleSpinner');
    }
  },

  async notify({ dispatch }, { renter, message }) {
    try {
      dispatch('toggleSpinner');

      await axios.post(`/api/v1/users/${renter.id}/send`, { message });

      dispatch('toast/showMessage', 'Notification sent.', { root: true });
    } catch(e) {
      dispatch('toast/showError', 'The notification could not be sent.', { root: true });
    } finally {
      dispatch('toggleSpinner');
    }
  },

  async destroy({ dispatch, commit }, renter) {
    try {
      dispatch('toggleSpinner');

      await axios.delete(`/api/v1/users/${renter.id}`);
      commit(types.DELETE_RENTER, renter);

      dispatch('toast/showMessage', 'Renter deleted.', { root: true });
    } catch(e) {
      dispatch('toast/showError', 'Something went wrong.', { root: true });
    } finally {
      dispatch('toggleSpinner');
    }
  },
};
