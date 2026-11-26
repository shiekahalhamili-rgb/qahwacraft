<?php
// Connects to MySQL. On the first visit it creates the database (if needed), the tables,
// the products and the three accounts. If products or accounts are missing later, they are added again.

function db()
{
    static $pdo = null;
    if ($pdo) {
        return $pdo;
    }
    if (DB_HOST === '') {
        exit('<h1>Database not configured</h1><p>Add DB_HOST, DB_PORT, DB_USER, DB_PASS and DB_NAME in the Render service (Environment tab).</p>');
    }
    // The database only accepts encrypted connections.
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::MYSQL_ATTR_SSL_CA => DB_SSL_CA,
        PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => true,
    ];
    $server = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';charset=utf8mb4';
    try {
        $pdo = new PDO($server . ';dbname=' . DB_NAME, DB_USER, DB_PASS, $options);
    } catch (PDOException $e) {
        // 1049 = unknown database: create it, then connect again.
        if ((int) ($e->errorInfo[1] ?? 0) !== 1049) {
            db_error($e);
        }
        try {
            $root = new PDO($server, DB_USER, DB_PASS, $options);
            $root->exec('CREATE DATABASE IF NOT EXISTS `' . DB_NAME . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
            $pdo = new PDO($server . ';dbname=' . DB_NAME, DB_USER, DB_PASS, $options);
        } catch (PDOException $e2) {
            db_error($e2);
        }
    }
    if (empty($_SESSION['db_ready'])) {
        setup_database($pdo);
        $_SESSION['db_ready'] = true;
    }
    return $pdo;
}

function db_error($e)
{
    $hint = 'Check DB_HOST, DB_PORT, DB_USER and DB_PASS in the Render service (Environment tab). They must match the TiDB Cloud connection details.';
    exit('<h1>Database connection failed</h1><p>' . $hint . '</p><p>' . htmlspecialchars($e->getMessage()) . '</p>');
}

