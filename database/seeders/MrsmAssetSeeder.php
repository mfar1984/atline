<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Location;
use App\Models\Client;
use App\Models\Vendor;
use App\Models\Project;
use Illuminate\Database\Seeder;

class MrsmAssetSeeder extends Seeder
{
    public function run(): void
    {
        // Get client and vendor
        $client = Client::where('name', 'LIKE', '%MRSM GERIK%')->first();
        $vendor = Vendor::where('name', 'LIKE', '%ATLINE%')->first();

        if (!$client || !$vendor) {
            $this->command->error('Client MRSM GERIK or Vendor ATLINE not found!');
            return;
        }

        $this->command->info("Client: {$client->name} (ID: {$client->id})");
        $this->command->info("Vendor: {$vendor->name} (ID: {$vendor->id})");

        // Create or get project
        $project = Project::firstOrCreate(
            ['client_id' => $client->id, 'name' => 'MRSM GERIK - Network Infrastructure'],
            [
                'description' => 'Projek infrastruktur rangkaian untuk MRSM GERIK',
                'status' => 'active',
                'start_date' => '2025-02-09',
                'warranty_expiry' => '2029-02-09',
            ]
        );

        $this->command->info("Project: {$project->name} (ID: {$project->id})");

        // CSV mapping:
        // NAMA MODEL = model
        // JENIS ALAT = notes (description/jenis)
        // NO. SIRI = serial_number
        // HARGA (RM) = unit_price
        // CATATAN = location (nama lokasi)
        // PENGGUNA = assigned_to
        
        // Data format: [model, jenis_alat, serial_number, unit_price, lokasi, brand, category]
        $assets = [
            // Server & UPS
            ['DELL POWEREDGE R760', 'SERVER VIRTUAL', '7SNCZC4', 48000.00, 'Bilik Server', 'Dell', 'Server'],
            ['Eaton 9PX UPS', 'UPS-5kVA', 'GN55T43001', 10570.00, 'Bilik Server', 'Eaton', 'UPS'],
            
            // Laptops
            ['Dell Inspiron 16 5640', 'Laptop', '6M1GPC4', 5500.00, 'Bilik Server', 'Dell', 'Laptop'],
            ['Dell Inspiron 16 5640', 'Laptop', '7M1GPC4', 5500.00, 'Bilik Server', 'Dell', 'Laptop'],
            ['Dell Inspiron 16 5640', 'Laptop', '5M1GPC4', 5500.00, 'Bilik Server', 'Dell', 'Laptop'],
            
            // Network Switches - ICX 7150
            ['Ruckus ICX 7150-C12P', 'User Access Switch 12 Ports PoE+', 'FEK3807W066', 5300.00, 'Surau', 'Ruckus', 'Network Switch'],
            ['Ruckus ICX 7150-C12P', 'User Access Switch 12 Ports PoE+', 'FEK3807W0Y3', 5300.00, 'Pondok Guard', 'Ruckus', 'Network Switch'],
            
            // Network Switches - ICX8200-24P (PoE)
            ['Ruckus ICX8200-24P', 'User Access Switch 24 Ports PoE+', 'FND5037W1CZ', 9300.00, 'Pusat Pelajar', 'Ruckus', 'Network Switch'],
            ['Ruckus ICX8200-24P', 'User Access Switch 24 Ports PoE+', 'FND5037W1D3', 9300.00, 'Jabatan Math', 'Ruckus', 'Network Switch'],
            ['Ruckus ICX8200-24P', 'User Access Switch 24 Ports PoE+', 'FND5037W10A', 9300.00, 'Blok KH', 'Ruckus', 'Network Switch'],
            
            // Network Switches - ICX8200-48P (PoE)
            ['Ruckus ICX8200-48P', 'User Access Switch 48 Ports PoE+', 'FNG5036W0HX', 13000.00, 'Dewan Besar', 'Ruckus', 'Network Switch'],
            
            // Network Switches - ICX8200-24 (Non PoE)
            ['Ruckus ICX8200-24', 'User Access Switch 24 Ports (Non POE)', 'FNC5038W0HT', 6500.00, 'Makmal Komputer', 'Ruckus', 'Network Switch'],
            
            // Network Switches - ICX 8200-48 (Non PoE)
            ['RUCKUS ICX 8200-48', 'User Access Switch 48 Ports Non POE', 'FNF5045W0CG', 9500.00, 'Pejabat', 'Ruckus', 'Network Switch'],
            ['RUCKUS ICX 8200-48', 'User Access Switch 48 Ports Non POE', 'FNF5045W04X', 9500.00, 'Pejabat', 'Ruckus', 'Network Switch'],
            ['RUCKUS ICX 8200-48', 'User Access Switch 48 Ports Non POE', 'FNF5045W0KV', 9500.00, 'Dewan Besar', 'Ruckus', 'Network Switch'],
            ['RUCKUS ICX 8200-48', 'User Access Switch 48 Ports Non POE', 'FNF5045W0BV', 9500.00, 'Jabatan Matematik', 'Ruckus', 'Network Switch'],
            
            // SFP Transceivers - 10GBASE-LR
            ['RUCKUS 10GBASE-LR SFPP SMF', 'Singlemode SFP Transceiver 10GB LR', 'ADV1250600001N5', 3700.00, 'Bilik Server', 'Ruckus', 'SFP Transceiver'],
            ['RUCKUS 10GBASE-LR SFPP SMF', 'Singlemode SFP Transceiver 10GB LR', 'ADV1250600001M3', 3700.00, 'Bilik Server', 'Ruckus', 'SFP Transceiver'],
            ['RUCKUS 10GBASE-LR SFPP SMF', 'Singlemode SFP Transceiver 10GB LR', 'ADV1250600001LW', 3700.00, 'Bilik Server', 'Ruckus', 'SFP Transceiver'],
            ['RUCKUS 10GBASE-LR SFPP SMF', 'Singlemode SFP Transceiver 10GB LR', 'ADV1250600001ME', 3700.00, 'Bilik Server', 'Ruckus', 'SFP Transceiver'],
            ['RUCKUS 10GBASE-LR SFPP SMF', 'Singlemode SFP Transceiver 10GB LR', 'ADV1250600001NB', 3700.00, 'Bilik Server', 'Ruckus', 'SFP Transceiver'],
            ['RUCKUS 10GBASE-LR SFPP SMF', 'Singlemode SFP Transceiver 10GB LR', 'ADV1250600001LZ', 3700.00, 'Bilik Server', 'Ruckus', 'SFP Transceiver'],
            ['RUCKUS 10GBASE-LR SFPP SMF', 'Singlemode SFP Transceiver 10GB LR', 'ADV1250600001MD', 3700.00, 'Bilik Server', 'Ruckus', 'SFP Transceiver'],
            ['RUCKUS 10GBASE-LR SFPP SMF', 'Singlemode SFP Transceiver 10GB LR', 'ADV1250600001LV', 3700.00, 'Bilik Server', 'Ruckus', 'SFP Transceiver'],
            ['RUCKUS 10GBASE-LR SFPP SMF', 'Singlemode SFP Transceiver 10GB LR', 'ADV1250600001MC', 3700.00, 'Pejabat Matematik', 'Ruckus', 'SFP Transceiver'],
            ['RUCKUS 10GBASE-LR SFPP SMF', 'Singlemode SFP Transceiver 10GB LR', 'ADV1250600001M2', 3700.00, 'Makmal Komputer', 'Ruckus', 'SFP Transceiver'],
            ['RUCKUS 10GBASE-LR SFPP SMF', 'Singlemode SFP Transceiver 10GB LR', 'ADV1250600001M0', 3700.00, 'Pejabat', 'Ruckus', 'SFP Transceiver'],
            ['RUCKUS 10GBASE-LR SFPP SMF', 'Singlemode SFP Transceiver 10GB LR', 'ADV1250600001ND', 3700.00, 'Blok KH', 'Ruckus', 'SFP Transceiver'],
            ['RUCKUS 10GBASE-LR SFPP SMF', 'Singlemode SFP Transceiver 10GB LR', 'ADV1250600001MA', 3700.00, 'Pusat Pelajar', 'Ruckus', 'SFP Transceiver'],
            ['RUCKUS 10GBASE-LR SFPP SMF', 'Singlemode SFP Transceiver 10GB LR', 'ADV1250600001LY', 3700.00, 'Dewan Besar', 'Ruckus', 'SFP Transceiver'],
            ['RUCKUS 10GBASE-LR SFPP SMF', 'Singlemode SFP Transceiver 10GB LR', 'ADV1250600001MB', 3700.00, 'Surau', 'Ruckus', 'SFP Transceiver'],
            ['RUCKUS 10GBASE-LR SFPP SMF', 'Singlemode SFP Transceiver 10GB LR', 'ADV1250600001NC', 3700.00, 'Guard House', 'Ruckus', 'SFP Transceiver'],
            
            // DAC Cables
            ['RUCKUS 10GbE Direct Attach SFP+', '10G SFP+ to 10G SFP+ DAC Cable', 'PAMF2526R017MG5', 500.00, 'Bilik Server', 'Ruckus', 'DAC Cable'],
            ['RUCKUS 10GbE Direct Attach SFP+', '10G SFP+ to 10G SFP+ DAC Cable', 'PAMF2526R017ME1', 500.00, 'Pejabat', 'Ruckus', 'DAC Cable'],
            ['RUCKUS 10GbE Direct Attach SFP+', '10G SFP+ to 10G SFP+ DAC Cable', 'PAMF2526R017MWA', 500.00, 'Pejabat', 'Ruckus', 'DAC Cable'],
            ['RUCKUS 10GbE Direct Attach SFP+', '10G SFP+ to 10G SFP+ DAC Cable', 'PAMF2526R019750', 500.00, 'Pejabat', 'Ruckus', 'DAC Cable'],
            ['RUCKUS 10GbE Direct Attach SFP+', '10G SFP+ to 10G SFP+ DAC Cable', 'PAMF2526R0197DH', 500.00, 'Jabatan Matematik', 'Ruckus', 'DAC Cable'],
            ['RUCKUS 10GbE Direct Attach SFP+', '10G SFP+ to 10G SFP+ DAC Cable', 'PAMF2526R017M9W', 500.00, 'Jabatan Matematik', 'Ruckus', 'DAC Cable'],
            
            // Wireless AP - T350 (Outdoor)
            ['RUCKUS T350', 'Wireless AP Outdoor', '262522000011', 6000.00, 'Depan Kaunter Pejabat Luar', 'Ruckus', 'Wireless AP'],
            ['RUCKUS T350', 'Wireless AP Outdoor', '262522000050', 6000.00, 'Makmal Komputer Bitara', 'Ruckus', 'Wireless AP'],
            ['RUCKUS T350', 'Wireless AP Outdoor', '262522000536', 6000.00, 'Pusat Pelajar', 'Ruckus', 'Wireless AP'],
            
            // Wireless AP - R350 (Indoor)
            ['RUCKUS R350', 'Wireless AP Indoor', '342573005424', 2100.00, 'Bilik Kaunseling', 'Ruckus', 'Wireless AP'],
            ['RUCKUS R350', 'Wireless AP Indoor', '342573005531', 2100.00, 'Makmal Kimia 1', 'Ruckus', 'Wireless AP'],
            ['RUCKUS R350', 'Wireless AP Indoor', '342573005608', 2100.00, 'Makmal Kimia 2', 'Ruckus', 'Wireless AP'],
            ['RUCKUS R350', 'Wireless AP Indoor', '342573005552', 2100.00, 'Makmal Sains 1', 'Ruckus', 'Wireless AP'],
            ['RUCKUS R350', 'Wireless AP Indoor', '342573005711', 2100.00, 'Makmal Sains 2', 'Ruckus', 'Wireless AP'],
            ['RUCKUS R350', 'Wireless AP Indoor', '342573005446', 2100.00, 'Kelas Wing Kanan Bawah 2', 'Ruckus', 'Wireless AP'],
            ['RUCKUS R350', 'Wireless AP Indoor', '342573005482', 2100.00, 'Bilik Jabatan Matematik', 'Ruckus', 'Wireless AP'],
            ['RUCKUS R350', 'Wireless AP Indoor', '342573005512', 2100.00, 'Kelas Wing Kanan Bawah 1', 'Ruckus', 'Wireless AP'],
            ['RUCKUS R350', 'Wireless AP Indoor', '342573005475', 2100.00, 'Makmal Komputer Bitara', 'Ruckus', 'Wireless AP'],
            ['RUCKUS R350', 'Wireless AP Indoor', '422322010691', 2100.00, 'Blok Akademik Wing Kanan Makmal Komputer 36', 'Ruckus', 'Wireless AP'],
            ['RUCKUS R350', 'Wireless AP Indoor', '342573005393', 2100.00, 'Blok Akademik Wing Kanan Kelas 35', 'Ruckus', 'Wireless AP'],
            ['RUCKUS R350', 'Wireless AP Indoor', '342573004245', 2100.00, 'Blok Akademik Wing Kanan Kelas 34', 'Ruckus', 'Wireless AP'],
            ['RUCKUS R350', 'Wireless AP Indoor', '342573004934', 2100.00, 'Blok Akademik Wing Kanan Kelas 33', 'Ruckus', 'Wireless AP'],
            ['RUCKUS R350', 'Wireless AP Indoor', '342573005641', 2100.00, 'Blok Akademik Wing Kanan Kelas 32', 'Ruckus', 'Wireless AP'],
            ['RUCKUS R350', 'Wireless AP Indoor', '422322013434', 2100.00, 'Blok Akademik Wing Kanan Pusat Sumber 38', 'Ruckus', 'Wireless AP'],
            ['RUCKUS R350', 'Wireless AP Indoor', '422322012800', 2100.00, 'Blok Akademik Wing Kanan Pusat Sumber 37', 'Ruckus', 'Wireless AP'],
            ['RUCKUS R350', 'Wireless AP Indoor', '342573005673', 2100.00, 'Kelas Wing Kanan Atas', 'Ruckus', 'Wireless AP'],
            ['RUCKUS R350', 'Wireless AP Indoor', '342573005572', 2100.00, 'Blok Akademik Wing Kiri Pejabat 26', 'Ruckus', 'Wireless AP'],
            ['RUCKUS R350', 'Wireless AP Indoor', '342573005468', 2100.00, 'Blok Akademik Wing Kiri Pejabat 25', 'Ruckus', 'Wireless AP'],
            ['RUCKUS R350', 'Wireless AP Indoor', '342573005814', 2100.00, 'Blok Akademik Wing Kiri Makmal 28', 'Ruckus', 'Wireless AP'],
            ['RUCKUS R350', 'Wireless AP Indoor', '342573005747', 2100.00, 'Blok Akademik Wing Kiri Makmal 27', 'Ruckus', 'Wireless AP'],
            ['RUCKUS R350', 'Wireless AP Indoor', '342573005398', 2100.00, 'Blok Akademik Kelas 15', 'Ruckus', 'Wireless AP'],
            ['RUCKUS R350', 'Wireless AP Indoor', '342573005509', 2100.00, 'Blok Akademik Kelas 16', 'Ruckus', 'Wireless AP'],
            ['RUCKUS R350', 'Wireless AP Indoor', '342573005186', 2100.00, 'Blok Akademik Wing Kiri Kelas 30', 'Ruckus', 'Wireless AP'],
            ['RUCKUS R350', 'Wireless AP Indoor', '342573005631', 2100.00, 'Blok Akademik Wing Kiri Kelas 29', 'Ruckus', 'Wireless AP'],
            ['RUCKUS R350', 'Wireless AP Indoor', '342573005847', 2100.00, 'Blok Akademik Wing Kiri Kelas 23', 'Ruckus', 'Wireless AP'],
            ['RUCKUS R350', 'Wireless AP Indoor', '342573005486', 2100.00, 'Blok Akademik Wing Kiri Kelas 22', 'Ruckus', 'Wireless AP'],
            ['RUCKUS R350', 'Wireless AP Indoor', '342573005678', 2100.00, 'Blok Akademik Wing Kiri Kelas 21', 'Ruckus', 'Wireless AP'],
            ['RUCKUS R350', 'Wireless AP Indoor', '342573005454', 2100.00, 'Blok Akademik Kelas 12', 'Ruckus', 'Wireless AP'],
            ['RUCKUS R350', 'Wireless AP Indoor', '342573005748', 2100.00, 'Blok Akademik Kelas 13', 'Ruckus', 'Wireless AP'],
            ['RUCKUS R350', 'Wireless AP Indoor', '342573005626', 2100.00, 'Blok Akademik Kelas 10', 'Ruckus', 'Wireless AP'],
            ['RUCKUS R350', 'Wireless AP Indoor', '342573005392', 2100.00, 'Blok Akademik Wing Kiri Bilik Guru 24', 'Ruckus', 'Wireless AP'],
            ['RUCKUS R350', 'Wireless AP Indoor', '422322012854', 2100.00, 'Blok Akademik Wing Kiri Bilik Guru 31', 'Ruckus', 'Wireless AP'],
            ['RUCKUS R350', 'Wireless AP Indoor', '342573005612', 2100.00, 'Kelas Tingkatan 4 - 44', 'Ruckus', 'Wireless AP'],
            ['RUCKUS R350', 'Wireless AP Indoor', '342573005503', 2100.00, 'Kelas Tingkatan 4 - 43', 'Ruckus', 'Wireless AP'],
            ['RUCKUS R350', 'Wireless AP Indoor', '342573005825', 2100.00, 'Kelas Tingkatan 4 - 41', 'Ruckus', 'Wireless AP'],
            ['RUCKUS R350', 'Wireless AP Indoor', '342573005536', 2100.00, 'Kelas Tingkatan 4 - 42', 'Ruckus', 'Wireless AP'],
            ['RUCKUS R350', 'Wireless AP Indoor', '342573005632', 2100.00, 'Bengkel RBT', 'Ruckus', 'Wireless AP'],
            ['RUCKUS R350', 'Wireless AP Indoor', '342573005464', 2100.00, 'Bengkel RBT Atas 2', 'Ruckus', 'Wireless AP'],
            ['RUCKUS R350', 'Wireless AP Indoor', '342573005358', 2100.00, 'Bengkel RBT Atas 1', 'Ruckus', 'Wireless AP'],
            ['RUCKUS R350', 'Wireless AP Indoor', '342573005465', 2100.00, 'Blok KH Kelas 2 Bawah', 'Ruckus', 'Wireless AP'],
            ['RUCKUS R350', 'Wireless AP Indoor', '342573005832', 2100.00, 'Blok Akademik Wing Kiri Kelas 18', 'Ruckus', 'Wireless AP'],
            ['RUCKUS R350', 'Wireless AP Indoor', '342573005189', 2100.00, 'Blok Akademik Wing Kiri Makmal 20', 'Ruckus', 'Wireless AP'],
            ['RUCKUS R350', 'Wireless AP Indoor', '342573004707', 2100.00, 'Blok Akademik Wing Kiri Makmal 19', 'Ruckus', 'Wireless AP'],
            ['RUCKUS R350', 'Wireless AP Indoor', '342573005569', 2100.00, 'Blok Akademik Bilik Mesyuarat', 'Ruckus', 'Wireless AP'],
            ['RUCKUS R350', 'Wireless AP Indoor', '342573005827', 2100.00, 'Blok Akademik Kelas 11', 'Ruckus', 'Wireless AP'],
            
            // Wireless AP - R750 (Indoor High Performance)
            ['RUCKUS R750', 'Wireless AP Indoor High Performance', '342573005472', 3800.00, 'Blok Akademik Kelas 9', 'Ruckus', 'Wireless AP'],
            ['RUCKUS R750', 'Wireless AP Indoor High Performance', '422322013462', 3800.00, 'Dewan Selera 39', 'Ruckus', 'Wireless AP'],
            ['RUCKUS R750', 'Wireless AP Indoor High Performance', '422322010551', 3800.00, 'Dewan Selera 40', 'Ruckus', 'Wireless AP'],
            ['RUCKUS R750', 'Wireless AP Indoor High Performance', '342573005654', 3800.00, 'Bilik Seminar Dewan Makan Atas', 'Ruckus', 'Wireless AP'],
            ['RUCKUS R750', 'Wireless AP Indoor High Performance', '342573005462', 3800.00, 'Surau', 'Ruckus', 'Wireless AP'],
            
            // Wireless AP - R750 (Dewan)
            ['RUCKUS R750', 'Wireless AP Dewan', '342573005670', 3800.00, 'Pondok Guard', 'Ruckus', 'Wireless AP'],
            ['RUCKUS R750', 'Wireless AP Dewan', '422322012769', 3800.00, 'Luar Bilik ICT', 'Ruckus', 'Wireless AP'],
            ['RUCKUS R750', 'Wireless AP Dewan', '422322012673', 3800.00, 'Dewan Besar 1', 'Ruckus', 'Wireless AP'],
            ['RUCKUS R750', 'Wireless AP Dewan', '422322012985', 3800.00, 'Dewan Besar 2', 'Ruckus', 'Wireless AP'],
            ['RUCKUS R750', 'Wireless AP Dewan', '422322012840', 3800.00, 'Dewan Besar 3', 'Ruckus', 'Wireless AP'],
            ['RUCKUS R750', 'Wireless AP Dewan', '422322012836', 3800.00, 'Surau', 'Ruckus', 'Wireless AP'],
        ];

        $counter = 0;
        foreach ($assets as $index => $data) {
            [$model, $jenis_alat, $serial_number, $unit_price, $lokasi, $brandName, $categoryName] = $data;

            // Get or create brand
            $brand = Brand::firstOrCreate(
                ['name' => $brandName],
                ['is_active' => true]
            );

            // Get or create category with code
            $categoryCode = strtoupper(str_replace(' ', '-', $categoryName));
            $category = Category::firstOrCreate(
                ['name' => $categoryName],
                ['code' => $categoryCode, 'is_active' => true]
            );

            // Get or create location
            $location = Location::firstOrCreate(
                ['name' => $lokasi],
                ['type' => 'room', 'is_active' => true]
            );

            // Generate asset tag: MRSM-0001, MRSM-0002, etc.
            $assetTag = 'MRSM-' . str_pad($index + 1, 4, '0', STR_PAD_LEFT);

            // Create asset
            Asset::create([
                'project_id' => $project->id,
                'category_id' => $category->id,
                'brand_id' => $brand->id,
                'location_id' => $location->id,
                'vendor_id' => $vendor->id,
                'asset_tag' => $assetTag,
                'model' => $model,
                'serial_number' => $serial_number,
                'unit_price' => $unit_price,
                'status' => 'active',
                'assigned_to' => 'Unit ICT',
                'department' => 'ICT',
                'notes' => $jenis_alat,
            ]);

            $counter++;
        }

        $this->command->info("Berjaya import {$counter} aset!");
    }
}
