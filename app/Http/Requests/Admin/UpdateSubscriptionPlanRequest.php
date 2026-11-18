<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSubscriptionPlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdminUser() ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'price_xof' => ['required', 'integer', 'min:0'],
            'duration_days' => ['required', 'integer', 'min:1', 'max:3650'],
            'daily_cap_xof' => ['nullable', 'integer', 'min:0'],
            'indicative_task_value_xof' => ['required', 'integer', 'min:0'],
            'max_tasks_per_day' => ['required', 'integer', 'min:1', 'max:10'],
            'is_active' => ['sometimes', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'benefits_text' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
