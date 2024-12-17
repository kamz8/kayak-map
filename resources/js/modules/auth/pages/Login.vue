<!-- modules/auth/pages/Login.vue -->
<template>
    <v-container fluid class="100vh login-wrapper">
        <!-- Background image grid -->
        <div class="background-grid">
            <v-img
                v-for="(image, index) in backgroundImages"
                :key="index"
                :src="`login_img/login${index + 1}.jpg`"
                cover
                height="100%"
                class="background-image"
            >
                <div class="image-overlay"></div>
            </v-img>
        </div>

        <v-container fluid class="login-container fill-height ma-0 pa-0">
            <v-row justify="center" align="center">
                <v-col cols="12" sm="8" md="6" lg="4">
                    <v-card
                        class="mx-auto px-6 py-8"
                        elevation="8"
                        rounded="xl"
                        color="white"
                    >
                        <!-- Header -->
                        <div class="text-center mb-6">
                            <h2 class="text-h5 font-weight-bold mb-2">
                                Witamy z powrotem.
                            </h2>
                            <p class="text-subtitle-1 text-medium-emphasis">
                                Zaloguj się i zacznij odkrywanie.
                            </p>
                        </div>

                        <!-- Form -->
                        <v-form @submit.prevent="onSubmit" ref="form">
                            <v-text-field
                                v-model="formData.email"
                                label="Adres e-mail"
                                type="email"
                                variant="outlined"
                                :rules="emailRules"
                                class="mb-4"
                                color="primary"
                                bg-color="grey-lighten-5"
                            ></v-text-field>

                            <v-text-field
                                v-model="formData.password"
                                label="Hasło"
                                :type="showPassword ? 'text' : 'password'"
                                variant="outlined"
                                :rules="passwordRules"
                                :append-inner-icon="showPassword ? 'mdi-eye-off' : 'mdi-eye'"
                                @click:append-inner="showPassword = !showPassword"
                                color="primary"
                                bg-color="grey-lighten-5"
                            ></v-text-field>

                            <v-btn
                                block
                                color="#2B381F"
                                size="large"
                                type="submit"
                                :loading="loading"
                                class="mt-6 mb-4"
                                flat
                            >
                                Zaloguj się
                            </v-btn>
                        </v-form>

                        <v-btn
                            block
                            variant="plain"
                            color="black"
                            class="mb-3"
                            @click="handleForgotPassword()"
                            flat
                            base-color="primary"
                        >
                            Zapomniałeś hasła?
                        </v-btn>

                        <div class="d-flex align-center my-6">
                            <v-divider></v-divider>
                            <span class="mx-4 text-medium-emphasis text-caption">lub</span>
                            <v-divider></v-divider>
                        </div>

                        <!-- Social login buttons -->
                        <v-btn
                            block
                            variant="outlined"
                            color="black"
                            class="mb-3"
                            @click="handleSocialLogin('google')"
                            flat
                        >
                            <v-icon start icon="mdi-google" class="mr-2"></v-icon>
                            Kontynuuj z Google
                        </v-btn>

                        <v-btn
                            block
                            variant="outlined"
                            color="black"
                            class="mb-6"
                            @click="handleSocialLogin('facebook')"
                            flat
                        >
                            <v-icon start icon="mdi-facebook" class="mr-2"></v-icon>
                            Kontynuuj z Facebookiem
                        </v-btn>

                        <!-- Register link -->
                        <div class="text-center text-caption">
                            <span class="text-medium-emphasis">Nie masz konta?</span>
                            <v-btn
                                variant="text"
                                color="primary"
                                class="ml-2 text-none text-caption"
                                to="/register"
                                density="comfortable"
                            >
                                Zarejestruj się za darmo
                            </v-btn>
                        </div>
                    </v-card>
                </v-col>
            </v-row>
        </v-container>
    </v-container>

</template>

<script>
import { mapActions } from 'vuex';

export default {
    name: 'LoginPage',

    data() {
        return {
            formData: {
                email: '',
                password: ''
            },
            showPassword: false,
            loading: false,
            backgroundImages: [1, 2, 3],
            emailRules: [
                v => !!v || 'E-mail jest wymagany',
                v => /.+@.+\..+/.test(v) || 'E-mail musi być poprawny'
            ],
            passwordRules: [
                v => !!v || 'Hasło jest wymagane',
                v => v.length >= 6 || 'Hasło musi mieć minimum 6 znaków'
            ]
        };
    },

    computed: {
        backgroundStyle() {
            return {
                position: 'fixed',
                top: '64px',
                left: 0,
                right: 0,
                bottom: 0,
                display: 'grid',
                gridTemplateColumns: 'repeat(3, 1fr)',
                gap: 0,
                zIndex: 0
            };
        }
    },

    methods: {
        ...mapActions('auth', ['login', 'initSocialLogin']),

        async onSubmit() {
            try {
                const { valid } = await this.$refs.form.validate();

                if (!valid) return;

                this.loading = true;
                await this.login(this.formData);
                this.$router.push('/');
                this.$alertInfo('Zalogowano pomyślnie');
            } catch (error) {
                this.$alertError(error?.response?.data?.message || 'Błąd podczas logowania');
                console.error('Login error:', error);
            } finally {
                this.loading = false;
            }
        },

        async handleSocialLogin(provider) {
            try {
                this.loading = true;
                const response = await this.initSocialLogin(provider);
                if (response?.redirect_url) {
                    window.location.href = response.redirect_url;
                }
            } catch (error) {
                this.$alertError(`Błąd podczas logowania przez ${provider}`);
                console.error(`${provider} login error:`, error);
            } finally {
                this.loading = false;
            }
        },

        handleForgotPassword() {

        },

        clearForm() {
            this.formData.email = '';
            this.formData.password = '';
            this.$refs.form?.reset();
        }
    },

    beforeUnmount() {
        this.clearForm();
    }
};
</script>

<style scoped>
.login-wrapper {
    position: relative;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    background-color: rgb(246, 246, 246);
    font-family: "Inter", "Poppins", sans-serif;
}

.background-grid {
    position: fixed;
    top: 64px;
    left: 0;
    right: 0;
    bottom: 0;
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 0;
    z-index: 0;
}

.background-image {
    position: relative;
}

.image-overlay {
    position: absolute;
    inset: 0;
    background: rgba(0, 0, 0, 0.4);
}

.login-container {
    position: relative;
    z-index: 1;
    flex: 1 0 auto;

    padding-top: 64px;
}

:deep(.v-btn) {
    text-transform: none;
    letter-spacing: normal;
}

:deep(.v-field__outline__start),
:deep(.v-field__outline__end) {
    border-color: rgba(0, 0, 0, 0.15) !important;
}

@media (max-width: 600px) {
    .background-grid {
        display: none;
    }


}
</style>
