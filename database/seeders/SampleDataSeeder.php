<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\Client;
use App\Models\Project;
use Illuminate\Database\Seeder;

class SampleDataSeeder extends Seeder
{
    public function run(): void
    {
        // Get existing client
        $client = Client::first();
        
        // Create more clients first
        $clients = [
            [
                'name' => 'Universiti Malaysia Sarawak (UNIMAS)',
                'organization_type' => 'gov',
                'address_1' => '94300 Kota Samarahan',
                'state' => 'Sarawak',
                'country' => 'Malaysia',
                'is_active' => true,
            ],
            [
                'name' => 'Hospital Umum Sarawak',
                'organization_type' => 'gov',
                'address_1' => 'Jalan Hospital',
                'postcode' => '93586',
                'district' => 'Kuching',
                'state' => 'Sarawak',
                'country' => 'Malaysia',
                'is_active' => true,
            ],
            [
                'name' => 'Sarawak Energy Berhad',
                'organization_type' => 'company',
                'address_1' => 'No. 1, The Isthmus',
                'postcode' => '93050',
                'district' => 'Kuching',
                'state' => 'Sarawak',
                'country' => 'Malaysia',
                'website' => 'https://www.sarawakenergy.com',
                'is_active' => true,
            ],
        ];

        foreach ($clients as $clientData) {
            Client::firstOrCreate(['name' => $clientData['name']], $clientData);
        }

        // Create Projects
        $projects = [
            [
                'name' => 'ICT Infrastructure Upgrade 2025',
                'description' => 'Projek naik taraf infrastruktur ICT termasuk server, networking dan workstation untuk Politeknik Mukah',
                'client_id' => 1, // Politeknik Mukah
                'project_value' => 450000.00,
                'start_date' => '2025-01-15',
                'end_date' => '2025-06-30',
                'status' => 'active',
                'purchase_date' => '2025-01-10',
                'po_number' => 'PO/PMK/2025/001',
                'warranty_period' => '36 months',
                'warranty_expiry' => '2028-01-10',
            ],
            [
                'name' => 'Computer Lab Renovation - Block A',
                'description' => 'Pembaharuan makmal komputer Block A dengan 40 unit PC baru dan peralatan rangkaian',
                'client_id' => 1,
                'project_value' => 280000.00,
                'start_date' => '2025-02-01',
                'end_date' => '2025-04-30',
                'status' => 'active',
                'purchase_date' => '2025-01-25',
                'po_number' => 'PO/PMK/2025/002',
                'warranty_period' => '24 months',
                'warranty_expiry' => '2027-01-25',
            ],
            [
                'name' => 'UNIMAS Data Center Enhancement',
                'description' => 'Peningkatan kapasiti data center termasuk server rack, storage dan UPS',
                'client_id' => Client::where('name', 'like', '%UNIMAS%')->first()?->id ?? 2,
                'project_value' => 850000.00,
                'start_date' => '2025-03-01',
                'end_date' => '2025-09-30',
                'status' => 'on_hold',
                'purchase_date' => '2025-02-20',
                'po_number' => 'PO/UNIMAS/2025/ICT-001',
                'warranty_period' => '60 months',
                'warranty_expiry' => '2030-02-20',
            ],
            [
                'name' => 'Hospital Network Infrastructure',
                'description' => 'Pemasangan infrastruktur rangkaian untuk Hospital Umum Sarawak termasuk switch, router dan access point',
                'client_id' => Client::where('name', 'like', '%Hospital%')->first()?->id ?? 3,
                'project_value' => 320000.00,
                'start_date' => '2024-10-01',
                'end_date' => '2025-01-31',
                'status' => 'completed',
                'purchase_date' => '2024-09-15',
                'po_number' => 'PO/HUS/2024/NET-005',
                'warranty_period' => '36 months',
                'warranty_expiry' => '2027-09-15',
            ],
            [
                'name' => 'Sarawak Energy Office Automation',
                'description' => 'Projek automasi pejabat termasuk laptop, printer dan software license untuk kakitangan',
                'client_id' => Client::where('name', 'like', '%Sarawak Energy%')->first()?->id ?? 4,
                'project_value' => 520000.00,
                'start_date' => '2025-04-01',
                'end_date' => '2025-08-31',
                'status' => 'on_hold',
                'purchase_date' => '2025-03-15',
                'po_number' => 'PO/SEB/2025/OA-001',
                'warranty_period' => '24 months',
                'warranty_expiry' => '2027-03-15',
            ],
        ];

        foreach ($projects as $projectData) {
            Project::firstOrCreate(
                ['name' => $projectData['name']],
                $projectData
            );
        }

        $this->command->info('Projects created successfully!');

        // Now create Assets for each project
        $this->createAssetsForProjects();
    }

