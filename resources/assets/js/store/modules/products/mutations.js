import * as types from '@/store/types';
import Vue from 'vue'

export default {
  [types.TOGGLE_SPINNER] (state) {
    state.isLoading = !state.isLoading;
  },

  [types.FETCH_PRODUCTS_SUCCESS] (state, products) {
    state.products = products;
  },

  [types.FETCH_PRODUCTS_FAILURE] (state) {
    state.products = [];
  },

  [types.UPDATE_PRODUCT] (state, product) {
    let currentProduct = state.products.find(x => x.id === product.id);
    let index = state.products.indexOf(currentProduct);

    Vue.set(state.products, index, product);
  },

  [types.DELETE_PRODUCT] (state, product) {
    let index = state.products.indexOf(product);
    state.products.splice(index, 1);
  },
};
