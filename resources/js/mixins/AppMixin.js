import appConfigFile from "@/config/appConfig.js";
import unitMixin from "@/mixins/UnitMixin.js";
export default {
    data () {
      return{
          pageLoading: false
      }
    },
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
