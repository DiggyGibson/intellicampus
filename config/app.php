<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Application Name
    |--------------------------------------------------------------------------
    |
    | This value is the name of your application, which will be used when the
    | framework needs to place the application's name in a notification or
    | other UI elements where an application name needs to be displayed.
    |
    */

    'name' => env('APP_NAME', 'IntelliCampus'),

    /*
    |--------------------------------------------------------------------------
    | Application Environment
    |--------------------------------------------------------------------------
    |
    | This value determines the "environment" your application is currently
    | running in. This may determine how you prefer to configure various
    | services the application utilizes. Set this in your ".env" file.
    |
    */

    'env' => env('APP_ENV', 'production'),

    /*
    |--------------------------------------------------------------------------
    | Application Debug Mode
    |--------------------------------------------------------------------------
    |
    | When your application is in debug mode, detailed error messages with
    | stack traces will be shown on every error that occurs within your
    | application. If disabled, a simple generic error page is shown.
    |
    */

    'debug' => (bool) env('APP_DEBUG', false),

    /*
    |--------------------------------------------------------------------------
    | Application URL
    |--------------------------------------------------------------------------
    |
    | This URL is used by the console to properly generate URLs when using
    | the Artisan command line tool. You should set this to the root of
    | the application so that it's available within Artisan commands.
    |
    */

    'url' => env('APP_URL', 'http://localhost'),

    'frontend_url' => env('FRONTEND_URL', 'http://localhost:3000'),

    /*
    |--------------------------------------------------------------------------
    | Application Timezone
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default timezone for your application, which
    | will be used by the PHP date and date-time functions. The timezone
    | is set to "UTC" by default as it is suitable for most use cases.
    |
    */

    'timezone' => env('APP_TIMEZONE', 'UTC'),

    /*
    |--------------------------------------------------------------------------
    | Application Locale Configuration
    |--------------------------------------------------------------------------
    |
    | The application locale determines the default locale that will be used
    | by Laravel's translation / localization methods. This option can be
    | set to any locale for which you plan to have translation strings.
    |
    */

    'locale' => env('APP_LOCALE', 'en'),

    'fallback_locale' => env('APP_FALLBACK_LOCALE', 'en'),

    'faker_locale' => env('APP_FAKER_LOCALE', 'en_US'),

    /*
    |--------------------------------------------------------------------------
    | Encryption Key
    |--------------------------------------------------------------------------
    |
    | This key is utilized by Laravel's encryption services and should be set
    | to a random, 32 character string to ensure that all encrypted values
    | are secure. You should do this prior to deploying the application.
    |
    */

    'cipher' => 'AES-256-CBC',

    'key' => env('APP_KEY'),

    'previous_keys' => [
        ...array_filter(
            explode(',', (string) env('APP_PREVIOUS_KEYS', ''))
        ),
    ],

    /*
    |--------------------------------------------------------------------------
    | Maintenance Mode Driver
    |--------------------------------------------------------------------------
    |
    | These configuration options determine the driver used to determine and
    | manage Laravel's "maintenance mode" status. The "cache" driver will
    | allow maintenance mode to be controlled across multiple machines.
    |
    | Supported drivers: "file", "cache"
    |
    */

    'maintenance' => [
        'driver' => env('APP_MAINTENANCE_DRIVER', 'file'),
        'store' => env('APP_MAINTENANCE_STORE', 'database'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Service Provider Registration
    |--------------------------------------------------------------------------
    |
    | Here you may register all of the service providers for your application.
    | Laravel 12 uses a new approach for service provider registration.
    |
    */

    'providers' => \Illuminate\Support\ServiceProvider::defaultProviders()->merge([
        /*
         * Application Service Providers...
         */
        App\Providers\AppServiceProvider::class,
        App\Providers\AuthServiceProvider::class,
        // App\Providers\BroadcastServiceProvider::class,
        App\Providers\EventServiceProvider::class,
        App\Providers\RouteServiceProvider::class,
        
        /*
         * Custom Service Providers for Scope Management
         */
        // App\Providers\ScopeServiceProvider::class,
        // App\Providers\OrganizationalServiceProvider::class,
    ])->toArray(),

    /*
    |--------------------------------------------------------------------------
    | Class Aliases
    |--------------------------------------------------------------------------
    |
    | This array of class aliases will be registered when this application
    | is started. You may add your own aliases here.
    |
    */

    'aliases' => \Illuminate\Support\Facades\Facade::defaultAliases()->merge([
        // 'ScopeService' => App\Facades\ScopeService::class,
    ])->toArray(),

    /*
    |--------------------------------------------------------------------------
    | IntelliCampus Settings
    |--------------------------------------------------------------------------
    |
    | Application-specific settings for the IntelliCampus University 
    | Management System. These control various aspects of the system.
    |
    */

    'intellicampus' => [
        'version' => '1.0.0',
        
        'institution' => [
            'name' => env('INSTITUTION_NAME', 'IntelliCampus University'),
            'short_name' => env('INSTITUTION_SHORT_NAME', 'ICU'),
            'domain' => env('INSTITUTION_DOMAIN', 'intellicampus.edu'),
            'timezone' => env('INSTITUTION_TIMEZONE', 'America/New_York'),
            'logo' => env('INSTITUTION_LOGO', 'images/logo.png'),
            'address' => env('INSTITUTION_ADDRESS', '123 University Ave'),
            'city' => env('INSTITUTION_CITY', 'Education City'),
            'state' => env('INSTITUTION_STATE', 'ST'),
            'zip' => env('INSTITUTION_ZIP', '12345'),
            'country' => env('INSTITUTION_COUNTRY', 'USA'),
            'phone' => env('INSTITUTION_PHONE', '(555) 123-4567'),
            'email' => env('INSTITUTION_EMAIL', 'info@intellicampus.edu'),
        ],
        
        'modules' => [
            // Core modules (always enabled)
            'authentication' => ['enabled' => true],
            'users' => ['enabled' => true],
            'admissions' => ['enabled' => true],
            'students' => ['enabled' => true],
            'faculty' => ['enabled' => true],
            'courses' => ['enabled' => true],
            'registration' => ['enabled' => true],
            'grades' => ['enabled' => true],
            'transcripts' => ['enabled' => true],
            'financial' => ['enabled' => true],
            'academic_planning' => ['enabled' => true],
            
            // Advanced modules (can be disabled)
            'lms' => ['enabled' => env('MODULE_LMS_ENABLED', true)],
            'attendance' => ['enabled' => env('MODULE_ATTENDANCE_ENABLED', true)],
            'scheduling' => ['enabled' => env('MODULE_SCHEDULING_ENABLED', true)],
            'examinations' => ['enabled' => env('MODULE_EXAMS_ENABLED', true)],
            'degree_audit' => ['enabled' => env('MODULE_DEGREE_AUDIT_ENABLED', true)],
            'advising' => ['enabled' => env('MODULE_ADVISING_ENABLED', true)],
            'communications' => ['enabled' => env('MODULE_COMMUNICATIONS_ENABLED', true)],
            
            // Optional modules (disabled by default)
            'housing' => ['enabled' => env('MODULE_HOUSING_ENABLED', false)],
            'library' => ['enabled' => env('MODULE_LIBRARY_ENABLED', false)],
            'alumni' => ['enabled' => env('MODULE_ALUMNI_ENABLED', false)],
            'healthcare' => ['enabled' => env('MODULE_HEALTHCARE_ENABLED', false)],
            'athletics' => ['enabled' => env('MODULE_ATHLETICS_ENABLED', false)],
            'transportation' => ['enabled' => env('MODULE_TRANSPORTATION_ENABLED', false)],
            'cafeteria' => ['enabled' => env('MODULE_CAFETERIA_ENABLED', false)],
            'research' => ['enabled' => env('MODULE_RESEARCH_ENABLED', false)],
        ],
        
        'academic' => [
            'grading_scale' => env('GRADING_SCALE', 'standard'), // standard, plus_minus, custom
            'credit_system' => env('CREDIT_SYSTEM', 'semester'), // semester, quarter, trimester
            'max_credits_per_term' => env('MAX_CREDITS_PER_TERM', 21),
            'min_credits_per_term' => env('MIN_CREDITS_PER_TERM', 12),
            'full_time_credits' => env('FULL_TIME_CREDITS', 12),
            'gpa_scale' => env('GPA_SCALE', 4.0),
            'academic_year_start' => env('ACADEMIC_YEAR_START', 'September'),
            'allow_grade_changes' => env('ALLOW_GRADE_CHANGES', true),
            'grade_change_deadline_days' => env('GRADE_CHANGE_DEADLINE_DAYS', 30),
        ],
        
        'registration' => [
            'allow_waitlist' => env('ALLOW_WAITLIST', true),
            'allow_overrides' => env('ALLOW_OVERRIDES', true),
            'shopping_cart_timeout' => env('SHOPPING_CART_TIMEOUT', 20), // minutes
            'max_waitlist_per_student' => env('MAX_WAITLIST_PER_STUDENT', 5),
            'registration_priority_groups' => env('REGISTRATION_PRIORITY_GROUPS', true),
            'allow_cross_registration' => env('ALLOW_CROSS_REGISTRATION', false),
        ],
        
        'financial' => [
            'currency' => env('CURRENCY', 'USD'),
            'currency_symbol' => env('CURRENCY_SYMBOL', '$'),
            'payment_gateway' => env('PAYMENT_GATEWAY', 'stripe'),
            'allow_payment_plans' => env('ALLOW_PAYMENT_PLANS', true),
            'late_fee_percentage' => env('LATE_FEE_PERCENTAGE', 1.5),
            'payment_deadline_days' => env('PAYMENT_DEADLINE_DAYS', 30),
            'allow_partial_payments' => env('ALLOW_PARTIAL_PAYMENTS', true),
            'minimum_payment_amount' => env('MINIMUM_PAYMENT_AMOUNT', 100),
        ],
        
        'admissions' => [
            'application_fee' => env('APPLICATION_FEE', 50),
            'allow_multiple_applications' => env('ALLOW_MULTIPLE_APPLICATIONS', false),
            'require_entrance_exam' => env('REQUIRE_ENTRANCE_EXAM', true),
            'allow_transfer_students' => env('ALLOW_TRANSFER_STUDENTS', true),
            'international_students' => env('ALLOW_INTERNATIONAL_STUDENTS', true),
            'application_deadline_days' => env('APPLICATION_DEADLINE_DAYS', 60),
            'document_upload_max_size' => env('DOCUMENT_UPLOAD_MAX_SIZE', 5), // MB
        ],
        
        'security' => [
            'password_expiry_days' => env('PASSWORD_EXPIRY_DAYS', 90),
            'max_login_attempts' => env('MAX_LOGIN_ATTEMPTS', 5),
            'lockout_duration' => env('LOCKOUT_DURATION', 30), // minutes
            'session_lifetime' => env('SESSION_LIFETIME', 120), // minutes
            'two_factor_auth' => env('TWO_FACTOR_AUTH', false),
            'ip_restriction' => env('IP_RESTRICTION', false),
        ],
        
        'notifications' => [
            'email_enabled' => env('EMAIL_NOTIFICATIONS', true),
            'sms_enabled' => env('SMS_NOTIFICATIONS', false),
            'push_enabled' => env('PUSH_NOTIFICATIONS', false),
            'digest_frequency' => env('DIGEST_FREQUENCY', 'daily'), // daily, weekly, never
        ],
    ],
];