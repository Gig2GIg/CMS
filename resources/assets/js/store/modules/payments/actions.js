import * as types from '@/store/types';
import axios from 'axios';

export default {
  async fetch({ commit }) {
    try {
      const { data } = await axios.get('/api/cms/subcribers-payments/plans');
      commit(types.FETCH_PAYMENTS_SUCCESS, data);
    } catch (e) {
      commit(types.FETCH_PAYMENTS_FAILURE);
    }
  },
};
