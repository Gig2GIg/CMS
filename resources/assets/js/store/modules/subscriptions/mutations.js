import * as types from '@/store/types';
import Vue from 'vue';

export default {
  [types.TOGGLE_SPINNER] (state) {
    state.isLoading = !state.isLoading;
  },

  [types.FETCH_SUBSCRIPTIONS_SUCCESS] (state, subscriptions) {
    state.subscriptions = subscriptions;
  },

  [types.FETCH_SUBSCRIPTIONS_FAILURE] (state) {
    state.subscriptions = [];
  },

  [types.UPDATE_SUBSCRIPTION] (state, subscription) {
    let currentSubscription = state.subscriptions.find(x => x.id === subscription.id);
    let index = state.subscriptions.indexOf(currentSubscription);

    Vue.set(state.subscriptions, index, subscription);
  },
};
