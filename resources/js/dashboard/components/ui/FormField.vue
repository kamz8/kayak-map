<template>
    <div class="form-field">
        <!-- Text Field -->
        <v-text-field
            v-if="type === 'text' || type === 'email' || type === 'password'"
            :model-value="modelValue"
            :type="type"
            :label="label"
            :placeholder="placeholder"
            :rules="validationRules"
            :error-messages="errorMessages"
            :required="required"
            :disabled="disabled"
            :readonly="readonly"
            :prepend-inner-icon="prependIcon"
            :append-inner-icon="appendIcon"
            :prefix="prefix"
            :suffix="suffix"
            :variant="variant"
            :density="density"
            :clearable="clearable"
            :counter="counter"
            :maxlength="maxlength"
            @update:model-value="$emit('update:modelValue', $event)"
            @blur="validateField"
        />

        <!-- Textarea -->
        <v-textarea
            v-else-if="type === 'textarea'"
            :model-value="modelValue"
            :label="label"
            :placeholder="placeholder"
            :rules="validationRules"
            :error-messages="errorMessages"
            :required="required"
            :disabled="disabled"
            :readonly="readonly"
            :rows="rows"
            :variant="variant"
            :density="density"
            :clearable="clearable"
            :counter="counter"
            :maxlength="maxlength"
            @update:model-value="$emit('update:modelValue', $event)"
        >
            <template v-if="counterTemplate" #counter="{ value, max }">
                {{ counterTemplate(value, max) }}
            </template>
        </v-textarea>

        <!-- Select -->
        <v-select
            v-else-if="type === 'select'"
            :model-value="modelValue"
            :items="options"
            :label="label"
            :placeholder="placeholder"
            :rules="validationRules"
            :error-messages="errorMessages"
            :required="required"
            :disabled="disabled"
            :readonly="readonly"
            :multiple="multiple"
            :chips="multiple"
            :clearable="clearable"
            :variant="variant"
            :density="density"
            @update:model-value="$emit('update:modelValue', $event)"
        />

        <!-- Checkbox -->
        <v-checkbox
            v-else-if="type === 'checkbox'"
            :model-value="modelValue"
            :label="label"
            :rules="validationRules"
            :error-messages="errorMessages"
            :disabled="disabled"
            :readonly="readonly"
            @update:model-value="$emit('update:modelValue', $event)"
        />

        <!-- File Upload -->
        <v-file-input
            v-else-if="type === 'file'"
            :model-value="modelValue"
            :label="label"
            :rules="validationRules"
            :error-messages="errorMessages"
            :required="required"
            :disabled="disabled"
            :readonly="readonly"
            :accept="accept"
            :multiple="multiple"
            :variant="variant"
            :density="density"
            @update:model-value="$emit('update:modelValue', $event)"
        />

        <!-- Help text -->
        <div v-if="helpText" class="text-caption text-medium-emphasis mt-1">
            {{ helpText }}
        </div>
    </div>
</template>

<script>
export default {
    name: 'FormField',
    emits: ['update:modelValue'],
    props: {
        modelValue: [String, Number, Boolean, Array, File],
        type: {
            type: String,
            default: 'text',
            validator: (value) => [
                'text', 'email', 'password', 'textarea', 'select',
                'checkbox', 'file'
            ].includes(value)
        },
        label: String,
        placeholder: String,
        required: Boolean,
        disabled: Boolean,
        readonly: Boolean,
        clearable: Boolean,
        multiple: Boolean,
        rules: Array,
        options: Array,
        rows: {
            type: Number,
            default: 3
        },
        accept: String,
        prependIcon: String,
        appendIcon: String,
        prefix: String,
        suffix: String,
        variant: {
            type: String,
            default: 'outlined'
        },
        density: {
            type: String,
            default: 'compact'
        },
        helpText: String,
        // Nowe propsy dla countera
        counter: [Boolean, Number, String],
        maxlength: [Number, String],
        counterTemplate: Function
    },
    data() {
        return {
            errorMessages: []
        }
    },
    computed: {
        validationRules() {
            const rules = [...(this.rules || [])]

            if (this.required) {
                rules.unshift((v) => !!v || `${this.label || 'To pole'} jest wymagane`)
            }

            if (this.type === 'email') {
                rules.push((v) => {
                    if (!v) return true
                    const pattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
                    return pattern.test(v) || 'Wprowadź poprawny adres email'
                })
            }

            // Automatyczna reguła dla maxlength jeśli podano
            if (this.maxlength) {
                rules.push((v) => !v || v.length <= this.maxlength || `Maksymalnie ${this.maxlength} znaków`)
            }

            return rules
        }
    },
    methods: {
        validateField() {
            this.errorMessages = []

            for (const rule of this.validationRules) {
                const result = rule(this.modelValue)
                if (result !== true) {
                    this.errorMessages.push(result)
                    break
                }
            }
        },
        validate() {
            this.validateField()
            return this.errorMessages.length === 0
        }
    }
}
</script>
