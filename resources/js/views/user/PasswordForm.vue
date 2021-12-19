<template>
    <el-row>
        <el-col :span="24">
            <el-row class="mb20 pt20 pb20 b-t b-b bg-info lighter">
                <el-col :span="24" class="pl20 pr20">
                    <el-breadcrumb separator-class="el-icon-arrow-right">
                        <el-breadcrumb-item>Change Password</el-breadcrumb-item>
                    </el-breadcrumb>
                </el-col>
            </el-row>
            <el-row>
                <el-col :span="24" class="pl20 pr10">
                    <el-card >
                        <el-form :model="model" :rules="rules" ref="form" @submit.native.prevent="submit(model)" label-width="auto" v-loading="isLoading">
                            <el-form-item label="New Password">
                                <el-input v-model="model.passwd" type="password"></el-input>
                            </el-form-item>
                            <el-form-item label="Repeat Password">
                                <el-input v-model="model.repeatPasswd" type="password"></el-input>
                            </el-form-item>
                            <el-form-item class="mbn">
                                <el-col :span="6" :offset="18" class="text-right">
                                    <el-button-group>
                                        <el-button type="primary" native-type="submit">Submit</el-button>
                                    </el-button-group>
                                </el-col>
                            </el-form-item>
                        </el-form>
                    </el-card>
                </el-col>
            </el-row>
        </el-col>
    </el-row>
</template>

<style scoped>

</style>

<script>
export default {
    data() {
        return {
            model: {
                passwd: '',
                repeatPasswd: ''
            }
        }
    },
    methods: {
        async submit(model) {
            if(model.passwd != model.repeatPasswd) {
                this.$notify.error({
                    title: 'Password missmatch',
                    message: 'The password you\'re typing is missmatch.',
                });

                return;
            }

            // submit form
            var vm = this;
            this.$store.dispatch('doPost', { url: '/api/users/password', data: model })
                .then(res => {
                    vm.$notify.success({
                        title: 'Success',
                        message: 'Data saved!'
                    });
                    vm.$router.push({ name: 'home' });
                })
                .catch(err => {
                    this.$notify.error({
                        title: err.response.status +' - '+ err.response.statusText,
                        message: err.response.data,
                        duration: 2500
                    });
                })
        },
    },
    computed: {
        isLoading() {
            return this.$store.getters.isLoading;
        },
    },
}
</script>