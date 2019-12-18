import getters from './getters';
import actions from './actions';
import mutations from './mutations';

const state = {
  topics: [],
  isLoading: false,
};

export default {
  state,
  getters,
  actions,
  mutations,
};
