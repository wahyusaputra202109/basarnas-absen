<template>
    <el-row class="h100pc">
        <el-col :span="24" class="bg-info lighter h100pc">
            <el-row class="mb10 pt20 pb20 b-t">
                <el-col :span="24" class="pl20 pr20">
                    <el-breadcrumb separator-class="el-icon-arrow-right">
                        <el-breadcrumb-item>Siaga Assign</el-breadcrumb-item>
                        <el-breadcrumb-item>Entry</el-breadcrumb-item>
                    </el-breadcrumb>
                </el-col>
            </el-row>
            <el-row>
                <el-col :span="24" class="pl10 pr10">
                    <el-card>
                        <el-form :model="model" :rules="rules" ref="form" @submit.native.prevent="submit(model)" label-width="auto" v-loading="isLoading">
                            <el-form-item label="Pegawai" prop="nip">
                                <el-select v-model="model.nip" placeholder="Type NIP or Nama to pick a Pegawai" class="w100pc" filterable remote :remote-method="getEmp" @change="selectEmp">
                                    <el-option v-for="item in arrEmp" :key="item.nip" :label="item.nip +' - '+ item.name" :value="item.nip"></el-option>
                                </el-select>
                            </el-form-item>
                            <el-form-item label="Pangkat">
                                <el-input v-model="model.level" readonly></el-input>
                            </el-form-item>
                            <el-form-item label="Jabatan">
                                <el-input v-model="model.position" readonly></el-input>
                            </el-form-item>
                            <el-form-item label="Tanggal" prop="tanggal">
                                <el-date-picker type="date" v-model="model.tanggal" placeholder="Tanggal" format="dd/MM/yyyy" value-format="dd/MM/yyyy"></el-date-picker>
                            </el-form-item>
                            <el-form-item label="Shift" prop="shift_id">
                                <el-select v-model="model.shift_id" placeholder="Pick a Shift" class="w100pc">
                                    <el-option v-for="item in arrShift" :value="item.id" :key="item.id" :label="item.nama +' (Masuk: '+ item.mulaimasuk +' s.d '+ item.selesaimasuk +', Pulang: '+ item.mulaipulang +' s.d '+ item.selesaipulang +')'"></el-option>
                                </el-select>
                            </el-form-item>
                            <el-form-item label="Siaga Jabatan" prop="jabatan_id">
                                <el-select v-model="model.jabatan_id" placeholder="Pick a Siaga Jabatan" class="w100pc">
                                    <el-option v-for="item in arrSiagaJabatan" :value="item.id" :key="item.id" :label="item.name"></el-option>
                                </el-select>
                            </el-form-item>
                            <el-form-item>
                                <el-input type="textarea" :row="2" placeholder="Keterangan" v-model="model.keterangan" />
                            </el-form-item>
                            <el-divider class="mt10 mb10"></el-divider>
                            <el-form-item class="mbn">
                                <el-col :span="6" :offset="18" class="text-right">
                                    <el-button-group>
                                        <el-button type="danger" @click.native="$router.push({ name: 'assign' })">Cancel</el-button>
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
                nip: '',
                level: '',
                position: '',
                shift_id: '',
                tanggal: '',
                jabatan_id: '',
                keterangan: '',
            },
            rules: {
                nip: [
                    {
                        required: true,
                        message: 'Pegawai is required!',
                        trigger: 'blur'
                    }
                ],
                tanggal: [
                    {
                        required: true,
                        message: 'Tanggal is required!',
                        trigger: 'blur'
                    }
                ],
                shift_id: [
                    {
                        required: true,
                        message: 'Shift is required!',
                        trigger: 'change'
                    }
                ],
                jabatan_id: [
                    {
                        required: true,
                        message: 'Siaga Jabatan is required!',
                        trigger: 'change'
                    }
                ],
            },
            arrEmp: [],
            arrShift: [],
            arrSiagaJabatan: []
        }
    },
    computed: {
        isLoading() {
            return this.$store.getters.isLoading;
        },
    },
    mounted() {
        // init shift data
        this.getShift();
        this.getSiagaJabatan();
        // init form data
        var id = this.$route.params.id || 0;
        if(id >0)
            this.get(id);
    },
    methods: {
        async submit(model) {
            this.loading = true;
            let isValid = await this.$refs.form.validate();
            if(!isValid)
                return;

            // submit form
            var vm = this;
            this.$store.dispatch('doPost', { url: '/api/siaga/assign/store', data: model })
                .then(res => {
                    vm.$notify.success({
                        title: 'Success',
                        message: 'Data saved!'
                    });
                    vm.$router.push({ name: 'assign' });
                })
                .catch(err => {
                    this.$notify.error({
                        title: err.response.status +' - '+ err.response.statusText,
                        message: err.response.data,
                    });
                })
        },
        get(id) {
            var vm = this;
            this.$store.dispatch('doGet', { url: '/api/siaga/assign/'+ id })
                .then(res => {
                    if(res.data) {
                        vm.model = res.data;
                        vm.model.tanggal = res.data.tanggal_f;
                        vm.model.level = res.data.employee.level.name;
                        vm.model.position = res.data.employee.position.name;
                        // if(res.data.id)
                        //     vm.inputDisabled = true;
                    }
                })
                .catch(err => {
                    this.$notify.error({
                        title: err.response.status +' - '+ err.response.statusText,
                        message: err.response.data,
                    });
                });
        },
        getEmp(emp) {
            if(emp !=='') {
                var vm = this;
                this.$store.dispatch('doGet', { url: '/api/employees/dt', data: { page: 1, search: emp, viewData: 100, sort: [] } })
                    .then(res => {
                        vm.arrEmp = res.data.data;
                    })
            } else {
                this.arrEmp = [];
            }
        },
        selectEmp(nip) {
            if(nip !=='') {
                let res = this.arrEmp.filter(item => {
                    return item.nip === nip;
                });
                if(res.length >0) {
                    this.model.position = res[0].position.name;
                    this.model.level = res[0].level.name;
                }
            }
        },
        getShift() {
            var vm = this;
            this.$store.dispatch('doGet', { url: '/api/siaga/shift/dt', data: { page: 1, search: '', viewData: 100, sort: [] } })
                .then(res => {
                    vm.arrShift = res.data.data;
                })
        },
        getSiagaJabatan() {
            var vm = this;
            this.$store.dispatch('doGet', { url: '/api/siaga/position/dt', data: { page: 1, search: '', viewData: 100, sort: [] } })
                .then(res => {
                    vm.arrSiagaJabatan = res.data.data;
                })
        }
    }
}
</script>
