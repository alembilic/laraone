<template>
    <div :style="columnStyles">
        <draggable
            v-model="subItems"
            :style="itemsWrapperStyles"
            group="column"
            handle=".lo-icon-move"
            chosenClass="dragging1">
            <template v-for="block in subItems">
                <component-wrapper
                    :type="block.type"
                    :uniqueId="block.uniqueId"
                    :settings="block.settings"
                    :show-headers="showBlockHeaders"
                    :show-labels="showBlockLabels"
                    v-on:remove="removeBlock(block.uniqueId)"
                    :key="block.uniqueId"
                    :storePath="storePath"
                    :ancestorSettings="propagatedSettings"/>
            </template>
        </draggable>
    </div>
</template>

<script>
    import GeneralMixin from '../../../mixins/GeneralMixin'
    import ComponentWrapper from '../../../ComponentWrapper'
    import { mapGetters, mapActions } from 'vuex'
    import draggable from 'vuedraggable'
    import { getComponentByName, processSettingsConfig } from '~/utils/helpers.js'

    export default {
        mixins: [GeneralMixin],
        components: {
            draggable,
            ComponentWrapper
        },
        data() {
            return {
                showBlockHeaders: this.showHeaders,
                showBlockLabels: this.showLabels,
                showPreview: true,
                cssSettingsOpen: true,
            }
        },
        computed: {
            content() {
                return this.$store.getters[`${this.storePath}/itemContent`](this.uniqueId)
            },
            modalTitle() {
                return this.settings.blockTitle + ' Settings'
            },
            settings: {
                get() {
                    return this.$store.getters[`${this.storePath}/itemSettings`](this.uniqueId)
                }
            },
            subItems: {
                get() {
                    return this.$store.getters[`${this.storePath}/items`](this.uniqueId)
                },
                set(object) {
                    this.updateItemsList({list: _.map(object, 'uniqueId'), id: this.uniqueId})
                }
            },
            itemsWrapperStyles() {
                let styles = ""
                styles = styles + 'min-height: 120px;'
                styles = styles + 'display: '+this.settings.display+';'
                //
                if(this.settings.display == 'flex') {
                    styles = styles + 'flex-direction: '+this.settings.flexDirection+';'
                    styles = styles + 'align-items: '+this.settings.alignItems+';'
                    styles = styles + 'justify-content: '+this.settings.justifyContent+';'
                }

                return styles
            },
            columnStyles() {
                let styles = ""
                if(this.containerPreview) {
                    if(this.settings.backgroundImage) {
                        styles = styles + 'background-image: url('+this.settings.backgroundImage+');'
                        styles = styles + 'background-attachment: '+this.settings.backgroundStyle+';'
                        styles = styles + 'background-position: '+this.settings.backgroundPosition+';'
                        styles = styles + 'background-repeat: '+this.settings.backgroundRepeat+';'
                        styles = styles + 'box-shadow: inset 0 0 0 2000px '+this.settings.backgroundColor+';'
                        switch (this.settings.backgroundSize) {
                            case 'manual':
                                styles = styles + 'background-size: '+this.settings.backgroundSizeManual+';'
                            break;
                            default:
                                styles = styles + 'background-size: '+this.settings.backgroundSize+';'
                        }
                    }
                    else {
                        styles = styles + 'background-color: '+this.settings.backgroundColor+';'
                    }

                    styles = styles + 'height: '+this.settings.height+';'
                    styles = styles + 'padding: '+this.settings.padding+';'
                    styles = styles + 'margin: '+this.settings.margin+';'
                    styles = styles + 'display: flex;'
                    styles = styles + 'flex: 1;'
                }
                return styles
            },
            // columnWidth: function() {
            //     return this.width
            // }
        },
        watch: {
            // action: {
            //     handler(val) {
            //         this.updateItemContent({content: val, uniqueId: this.uniqueId})
            //     },
            //     deep: true
            // },
            'settings.link'(val, old) {
                this.button.link = this.correctUrl(val)
            },
            showHeaders(val, old) {
                this.showBlockHeaders = val
            },
            showLabels(val, old) {
                this.showBlockLabels = val
            },
            settings: {
                handler(newVal) {
                    this.updateItemSettings({settings: newVal, uniqueId: this.uniqueId})
                },
                deep: true
            },
            showHeaders(val, old) {
                this.showBlockHeaders = val
            },
            // columnWidth(val, old) {
            //     this.settings.width = val
            //     this.$forceUpdate()
            // },
        },
        methods: {
            correctUrl(link) {
                let regex = /^(http|https)/;
                if(link.length > 3 && !link.match(regex)) {
                    link = 'http://' + link;
                }
                return link
            },
            responsivePadding() {
                this.settings.paddingResponsive =! this.settings.paddingResponsive

                if(this.settings.paddingResponsive) {
                    this.settings.paddingTablet = this.settings.padding
                    this.settings.paddingMobile = this.settings.padding
                }
            },
            responsiveMargin() {
                this.settings.marginResponsive =! this.settings.marginResponsive

                if(this.settings.marginResponsive) {
                    this.settings.marginTablet = this.settings.margin
                    this.settings.marginMobile = this.settings.margin
                }
            },
            addBlock(compName = undefined) {
                let comp = getComponentByName(compName)
                if (!comp) {
                    console.error('Wrong component\'s name')
                    return false
                }

                let customSettings = processSettingsConfig(compName)

                this.addItem({
                    type: compName,
                    title: customSettings.blockTitle.default,
                    parentId: this.uniqueId,
                    settingsConfig: customSettings,
                    settings: _.mapValues(customSettings, object => (object.default === undefined) ? null : object.default)
                })
            },
            onEnd(evt) {
                // for (var i = 0; i < this.blockList.length; i++) {
                //     this.blockList[i].order = (i+1)
                // }
            },
            removeBlock(blockId) {
                this.removeItem({
                    id: blockId,
                    parentId: this.uniqueId
                })
            }
        }
    }
</script>