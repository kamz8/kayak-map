<template>
    <div class="alert-container">
        <transition-group name="alert" tag="div">
            <v-alert
                v-for="message in messages"
                :key="message.id"
                :type="message.type"
                :title="message.title"
                dense
                density="compact"
                theme="light"
                closable
                close-label="Zamknij wiadomość"
                class="mb-2 alert"
            >
                {{ message.text }}
            </v-alert>
        </transition-group>
    </div>
</template>

<script>
import { mapGetters } from 'vuex';

export default {
    name: 'AlertMessages',
    computed: {
        ...mapGetters('system-messages', ['messages']),
    },
};
</script>

<style scoped>
.alert-container {
    position: fixed;
    top: 16px;
    right: 16px;
    z-index: 2000;
    display: flex;
    flex-direction: column;
    gap: 8px;
    max-width: 350px;
}

.alert {
    transition: all 0.5s ease;
}

.alert-enter-from,
.alert-leave-to {
    opacity: 0;
    transform: translateY(-30px);
}

.alert-enter-active {
    transition: all 0.5s ease;
}

.alert-leave-active {
    transition: all 0.5s ease;
    position: absolute;
    right: 16px;
}

.alert-move {
    transition: transform 0.5s ease;
}
</style>
