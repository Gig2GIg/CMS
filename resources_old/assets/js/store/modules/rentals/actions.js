import * as types from '@/store/types';
import axios from 'axios';

export default {
  async fetch({ commit }) {
    try {
      const { data: { data } } = await axios.get('/api/v1/rentals');
      commit(types.FETCH_RENTALS_SUCCESS, data);
    } catch (e) {
      commit(types.FETCH_RENTALS_FAILURE);
    }
  },
};
