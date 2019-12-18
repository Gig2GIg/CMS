import * as types from '@/store/types';
import axios from 'axios';

export default {
  toggleSpinner({ commit }) {
    commit(types.TOGGLE_SPINNER);
  },

  async fetch({ commit }) {
    try {
      const { data: { data } } = await axios.get('/api/cms/auditions');
      commit(types.FETCH_AUDITIONS_SUCCESS, data);
    } catch (e) {
      commit(types.FETCH_AUDITIONS_FAILURE);
    }
  },

  async destroy({ dispatch, commit }, audition) {
    try {
      dispatch('toggleSpinner');

      await axios.delete(`/api/cms/auditions/${audition.id}`);
      commit(types.DELETE_AUDITION, audition);

      dispatch('toast/showMessage', 'Audition deleted.', { root: true });
    } catch(e) {
      dispatch('toast/showError', 'Something went wrong.', { root: true });
    } finally {
      dispatch('toggleSpinner');
    }
  },
};
