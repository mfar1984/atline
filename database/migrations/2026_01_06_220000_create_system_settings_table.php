<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('group')->index(); // company, regional, security, notification, defaults
            $table->string('key')->index();
            $table->text('value')->nullable();
            $table->string('type')->default('string'); // string, boolean, integer, json
            $table->timestamps();
            
            $table->unique(['group', 'key']);
        });
        
        // Insert default settings
        $defaults = [
            // Company Information
            ['group' => 'company', 'key' => 'name', 'value' => 'Atline Sdn Bhd', 'type' => 'string'],
            ['group' => 'company', 'key' => 'short_name', 'value' => 'ATLINE', 'type' => 'string'],
            ['group' => 'company', 'key' => 'email', 'value' => 'info@atline.com.my', 'type' => 'string'],
            ['group' => 'company', 'key' => 'phone', 'value' => '', 'type' => 'string'],
            ['group' => 'company', 'key' => 'address', 'value' => '', 'type' => 'string'],
            ['group' => 'company', 'key' => 'website', 'value' => '', 'type' => 'string'],
            ['group' => 'company', 'key' => 'ssm_number', 'value' => '', 'type' => 'string'],
            
            // Regional Settings
            ['group' => 'regional', 'key' => 'timezone', 'value' => 'Asia/Kuala_Lumpur', 'type' => 'string'],
            ['group' => 'regional', 'key' => 'date_format', 'value' => 'd/m/Y', 'type' => 'string'],
            ['group' => 'regional', 'key' => 'time_format', 'value' => 'H:i', 'type' => 'string'],
            ['group' => 'regional', 'key' => 'currency', 'value' => 'MYR', 'type' => 'string'],
            ['group' => 'regional', 'key' => 'currency_symbol', 'value' => 'RM', 'type' => 'string'],
            ['group' => 'regional', 'key' => 'language', 'value' => 'en', 'type' => 'string'],
            
            // Security Settings
            ['group' => 'security', 'key' => 'session_timeout', 'value' => '120', 'type' => 'integer'], // minutes
            ['group' => 'security', 'key' => 'password_min_length', 'value' => '8', 'type' => 'integer'],
            ['group' => 'security', 'key' => 'require_2fa', 'value' => '0', 'type' => 'boolean'],
            ['group' => 'security', 'key' => 'max_login_attempts', 'value' => '5', 'type' => 'integer'],
            ['group' => 'security', 'key' => 'lockout_duration', 'value' => '15', 'type' => 'integer'], // minutes
            
            // Notification Settings
            ['group' => 'notification', 'key' => 'email_ticket_created', 'value' => '1', 'type' => 'boolean'],
            ['group' => 'notification', 'key' => 'email_ticket_replied', 'value' => '1', 'type' => 'boolean'],
            ['group' => 'notification', 'key' => 'email_ticket_status_changed', 'value' => '1', 'type' => 'boolean'],
            ['group' => 'notification', 'key' => 'email_ticket_assigned', 'value' => '1', 'type' => 'boolean'],
            ['group' => 'notification', 'key' => 'email_user_created', 'value' => '1', 'type' => 'boolean'],
            
            // System Defaults
            ['group' => 'defaults', 'key' => 'pagination_size', 'value' => '15', 'type' => 'integer'],
            ['group' => 'defaults', 'key' => 'ticket_auto_close_days', 'value' => '7', 'type' => 'integer'],
            ['group' => 'defaults', 'key' => 'attachment_max_size', 'value' => '10', 'type' => 'integer'], // MB
            ['group' => 'defaults', 'key' => 'allowed_file_types', 'value' => 'pdf,doc,docx,xls,xlsx,jpg,jpeg,png,gif,zip', 'type' => 'string'],
        ];
        
        $now = now();
        foreach ($defaults as &$setting) {
            $setting['created_at'] = $now;
            $setting['updated_at'] = $now;
        }
        
        DB::table('system_settings')->insert($defaults);
    }

    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};
