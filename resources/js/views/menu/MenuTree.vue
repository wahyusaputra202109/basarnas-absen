<template>
    <el-row class="h100pc">
        <el-col :span="24" class="bg-info lighter h100pc">
            <el-row class="mb10 pt20 pb20 b-t">
                <el-col :span="20" class="pl20">
                    <el-breadcrumb separator-class="el-icon-arrow-right">
                        <el-breadcrumb-item>Menu</el-breadcrumb-item>
                        <el-breadcrumb-item>Tree</el-breadcrumb-item>
                    </el-breadcrumb>
                </el-col>
                <el-col :span="4" class="pr30 text-right fs12" v-if="hasPermission('create menu')">
                    <el-link type="success" icon="el-icon-plus" @click="navigate('create', 0, 0)">New menu</el-link>
                </el-col>
            </el-row>
            <el-row>
                <el-col :span="24" class="pl10 pr10">
                    <el-card>
                        <el-tree :data="model" node-key="id" default-expand-all :expand-on-click-node="false" v-loading="isLoading">
                            <span class="custom-tree-node" slot-scope="{ node, data }">
                                <span><i :class="data.icon"></i> - {{ node.label }}</span>
                                <span>
                                    <span class="pr30 text-info">{{ data.attrs.slug }}</span>
                                    <span class="pr30">
                                        <el-tooltip class="item" effect="dark" :content="data.attrs.is_active ? 'Active' : 'Inactive'" placement="top-start">
                                            <i :class="data.attrs.is_active ? 'el-icon-success text-success' : 'el-icon-warning text-danger'"></i>
                                        </el-tooltip>
                                    </span>
                                    <el-button-group>
                                        <el-tooltip class="item" effect="dark" content="Append item" placement="top-start" v-if="hasPermission('create menu')">
                                            <el-button size="mini" type="primary" icon="el-icon-plus" @click="navigate('create', data.id, 0)"></el-button>
                                        </el-tooltip>
                                        <el-tooltip class="item" effect="dark" content="Edit item" placement="top-start" v-if="hasPermission('update menu')">
                                            <el-button size="mini" type="success" icon="el-icon-edit" @click="navigate('create', 0, data.id)"></el-button>
                                        </el-tooltip>
                                        <el-tooltip class="item" effect="dark" content="Delete item" placement="top-start" v-if="hasPermission('delete menu')">
                                            <el-button size="mini" type="danger" icon="el-icon-delete" @click="doDelete(data.id)"></el-button>
                                        </el-tooltip>
                                    </el-button-group>
                                </span>
                            </span>
                        </el-tree>
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
button.el-button.item {
    padding-left: 7px;
    padding-right: 7px;
}
</style>

<style>
.el-tree-node__content {
    padding-top: 7px;
    padding-bottom: 7px;
}
</style>

<script>
export default {
    data() {
        return {
            model: []
        }
    },
    mounted() {
        this.loadTree();
    },
    methods: {
        loadTree() {
            this.$store.dispatch('doGet', { url: '/api/menus' })
                .then(res => {
                    this.model = res.data;
                })
                .catch(err => {
                    this.$notify.error({
                        title: 'Error',
                        message: err,
                        duration: 2500
                    });
                });
        },
        navigate(kind, parent, id) {
            this.$router.push({ name: 'menu-'+ kind, params: { parent: parent, id: id } });
        },
        doDelete(id) {
            this.$confirm('Are you sure want to delete this menu item?', 'Warning', {
                    type: 'warning'
                })
                .then(_ => {
                    this.$store.dispatch('doGet', { url: 'api/menus/delete/'+ id })
                        .then(res => {
                            this.loadTree();
                            this.$notify.success({
                                title: 'Success',
                                message: 'Menu item deleted successfully.'
                            });
                        })
                        .catch(err => {
                            this.$notify.error({
                                title: 'Error',
                                message: err,
                                duration: 2500
                            });
                        });
                })
                .catch(_ => {});
        },
        hasPermission(perm) {
            return this.$store.getters.hasPermission(perm);
        }
    },
    computed: {
        isLoading() {
            return this.$store.getters.isLoading;
        },
    }
}
</script>