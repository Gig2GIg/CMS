import * as types from '@/store/types';
import axios from 'axios';

export default {
  toggleSpinner({ commit }) {
    commit(types.TOGGLE_SPINNER);
  },

  async init({ dispatch }) {
    await dispatch('fetch');
  },

  async fetch({ commit }) {
    try {
      const { data: { data } } = await axios.get('/api/cms/users');

      commit(types.FETCH_USERS_SUCCESS, data);
    } catch (e) {
      commit(types.FETCH_USERS_FAILURE);
    }
  },

  async update({ dispatch, commit }, user) {
    try {
      dispatch('toggleSpinner');
      let updateRequest = {
        "image":user.image ? user.image : user.details.image,
        "first_name":user.details.first_name,
        "last_name":user.details.last_name,
        "email":user.email,
        "birth":user.details.birth,
        "profesion":user.details.profesion ? user.details.profesion : null,
        "agency_name":user.details.agency_name ? user.details.agency_name : null,
        "url":user.details.stage_name ? user.details.stage_name : null,
        "url":user.details.url ? user.details.url : null,        
        "gender":user.details.gender ? user.details.gender : null,
        "address":user.details.address,
        "city":user.details.city,
        "state":user.details.state,
        "zip":user.details.zip,    
     }
      // Save user changes
      await axios.put(`/api/cms/users/${user.id}`, updateRequest);
      if(user.image){
        user.details.image = user.image;
      }
      
      commit(types.UPDATE_USER, user);

      dispatch('toast/showMessage', 'User updated.', { root: true });
    } catch (e) {
      throw e;
    } finally {
      dispatch('toggleSpinner');
    }
  },

  async destroy({ dispatch, commit }, user) {
    try {
      dispatch('toggleSpinner');

      // Delete user
      await axios.delete(`/api/cms/users/${user.id}/delete`);
      commit(types.DELETE_USER, user);

      dispatch('toast/showMessage', 'User deleted.', { root: true });
    } catch (e) {
      dispatch('toast/showError', 'Something went wrong.', { root: true });
    } finally {
      dispatch('toggleSpinner');
    }
  },
  async status_change({ dispatch, commit }, user) {
    try {
      dispatch('toggleSpinner');

      // Save user changes
      let newStatus = user.is_active ? 0 : 1;
      let param = {
        id: user.id,
        status: newStatus
      }
      await axios.post(`/api/cms/users/changeStatus`, param);
      user.is_active = newStatus;
      commit(types.UPDATE_USER, user);

      dispatch('toast/showMessage', 'User status changed.', { root: true });
    } catch (e) {
      throw e;
    } finally {
      dispatch('toggleSpinner');
    }
  },
};

