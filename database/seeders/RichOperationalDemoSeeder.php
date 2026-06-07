<?php

namespace Database\Seeders;

use App\Models\Agent;
use App\Models\Badge;
use App\Models\Campaign;
use App\Models\CustomerGift;
use App\Models\DeliveryRoute;
use App\Models\Employee;
use App\Models\EmployeeAllowance;
use App\Models\EmployeeBadge;
use App\Models\EmployeeEquipment;
use App\Models\EmployeeLeave;
use App\Models\Vehicle;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Rich operational demo data: 20+ employees, 30 agents, fleet, routes,
 * HR records, marketing, and customer gifts. Idempotent via firstOrCreate.
 */
class RichOperationalDemoSeeder extends Seeder
{
    protected array $zones = [
        'Dhaka North' => ['Mirpur', 'Uttara', 'Mohakhali', 'Banani'],
        'Dhaka South' => ['Jatrabari', 'Demra', 'Shyampur', 'Keraniganj'],
        'Dhaka East' => ['Badda', 'Rampura', 'Khilgaon', 'Motijheel'],
        'Dhaka West' => ['Mohammadpur', 'Dhanmondi', 'Gabtoli', 'Shyamoli'],
        'Chattogram' => ['Agrabad', 'Halishahar', 'Pahartali', 'Patenga'],
        'Sylhet' => ['Zindabazar', 'Ambarkhana', 'Subhanighat', 'Tilagor'],
        'Rajshahi' => ['Shaheb Bazar', 'Boalia', 'Padma', 'Damkura'],
        'Khulna' => ['Sonadanga', 'Khalishpur', 'Daulatpur', 'Boyra'],
    ];

    public function run(): void
    {
        $employees = $this->seedEmployees();
        $this->seedBadges($employees);
        $this->seedEquipment($employees);
        $this->seedLeaves($employees);
        $this->seedAllowances($employees);
        $vehicles = $this->seedVehicles();
        $this->seedAgents();
        $this->seedRoutes($vehicles);
        $this->seedCampaigns();
        $this->seedCustomerGifts($employees);
    }

    protected function seedEmployees(): array
    {
        $rows = [
            ['Admin Staff', 'Administration', 'System admin', 'Head office', 'admin.staff@demo.local'],
            ['Warehouse Manager', 'Inventory & logistics', 'Warehouse manager', 'Factory / central depot', 'warehouse@demo.local'],
            ['Production Manager', 'Production', 'Production manager', 'Factory', 'production@demo.local'],
            ['Sales Manager', 'Sales & marketing', 'Sales manager', 'National', 'sales.manager@demo.local'],
            ['QC Officer', 'Quality control', 'QC officer', 'Factory', 'qc@demo.local'],
            ['QC Assistant 01', 'Quality control', 'QC assistant', 'Factory', 'qc.asst01@demo.local'],
            ['QC Assistant 02', 'Quality control', 'QC assistant', 'Factory', 'qc.asst02@demo.local'],
            ['Warehouse Supervisor', 'Inventory & logistics', 'Warehouse supervisor', 'Factory / central depot', 'wh.super@demo.local'],
            ['Store Keeper 01', 'Inventory & logistics', 'Store keeper', 'Factory / central depot', 'store01@demo.local'],
            ['Store Keeper 02', 'Inventory & logistics', 'Store keeper', 'Chattogram depot', 'store02@demo.local'],
            ['Line Operator 01', 'Production', 'Line operator', 'Factory line 1', 'line01@demo.local'],
            ['Line Operator 02', 'Production', 'Line operator', 'Factory line 2', 'line02@demo.local'],
            ['Line Operator 03', 'Production', 'Line operator', 'Factory line 2', 'line03@demo.local'],
            ['Maintenance Tech', 'Production', 'Maintenance technician', 'Factory', 'maint@demo.local'],
            ['Field Sales Rep 01', 'Sales & marketing', 'Sales representative', 'Dhaka North', 'salesrep01@demo.local'],
            ['Field Sales Rep 02', 'Sales & marketing', 'Sales representative', 'Dhaka South', 'salesrep02@demo.local'],
            ['Field Sales Rep 03', 'Sales & marketing', 'Sales representative', 'Dhaka East', 'salesrep03@demo.local'],
            ['Field Sales Rep 04', 'Sales & marketing', 'Sales representative', 'Chattogram', 'salesrep04@demo.local'],
            ['Driver 01', 'Inventory & logistics', 'Delivery driver', 'Dhaka North', 'driver01@demo.local'],
            ['Driver 02', 'Inventory & logistics', 'Delivery driver', 'Dhaka South', 'driver02@demo.local'],
            ['Accountant 01', 'Finance', 'Accounts officer', 'Head office', 'accounts01@demo.local'],
            ['HR Officer', 'Administration', 'HR officer', 'Head office', 'hr@demo.local'],
            ['Marketing Executive', 'Sales & marketing', 'Marketing executive', 'National', 'marketing@demo.local'],
        ];

        $employees = [];

        foreach ($rows as $index => [$name, $department, $position, $zone, $email]) {
            $employees[$name] = Employee::firstOrCreate(
                ['name' => $name],
                [
                    'work_email' => $email,
                    'work_phone' => '02-' . str_pad((string) ($index + 1), 7, '0', STR_PAD_LEFT),
                    'work_mobile' => '017' . str_pad((string) (10000000 + $index), 8, '0', STR_PAD_LEFT),
                    'department' => $department,
                    'job_position' => $position,
                    'work_zone' => $zone,
                ]
            );
        }

        return $employees;
    }

