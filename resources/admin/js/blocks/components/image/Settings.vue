<template>
    <div class="block-settings">
        <el-tabs v-model="activeTab">
            <el-tab-pane label="General" name="general">
                <div class="form-group">
                    <label>Block Title</label>
                    <input type="text" class="form-control" v-model="settings.blockTitle">
                </div>

                <div class="form-group">
                    <label>Size</label>
                    <select class="form-control" v-model="settings.imageSize">
                        <option v-for="(option, index) in imageSizeOptions" :key="index" :value="option.value">
                            {{ option.text }}
                        </option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Responsive</label>
                    <select class="form-control" v-model="settings.imageResponsive">
                        <option :value=true>Yes</option>
                        <option :value=false>No</option>
                    </select>
                </div>

                <div v-if="!settings.imageResponsive" class="form-group">
                    <label>Width</label>
                    <px :maxAllowed=1 :px.sync="settings.width"></px>
                </div>

                <div class="form-group">
                    <label>Position</label>
                    <select class="form-control" v-model="settings.imagePosition">
                        <option value="flex-start">Left</option>
                        <option value="center">Center</option>
                        <option value="flex-end">Right</option>
                    </select>
                </div>

                <div v-if="!settings.paddingResponsive" class="form-group">
                    <label>Padding</label> <i @click="settings.paddingResponsive = !settings.paddingResponsive" class="lo-icon lo-icon-desktop" title="Responsive"></i>
                    <px :px.sync="settings.padding"></px>
                </div>
                <div v-else class="form-group">
                    <label>Padding</label> <i @click="settings.paddingResponsive = !settings.paddingResponsive" class="lo-icon lo-icon-desktop" title="Responsive"></i>
                    <px-responsive
                        :extraLarge.sync="settings.padding"
                        :large.sync="settings.paddingLarge"
                        :medium.sync="settings.paddingMedium"
                        :small.sync="settings.paddingSmall"
                        :extraSmall.sync="settings.paddingExtraSmall"
                    >
                    </px-responsive>
                </div>

                <div v-if="!settings.marginResponsive" class="form-group">
                    <label>Margin</label> <i @click="settings.marginResponsive = !settings.marginResponsive" class="lo-icon lo-icon-desktop" title="Responsive"></i>
                    <px :px.sync="settings.margin"></px>
                </div>
                <div v-else class="form-group">
                    <label>Margin</label> <i @click="settings.marginResponsive = !settings.marginResponsive" class="lo-icon lo-icon-desktop" title="Responsive"></i>
                    <px-responsive
                        :extraLarge.sync="settings.margin"
                        :large.sync="settings.marginLarge"
                        :medium.sync="settings.marginMedium"
                        :small.sync="settings.marginSmall"
                        :extraSmall.sync="settings.marginExtraSmall"
                    >
                    </px-responsive>
                </div>

                <div class="form-group">
                    <label>Image Border</label>
                    <border v-model="settings.imageBorder"></border>
                </div>

                <div class="form-group">
                    <label>Image Border Radius</label>
                    <px :maxAllowed="1" :px.sync="settings.imageBorderRadius"></px>
                </div>

                <div class="form-group">
                    <label>Background Color</label>
                    <color-picker v-model="settings.backgroundColor"></color-picker>
                </div>

                <div class="form-group">
                    <label>Custom Class</label>
                    <input type="text" class="form-control" v-model="settings.customClass">
                </div>
            </el-tab-pane>

            <el-tab-pane label="Actions" name="actions">
                <div class="form-group">
                    <label>On Click</label>
                    <select class="form-control" v-model="settings.onClick">
                        <option value='nothing'>Do nothing</option>
                        <option value='lightbox'>Lightbox</option>
                        <option value='open-link'>Link</option>
                    </select>
                </div>

                <div v-if="settings.onClick=='open-link'" class="form-group">
                    <label>Target</label>
                    <select class="form-control" v-model="settings.target">
                        <option value='_blank'>New Tab</option>
                        <option value='_self'>Same Tab</option>
                    </select>
                </div>

                <div v-if="settings.onClick=='open-link'" class="form-group">
                    <label>Link</label>
                    <input type="text" class="form-control" v-model="content.link">
                </div>
            </el-tab-pane>

            <el-tab-pane label="Css" name="css">
                <div class="form-group">
                    <prism-editor v-if="!$root.isFirefox" v-model="settings.css" :lineNumbers=true language="css"></prism-editor>
                    <textarea v-else v-model="settings.css" class="form-control" rows="3"></textarea>
                </div>
            </el-tab-pane>
        </el-tabs>
    </div>
</template>

<script>
    import SettingsMixin from '../../mixins/SettingsMixin'
    import { mapGetters, mapActions } from 'vuex'

    export default {
        mixins: [SettingsMixin],
        data() {
            return {
                imageSizeOptions: [
                    { text: 'Original', value: 'original' },
                    { text: 'Large', value: 'large' },
                    { text: 'Medium', value: 'medium' },
                    { text: 'Thumbnail', value: 'thumb' },
                ]
            }
        },
        customSettings: {
            blockTitle: {type: String, default: 'Image'},
            customClass: {type: String, default: ''},

            imageSize: {type: String, default: 'original'},
            imageResponsive: {type: Boolean, default: true},
            imagePosition: {type: String, default: 'center'},

            backgroundColor: {type: String, default: ''},

            imageBorder: {type: String, default: '0px solid transparent'},
            imageBorderRadius: {type: String, default: '0px'},

            widthResponsive: {type: Boolean, default: false},
            width: {type: String, default: 'auto'},
            widthLarge: {type: String, default: ''},
            widthMedium: {type: String, default: ''},
            widthSmall: {type: String, default: ''},
            widthExtraSmall: {type: String, default: ''},

            paddingResponsive: {type: Boolean, default: false},
            padding: {type: String, default: '0px'},
            paddingLarge: {type: String, default: ''},
            paddingMedium: {type: String, default: ''},
            paddingSmall: {type: String, default: ''},
            paddingExtraSmall: {type: String, default: ''},

            marginResponsive: {type: Boolean, default: false},
            margin: {type: String, default: '0px 0px 15px 0px'},
            marginLarge: {type: String, default: ''},
            marginMedium: {type: String, default: ''},
            marginSmall: {type: String, default: ''},
            marginExtraSmall: {type: String, default: ''},

            onClick: {type: String, default: 'lightbox'},
            target: {type: String, default: '_blank'},

            css: {type: String, default: ''},
        }
    }
</script>
