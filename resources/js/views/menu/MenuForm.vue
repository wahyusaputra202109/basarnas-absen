<template>
    <el-row class="h100pc">
        <el-col :span="24" class="bg-info lighter h100pc">
            <el-row class="mb10 pt20 pb20 b-t">
                <el-col :span="24" class="pl20 pr20">
                    <el-breadcrumb separator-class="el-icon-arrow-right">
                        <el-breadcrumb-item>Menu</el-breadcrumb-item>
                        <el-breadcrumb-item>Entry</el-breadcrumb-item>
                    </el-breadcrumb>
                </el-col>
            </el-row>
            <el-row>
                <el-col :span="24" class="pl10 pr10">
                    <el-card>
                        <el-form :model="model" :rules="rules" ref="form" @submit.native.prevent="submit(model)" label-width="auto" v-loading="isLoading">
                            <input v-model="model.id" type="hidden"/>
                            <input v-model="model.parent" type="hidden"/>
                            <el-form-item label="Name" prop="name">
                                <el-input v-model="model.name"></el-input>
                            </el-form-item>
                            <el-form-item label="Slug" prop="slug">
                                <el-col :span="12">
                                    <el-input v-model="model.slug" :disabled="inputDisabled"></el-input>
                                </el-col>
                                <el-col :span="1">&nbsp;</el-col>
                                <el-col :span="10">
                                    <p class="text-info fs12" style="line-height: 1">Note: always starts with /</p>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Icon">
                                <el-col :span="12">
                                    <el-autocomplete v-model="model.icon" :fetch-suggestions="qryIcons" :trigger-on-focus="false" style="width: 100%">
                                        <template slot-scope="{ item }">
                                            <i v-bind:class="item.value"></i>&nbsp;&nbsp;{{ item.value }}
                                        </template>
                                    </el-autocomplete>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="Order No.">
                                <el-input-number v-model="model.order_no" :min="1"></el-input-number>
                            </el-form-item>
                            <el-form-item label="Active ?">
                                <el-switch v-model="model.is_active"></el-switch>
                            </el-form-item>
                            <el-form-item label="Available permissions">
                                <el-select v-model="model.permissions" multiple filterable allow-create default-first-option placeholder="Pick permissions" class="w100pc">
                                    <el-option v-for="item in model.arrPerms" :key="item.name" :label="item.name" :value="item.name"></el-option>
                                </el-select>
                            </el-form-item>
                            <el-divider class="mt10 mb10"></el-divider>
                            <el-form-item class="mbn">
                                <el-col :span="6" :offset="18" class="text-right">
                                    <el-button-group>
                                        <el-button type="danger" @click.native="$router.push({ name: 'menu' })">Cancel</el-button>
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
.el-tag {
    margin-right: 2px;
    margin-left: 2px;
}
</style>

