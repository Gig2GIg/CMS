<template>
  <div class="column is-one-third-desktop is-one-mobile">
    <article class="card rounded shadow">
      <form @submit.prevent="submit">
        <div class="card-content">
          <h1 class="title has-text-centered">
            <a href="/">
              <img src="/storage/logo.png" alt="Logo" class="h-32">
            </a>
          </h1>

          <b-field
            label="Email"
            :type="{'is-danger': errors.has('email')}"
            :message="errors.first('email')">
            <b-input
              v-model="form.email"
              v-validate="'required|email'"
              name="email"
              autofocus
            />
          </b-field>

          <b-field
            label="Password"
            :type="{'is-danger': errors.has('password')}"
            :message="errors.first('password')">
            <b-input
              v-model="form.password"
              v-validate="'required'"
              name="password"
              type="password"
            />
          </b-field>

          <b-field>
            <b-checkbox v-model="form.remember" name="remember">
              Remember Me
            </b-checkbox>
          </b-field>

          <div class="field">
            <button class="button is-info is-medium is-fullwidth rounded shadow" :disabled="isLoading">
              <i class="fa fa-user"></i>
              Login
            </button>
          </div>

          <div class="has-text-centered">
            <small>
              <router-link to="/password/reset" class="is-link hover:underline">
                Forgot Your Password?
              </router-link>
            </small>
          </div>
        </div>
      </form>
    </article>
  </div>
</template>

<script>
import { mapActions, mapState } from 'vuex';

export default {
  layout: 'auth',
  data: () => ({
    form: {
      email: null,
      password: null,
      remember: false,
    },
  }),
  computed: {
    ...mapState('auth', ['isLoading']),
  },
  methods: {
    ...mapActions('auth', ['login']),
    ...mapActions('toast', ['showError']),

    async submit() {
      let valid = await this.$validator.validateAll();

      if (! valid) {
        this.showError('Please check the fields.');
        return;
      }

      if (await this.login(this.form)) {
        this.$router.replace({ name: 'home' });
      }
    },
  }
};
</script>
