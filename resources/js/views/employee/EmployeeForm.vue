<template>
    <el-row class="h100pc">
        <el-col :span="24" class="bg-info lighter h100pc">
            <el-row class="mb10 pt20 pb20 b-t">
                <el-col :span="24" class="pl20 pr20">
                    <el-breadcrumb separator-class="el-icon-arrow-right">
                        <el-breadcrumb-item>Employee</el-breadcrumb-item>
                        <el-breadcrumb-item>Entry</el-breadcrumb-item>
                    </el-breadcrumb>
                </el-col>
            </el-row>
            <el-row>
                <el-col :span="24" class="pl10 pr10">
                    <el-card>
                        <el-form :model="model" :rules="rules" ref="form" @submit.native.prevent="submit(model)" label-width="auto" v-loading="isLoading">
                            <el-form-item label="NIP" prop="nip">
                                <el-input v-model="model.nip" :disabled="isDisabled"></el-input>
                            </el-form-item>
                            <el-form-item label="Nama" prop="name">
                                <el-input v-model="model.name" :disabled="isDisabled"></el-input>
                            </el-form-item>
                            <el-form-item label="Pangkat" prop="level_id">
                                <el-select v-model="model.level_id" placeholder="Pick a Pangkat" class="w100pc" allow-cre>
                                    <el-option v-for="item in arrLevel" :key="item.id" :label="item.name" :value="item.id"></el-option>
                                </el-select>
                            </el-form-item>
                            <el-form-item label="Jabatan" prop="position_id">
                                <el-select v-model="model.position_id" placeholder="Type here to pick a Jabatan" class="w100pc" filterable remote :remote-method="getPosition" @change="selectJabatan" allow-create>
                                    <el-option v-for="item in arrPosition" :key="item.id" :label="item.name" :value="item.id"></el-option>
                                </el-select>
                            </el-form-item>
                            <el-form-item label="Unit Kerja">
                                <el-select v-model="model.unit_id" placeholder="Pick a Unit" class="w100pc" :disabled="isDisabled">
                                    <el-option v-for="item in arrUnit" :key="item.id" :label="item.name" :value="item.id"></el-option>
                                </el-select>
                            </el-form-item>
                            <el-divider class="mt10 mb10"></el-divider>
                            <el-form-item class="mbn">
                                <el-col :span="6" :offset="18" class="text-right">
                                    <el-button-group>
                                        <el-button type="danger" @click.native="$router.push({ name: 'employee' })">Cancel</el-button>
                                        <el-button type="primary" native-type="submit">Submit</el-button>
                                    </el-button-group>
                                </el-col>
                            </el-form-item>
                        </el-form>
                    </el-card>

                    <el-dialog title="Select Unit Kerja" width="80%" :visible.sync="mdlUnit">
                        <el-form :model="modelUnit" :rule="rulesUnit" ref="formUnit" @submit.native.prevent="submit(modelUnit)" label-width="auto" v-loading="isLoading">
                            <el-form-item label="Unit Kerja" prop="name">
                                <el-select v-model="modelUnit.id" placeholder="Pick a Unit" class="w100pc" @change="selectUnit">
                                    <el-option v-for="item in arrUnit" :key="item.id" :label="item.name" :value="item.id"></el-option>
                                </el-select>
                            </el-form-item>
                            <el-divider class="mt10 mb10"></el-divider>
                            <el-form-item class="mbn">
                                <el-col :span="6" :offset="18" class="text-right">
                                    <el-button-group>
                                        <el-button type="info" @click.native="mdlUnit = false">OK</el-button>
                                        <!-- <el-button type="primary" @click-native="model.unit_id = modelUnit.id">Submit</el-button> -->
                                    </el-button-group>
                                </el-col>
                            </el-form-item>
                        </el-form>
                    </el-dialog>
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
                name: '',
                position_id: '',
                level_id: '',
                unit_id: ''
            },
            rules: {
                nip: [
                    {
                        required: true,
                        message: 'NIP is required!',
                        trigger: 'blur'
                    }
                ],
                name: [
                    {
                        required: true,
                        message: 'Nama is required!',
                        trigger: 'blur'
                    }
                ],
                level_id: [
                    {
                        required: true,
                        message: 'Pangkat is required!',
                        trigger: 'change'
                    }
                ],
                position_id: [
                    {
                        required: true,
                        message: 'Jabatan is required!',
                        trigger: 'change'
                    }
                ],
            },
            arrPosition: [],
            arrLevel: [],
            arrUnit: [],
            isDisabled: false,
            mdlUnit: false,
            modelUnit: {
                id: '',
                name: ''
            },
            rulesUnit: {
                name: {
                    required: true,
                    message: 'Unit Kerja is required!',
                    trigger: 'blur'
                }
            }
        }
    },
    computed: {
        isLoading() {
            return this.$store.getters.isLoading;
        },
    },
    mounted() {
        // init shift data
        this.getLevel();
        this.getUnit();
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
            this.$store.dispatch('doPost', { url: '/api/employees/store', data: model })
                .then(res => {
                    vm.$notify.success({
                        title: 'Success',
                        message: 'Data saved!'
                    });
                    vm.$router.push({ name: 'employee' });
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
            this.$store.dispatch('doGet', { url: '/api/employees/'+ id })
                .then(res => {
                    vm.isDisabled = false;
                    if(res.data) {
                        vm.model = res.data;
                        vm.arrPosition.push({ id: res.data.position.id, name: res.data.position.name });
                        vm.model.unit_id = res.data.position.unit.id;
                        vm.isDisabled = true;
                    }
                })
                .catch(err => {
                    this.$notify.error({
                        title: err.response.status +' - '+ err.response.statusText,
                        message: err.response.data,
                    });
                });
        },
        getPosition(position) {
            if(position !=='') {
                var vm = this;
                this.$store.dispatch('doGet', { url: '/api/positions/dt', data: { page: 1, search: position, viewData: 100, sort: [] } })
                    .then(res => {
                        vm.arrPosition = res.data.data;
                    })
            } else {
                this.arrPosition = [];
            }
        },
        getLevel() {
            var vm = this;
            this.$store.dispatch('doGet', { url: '/api/levels/dt', data: { page: 1, search: '', viewData: 100, sort: [] } })
                .then(res => {
                    vm.arrLevel = res.data.data;
                })
        },
        getUnit() {
            var vm = this;
            this.$store.dispatch('doGet', { url: '/api/work-units/dt', data: { page: 1, search: '', viewData: 100, sort: [] } })
                .then(res => {
                    vm.arrUnit = res.data;
                })
        },
        selectJabatan(val) {
            this.mdlUnit = false;
            var res = this.arrPosition.filter(item => {
                return item.id == val;
            });
            this.model.unit_id = res.length >0 ? res[0].unit.id : '';
            if(isNaN(val)) {
                this.modelUnit.id = this.model.unit_id;
                this.mdlUnit = true;
            }
        },
        selectUnit(val) {
            this.model.unit_id = val;
        }
    }
}
</script>
