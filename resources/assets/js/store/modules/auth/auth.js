import Cookies from "js-cookie";
import actions from './actions';
import getters from './getters';
import mutations from './mutations';

const state = {
  user: null,
  token: Cookies.get("token"),
  isLoading: false,
};

export default {
  state,
  getters,
  actions,
  mutations,
};
