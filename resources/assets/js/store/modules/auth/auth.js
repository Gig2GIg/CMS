import Cookies from "js-cookie";
import actions from './actions';
import getters from './getters';
import mutations from './mutations';

// user: {name: 'Rigo', last_name: 'Gomez'},
const state = {
  user: {name: 'Rigo', last_name: 'Gomez'},
  token: Cookies.get("token"),
  isLoading: false,
};

export default {
  state,
  getters,
  actions,
  mutations,
};
