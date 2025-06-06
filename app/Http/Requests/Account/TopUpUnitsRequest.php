<?php

    namespace App\Http\Requests\Account;

    use Illuminate\Contracts\Validation\ValidationRule;
    use Illuminate\Foundation\Http\FormRequest;

    class TopUpUnitsRequest extends FormRequest
    {
        /**
         * Determine if the user is authorized to make this request.
         */
        public function authorize(): bool
        {
            return true;
        }

        /**
         * Get the validation rules that apply to the request.
         *
         * @return array<string, ValidationRule|array|string>
         */
        public function rules(): array
        {
            return [
                'add_unit' => 'required|numeric|min:0',
                'sms_unit' => 'required|numeric|min:0',
            ];
        }

    }
