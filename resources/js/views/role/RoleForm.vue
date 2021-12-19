<template>
    <el-row class="h100pc">
        <el-col :span="24" class="bg-info lighter h100pc">
            <el-row class="mb10 pt20 pb20 b-t">
                <el-col :span="24" class="pl20 pr20">
                    <el-breadcrumb separator-class="el-icon-arrow-right">
                        <el-breadcrumb-item>Role</el-breadcrumb-item>
                        <el-breadcrumb-item>Entry</el-breadcrumb-item>
                    </el-breadcrumb>
                </el-col>
            </el-row>
            <el-row>
                <el-col :span="24" class="pl10 pr10">
                    <el-card>
                        <el-form :model="model" :rules="rules" ref="form" @submit.native.prevent="submit(model)" label-width="auto" v-loading="isLoading">
                            <input v-model="model.id" type="hidden"/>
                            <input v-model="model.old_name" type="hidden"/>
                            <el-form-item label="Name" prop="name">
                                <el-input v-model="model.name"></el-input>
                            </el-form-item>
                            <el-form-item>
                                <el-tree :data="tree" ref="tree" node-key="id" :expand-on-click-node="false" :default-checked-keys="model.menus" show-checkbox default-expand-all>
                                    <span class="custom-tree-node" slot-scope="{ node, data }">
                                        <span><i v-bind:class="data.icon"></i> - {{ node.label }}</span>
                                        <span class="pr20">
                                            <el-checkbox-group v-model="model.permissions" size="mini">
                                                <el-checkbox-button v-for="perm in data.perms" :label="perm.name" :key="perm.id">{{ perm.name }}</el-checkbox-button>
                                            </el-checkbox-group>
                                        </span>
                                    </span>
                                </el-tree>
                            </el-form-item>
                            <el-divider class="mt10 mb10"></el-divider>
                            <el-form-item class="mbn">
                                <el-col :span="6" :offset="18" class="text-right">
                                    <el-button-group>
                                        <el-button type="danger" @click.native="$router.push({ name: 'role' })">Cancel</el-button>
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
.custom-tree-node {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 14px;
}
</style>

<script>
export default {
    data() {
        return {
            model: {
                id: 0,
                name: '',
                old_name: '',
                guard_name: 'web',
                menus: [],
                permissions: [],
                arrPerms: []
            },
            tree: [],
            rules: {
                name: [
                    {
                        required: true,
                        message: 'Name is required!',
                        trigger: 'blur'
                    }
                ],
            },
        }
    },
    computed: {
        isLoading() {
            return this.$store.getters.isLoading;
        },
    },
    created() {
        // init form data
        var id = this.$route.params.id || 0;
        this.get(id);
        this.loadTree();
    },
    methods: {
        async submit(model) {
            this.loading = true;
            let isValid = await this.$refs.form.validate();
            if(!isValid) 
                return;
            
            model.menus = this.$refs.tree.getCheckedKeys();
            model.permissions = Object.keys(model.arrPerms)
                .filter(key => {
                    return model.permissions.includes(key)
                })
                .map(key => {
                    return model.arrPerms[key]
                });
            delete model.arrPerms;

            // submit form
            var vm = this;
            this.$store.dispatch('doPost', { url: '/api/roles/store', data: model })
                .then(res => {
                    vm.$notify.success({
                        title: 'Success',
                        message: 'Data saved!'
                    });
                    vm.$router.push({ name: 'role' });
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
            this.$store.dispatch('doGet', { url: '/api/roles/'+ id })
                .then(res => {
                    if(res.data) {
                        vm.model = res.data;
                        if(res.data.id)
                            this.model.old_name = res.data.name;
                        else {
                            vm.model.menus.push(1, 6);
                            vm.model.permissions.push('change password', 'logout')
                        }
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
        loadTree() {
            this.$store.dispatch('doGet', { url: '/api/menus' })
                .then(res => {
                    this.tree = res.data;
                })
                .catch(err => {
                    this.$notify.error({
                        title: 'Error',
                        message: err,
                        duration: 2500
                    });
                });
        },
    }
}
</script>