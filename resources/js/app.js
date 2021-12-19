require('./bootstrap');

import Vue from 'vue';
import VueRouter from 'vue-router';
import ElementUI from 'element-ui';
import store from './store';

import App from './views/App';
import Absen from './views/Absen';
import Login from './views/Login';
import Home from './views/Home';
import NotFound from './views/components/NotFound';
import MenuTree from './views/menu/MenuTree';
import MenuForm from './views/menu/MenuForm';
import RoleDT from './views/role/RoleDT';
import RoleForm from "./views/role/RoleForm";
import UserDT from './views/user/UserDT';
import UserForm from './views/user/UserForm';
import PasswordForm from './views/user/PasswordForm';
import HolidayDT from './views/holiday/HolidayDT';
import HolidayForm from './views/holiday/HolidayForm';
import RptPegawaiForm from './views/report/RptPegawaiForm';
import ShiftDT from './views/siaga/ShiftDT';
import ShiftForm from './views/siaga/ShiftForm';
import AssignDT from './views/siaga/AssignDT';
import AssignForm from './views/siaga/AssignForm';
import AbsenSiaga from './views/siaga/AbsenSiaga';
import PositionDT from './views/siaga/PositionDT';
import PositionForm from './views/siaga/PositionForm';
import ReportForm from './views/siaga/ReportForm';
import EmployeeForm from './views/employee/EmployeeForm';
import EmployeeDT from './views/employee/EmployeeDT';

import 'element-ui/lib/theme-chalk/index.css';
import locale from 'element-ui/lib/locale/lang/en'
import '../sass/app.scss';

Vue.use(VueRouter);
Vue.use(ElementUI, { locale });
Vue.config.productionTip = false;


const router = new VueRouter({
    mode: 'history',
    routes: [
        {
            path: '/admin',
            name: 'admin',
            component: Login
        },
        {
            path: '/logout',
            name: 'logout',
            redirect: to => {
                store.dispatch('doLogout');
                return '/admin';
            }
        },
        {
            path: '/admin/home',
            name: 'home',
            component: Home,
            meta: {
                requiresAuth: true
            }
        },
        {
            path: '/admin/menus',
            name: 'menu',
            component: MenuTree,
            meta: {
                requiresAuth: true
            }
        },
        {
            path: '/admin/menus/create/:parent/:id',
            name: 'menu-create',
            component: MenuForm,
            meta: {
                requiresAuth: true
            }
        },
        {
            path: '/admin/roles',
            name: 'role',
            component: RoleDT,
            meta: {
                requiresAuth: true
            }
        },
        {
            path: '/admin/roles/create/:id?',
            name: 'role-create',
            component: RoleForm,
            meta: {
                requiresAuth: true
            }
        },
        {
            path: '/admin/users',
            name: 'user',
            component: UserDT,
            meta: {
                requiresAuth: true
            }
        },
        {
            path: '/admin/users/create/:id?',
            name: 'user-create',
            component: UserForm,
            meta: {
                requiresAuth: true
            }
        },
        {
            path: '/admin/password',
            name: 'password',
            component: PasswordForm,
            meta: {
                requiresAuth: true
            }
        },
        {
            path: '/admin/holidays',
            name: 'holiday',
            component: HolidayDT,
            meta: {
                requiresAuth: true
            }
        },
        {
            path: '/admin/holidays/create/:id?',
            name: 'holiday-create',
            component: HolidayForm,
            meta: {
                requiresAuth: true
            }
        },
        {
            path: '/admin/reports/peg',
            name: 'report-pegawai-form',
            component: RptPegawaiForm,
            meta: {
                requiresAuth: true
            }
        },
        {
            path: '/admin/employees',
            name: 'employee',
            component: EmployeeDT,
            meta: {
                requiresAuth: true
            }
        },
        {
            path: '/admin/employees/create/:id?',
            name: 'employee-create',
            component: EmployeeForm,
            meta: {
                requiresAuth: true
            }
        },
        {
            path: '/siaga/admin/shift',
            name: 'shift',
            component: ShiftDT,
            meta: {
                requiresAuth: true
            }
        },
        {
            path: '/siaga/admin/shift/create/:id?',
            name: 'shift-create',
            component: ShiftForm,
            meta: {
                requiresAuth: true
            }
        },
        {
            path: '/siaga/admin/assign',
            name: 'assign',
            component: AssignDT,
            meta: {
                requiresAuth: true
            }
        },
        {
            path: '/siaga/admin/assign/create/:id?',
            name: 'assign-create',
            component: AssignForm,
            meta: {
                requiresAuth: true
            }
        },
        {
            path: '/siaga/admin/position',
            name: 'siaga-position',
            component: PositionDT,
            meta: {
                requiresAuth: true
            }
        },
        {
            path: '/siaga/admin/position/create/:id?',
            name: 'siaga-position-create',
            component: PositionForm,
            meta: {
                requiresAuth: true
            }
        },
        {
            path: '/siaga/admin/rekap',
            name: 'siaga-rekap',
            component: ReportForm,
            meta: {
                requiresAuth: true
            }
        },
        {
            path: '/siaga/admin',
            name: 'admin-siaga',
            component: Login
        },
        {
            path: '/siaga/:tz?',
            name: 'absen-siaga',
            component: AbsenSiaga
        },
        {
            path: '/:tz?',
            name: 'absen',
            component: Absen
        },
        {
            path: '/:any',
            name: 'not-found',
            component: NotFound,
        },
    ]
});

router.beforeEach((to, from, next) => {
    if(to.matched.some(record => record.meta.requiresAuth)) {
        if(store.getters.isLoggedIn) {
            next();
            return;
        }
        next('/admin');
    } else {
        next();
    }
});

const app = new Vue({
    el: '#app',
    store,
    render: h => h(App),
    router,
});
