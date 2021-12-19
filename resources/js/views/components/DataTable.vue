<template>
    <el-row>
        <el-col :span="24">
            <el-row class="p10">
                <el-col :span="8">
                    <el-tooltip class="item" effect="dark" content="Show entries" placement="top-start">
                        <el-select v-model="viewData" size="mini" class="w70" @change="getTableData">
                            <el-option v-for="item in viewOpt" :key="item.value" :label="item.label" :value="item.value"></el-option>
                        </el-select>
                    </el-tooltip>
                    <el-tooltip class="item" effect="dark" content="Reset table" placement="top-start">
                        <el-button class="dt-btn-refresh" type="primary" icon="el-icon-refresh" size="mini" @click="refresh" plain></el-button>
                    </el-tooltip>
                </el-col>
                <el-col :span="8">
                    <el-input v-model="search" placeholder="Search" size="mini" prefix-icon="el-icon-search" @keydown.enter.native="getTableData"></el-input>
                </el-col>
                <el-col :span="8" class="text-right">
                    <el-button-group>
                        <el-button v-if="btnCreateVisible" type="primary" icon="el-icon-plus" size="mini" @click="navigate">Create</el-button>
                    </el-button-group>
                </el-col>
            </el-row>
            <el-row>
                <el-col :span="24">
                    <el-table :data="data" v-bind="$attrs" v-on="listeners" v-loading="loading" class="w100pc">
                        <slot name="columns">
                            <el-table-column v-for="column in columns" :sortable="column.sortable ? 'custom' : false" :key="column.prop" v-bind="column">
                                <template slot-scope="{ row }">
                                    <slot :name="column.prop || column.type || column.label" :row="row">
                                        {{ row[column.prop] }}
                                    </slot>
                                </template>
                            </el-table-column>
                        </slot>
                    </el-table>
                </el-col>
            </el-row>
            <el-row>
                <el-col :span="12">
                    <slot name="dataInfo" :from="from" :to="to" :total="totalData">
                        <p class="text-info fs12 mn pt10 pb10 pl10 va-center" style="height: 22px">From {{ from }} to {{ to }} of {{ totalData }} entries.</p>
                    </slot>
                </el-col>
                <el-col :span="12" class="text-right">
                    <slot name="pagination" :page="page" :total="totalPage">
                        <el-pagination :total="totalPage" :page-size="parseInt(viewData)" :current-page.sync="page" @current-change="getTableData" layout="prev, pager, next" class="pn pt10 pb10 mr5" small background></el-pagination>
                    </slot>
                </el-col>
            </el-row>
        </el-col>
    </el-row>
</template>

<style scoped>
.el-button.dt-btn-refresh {
    padding-left: 7px;
    padding-right: 7px;
}
</style>

<script>
export default {
    name: 'DataTable',
    inheritAttrs: false,
    props: {
        columns: {
            type: Array,
            default: () => []
        },
        getData: {
            type: Function,
            default: () => Promise.resolve([])
        },
        display: {
            type: String,
            default: '10'
        },
        createVisible: {
            type: Boolean,
            default: true
        },
        createRoute: {
            type: Object,
            default: {}
        }
    },
    data() {
        return {
            data: [],
            viewData: this.display,
            totalData: 10,
            from: 1,
            to: 10,
            page: 1,
            totalPage: 10,
            search: '',
            sortParams: [],
            viewOpt: [{
                value: '10',
                label: '10'
            }, {
                value: '25',
                label: '25'
            }, {
                value: '50',
                label: '50'
            }, {
                value: '100',
                label: '100'
            }],
            btnCreateVisible: this.createVisible,
            btnCreateRoute: this.createRoute,
            loading: false
        }
    },
    computed: {
        listeners() {
            return {
                ...this.$listeners,
                ['sort-change']: this.onSortChange
            }
        }
    },
    methods: {
        async getTableData(page) {
            this.loading = true;
            try {
                let res = await this.getData({
                    page: this.page,
                    search: this.search,
                    sortParams: this.sortParams,
                    viewData: this.viewData,
                });
                let json = res.data;
                this.data = json.data;
                this.from = json.from;
                this.to = json.to;
                this.totalData = json.totalData;
                this.totalPage = json.totalData;
            } finally {
                this.loading = false;
            }
        },
        onSortChange({ column, prop, order }) {
            if(prop !== null) {
                let sortOrder = order == 'ascending' ? 'asc' : 'desc' ;
                this.sortParams = [`${prop}|${sortOrder}`];
            } else {
                this.sortParams = [];
            }
            this.getTableData();
        },
        refresh() {
            this.sortParams = [];
            this.search = '';
            this.page = 1;
            this.getTableData();
        },
        navigate() {
            this.$router.push(this.btnCreateRoute);
        }
    },
    created() {
        this.getTableData();
    }
}
</script>