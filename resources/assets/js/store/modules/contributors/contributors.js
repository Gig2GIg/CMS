import mutations from './mutations';
import actions from './actions';
import getters from './getters';

const state = {
  isLoading: false,
  contributors: [],
};

export default {
  state,
  mutations,
  actions,
  getters,
};
