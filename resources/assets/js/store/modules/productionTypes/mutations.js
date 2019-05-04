import * as types from '@/store/types';
import Vue from 'vue'

export default {
  [types.TOGGLE_SPINNER] (state) {
    state.isLoading = !state.isLoading;
  },

  [types.FETCH_PRODUCTION_TYPES_SUCCESS] (state, productionTypes) {
    state.productionTypes = productionTypes;
  },

  [types.FETCH_PRODUCTION_TYPES_FAILURE] (state) {
    state.productionTypes = [];
  },

  [types.CREATE_PRODUCTION_TYPE] (state, productionType) {
    state.productionTypes.push(productionType);
  },

  [types.UPDATE_PRODUCTION_TYPE] (state, productionType) {
    let currentCategory = state.productionTypes.find(x => x.id === productionType.id);
    let index = state.productionTypes.indexOf(currentCategory);

    Vue.set(state.productionTypes, index, productionType);
  },

  [types.DELETE_PRODUCTION_TYPE] (state, productionType) {
    let index = state.productionTypes.indexOf(productionType);
    state.productionTypes.splice(index, 1);
  },
};
