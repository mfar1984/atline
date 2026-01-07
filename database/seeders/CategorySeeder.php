<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'PC/Desktop',
                'code' => 'PC',
                'fields_config' => [
                    ['name' => 'cpu', 'label' => 'Processor (CPU)', 'type' => 'text', 'required' => false],
                    ['name' => 'ram', 'label' => 'RAM', 'type' => 'select', 'options' => ['4GB', '8GB', '16GB', '32GB', '64GB'], 'required' => false],
                    ['name' => 'storage', 'label' => 'Storage', 'type' => 'select', 'options' => ['128GB SSD', '256GB SSD', '512GB SSD', '1TB SSD', '1TB HDD', '2TB HDD'], 'required' => false],
                    ['name' => 'os', 'label' => 'Operating System', 'type' => 'text', 'required' => false],
                    ['name' => 'hostname', 'label' => 'Hostname', 'type' => 'text', 'required' => false],
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Laptop',
                'code' => 'LT',
                'fields_config' => [
                    ['name' => 'cpu', 'label' => 'Processor (CPU)', 'type' => 'text', 'required' => false],
                    ['name' => 'ram', 'label' => 'RAM', 'type' => 'select', 'options' => ['4GB', '8GB', '16GB', '32GB', '64GB'], 'required' => false],
                    ['name' => 'storage', 'label' => 'Storage', 'type' => 'select', 'options' => ['128GB SSD', '256GB SSD', '512GB SSD', '1TB SSD'], 'required' => false],
                    ['name' => 'os', 'label' => 'Operating System', 'type' => 'text', 'required' => false],
                    ['name' => 'hostname', 'label' => 'Hostname', 'type' => 'text', 'required' => false],
                    ['name' => 'screen_size', 'label' => 'Screen Size', 'type' => 'select', 'options' => ['13"', '14"', '15.6"', '17"'], 'required' => false],
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Server',
                'code' => 'SRV',
                'fields_config' => [
                    ['name' => 'cpu', 'label' => 'Processor (CPU)', 'type' => 'text', 'required' => false],
                    ['name' => 'ram', 'label' => 'RAM', 'type' => 'select', 'options' => ['16GB', '32GB', '64GB', '128GB', '256GB', '512GB'], 'required' => false],
                    ['name' => 'storage', 'label' => 'Storage', 'type' => 'text', 'required' => false],
                    ['name' => 'os', 'label' => 'Operating System', 'type' => 'text', 'required' => false],
                    ['name' => 'hostname', 'label' => 'Hostname', 'type' => 'text', 'required' => false],
                    ['name' => 'ip_address', 'label' => 'IP Address', 'type' => 'text', 'required' => false],
                    ['name' => 'rack_location', 'label' => 'Rack Location', 'type' => 'text', 'required' => false],
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Network Switch',
                'code' => 'NSW',
                'fields_config' => [
                    ['name' => 'port_count', 'label' => 'Port Count', 'type' => 'select', 'options' => ['8', '16', '24', '48'], 'required' => false],
                    ['name' => 'speed', 'label' => 'Speed', 'type' => 'select', 'options' => ['100Mbps', '1Gbps', '10Gbps'], 'required' => false],
                    ['name' => 'switch_type', 'label' => 'Type', 'type' => 'select', 'options' => ['Managed', 'Unmanaged'], 'required' => false],
                    ['name' => 'ip_address', 'label' => 'IP Address', 'type' => 'text', 'required' => false],
                    ['name' => 'firmware', 'label' => 'Firmware Version', 'type' => 'text', 'required' => false],
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Router',
                'code' => 'RTR',
                'fields_config' => [
                    ['name' => 'wan_ports', 'label' => 'WAN Ports', 'type' => 'number', 'required' => false],
                    ['name' => 'lan_ports', 'label' => 'LAN Ports', 'type' => 'number', 'required' => false],
                    ['name' => 'wifi_support', 'label' => 'WiFi Support', 'type' => 'select', 'options' => ['Yes', 'No'], 'required' => false],
                    ['name' => 'ip_address', 'label' => 'IP Address', 'type' => 'text', 'required' => false],
                    ['name' => 'firmware', 'label' => 'Firmware Version', 'type' => 'text', 'required' => false],
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Software License',
                'code' => 'SWL',
                'fields_config' => [
                    ['name' => 'software_name', 'label' => 'Software Name', 'type' => 'text', 'required' => true],
                    ['name' => 'license_key', 'label' => 'License Key', 'type' => 'text', 'required' => false],
                    ['name' => 'license_type', 'label' => 'License Type', 'type' => 'select', 'options' => ['Perpetual', 'Subscription', 'OEM', 'Volume'], 'required' => false],
                    ['name' => 'license_start', 'label' => 'License Start Date', 'type' => 'date', 'required' => false],
                    ['name' => 'license_end', 'label' => 'License End Date', 'type' => 'date', 'required' => false],
                    ['name' => 'seats', 'label' => 'Number of Seats', 'type' => 'number', 'required' => false],
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Monitor',
                'code' => 'MON',
                'fields_config' => [
                    ['name' => 'screen_size', 'label' => 'Screen Size', 'type' => 'select', 'options' => ['19"', '21"', '24"', '27"', '32"', '34"'], 'required' => false],
                    ['name' => 'resolution', 'label' => 'Resolution', 'type' => 'select', 'options' => ['1080p FHD', '1440p QHD', '4K UHD'], 'required' => false],
                    ['name' => 'panel_type', 'label' => 'Panel Type', 'type' => 'select', 'options' => ['IPS', 'VA', 'TN', 'OLED'], 'required' => false],
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Printer',
                'code' => 'PRT',
                'fields_config' => [
                    ['name' => 'printer_type', 'label' => 'Printer Type', 'type' => 'select', 'options' => ['Laser', 'Inkjet', 'Dot Matrix', 'Thermal'], 'required' => false],
                    ['name' => 'color_support', 'label' => 'Color Support', 'type' => 'select', 'options' => ['Color', 'Monochrome'], 'required' => false],
                    ['name' => 'connectivity', 'label' => 'Connectivity', 'type' => 'select', 'options' => ['USB', 'Network', 'WiFi', 'USB + Network'], 'required' => false],
                    ['name' => 'ip_address', 'label' => 'IP Address', 'type' => 'text', 'required' => false],
                ],
                'is_active' => true,
            ],
            [
                'name' => 'UPS',
                'code' => 'UPS',
                'fields_config' => [
                    ['name' => 'capacity', 'label' => 'Capacity (VA)', 'type' => 'select', 'options' => ['650VA', '1000VA', '1500VA', '2000VA', '3000VA'], 'required' => false],
                    ['name' => 'battery_count', 'label' => 'Battery Count', 'type' => 'number', 'required' => false],
                    ['name' => 'runtime', 'label' => 'Runtime (minutes)', 'type' => 'number', 'required' => false],
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Other',
                'code' => 'OTH',
                'fields_config' => [],
                'is_active' => true,
            ],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(
                ['code' => $category['code']],
                $category
            );
        }
    }
}
