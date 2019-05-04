import * as types from '@/store/types';
import axios from 'axios';

export default {
  toggleSpinner({ commit }) {
    commit(types.TOGGLE_SPINNER);
  },

  async fetch({ commit }, audition) {
    try {
      const { data: { data } } = await axios.get(`/api/cms/auditions/${audition}/contributors`);
      commit(types.FETCH_CONTRIBUTORS_SUCCESS, data);
    } catch (e) {
      commit(types.FETCH_CONTRIBUTORS_FAILURE);
    }
  },
};
