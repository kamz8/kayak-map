import appConfigFile from "@/config/appConfig.js";
import unitMixin from "@/mixins/UnitMixin.js";
export default {
    computed: {
        appConfig() {
            return appConfigFile;
        },
        placeholderImage() {
            return appConfigFile.placeholderImage
        }
    },
    methods: {
    ...unitMixin.methods
    }
};
