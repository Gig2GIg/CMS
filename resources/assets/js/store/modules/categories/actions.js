import * as types from '@/store/types';
import axios from 'axios';

export default {
  toggleSpinner({ commit }) {
    commit(types.TOGGLE_SPINNER);
  },

  async init({ dispatch }) {
    await dispatch('fetch');
  },

  async fetch({ commit }) {
    try {
      const { data: { data } } = await axios.get('/api/cms/marketplace_categories');
      commit(types.FETCH_CATEGORIES_SUCCESS, data);
    } catch (e) {
      commit(types.FETCH_CATEGORIES_FAILURE);
    }
  },

  async update({ dispatch, commit }, category) {
    try {
      dispatch('toggleSpinner');

      // Save changes
      await axios.put(`/api/cms/marketplace_categories/update/${category.id}`, category);
      commit(types.UPDATE_CATEGORY, category);

      dispatch('toast/showMessage', 'Category updated.', { root: true });
    } catch (e) {
      throw e;
    } finally {
      dispatch('toggleSpinner');
    }
  },

  async destroy({ dispatch, commit }, category) {
    try {
      dispatch('toggleSpinner');

      // Delete category
      await axios.delete(`/api/cms/marketplace_categories/delete/${category.id}`);
      commit(types.DELETE_CATEGORY, category);

      dispatch('toast/showMessage', 'Category deleted.', { root: true });
    } catch(e) {
      dispatch('toast/showError', 'Something went wrong.', { root: true });
    } finally {
      dispatch('toggleSpinner');
    }
  },
};
