import * as types from '@/store/types';
import axios from 'axios';

export default {
  toggleSpinner({ commit }) {
    commit(types.TOGGLE_SPINNER);
  },

  async fetch({ commit }) {
    try {
      const { data: { data } } = await axios.get('/api/cms/marketplace-featured-listing');
      commit(types.FETCH_AUDITIONS_SUCCESS, data);
    } catch (e) {
      commit(types.FETCH_AUDITIONS_FAILURE);
    }
  },
};
