import * as types from '@/store/types';
import axios from 'axios';
import uuid from 'uuid/v1';
import firebase from 'firebase/app';
import 'firebase/storage';

export default {
  toggleSpinner({ commit }) {
    commit(types.TOGGLE_SPINNER);
  },

  async fetch({ commit }) {
    try {
      const { data: { data } } = await axios.get('/api/v1/admin/categories');
      commit(types.FETCH_CATEGORIES_SUCCESS, data);
    } catch (e) {
      commit(types.FETCH_CATEGORIES_FAILURE);
    }
  },

  async store({ dispatch, commit }, { category, fileData }) {
    try {
      dispatch('toggleSpinner');

      // Upload image
      const imageName = `${uuid()}.${fileData.extension}`;
      const image = await firebase
        .storage()
        .ref(`categories/${imageName}`)
        .put(fileData.file);

      category.img_name = imageName;
      category.url_img = await image.ref.getDownloadURL();

      // Save changes
      const { data: { data } } = await axios.post('/api/v1/admin/categories', category);
      commit(types.CREATE_CATEGORY, data);

      dispatch('toast/showMessage', 'Category created.', { root: true });
    } catch (e) {
      throw e;
    } finally {
      dispatch('toggleSpinner');
    }
  },

  async update({ dispatch, commit }, { category, fileData }) {
    try {
      dispatch('toggleSpinner');

      if (fileData.file) {
        // Remove current image
        await firebase.storage().ref(`categories/${category.img_name}`).delete();

        // Upload image
        const imageName = `${uuid()}.${fileData.extension}`;
        const image = await firebase
          .storage()
          .ref(`categories/${imageName}`)
          .put(fileData.file);

        category.img_name = imageName;
        category.url_img = await image.ref.getDownloadURL();
      }

      // Save changes
      await axios.put(`/api/v1/admin/categories/${category.id}`, category);
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

      // Delete image
      await firebase.storage().ref(`categories/${category.img_name}`).delete();

      // Delete category
      await axios.delete(`/api/v1/admin/categories/${category.id}`);
      commit(types.DELETE_CATEGORY, category);

      dispatch('toast/showMessage', 'Category deleted.', { root: true });
    } catch(e) {
      dispatch('toast/showError', 'Something went wrong.', { root: true });
    } finally {
      dispatch('toggleSpinner');
    }
  },
};