    protected function seedBadges(array $employees): void
    {
        $definitions = [
            ['TOP-PERFORMER', 'Top performer', 'Outstanding sales or operations performance', '#2563eb'],
            ['ON-TIME-DELIVERY', 'On-time delivery', 'Consistently on-time dispatch', '#16a34a'],
            ['QC-EXCELLENCE', 'QC excellence', 'Zero critical QC failures in 90 days', '#7c3aed'],
            ['SAFETY-STAR', 'Safety star', 'No safety incidents this quarter', '#ea580c'],
            ['TEAM-PLAYER', 'Team player', 'Cross-team support recognised by management', '#0891b2'],
            ['ROUTE-MASTER', 'Route master', 'Best route coverage and agent visits', '#db2777'],
            ['ATTENDANCE', 'Perfect attendance', 'No unplanned absences this quarter', '#059669'],
            ['INNOVATION', 'Innovation', 'Process improvement suggestion adopted', '#4f46e5'],
        ];

        $badges = [];
        foreach ($definitions as [$code, $name, $description, $color]) {
            $badges[$code] = Badge::firstOrCreate(
                ['code' => $code],
                compact('name', 'description', 'color') + ['is_active' => true]
            );
        }

        $assignments = [
            ['Field Sales Rep 01', 'TOP-PERFORMER'],
            ['Field Sales Rep 02', 'ROUTE-MASTER'],
            ['Warehouse Manager', 'ON-TIME-DELIVERY'],
            ['QC Officer', 'QC-EXCELLENCE'],
            ['Line Operator 01', 'SAFETY-STAR'],
            ['Driver 01', 'ON-TIME-DELIVERY'],
            ['Marketing Executive', 'INNOVATION'],
            ['HR Officer', 'TEAM-PLAYER'],
            ['Store Keeper 01', 'ATTENDANCE'],
            ['Field Sales Rep 03', 'TOP-PERFORMER'],
        ];

        foreach ($assignments as [$employeeName, $badgeCode]) {
            $employee = $employees[$employeeName] ?? null;
            $badge = $badges[$badgeCode] ?? null;

            if (! $employee || ! $badge) {
                continue;
            }

            EmployeeBadge::firstOrCreate(
                [
                    'employee_id' => $employee->id,
                    'badge_id' => $badge->id,
                    'granted_at' => Carbon::today()->subDays(rand(7, 90)),
                ],
                [
                    'granted_by' => 'Admin Staff',
                    'note' => 'Demo badge assignment',
                ]
            );
        }
    }

