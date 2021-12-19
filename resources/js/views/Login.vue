<template>
    <el-container class="content-wrapper">
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
                                        <el-button type="primary" :loading="loading" native-type="submit" class="w100pc" block>Login</el-button>
                                    </el-form-item>
                                </el-form>
                            </el-card>
                        </el-col>
                    </el-row>
                </el-col>
            </el-row>
        </el-main>
    </el-container>
</template>

<script>
import store from '../store';

export default {
    data() {
        return {
            loading: store.state.status == 'loading',
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
                        min: 3,
                        message: 'Username length should be at least 3 characters!',
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
    methods: {
        async doLogin(model) {
            let isValid = await this.$refs.form.validate();
            if(!isValid) 
                return;

            store.dispatch('doLogin', model);
        }
    }
}
</script>

<style scoped>
.content-wrapper {
    height: 100vh;
}
</style>