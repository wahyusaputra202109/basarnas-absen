<template>
    <el-row class="h100pc">
        <el-col :span="24" class="bg-info lighter h100pc">
            <el-row class="mb10 pt20 pb20 b-t">
                <el-col :span="24" class="pl20 pr20">
                    <el-breadcrumb separator-class="el-icon-arrow-right">
                        <el-breadcrumb-item>Rekapitulasi Absensi Siaga</el-breadcrumb-item>
                    </el-breadcrumb>
                </el-col>
            </el-row>
            <el-row>
                <el-col :span="24" class="pl10 pr10">
                    <el-card>
                        <el-form :model="model" ref="form" @submit.native.prevent="submit(model)" label-width="auto" v-loading="isLoading">
                            <el-form-item label="Unit Kerja">
                                <el-select v-model="model.unit_id" placeholder="Pick a Unit Kerja" class="w100pc" size="small">
                                    <el-option value="" label="--    All Unit"></el-option>
                                    <el-option v-for="item in arrUnit" :key="item.id" :label="item.name" :value="item.id"></el-option>
                                </el-select>
                            </el-form-item>
                            <el-form-item label="Pangkat / Golongan">
                                <el-select v-model="model.level_id" placeholder="Pick a Pangkat / Golongan" class="w100pc" size="small">
                                    <el-option value="" label="--    All Pangkat / Golongan"></el-option>
                                    <el-option v-for="item in arrPangkat" :key="item.id" :label="item.name" :value="item.id"></el-option>
                                </el-select>
                            </el-form-item>
                            <!-- <el-form-item label="Peride">
                                <el-date-picker v-model="model.periode" type="daterange" value-format="yyyy-MM-dd" range-separator="s.d" start-placeholder="Mulai" end-placeholder="Selesai" size="small"></el-date-picker>
                            </el-form-item> -->
                            <el-form-item label="Bulan">
                                <el-col :span="8">
                                    <el-select v-model="model.bulan" placeholder="Pick a Bulan" class="w100pc" size="small">
                                        <el-option v-for="item in arrBulan" :key="item.value" :label="item.label" :value="item.value"></el-option>
                                    </el-select>
                                </el-col>
                                <el-col :span="4">
                                    <el-input-number v-model="model.tahun" :min="2020" size="small"></el-input-number>
                                </el-col>
                            </el-form-item>
                            <el-divider class="mt10 mb10"></el-divider>
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
                unit_id: '',
                level_id: '',
                periode: '',
                // from: '',
                // to: ''
                bulan: '',
                tahun: 0
            },
            arrBulan: [{
                value: 1,
                label: 'Januari'
            }, {
                value: 2,
                label: 'Februari'
            }, {
                value: 3,
                label: 'Maret'
            }, {
                value: 4,
                label: 'April'
            }, {
                value: 5,
                label: 'Mei'
            }, {
                value: 6,
                label: 'Juni'
            }, {
                value: 7,
                label: 'Juli'
            }, {
                value: 8,
                label: 'Agustus'
            }, {
                value: 9,
                label: 'September'
            }, {
                value: 10,
                label: 'Oktober'
            }, {
                value: 11,
                label: 'November'
            }, {
                value: 12,
                label: 'Desember'
            }],
            arrUnit: [],
            arrPangkat: []
        }
    },
    computed: {
        isLoading() {
            return this.$store.getters.isLoading;
        },
    },
    mounted() {
        // init form data
        this.getUnit();
        this.getPangkat();
        var today = new Date();
        this.model.bulan = today.getMonth() +1;
        this.model.tahun = today.getFullYear();

        console.log(this.arrUnit);
    },
    methods: {
        async submit(model) {
            let isValid = await this.$refs.form.validate();
            if(!isValid)
                return;

            // this.from = this.periode[0];
            // this.to = this.periode[1];

            // submit form
            let vm = this;
            this.$store.dispatch('doDownload', { url: '/api/siaga/rekap/xls', data: model })
            // this.$store.dispatch('doGet', { url: '/api/siaga/rekap/xls', data: model })
                .catch(err => {
                    console.log(err);
                })
        },
        getUnit() {
            var vm = this;
            this.$store.dispatch('doGet', { url: '/api/work-units/dt', data: { page: 1, search: '', viewData: 100, sort: [] } })
                .then(res => {
                    vm.arrUnit = res.data;
                })
        },
        getPangkat() {
            var vm = this;
            this.$store.dispatch('doGet', { url: '/api/levels/dt', data: { page: 1, search: '', viewData: 100, sort: [] } })
                .then(res => {
                    vm.arrPangkat = res.data.data;
                })
        },
    }
}
</script>
