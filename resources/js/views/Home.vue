<template>
    <el-row>
        <el-col :span="24">
            <el-row class="mb20 pt20 pb20 b-t b-b bg-info lighter">
                <el-col :span="24" class="pl20 pr20">
                    <el-breadcrumb separator-class="el-icon-arrow-right">
                        <el-breadcrumb-item>Home</el-breadcrumb-item>
                    </el-breadcrumb>
                </el-col>
            </el-row>
            <el-row>
                <el-col :span="24" class="pl20 pr10">
                    <el-card v-if="isSiaga">
                        <el-calendar>
                            <template slot="dateCell" slot-scope="{ date, data }">
                                <p class="mn" :style="(typeof dataSiaga != 'undefined' && (dataSiaga.weekends.indexOf(data.day) >=0 || dataSiaga.holidays.indexOf(data.day) >=0)) ? 'background-color:#fef0f0;color:#f56c6c;font-weight:bold' : ''">{{ date.getDate() }}</p>
                                <span v-if="typeof dataSiaga != 'undefined' && dataSiaga.data.hasOwnProperty(data.day)">
                                    <span v-if="dataSiaga.data[data.day].length <=2">
                                        <div v-for="item in dataSiaga.data[data.day]" :key="item.nip">
                                            <el-link type="success" @click="siagaLinkClicked(data.day)">{{ item.namashort }}</el-link>
                                        </div>
                                    </span>
                                    <span v-else>
                                        <div>
                                            <el-link type="success" @click="siagaLinkClicked(data.day)">{{ dataSiaga.data[data.day][0].namashort }}</el-link>
                                            <el-link type="info" @click="siagaLinkClicked(data.day)">{{ dataSiaga.data[data.day].length -1 }} more...</el-link>
                                        </div>
                                    </span>
                                </span>
                            </template>
                        </el-calendar>

                        <el-dialog :title="'Siaga Assign '+ selectedSiaga.tanggal" width="80%" :visible.sync="isSiagaModal">
                            <el-table style="width:100%" :data="selectedSiaga.table" stripe>
                                <el-table-column prop="nip" label="NIP"></el-table-column>
                                <el-table-column prop="nama" label="Nama"></el-table-column>
                                <el-table-column prop="level" label="Pangkat"></el-table-column>
                                <el-table-column prop="jabatan" label="Jabatan"></el-table-column>
                                <el-table-column prop="shift" label="Shift"></el-table-column>
                                <el-table-column prop="masuk" label="Jam Masuk"></el-table-column>
                                <el-table-column prop="pulang" label="Jam Pulang"></el-table-column>
                            </el-table>
                        </el-dialog>
                    </el-card>
                    <el-card v-else v-loading="isLoading">
                        <el-row class="mb10">
                            <el-col :span="3">
                                <span>Unit Kerja</span>
                            </el-col>
                            <el-col :span="10">
                                <el-select v-model="unit_kerja" placeholder="Pilih Unit Kerja" size="small" class="w100pc">
                                    <el-option v-for="item in units" :key="item.id" :value="item.id" :label="item.name"></el-option>
                                </el-select>
                            </el-col>
                        </el-row>
                        <el-row>
                            <el-col :span="3">
                                <span>Periode</span>
                            </el-col>
                            <el-col :span="9">
                                <el-date-picker v-model="periode" type="daterange" range-separator="s.d" start-placeholder="Mulai" end-placeholder="Selesai" size="small"></el-date-picker>
                            </el-col>
                            <el-col :span="1">
                                <el-tooltip class="item" effect="dark" content="Export report as excel" placement="bottom">
                                    <el-button type="success" icon="el-icon-document" size="small" @click="doExport()"></el-button>
                                </el-tooltip>
                            </el-col>
                        </el-row>
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
            units: [],
            periode: '',
            isLoading: false,
            isSiaga: false,
            dataSiaga: [],
            isSiagaModal: false,
            selectedSiaga: {
                tanggal: '',
                table: []
            },
        }
    },
    mounted() {
        this.getUnits();

        if(this.getLoginAs == 'admin-siaga') {
            this.isSiaga = true;
            this.dataSiaga = this.getAssignByMonth();
        }
    },
    methods: {
        getUnits() {
            var vm = this;
            this.$store.dispatch('doGet', { url: '/api/work-units/ls' })
                .then(res => {
                    if(res.data) {
                        vm.units = res.data;
                    }
                })
                .catch(err => {
                    this.$notify.error({
                        title: err.response.status +' - '+ err.response.statusText,
                        message: err.response.data,
                    });
                });
        },
        doExport() {
            this.isLoading = true;
            let data = {
                unit_id     : this.unit_kerja,
                from        : this.periode[0].getFullYear() +'-'+ (this.periode[0].getMonth()+1) +'-'+ this.periode[0].getDate() +' 00:00:00',
                to          : this.periode[1].getFullYear() +'-'+ (this.periode[1].getMonth()+1) +'-'+ this.periode[1].getDate() +' 23:59:59',
            };

            let vm = this;
            this.$store.dispatch('doDownload', { url: '/api/absen/xls', data: data })
            // this.$store.dispatch('doDownload', { url: '/api/absen/xls-per-unit', data: data })
            // this.$store.dispatch('doGet', { url: '/api/absen/xls-per-unit', data: data })
                .then(res => {
                    vm.isLoading = false;
                })
                .catch(err => {
                    vm.isLoading = false;
                })

        },
        getAssignByMonth() {
            var now = new Date();
            var vm = this;
            this.$store.dispatch('doGet', { url: '/api/siaga/assign/get/'+ (now.getMonth()+1) })
                .then(res => {
                    vm.dataSiaga = res.data;
                })
        },
        siagaLinkClicked(tgl) {
            this.isSiagaModal = true;
            this.selectedSiaga.tanggal = tgl.split('-').reverse().join('/');
            this.selectedSiaga.table = this.dataSiaga.data[tgl];
        }
    },
    computed: {
        getLoginAs() {
            return this.$store.getters.getLoginAs;
        },
    }
    // beforeRouteEnter(to, from, next) {
    //     next((vm) => {
    //         if(from.name == 'admin-siaga') {
    //             vm.isSiaga = true;
    //             vm.dataSiaga = vm.getAssignByMonth();
    //         }
    //     })
    // }
}
</script>
