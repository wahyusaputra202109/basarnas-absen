<template>
    <div>
        <transition name="bounce">
        <div key="wr1" v-if="isLoggedIn" class="content-wrapper-inside">
            <el-container class="content-wrapper">
                <el-aside class="el-aside-menu el-aside-menu-close">
                    <el-row>
                        <el-col :span="24">
                            <el-menu :default-active="activeIndex" class="el-menu-vertical" background-color="#545c64" text-color="#fff" active-text-color="#ffd04b" :collapse="isCollapse" :router="true" @select="handleSelect" :collapse-transition="false">
                                <el-menu-item style="height: 59px" @click="menuClick">
                                    <i class="el-icon-menu"></i>
                                </el-menu-item>
                                <el-divider class="mn bg-info"></el-divider>
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
                </el-aside>
                <el-container>
                    <el-header>
                        <el-row>
                            <el-col :span="20">
                                <h3>
                                    <span>[logo] Starter v68</span>
                                </h3>
                            </el-col>
                        </el-row>
                    </el-header>
                    <el-main class="pn">
                        <router-view></router-view>
                    </el-main>
                    <el-footer class="b-t" height="40px">
                        <el-row>
                            <el-col :span="4" :offset="20"><p class="text-basic lighter text-right fs12">adesr &copy; 2020</p></el-col>
                        </el-row>
                    </el-footer>
                </el-container>
            </el-container>
        </div>
        <div v-else class="content-wrapper-inside">
            <el-container>
                <el-main>
                    <el-row>
                        <el-col :span="24">
                            <el-row style="height: 200px">
                                <el-col :span="24">&nbsp;</el-col>
                            </el-row>
                            <el-row>
                                <el-col :span="6" :push="16">
                                    <el-card shadow="always">
                                        <h2 class="text-center mb30">[Application Name]</h2>
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
.el-menu-vertical {
    min-height: 100vh;
}
/* .el-main {
    -webkit-transition: all 0.5s ease;
    -moz-transition: all 0.5s ease;
    -o-transition: all 0.5s ease;
    transition: all 0.5s ease;
} */
.el-aside-menu-open {
    width: 240px !important;
}
.el-aside-menu-close {
    width: 64px !important;
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
export default {
    data() {
        return {
            activeIndex: '1',
            isCollapse: true,
            model: {
                username: '',
                password: ''
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
            }
        }
    },
    mounted() {
        this.activeIndex = '1';
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