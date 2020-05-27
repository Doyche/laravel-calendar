import Vue from 'vue';
import VueRouter from 'vue-router';

Vue.use(VueRouter);

//import Dashboard from '../components/Dashboard/Container';
import AppContainer from '../components/AppContainer';
//import Users from '../components/Users/Container';

const routes = [
    /*{
        component: Dashboard,
        name: 'dashboard',
        path: '/dashboard'
    },
    {
        component: Users,
        name: 'users',
        path: '/users'
    },*/
    {
        component: AppContainer,
        name: 'contact',
        path: '/contact'
    },
];

export default new VueRouter({
    routes
});

