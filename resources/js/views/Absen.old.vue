<template>
    <el-main style="padding: 50px 50px">
        <el-row class="h100pc">
            <el-col :span="24" class="h100pc">
                <el-row>
                    <el-col :span="24">
                        <el-card shadow="always" class="bg-info">
                            <el-row>
                                <el-col :xs="24" :md="4" :lg="4" class="text-center">
                                    <img src="/images/basarnas.png" width="128">
                                </el-col>
                                <el-col :xs="24" :md="20" :lg="20">
                                    <h2>Absensi Online Basarnas</h2>
                                    <hr/>
                                    <h5></h5>
                                </el-col>
                            </el-row>
                        </el-card>
                    </el-col>
                </el-row>
                <el-row class="mt20">
                    <el-col :span="24">
                        <el-card shadow="always" v-if="isSubmitted">
                            <el-row>
                                <el-col :xs="24" :md="4" :lg="4">
                                    <el-card shadow="always" body-style="padding: 0px">
                                        <img :src="dataEmp.photo" style="width: 100%; display: block"/>
                                    </el-card>
                                </el-col>
                                <el-col :xs="24" :md="1" :lg="1">&nbsp;</el-col>
                                <el-col :xs="24" :md="19" :lg="19">
                                    <el-row class="mb10">
                                        <el-col :xs="24" :sm="7" :md="6" :lg="5" :span="5">
                                            <span><b>Waktu Absen</b></span>
                                        </el-col>
                                        <el-col :xs="1" :sm="1" :md="1" :lg="1" :span="1">:</el-col>
                                        <el-col :xs="20" :sm="16" :md="17" :lg="18" :span="18">
                                            <span class="fs24 text-danger"><b>{{ dataEmp.waktu }}</b></span>
                                        </el-col>
                                    </el-row>
                                    <el-row class="mb10">
                                        <el-col :xs="24" :sm="7" :md="6" :lg="5" :span="5">
                                            <span><b>NIP / NRP</b></span>
                                        </el-col>
                                        <el-col :xs="1" :sm="1" :md="1" :lg="1" :span="1">:</el-col>
                                        <el-col :xs="20" :sm="16" :md="17" :lg="18" :span="18">
                                            <span>{{ dataEmp.nipnew }}</span>
                                        </el-col>
                                    </el-row>
                                    <el-row class="mb10">
                                        <el-col :xs="24" :sm="7" :md="6" :lg="5" :span="5">
                                            <span><b>Nama</b></span>
                                        </el-col>
                                        <el-col :xs="1" :sm="1" :md="1" :lg="1" :span="1">:</el-col>
                                        <el-col :xs="20" :sm="16" :md="17" :lg="18" :span="18">
                                            <span>{{ (dataEmp.gelar_depan?dataEmp.gelar_depan+' ':'') + dataEmp.nama + (dataEmp.gelar_blkg?' '+dataEmp.gelar_blkg:'') }}</span>
                                        </el-col>
                                    </el-row>
                                    <el-row class="mb10">
                                        <el-col :xs="24" :sm="7" :md="6" :lg="5" :span="5">
                                            <span><b>Pangkat / Golongan</b></span>
                                        </el-col>
                                        <el-col :xs="1" :sm="1" :md="1" :lg="1" :span="1"><span>:</span></el-col>
                                        <el-col :xs="20" :sm="16" :md="17" :lg="18" :span="18">
                                            <span>{{ dataEmp.pangkat +' / '+ dataEmp.golongan }}</span>
                                        </el-col>
                                    </el-row>
                                    <el-row class="mb10">
                                        <el-col :xs="24" :sm="7" :md="6" :lg="5" :span="5">
                                            <span><b>Jabatan</b></span>
                                        </el-col>
                                        <el-col :xs="1" :sm="1" :md="1" :lg="1" :span="1">:</el-col>
                                        <el-col :xs="20" :sm="16" :md="17" :lg="18" :span="18">
                                            <span>{{ dataEmp.position }}</span>
                                        </el-col>
                                    </el-row>
                                    <el-row class="mb10">
                                        <el-col :xs="24" :sm="7" :md="6" :lg="5" :span="5">
                                            <span><b>Unit Kerja</b></span>
                                        </el-col>
                                        <el-col :xs="1" :sm="1" :md="1" :lg="1" :span="1">:</el-col>
                                        <el-col :xs="20" :sm="16" :md="17" :lg="18" :span="18">
                                            <span>{{ dataEmp.unit }}</span>
                                        </el-col>
                                    </el-row>
                                    <el-row class="mb10">
                                        <el-col :xs="24" :sm="7" :md="6" :lg="5" :span="5">
                                            <span><b>Absen masuk terakhir</b></span>
                                        </el-col>
                                        <el-col :xs="1" :sm="1" :md="1" :lg="1" :span="1">:</el-col>
                                        <el-col :xs="20" :sm="16" :md="17" :lg="18" :span="18">
                                            <span v-if="dataEmp.absenmasukakhir!=''">{{ dataEmp.absenmasukakhir }}</span>
                                            <span v-else>-</span>
                                        </el-col>
                                    </el-row>
                                    <el-row class="mb10">
                                        <el-col :xs="24" :sm="7" :md="6" :lg="5" :span="5">
                                            <span><b>Absen pulang terakhir</b></span>
                                        </el-col>
                                        <el-col :xs="1" :sm="1" :md="1" :lg="1" :span="1">:</el-col>
                                        <el-col :xs="20" :sm="16" :md="17" :lg="18" :span="18">
                                            <span v-if="dataEmp.absenkeluarakhir!=''">{{ dataEmp.absenkeluarakhir }}</span>
                                            <span v-else>-</span>
                                        </el-col>
                                    </el-row>
                                    <el-row class="mb5">
                                        <el-col :span="24" class="text-center">
                                            <span class="fs24 text-danger"><b>Absensi {{ dataEmp.pesanabsen }} Kerja Berhasil</b></span>
                                        </el-col>
                                    </el-row>
                                    <el-row>
                                        <el-col :xs="12" :md="19" :lg="19">&nbsp;</el-col>
                                        <el-col :xs="12" :md="4" :lg="4" class="text-right">
                                            <el-button type="danger" icon="el-icon-arrow-left" @click="$router.go(0)">Logout</el-button>
                                        </el-col>
                                    </el-row>
                                </el-col>
                            </el-row>
                        </el-card>
                        <el-card shadow="always" v-else>
                            <el-row>
                                <el-col :xs="24" :md="15" :lg="15">
                                    <h3 style="font-size: 36px">{{ datetime }}</h3>
                                    <!-- <p class="mn">Berdasarkan Pengumuman Nomor: PENG-14/KP.04.03/IV/BSN-2020</p> -->
                                    <!-- <p class="mtn">Penetapan Jam Kerja Pada Bulan Ramadhan 1441H</p> -->
                                    <el-table :data="tableDataInfo" :span-method="tableDataInfoSpan" border>
                                        <el-table-column prop="ket" label="" width="120"></el-table-column>
                                        <el-table-column prop="pagi" label="Absen Masuk" width="190"></el-table-column>
                                        <el-table-column prop="sore" label="Absen Pulang"></el-table-column>
                                    </el-table>
                                    <p class="text-basic lighter">Ketentuan ini berlaku periode penyesuaian sistem kerja ASN dalam <b>upaya pencegahan Covid 19</b> di lingkungan <b>Badan Nasional Pencarian dan Pertolongan</b></p>
                                </el-col>
                                <el-col :span="1" :xs="24">&nbsp;</el-col>
                                <el-col :xs="24" :md="8" :lg="8">
                                    <template v-if="msgOpen == 'officehour'">
                                        <h6>&nbsp;</h6>
                                        <el-form :model="model" :rules="rules" ref="absenForm" @submit.native.prevent="doAbsen(model)">
                                            <el-form-item prop="nip">
                                                <el-input v-model="model.nip" placeholder="NIP / NRP" @focus="inputFocused"></el-input>
                                            </el-form-item>
                                            <el-form-item>
                                                <el-radio-group v-model="model.wfw">
                                                    <el-radio-button label="WFH" key="WFH"></el-radio-button>
                                                    <el-radio-button label="WFO" key="WFO"></el-radio-button>
                                                </el-radio-group>
                                            </el-form-item>
                                            <el-form-item>
                                                <el-row>
                                                    <el-col :span="15">
                                                        <el-input v-model="model.captcha" placeholder="Captcha"></el-input>
                                                    </el-col>
                                                    <el-col :span="1">&nbsp;</el-col>
                                                    <el-col :span="8" class="text-center text-danger"><span style="font-size: 18px; letter-spacing: 4px"><b>{{ captchaTxt }}</b></span></el-col>
                                                </el-row>
                                            </el-form-item>
                                            <el-form-item>
                                                <el-button type="primary" :loading="isLoading" native-type="submit" class="w100pc" block>Submit</el-button>
                                            </el-form-item>
                                        </el-form>
                                    </template>
                                    <template v-else>
                                        <h6>&nbsp;</h6>
                                        <el-card>
                                            <el-row>
                                                <el-col :span="24" class="text-center">
                                                    <i :class="((msgOpen == 'before5' || msgOpen == 'after22') ? 'el-icon-timer' : 'el-icon-s-home')" class="text-danger" style="font-size: 96px"></i>
                                                </el-col>
                                            </el-row>
                                            <el-row>
                                                <el-col :span="24" class="text-center">
                                                    <p class="text-basic lighter">{{ msgText }}</p>
                                                </el-col>
                                            </el-row>
                                        </el-card>
                                    </template>
                                </el-col>
                            </el-row>
                        </el-card>
                    </el-col>
                </el-row>
                <el-row class="mt20">
                    <el-col :span="24">
                        <el-card shadow="always" class="bg-info">
                            <el-row>
                                <el-col :span="24" class="text-center">
                                    <p class="fs12"><b>&copy; (2020) Badan Nasional Pencarian dan Pertolongan.</b> v.0.0.1</p>
                                </el-col>
                            </el-row>
                        </el-card>
                    </el-col>
                </el-row>
            </el-col>
        </el-row>
    </el-main>
