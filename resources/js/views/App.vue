<template>
    <div>
        <transition name="bounce">
            <div key="wr1" v-if="isLoggedIn" class="content-wrapper-inside" id="div-main">
                <el-container class="content-wrapper">
                    <el-header>
                        <el-row>
                            <el-col :span="2" class="pt5 text-center">
                                <img src="/images/basarnas.png" width="48"/>
                            </el-col>
                            <el-col :span="22">
                                <h3 class="text-basic lighter">Absensi Online | Basarnas</h3>
                            </el-col>
                        </el-row>
                        <el-row>
                            <el-col :span="24">
                                <el-menu :default-active="activeIndex" background-color="#545c64" text-color="#fff" active-text-color="#ffd04b" :router="true" @select="handleSelect" mode="horizontal">
                                    <template v-for="menu in getMenus">
                                        <el-submenu v-if="menu.children" :key="menu.id" :index="menu.id.toString()">
                                            <template slot="title">
                                                <i :class="menu.icon"></i>
                                                <span>{{ menu.name }}</span>
                                            </template>
                                            <el-menu-item v-for="submenu in menu.children" :index="submenu.id.toString()" :route="{path: submenu.url}" :key="submenu.id"><i :class="submenu.icon"></i>&nbsp;{{ submenu.name }}</el-menu-item>
                                        </el-submenu>
                                        <el-menu-item v-else :index="menu.id.toString()" :route="{path: menu.url}" :key="menu.id">
                                            <i :class="menu.icon"></i>
                                            <span slot="title">{{ menu.name }}</span>
                                        </el-menu-item>
                                    </template>
                                </el-menu>
                            </el-col>
                        </el-row>
                    </el-header>
                    <el-main class="pn">
                        <router-view ref="mainView"></router-view>
                    </el-main>
                    <el-footer class="b-t" height="40px">
                        <el-row>
                            <el-col :span="4" :offset="20" class="pt10 text-basic lighter fs12" style="display: flex; align-items: center; justify-content: center">
                                Basarnas&nbsp;&copy; 2020
                            </el-col>
                        </el-row>
                    </el-footer>
                </el-container>
            </div>
            <div v-else class="content-wrapper-inside" id="div-login">
                <el-container v-if="isAbsenRoute">
                    <absen></absen>
                </el-container>
                <el-container v-else-if="isAbsenSiagaRoute">
                    <absen-siaga></absen-siaga>
                </el-container>
                <el-container v-else>
                    <el-main>
                        <el-row>
                            <el-col :span="24">
                                <el-row style="height: 200px">
                                    <el-col :span="24">&nbsp;</el-col>
                                </el-row>
                                <el-row>
                                    <el-col :span="6" :push="16">
                                        <el-card shadow="always">
                                            <div class="login-logo">
                                                <img src="/images/basarnas.png" width="72" alt="Absensi Online"/>
                                            </div>
                                            <el-form :model="model" :rules="rules" ref="form" @submit.native.prevent="doLogin(model)">
                                                <el-form-item prop="username">
                                                    <el-input v-model="model.username" placeholder="Username" prefix-icon="el-icon-user"></el-input>
                                                </el-form-item>
                                                <el-form-item prop="password">
                                                    <el-input v-model="model.password" type="password" placeholder="Password" prefix-icon="el-icon-lock"></el-input>
                                                </el-form-item>
                                                <el-form-item>
                                                    <el-button type="primary" :loading="isLoading" native-type="submit" class="w100pc" block>Login</el-button>
                                                </el-form-item>
                                            </el-form>
                                        </el-card>
                                    </el-col>
                                </el-row>
                            </el-col>
                        </el-row>
                    </el-main>
                </el-container>
            </div>
        </transition>
    </div>
</template>

<style>
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}
.content-wrapper {
    height: 100vh;
}
.content-wrapper-inside {
    width: 100%;
}
#div-login {
    background: url('/images/background.jpg') no-repeat top left;
    background-size: cover;
    height: 100vh;
}
.login-logo {
    margin-bottom: 12px;
    text-align: center;
}
.el-header {
    height: auto !important;
    padding: 0px !important;
}
.el-form-item__error {
    /* top: 25% !important;
    padding-left: 15px; */
    display: none;
}
</style>

<style scoped>
.bounce-enter-active {
    animation: bounce-in .7s;
}
.bounce-leave-active {
    animation: bounce-in .01s reverse;
}
@keyframes bounce-in {
    0% {
        transform: scale(0);
    }
    50% {
        transform: scale(1.1);
    }
    100% {
        transform: scale(1);
    }
}
</style>

<script>
import Absen from './Absen';
import AbsenSiaga from './siaga/AbsenSiaga';

export default {
    components: {
        Absen,
        AbsenSiaga
    },
    data() {
        return {
            activeIndex: '1',
            isCollapse: true,
            model: {
                username: '',
                password: '',
                loginAs: ''
            },
            rules: {
                username: [
                    {
                        required: true,
                        message: 'Username is required!',
                        trigger: 'blur'
                    },
                    {
                        min: 4,
                        message: 'Username length should be at least 5 characters!',
                        trigger: 'blur'
                    }
                ],
                password: [
                    {
                        required: true,
                        message: 'Password is required!',
                        trigger: 'blur'
                    },
                    {
                        min: 4,
                        message: 'Password length should be at least 5 characters!',
                        trigger: 'blur'
                    }
                ]
            },
            isAbsenRoute: false,
            isAbsenSiagaRoute: false,
        }
    },
    mounted() {
        this.activeIndex = '1';
        this.isAbsenRoute = this.$router.currentRoute.name == 'absen';
        this.isAbsenSiagaRoute = this.$router.currentRoute.name == 'absen-siaga';
        this.model.loginAs = this.$router.currentRoute.name;
    },
    methods: {
        menuClick() {
            this.isCollapse = !this.isCollapse;
            var ele = document.getElementsByClassName('el-aside-menu')[0];
            if(this.isCollapse) {
                ele.classList.remove('el-aside-menu-open');
                ele.classList.add('el-aside-menu-close');
            } else {
                ele.classList.add('el-aside-menu-open');
                ele.classList.remove('el-aside-menu-close');
            }
        },
        handleSelect(key, path) {
            this.activeIndex = key;
        },
        async doLogin(model) {
            this.activeIndex = '1';

            let isValid = await this.$refs.form.validate();
            if(!isValid)
                return;

            var vm = this;
            this.$store.dispatch('doLogin', model)
                .then(() => {
                    vm.$router.push({ name: 'home' });
                })
                .catch(err => console.log(err));
        },
        doLogout() {
            this.$store.dispatch('doLogout');
        }
    },
    computed: {
        isLoggedIn() {
            return this.$store.getters.isLoggedIn;
        },
        isLoading() {
            return this.$store.getters.isLoading;
        },
        getMenus() {
            return this.$store.getters.getMenus;
        }
    }
}
</script>
