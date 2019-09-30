import * as types from '@/store/types';
import axios from 'axios';

export default {
  toggleSpinner({ commit }) {
    commit(types.TOGGLE_SPINNER);
  },

  async fetch({ commit }) {
    try {
      const { data: { data } } = await axios.get('/api/cms/forum/posts');
     
      commit(types.FETCH_SKILLS_SUCCESS, data);
    } catch (e) {
      commit(types.FETCH_SKILLS_FAILURE);
    }
  },

  async store({ dispatch, commit }, post) {
    try {
      dispatch('toggleSpinner');

      // Save changes
      console.log(post.body);
      const { data: { data } } = await axios.post('/api/cms/blog/posts', { title: post.title, body: post.body});
      
      commit(types.CREATE_SKILL, data);

      dispatch('toast/showMessage', 'Blog created.', { root: true });
    } catch (e) {
      throw e;
    } finally {
      dispatch('toggleSpinner');
    }
  },

  async update({ dispatch, commit }, post) {
    try {
      dispatch('toggleSpinner');

      // Save changes
      await axios.put(`/api/cms/skills/update/${skill.id}`, skill);
      commit(types.UPDATE_SKILL, skill);

      dispatch('toast/showMessage', 'Blog updated.', { root: true });
    } catch (e) {
      throw e;
    } finally {
      dispatch('toggleSpinner');
    }
  },

  async destroy({ dispatch, commit }, post) {
    try {
      dispatch('toggleSpinner');

      // Delete skill
      await axios.delete(`/api/cms/skills/delete/${post.id}`);
      commit(types.DELETE_SKILL, post);

      dispatch('toast/showMessage', 'Blog deleted.', { root: true });
    } catch(e) {
      dispatch('toast/showError', 'Something went wrong.', { root: true });
    } finally {
      dispatch('toggleSpinner');
    }
  },
};
