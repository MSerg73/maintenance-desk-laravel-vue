<?php

namespace Database\Seeders;

use App\Enums\EquipmentStatus;
use App\Enums\MaintenanceRequestStatus;
use App\Enums\RequestPriority;
use App\Enums\UserRole;
use App\Models\Equipment;
use App\Models\MaintenanceRequest;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $admin = $this->user(
                'admin@example.com',
                'Demo Admin',
                UserRole::Admin,
            );

            $engineer = $this->user(
                'engineer@example.com',
                'Demo Engineer',
                UserRole::Engineer,
            );

            $operator = $this->user(
                'operator@example.com',
                'Demo Operator',
                UserRole::Operator,
            );

            $equipment = [
                'MX-101' => $this->equipment('MX-101', 'Mixer MX-101', 'Mixing line', EquipmentStatus::Active),
                'PK-210' => $this->equipment('PK-210', 'Packaging machine PK-210', 'Packaging line', EquipmentStatus::Maintenance),
                'PU-310' => $this->equipment('PU-310', 'Utility pump PU-310', 'Utilities', EquipmentStatus::Active),
                'CV-420' => $this->equipment('CV-420', 'Conveyor CV-420', 'Dispatch area', EquipmentStatus::OutOfService),
                'TK-510' => $this->equipment('TK-510', 'Process tank TK-510', 'Processing area', EquipmentStatus::Active),
                'CH-610' => $this->equipment('CH-610', 'Chiller CH-610', 'Refrigeration plant', EquipmentStatus::Maintenance),
            ];

            $requests = [
                [
                    'equipment' => 'PK-210',
                    'title' => 'Conveyor drive vibration',
                    'description' => 'Increased vibration detected on the packaging conveyor drive.',
                    'priority' => RequestPriority::from('high'),
                    'status' => MaintenanceRequestStatus::from('in_progress'),
                    'assignee' => $engineer,
                    'hours_ago' => 2,
                ],
                [
                    'equipment' => 'MX-101',
                    'title' => 'Temperature signal drift',
                    'description' => 'Product temperature channel shows intermittent drift during operation.',
                    'priority' => RequestPriority::from('normal'),
                    'status' => MaintenanceRequestStatus::from('new'),
                    'assignee' => null,
                    'hours_ago' => 4,
                ],
                [
                    'equipment' => 'CH-610',
                    'title' => 'Cooling pressure below setpoint',
                    'description' => 'Refrigeration pressure is below the normal operating range.',
                    'priority' => RequestPriority::from('critical'),
                    'status' => MaintenanceRequestStatus::from('new'),
                    'assignee' => $engineer,
                    'hours_ago' => 6,
                ],
                [
                    'equipment' => 'PU-310',
                    'title' => 'Pump seal inspection',
                    'description' => 'Routine inspection completed after a minor leakage report.',
                    'priority' => RequestPriority::from('low'),
                    'status' => MaintenanceRequestStatus::from('done'),
                    'assignee' => $engineer,
                    'hours_ago' => 10,
                ],
                [
                    'equipment' => 'CV-420',
                    'title' => 'Motor insulation fault',
                    'description' => 'Drive motor was isolated after an insulation resistance fault.',
                    'priority' => RequestPriority::from('critical'),
                    'status' => MaintenanceRequestStatus::from('closed'),
                    'assignee' => $engineer,
                    'hours_ago' => 18,
                ],
                [
                    'equipment' => 'TK-510',
                    'title' => 'Level sensor intermittent',
                    'description' => 'Level transmitter periodically drops to an invalid reading.',
                    'priority' => RequestPriority::from('high'),
                    'status' => MaintenanceRequestStatus::from('in_progress'),
                    'assignee' => $engineer,
                    'hours_ago' => 22,
                ],
                [
                    'equipment' => 'MX-101',
                    'title' => 'Knife guard switch check',
                    'description' => 'Safety switch alignment and repeatability check completed.',
                    'priority' => RequestPriority::from('normal'),
                    'status' => MaintenanceRequestStatus::from('done'),
                    'assignee' => $engineer,
                    'hours_ago' => 30,
                ],
                [
                    'equipment' => 'PK-210',
                    'title' => 'Bearing temperature alarm',
                    'description' => 'Bearing temperature alarm investigated and cleared after maintenance.',
                    'priority' => RequestPriority::from('high'),
                    'status' => MaintenanceRequestStatus::from('closed'),
                    'assignee' => $engineer,
                    'hours_ago' => 36,
                ],
            ];

            foreach ($requests as $item) {
                $request = MaintenanceRequest::query()->firstOrNew([
                    'equipment_id' => $equipment[$item['equipment']]->id,
                    'title' => $item['title'],
                ]);

                $request->forceFill([
                    'description' => $item['description'],
                    'priority' => $item['priority']->value,
                    'status' => $item['status']->value,
                    'created_by' => $operator->id,
                    'assigned_to' => $item['assignee']?->id,
                    'created_at' => now()->subHours($item['hours_ago']),
                    'updated_at' => now()->subHours(max(1, (int) ($item['hours_ago'] / 2))),
                ])->save();
            }

            $admin->touch();
        });
    }

    private function user(string $email, string $name, UserRole $role): User
    {
        $user = User::query()->firstOrNew(['email' => $email]);

        $user->forceFill([
            'name' => $name,
            'email' => $email,
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'role' => $role->value,
        ])->save();

        return $user;
    }

    private function equipment(
        string $code,
        string $name,
        string $location,
        EquipmentStatus $status,
    ): Equipment {
        $equipment = Equipment::query()->firstOrNew(['code' => $code]);

        $equipment->forceFill([
            'name' => $name,
            'code' => $code,
            'location' => $location,
            'status' => $status->value,
        ])->save();

        return $equipment;
    }
}