    protected function seedEquipment(array $employees): void
    {
        $items = [
            ['Field Sales Rep 01', 'Android tablet', 'TAB-SR01-0001'],
            ['Field Sales Rep 01', 'Smartphone', 'PHN-SR01-0001'],
            ['Field Sales Rep 02', 'Android tablet', 'TAB-SR02-0001'],
            ['Field Sales Rep 03', 'Smartphone', 'PHN-SR03-0001'],
            ['Field Sales Rep 04', 'Android tablet', 'TAB-SR04-0001'],
            ['Warehouse Manager', 'Handheld barcode scanner', 'SCAN-WHM-0001'],
            ['Warehouse Supervisor', 'Handheld barcode scanner', 'SCAN-WHS-0001'],
            ['Store Keeper 01', 'Safety helmet', null],
            ['Store Keeper 02', 'Safety helmet', null],
            ['QC Officer', 'TDS & pH meter', 'QC-MTR-0001'],
            ['QC Assistant 01', 'Refractometer', 'QC-REF-0001'],
            ['QC Assistant 02', 'Sample kit case', 'QC-KIT-0001'],
            ['Driver 01', 'Delivery smartphone', 'PHN-DRV-0001'],
            ['Driver 02', 'Delivery smartphone', 'PHN-DRV-0002'],
            ['Maintenance Tech', 'Tool kit', 'MNT-TK-0001'],
            ['Marketing Executive', 'Laptop', 'MKT-LP-0001'],
            ['Production Manager', 'Production tablet', 'TAB-PM-0001'],
            ['Line Operator 01', 'Safety boots', null],
            ['Line Operator 02', 'Safety boots', null],
            ['Accountant 01', 'Laptop', 'FIN-LP-0001'],
        ];

        foreach ($items as [$employeeName, $productName, $deviceId]) {
            $employee = $employees[$employeeName] ?? null;
            if (! $employee) {
                continue;
            }

            EmployeeEquipment::firstOrCreate(
                [
                    'employee_id' => $employee->id,
                    'product_name' => $productName,
                    'effective_date' => Carbon::today()->subDays(rand(14, 120)),
                ],
                [
                    'device_identifier' => $deviceId,
                    'status' => 'assigned',
                    'notes' => 'Demo equipment assignment',
                ]
            );
        }
    }

    protected function seedLeaves(array $employees): void
    {
        $today = Carbon::today();

        $rows = [
            ['Warehouse Manager', 'annual', $today->copy()->addDays(10), $today->copy()->addDays(12), 'approved', 'Planned annual leave'],
            ['Field Sales Rep 01', 'sick', $today->copy()->subDays(2), $today->copy()->subDays(1), 'approved', 'Flu and fever'],
            ['Field Sales Rep 02', 'sick', $today->copy()->addDay(), $today->copy()->addDays(2), 'pending', 'Doctor advised rest'],
            ['Line Operator 02', 'sick', $today->copy()->subDays(5), $today->copy()->subDays(4), 'approved', 'Food poisoning'],
            ['Store Keeper 01', 'casual', $today->copy()->addDays(4), $today->copy()->addDays(4), 'pending', 'Family event'],
            ['QC Assistant 01', 'sick', $today->copy()->subDays(1), $today, 'approved', 'Migraine'],
            ['Driver 02', 'annual', $today->copy()->addDays(20), $today->copy()->addDays(24), 'approved', 'Eid holiday travel'],
            ['HR Officer', 'casual', $today->copy()->subDays(8), $today->copy()->subDays(8), 'rejected', 'Peak payroll week'],
            ['Marketing Executive', 'annual', $today->copy()->addDays(15), $today->copy()->addDays(18), 'pending', 'Campaign planning break'],
            ['Line Operator 03', 'sick', $today->copy()->subDays(3), $today->copy()->subDays(2), 'approved', 'Back pain'],
        ];

        foreach ($rows as [$employeeName, $type, $start, $end, $status, $reason]) {
            $employee = $employees[$employeeName] ?? null;
            if (! $employee) {
                continue;
            }

            EmployeeLeave::firstOrCreate(
                [
                    'employee_id' => $employee->id,
                    'start_date' => $start,
                    'end_date' => $end,
                    'type' => $type,
                ],
                [
                    'reason' => $reason,
                    'status' => $status,
                    'approved_by' => in_array($status, ['approved', 'rejected'], true) ? 'Admin Staff' : null,
                    'approved_at' => in_array($status, ['approved', 'rejected'], true) ? Carbon::now()->subDays(1) : null,
                ]
            );
        }
    }

