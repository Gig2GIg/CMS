import Login from '@/pages/auth/Login';
import Email from '@/pages/auth/password/Email';
import Home from '@/pages/Home';
import Auditions from '@/pages/Auditions';
import AuditionsBanned from '@/pages/AuditionsBanned';
import Performers from '@/pages/Performers';
import Contributors from '@/pages/Contributors';
import Marketplace from '@/pages/Marketplace';
import Vendors from '@/pages/Vendors';
import ProductionTypes from '@/pages/ProductionTypes';
import Skills from '@/pages/Skills';
import Subscriptions from '@/pages/Subscriptions';
import Payments from '@/pages/Payments';
import Topics from '@/pages/Topics';
import Blogs from '@/pages/Blogs';
import Forums from '@/pages/Forums';
import Settings from '@/pages/Settings';
import NotFound from '@/pages/errors/404';
import FeaturedListing from '@/pages/FeaturedListing';

export default [
  // Guest routes.
  ...middleware('guest', [
    { path: '/login', alias: '/', name: 'login', component: Login },
    { path: '/password/reset', name: 'password.request', component: Email },
  ]),

  // Authenticated routes.
  ...middleware('auth', [
    { path: '/home', alias: '/', title: 'Home', name: 'home', component: Home },
    { path: '/auditions', title: 'Auditions', name: 'auditions', component: Auditions },
    { path: '/auditions-banned', title: 'Auditions Banned', name: 'auditions-banned', component: AuditionsBanned },
    { path: '/auditions/:id/performers', name: 'performers', component: Performers },
    { path: '/auditions/:id/contributors', name: 'contributors', component: Contributors },
    { path: '/marketplace', title: 'Marketplace Categories', name: 'marketplace', component: Marketplace },
    { path: '/vendors', title: 'Marketplace Vendors', name: 'vendors', component: Vendors },
    { path: '/production-types', title: 'Production Types', name: 'production-types', component: ProductionTypes },
    { path: '/skills', title: 'Skills', name: 'skills', component: Skills },
    { path: '/subscriptions', title: 'Subscriptions', name: 'subscriptions', component: Subscriptions },
    { path: '/payments', title: 'Payments', name: 'payments', component: Payments },
    { path: '/settings', title: 'Settings', name: 'settings', component: Settings },
    { path: '/topics', title: 'Topics', name: 'topics', component: Topics },
    { path: '/blogs', title: 'Blogs', name: 'blogs', component: Blogs },
    { path: '/forum', title: 'Forums', name: 'forums', component: Forums },    
    { path: '/featured-listing', title: 'Marketplace Featured Listing', name: 'featured-listing', component: FeaturedListing },

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
