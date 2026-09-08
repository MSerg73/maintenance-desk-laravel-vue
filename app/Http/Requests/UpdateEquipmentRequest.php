<?php

namespace App\Http\Requests;

use App\Enums\EquipmentStatus;
use App\Models\Equipment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEquipmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $equipment = $this->route('equipment');

        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'required',
                'string',
                'max:100',
                Rule::unique('equipment', 'code')->ignore(
                    $equipment instanceof Equipment ? $equipment->getKey() : $equipment,
                ),
            ],
            'location' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::enum(EquipmentStatus::class)],
        ];
    }
}
