import * as types from '@/store/types';
import axios from 'axios';

export default {
  toggleSpinner({ commit }) {
    commit(types.TOGGLE_SPINNER);
  },

  async fetch({ commit }) {
    try {
      const { data: { data } } = await axios.get('/api/cms/auditions-pending');
      commit(types.FETCH_AUDITIONS_SUCCESS, data);
    } catch (e) {
      commit(types.FETCH_AUDITIONS_FAILURE);
    }
  },

  async destroy({ dispatch, commit }, audition) {
    try {
      dispatch('toggleSpinner');

      await axios.put(`/api/cms/auditions/banaudition/${audition.id}`);
      commit(types.ACCEPT_BAN, audition);

      dispatch('toast/showMessage', 'Audition Ban.', { root: true });
    } catch(e) {
      dispatch('toast/showError', 'Something went wrong.', { root: true });
    } finally {
      dispatch('toggleSpinner');
    }
  },

  async accept({ dispatch, commit }, audition) {
    try {
      dispatch('toggleSpinner');

      const {data} = await axios.put(`/api/cms/auditions/banaudition/${audition.id}`, {banned: 'yes'});
      commit(types.ACCEPT_BAN, data);

      dispatch('toast/showMessage', 'Audition Ban.', { root: true });
    } catch(e) {
      dispatch('toast/showError', e, { root: true });
    } finally {
      dispatch('toggleSpinner');
    }
  },


  async removeBan({ dispatch, commit }, audition) {
    try {
      dispatch('toggleSpinner');

      await axios.put(`/api/cms/auditions/banaudition/${audition.id}`, {banned: 'pending'});

      commit(types.REMOVE_BAN, audition);

      dispatch('toast/showMessage', 'Audition removed Ban.', { root: true });
    } catch(e) {
      dispatch('toast/showError', e, { root: true });
    } finally {
      dispatch('toggleSpinner');
    }
  },
};
