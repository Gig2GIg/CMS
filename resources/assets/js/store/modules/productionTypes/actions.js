import * as types from '@/store/types';
import axios from 'axios';

export default {
  toggleSpinner({ commit }) {
    commit(types.TOGGLE_SPINNER);
  },

  async fetch({ commit }) {
    try {
      const { data: { data } } = await axios.get('/api/cms/type-products');
      commit(types.FETCH_PRODUCTION_TYPES_SUCCESS, data);
    } catch (e) {
      commit(types.FETCH_PRODUCTION_TYPES_FAILURE);
    }
  },

  async update({ dispatch, commit }, productionType) {
    try {
      dispatch('toggleSpinner');

      // Save changes
      await axios.put(`/api/cms/type-products/update/${productionType.id}`, productionType);
      commit(types.UPDATE_PRODUCTION_TYPE, productionType);

      dispatch('toast/showMessage', 'Production Type updated.', { root: true });
    } catch (e) {
      throw e;
    } finally {
      dispatch('toggleSpinner');
    }
  },

  async destroy({ dispatch, commit }, productionType) {
    try {
      dispatch('toggleSpinner');

      await axios.delete(`/api/cms/type-products/delete/${productionType.id}`);
      commit(types.DELETE_PRODUCTION_TYPE, productionType);

      dispatch('toast/showMessage', 'Production Type deleted.', { root: true });
    } catch(e) {
      dispatch('toast/showError', 'Something went wrong.', { root: true });
    } finally {
      dispatch('toggleSpinner');
    }
  },
};
