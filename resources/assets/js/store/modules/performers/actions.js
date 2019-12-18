import * as types from '@/store/types';
import axios from 'axios';

export default {
  toggleSpinner({ commit }) {
    commit(types.TOGGLE_SPINNER);
  },

  async fetch({ commit }, audition) {
    try {
      const { data: { data } } = await axios.get(`/api/cms/performers/auditions/${audition}`);
      commit(types.FETCH_PERFORMERS_SUCCESS, data);
    } catch (e) {
      commit(types.FETCH_PERFORMERS_FAILURE);
    }
  },

  async sendPassword({ dispatch }, performer) {
    try {
      dispatch('toggleSpinner');

      await axios.post('/api/cms/remember', {
        email: performer.email,
      });

      dispatch('toast/showMessage', 'Password sent.', { root: true });
    } catch(e) {
      dispatch('toast/showError', 'Something went wrong.', { root: true });
    } finally {
      dispatch('toggleSpinner');
    }
  },

  async notify(_, { client, message }) {
    try {
      await axios.post(`/api/cms/send-notifications/users/${client.user_id}`, {
        'title': message,
      });

      dispatch('toast/showMessage', 'Notification sent!', { root: true });
    } catch (e) {
      throw e;
    }
  },

  async destroy({ dispatch, commit }, performer) {
    try {
      dispatch('toggleSpinner');

      // Delete performer
      await axios.delete(`/api/cms/slots/${performer.id}`);
      commit(types.DELETE_PERFORMER, performer);

      dispatch('toast/showMessage', 'Performer deleted.', { root: true });
    } catch(e) {
      dispatch('toast/showError', 'Something went wrong.', { root: true });
    } finally {
      dispatch('toggleSpinner');
    }
  },
};
