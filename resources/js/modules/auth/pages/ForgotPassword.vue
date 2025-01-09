<!-- modules/auth/pages/ForgotPassword.vue -->
<template>
        <!-- Header -->
    <header>
        <v-col cols="12">
            <v-btn icon="mdi-arrow-left" flat density="comfortable" to="/login"></v-btn>
        </v-col>
        <div class="text-center mb-6">
            <h2 class="text-h5 font-weight-bold mb-2">
                Zapomniałeś hasła?
            </h2>
            <p class="text-subtitle-1 text-medium-emphasis">
                Podaj swój adres email, a wyślemy Ci link do zresetowania hasła.
            </p>
        </div>
    </header>
        <!-- Form -->
        <v-form @submit.prevent="handleSubmit" ref="form">
            <v-text-field
                v-model="email"
                label="Adres e-mail"
                type="email"
                variant="outlined"
                :rules="emailRules"
                :loading="loading"
                :error-messages="apiErrors.email"
                class="mb-4"
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
                Wyślij link do resetowania
            </v-btn>
        </v-form>

        <!-- Back to login -->
        <div class="text-center text-caption">
            <v-btn
                variant="text"
                color="primary"
                to="/login"
                class="text-none"
            >
                Powrót do logowania
            </v-btn>
        </div>
</template>

<script>
import {mapActions, mapGetters} from 'vuex'

export default {
    name: 'ForgotPassword',
    data() {
        return {
            email: '',
            apiErrors: {
                email: []
            },
            emailRules: [
                v => !!v || 'E-mail jest wymagany',
                v => /.+@.+\..+/.test(v) || 'E-mail musi być poprawny'
            ]
        }
    },

    computed: {
        ...mapGetters('auth', ['loading'])
    },

    methods: {
        ...mapActions('auth', ['sendResetLink']),

        clearApiErrors() {
            this.apiErrors = {
                email: []
            }
        },

        async handleSubmit() {
            try {
                const { valid } = await this.$refs.form.validate()
                if (!valid) return

                this.clearApiErrors()

                // Używamy zmapowanej akcji
                await this.sendResetLink({ email: this.email })

                this.$alertSuccess('Link do resetowania hasła został wysłany na podany adres email')
                this.$router.push('/login')
            } catch (error) {
                if (error.response?.status === 422) {
                    const errors = error.response.data.errors
                    if (errors.email) {
                        this.apiErrors.email = Array.isArray(errors.email)
                            ? errors.email
                            : [errors.email]
                    }
                } else {
                    console.log(error)
                    this.$alertError(
                        error.response?.data?.message ||
                        'Wystąpił błąd podczas wysyłania linku'
                    )
                }
            }
        }
    },

    beforeUnmount() {
        this.clearApiErrors()
    }
}
</script>
