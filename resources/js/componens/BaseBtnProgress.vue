<template>
    <v-btn class="pa-0" @click="handleClick" elevation="0">
        <v-progress-linear
            :model-value="progress"
            height="5"
            color="primary"
            class="clickable-progress"
        ></v-progress-linear>
    </v-btn>
</template>

<script>
export default {
    name: 'BaseBtnProgress',
    props: {
        interval: {
            type: Number,
            default: 10000
        },
        isActive: {
            type: Boolean,
            default: false
        }
    },
    data() {
        return {
            progress: 0,
            intervalId: null
        };
    },
    watch: {
        interval(newInterval) {
            this.restartInterval(newInterval);
        },
        isActive(newValue) {
            if (newValue) {
                this.startInterval();
            } else {
                this.clearInterval();
            }
        }
    },
    mounted() {
        if (this.isActive) {
            this.startInterval();
        }
    },
    beforeDestroy() {
        this.clearInterval();
    },
    methods: {
        handleClick() {
            this.$emit('progress-click');
        },
        startInterval() {
            this.clearInterval();
            this.intervalId = setInterval(() => {
                this.progress = (this.progress + 100 / (this.interval / 1000)) % 100;
            }, 1000);
        },
        restartInterval(newInterval) {
            this.interval = newInterval;
            this.startInterval();
        },
        clearInterval() {
            if (this.intervalId) {
                clearInterval(this.intervalId);
                this.intervalId = null;
            }
        }
    }
};
</script>

<style scoped>
.clickable-progress {
    cursor: pointer;
}
.v-btn {
    width: 100%;
}
.v-progress-linear {
    width: 100%;
    height: .8em;
}
</style>
