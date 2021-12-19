<template>
    <el-row>
        <el-col :span="24">
            <el-row class="mb20 pt20 pb20 b-t b-b bg-info lighter">
                <el-col :span="24" class="pl20 pr20">
                    <el-breadcrumb separator-class="el-icon-arrow-right">
                        <el-breadcrumb-item>Report Absensi Pegawai</el-breadcrumb-item>
                    </el-breadcrumb>
                </el-col>
            </el-row>
            <el-row>
                <el-col :span="24" class="pl20 pr10">
                    <el-card v-loading="isLoading">
                        <el-row class="mb10">
                            <el-col :span="3">
                                <span>Unit Kerja</span>
                            </el-col>
                            <el-col :span="10">
                                <el-select v-model="unit_kerja" placeholder="Pilih Unit Kerja" size="small" class="w100pc" @change="getEmployees">
                                    <el-option v-for="item in units" :key="item.id" :value="item.id" :label="item.name"></el-option>
                                </el-select>
                            </el-col>
                        </el-row>
                        <el-row class="mb10">
                            <el-col :span="3">
                                <span>Pegawai</span>
                            </el-col>
                            <el-col :span="10">
                                <el-select v-model="emp" placeholder="Pilih Pegawai" size="small" class="w100pc">
                                    <el-option v-for="item in emps" :key="item.id" :value="item.id" :label="item.name"></el-option>
                                </el-select>
                            </el-col>
                        </el-row>
                        <el-row class="mb20">
                            <el-col :span="3">
                                <span>Periode</span>
                            </el-col>
                            <el-col :span="8">
                                <el-date-picker v-model="periode" type="daterange" value-format="yyyy-MM-dd" range-separator="s.d" start-placeholder="Mulai" end-placeholder="Selesai" size="small"></el-date-picker>
                            </el-col>
                            <el-col :span="2">
                                <el-button type="primary" icon="el-icon-notebook-2" size="small" @click="doTable()"></el-button>
                                <el-tooltip class="item" effect="dark" content="Export report as excel" placement="bottom">
                                    <el-button type="success" icon="el-icon-document" size="small" @click="doExport()"></el-button>
                                </el-tooltip>
                            </el-col>
                        </el-row>
                        <template v-if="tableData.length >0">
                            <el-dialog title="New Data" :visible.sync="showDialog">
                                <el-form :model="model" :rules="rules" ref="formData" @submit.native.prevent="submitData" label-width="auto">
                                    <el-form-item label="Waktu">
                                        <el-date-picker v-model="model.waktu" type="datetime" value-format="yyyy-MM-dd HH:mm:ss" placeholder="Select date and time"></el-date-picker>
                                    </el-form-item>
                                    <el-form-item label="Klasifikasi">
                                        <el-select v-model="model.wfw" placeholder="Pilih Klasifikasi" size="small">
                                            <el-option key="WFH" value="WFH" label="WFH"></el-option>
                                            <el-option key="WFO" value="WFO" label="WFO"></el-option>
                                        </el-select>
                                    </el-form-item>
                                </el-form>
                                <span slot="footer" class="dialog-footer">
                                    <el-button type="danger" @click="showDialog = false">Cancel</el-button>
                                    <el-button type="primary" @click="submitData">Submit</el-button>
                                </span>
                            </el-dialog>
                            <el-row>
                                <el-col :offset="22" :span="2">
                                    <el-button type="primary" icon="el-icon-plus" size="small" @click="showInput(0)"> New Data</el-button>
                                </el-col>
                            </el-row>
                            <el-row>
                                <el-col :span="24">
                                    <el-table :data="tableData" @current-change="rowSelected" highlight-current-row>
                                        <el-table-column label="Waktu">
                                            <template slot-scope="scope">
                                                <i class="el-icon-time"></i>
                                                <span style="margin-left:10px">{{ scope.row.submitted_at }}</span>
                                            </template>
                                        </el-table-column>
                                        <el-table-column label="Klasifikasi">
                                            <template slot-scope="scope">
                                                <span>{{ scope.row.workfrom }}</span>
                                            </template>
                                        </el-table-column>
                                        <el-table-column label="Zona">
                                            <template slot-scope="scope">
                                                <span>{{ scope.row.tz }}</span>
                                            </template>
                                        </el-table-column>
                                        <el-table-column label="__" width="80">
                                            <template slot-scope="scope">
                                                <el-button type="success" icon="el-icon-edit" size="small" @click="showInput(scope.row.id)"></el-button>
                                            </template>
                                        </el-table-column>
                                    </el-table>
                                </el-col>
                            </el-row>
                        </template>
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
            unit_kerja: '',
            emp: '',
            units: [],
            emps: [],
            periode: '',
            tableData: [],
            showDialog: false,
            model: {
                id: 0,
                emp_id: 0,
                wfw: 'WFH',
                waktu: '',
            },
            rules: {
                waktu: [
                    {
                        required: true,
                        message: 'Please select date and time!',
                        trigger: blur
                    }
                ]
            }
        }
    },
    mounted() {
        this.getUnits();
    },
    methods: {
        getUnits() {
            var vm = this;
            this.$store.dispatch('doGet', { url: '/api/work-units/ls' })
                .then(res => {
                    if(res.data) {
                        vm.units = res.data;
                        vm.tableData = [];
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
        getEmployees(unit_id) {
            var vm = this;
            this.$store.dispatch('doGet', { url: '/api/work-units/'+ unit_id +'/employees' })
                .then(res => {
                    if(res.data) {
                        vm.emps = res.data;
                        vm.tableData = [];
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
        doTable() {
            let unit_id     = this.unit_kerja,
                emp         = this.emp,
                from        = this.periode[0],
                to          = this.periode[1];

            let vm = this;
            this.$store.dispatch('doGet', { url: '/api/absen/employee/'+ emp +'/from/'+ from +'/to/'+ to})
                .then(res => {
                    if(res.data)
                        vm.tableData = res.data;
                })
                .catch(err => {
                    this.$notify.error({
                        title: err.response.status +' - '+ err.response.statusText,
                        message: err.response.data,
                        duration: 2500
                    });
                });
        },
        doExport() {
            let data = {
                unit_id     : this.unit_kerja,
                emp         : this.emp,
                from        : this.periode[0],
                to          : this.periode[1]
            };

            let vm = this;
            this.$store.dispatch('doDownload', { url: '/api/absen/unit/xls', data: data })
            // this.$store.dispatch('doGet', { url: '/api/absen/unit/xls', data: data })
                // .then(res => {
                //     vm.isLoading = false;
                // })
                // .catch(err => {
                //     vm.isLoading = false;
                // });
        },
        showInput(id) {
            this.showDialog = true;
            this.model.emp_id = this.emp;
        },
        async submitData() {
            let isValid = await this.$refs.formData.validate();
            if(!isValid) 
                return;

            let vm = this;
            this.$store.dispatch('doPost', { url: '/api/absen/employee/store', data: this.model })
                .then(res => {
                    vm.$notify.success({
                        title: 'Success',
                        message: 'Data saved!'
                    });
                    this.doTable();
                    this.showDialog = false;
                })
                .catch(err => {
                    this.$notify.error({
                        title: 'Error',
                        message: err,
                        duration: 2500
                    });
                    this.showDialog = false;
                })
        },
        rowSelected(row) {
            this.model.id = row.id;
            this.model.wfw = row.workfrom;
            this.model.waktu = row.submitted_at;
        }
    },
    computed: {
        isLoading() {
            return this.$store.getters.isLoading;
        },
    }
}
</script>