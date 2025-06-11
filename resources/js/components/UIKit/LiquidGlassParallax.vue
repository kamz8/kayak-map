<template>
    <v-parallax
        :src="imageSrc"
        :height="height"
        class="liquid-glass-parallax"
    >
        <div class="liquid-glass-overlay">
            <slot></slot>
        </div>
    </v-parallax>
</template>

<script>
export default {
    name: 'LiquidGlass',
    props: {
        imageSrc: {
            type: String,
            required: true
        },
        height: {
            type: [String, Number],
            default: 550
        },
        blurStrength: {
            type: Number,
            default: 8
        },
        overlayOpacity: {
            type: Number,
            default: 0.15,
            validator: (value) => value >= 0 && value <= 1
        }
    }
}
</script>

<style scoped>
.liquid-glass-parallax {
    position: relative;
}

.liquid-glass-parallax::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: inherit;
    filter: blur(v-bind('blurStrength + "px"'));
    z-index: 0;
    transform: scale(1.05);
}

.liquid-glass-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, v-bind(overlayOpacity));
    backdrop-filter: blur(v-bind('blurStrength / 2 + "px"'));
    -webkit-backdrop-filter: blur(v-bind('blurStrength / 2 + "px"'));
    z-index: 1;
    display: flex;
    align-items: center;
}
</style>
