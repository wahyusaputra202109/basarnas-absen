import Vue from 'vue';
import Vuex from 'vuex';
import axios from 'axios';

Vue.use(Vuex);

export default new Vuex.Store({
    state: {
        status: '',
        token: localStorage.getItem('token') || '',
        user: JSON.parse(localStorage.getItem('user')) || {},
        menus: JSON.parse(localStorage.getItem('menus')) || {},
        permissions: JSON.parse(localStorage.getItem('permissions')) || {},
        loginAs: localStorage.getItem('loginAs') || '',
        vendorState: '',
    },
    mutations: {
        auth_request(state) {
            state.status = 'loading';
        },
        auth_success(state, payload) {
            state.token = payload.token;
            state.user = payload.user;
            state.menus = payload.menus;
            state.permissions = payload.permissions;
            state.status = 'success';
            state.loginAs = payload.loginAs;
        },
        auth_failed(state) {
            state.status = 'error';
        },
        logout(state) {
            state.status = '';
            state.token = '';
            state.user = {};
            state.menus = {};
            state.permissions = {};
            state.loginAs = '';
        },
        request(state) {
            state.status = 'loading';
        },
        success(state) {
            state.status = 'success';
        },
        failed(state) {
            state.status = 'failed';
        },
    },
    actions: {
        doLogin({commit}, user) {
            var modelUser = user;
            return new Promise((resolve, reject) => {
                commit('auth_request');
                axios.post('/api/login', user)
                    .then(res => {
                        // get json data
                        const user = res.data.values.user;
                        const menus = res.data.values.menus;
                        const token = res.data.values.token;
                        const permissions = res.data.values.permissions;

                        // store token
                        localStorage.setItem('token', token);
                        localStorage.setItem('user', JSON.stringify(user));
                        localStorage.setItem('menus', JSON.stringify(menus));
                        localStorage.setItem('permissions', JSON.stringify(permissions));
                        localStorage.setItem('loginAs', modelUser.loginAs);

                        // trigger store mutator
                        commit('auth_success', { token: token, user: user, menus: menus, permissions: permissions, loginAs: modelUser.loginAs});
                        resolve(res);
                    }).catch(err => {
                        commit('auth_failed');
                        localStorage.removeItem('token');
                        reject(err);
                    });
            });
        },
        doLogout({commit}) {
            return new Promise((resolve, reject) => {
                localStorage.removeItem('token');
                localStorage.removeItem('user');
                localStorage.removeItem('menus');
                localStorage.removeItem('permissions');
                commit('logout');
                resolve();
            });
        },
        doGet({commit, dispatch}, params) {
            return new Promise((resolve, reject) => {
                commit('request');
                axios.get(params.url, { params: params.data, headers: { Authorization: 'Bearer '+ this.state.token } })
                    .then(res => {
                        commit('success');
                        resolve(res);
                    }).catch(err => {
                        commit('failed');
                        reject(err);
                        if(err.response.status == 401) {
                            dispatch('doLogout');
                            location.href = '/login';
                        } else if(err.response.status == 403)
                            location.href = '/not-found';
                    });
            });
        },
        doPost({commit, dispatch}, params) {
            return new Promise((resolve, reject) => {
                commit('request');
                axios.post(params.url, params.data, { headers: { Authorization: 'Bearer '+ this.state.token } })
                    .then(res => {
                        commit('success');
                        resolve(res);
                    }).catch(err => {
                        commit('failed');
                        reject(err);
                        if(err.response.status == 401) {
                            dispatch('doLogout');
                            location.href = '/login';
                        } else if(err.response.status == 403)
                            location.href = '/not-found';
                    });
            });
        },
        doDownload({commit, dispatch}, params) {
            return new Promise((resolve, reject) => {
                commit('request');
                axios.get(params.url, { params: params.data, headers: { Authorization: 'Bearer '+ this.state.token }, responseType: 'blob' })
                    .then(res => {
                        commit('success');

                        let blob = new Blob([ res.data ], { type: 'application/vnd.ms-excel' });
                        let fileUrl = window.URL.createObjectURL(blob);
                        let fileLink = document.createElement('a');

                        fileLink.href = fileUrl;
                        fileLink.setAttribute('download', 'file.xls');
                        document.body.appendChild(fileLink);
                        fileLink.click();

                        resolve(res);
                    }).catch(err => {
                        commit('failed');
                        reject(err);
                        if(err.response.status == 401) {
                            dispatch('doLogout');
                            location.href = '/login';
                        } else if(err.response.status == 403)
                            location.href = '/not-found';
                    });
            });
        },
    },
    getters: {
        isLoggedIn: state => !!state.token,
        getStatus: state => state.status,
        isLoading: state => state.status == 'loading',
        getToken: state => state.token,
        getUser: state => state.user,
        getMenus: state => state.menus,
        getPermissions: state => state.permissions,
        hasPermission: (state) => (perm) => {
            return state.permissions.indexOf(perm) > -1;
        },
        getVendorState: state => state.vendorState,
        getLoginAs: state => state.loginAs,
    }
});