    private function createAssetsForProjects(): void
    {
        // Project 1: ICT Infrastructure Upgrade 2025
        $project1 = Project::where('name', 'ICT Infrastructure Upgrade 2025')->first();
        if ($project1) {
            $assets1 = [
                // Servers
                ['category_id' => 3, 'asset_tag' => 'SRV-PMK-001', 'brand_id' => 1, 'model' => 'PowerEdge R750', 'serial_number' => 'DELL-SRV-2025-001', 'status' => 'active', 'unit_price' => 45000.00, 'location_id' => 2, 'vendor_id' => 1, 'specs' => ['cpu' => 'Intel Xeon Gold 6330', 'ram' => '128GB DDR4', 'storage' => '4x 1.92TB SSD']],
                ['category_id' => 3, 'asset_tag' => 'SRV-PMK-002', 'brand_id' => 1, 'model' => 'PowerEdge R750', 'serial_number' => 'DELL-SRV-2025-002', 'status' => 'active', 'unit_price' => 45000.00, 'location_id' => 2, 'vendor_id' => 1, 'specs' => ['cpu' => 'Intel Xeon Gold 6330', 'ram' => '128GB DDR4', 'storage' => '4x 1.92TB SSD']],
                // Network Switches
                ['category_id' => 4, 'asset_tag' => 'NSW-PMK-001', 'brand_id' => 8, 'model' => 'Catalyst 9300-48P', 'serial_number' => 'CISCO-SW-2025-001', 'status' => 'active', 'unit_price' => 18000.00, 'location_id' => 2, 'vendor_id' => 4, 'specs' => ['ports' => '48', 'poe' => 'Yes', 'speed' => '1Gbps']],
                ['category_id' => 4, 'asset_tag' => 'NSW-PMK-002', 'brand_id' => 8, 'model' => 'Catalyst 9300-48P', 'serial_number' => 'CISCO-SW-2025-002', 'status' => 'active', 'unit_price' => 18000.00, 'location_id' => 2, 'vendor_id' => 4, 'specs' => ['ports' => '48', 'poe' => 'Yes', 'speed' => '1Gbps']],
                // UPS
                ['category_id' => 9, 'asset_tag' => 'UPS-PMK-001', 'brand_id' => 16, 'model' => 'Smart-UPS SRT 10kVA', 'serial_number' => 'APC-UPS-2025-001', 'status' => 'active', 'unit_price' => 35000.00, 'location_id' => 2, 'vendor_id' => 1, 'specs' => ['capacity' => '10kVA', 'runtime' => '30 min', 'type' => 'Online Double Conversion']],
                // Workstations
                ['category_id' => 1, 'asset_tag' => 'PC-PMK-001', 'brand_id' => 1, 'model' => 'OptiPlex 7010', 'serial_number' => 'DELL-PC-2025-001', 'status' => 'active', 'unit_price' => 4500.00, 'location_id' => 3, 'vendor_id' => 1, 'specs' => ['cpu' => 'Intel Core i7-13700', 'ram' => '16GB DDR5', 'storage' => '512GB NVMe']],
                ['category_id' => 1, 'asset_tag' => 'PC-PMK-002', 'brand_id' => 1, 'model' => 'OptiPlex 7010', 'serial_number' => 'DELL-PC-2025-002', 'status' => 'active', 'unit_price' => 4500.00, 'location_id' => 3, 'vendor_id' => 1, 'specs' => ['cpu' => 'Intel Core i7-13700', 'ram' => '16GB DDR5', 'storage' => '512GB NVMe']],
                ['category_id' => 1, 'asset_tag' => 'PC-PMK-003', 'brand_id' => 1, 'model' => 'OptiPlex 7010', 'serial_number' => 'DELL-PC-2025-003', 'status' => 'active', 'unit_price' => 4500.00, 'location_id' => 4, 'vendor_id' => 1, 'specs' => ['cpu' => 'Intel Core i7-13700', 'ram' => '16GB DDR5', 'storage' => '512GB NVMe']],
            ];

            foreach ($assets1 as $asset) {
                Asset::firstOrCreate(
                    ['asset_tag' => $asset['asset_tag']],
                    array_merge($asset, ['project_id' => $project1->id])
                );
            }
        }

        // Project 2: Computer Lab Renovation
        $project2 = Project::where('name', 'Computer Lab Renovation - Block A')->first();
        if ($project2) {
            $assets2 = [];
            // 40 PCs for computer lab
            for ($i = 1; $i <= 20; $i++) {
                $assets2[] = [
                    'category_id' => 1,
                    'asset_tag' => sprintf('PC-LAB-A-%03d', $i),
                    'brand_id' => 2, // HP
                    'model' => 'ProDesk 400 G9',
                    'serial_number' => sprintf('HP-LAB-2025-%03d', $i),
                    'status' => 'active',
                    'unit_price' => 3800.00,
                    'location_id' => 3,
                    'vendor_id' => 2,
                    'specs' => ['cpu' => 'Intel Core i5-12500', 'ram' => '8GB DDR4', 'storage' => '256GB SSD'],
                ];
            }
            // Monitors
            for ($i = 1; $i <= 20; $i++) {
                $assets2[] = [
                    'category_id' => 7,
                    'asset_tag' => sprintf('MON-LAB-A-%03d', $i),
                    'brand_id' => 2, // HP
                    'model' => 'P24h G5 FHD',
                    'serial_number' => sprintf('HP-MON-2025-%03d', $i),
                    'status' => 'active',
                    'unit_price' => 850.00,
                    'location_id' => 3,
                    'vendor_id' => 2,
                    'specs' => ['size' => '24 inch', 'resolution' => '1920x1080', 'panel' => 'IPS'],
                ];
            }
            // Network Switch for lab
            $assets2[] = [
                'category_id' => 4,
                'asset_tag' => 'NSW-LAB-A-001',
                'brand_id' => 9, // TP-Link
                'model' => 'TL-SG3452P',
                'serial_number' => 'TPL-SW-2025-001',
                'status' => 'active',
                'unit_price' => 4500.00,
                'location_id' => 3,
                'vendor_id' => 4,
                'specs' => ['ports' => '48', 'poe' => 'Yes', 'speed' => '1Gbps'],
            ];

            foreach ($assets2 as $asset) {
                Asset::firstOrCreate(
                    ['asset_tag' => $asset['asset_tag']],
                    array_merge($asset, ['project_id' => $project2->id])
                );
            }
        }

        // Project 4: Hospital Network Infrastructure (Completed)
        $project4 = Project::where('name', 'Hospital Network Infrastructure')->first();
        if ($project4) {
            $assets4 = [
                // Core Switches
                ['category_id' => 4, 'asset_tag' => 'NSW-HUS-001', 'brand_id' => 8, 'model' => 'Catalyst 9500-48Y4C', 'serial_number' => 'CISCO-HUS-2024-001', 'status' => 'active', 'unit_price' => 65000.00, 'location_id' => 2, 'vendor_id' => 4, 'specs' => ['ports' => '48', 'speed' => '25Gbps', 'type' => 'Core Switch']],
                ['category_id' => 4, 'asset_tag' => 'NSW-HUS-002', 'brand_id' => 8, 'model' => 'Catalyst 9500-48Y4C', 'serial_number' => 'CISCO-HUS-2024-002', 'status' => 'active', 'unit_price' => 65000.00, 'location_id' => 2, 'vendor_id' => 4, 'specs' => ['ports' => '48', 'speed' => '25Gbps', 'type' => 'Core Switch']],
                // Access Switches
                ['category_id' => 4, 'asset_tag' => 'NSW-HUS-003', 'brand_id' => 8, 'model' => 'Catalyst 9200-48P', 'serial_number' => 'CISCO-HUS-2024-003', 'status' => 'active', 'unit_price' => 12000.00, 'location_id' => 3, 'vendor_id' => 4, 'specs' => ['ports' => '48', 'poe' => 'Yes', 'type' => 'Access Switch']],
                ['category_id' => 4, 'asset_tag' => 'NSW-HUS-004', 'brand_id' => 8, 'model' => 'Catalyst 9200-48P', 'serial_number' => 'CISCO-HUS-2024-004', 'status' => 'active', 'unit_price' => 12000.00, 'location_id' => 4, 'vendor_id' => 4, 'specs' => ['ports' => '48', 'poe' => 'Yes', 'type' => 'Access Switch']],
                // Routers
                ['category_id' => 5, 'asset_tag' => 'RTR-HUS-001', 'brand_id' => 8, 'model' => 'ISR 4451-X', 'serial_number' => 'CISCO-RTR-2024-001', 'status' => 'active', 'unit_price' => 28000.00, 'location_id' => 2, 'vendor_id' => 4, 'specs' => ['throughput' => '2Gbps', 'wan_ports' => '4', 'type' => 'Enterprise Router']],
            ];

            foreach ($assets4 as $asset) {
                Asset::firstOrCreate(
                    ['asset_tag' => $asset['asset_tag']],
                    array_merge($asset, ['project_id' => $project4->id])
                );
            }
        }

        // Project 5: Sarawak Energy Office Automation
        $project5 = Project::where('name', 'Sarawak Energy Office Automation')->first();
        if ($project5) {
            $assets5 = [
                // Laptops
                ['category_id' => 2, 'asset_tag' => 'LT-SEB-001', 'brand_id' => 3, 'model' => 'ThinkPad X1 Carbon Gen 11', 'serial_number' => 'LNV-LT-2025-001', 'status' => 'active', 'unit_price' => 8500.00, 'location_id' => 1, 'vendor_id' => 2, 'assigned_to' => 'Ahmad Razak', 'department' => 'Management', 'specs' => ['cpu' => 'Intel Core i7-1365U', 'ram' => '16GB', 'storage' => '512GB SSD']],
                ['category_id' => 2, 'asset_tag' => 'LT-SEB-002', 'brand_id' => 3, 'model' => 'ThinkPad X1 Carbon Gen 11', 'serial_number' => 'LNV-LT-2025-002', 'status' => 'active', 'unit_price' => 8500.00, 'location_id' => 1, 'vendor_id' => 2, 'assigned_to' => 'Siti Aminah', 'department' => 'Finance', 'specs' => ['cpu' => 'Intel Core i7-1365U', 'ram' => '16GB', 'storage' => '512GB SSD']],
                ['category_id' => 2, 'asset_tag' => 'LT-SEB-003', 'brand_id' => 3, 'model' => 'ThinkPad T14s Gen 4', 'serial_number' => 'LNV-LT-2025-003', 'status' => 'active', 'unit_price' => 6200.00, 'location_id' => 1, 'vendor_id' => 2, 'assigned_to' => 'Mohd Faizal', 'department' => 'IT', 'specs' => ['cpu' => 'Intel Core i5-1345U', 'ram' => '16GB', 'storage' => '256GB SSD']],
                ['category_id' => 2, 'asset_tag' => 'LT-SEB-004', 'brand_id' => 3, 'model' => 'ThinkPad T14s Gen 4', 'serial_number' => 'LNV-LT-2025-004', 'status' => 'active', 'unit_price' => 6200.00, 'location_id' => 1, 'vendor_id' => 2, 'assigned_to' => 'Nurul Huda', 'department' => 'HR', 'specs' => ['cpu' => 'Intel Core i5-1345U', 'ram' => '16GB', 'storage' => '256GB SSD']],
                ['category_id' => 2, 'asset_tag' => 'LT-SEB-005', 'brand_id' => 3, 'model' => 'ThinkPad T14s Gen 4', 'serial_number' => 'LNV-LT-2025-005', 'status' => 'active', 'unit_price' => 6200.00, 'location_id' => 10, 'vendor_id' => 2, 'assigned_to' => 'Lee Wei Ming', 'department' => 'Operations', 'specs' => ['cpu' => 'Intel Core i5-1345U', 'ram' => '16GB', 'storage' => '256GB SSD']],
                // Printers
                ['category_id' => 8, 'asset_tag' => 'PRT-SEB-001', 'brand_id' => 14, 'model' => 'WorkForce Pro WF-C5890', 'serial_number' => 'EPS-PRT-2025-001', 'status' => 'active', 'unit_price' => 3200.00, 'location_id' => 1, 'vendor_id' => 5, 'specs' => ['type' => 'Inkjet MFP', 'color' => 'Yes', 'duplex' => 'Yes']],
                ['category_id' => 8, 'asset_tag' => 'PRT-SEB-002', 'brand_id' => 15, 'model' => 'imageCLASS MF753Cdw', 'serial_number' => 'CAN-PRT-2025-001', 'status' => 'active', 'unit_price' => 4500.00, 'location_id' => 10, 'vendor_id' => 5, 'specs' => ['type' => 'Laser MFP', 'color' => 'Yes', 'duplex' => 'Yes']],
                // Software Licenses
                ['category_id' => 6, 'asset_tag' => 'SWL-SEB-001', 'brand_id' => 7, 'model' => 'Microsoft 365 Business Premium', 'serial_number' => 'MS365-SEB-2025-001', 'status' => 'active', 'unit_price' => 15000.00, 'location_id' => 1, 'vendor_id' => 2, 'specs' => ['license_type' => 'Subscription', 'users' => '50', 'validity' => '1 Year']],
            ];

            foreach ($assets5 as $asset) {
                Asset::firstOrCreate(
                    ['asset_tag' => $asset['asset_tag']],
                    array_merge($asset, ['project_id' => $project5->id])
                );
            }
        }

        $this->command->info('Assets created successfully!');
    }
}
