import Login from '@/pages/auth/Login';
import Email from '@/pages/auth/password/Email';
import Reset from '@/pages/auth/password/Reset';
import Home from '@/pages/Home';
import Clients from '@/pages/Clients';
import Renters from '@/pages/Renters';
import Rentals from '@/pages/Rentals';
import Categories from '@/pages/Categories';
import Products from '@/pages/Products';
import Settings from '@/pages/Settings';
import NotFound from '@/pages/errors/404';

export default [
  // Guest routes.
  ...middleware('guest', [
    { path: '/login', alias: '/', name: 'login', component: Login },
    { path: '/password/reset', name: 'password.request', component: Email },
    { path: '/password/reset/:token', name: 'password.reset', component: Reset },
  ]),

  // Authenticated routes.
  ...middleware('auth', [
    { path: '/home', alias: '/', title: 'Home', name: 'home', component: Home },
    { path: '/clients', title: 'Clients', name: 'clients', component: Clients },
    { path: '/renters', title: 'Renters', name: 'renters', component: Renters },
    { path: '/rentals', title: 'Rentals', name: 'rentals', component: Rentals },
    { path: '/products', title: 'Products', name: 'products', component: Products },
    { path: '/categories', title: 'Categories', name: 'categories', component: Categories },
    { path: '/settings', title: 'Settings', name: 'settings', component: Settings },
  ]),

  { path: '*', component: NotFound },
];

/**
 * @param  {String|Function} middleware
 * @param  {Array} routes
 * @return {Array}
 */
function middleware(middleware, routes) {
  routes.forEach(route =>
    (route.middleware || (route.middleware = [])).unshift(middleware)
  );

  return routes;
}
