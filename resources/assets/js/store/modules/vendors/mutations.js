import * as types from '@/store/types';
import Vue from 'vue';

export default {
  [types.TOGGLE_SPINNER] (state) {
    state.isLoading = !state.isLoading;
  },

  [types.FETCH_VENDORS_SUCCESS] (state, vendors) {
    state.vendors = vendors;
  },

  [types.FETCH_VENDORS_FAILURE] (state) {
    state.vendors = [];
  },

  [types.CREATE_VENDOR] (state, vendor) {
    state.vendors.push(vendor);
  },

  [types.UPDATE_VENDOR] (state, vendor) {
    let currentVendor = state.vendors.find(x => x.id === vendor.id);
    let index = state.vendors.indexOf(currentVendor);

    Vue.set(state.vendors, index, vendor);
  },

  [types.DELETE_VENDOR] (state, vendor) {
    let index = state.vendors.indexOf(vendor);
    state.vendors.splice(index, 1);
  },
};
