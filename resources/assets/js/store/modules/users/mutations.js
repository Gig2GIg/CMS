import * as types from '@/store/types';
import Vue from 'vue';

export default {
  [types.TOGGLE_SPINNER](state) {
    state.isLoading = !state.isLoading;
  },

  [types.FETCH_USERS_SUCCESS](state, users) {  
    users = users.map(user=>{      
      if(user.details){
        user.first_name = user.details.first_name ? user.details.first_name : '';
        user.last_name = user.details.last_name ? user.details.last_name  : '';
        user.type = user.details.type ? user.details.type  : '';
        user.created_at = user.details.created_at ? user.details.created_at  : '';        
      }      
      return user;
    })
    state.userList = users;
  },

  [types.FETCH_USERS_FAILURE](state) {
    state.userList = [];
  },

  [types.UPDATE_USER](state, user) {
    let current = state.userList.find(x => x.id === user.id);
    let index = state.userList.indexOf(current);
    if(user.details){
      user.first_name = user.details.first_name ? user.details.first_name : '';
      user.last_name = user.details.last_name ? user.details.last_name  : '';
      user.type = user.details.type ? user.details.type  : '';
      user.created_at = user.details.created_at ? user.details.created_at  : '';        
    }    
    Vue.set(state.userList, index, user);
  },

  [types.DELETE_TOPIC](state, user) {
    let index = state.userList.indexOf(user);
    state.userList.splice(index, 1);
  },
};