function setup_database($pdo)
{
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(120) NOT NULL,
        email VARCHAR(190) NOT NULL UNIQUE,
        phone VARCHAR(30) NOT NULL DEFAULT '',
        password VARCHAR(255) NOT NULL,
        role VARCHAR(10) NOT NULL DEFAULT 'customer',
        points INT NOT NULL DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    $pdo->exec("CREATE TABLE IF NOT EXISTS products (
        id VARCHAR(40) PRIMARY KEY,
        type VARCHAR(20) NOT NULL,
        name VARCHAR(120) NOT NULL,
        roaster VARCHAR(20) NULL,
        origin VARCHAR(120) NULL,
        process VARCHAR(60) NULL,
        roast_level INT NULL,
        flavor VARCHAR(20) NULL,
        notes VARCHAR(255) NULL,
        price DECIMAL(10,2) NOT NULL,
        weight VARCHAR(20) NULL,
        description TEXT NOT NULL,
        icon VARCHAR(20) NULL,
        featured INT NOT NULL DEFAULT 0,
        stock INT NOT NULL DEFAULT 50
    )");
    $pdo->exec("CREATE TABLE IF NOT EXISTS orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_no VARCHAR(20) NULL UNIQUE,
        user_id INT NOT NULL,
        name VARCHAR(120) NOT NULL,
        phone VARCHAR(30) NOT NULL,
        email VARCHAR(190) NOT NULL,
        city VARCHAR(60) NOT NULL,
        address VARCHAR(200) NOT NULL,
        courier VARCHAR(20) NOT NULL,
        tracking_no VARCHAR(30) NOT NULL,
        payment VARCHAR(20) NOT NULL,
        coupon VARCHAR(20) NULL,
        subtotal DECIMAL(10,2) NOT NULL,
        discount DECIMAL(10,2) NOT NULL DEFAULT 0,
        shipping DECIMAL(10,2) NOT NULL DEFAULT 0,
        total DECIMAL(10,2) NOT NULL,
        status VARCHAR(30) NOT NULL DEFAULT 'Placed',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    $pdo->exec("CREATE TABLE IF NOT EXISTS order_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT NOT NULL,
        product_id VARCHAR(40) NULL,
        name VARCHAR(160) NOT NULL,
        details VARCHAR(200) NOT NULL,
        qty INT NOT NULL,
        price DECIMAL(10,2) NOT NULL
    )");
    $pdo->exec("CREATE TABLE IF NOT EXISTS subscriptions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        name VARCHAR(160) NOT NULL,
        details VARCHAR(200) NOT NULL,
        price DECIMAL(10,2) NOT NULL,
        next_date DATE NOT NULL,
        status VARCHAR(12) NOT NULL DEFAULT 'Active'
    )");
    $pdo->exec("CREATE TABLE IF NOT EXISTS messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(120) NOT NULL,
        email VARCHAR(190) NOT NULL,
        order_no VARCHAR(20) NULL,
        topic VARCHAR(60) NOT NULL,
        message TEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    if ($pdo->query('SELECT COUNT(*) FROM products')->fetchColumn() == 0) {
        add_products($pdo);
    }
    global $ACCOUNTS;
    foreach ($ACCOUNTS as $a) {
        $st = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $st->execute([$a[1]]);
        if (!$st->fetch()) {
            $points = $a[3] === 'admin' ? 0 : 500;
            $pdo->prepare('INSERT INTO users (name, email, password, role, points, phone) VALUES (?, ?, ?, ?, ?, ?)')
                ->execute([$a[0], $a[1], password_hash($a[2], PASSWORD_DEFAULT), $a[3], $points, '+966 55 000 0000']);
        }
    }
}

function add_products($pdo)
{
    // id, type, name, roaster, origin, process, roast level (0 light - 4 dark), flavor, notes, price, weight, featured, description, icon
    $products = [
        ['jazan-khawlani', 'beans', 'Jazan Khawlani', 'jazan', 'Saudi Arabia · Jazan', 'Natural', 1, 'fruity', 'Dried apricot, Cardamom, Wild honey', 89, '250 g', 1, 'Grown on terraced farms in the Jazan mountains and dried slowly in the sun. Sweet and lightly spiced, with a long honey finish.', null],
        ['ethiopia-yirgacheffe', 'beans', 'Ethiopia Yirgacheffe', 'najd', 'Ethiopia · Gedeo', 'Washed', 0, 'floral', 'Jasmine, Bergamot, Lemon zest', 72, '250 g', 1, 'Clean and tea-like, with a jasmine aroma that opens as it cools. Best brewed as pour-over.', null],
        ['colombia-huila', 'beans', 'Colombia Huila', 'hijaz', 'Colombia · Huila', 'Washed', 1, 'fruity', 'Strawberry, Hibiscus, Panela', 79, '250 g', 1, 'A juicy cup from the Pink Bourbon variety, with red-fruit sweetness and a soft hibiscus acidity.', null],
        ['brazil-cerrado', 'beans', 'Brazil Cerrado', 'khobar', 'Brazil · Minas Gerais', 'Pulped natural', 2, 'chocolatey', 'Milk chocolate, Hazelnut, Caramel', 55, '250 g', 1, 'Smooth and round, easy to love with or without milk. A good first bag for new home brewers.', null],
        ['kenya-nyeri', 'beans', 'Kenya Nyeri AA', 'asir', 'Kenya · Nyeri', 'Washed', 1, 'fruity', 'Blackcurrant, Grapefruit, Brown sugar', 84, '250 g', 0, 'Big, bright and syrupy, with classic Kenyan blackcurrant and a sweet brown-sugar body.', null],
        ['guatemala-huehue', 'beans', 'Guatemala Huehuetenango', 'najd', 'Guatemala · Huehuetenango', 'Washed', 2, 'chocolatey', 'Cocoa, Brown sugar, Red apple', 62, '250 g', 0, 'Balanced and comforting, with cocoa sweetness and a crisp apple note.', null],
        ['yemen-haraz', 'beans', 'Yemen Haraz', 'jazan', 'Yemen · Haraz', 'Natural', 2, 'chocolatey', 'Dark chocolate, Raisin, Clove', 120, '250 g', 0, 'Grown at 2,200 m on old terraces. Deep and winey, with dark chocolate and a warm clove finish.', null],
        ['rwanda-nyamasheke', 'beans', 'Rwanda Nyamasheke', 'hijaz', 'Rwanda · Western Province', 'Washed', 1, 'floral', 'Rose, Black tea, Red grape', 76, '250 g', 0, 'Delicate and floral, with a black-tea finish. Lovely as iced pour-over.', null],
        ['asir-espresso', 'beans', 'Asir Espresso Blend', 'asir', 'Brazil + Ethiopia blend', 'Natural / Washed', 3, 'chocolatey', 'Dark cocoa, Toffee, Orange peel', 58, '250 g', 0, 'Made for espresso and milk drinks: thick crema, cocoa and toffee, with a hint of orange.', null],
        ['panama-geisha', 'beans', 'Panama Geisha', 'najd', 'Panama · Boquete', 'Washed', 0, 'floral', 'Jasmine, White peach, Honeysuckle', 145, '100 g', 0, 'Our most special coffee. Perfumed and silky, best brewed as pour-over.', null],
        ['ethiopia-guji', 'beans', 'Ethiopia Guji Natural', 'khobar', 'Ethiopia · Guji', 'Natural', 1, 'fruity', 'Blueberry, Dark chocolate, Red wine', 74, '250 g', 0, 'Jammy blueberry sweetness over a chocolate base. A customer favourite.', null],
        ['kit-starter', 'tool', 'Pour-Over Starter Kit', null, null, null, null, null, null, 249, null, 1, 'Ceramic dripper, 100 filters, a glass server and a coffee scale: everything for your first pour-over.', 'kit'],
        ['dripper-02', 'tool', 'Ceramic Pour-Over Dripper 02', null, null, null, null, null, null, 65, null, 1, 'A cone dripper with spiral ribs for an even flow. Fits size 02 filters and brews 1–4 cups.', 'dripper'],
        ['grinder-hand', 'tool', 'Manual Burr Grinder', null, null, null, null, null, null, 295, null, 1, 'Stainless steel burrs that adjust from espresso to French press. Holds 30 g of beans.', 'grinder'],
        ['kettle-goose', 'tool', 'Gooseneck Kettle 600 ml', null, null, null, null, null, null, 329, null, 1, 'Set the exact temperature from 40 to 100 °C and pour slowly with the slim spout.', 'kettle'],
        ['grinder-electric', 'tool', 'Electric Grinder', null, null, null, null, null, null, 649, null, 0, 'Even grinding for pour-over and espresso at the touch of a button, with 40 settings.', 'egrinder'],
        ['scale-timer', 'tool', 'Coffee Scale with Timer', null, null, null, null, null, null, 119, null, 0, 'Weigh coffee and water to 0.1 g with a built-in brew timer.', 'scale'],
        ['filters-02', 'accessory', 'Paper Filters 02 · 100 pcs', null, null, null, null, null, null, 22, null, 0, 'Paper filters with no papery taste. Rinse once before brewing.', 'filters'],
        ['server-600', 'accessory', 'Glass Server 600 ml', null, null, null, null, null, null, 75, null, 0, 'Heat-resistant glass server with marks every 100 ml.', 'server'],
        ['canister', 'accessory', 'Airtight Bean Canister', null, null, null, null, null, null, 69, null, 0, 'Keeps 250 g of beans fresh with a one-way valve.', 'canister'],
        ['gift-box', 'accessory', 'Gift Box · Three Roasters', null, null, null, null, null, null, 199, null, 0, 'Three 100 g coffees from three Saudi roasters, with a brewing card and a gift message.', 'gift'],
    ];
    $st = $pdo->prepare('INSERT INTO products (id, type, name, roaster, origin, process, roast_level, flavor, notes, price, weight, featured, description, icon)
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    foreach ($products as $p) {
        $st->execute($p);
    }
}
