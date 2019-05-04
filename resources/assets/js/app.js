import App from '@/components/App';
import VeeValidate from 'vee-validate';
import VeeValidateLaravel from '@/plugins/vee-validate-laravel';
import CKEditor from '@ckeditor/ckeditor5-vue';
import Vue from 'vue';
import VueMoment from 'vue-moment';
import router from '@/router';
import store from '@/store';
import firebase from 'firebase/app';

import '@/plugins';
import '@/components';

Vue.use(VeeValidate);
Vue.use(VeeValidateLaravel);
Vue.use(VueMoment);
Vue.use(CKEditor);

Vue.config.productionTip = false;

firebase.initializeApp({
  apiKey: 'AIzaSyDSamX1us63KDTNfT5rbvWz1bIcZ1jwOfU',
  projectId: 'lending-lavish',
  storageBucket: 'lending-lavish.appspot.com',
});

Array.prototype.search = function(needle) {
  const match = item => Object.values(item).some(value => {
    if (value && typeof value === 'object') {
      return match(value);
    }
    return String(value).toLowerCase().indexOf(needle.toLowerCase()) !== -1;
  });

  return this.filter(item => match(item));
};

new Vue({
  router,
  store,
  ...App
});
