import getters from './getters';
import actions from './actions';
import mutations from './mutations';

const state = {
  categories: [],
  isLoading: false,
};

export default {
  state,
  getters,
  actions,
  mutations,
};
