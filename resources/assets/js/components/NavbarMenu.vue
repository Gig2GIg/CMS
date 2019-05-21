<template>
  <div class="navbar-menu" :class="{'is-active': navbarBurger }">
    <div class="navbar-end items-center">
      <div>
        <button
          class="active:outline-none mr-6 px-3"
          @click="showNotificationModal">
          Broadcast notification
        </button>
      </div>
      <div class="navbar-item has-dropdown is-hoverable" :class="{ 'is-active': navbar }">
        <a class="navbar-link" @click.prevent="navbar = !navbar" v-text="user.name"/>
        <div class="navbar-dropdown is-boxed">
          <a class="navbar-item" @click.prevent="logout">Logout</a>
        </div>
      </div>
    </div>

    <b-modal :active.sync="isModalActive" has-modal-card>
      <form @submit.prevent="sendBroadcast">
        <div class="modal-card">
          <header class="modal-card-head">
            <p class="modal-card-title">Send push notification</p>
          </header>

          <section class="modal-card-body">
            <b-field
              label="Title"
              :type="{'is-info': errors.has('title')}"
              :message="errors.first('title')"
            >
              <b-input
                v-model="notification.title"
                v-validate="'required|max:100'"
                name="title"
                autofocus
              />
            </b-field>

            <b-field
              label="Message"
              :type="{'is-info': errors.has('message')}"
              :message="errors.first('message')"
            >
              <b-input
                v-model="notification.message"
                v-validate="'required|max:2000'"
                name="message"
              />
            </b-field>
          </section>
          <footer class="modal-card-foot">
            <button
              class="button"
              type="button"
              :disabled="isLoading"
              @click="isModalActive = false"
            >Close</button>
            <button class="button is-primary" :disabled="isLoading">Send</button>
          </footer>
        </div>
      </form>
    </b-modal>
  </div>
</template>

<script>
import bus from "@/bus";
import { mapActions } from "vuex";
export default {
  name: "NavbarMenu",
  props: {
    user: {
      type: Object,
      required: true
    }
  },
  data() {
    return {
      notification: {},
      navbar: false,
      navbarBurger: false,
      isModalActive: false,
      isLoading: false,
      windowWidth: window.innerWidth
    };
  },
  created() {
    bus.$on("active-burger", response => {
      this.navbarBurger = response;
    });
  },
  methods: {
    ...mapActions({ signout: "auth/logout" }),
    ...mapActions('auth', ['broadcast']),

    showNotificationModal() {
      this.notification = {};
      this.isModalActive = true;
    },

    async sendBroadcast() {
      try {
        const valid = await this.$validator.validateAll();

        if (!valid) {
          this.showError("Please check the fields.");
          return;
        }

        this.isLoading = true;
        await this.broadcast(this.notification);
        this.isLoading = false;

        this.isModalActive = false;
      } catch(e) {
        console.log(e);
        this.$setErrorsFromResponse(e.response.data);
      }
    },

    async logout() {
      // Log out the user.
      await this.signout();

      // Redirect to login.
      this.$router.replace({
        name: "login"
      });
    }
  }
};
</script>
