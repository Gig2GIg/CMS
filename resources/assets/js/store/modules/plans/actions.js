import * as types from '@/store/types';
import axios from 'axios';

export default {
  async fetch({ commit }) {
    try {
      const { data } = await axios.get('/api/users/listSubscriptionPlans');
      const planList = data.data ? data.data : [];
      commit(types.FETCH_PLANS_SUCCESS, planList);
    } catch (e) {
      commit(types.FETCH_PLANS_FAILURE);
    }
  },
};
