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
      const { data: { data } } = await axios.get('/api/cms/topics');
      
      commit(types.FETCH_TOPICS_SUCCESS, data);
    } catch (e) {
      commit(types.FETCH_TOPICS_FAILURE);
    }
  },

  async store({ dispatch, commit }, topic) {
    try {
      dispatch('toggleSpinner');

      // Save changes
      const { data: { data } } = await axios.post('/api/cms/topics/create', topic);
      
      commit(types.CREATE_TOPIC, data);

      dispatch('toast/showMessage', 'Topic created.', { root: true });
    } catch (e) {
      throw e;
    } finally {
      dispatch('toggleSpinner');
    }
  },

  async update({ dispatch, commit }, topic) {
    try {
      dispatch('toggleSpinner');

      // Save changes
      await axios.put(`/api/cms/topics/update/${topic.id}`, topic);
      commit(types.UPDATE_TOPIC, topic);

      dispatch('toast/showMessage', 'Topic updated.', { root: true });
    } catch (e) {
      throw e;
    } finally {
      dispatch('toggleSpinner');
    }
  },

  async destroy({ dispatch, commit }, topic) {
    try {
      dispatch('toggleSpinner');

      // Delete topic
      await axios.delete(`/api/cms/topics/delete/${topic.id}`);
      commit(types.DELETE_TOPIC, topic);

      dispatch('toast/showMessage', 'Topic deleted.', { root: true });
    } catch(e) {
      dispatch('toast/showError', 'Something went wrong.', { root: true });
    } finally {
      dispatch('toggleSpinner');
    }
  },
};
