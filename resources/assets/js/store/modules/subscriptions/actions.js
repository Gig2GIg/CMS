import * as types from '@/store/types';
import axios from 'axios';

export default {
  toggleSpinner({ commit }) {
    commit(types.TOGGLE_SPINNER);
  },

  async fetch({ commit }) {
    try {
      const { data: { data } } = await axios.get('/api/cms/subscriptions');
      commit(types.FETCH_SUBSCRIPTIONS_SUCCESS, data);
    } catch (e) {
      commit(types.FETCH_SUBSCRIPTIONS_FAILURE);
    }
  },

  async update({ dispatch, commit }, subscription) {
    try {
      dispatch('toggleSpinner');

      // Save changes
      await axios.post('/api/cms/subscriptions/users', subscription);
      commit(types.UPDATE_SUBSCRIPTION, subscription);

      dispatch('toast/showMessage', 'Subscription updated.', { root: true });
    } catch (e) {
      throw e;
    } finally {
      dispatch('toggleSpinner');
    }
  },
};
