<template>
    <el-row class="h100pc">
        <el-col :span="24" class="bg-info lighter h100pc">
            <el-row class="mb10 pt20 pb20 b-t">
                <el-col :span="24" class="pl20 pr20">
                    <el-breadcrumb separator-class="el-icon-arrow-right">
                        <el-breadcrumb-item>User</el-breadcrumb-item>
                        <el-breadcrumb-item>Entry</el-breadcrumb-item>
                    </el-breadcrumb>
                </el-col>
            </el-row>
            <el-row>
                <el-col :span="24" class="pl10 pr10">
                    <el-card>
                        <el-form :model="model" :rules="rules" ref="form" @submit.native.prevent="submit(model)" label-width="auto" v-loading="isLoading">
                            <input v-model="model.id" type="hidden"/>
                            <el-form-item label="Role">
                                <el-select v-model="model.role" placeholder="Pick a role" class="w100pc">
                                    <el-option v-for="item in model.arrRoles" :key="item.id" :label="item.name" :value="item.id"></el-option>
                                </el-select>
                            </el-form-item>
                            <el-form-item label="Name" prop="name">
                                <el-input v-model="model.name"></el-input>
                            </el-form-item>
                            <el-form-item label="Username" prop="username">
                                <el-input v-model="model.username" :disabled="inputDisabled"></el-input>
                            </el-form-item>
                            <el-form-item label="Email" prop="email">
                                <el-input v-model="model.email"></el-input>
                            </el-form-item>
                            <el-form-item label="Work Units">
                                <el-select v-model="model.units" multiple filterable default-first-option placeholder="Pick work units" class="w100pc">
                                    <el-option v-for="item in arrUnits" :key="item.id" :label="item.name" :value="item.id"></el-option>
                                </el-select>
                            </el-form-item>
                            <el-divider class="mt10 mb10"></el-divider>
                            <el-form-item class="mbn">
                                <el-col :span="6" :offset="18" class="text-right">
                                    <el-button-group>
                                        <el-button type="danger" @click.native="$router.push({ name: 'user' })">Cancel</el-button>
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
                id: 0,
                name: '',
                username: '',
                email: '',
                role: '',
                arrRoles: [],
                units: []
            },
            rules: {
                name: [
                    {
                        required: true,
                        message: 'Name is required!',
                        trigger: 'blur'
                    }
                ],
                username: [
                    {
                        required: true,
                        message: 'Username is required!',
                        trigger: 'blur'
                    }
                ],
                email: [
                    {
                        required: true,
                        message: 'Email is required!',
                        trigger: 'blur'
                    }
                ],
            },
            inputDisabled: false,
            arrUnits: [],
        }
    },
    computed: {
        isLoading() {
            return this.$store.getters.isLoading;
        },
    },
    mounted() {
        // init form data
        var id = this.$route.params.id || 0;
        this.get(id);
        this.getWorkUnits();
    },
    methods: {
        async submit(model) {
            this.loading = true;
            let isValid = await this.$refs.form.validate();
            if(!isValid) 
                return;

            // submit form
            var vm = this;
            this.$store.dispatch('doPost', { url: '/api/users/store', data: model })
                .then(res => {
                    vm.$notify.success({
                        title: 'Success',
                        message: 'Data saved!'
                    });
                    vm.$router.push({ name: 'user' });
                })
                .catch(err => {
                    this.$notify.error({
                        title: err.response.status +' - '+ err.response.statusText,
                        message: err.response.data,
                        duration: 2500
                    });
                })
        },
        get(id) {
            var vm = this;
            this.$store.dispatch('doGet', { url: '/api/users/'+ id })
                .then(res => {
                    if(res.data) {
                        vm.model = res.data;
                        if(res.data.id)
                            vm.inputDisabled = true;
                    }
                })
                .catch(err => {
                    this.$notify.error({
                        title: err.response.status +' - '+ err.response.statusText,
                        message: err.response.data,
                        duration: 2500
                    });
                });
        },
        getWorkUnits() {
            var vm = this;
            this.$store.dispatch('doGet', { url: '/api/work-units/dt' })
                .then(res => {
                    if(res.data) {
                        vm.arrUnits = res.data;
                    }
                })
                .catch(err => {
                    this.$notify.error({
                        title: err.response.status +' - '+ err.response.statusText,
                        message: err.response.data,
                        duration: 2500
                    });
                });
        }
    }
}
</script>