</template>

<style scoped>

</style>

<script>
import axios from 'axios';

export default {
    data() {
        return {
            datetime: '',
            now: '',
            model: {
                nip: '',
                wfw: 'WFH',
                captcha: '',
            },
            rules: {
                nip: [
                    {
                        required: true,
                        message: 'NIP / NRP is required!',
                        trigger: 'blur'
                    }
                ],
            },
            captchaTxt: '',
            tableDataInfo: [{
                ket: 'Tepat waktu',
                pagi: '06.00 s/d 07.30',
                sore: '16.00 s/d 19.00 (Senin s/d Kamis)'
            },{
                ket: '16.30 s/d 19.00 (Jumat)',
            },{
                ket: 'Dispensasi',
                pagi: '07.30 s/d 08.00',
                sore: 'Mengganti jumlah menit terlambat (maksimal 30\')'
            },{
                ket: 'Terlambat',
                pagi: '> 08.00',
                sore: ' '
            }],
            // tableDataInfo: [{
            //     ket: 'Tepat waktu',
            //     pagi: '06.00 s/d 08.00',
            //     sore: '15.00 s/d 18.00 (Senin s/d Kamis)'
            // },{
            //     ket: '15.30 s/d 18.00 (Jumat)',
            // },{
            //     ket: 'Terlambat',
            //     pagi: '> 08.00',
            //     sore: ' '
            // }],
            dataEmp: [],
            isLoading: false,
            isSubmitted: false,
            days: [ 'Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis' , 'Jum\'at', 'Sabtu' ],
            months: [ 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember' ],
            msgOpen: '',
            msgText: '',
        }
    },
    mounted() {
        this.getDateTime();
        setInterval(this.getDateTime, 1000);

        let alphanum = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        this.captchaTxt = alphanum.substr(Math.floor(Math.random() * alphanum.length), 1) +
            alphanum.substr(Math.floor(Math.random() * alphanum.length), 1) +
            alphanum.substr(Math.floor(Math.random() * alphanum.length), 1) +
            alphanum.substr(Math.floor(Math.random() * alphanum.length), 1);
    },
    methods: {
        async getDateTime() {
            this.now = new Date();

            let vm = this;
            if(this.datetime == '')
                this.$store.dispatch('doGet', { url: '/api/absen/time' })
                    .then(res => {
                        let arrDateTime = res.data.datetime.split(',').map(d => { return parseInt(d) });
                        vm.now = new Date(arrDateTime[0],arrDateTime[1] -1,arrDateTime[2],arrDateTime[3],arrDateTime[4],arrDateTime[5]);
                        vm.msgOpen = res.data.msgOpen;
                        
                        switch (vm.msgOpen) {
                            case 'before5': 
                            case 'after22': vm.msgText = 'Form Absen akan dibuka mulai pukul 05.00 s/d 19.00.'; break;
                            // case 'after22': vm.msgText = 'Form Absen akan dibuka mulai pukul 06.00 s/d 18.00.'; break;
                            case 'weekend': vm.msgText = 'Form Absen tidak dibuka pada akhir pekan.'; break;
                            case 'holiday': vm.msgText = 'Form Absen tidak dibuka pada hari libur.'; break;
                        }
                    })
                    .catch(err => {
                        console.log(err);
                    });
            
            this.now.setSeconds(this.now.getSeconds() +1);
            this.datetime = this.days[this.now.getDay()] +', '+ this.now.getDate() +' '+ this.months[this.now.getMonth()] +' '+ this.now.getFullYear() +' '+ (this.now.getHours()<10?'0':'') + this.now.getHours() +':'+ (this.now.getMinutes()<10?'0':'') + this.now.getMinutes() +':'+ (this.now.getSeconds()<10?'0':'') + this.now.getSeconds() + ' WIB';
        },
        async doAbsen(model) {
            let isValid = await this.$refs.absenForm.validate();
            if(!isValid)
                return;
            
            if(model.captcha != this.captchaTxt) {
                this.$alert('Invalid captcha, please try again!', 'Error', {
                    type: 'danger',
                    confirmButtonText: 'OK',
                });
                return;
            }

            this.isLoading = true;
            this.isSubmitted = false;

            let vm = this;
            this.$store.dispatch('doPost', { url: '/api/absen/store', data: model })
                .then(res => {
                    vm.$notify.success({
                        title: 'Success',
                        message: 'Data absensi tersimpan!'
                    });
                    vm.isLoading = false;
                    vm.isSubmitted = true;

                    vm.dataEmp = res.data;

                    // vm.now = new Date(vm.dataEmp.waktu);
                    let arrWaktu = vm.dataEmp.waktu.split(',').map(d => { return parseInt(d) });
                    vm.now = new Date(arrWaktu[0],arrWaktu[1] -1,arrWaktu[2],arrWaktu[3],arrWaktu[4],arrWaktu[5]);
                    vm.dataEmp.waktu = this.days[this.now.getDay()] +', '+ this.now.getDate() +' '+ this.months[this.now.getMonth()] +' '+ this.now.getFullYear() +' '+ (this.now.getHours()<10?'0':'') + this.now.getHours() +':'+ (this.now.getMinutes()<10?'0':'') + this.now.getMinutes() +':'+ (this.now.getSeconds()<10?'0':'') + this.now.getSeconds() + ' WIB';

                    if(vm.dataEmp.absenmasukakhir) {
                        // vm.now = new Date(vm.dataEmp.absenakhir);
                        arrWaktu = vm.dataEmp.absenmasukakhir.split(',').map(d => { return parseInt(d) });
                        vm.now = new Date(arrWaktu[0],arrWaktu[1] -1,arrWaktu[2],arrWaktu[3],arrWaktu[4],arrWaktu[5]);
                        vm.dataEmp.absenmasukakhir = this.days[this.now.getDay()] +', '+ this.now.getDate() +' '+ this.months[this.now.getMonth()] +' '+ this.now.getFullYear() +' '+ (this.now.getHours()<10?'0':'') + this.now.getHours() +':'+ (this.now.getMinutes()<10?'0':'') + this.now.getMinutes() +':'+ (this.now.getSeconds()<10?'0':'') + this.now.getSeconds() + ' WIB';
                    }
                    if(vm.dataEmp.absenkeluarakhir) {
                        // vm.now = new Date(vm.dataEmp.absenakhir);
                        arrWaktu = vm.dataEmp.absenkeluarakhir.split(',').map(d => { return parseInt(d) });
                        vm.now = new Date(arrWaktu[0],arrWaktu[1] -1,arrWaktu[2],arrWaktu[3],arrWaktu[4],arrWaktu[5]);
                        vm.dataEmp.absenkeluarakhir = this.days[this.now.getDay()] +', '+ this.now.getDate() +' '+ this.months[this.now.getMonth()] +' '+ this.now.getFullYear() +' '+ (this.now.getHours()<10?'0':'') + this.now.getHours() +':'+ (this.now.getMinutes()<10?'0':'') + this.now.getMinutes() +':'+ (this.now.getSeconds()<10?'0':'') + this.now.getSeconds() + ' WIB';
                    }
                })
                .catch(err => {
                    let res = err.response;
                    this.$notify.error({
                        title: 'Error',
                        message: res.data
                    });
                    vm.isLoading = false;
                })
        },
        inputFocused(ev) {
            let ele = ev.target.parentNode.parentNode;

            if(ele.classList.contains('el-col')) {
                ele.parentNode.parentNode.children[1].style = 'display: none';
            } else {
                if(ele.children.length >1)
                    ele.children[1].style = 'display: none';
            }

        },
        tableDataInfoSpan({row, column, rowIndex, columnIndex}) {
            if(columnIndex === 0) {
                if(rowIndex === 0) 
                    return {
                        rowspan: 2,
                        colspan: 1
                    };
                else 
                    return {
                        rowspan: 1,
                        colspan: 1
                    };
            } else if(columnIndex === 1) {
                if(rowIndex === 0) 
                    return {
                        rowspan: 2,
                        colspan: 1
                    };
                else 
                    return {
                        rowspan: 1,
                        colspan: 1
                    };
            } else {
                return {
                    rowspan: 1,
                    colspan: 1
                };
            }
        }
    }
}
</script>