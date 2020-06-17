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
      const { data: { data } } = await axios.get('/api/cms/marketplaces');

      commit(types.FETCH_VENDORS_SUCCESS, data);
    } catch (e) {
      commit(types.FETCH_VENDORS_FAILURE);
    }
  },

  async store({ dispatch, commit }, { vendor, imageData }) {
    try {
      dispatch('toggleSpinner');

      // Upload image
      const imageName = `${uuid()}.${imageData.extension}`;
      const image = await firebase
        .storage()
        .ref(`vendors/${imageName}`)
        .put(imageData.file);

      vendor.image_name = imageName;
      vendor.image_url = await image.ref.getDownloadURL();

      // Save changes
      if(vendor.marketplace_category_id){
        const { data: { data } } = await axios.post(`/api/cms/marketplace_categories/${vendor.marketplace_category_id}/marketplaces/create`, vendor);
        commit(types.CREATE_VENDOR, data);
      } else {
        const { data: { data } } = await axios.post(`/api/cms/marketplaces/create`, vendor);
        commit(types.CREATE_VENDOR, data);
      }
      dispatch('toast/showMessage', 'Vendor created.', { root: true });
    } catch (e) {
      throw e;
    } finally {
      dispatch('toggleSpinner');
    }
  },

  async update({ dispatch, commit }, { vendor, imageData }) {
    try {
      dispatch('toggleSpinner');

      if (imageData.file) {
        // Remove current image
        await firebase.storage().ref(`vendors/${vendor.image.name}`).delete();

        // Upload image
        const imageName = `${uuid()}.${imageData.extension}`;
        const image = await firebase
          .storage()
          .ref(`vendors/${imageName}`)
          .put(imageData.file);

        vendor.image_name = imageName;
        vendor.image_url = await image.ref.getDownloadURL();
      }

      // Save changes
      const { data: { data } } = await axios.put(`/api/cms/marketplaces/update/${vendor.id}`, vendor);
      commit(types.UPDATE_VENDOR, data);

      dispatch('toast/showMessage', 'Vendor updated.', { root: true });
    } catch (e) {
      throw e;
    } finally {
      dispatch('toggleSpinner');
    }
  },

  async destroy({ dispatch, commit }, vendor) {
    try {
      dispatch('toggleSpinner');

      // Delete vendor
      await axios.delete(`/api/cms/marketplaces/delete/${vendor.id}`);

      dispatch('toast/showMessage', 'Vendor deleted.', { root: true });
    } catch(e) {
      dispatch('toast/showError', 'Something went wrong.', { root: true });
    } finally {
      dispatch('toggleSpinner');
    }
  },

  async destroyFirebase({ dispatch, commit }, vendor) {
    try {
      dispatch('toggleSpinner');
      // Delete image
      await firebase.storage().ref(`vendors/${vendor.image.name}`).delete();
      // commit(types.DELETE_VENDOR, vendor);
    } catch(e) {
      //
    } finally {
      //
      commit(types.DELETE_VENDOR, vendor); 
      dispatch('toggleSpinner');
    }
  },

  async updateFeatured({ dispatch, commit }, vendor ) {
    try {
      dispatch('toggleSpinner');

      // Save changes
      const { data: { data } } = await axios.post(`/api/cms/marketplaces/${vendor.id}/featured`);
      commit(types.UPDATE_VENDOR, data);

      dispatch('toast/showMessage', 'Vendor updated.', { root: true });
    } catch (e) {
      throw e;
    } finally {
      dispatch('toggleSpinner');
    }
  },

  async updateNotFeatured({ dispatch, commit }, vendor ) {
    try {
      dispatch('toggleSpinner');

      // Save changes
      const { data: { data } } = await axios.post(`/api/cms/marketplaces/${vendor.id}/not-featured`);
      commit(types.UPDATE_VENDOR, data);

      dispatch('toast/showMessage', 'Vendor updated.', { root: true });
    } catch (e) {
      throw e;
    } finally {
      dispatch('toggleSpinner');
    }
  }
};
