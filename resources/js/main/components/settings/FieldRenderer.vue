<template>
  <a-col :span="colSpan" class="mb-4">
    <a-form-item
      :label="field.label"
      :help="error || field.help"
      :validate-status="error ? 'error' : ''"
      :required="isRequired"
    >
      <component
        :is="fieldComponent"
        :field="field"
        :value="value"
        @update:value="onUpdate"
      />
    </a-form-item>
  </a-col>
</template>

<script>
import { computed } from 'vue';

// Import all specific field components
import TextField from './fields/TextField.vue';
import TextAreaField from './fields/TextAreaField.vue';
import PasswordField from './fields/PasswordField.vue';
import NumberField from './fields/NumberField.vue';
import BooleanField from './fields/BooleanField.vue';
import SelectField from './fields/SelectField.vue';
import ColorField from './fields/ColorField.vue';
import DateField from './fields/DateField.vue';
import DateTimeField from './fields/DateTimeField.vue';
import FileField from './fields/FileField.vue';
import ImageField from './fields/ImageField.vue';
import MarkdownField from './fields/MarkdownField.vue';
import RichTextField from './fields/RichTextField.vue';

export default {
  name: 'FieldRenderer',
  props: {
    field: {
      type: Object,
      required: true,
    },
    value: {
      type: [String, Number, Boolean, Array],
      default: undefined,
    },
    error: {
      type: String,
      default: '',
    },
  },
  emits: ['update:value'],
  setup(props, { emit }) {
    const colSpan = computed(() => {
      const w = Number(props.field.width);
      return !isNaN(w) && w >= 1 && w <= 24 ? w : 24;
    });

    const isRequired = computed(() => {
      return !!props.field.validation?.required;
    });

    const fieldComponent = computed(() => {
      const type = props.field.type || 'text';
      const componentMap = {
        text: TextField,
        textarea: TextAreaField,
        password: PasswordField,
        number: NumberField,
        boolean: BooleanField,
        select: SelectField,
        multiselect: SelectField, // Both map to SelectField (internally checked for multiple mode)
        color: ColorField,
        date: DateField,
        datetime: DateTimeField,
        file: FileField,
        image: ImageField,
        markdown: MarkdownField,
        richtext: RichTextField,
      };

      return componentMap[type] || TextField;
    });

    const onUpdate = (newVal) => {
      emit('update:value', newVal);
    };

    return {
      colSpan,
      isRequired,
      fieldComponent,
      onUpdate,
    };
  },
};
</script>
