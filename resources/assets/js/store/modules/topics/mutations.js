import * as types from '@/store/types';
import Vue from 'vue';

export default {
  [types.TOGGLE_SPINNER] (state) {
    state.isLoading = !state.isLoading;
  },

  [types.FETCH_TOPICS_SUCCESS] (state, topics) {
    state.topics = topics;
  },

  [types.FETCH_TOPICS_FAILURE] (state) {
    state.topics = [];
  },

  [types.CREATE_TOPIC] (state, topic) {
    state.topics.push(topic);
  },

  [types.UPDATE_TOPIC] (state, topic) {
    let current = state.topics.find(x => x.id === topic.id);
    let index = state.topics.indexOf(current);

    Vue.set(state.topics, index, topic);
  },

  [types.DELETE_TOPIC] (state, topic) {
    let index = state.topics.indexOf(topic);
    state.topics.splice(index, 1);
  },
};
