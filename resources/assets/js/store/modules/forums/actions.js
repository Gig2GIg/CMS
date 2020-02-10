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
      commit(types.FETCH_FORUMS_SUCCESS, data);
    } catch (e) {
      commit(types.FETCH_FORUMS_FAILURE);
    }
  },

  async store({ dispatch, commit }, post) {
    try {
      dispatch('toggleSpinner');

      // Save changes
      let { data: { data } } = await axios.post('/api/cms/blog/posts', { title: post.title, body: post.body, topic_ids: [{id: post.topic_id}], type: post.type, url_media: post.url_media, search_to: post.search_to});
      data.topic_id = post.topic_id;
      commit(types.CREATE_FORUM, data);

      dispatch('toast/showMessage', 'Forum created.', { root: true });
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
      commit(types.UPDATE_FORUM, post);

      dispatch('toast/showMessage', 'Forum updated.', { root: true });
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
      commit(types.DELETE_FORUM, post);

      dispatch('toast/showMessage', 'Forum deleted.', { root: true });
    } catch(e) {
      dispatch('toast/showError', 'Something went wrong.', { root: true });
    } finally {
      dispatch('toggleSpinner');
    }
  },
};

