<template>
    <el-row class="h100pc">
        <el-col :span="24" class="bg-info lighter h100pc">
            <el-row class="mb10 pt20 pb20 b-t">
                <el-col :span="20" class="pl20">
                    <el-breadcrumb separator-class="el-icon-arrow-right">
                        <el-breadcrumb-item>Holiday</el-breadcrumb-item>
                        <el-breadcrumb-item>Table</el-breadcrumb-item>
                    </el-breadcrumb>
                </el-col>
            </el-row>
            <el-row>
                <el-col :span="24" class="pl10 pr10">
                    <el-card body-style="padding: 0;background-color: #f5f5f5">
                        <data-table :get-data="getData" ref="table" :columns="columns" :display="'25'" :create-visible="hasPermission('create holiday')" :create-route="{ name: 'holiday-create' }">
                            <span slot="date_frm" slot-scope="{ row }">
                                {{ row.date_frm }}
                            </span>
                            <span slot="description" slot-scope="{ row }">
                                {{ row.description }}
                            </span>
                            <div slot="__" slot-scope="{ row }">
                                <el-button-group>
                                    <el-tooltip class="item" effect="dark" content="Edit item" placement="top-start">
                                        <el-button size="mini" icon="el-icon-edit" type="success" @click="doAction('edit', row)" v-if="hasPermission('update holiday')"></el-button>
                                    </el-tooltip>
                                    <el-tooltip class="item" effect="dark" content="Delete item" placement="top-start">
                                        <el-button size="mini" icon="el-icon-delete" type="danger" @click="doAction('delete', row)" v-if="hasPermission('delete holiday')"></el-button>
                                    </el-tooltip>
                                </el-button-group>
                            </div>
                        </data-table>
                    </el-card>
                </el-col>
            </el-row>
        </el-col>
    </el-row>
</template>

<style scoped>
button.el-button.item {
    padding-left: 7px;
    padding-right: 7px;
}
</style>

<script>
import DataTable from "../components/DataTable";

export default {
    components: {
        DataTable
    },
    data() {
        return {
            columns: [{
                prop: 'date_frm',
                label: 'Date',
                sortable: true,
                className: 'pl10'
            }, {
                prop: 'description',
                label: 'Desc',
                sortable: true,
            }, {
                label: '__',
                width: '120',
                className: 'text-center'
            }]
        }
    },
    methods: {
        getData({ page, search, sortParams, viewData }) {
            return this.$store.dispatch('doGet', { url: '/api/holidays/dt', data: { page, search, viewData, sort: sortParams } })
                .then(res => {
                    return {
                        data: res.data,
                        totalPage: res.totalPage
                    }
                })
        },
        doAction(action, row) {
            this.$confirm('Are you sure want to '+ action +' this item?', 'Warning', {
                    type: 'warning'
                })
                .then(_ => {
                    if(action == 'edit')
                        this.$router.push({ name: 'holiday-create', params: { id: row.id } });
                    else 
                        this.$store.dispatch('doGet', { url: '/api/holidays/delete/'+ row.id })
                            .then(res => {
                                this.$refs.table.getTableData();
                                this.$notify.success({
                                    title: 'Success',
                                    message: 'Holiday data deleted successfully.'
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
                .catch(_ => {})
        },
        hasPermission(perm) {
            return this.$store.getters.hasPermission(perm);
        }
    }
}
</script>