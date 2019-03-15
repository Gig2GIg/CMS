import * as types from '@/store/types';
import axios from 'axios';

export default {
  toggleSpinner({ commit }) {
    commit(types.TOGGLE_SPINNER);
  },

  async fetch({ commit }) {
    try {
      const { data: { data } } = await axios.get('/api/v1/products');
      commit(types.FETCH_PRODUCTS_SUCCESS, data);
    } catch (e) {
      commit(types.FETCH_PRODUCTS_FAILURE);
    }
  },

  async update({ dispatch, commit }, category) {
    try {
      dispatch('toggleSpinner');

      await axios.put(`/api/v1/products/${category.id}`, category);
      commit(types.UPDATE_PRODUCT, category);

      dispatch('toast/showMessage', 'Product updated.', { root: true });
    } catch (e) {
      throw e;
    } finally {
      dispatch('toggleSpinner');
    }
  },

  async destroy({ dispatch, commit }, product) {
    try {
      dispatch('toggleSpinner');

      await axios.delete(`/api/v1/products/${product.id}`);
      commit(types.DELETE_PRODUCT, product);

      dispatch('toast/showMessage', 'Product deleted.', { root: true });
    } catch(e) {
      dispatch('toast/showError', 'Something went wrong.', { root: true });
    } finally {
      dispatch('toggleSpinner');
    }
  },
};
