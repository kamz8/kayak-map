<template>
    <v-footer app color="dark-green" class="white--text" tag="footer">
        <v-container>
            <v-row>
                <v-col>
                    <h3>Footer Column 1</h3>
                    <p>Link 1</p>
                    <p>Link 2</p>
                </v-col>
                <v-col>
                    <h3>Footer Column 2</h3>
                    <p>Link 3</p>
                    <p>Link 4</p>
                </v-col>
                <v-col>
                    <h3>Footer Column 3</h3>
                    <p>Link 5</p>
                    <p>Link 6</p>
                </v-col>
            </v-row>
        </v-container>
    </v-footer>
</template>

<script>
export default {
    name: 'Footer'
};
</script>

<style scoped>
.dark-green {
    background-color: var(--dark-green);
}
</style>
