<?php
// Database settings come from the Render service's Environment variables,
// so the password is never stored in the code.
// The database is TiDB Cloud (MySQL-compatible) and the connection is encrypted (TLS).
define('DB_HOST', getenv('DB_HOST') ?: '');
define('DB_PORT', getenv('DB_PORT') ?: '4000');
define('DB_NAME', getenv('DB_NAME') ?: 'qahwacraft_db');
define('DB_USER', getenv('DB_USER') ?: '');
define('DB_PASS', getenv('DB_PASS') ?: '');
// Certificate list used to check the database's certificate.
define('DB_SSL_CA', getenv('DB_SSL_CA') ?: '/etc/ssl/certs/ca-certificates.crt');

// Store rules
define('SHIPPING_FEE', 25);     // SAR, one fee per order (subscription deliveries include delivery)
define('DEMO_OTP', '123456');   // demo bank verification code for mada

$COUPONS = [
    'WELCOME15' => 0.15,   // first order
    'COFFEEDAY' => 0.10,   // seasonal campaign
    'SAMMER'  => 1 ,   // lecturer discount (Sammer Aldbass)
];

// One delivery service: everything ships together in one box with one fee.
$COURIERS = [
    'standard' => ['name' => 'Standard delivery (one combined box)', 'fee' => SHIPPING_FEE, 'time' => '1–3 working days in main cities, 3–5 elsewhere'],
];

// Payment methods selected for the project (report section 11)
$PAYMENTS = [
    'mada'     => 'mada debit card',
    'applepay' => 'Apple Pay',
];

// Subscription delivery schedules: value used to work out the next date => label
$SCHEDULES = [
    '1 month'  => 'Every month',
    '2 weeks'  => 'Every 2 weeks',
    '2 months' => 'Every 2 months',
];

$GRINDS = ['whole' => 'Whole bean', 'pourover' => 'Pour-over (V60)', 'espresso' => 'Espresso', 'french' => 'French press'];

$STATUSES = ['Placed', 'Packed', 'Shipped', 'Out for delivery', 'Delivered'];

// Roasters (names are fictional): key => [name, city, label color]
$ROASTERS = [
    'najd'   => ['Najd Roastery', 'Riyadh', '#1F3D2B'],
    'hijaz'  => ['Hijaz Bean Co.', 'Jeddah', '#8C3B2C'],
    'jazan'  => ['Jazan Highlands Roasters', 'Jazan', '#B07A1E'],
    'khobar' => ['Khobar Coast Roasting', 'Al Khobar', '#2E5A62'],
    'asir'   => ['Asir Peak Coffee', 'Abha', '#5C4632'],
];

// Accounts created automatically
$ACCOUNTS = [
    ['Sammer Aldbass', 'sammer.aldbass@qahwacraft.sa', 'Sammer@2026', 'customer', 'Lecturer account'],
    ['Noura Al-Qahtani', 'demo@qahwacraft.sa', 'Demo@1234', 'customer', 'Demo customer'],
    ['Admin', 'admin@qahwacraft.sa', 'Admin@2026', 'admin', 'Admin account'],
];
