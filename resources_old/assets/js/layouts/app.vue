<template>
  <div id="app">
    <navbar></navbar>
    <div class="container is-fluid">
      <div class="columns">
        <div class="column is-2">
          <sidebar></sidebar>
        </div>
        <div class="column is-10">
          <child></child>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import Navbar from "@/components/Navbar";
import Sidebar from "@/components/Sidebar";
export default {
  name: "app-layout",
  components: {
    Navbar,
    Sidebar
  }
};
</script>
