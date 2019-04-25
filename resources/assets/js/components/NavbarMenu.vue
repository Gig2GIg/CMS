<template>
  <div class="navbar-menu" :class="{'is-active': navbarBurger }">
    <div class="navbar-end items-center">
      <div>
        <button
          class="active:outline-none mr-6 px-3"
          :disabled="isLoading"
          @click="confirmBroadcast"
        >Broadcast notification</button>
      </div>
      <div class="navbar-item has-dropdown is-hoverable" :class="{ 'is-active': navbar }">
        <a class="navbar-link" @click.prevent="navbar = !navbar" v-text="user.name"/>
        <div class="navbar-dropdown is-boxed">
          <a class="navbar-item" @click.prevent="logout">Logout</a>
        </div>
      </div>
    </div>
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
      navbar: false,
      navbarBurger: false,
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
    ...mapActions("clients", ["fetch", "broadcast", "notify", "destroy"]),

    confirmBroadcast() {
      this.$dialog.prompt({
        message: "Type a message",
        inputAttrs: {
          placeholder: "Message",
          maxlenght: 2000
        },
        onConfirm: value => this.sendBroadcast(value)
      });
    },
    async sendBroadcast(message) {
      await this.broadcast(message);
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
