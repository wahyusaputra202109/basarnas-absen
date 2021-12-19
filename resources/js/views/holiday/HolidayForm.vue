<template>
    <el-row class="h100pc">
        <el-col :span="24" class="bg-info lighter h100pc">
            <el-row class="mb10 pt20 pb20 b-t">
                <el-col :span="24" class="pl20 pr20">
                    <el-breadcrumb separator-class="el-icon-arrow-right">
                        <el-breadcrumb-item>Holiday</el-breadcrumb-item>
                        <el-breadcrumb-item>Entry</el-breadcrumb-item>
                    </el-breadcrumb>
                </el-col>
            </el-row>
            <el-row>
                <el-col :span="24" class="pl10 pr10">
                    <el-card>
                        <el-form :model="model" :rules="rules" ref="form" @submit.native.prevent="submit(model)" label-width="auto" v-loading="isLoading">
                            <input v-model="model.id" type="hidden"/>
                            <el-form-item label="Date" prop="holiday_date">
                                <el-date-picker v-model="model.holiday_date" type="date" value-format="yyyy-MM-dd" placeholder="Pick a date"></el-date-picker>
                            </el-form-item>
                            <el-form-item label="Description">
                                <el-input v-model="model.description" placeholder="Description"></el-input>
                            </el-form-item>
                            <el-divider class="mt10 mb10"></el-divider>
                            <el-form-item class="mbn">
                                <el-col :span="6" :offset="18" class="text-right">
                                    <el-button-group>
                                        <el-button type="danger" @click.native="$router.push({ name: 'holiday' })">Cancel</el-button>
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
                holiday_date: '',
                description: '',
            },
            rules: {
                holiday_date: [
                    {
                        required: true,
                        message: 'Date is required!',
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
            this.$store.dispatch('doPost', { url: '/api/holidays/store', data: model })
                .then(res => {
                    vm.$notify.success({
                        title: 'Success',
                        message: 'Data saved!'
                    });
                    vm.$router.push({ name: 'holiday' });
                })
                .catch(err => {
                    this.$notify.error({
                        title: err.response.status +' - '+ err.response.statusText,
                        message: err.response.data
                    });
                })
        },
        get(id) {
            var vm = this;
            this.$store.dispatch('doGet', { url: '/api/holidays/'+ id })
                .then(res => {
                    if(res.data) {
                        vm.model = res.data;
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
    }
}
</script>