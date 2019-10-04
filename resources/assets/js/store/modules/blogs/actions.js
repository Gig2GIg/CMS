import * as types from '@/store/types';
import axios from 'axios';

export default {
  toggleSpinner({ commit }) {
    commit(types.TOGGLE_SPINNER);
  },

  async fetch({ commit }) {
    try {
      const { data: { data } } = await axios.get('/api/cms/forum/posts');
     console.log(data);
      commit(types.FETCH_BLOGS_SUCCESS, data);
    } catch (e) {
      commit(types.FETCH_BLOGS_FAILURE);
    }
  },

  async store({ dispatch, commit }, post) {
    try {
      dispatch('toggleSpinner');

      // Save changes
      const { data: { data } } = await axios.post('/api/cms/blog/posts', { title: post.title, body: post.body, topic_ids: [{id: post.topic_id}], type: post.type, url_media: post.url_media, search_to: post.search_to});
      
      commit(types.CREATE_BLOG, data);

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
      await axios.put(`/api/cms/forum/posts/${post.id}`, { title: post.title, body: post.body, topic_ids: [{id: post.topic_id}], type: post.type, url_media: post.url_media, search_to: post.search_to});
      commit(types.UPDATE_BLOG, post);

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
      console.log('ActioN========');
      console.log(post)
      // Delete skill
      await axios.delete(`/api/cms/forum/posts/${post.id}/delete`);
      commit(types.DELETE_BLOG, post);

      dispatch('toast/showMessage', 'Blog deleted.', { root: true });
    } catch(e) {
      dispatch('toast/showError', 'Something went wrong.', { root: true });
    } finally {
      dispatch('toggleSpinner');
    }
  },
};

