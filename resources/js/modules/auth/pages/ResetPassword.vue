<!-- modules/auth/pages/ResetPassword.vue -->
<template>
    <!-- Header -->
    <header>
        <v-col cols="12">
            <v-btn icon="mdi-arrow-left" flat density="comfortable" to="/login"></v-btn>
        </v-col>
        <div class="text-center mb-6">
            <h2 class="text-h5 font-weight-bold mb-2">
                Resetowanie hasła
            </h2>
            <p class="text-subtitle-1 text-medium-emphasis">
                Odzyskaj swoje konto ustalając nowe hasło.
            </p>
        </div>
    </header>

    <!-- Form -->
    <v-form @submit.prevent="handleSubmit" ref="form">
        <v-text-field
            v-model="formData.email"
            label="Adres e-mail"
            type="email"
            variant="outlined"
            :rules="emailRules"
            :error-messages="apiErrors.email"
            readonly
            class="mb-4"
            color="primary"
            bg-color="grey-lighten-5"
            :loading="loading"
        ></v-text-field>

        <v-text-field
            v-model="formData.password"
            label="Nowe hasło"
            :type="showPassword ? 'text' : 'password'"
            variant="outlined"
            :rules="passwordRules"
            :error-messages="apiErrors.password"
            :append-inner-icon="showPassword ? 'mdi-eye-off' : 'mdi-eye'"
            @click:append-inner="showPassword = !showPassword"
            color="primary"
            bg-color="grey-lighten-5"
            :loading="loading"
        ></v-text-field>

        <v-text-field
            v-model="formData.password_confirmation"
            label="Powtórz hasło"
            :type="showPassword ? 'text' : 'password'"
            variant="outlined"
            :rules="confirmPasswordRules"
            :error-messages="apiErrors.password_confirmation"
            color="primary"
            bg-color="grey-lighten-5"
            class="mb-4"
            :loading="loading"
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
            Zresetuj hasło
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
export default {
    name: 'ResetPassword',

    data() {
        return {
            formData: {
                email: '',
                password: '',
                password_confirmation: '',
                token: ''
            },
            showPassword: false,
            loading: false,
            apiErrors: {
                email: [],
                password: [],
                password_confirmation: []
            },
            emailRules: [
                v => !!v || 'E-mail jest wymagany',
                v => /.+@.+\..+/.test(v) || 'E-mail musi być poprawny'
            ],
            passwordRules: [
                v => !!v || 'Hasło jest wymagane',
                v => v.length >= 8 || 'Hasło musi mieć minimum 8 znaków'
            ]
        }
    },

    computed: {
        confirmPasswordRules() {
            return [
                v => !!v || 'Potwierdzenie hasła jest wymagane',
                v => v === this.formData.password || 'Hasła muszą być identyczne'
            ]
        }
    },

    created() {
        const { token, email } = this.$route.query
        this.formData.token = token
        this.formData.email = email
    },

    methods: {
        clearApiErrors() {
            this.apiErrors = {
                email: [],
                password: [],
                password_confirmation: []
            }
        },

        async handleSubmit() {
            try {
                const { valid } = await this.$refs.form.validate()
                if (!valid) return

                this.loading = true
                this.clearApiErrors()

                await this.$store.dispatch('auth/resetPassword', this.formData)

                this.$alertSuccess('Hasło zostało pomyślnie zmienione')
                this.$router.push('/login')
            } catch (error) {
                if (error.response?.status === 422) {
                    const errors = error.response.data.errors
                    Object.keys(this.apiErrors).forEach(key => {
                        if (errors[key]) {
                            this.apiErrors[key] = Array.isArray(errors[key])
                                ? errors[key]
                                : [errors[key]]
                        }
                    })
                } else {
                    this.$alertError(
                        error.response?.data?.message ||
                        'Wystąpił błąd podczas resetowania hasła'
                    )
                }
            } finally {
                this.loading = false
            }
        }
    },

    beforeUnmount() {
        this.clearApiErrors()
    }
}
</script>
