<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Permission Labels (Actions)
    |--------------------------------------------------------------------------
    */
    'labels' => [
        'view' => 'View',
        'create' => 'Create',
        'update' => 'Update',
        'delete' => 'Delete',
        'export' => 'Export',
        'assign' => 'Assign',
    ],

    /*
    |--------------------------------------------------------------------------
    | Module Labels
    |--------------------------------------------------------------------------
    */
    'modules' => [
        // Overview
        'overview' => 'Overview',
        
        // Internal
        'internal_credentials' => 'Internal > Credentials',
        'internal_download' => 'Internal > Download',
        'internal_employee' => 'Internal > Employee',
        'internal_inventory_assets' => 'Internal > Inventory > Assets',
        'internal_inventory_movements' => 'Internal > Inventory > Movements',
        'internal_inventory_checkout' => 'Internal > Inventory > Checkout',
        'internal_inventory_locations' => 'Internal > Inventory > Locations',
        'internal_inventory_brands' => 'Internal > Inventory > Brands',
        'internal_inventory_categories' => 'Internal > Inventory > Categories',
        
        // External
        'external_projects' => 'External > Projects',
        'external_inventory' => 'External > Inventory',
        'external_reports' => 'External > Reports',
        'external_attachments' => 'External > Attachments',
        'external_settings_client' => 'External > Settings > Client',
        'external_settings_vendor' => 'External > Settings > Vendor',
        'external_settings_location' => 'External > Settings > Location',
        'external_settings_brand' => 'External > Settings > Brand',
        'external_settings_category' => 'External > Settings > Category',
        
        // Helpdesk
        'helpdesk_tickets' => 'Helpdesk > Tickets',
        'helpdesk_templates' => 'Helpdesk > Templates',
        'helpdesk_priorities' => 'Helpdesk > Priorities',
        'helpdesk_categories' => 'Helpdesk > Categories',
        'helpdesk_statuses' => 'Helpdesk > Statuses',
        'helpdesk_reports' => 'Helpdesk > Reports',
        
        // System Settings
        'settings_configuration' => 'System Settings > Configuration',
        'settings_roles' => 'System Settings > Group Roles',
        'settings_users' => 'System Settings > User Management',
        'settings_integrations_recycle_bin' => 'System Settings > Integrations > Recycle Bin',
        'settings_integrations_email' => 'System Settings > Integrations > Email',
        'settings_integrations_payment' => 'System Settings > Integrations > Payment Gateway',
        'settings_integrations_storage' => 'System Settings > Integrations > Storage',
        'settings_integrations_weather' => 'System Settings > Integrations > Weather',
        'settings_integrations_webhook' => 'System Settings > Integrations > Webhook',
        'settings_activity_logs' => 'System Settings > Activity Logs',
    ],

    /*
    |--------------------------------------------------------------------------
    | Permission Matrix
    |--------------------------------------------------------------------------
    | Define which permissions are available for each module
    | Set to true to enable, false to disable
    */
    'matrix' => [
        // Overview - Dashboard hanya view sahaja, tiada export button
        'overview' => [
            'view' => true,
            'create' => false,
            'update' => false,
            'delete' => false,
            'export' => false,
        ],
        
        // Internal
        'internal_credentials' => [
            'view' => true,
            'create' => true,
            'update' => true,
            'delete' => true,
            'export' => false,
        ],
        'internal_download' => [
            'view' => true,
            'create' => true,
            'update' => false,
            'delete' => true,
            'export' => false,
        ],
        'internal_employee' => [
            'view' => true,
            'create' => true,
            'update' => true,
            'delete' => true,
            'export' => false, // Tiada butang export dalam UI
        ],
        'internal_inventory_assets' => [
            'view' => true,
            'create' => true,
            'update' => true,
            'delete' => true,
            'export' => false, // Tiada butang export dalam UI
        ],
        'internal_inventory_movements' => [
            'view' => true,
            'create' => false, // View only - tiada create button
            'update' => false, // View only - tiada update button
            'delete' => false, // View only - tiada delete button
            'export' => false, // Tiada butang export dalam UI
        ],
        'internal_inventory_checkout' => [
            'view' => true,
            'create' => true, // Checkout form
            'update' => false, // Tiada update - hanya checkin
            'delete' => false, // Tiada delete button
            'export' => false, // Tiada butang export dalam UI
        ],
        'internal_inventory_locations' => [
            'view' => true,
            'create' => true,
            'update' => true,
            'delete' => true,
            'export' => false,
        ],
        'internal_inventory_brands' => [
            'view' => true,
            'create' => true,
            'update' => true,
            'delete' => true,
            'export' => false,
        ],
        'internal_inventory_categories' => [
            'view' => true,
            'create' => true,
            'update' => true,
            'delete' => true,
            'export' => false,
        ],
        
        // External
        'external_projects' => [
            'view' => true,
            'create' => true,
            'update' => true,
            'delete' => true,
            'export' => false, // Tiada butang export dalam UI
        ],
        'external_inventory' => [
            'view' => true,
            'create' => true,
            'update' => true,
            'delete' => true,
            'export' => false, // Tiada butang export dalam UI
        ],
        'external_reports' => [
            'view' => true,
            'create' => false, // Reports adalah view only
            'update' => false,
            'delete' => false, // Tiada delete button
            'export' => true, // Print button wujud
        ],
        'external_attachments' => [
            'view' => true,
            'create' => false, // Attachments dicipta melalui Project/Asset, bukan direct
            'update' => false, // Tiada update button
            'delete' => true,
            'export' => false, // Tiada butang export dalam UI
        ],
        'external_settings_client' => [
            'view' => true,
            'create' => true,
            'update' => true,
            'delete' => true,
            'export' => false, // Tiada butang export dalam UI
        ],
        'external_settings_vendor' => [
            'view' => true,
            'create' => true,
            'update' => true,
            'delete' => true,
            'export' => false, // Tiada butang export dalam UI
        ],
        'external_settings_location' => [
            'view' => true,
            'create' => true,
            'update' => true,
            'delete' => true,
            'export' => false, // Tiada butang export dalam UI
        ],
        'external_settings_brand' => [
            'view' => true,
            'create' => true,
            'update' => true,
            'delete' => true,
            'export' => false, // Tiada butang export dalam UI
        ],
        'external_settings_category' => [
            'view' => true,
            'create' => true,
            'update' => true,
            'delete' => true,
            'export' => false, // Tiada butang export dalam UI
        ],
        
        // Helpdesk
        'helpdesk_tickets' => [
            'view' => true,
            'create' => true,
            'update' => true,
            'delete' => true,
            'export' => false, // Tiada butang export dalam UI
            'assign' => true,
        ],
        'helpdesk_templates' => [
            'view' => true,
            'create' => true,
            'update' => true,
            'delete' => true,
            'export' => false,
        ],
        'helpdesk_priorities' => [
            'view' => true,
            'create' => true,
            'update' => true,
            'delete' => true,
            'export' => false,
        ],
        'helpdesk_categories' => [
            'view' => true,
            'create' => true,
            'update' => true,
            'delete' => true,
            'export' => false,
        ],
        'helpdesk_statuses' => [
            'view' => true,
            'create' => true,
            'update' => true,
            'delete' => true,
            'export' => false,
        ],
        'helpdesk_reports' => [
            'view' => true,
            'create' => false,
            'update' => false,
            'delete' => false,
            'export' => true, // Print button wujud
        ],
        
        // System Settings
        'settings_configuration' => [
            'view' => true,
            'create' => false,
            'update' => true,
            'delete' => false,
            'export' => false,
        ],
        'settings_roles' => [
            'view' => true,
            'create' => true,
            'update' => true,
            'delete' => true,
            'export' => false, // Tiada butang export dalam UI
        ],
        'settings_users' => [
            'view' => true,
            'create' => true,
            'update' => true,
            'delete' => true,
            'export' => false, // Tiada butang export dalam UI
        ],
        'settings_integrations_recycle_bin' => [
            'view' => true,
            'create' => false,
            'update' => true, // For restore
            'delete' => true, // For permanent delete
            'export' => false,
        ],
        'settings_integrations_email' => [
            'view' => true,
            'create' => false,
            'update' => true,
            'delete' => false,
            'export' => false,
        ],
        'settings_integrations_payment' => [
            'view' => true,
            'create' => false,
            'update' => true,
            'delete' => false,
            'export' => false,
        ],
        'settings_integrations_storage' => [
            'view' => true,
            'create' => false,
            'update' => true,
            'delete' => false,
            'export' => false,
        ],
        'settings_integrations_weather' => [
            'view' => true,
            'create' => false,
            'update' => true,
            'delete' => false,
            'export' => false,
        ],
        'settings_integrations_webhook' => [
            'view' => true,
            'create' => false,
            'update' => true,
            'delete' => false,
            'export' => false,
        ],
        'settings_activity_logs' => [
            'view' => true,
            'create' => false,
            'update' => false,
            'delete' => false,
            'export' => false, // Tiada butang export dalam UI
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Route to Module Mapping
    |--------------------------------------------------------------------------
    | Maps route prefixes to permission module keys
    */
    'route_mapping' => [
        // Dashboard
        'dashboard' => 'overview',
        
        // Internal
        'internal.credentials' => 'internal_credentials',
        'internal.download' => 'internal_download',
        'internal.employee' => 'internal_employee',
        'internal.inventory' => 'internal_inventory', // Will be further mapped by tab
        
        // External
        'external.projects' => 'external_projects',
        'external.inventory' => 'external_inventory',
        'external.reports' => 'external_reports',
        'external.attachments' => 'external_attachments',
        'external.settings.clients' => 'external_settings_client',
        'external.settings.vendors' => 'external_settings_vendor',
        'external.settings.locations' => 'external_settings_location',
        'external.settings.brands' => 'external_settings_brand',
        'external.settings.categories' => 'external_settings_category',
        'external.settings' => 'external_settings_client', // Default for settings index
        
        // Helpdesk
        'helpdesk' => 'helpdesk', // Will be further mapped by tab
        
        // System Settings
        'settings.configuration' => 'settings_configuration',
        'settings.roles' => 'settings_roles',
        'settings.users' => 'settings_users',
        'settings.integrations' => 'settings_integrations', // Will be further mapped by tab
        'settings.activity-logs' => 'settings_activity_logs',
    ],

    /*
    |--------------------------------------------------------------------------
    | Tab to Sub-Module Mapping
    |--------------------------------------------------------------------------
    | Maps tab parameters to specific sub-module permissions
    | for multi-tab modules
    */
    'tab_mapping' => [
        'internal.inventory' => [
            'assets' => 'internal_inventory_assets',
            'movements' => 'internal_inventory_movements',
            'checkout' => 'internal_inventory_checkout',
            'locations' => 'internal_inventory_locations',
            'brands' => 'internal_inventory_brands',
            'categories' => 'internal_inventory_categories',
        ],
        'helpdesk' => [
            'tickets' => 'helpdesk_tickets',
            'templates' => 'helpdesk_templates',
            'priorities' => 'helpdesk_priorities',
            'categories' => 'helpdesk_categories',
            'statuses' => 'helpdesk_statuses',
            'reports' => 'helpdesk_reports',
        ],
        'settings.integrations' => [
            'recycle-bin' => 'settings_integrations_recycle_bin',
            'email' => 'settings_integrations_email',
            'payment' => 'settings_integrations_payment',
            'storage' => 'settings_integrations_storage',
            'weather' => 'settings_integrations_weather',
            'webhooks' => 'settings_integrations_webhook',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Action to Permission Mapping
    |--------------------------------------------------------------------------
    | Maps route action names to permission action keys
    */
    'action_mapping' => [
        'index' => 'view',
        'show' => 'view',
        'create' => 'create',
        'store' => 'create',
        'edit' => 'update',
        'update' => 'update',
        'destroy' => 'delete',
        'export' => 'export',
        'assign' => 'assign',
        'file' => 'view', // For download file
        'download' => 'view', // For download
        'progress' => 'view', // For progress check
    ],
];
