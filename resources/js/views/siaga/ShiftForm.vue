<template>
    <el-row class="h100pc">
        <el-col :span="24" class="bg-info lighter h100pc">
            <el-row class="mb10 pt20 pb20 b-t">
                <el-col :span="24" class="pl20 pr20">
                    <el-breadcrumb separator-class="el-icon-arrow-right">
                        <el-breadcrumb-item>Siaga Shift</el-breadcrumb-item>
                        <el-breadcrumb-item>Entry</el-breadcrumb-item>
                    </el-breadcrumb>
                </el-col>
            </el-row>
            <el-row>
                <el-col :span="24" class="pl10 pr10">
                    <el-card>
                        <el-form :model="model" :rules="rules" ref="form" @submit.native.prevent="submit(model)" label-width="auto" v-loading="isLoading">
                            <el-form-item label="Nama" prop="nama">
                                <el-input v-model="model.nama"></el-input>
                            </el-form-item>
                            <el-form-item label="Jam Mulai Masuk" prop="masuk">
                                <el-time-picker v-model="model.mulaimasuk" placeholder="Jam Mulai Masuk" :picker-options="{ selectableRange: '00:00:00 - 23:59:59' }" value-format="HH:mm:ss" arrow-control></el-time-picker>
                            </el-form-item>
                            <el-form-item label="Jam Selesai Masuk" prop="masuk">
                                <el-time-picker v-model="model.selesaimasuk" placeholder="Jam Selesai Masuk" :picker-options="{ selectableRange: '00:00:00 - 23:59:59' }" value-format="HH:mm:ss" arrow-control></el-time-picker>
                            </el-form-item>
                            <el-form-item label="Jam Mulai Pulang" prop="pulang">
                                <el-time-picker v-model="model.mulaipulang" placeholder="Jam Mulai Pulang" :picker-options="{ selectableRange: '00:00:00 - 23:59:59' }" value-format="HH:mm:ss" arrow-control></el-time-picker>
                            </el-form-item>
                            <el-form-item label="Jam Selesai Pulang" prop="pulang">
                                <el-time-picker v-model="model.selesaipulang" placeholder="Jam Selesai Pulang" :picker-options="{ selectableRange: '00:00:00 - 23:59:59' }" value-format="HH:mm:ss" arrow-control></el-time-picker>
                            </el-form-item>
                            <el-divider class="mt10 mb10"></el-divider>
                            <el-form-item class="mbn">
                                <el-col :span="6" :offset="18" class="text-right">
                                    <el-button-group>
                                        <el-button type="danger" @click.native="$router.push({ name: 'shift' })">Cancel</el-button>
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
                nama: '',
                mulaimasuk: '',
                selesaimasuk: '',
                mulaipulang: '',
                selesaipulang: '',
            },
            rules: {
                nama: [
                    {
                        required: true,
                        message: 'Nama is required!',
                        trigger: 'blur'
                    }
                ],
                mulaimasuk: [
                    {
                        required: true,
                        message: 'Jam Mulai Masuk is required!',
                        trigger: 'blur'
                    }
                ],
                selesaimasuk: [
                    {
                        required: true,
                        message: 'Jam Selesai Masuk is required!',
                        trigger: 'blur'
                    }
                ],
                mulaipulang: [
                    {
                        required: true,
                        message: 'Jam Mulai Pulang is required!',
                        trigger: 'blur'
                    }
                ],
                selesaipulang: [
                    {
                        required: true,
                        message: 'Jam Selesai Pulang is required!',
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
    mounted() {
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
            this.$store.dispatch('doPost', { url: '/api/siaga/shift/store', data: model })
                .then(res => {
                    vm.$notify.success({
                        title: 'Success',
                        message: 'Data saved!'
                    });
                    vm.$router.push({ name: 'shift' });
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
            this.$store.dispatch('doGet', { url: '/api/siaga/shift/'+ id })
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
                    });
                });
        }
    }
}
</script>
