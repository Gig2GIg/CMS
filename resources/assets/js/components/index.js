import Vue from "vue";
import Child from "./Child";
import Navbar from "./Navbar";
import NavbarBurger from "./NavbarBurger";

// Components that are registered globaly.
[Child, Navbar, NavbarBurger].forEach(Component => {
  Vue.component(Component.name, Component);
});
