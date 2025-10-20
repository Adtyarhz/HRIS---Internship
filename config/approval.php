<?php

return [

    // Models monitored by approval workflow (class names)
    'models' => [
        \App\Models\Announcement::class,
        \App\Models\CareerHistory::class,
        \App\Models\CareerProjection::class,
        \App\Models\Certification::class,
        \App\Models\CertificationMaterial::class,
        \App\Models\Division::class,
        \App\Models\EducationHistory::class,
        \App\Models\Employee::class,
        \App\Models\FamilyDependent::class,
        \App\Models\HealthRecord::class,
        \App\Models\Insurance::class,
        \App\Models\KpiIndicator::class,
        \App\Models\KpiPeriod::class,
        \App\Models\KpiTemplate::class,
        \App\Models\KpiTemplateItem::class,
        \App\Models\KpiScoringRule::class,
        \App\Models\Position::class,
        \App\Models\TrainingHistory::class,
        \App\Models\TrainingMaterial::class,
        \App\Models\WorkExperience::class,
        // add more as needed
    ],

    // How many days before a pending request auto-expires (TTL)
    'ttl_days' => 7,

    // Whether to auto apply the change when approved. If false, approver must trigger apply.
    'auto_apply' => true,

    // notification channels: 'database', 'mail'
    'notification_channels' => ['database', 'mail'],

    // Approver identification rule (closure or array)
    // default identifies approver by position name on related employee model
    'approver_position_name' => 'HC & GA Manager',
];
