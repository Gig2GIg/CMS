<template>
  <div class="column is-one-third-desktop is-one-mobile">
    <article class="card rounded shadow">
      <form @submit.prevent="submit">
        <div class="card-content">
          <h1 class="title has-text-centered">
            <a href="/">
              <img src="/storage/logo.png" alt="Logo" class="">
            </a>
          </h1>

          <b-field
            label="Email"
            :type="{'is-danger': errors.has('email')}"
            :message="errors.first('email')"
          >
            <b-input
              v-model="form.email"
              v-validate="'required|email'"
              name="email"
              autofocus
            />
          </b-field>

          <div class="field">
            <button class="button is-info is-medium is-fullwidth rounded shadow" :disabled="isLoading">
              <i class="fa fa-user"></i>
              Reset password
            </button>
          </div>
          <div class="has-text-centered">
            <small>
              <router-link to="/login" class="is-link hover:underline">
                Sign in instead
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
    },
  }),
  computed: {
    ...mapState('auth', ['isLoading']),
  },
  methods: {
    ...mapActions('auth', ['forgot']),
    ...mapActions('toast', ['showError']),

    async submit() {
      try {
        let valid = await this.$validator.validateAll();

        if (! valid) {
          this.showError('Please check the fields.');
          return;
        }

        if (await this.forgot(this.form)) {
          this.$router.replace({ name: 'login' });
        }
      } catch(e) {
        this.$setErrorsFromResponse(e.response.data);
      }
    },
  },
};
</script>