    protected function seedAllowances(array $employees): void
    {
        $today = Carbon::today();
        $types = ['TA', 'DA', 'BONUS'];

        foreach ($employees as $name => $employee) {
            if (in_array($name, ['Admin Staff', 'HR Officer'], true)) {
                continue;
            }

            foreach ($types as $offset => $type) {
                EmployeeAllowance::firstOrCreate(
                    [
                        'employee_id' => $employee->id,
                        'date' => $today->copy()->subDays(($offset + 1) * 3 + ($employee->id % 4)),
                        'type' => $type,
                        'reference' => strtoupper(substr(str_replace(' ', '-', $type), 0, 3)) . '-' . $employee->id . '-' . ($offset + 1),
                    ],
                    [
                        'amount' => match ($type) {
                            'BONUS' => rand(800, 2500),
                            'TA' => rand(250, 900),
                            default => rand(200, 600),
                        },
                        'description' => match ($type) {
                            'BONUS' => 'Performance bonus — demo data',
                            'TA' => 'Travel allowance — demo route visits',
                            default => 'Daily allowance — field work',
                        },
                        'status' => ['approved', 'approved', 'submitted'][$offset],
                    ]
                );
            }
        }
    }

    protected function seedVehicles(): array
    {
        $drivers = [
            'Abdul Karim', 'Rahim Uddin', 'Jamal Hossain', 'Shah Alam', 'Nur Islam',
            'Kamal Ahmed', 'Faruk Mia', 'Saiful Islam', 'Anwar Hossain', 'Mizanur Rahman',
            'Hasan Ali', 'Imran Khan',
        ];

        $vehicles = [];

        for ($i = 1; $i <= 8; $i++) {
            $name = 'Truck ' . $i;
            $vehicles[$name] = Vehicle::firstOrCreate(
                ['name' => $name],
                [
                    'type' => 'truck',
                    'license_plate' => 'DHAKA-METRO-TA-' . str_pad((string) (1000 + $i), 4, '0', STR_PAD_LEFT),
                    'driver' => $drivers[$i - 1] ?? 'Driver ' . $i,
                    'capacity_crates' => 160 + ($i * 10),
                    'is_active' => true,
                ]
            );
        }

        for ($i = 1; $i <= 4; $i++) {
            $name = 'Van ' . $i;
            $vehicles[$name] = Vehicle::firstOrCreate(
                ['name' => $name],
                [
                    'type' => 'van',
                    'license_plate' => 'DHAKA-METRO-VN-' . str_pad((string) (2000 + $i), 4, '0', STR_PAD_LEFT),
                    'driver' => $drivers[7 + $i] ?? 'Van driver ' . $i,
                    'capacity_crates' => 80 + ($i * 5),
                    'is_active' => true,
                ]
            );
        }

        return $vehicles;
    }

    protected function seedAgents(): void
    {
        $counter = 1;
        $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

        foreach ($this->zones as $zone => $areas) {
            foreach ($areas as $areaIndex => $area) {
                if ($counter > 30) {
                    break 2;
                }

                $code = strtoupper(substr(str_replace(' ', '', $zone), 0, 3))
                    . '-' . strtoupper(substr(str_replace(' ', '', $area), 0, 3))
                    . '-' . str_pad((string) $counter, 2, '0', STR_PAD_LEFT);

                Agent::firstOrCreate(
                    ['location_code' => $code],
                    [
                        'name' => $zone . ' dealer ' . str_pad((string) ($areaIndex + 1), 2, '0', STR_PAD_LEFT),
                        'email' => 'agent.' . strtolower(str_replace('-', '.', $code)) . '@demo.local',
                        'phone' => '017' . str_pad((string) (12000000 + $counter), 8, '0', STR_PAD_LEFT),
                        'area' => $area,
                        'zone' => $zone,
                        'credit_limit' => 100000 + ($counter * 5000),
                        'withholding_rate' => $counter % 5 === 0 ? 5 : 0,
                        'is_active' => true,
                    ]
                );

                $counter++;
            }
        }
    }

