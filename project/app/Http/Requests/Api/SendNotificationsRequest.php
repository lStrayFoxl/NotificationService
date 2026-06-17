<?php

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class SendNotificationsRequest extends FormRequest {
    public function authorize(): bool {
        return true;
    }

    public function rules(): array {
        return [
            'notification_type' => 'required|string|exists:notification_types,type',
            'channel' => 'required|in:sms,email',
            'message' => 'required|string',
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'exists:users,id',
        ];
    }

    public function messages(): array {
        return [
            'notification_type.required' => 'Поле notification_type обязательно для заполнения.',
            'notification_type.exists' => 'Указанный notification_type не существует.',
            'channel.required' => 'Поле channel обязательно для заполнения.',
            'channel.in' => 'Поле channel должно быть "sms" или "email".',
            'message.required' => 'Поле message обязательно для заполнения.',
            'user_ids.required' => 'Поле user_ids обязательно для заполнения.',
            'user_ids.array' => 'Поле user_ids должно быть массивом.',
            'user_ids.min' => 'Поле user_ids должно содержать хотя бы один ID.',
            'user_ids.*.exists' => 'Не все из указанных пользователей, в поле user_ids, найдены.',
        ];
    }

    protected function failedValidation(Validator $validator) {
        $errors = $validator->errors();
        throw new HttpResponseException(
            response()->json(['errors' => $errors], 422)
        );
    }
}