<script>
export default {
    data() {
        return {
            model: {
                id: 0,
                parent: 0,
                name: '',
                slug: '',
                icon: '',
                order_no: 1,
                is_active: true,
                permissions: [],
                arrPerms: []
            },
            inputDisabled: false,
            rules: {
                name: [
                    {
                        required: true,
                        message: 'Name is required!',
                        trigger: 'blur'
                    }
                ],
                slug: [
                    {
                        required: true,
                        message: 'Slug is required!',
                        trigger: 'blur'
                    }
                ]
            },
            icons: [],
        }
    },
    computed: {
        isLoading() {
            return this.$store.getters.isLoading;
        },
    },
    mounted() {
        // init icons
        this.icons = this.loadIcons();
        // init form data
        var parent = this.$route.params.parent;
        var id = this.$route.params.id || 0;
        this.get(parent, id);
    },
    methods: {
        async submit(model) {
            this.loading = true;
            let isValid = await this.$refs.form.validate();
            if(!isValid) 
                return;

            delete model.arrPerms;

            // submit form
            var vm = this;
            this.$store.dispatch('doPost', { url: '/api/menus/store', data: model })
                .then(res => {
                    vm.$notify.success({
                        title: 'Success',
                        message: 'Data saved!'
                    });
                    vm.$router.push({ name: 'menu' });
                })
                .catch(err => {
                    this.$notify.error({
                        title: err.response.status +' - '+ err.response.statusText,
                        message: err.response.data,
                        duration: 2500
                    });
                })
        },
        get(parent, id) {
            var vm = this;
            this.$store.dispatch('doGet', { url: '/api/menus/'+ parent +'/'+ id })
                .then(res => {
                    if(res.data) {
                        vm.model = res.data;
                        if(res.data.id)
                            this.inputDisabled = true;
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
        qryIcons(qry, cb) {
            var icons = this.icons;
            var results = qry ? icons.filter(this.createFilter(qry)) : icons;
            cb(results);
        },
        createFilter(qry) {
            return (icon) => {
                return (icon.value.toLowerCase().indexOf(qry.toLowerCase()) >= 0);
            };
        },
        loadIcons() {
            return [
                { "value": "el-icon-platform-eleme" },
                { "value": "el-icon-eleme" },
                { "value": "el-icon-delete-solid" },
                { "value": "el-icon-delete" },
                { "value": "el-icon-s-tools" },
                { "value": "el-icon-setting" },
                { "value": "el-icon-user-solid" },
                { "value": "el-icon-user" },
                { "value": "el-icon-phone" },
                { "value": "el-icon-phone-outline" },
                { "value": "el-icon-more" },
                { "value": "el-icon-more-outline" },
                { "value": "el-icon-star-on" },
                { "value": "el-icon-star-off" },
                { "value": "el-icon-s-goods" },
                { "value": "el-icon-goods" },
                { "value": "el-icon-warning" },
                { "value": "el-icon-warning-outline" },
                { "value": "el-icon-question" },
                { "value": "el-icon-info" },
                { "value": "el-icon-remove" },
                { "value": "el-icon-circle-plus" },
                { "value": "el-icon-success" },
                { "value": "el-icon-error" },
                { "value": "el-icon-zoom-in" },
                { "value": "el-icon-zoom-out" },
                { "value": "el-icon-remove-outline" },
                { "value": "el-icon-circle-plus-outline" },
                { "value": "el-icon-circle-check" },
                { "value": "el-icon-circle-close" },
                { "value": "el-icon-s-help" },
                { "value": "el-icon-help" },
                { "value": "el-icon-minus" },
                { "value": "el-icon-plus" },
                { "value": "el-icon-check" },
                { "value": "el-icon-close" },
                { "value": "el-icon-picture" },
                { "value": "el-icon-picture-outline" },
                { "value": "el-icon-picture-outline-round" },
                { "value": "el-icon-upload" },
                { "value": "el-icon-upload2" },
                { "value": "el-icon-download" },
                { "value": "el-icon-camera-solid" },
                { "value": "el-icon-camera" },
                { "value": "el-icon-video-camera-solid" },
                { "value": "el-icon-video-camera" },
                { "value": "el-icon-message-solid" },
                { "value": "el-icon-bell" },
                { "value": "el-icon-s-cooperation" },
                { "value": "el-icon-s-order" },
                { "value": "el-icon-s-platform" },
                { "value": "el-icon-s-fold" },
                { "value": "el-icon-s-unfold" },
                { "value": "el-icon-s-operation" },
                { "value": "el-icon-s-promotion" },
                { "value": "el-icon-s-home" },
                { "value": "el-icon-s-release" },
                { "value": "el-icon-s-ticket" },
                { "value": "el-icon-s-management" },
                { "value": "el-icon-s-open" },
                { "value": "el-icon-s-shop" },
                { "value": "el-icon-s-marketing" },
                { "value": "el-icon-s-flag" },
                { "value": "el-icon-s-comment" },
                { "value": "el-icon-s-finance" },
                { "value": "el-icon-s-claim" },
                { "value": "el-icon-s-custom" },
                { "value": "el-icon-s-opportunity" },
                { "value": "el-icon-s-data" },
                { "value": "el-icon-s-check" },
                { "value": "el-icon-s-grid" },
                { "value": "el-icon-menu" },
                { "value": "el-icon-share" },
                { "value": "el-icon-d-caret" },
                { "value": "el-icon-caret-left" },
                { "value": "el-icon-caret-right" },
                { "value": "el-icon-caret-bottom" },
                { "value": "el-icon-caret-top" },
                { "value": "el-icon-bottom-left" },
                { "value": "el-icon-bottom-right" },
                { "value": "el-icon-back" },
                { "value": "el-icon-right" },
                { "value": "el-icon-bottom" },
                { "value": "el-icon-top" },
                { "value": "el-icon-top-left" },
                { "value": "el-icon-top-right" },
                { "value": "el-icon-arrow-left" },
                { "value": "el-icon-arrow-right" },
                { "value": "el-icon-arrow-down" },
                { "value": "el-icon-arrow-up" },
                { "value": "el-icon-d-arrow-left" },
                { "value": "el-icon-d-arrow-right" },
                { "value": "el-icon-video-pause" },
                { "value": "el-icon-video-play" },
                { "value": "el-icon-refresh" },
                { "value": "el-icon-refresh-right" },
                { "value": "el-icon-refresh-left" },
                { "value": "el-icon-finished" },
                { "value": "el-icon-sort" },
                { "value": "el-icon-sort-up" },
                { "value": "el-icon-sort-down" },
                { "value": "el-icon-rank" },
                { "value": "el-icon-loading" },
                { "value": "el-icon-view" },
                { "value": "el-icon-c-scale-to-original" },
                { "value": "el-icon-date" },
                { "value": "el-icon-edit" },
                { "value": "el-icon-edit-outline" },
                { "value": "el-icon-folder" },
                { "value": "el-icon-folder-opened" },
                { "value": "el-icon-folder-add" },
                { "value": "el-icon-folder-remove" },
                { "value": "el-icon-folder-delete" },
                { "value": "el-icon-folder-checked" },
                { "value": "el-icon-tickets" },
                { "value": "el-icon-document-remove" },
                { "value": "el-icon-document-delete" },
                { "value": "el-icon-document-copy" },
                { "value": "el-icon-document-checked" },
                { "value": "el-icon-document" },
                { "value": "el-icon-document-add" },
                { "value": "el-icon-printer" },
                { "value": "el-icon-paperclip" },
                { "value": "el-icon-takeaway-box" },
                { "value": "el-icon-search" },
                { "value": "el-icon-monitor" },
                { "value": "el-icon-attract" },
                { "value": "el-icon-mobile" },
                { "value": "el-icon-scissors" },
                { "value": "el-icon-umbrella" },
                { "value": "el-icon-headset" },
                { "value": "el-icon-brush" },
                { "value": "el-icon-mouse" },
                { "value": "el-icon-coordinate" },
                { "value": "el-icon-magic-stick" },
                { "value": "el-icon-reading" },
                { "value": "el-icon-data-line" },
                { "value": "el-icon-data-board" },
                { "value": "el-icon-pie-chart" },
                { "value": "el-icon-data-analysis" },
                { "value": "el-icon-collection-tag" },
                { "value": "el-icon-film" },
                { "value": "el-icon-suitcase" },
                { "value": "el-icon-suitcase-1" },
                { "value": "el-icon-receiving" },
                { "value": "el-icon-collection" },
                { "value": "el-icon-files" },
                { "value": "el-icon-notebook-1" },
                { "value": "el-icon-notebook-2" },
                { "value": "el-icon-toilet-paper" },
                { "value": "el-icon-office-building" },
                { "value": "el-icon-school" },
                { "value": "el-icon-table-lamp" },
                { "value": "el-icon-house" },
                { "value": "el-icon-no-smoking" },
                { "value": "el-icon-smoking" },
                { "value": "el-icon-shopping-cart-full" },
                { "value": "el-icon-shopping-cart-1" },
                { "value": "el-icon-shopping-cart-2" },
                { "value": "el-icon-shopping-bag-1" },
                { "value": "el-icon-shopping-bag-2" },
                { "value": "el-icon-sold-out" },
                { "value": "el-icon-sell" },
                { "value": "el-icon-present" },
                { "value": "el-icon-box" },
                { "value": "el-icon-bank-card" },
                { "value": "el-icon-money" },
                { "value": "el-icon-coin" },
                { "value": "el-icon-wallet" },
                { "value": "el-icon-discount" },
                { "value": "el-icon-price-tag" },
                { "value": "el-icon-news" },
                { "value": "el-icon-guide" },
                { "value": "el-icon-male" },
                { "value": "el-icon-female" },
                { "value": "el-icon-thumb" },
                { "value": "el-icon-cpu" },
                { "value": "el-icon-link" },
                { "value": "el-icon-connection" },
                { "value": "el-icon-open" },
                { "value": "el-icon-turn-off" },
                { "value": "el-icon-set-up" },
                { "value": "el-icon-chat-round" },
                { "value": "el-icon-chat-line-round" },
                { "value": "el-icon-chat-square" },
                { "value": "el-icon-chat-dot-round" },
                { "value": "el-icon-chat-dot-square" },
                { "value": "el-icon-chat-line-square" },
                { "value": "el-icon-message" },
                { "value": "el-icon-postcard" },
                { "value": "el-icon-position" },
                { "value": "el-icon-turn-off-microphone" },
                { "value": "el-icon-microphone" },
                { "value": "el-icon-close-notification" },
                { "value": "el-icon-bangzhu" },
                { "value": "el-icon-time" },
                { "value": "el-icon-odometer" },
                { "value": "el-icon-crop" },
                { "value": "el-icon-aim" },
                { "value": "el-icon-switch-button" },
                { "value": "el-icon-full-screen" },
                { "value": "el-icon-copy-document" },
                { "value": "el-icon-mic" },
                { "value": "el-icon-stopwatch" },
                { "value": "el-icon-medal-1" },
                { "value": "el-icon-medal" },
                { "value": "el-icon-trophy" },
                { "value": "el-icon-trophy-1" },
                { "value": "el-icon-first-aid-kit" },
                { "value": "el-icon-discover" },
                { "value": "el-icon-place" },
                { "value": "el-icon-location" },
                { "value": "el-icon-location-outline" },
                { "value": "el-icon-location-information" },
                { "value": "el-icon-add-location" },
                { "value": "el-icon-delete-location" },
                { "value": "el-icon-map-location" },
                { "value": "el-icon-alarm-clock" },
                { "value": "el-icon-timer" },
                { "value": "el-icon-watch-1" },
                { "value": "el-icon-watch" },
                { "value": "el-icon-lock" },
                { "value": "el-icon-unlock" },
                { "value": "el-icon-key" },
                { "value": "el-icon-service" },
                { "value": "el-icon-mobile-phone" },
                { "value": "el-icon-bicycle" },
                { "value": "el-icon-truck" },
                { "value": "el-icon-ship" },
                { "value": "el-icon-basketball" },
                { "value": "el-icon-football" },
                { "value": "el-icon-soccer" },
                { "value": "el-icon-baseball" },
                { "value": "el-icon-wind-power" },
                { "value": "el-icon-light-rain" },
                { "value": "el-icon-lightning" },
                { "value": "el-icon-heavy-rain" },
                { "value": "el-icon-sunrise" },
                { "value": "el-icon-sunrise-1" },
                { "value": "el-icon-sunset" },
                { "value": "el-icon-sunny" },
                { "value": "el-icon-cloudy" },
                { "value": "el-icon-partly-cloudy" },
                { "value": "el-icon-cloudy-and-sunny" },
                { "value": "el-icon-moon" },
                { "value": "el-icon-moon-night" },
                { "value": "el-icon-dish" },
                { "value": "el-icon-dish-1" },
                { "value": "el-icon-food" },
                { "value": "el-icon-chicken" },
                { "value": "el-icon-fork-spoon" },
                { "value": "el-icon-knife-fork" },
                { "value": "el-icon-burger" },
                { "value": "el-icon-tableware" },
                { "value": "el-icon-sugar" },
                { "value": "el-icon-dessert" },
                { "value": "el-icon-ice-cream" },
                { "value": "el-icon-hot-water" },
                { "value": "el-icon-water-cup" },
                { "value": "el-icon-coffee-cup" },
                { "value": "el-icon-cold-drink" },
                { "value": "el-icon-goblet" },
                { "value": "el-icon-goblet-full" },
                { "value": "el-icon-goblet-square" },
                { "value": "el-icon-goblet-square-full" },
                { "value": "el-icon-refrigerator" },
                { "value": "el-icon-grape" },
                { "value": "el-icon-watermelon" },
                { "value": "el-icon-cherry" },
                { "value": "el-icon-apple" },
                { "value": "el-icon-pear" },
                { "value": "el-icon-orange" },
                { "value": "el-icon-coffee" },
                { "value": "el-icon-ice-tea" },
                { "value": "el-icon-ice-drink" },
                { "value": "el-icon-milk-tea" },
                { "value": "el-icon-potato-strips" },
                { "value": "el-icon-lollipop" },
                { "value": "el-icon-ice-cream-square" },
                { "value": "el-icon-ice-cream-round" },
            ];
        }
    }
}
</script>