    protected function seedRoutes(array $vehicles): void
    {
        $vehicleList = array_values($vehicles);
        $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        $routeNo = 1;

        foreach ($this->zones as $zone => $areas) {
            foreach (array_slice($areas, 0, 2) as $areaIndex => $area) {
                $vehicle = $vehicleList[($routeNo - 1) % max(1, count($vehicleList))] ?? null;
                if (! $vehicle) {
                    continue;
                }

                DeliveryRoute::firstOrCreate(
                    ['name' => $zone . ' route ' . ($areaIndex + 1)],
                    [
                        'zone' => $zone,
                        'day' => $days[$routeNo % count($days)],
                        'vehicle_id' => $vehicle->id,
                        'driver' => $vehicle->driver,
                    ]
                );

                $routeNo++;
            }
        }
    }

    protected function seedCampaigns(): void
    {
        $campaigns = [
            ['Summer hydration push', 'facebook', 'running', 65000, 42000, 28000],
            ['Dhaka North launch', 'facebook', 'running', 50000, 32000, 22000],
            ['Chattogram dealer drive', 'google-ads', 'running', 72000, 48000, 35000],
            ['Ramadan offer', 'google-ads', 'completed', 80000, 52000, 40000],
            ['School season promo', 'instagram', 'running', 45000, 30000, 18000],
            ['20L jar refill campaign', 'facebook', 'planned', 30000, 0, 12000],
            ['Agent onboarding ads', 'google-ads', 'completed', 38000, 25000, 15000],
            ['Brand awareness TV digital', 'other', 'running', 90000, 60000, 45000],
        ];

        foreach ($campaigns as $index => [$name, $platform, $status, $reach, $impressions, $cost]) {
            Campaign::firstOrCreate(
                ['campaign_code' => 'CMP-2026-' . str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT)],
                [
                    'name' => $name,
                    'platform' => $platform,
                    'start_date' => now()->subDays(40 - ($index * 4))->toDateString(),
                    'end_date' => $status === 'completed'
                        ? now()->subDays(5)->toDateString()
                        : now()->addDays(20 + $index)->toDateString(),
                    'reach' => $reach,
                    'impressions' => $impressions,
                    'cost' => $cost,
                    'status' => $status,
                    'notes' => 'Rich demo campaign data',
                ]
            );
        }
    }

    protected function seedCustomerGifts(array $employees): void
    {
        $agents = Agent::query()->orderBy('id')->limit(15)->get();
        $salesRep = $employees['Field Sales Rep 01'] ?? null;
        $giftTypes = ['Fridge', 'Banner', 'Display rack', 'Cooler', 'POSM kit', 'Umbrella'];
        $occasions = ['Yearly', 'Festival', 'Launch', 'Loyalty', 'Eid'];

        foreach ($agents as $index => $agent) {
            CustomerGift::firstOrCreate(
                [
                    'agent_id' => $agent->id,
                    'campaign_code' => 'CG-2026-' . str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT),
                ],
                [
                    'employee_id' => $salesRep?->id,
                    'date' => now()->subDays(20 - $index)->toDateString(),
                    'gift_type' => $giftTypes[$index % count($giftTypes)],
                    'occasion' => $occasions[$index % count($occasions)],
                    'description' => 'Demo customer gift for ' . $agent->name,
                    'amount' => 1500 + ($index * 750),
                    'status' => $index % 3 === 0 ? 'planned' : 'given',
                ]
            );
        }
    }
}
