<template>
  <aside class="menu">
    <p class="menu-label">General</p>
    <ul class="menu-list">
      <li v-for="route in routes" v-bind:key="route.name">
        <router-link :to="route" active-class="is-active">
          {{ route.title }}
        </router-link>
      </li>
    </ul>
  </aside>
</template>

<script>
export default {
  name: 'Sidebar',
  computed: {
    routes: function () {
      var routes = [];
      for (var i in this.$router.options.routes) {
        if (!this.$router.options.routes.hasOwnProperty(i)) {
          continue;
        }
        var route = this.$router.options.routes[i];
        if(route.hasOwnProperty('title')) {
          routes.push(route);
        }
      }
      return routes;
    }
  },
};
</script>
