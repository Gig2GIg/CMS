import * as types from '@/store/types';
import Vue from 'vue';

export default {
  [types.TOGGLE_SPINNER] (state) {
    state.isLoading = !state.isLoading;
  },

  [types.FETCH_TOPICS_SUCCESS] (state, categories) {
    state.categories = categories;
  },

  [types.FETCH_TOPICS_FAILURE] (state) {
    state.categories = [];
  },

  [types.CREATE_TOPIC] (state, category) {
    state.categories.push(category);
  },

  [types.UPDATE_TOPIC] (state, category) {
    let currentSkill = state.categories.find(x => x.id === category.id);
    let index = state.categories.indexOf(currentSkill);

    Vue.set(state.categories, index, category);
  },

  [types.DELETE_TOPIC] (state, category) {
    let index = state.categories.indexOf(category);
    state.categories.splice(index, 1);
  },
};
