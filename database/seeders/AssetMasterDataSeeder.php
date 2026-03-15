<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Location;
use App\Models\Vendor;
use Illuminate\Database\Seeder;

class AssetMasterDataSeeder extends Seeder
{
    public function run(): void
    {
        // Brands
        $brands = [
            ['name' => 'Dell', 'is_active' => true],
            ['name' => 'HP', 'is_active' => true],
            ['name' => 'Lenovo', 'is_active' => true],
            ['name' => 'Asus', 'is_active' => true],
            ['name' => 'Acer', 'is_active' => true],
            ['name' => 'Apple', 'is_active' => true],
            ['name' => 'Microsoft', 'is_active' => true],
            ['name' => 'Cisco', 'is_active' => true],
            ['name' => 'TP-Link', 'is_active' => true],
            ['name' => 'D-Link', 'is_active' => true],
            ['name' => 'Samsung', 'is_active' => true],
            ['name' => 'LG', 'is_active' => true],
            ['name' => 'BenQ', 'is_active' => true],
            ['name' => 'Epson', 'is_active' => true],
            ['name' => 'Canon', 'is_active' => true],
            ['name' => 'APC', 'is_active' => true],
        ];

        foreach ($brands as $brand) {
            Brand::updateOrCreate(['name' => $brand['name']], $brand);
        }

        // Locations
        $locations = [
            ['name' => 'Head Office', 'type' => 'building', 'is_active' => true],
            ['name' => 'Server Room', 'type' => 'room', 'is_active' => true],
            ['name' => 'IT Department', 'type' => 'room', 'is_active' => true],
            ['name' => 'Finance Department', 'type' => 'room', 'is_active' => true],
            ['name' => 'HR Department', 'type' => 'room', 'is_active' => true],
            ['name' => 'Operations', 'type' => 'room', 'is_active' => true],
            ['name' => 'Meeting Room A', 'type' => 'room', 'is_active' => true],
            ['name' => 'Meeting Room B', 'type' => 'room', 'is_active' => true],
            ['name' => 'Warehouse', 'type' => 'building', 'is_active' => true],
            ['name' => 'Branch Office', 'type' => 'building', 'is_active' => true],
        ];

        foreach ($locations as $location) {
            Location::updateOrCreate(['name' => $location['name']], $location);
        }

        // Vendors
        $vendors = [
            ['name' => 'Tech Solutions Sdn Bhd', 'contact_person' => 'Ahmad', 'phone' => '03-12345678', 'email' => 'sales@techsolutions.com', 'is_active' => true],
            ['name' => 'IT World Enterprise', 'contact_person' => 'Lim', 'phone' => '03-87654321', 'email' => 'info@itworld.com', 'is_active' => true],
            ['name' => 'Computer Zone', 'contact_person' => 'Raj', 'phone' => '03-11223344', 'email' => 'sales@computerzone.com', 'is_active' => true],
            ['name' => 'Network Pro', 'contact_person' => 'Siti', 'phone' => '03-55667788', 'email' => 'support@networkpro.com', 'is_active' => true],
            ['name' => 'Office Supplies Co', 'contact_person' => 'Tan', 'phone' => '03-99887766', 'email' => 'order@officesupplies.com', 'is_active' => true],
        ];

        foreach ($vendors as $vendor) {
            Vendor::updateOrCreate(['name' => $vendor['name']], $vendor);
        }
    }
}
