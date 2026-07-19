<?php

declare(strict_types=1);

namespace App\Http\Requests\Housekeeping;

use Illuminate\Foundation\Http\FormRequest;

final class AssignHousekeepingTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'assignee_id' => [
                'required',
                'string',
                'exists:users,id',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'assignee_id' => 'assignee',
        ];
    }
}