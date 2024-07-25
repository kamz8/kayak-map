<template>
    <div class="alert-container">
        <v-alert
            v-for="(message, index) in messages"
            :key="index"
            :type="message.type"
            dense
            prominent
            class="mb-2"
        >
            {{ message.text }}
        </v-alert>
    </div>
</template>

<script>
import { mapGetters } from 'vuex';

export default {
    name: 'AlertMessages',
    computed: {
        ...mapGetters([{
            messages: 'system_messages/messages'
        }]),
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
}
</style>
