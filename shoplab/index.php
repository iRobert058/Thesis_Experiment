<?php
session_start();

// Qualtrics survey URL – change this to your actual survey link before running
define('QUALTRICS_URL', 'https://survey.uu.nl/jfe/form/SV_265oQKUgpQVtZlA');

// Data directory paths
define('DATA_DIR',     __DIR__ . '/data');
define('COUNTER_FILE', DATA_DIR . '/session_counter.txt');
define('EVENTS_FILE',  DATA_DIR . '/events.csv');

// All products in the store (1-4 are the target products, 5-52 are fillers)
$PRODUCTS = [
    1  => ['id'=>1, 'name'=>'Wireless Earbuds Pro',      'price'=>39.99, 'reviews'=>847,  'rating'=>4.4, 'category'=>'Electronics',   'color'=>'#dbeafe', 'icon'=>'🎧', 'desc'=>'High-quality wireless earbuds with active noise cancellation and 24-hour battery life. Compatible with iOS and Android.'],
    2  => ['id'=>2, 'name'=>'Water Bottle',              'price'=>24.95, 'reviews'=>312,  'rating'=>4.2, 'category'=>'Home & Kitchen', 'color'=>'#dcfce7', 'icon'=>'🍶', 'desc'=>'Eco-friendly insulated bottle, BPA-free. Keeps drinks cold 24 h and hot 12 h. 500 ml, dishwasher safe.'],
    3  => ['id'=>3, 'name'=>'Running Shoes X200',        'price'=>89.00, 'reviews'=>1203, 'rating'=>4.6, 'category'=>'Sports',         'color'=>'#fef3c7', 'icon'=>'👟', 'desc'=>'Lightweight running shoes with responsive cushioning. Breathable mesh upper, durable rubber outsole. Sizes 36–46.'],
    4  => ['id'=>4, 'name'=>'Bamboo Desk Organizer',     'price'=>32.50, 'reviews'=>564,  'rating'=>4.3, 'category'=>'Office & Study',  'color'=>'#f5f0eb', 'icon'=>'🗂',  'desc'=>'Premium bamboo desk organizer with 6 compartments. Keeps pens, papers and accessories tidy. 30 × 20 × 8 cm.'],
    5  => ['id'=>5, 'name'=>'Portable Phone Stand',      'price'=>12.99, 'reviews'=>2341, 'rating'=>4.7, 'category'=>'Electronics',   'color'=>'#f3f4f6', 'icon'=>'📱', 'desc'=>'Adjustable aluminium phone stand compatible with all smartphones and tablets. Folds flat for easy storage.'],
    6  => ['id'=>6, 'name'=>'Yoga Mat Premium',          'price'=>45.00, 'reviews'=>789,  'rating'=>4.4, 'category'=>'Sports',         'color'=>'#ede9fe', 'icon'=>'🧘', 'desc'=>'Non-slip 6 mm TPE yoga mat with alignment lines and carry strap. Suitable for all yoga styles. 183 × 61 cm.'],
    7  => ['id'=>7, 'name'=>'Manual Coffee Grinder',     'price'=>28.75, 'reviews'=>156,  'rating'=>4.1, 'category'=>'Home & Kitchen', 'color'=>'#fef9c3', 'icon'=>'☕', 'desc'=>'Hand-operated ceramic burr grinder for fresh coffee anywhere. Adjustable coarseness settings, 25 g capacity.'],
    8  => ['id'=>8, 'name'=>'Resistance Bands Set',      'price'=>19.99, 'reviews'=>1876, 'rating'=>4.6, 'category'=>'Sports',         'color'=>'#fee2e2', 'icon'=>'💪', 'desc'=>'Set of 5 latex resistance bands (2–45 kg). Includes carry bag, door anchor and illustrated exercise guide.'],
    // Office, travel and daily essentials
    9  => ['id'=>9,  'name'=>'A5 Notebook Set',           'price'=>16.99, 'reviews'=>654, 'rating'=>4.5, 'category'=>'Office & Study',      'color'=>'#fdf4ff', 'icon'=>'📓', 'desc'=>'Set of three ruled A5 notebooks with durable covers and smooth paper for meetings, classes and daily notes.'],
    10 => ['id'=>10, 'name'=>'Cable Organizer Kit',       'price'=>13.50, 'reviews'=>982, 'rating'=>4.6, 'category'=>'Office & Study',      'color'=>'#f0f9ff', 'icon'=>'🔗', 'desc'=>'Reusable cable ties, clips and labels for keeping chargers, desk cables and travel adapters organised.'],
    11 => ['id'=>11, 'name'=>'Desk Mat Large',            'price'=>15.99, 'reviews'=>741, 'rating'=>4.4, 'category'=>'Office & Study',      'color'=>'#f0fdf4', 'icon'=>'🖥', 'desc'=>'Large non-slip desk mat for keyboard, mouse and writing space. Water-resistant surface, 80 × 40 cm.'],
    12 => ['id'=>12, 'name'=>'Packing Cubes Set',         'price'=>29.99, 'reviews'=>743, 'rating'=>4.5, 'category'=>'Travel & Daily Use',  'color'=>'#fff7ed', 'icon'=>'🧳', 'desc'=>'Six-piece packing cube set with breathable mesh panels for organised suitcases, gym bags and weekend trips.'],
    13 => ['id'=>13, 'name'=>'Compact Umbrella',          'price'=>17.50, 'reviews'=>421, 'rating'=>4.3, 'category'=>'Travel & Daily Use',  'color'=>'#f7fee7', 'icon'=>'☂️', 'desc'=>'Wind-resistant compact umbrella with automatic open-close button and sleeve. Fits easily in a backpack.'],
    14 => ['id'=>14, 'name'=>'Insulated Lunch Box',       'price'=>22.99, 'reviews'=>318, 'rating'=>4.4, 'category'=>'Travel & Daily Use',  'color'=>'#fef9c3', 'icon'=>'🥪', 'desc'=>'Leak-resistant insulated lunch box with divider tray and cutlery slot. Suitable for work, school and day trips.'],
    // More filler products
    15 => ['id'=>15, 'name'=>'Bluetooth Speaker Mini',      'price'=>34.99, 'reviews'=>1298, 'rating'=>4.5, 'category'=>'Electronics',   'color'=>'#e0f2fe', 'icon'=>'🔊', 'desc'=>'Compact Bluetooth speaker with punchy sound, water-resistant housing and 12-hour playback. Includes USB-C charging cable.'],
    16 => ['id'=>16, 'name'=>'USB-C Charging Hub',          'price'=>27.95, 'reviews'=>684,  'rating'=>4.3, 'category'=>'Electronics',   'color'=>'#f1f5f9', 'icon'=>'🔌', 'desc'=>'Seven-in-one USB-C hub with HDMI, USB-A, SD card reader and fast power pass-through for laptops and tablets.'],
    17 => ['id'=>17, 'name'=>'Smart LED Desk Lamp',         'price'=>41.50, 'reviews'=>934,  'rating'=>4.4, 'category'=>'Electronics',   'color'=>'#fefce8', 'icon'=>'💡', 'desc'=>'Adjustable LED desk lamp with touch controls, dimming levels, warm/cool light modes and a built-in timer.'],
    18 => ['id'=>18, 'name'=>'Wireless Mouse Silent',       'price'=>18.99, 'reviews'=>1511, 'rating'=>4.5, 'category'=>'Electronics',   'color'=>'#e2e8f0', 'icon'=>'🖱', 'desc'=>'Quiet wireless mouse with ergonomic shape, adjustable DPI and long battery life for work or study.'],
    19 => ['id'=>19, 'name'=>'Laptop Sleeve 14 Inch',       'price'=>21.99, 'reviews'=>508,  'rating'=>4.2, 'category'=>'Electronics',   'color'=>'#fae8ff', 'icon'=>'💻', 'desc'=>'Padded laptop sleeve with soft lining, splash-resistant fabric and a slim front pocket for accessories.'],
    20 => ['id'=>20, 'name'=>'Digital Kitchen Scale',       'price'=>15.99, 'reviews'=>1124, 'rating'=>4.6, 'category'=>'Home & Kitchen', 'color'=>'#ecfccb', 'icon'=>'⚖️', 'desc'=>'Precise digital kitchen scale with tare function, stainless steel surface and easy-read display. Measures up to 5 kg.'],
    21 => ['id'=>21, 'name'=>'Ceramic Dinner Bowl Set',     'price'=>38.00, 'reviews'=>376,  'rating'=>4.4, 'category'=>'Home & Kitchen', 'color'=>'#fef3c7', 'icon'=>'🥣', 'desc'=>'Set of four ceramic dinner bowls with a modern matte finish. Microwave and dishwasher safe.'],
    22 => ['id'=>22, 'name'=>'Cotton Throw Blanket',        'price'=>36.95, 'reviews'=>642,  'rating'=>4.5, 'category'=>'Home & Kitchen', 'color'=>'#f5f0eb', 'icon'=>'🛋', 'desc'=>'Soft woven cotton throw blanket for sofa or bed. Breathable, machine washable and finished with tassels.'],
    23 => ['id'=>23, 'name'=>'Airtight Food Containers',    'price'=>26.49, 'reviews'=>1842, 'rating'=>4.6, 'category'=>'Home & Kitchen', 'color'=>'#dcfce7', 'icon'=>'🍱', 'desc'=>'Ten-piece airtight food container set with stackable lids. Suitable for meal prep, pantry storage and leftovers.'],
    24 => ['id'=>24, 'name'=>'Stainless Steel Mixing Bowls','price'=>31.99, 'reviews'=>733,  'rating'=>4.4, 'category'=>'Home & Kitchen', 'color'=>'#f8fafc', 'icon'=>'🥄', 'desc'=>'Nested stainless steel mixing bowl set with non-slip bases and measurement marks. Includes three sizes.'],
    25 => ['id'=>25, 'name'=>'Adjustable Dumbbell Pair',    'price'=>74.99, 'reviews'=>826,  'rating'=>4.3, 'category'=>'Sports',         'color'=>'#fee2e2', 'icon'=>'🏋️', 'desc'=>'Space-saving adjustable dumbbell pair for home workouts. Quick weight changes and comfortable textured grips.'],
    26 => ['id'=>26, 'name'=>'Foam Roller Pro',             'price'=>23.50, 'reviews'=>1419, 'rating'=>4.5, 'category'=>'Sports',         'color'=>'#dbeafe', 'icon'=>'🌀', 'desc'=>'Firm textured foam roller for stretching, mobility work and post-workout recovery. Lightweight and easy to store.'],
    27 => ['id'=>27, 'name'=>'Cycling Gloves',              'price'=>18.50, 'reviews'=>592,  'rating'=>4.2, 'category'=>'Sports',         'color'=>'#e0e7ff', 'icon'=>'🚴', 'desc'=>'Breathable cycling gloves with padded palms, anti-slip grip and pull tabs for quick removal.'],
    28 => ['id'=>28, 'name'=>'Quick-Dry Sports Towel',      'price'=>14.95, 'reviews'=>1022, 'rating'=>4.4, 'category'=>'Sports',         'color'=>'#ccfbf1', 'icon'=>'🏃', 'desc'=>'Lightweight microfiber sports towel that dries quickly and packs small. Includes a ventilated carry pouch.'],
    29 => ['id'=>29, 'name'=>'Fitness Jump Rope',           'price'=>16.99, 'reviews'=>1197, 'rating'=>4.5, 'category'=>'Sports',         'color'=>'#fef9c3', 'icon'=>'🤸', 'desc'=>'Adjustable speed jump rope with smooth bearings and comfortable handles for cardio training.'],
    30 => ['id'=>30, 'name'=>'Monitor Stand Riser',         'price'=>18.99, 'reviews'=>457,  'rating'=>4.3, 'category'=>'Office & Study',     'color'=>'#f0fdf4', 'icon'=>'🖥', 'desc'=>'Minimal monitor stand riser with storage space underneath for keyboard, notebooks and small desk supplies.'],
    31 => ['id'=>31, 'name'=>'Reusable To-Go Cutlery',      'price'=>24.50, 'reviews'=>899,  'rating'=>4.6, 'category'=>'Travel & Daily Use', 'color'=>'#fff7ed', 'icon'=>'🍴', 'desc'=>'Reusable stainless steel cutlery set in a slim carry case for lunch breaks, travel and picnics.'],
    32 => ['id'=>32, 'name'=>'Sticky Notes Bundle',         'price'=>21.95, 'reviews'=>312,  'rating'=>4.2, 'category'=>'Office & Study',     'color'=>'#eff6ff', 'icon'=>'📝', 'desc'=>'Assorted sticky notes and page flags for planning, studying and marking documents. Includes six colours.'],
    33 => ['id'=>33, 'name'=>'Travel Toiletry Bag',         'price'=>17.75, 'reviews'=>664,  'rating'=>4.4, 'category'=>'Travel & Daily Use', 'color'=>'#fdf4ff', 'icon'=>'🧼', 'desc'=>'Hanging toiletry bag with clear compartments, water-resistant lining and sturdy hook for bathrooms or luggage.'],
    34 => ['id'=>34, 'name'=>'Fine Tip Pen Set',            'price'=>19.99, 'reviews'=>528,  'rating'=>4.1, 'category'=>'Office & Study',     'color'=>'#fefce8', 'icon'=>'✏️', 'desc'=>'Set of twelve fine tip pens for notes, calendars and colour coding. Smooth ink with minimal bleed-through.'],
    35 => ['id'=>35, 'name'=>'Travel Pillow Memory Foam',   'price'=>42.99, 'reviews'=>883,  'rating'=>4.5, 'category'=>'Travel & Daily Use', 'color'=>'#fee2e2', 'icon'=>'🛫', 'desc'=>'Supportive memory foam travel pillow with washable cover and snap closure for flights and train rides.'],
    36 => ['id'=>36, 'name'=>'Reusable Shopping Tote',      'price'=>33.95, 'reviews'=>706,  'rating'=>4.4, 'category'=>'Travel & Daily Use', 'color'=>'#e0f2fe', 'icon'=>'🛍', 'desc'=>'Foldable reusable shopping tote made from sturdy recycled fabric. Packs into a small inner pocket.'],
    37 => ['id'=>37, 'name'=>'Document Tray Organizer',     'price'=>25.99, 'reviews'=>389,  'rating'=>4.3, 'category'=>'Office & Study',     'color'=>'#fce7f3', 'icon'=>'📥', 'desc'=>'Stackable metal document tray for mail, papers and project folders. Keeps desks clear and easy to scan.'],
    38 => ['id'=>38, 'name'=>'Commuter Travel Mug',         'price'=>39.50, 'reviews'=>1158, 'rating'=>4.6, 'category'=>'Travel & Daily Use', 'color'=>'#fef3c7', 'icon'=>'🥤', 'desc'=>'Leak-resistant travel mug with double-wall insulation and one-handed lid. Keeps coffee warm during commutes.'],
    39 => ['id'=>39, 'name'=>'Ergonomic Wrist Rest',        'price'=>18.95, 'reviews'=>477,  'rating'=>4.2, 'category'=>'Office & Study',     'color'=>'#f7fee7', 'icon'=>'⌨️', 'desc'=>'Soft keyboard wrist rest with non-slip base for comfortable typing during longer work or study sessions.'],
    40 => ['id'=>40, 'name'=>'Noise-Isolating Headphones',  'price'=>58.99, 'reviews'=>1672, 'rating'=>4.5, 'category'=>'Electronics',   'color'=>'#ede9fe', 'icon'=>'🎧', 'desc'=>'Over-ear headphones with soft ear cushions, foldable design and strong passive noise isolation for travel.'],
    41 => ['id'=>41, 'name'=>'Compact Power Bank',          'price'=>29.99, 'reviews'=>2218, 'rating'=>4.7, 'category'=>'Electronics',   'color'=>'#e0f2fe', 'icon'=>'🔋', 'desc'=>'Slim 10000 mAh power bank with USB-C input/output and LED battery indicator for phones and earbuds.'],
    42 => ['id'=>42, 'name'=>'Cordless Hand Vacuum',        'price'=>49.95, 'reviews'=>975,  'rating'=>4.3, 'category'=>'Home & Kitchen', 'color'=>'#f1f5f9', 'icon'=>'🧹', 'desc'=>'Lightweight cordless hand vacuum for crumbs, car interiors and quick cleanups. Includes two nozzle attachments.'],
    43 => ['id'=>43, 'name'=>'Herb Garden Starter Kit',     'price'=>27.50, 'reviews'=>612,  'rating'=>4.4, 'category'=>'Home & Kitchen', 'color'=>'#dcfce7', 'icon'=>'🌿', 'desc'=>'Indoor herb growing kit with pots, soil discs, labels and seeds for basil, parsley and chives.'],
    44 => ['id'=>44, 'name'=>'Trail Hiking Backpack',       'price'=>64.99, 'reviews'=>734,  'rating'=>4.5, 'category'=>'Sports',         'color'=>'#dbeafe', 'icon'=>'🎒', 'desc'=>'Lightweight 25 L hiking backpack with padded straps, rain cover and hydration pocket for day trips.'],
];

// Survey questions for step 6
$SURVEY_QUESTIONS = [
    ['id' => 'Q9',
     'text'    => ['en' => 'For quality control purposes, please select "Never" for this item. How often do you return purchased items to an online store?',
                   'nl' => 'Voor kwaliteitscontrole selecteer "Nooit" voor deze vraag: hoe vaak retourneer je gekochte artikelen naar een online winkel?'],
     'options' => ['en' => ['1' => 'Never', '2' => 'Rarely', '3' => 'Sometimes', '4' => 'Often', '5' => 'Always'],
                   'nl' => ['1' => 'Nooit',  '2' => 'Zelden', '3' => 'Soms',      '4' => 'Vaak',  '5' => 'Altijd']],
     'is_val' => true, 'correct' => '1', 'flag' => null],

    ['id' => 'D1',
     'text'    => ['en' => 'Which of the following products was the cheapest in the online store you just browsed?',
                   'nl' => 'Welk van de volgende producten was het goedkoopst in de webshoop die je zojuist hebt bekeken?'],
     'options' => ['en' => ['a' => 'Yoga Mat Premium', 'b' => 'Manual Coffee Grinder', 'c' => 'Water Bottle', 'd' => 'Portable Phone Stand'],
                   'nl' => ['a' => 'Yoga Mat Premium', 'b' => 'Manual Coffee Grinder', 'c' => 'Water Bottle', 'd' => 'Portable Phone Stand']],
     'is_val' => true, 'correct' => 'd', 'flag' => null],

    ['id' => 'D2',
     'text'    => ['en' => 'Approximately how many customer reviews did the most-reviewed product in the store have?',
                   'nl' => 'Hoeveel klantbeoordelingen had het meest beoordeelde product in de winkel ongeveer?'],
     'options' => ['en' => ['a' => 'Fewer than 500', 'b' => '500 – 1,000', 'c' => '1,000 – 2,000', 'd' => 'More than 2,000'],
                   'nl' => ['a' => 'Minder dan 500', 'b' => '500 – 1.000', 'c' => '1.000 – 2.000', 'd' => 'Meer dan 2.000']],
     'is_val' => true, 'correct' => 'd', 'flag' => null],
];

// UI strings for English and Dutch
$TRANSLATIONS = [
    'en' => [
        'home'            => 'Home',
        'search_ph'       => 'Search products…',
        'cart'            => 'Cart',
        'cat_all'         => 'All',
        'cat_electronics' => 'Electronics',
        'cat_home_kitchen'=> 'Home & Kitchen',
        'cat_sports'      => 'Sports & Wellness',
        'cat_office'      => 'Office & Study',
        'cat_travel'      => 'Travel & Daily Use',
        'all_products'    => 'All Products (%d)',
        'reviews'         => 'reviews',
        'back_catalog'    => '← Back to products',
        'in_stock'        => '✓ In stock - ships within 1–2 business days',
        'add_to_cart'     => 'Add to cart',
        'buy_now'         => 'Buy now',
        'toast_added'     => '🛒 Added to cart!',
        'task_label'      => 'Task %d of 7',
        'task_instr'      => 'Browse the store and please add the following product to your shopping cart: "%s"',
        'survey_banner'   => 'Please complete the following short survey. All questions must be answered before you can continue.',
        'dist_cheap'      => 'Browse the products below. Which product is the cheapest? Click on it to continue.',
        'dist_reviews'    => 'Browse the products below. Which product has the most reviews? Click on it to continue.',
        'ready_h'         => 'Are you ready for the next task?',
        'ready_p'         => 'Press the continue button when you are ready to proceed.',
        'continue_btn'    => 'Continue →',
        'welcome_h'       => 'Welcome to ShopLab',
        'welcome_p'       => 'You are about to complete a short shopping task. Your unique session ID is shown below you will need to enter it in the survey at the end.',
        'sid_label'       => 'Your session ID',
        'start_btn'       => 'Start experiment →',
        'sid_note'        => 'Your ID will be shown again at the end so you can copy it.',
        'survey_h'        => 'Quick Shopping Habits Survey',
        'survey_sub'      => 'This survey has 3 questions and should take less than 2 minutes.',
        'submit_btn'      => 'Submit survey →',
        'survey_alert'    => 'Please answer all questions before submitting.',
        'done_h'          => "You're done!",
        'done_p'          => 'Thank you for completing the shopping tasks. Please copy your session ID and enter it in the Qualtrics survey to link your responses.',
        'copy_btn'        => '📋 Copy session ID',
        'copied'          => '✓ Copied!',
        'return_btn'      => 'Return to survey →',
        'browse_title'    => 'Browse Products',
        'sort_label'      => 'Sort:',
        'sort_default'    => 'Default',
        'sort_price_asc'  => 'Price ↑',
        'sort_price_desc' => 'Price ↓',
        'sort_reviews'    => 'Most reviewed',
        'sort_rating'     => 'Top rated',
        'footer'          => '© 2026 ShopLab Store &nbsp;·&nbsp; All prices include VAT &nbsp;·&nbsp; Free returns within 30 days',
    ],
    'nl' => [
        'home'            => 'Homepagina',
        'search_ph'       => 'Zoek producten…',
        'cart'            => 'Winkelwagen',
        'cat_all'         => 'Alles',
        'cat_electronics' => 'Elektronica',
        'cat_home_kitchen'=> 'Huis, Tuin & Keuken',
        'cat_sports'      => 'Sport & Wellness',
        'cat_office'      => 'Kantoor & Studie',
        'cat_travel'      => 'Reizen & Dagelijks Gebruik',
        'all_products'    => 'Alle producten (%d)',
        'reviews'         => 'beoordelingen',
        'back_catalog'    => '←  Terug naar catalogus',
        'in_stock'        => '✓ Op voorraad verzending binnen 1–2 werkdagen',
        'add_to_cart'     => 'Toevoegen aan winkelwagen',
        'buy_now'         => 'Bestel direct!',
        'toast_added'     => '🛒 Toegevoegd aan winkelwagen!',
        'task_label'      => 'Taak %d van 7',
        'task_instr'      => 'Navigeer door de webshop en voeg het volgende product toe aan je winkelwagen: "%s"',
        'survey_banner'   => 'Wil je de volgende enquete invullen? Alle vragen moeten beantwoord worden om door te kunnen gaan.',
        'dist_cheap'      => 'Bekijk de volgende producten. Welk product is het goedkoopste? Klik erop om door te gaan.',
        'dist_reviews'    => 'Bekijk de volgende producten. Welk product heeft de meeste recensies? Klik erop om door te gaan.',
        'ready_h'         => 'Ben je klaar voor de volgende taak?',
        'ready_p'         => 'Druk op doorgaan als je klaar bent om verder te gaan.',
        'continue_btn'    => 'Doorgaan →',
        'welcome_h'       => 'Welkom bij ShopLab',
        'welcome_p'       => 'Je staat op het punt een kort webshop experiment uit te voeren. Je unieke sessie-ID wordt hieronder weergegeven. Wil je deze noteren in de survey? Zo kunnen we jouw survey antwoorden en resultaten koppelen.',
        'sid_label'       => 'Je sessie-ID',
        'start_btn'       => 'Start experiment →',
        'sid_note'        => 'Je ID wordt aan het einde opnieuw getoond zodat je het kunt kopiëren naar het klembord, kunt noteren of onthouden.',
        'survey_h'        => 'Korte Survey',
        'survey_sub'      => 'Deze enquête bevat 3 (korte) vragen.',
        'submit_btn'      => 'Enquête indienen →',
        'survey_alert'    => 'Beantwoord aub alle vragen voordat je de enquête voltooid.',
        'done_h'          => 'Klaar!',
        'done_p'          => 'Bedankt voor het voltooien van de winkelopdrachten. Kopieer jouw sessie-ID en voer deze aub in de Qualtrics-enquête in om jouw antwoorden te koppelen.',
        'copy_btn'        => '📋 Sessie-ID kopiëren',
        'copied'          => '✓ Gekopieerd!',
        'return_btn'      => 'Terug naar enquête →',
        'browse_title'    => 'Producten bekijken',
        'sort_label'      => 'Sorteer:',
        'sort_default'    => 'Standaard',
        'sort_price_asc'  => 'Prijs ↑',
        'sort_price_desc' => 'Prijs ↓',
        'sort_reviews'    => 'Meest beoordeeld',
        'sort_rating'     => 'Hoogst beoordeeld',
        'footer'          => '© 2026 ShopLab Store &nbsp;·&nbsp; Alle prijzen zijn inclusief btw &nbsp;·&nbsp; Gratis retourneren binnen 30 dagen',
    ],
];

// Get a translated string by key
function t(string $key): string {
    global $TRANSLATIONS;
    $lang = $_SESSION['lang'] ?? 'en';
    if (isset($TRANSLATIONS[$lang][$key])) {
        return $TRANSLATIONS[$lang][$key];
    }
    if (isset($TRANSLATIONS['en'][$key])) {
        return $TRANSLATIONS['en'][$key];
    }
    return $key;
}

// Translate a category name to the current language
function tCat(string $cat): string {
    $map = [
        'All'            => 'cat_all',
        'Electronics'    => 'cat_electronics',
        'Home & Kitchen' => 'cat_home_kitchen',
        'Sports'         => 'cat_sports',
        'Office & Study' => 'cat_office',
        'Travel & Daily Use' => 'cat_travel',
    ];
    if (isset($map[$cat])) {
        return t($map[$cat]);
    }
    return $cat;
}

// Returns the condition and product for each task step
function getTaskCfg(int $step): array {
    if ($step === 1) {
        return ['condition' => 'standard', 'product_id' => 1, 'label' => 'Standard-A'];
    } elseif ($step === 3) {
        return ['condition' => 'standard', 'product_id' => 2, 'label' => 'Standard-B'];
    } elseif ($step === 5) {
        return ['condition' => 'modified', 'product_id' => 3, 'label' => 'Modified-A'];
    } else {
        return ['condition' => 'modified', 'product_id' => 4, 'label' => 'Modified-B'];
    }
}

// Returns all products in a fixed display order where target products (1–4)
// are spread across positions 21, 26, 33, 42 so participants must scroll to find them.
function getOrderedProducts(): array {
    global $PRODUCTS;
    $order = [
        5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,
        21,22,23,24,
        1,
        25,26,27,28,
        2,
        29,30,31,32,33,34,
        3,
        35,36,37,38,39,40,41,42,
        4,
        43,44,
    ];
    $result = [];
    foreach ($order as $id) {
        if (isset($PRODUCTS[$id])) {
            $result[$id] = $PRODUCTS[$id];
        }
    }
    return $result;
}

// Create a unique session ID for each participant
function generateSessionId(): string {
    $num = random_int(1000, 9999);
    return 'S-2026-' . $num;
}

// Read the counter file, add 1, save it, and return the new number
function getParticipantNumber(): int {
    $count = 0;
    if (file_exists(COUNTER_FILE)) {
        $count = (int)file_get_contents(COUNTER_FILE);
    }
    $count++;
    file_put_contents(COUNTER_FILE, (string)$count);
    return $count;
}

// Get the current time as an ISO 8601 string with milliseconds
function nowIso(): string {
    $dt = new DateTime('now', new DateTimeZone('UTC'));
    $ms = (int)(microtime(true) * 1000) % 1000;
    $ms_padded = str_pad((string)$ms, 3, '0', STR_PAD_LEFT);
    return $dt->format('Y-m-d\TH:i:s') . '.' . $ms_padded . 'Z';
}

// How many milliseconds since the experiment started for this session
function msElapsed(): int {
    if (empty($_SESSION['start_microtime'])) {
        return 0;
    }
    return (int)round((microtime(true) - $_SESSION['start_microtime']) * 1000);
}

// Write an event row to the CSV log file
function appendEvent(array $data): void {
    $columns = [
        'session_id', 'timestamp_iso', 'ms_elapsed', 'event_type', 'step', 'condition',
        'target_product_id', 'clicked_element', 'click_x', 'click_y',
        'time_to_first_click_ms', 'is_unintended_interaction',
        'question_id', 'question_text', 'answer_value',
        'is_validation_question', 'validation_passed', 'completed'
    ];

    // Build the row, fill in empty string for missing fields
    $row = [];
    foreach ($columns as $col) {
        $row[] = isset($data[$col]) ? $data[$col] : '';
    }

    // Check if the file is new so we can write a header row first
    $is_new_file = !file_exists(EVENTS_FILE) || filesize(EVENTS_FILE) === 0;

    $fp = fopen(EVENTS_FILE, 'a');
    if ($fp && flock($fp, LOCK_EX)) {
        if ($is_new_file) {
            fputcsv($fp, $columns);
        }
        fputcsv($fp, $row);
        flock($fp, LOCK_UN);
    }
    if ($fp) {
        fclose($fp);
    }
}

// Render star icons for a given rating
function starsHtml(float $rating): string {
    $full  = (int)floor($rating);
    $half  = ($rating - $full >= 0.25) ? 1 : 0;
    $empty = 5 - $full - $half;

    $html = '';
    for ($i = 0; $i < $full; $i++) {
        $html .= '<span class="star full">★</span>';
    }
    if ($half) {
        $html .= '<span class="star half">★</span>';
    }
    for ($i = 0; $i < $empty; $i++) {
        $html .= '<span class="star empty">☆</span>';
    }
    return $html;
}

// Escape output to prevent XSS
function esc(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

// --- SESSION INIT ---

if (empty($_SESSION['step'])) {
    $_SESSION['step'] = 0;
}
if ($_SESSION['step'] === 0 && empty($_SESSION['session_id'])) {
    $_SESSION['session_id']         = generateSessionId();
    $_SESSION['participant_number'] = getParticipantNumber();
}

// Handle language switch via GET parameter
if (isset($_GET['lang']) && in_array($_GET['lang'], ['en', 'nl'], true)) {
    $_SESSION['lang'] = $_GET['lang'];
    header('Location: index.php');
    exit;
}

// --- POST HANDLER ---

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $cur    = (int)($_SESSION['step'] ?? 0);
    $intr   = !empty($_SESSION['show_interstitial']);

    $task_steps = [1, 3, 5, 7];

    // Start button clicked on the welcome page
    if ($action === 'start' && $cur === 0 && !$intr) {
        $_SESSION['start_microtime']   = microtime(true);
        $_SESSION['step']              = 1;
        $_SESSION['show_interstitial'] = true;
        appendEvent([
            'session_id'    => $_SESSION['session_id'] ?? '',
            'timestamp_iso' => nowIso(),
            'ms_elapsed'    => msElapsed(),
            'event_type'    => 'session_start',
            'step'          => 0,
        ]);
        header('Location: index.php');
        exit;
    }

    // Continue button on the interstitial screen
    if ($action === 'continue' && $intr) {
        $_SESSION['show_interstitial'] = false;
        unset($_SESSION['task_product_view']);
        appendEvent([
            'session_id'    => $_SESSION['session_id'] ?? '',
            'timestamp_iso' => nowIso(),
            'ms_elapsed'    => msElapsed(),
            'event_type'    => 'page_enter',
            'step'          => $cur,
        ]);
        header('Location: index.php');
        exit;
    }

    // Participant clicked on a product card
    if ($action === 'view_product' && !$intr && in_array($cur, $task_steps, true)) {
        $pid = (int)($_POST['product_id'] ?? 0);
        global $PRODUCTS;
        if (isset($PRODUCTS[$pid])) {
            $_SESSION['task_product_view'] = $pid;
        }
        header('Location: index.php');
        exit;
    }

    // Back button on product detail page
    if ($action === 'back_to_catalog' && !$intr && in_array($cur, $task_steps, true)) {
        unset($_SESSION['task_product_view']);
        header('Location: index.php');
        exit;
    }

    // Move to next step (triggered by JS after logging events)
    if ($action === 'advance' && !$intr && $cur >= 1 && $cur <= 7) {
        unset($_SESSION['task_product_view']);
        unset($_SESSION['task_started'][$cur]);
        $next_step = $cur + 1;
        $_SESSION['step'] = $next_step;
        if ($next_step === 8) {
            $_SESSION['show_interstitial'] = false;
            appendEvent([
                'session_id'    => $_SESSION['session_id'] ?? '',
                'timestamp_iso' => nowIso(),
                'ms_elapsed'    => msElapsed(),
                'event_type'    => 'session_complete',
                'step'          => 8,
                'completed'     => 1,
            ]);
            appendEvent([
                'session_id'    => $_SESSION['session_id'] ?? '',
                'timestamp_iso' => nowIso(),
                'ms_elapsed'    => msElapsed(),
                'event_type'    => 'page_enter',
                'step'          => 8,
            ]);
        } else {
            $_SESSION['show_interstitial'] = true;
        }
        header('Location: index.php');
        exit;
    }

    // Survey form submitted
    if ($action === 'submit_survey' && !$intr && $cur === 6) {
        global $SURVEY_QUESTIONS;
        $lang = $_SESSION['lang'] ?? 'en';
        foreach ($SURVEY_QUESTIONS as $q) {
            $ans  = $_POST['q_' . $q['id']] ?? '';
            $text = isset($q['text'][$lang]) ? $q['text'][$lang] : $q['text']['en'];
            $validation_passed = '';
            if ($q['is_val'] && $q['correct'] !== null) {
                $validation_passed = ($ans === $q['correct']) ? '1' : '0';
            }
            appendEvent([
                'session_id'             => $_SESSION['session_id'] ?? '',
                'timestamp_iso'          => nowIso(),
                'ms_elapsed'             => msElapsed(),
                'event_type'             => 'survey_response',
                'step'                   => 6,
                'question_id'            => $q['id'],
                'question_text'          => $text,
                'answer_value'           => $ans,
                'is_validation_question' => '1',
                'validation_passed'      => $validation_passed,
            ]);
        }
        appendEvent([
            'session_id'    => $_SESSION['session_id'] ?? '',
            'timestamp_iso' => nowIso(),
            'ms_elapsed'    => msElapsed(),
            'event_type'    => 'page_exit',
            'step'          => 6,
        ]);
        $_SESSION['step']              = 7;
        $_SESSION['show_interstitial'] = true;
        header('Location: index.php');
        exit;
    }

    header('Location: index.php');
    exit;
}

// --- ROUTING ---

$step = (int)($_SESSION['step'] ?? 0);
$intr = !empty($_SESSION['show_interstitial']);
$sid  = $_SESSION['session_id'] ?? '';

if ($intr && ($step < 1 || $step > 7)) {
    $_SESSION['show_interstitial'] = false;
    $intr = false;
}

// --- CSS ---

$CSS = <<<'CSS'
:root {
    --primary:   #25a6eb;
    --primary-h: #1d84d8;
    --orange:    #f97316;
    --orange-h:  #ea6c0e;
    --text:      #111827;
    --muted:     #6b7280;
    --border:    #e5e7eb;
    --bg:        #f9fafb;
    --white:     #ffffff;
    --radius:    8px;
    --sh:        0 1px 3px rgba(0,0,0,.10),0 1px 2px rgba(0,0,0,.06);
    --sh-md:     0 4px 6px rgba(0,0,0,.07),0 2px 4px rgba(0,0,0,.06);
    --sh-lg:     0 10px 25px rgba(0,0,0,.12);
}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;background:var(--bg);color:var(--text);line-height:1.5;min-height:100vh}
.site-header{background:var(--white);border-bottom:1px solid var(--border);position:sticky;top:0;z-index:100;box-shadow:var(--sh)}
.hdr-inner{max-width:1200px;margin:0 auto;padding:0 1.5rem;display:flex;align-items:center;gap:1.25rem;height:64px}
.logo{font-size:1.4rem;font-weight:800;color:var(--primary);text-decoration:none;flex-shrink:0;letter-spacing:-.5px}
.logo em{color:var(--text);font-style:normal}
.search-wrap{flex:1;position:relative;min-width:0}
.search-wrap input{width:100%;padding:.5rem 1rem .5rem 2.4rem;border:1px solid var(--border);border-radius:20px;font-size:.9rem;background:var(--bg);color:var(--text);outline:none}
.search-ico{position:absolute;left:.75rem;top:50%;transform:translateY(-50%);color:var(--muted);font-size:.95rem;pointer-events:none}
.hdr-nav{display:flex;gap:1.25rem;list-style:none}
.hdr-nav a{color:var(--muted);text-decoration:none;font-size:.875rem;white-space:nowrap}
.hdr-nav a:hover{color:var(--text)}
.cart-btn{background:var(--primary);color:#fff;border:none;padding:.5rem .875rem;border-radius:var(--radius);font-size:.875rem;display:flex;align-items:center;gap:.35rem;font-weight:600;white-space:nowrap;cursor:default}
.cat-bar{background:var(--white);border-bottom:1px solid var(--border);overflow-x:auto}
.cat-bar ul{display:flex;list-style:none;max-width:1200px;margin:0 auto;padding:0 1.5rem}
.cat-bar a{display:block;padding:.65rem 1rem;font-size:.85rem;color:var(--muted);text-decoration:none;white-space:nowrap;border-bottom:2px solid transparent}
.cat-bar a:hover,.cat-bar a.active{color:var(--text);border-color:var(--primary)}
.task-banner{background:#eff6ff;border-left:4px solid var(--primary);margin:1.25rem auto;max-width:1200px;padding:.875rem 1.25rem;border-radius:0 var(--radius) var(--radius) 0}
.task-banner .tag{font-size:.7rem;font-weight:700;color:var(--primary);text-transform:uppercase;letter-spacing:.06em}
.task-banner .msg{font-size:.975rem;font-weight:500;margin-top:.2rem;color:var(--text)}
.session-badge{position:fixed;top:70px;right:.875rem;background:rgba(255,255,255,.92);border:1px solid var(--border);border-radius:6px;padding:.2rem .5rem;font-size:.68rem;color:var(--muted);z-index:300;backdrop-filter:blur(4px);box-shadow:var(--sh);line-height:1.4;pointer-events:none}
.section{max-width:1200px;margin:0 auto;padding:1.5rem}
.section-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem;flex-wrap:wrap;gap:.5rem}
.section-title{font-size:1.05rem;font-weight:700}
.filter-bar{display:flex;gap:.5rem;flex-wrap:wrap}
.filter-chip{background:var(--white);border:1px solid var(--border);border-radius:20px;padding:.3rem .75rem;font-size:.8rem;color:var(--muted);cursor:pointer;user-select:none}
.filter-chip.active{background:var(--primary);color:#fff;border-color:var(--primary)}
.filter-chip:hover:not(.active){border-color:var(--primary);color:var(--primary)}
.product-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:1.25rem}
.p-card{background:var(--white);border:1px solid var(--border);border-radius:var(--radius);overflow:hidden;box-shadow:var(--sh);transition:box-shadow .15s,transform .15s;cursor:pointer;display:block;width:100%;text-align:left;font:inherit;color:inherit;appearance:none;-webkit-appearance:none;padding:0}
.p-card:hover{box-shadow:var(--sh-md);transform:translateY(-2px)}
.p-card-img{width:100%;aspect-ratio:1/1;display:flex;align-items:center;justify-content:center;font-size:3.5rem;overflow:hidden}
.p-card-img img{width:100%;height:100%;object-fit:cover;display:block}
.p-card-body{padding:.875rem}
.p-card-cat{font-size:.7rem;color:var(--muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:.2rem}
.p-card-name{font-size:.9rem;font-weight:600;margin-bottom:.3rem;line-height:1.3}
.p-card-stars{font-size:.8rem;margin-bottom:.25rem}
.star.full,.star.half{color:#f59e0b}
.star.empty{color:#d1d5db}
.p-card-row{display:flex;align-items:baseline;gap:.5rem}
.p-card-price{font-size:1.1rem;font-weight:700;color:var(--primary)}
.p-card-rev{font-size:.75rem;color:var(--muted)}
.back-wrap{max-width:1200px;margin:.875rem auto 0;padding:0 1.5rem}
.back-btn{background:none;border:none;color:var(--muted);font-size:.875rem;cursor:pointer;display:inline-flex;align-items:center;gap:.3rem;padding:.3rem 0;font-family:inherit}
.back-btn:hover{color:var(--primary)}
.breadcrumb{max-width:1200px;margin:.5rem auto 0;padding:0 1.5rem;font-size:.8rem;color:var(--muted)}
.breadcrumb a{color:var(--muted);text-decoration:none}
.breadcrumb a:hover{text-decoration:underline}
.breadcrumb span{margin:0 .35rem}
.pd-wrap{max-width:1200px;margin:1.25rem auto;padding:0 1.5rem}
.pd-grid{display:grid;grid-template-columns:1fr 1fr;gap:3rem;background:var(--white);border-radius:var(--radius);box-shadow:var(--sh-md);padding:2rem}
.pd-img{border-radius:var(--radius);display:flex;align-items:center;justify-content:center;font-size:7rem;aspect-ratio:1;overflow:hidden}
.pd-img img{width:100%;height:100%;object-fit:cover;display:block}
.pd-info h1{font-size:1.4rem;font-weight:700;line-height:1.3;margin-bottom:.5rem}
.pd-cat{font-size:.75rem;color:var(--muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:.4rem}
.pd-stars{font-size:.9rem;margin-bottom:.75rem;display:flex;align-items:center;gap:.4rem}
.pd-stars .rev-count{font-size:.8rem;color:var(--muted)}
.pd-price{font-size:1.85rem;font-weight:800;color:var(--primary);margin-bottom:.75rem}
.pd-desc{font-size:.9rem;color:var(--muted);line-height:1.6;margin-bottom:1.5rem;border-top:1px solid var(--border);padding-top:1rem}
.pd-stock{font-size:.8rem;color:#16a34a;font-weight:600;margin-bottom:1rem}
.pd-btn{width:100%;padding:.875rem 1rem;border:none;border-radius:var(--radius);font-size:1rem;font-weight:700;cursor:pointer;transition:background .15s;margin-bottom:.6rem;letter-spacing:.01em}
.pd-btn:active{opacity:.9}
.btn-atc{background:var(--primary);color:#fff}
.btn-atc:hover{background:var(--primary-h)}
.btn-bn{background:var(--orange);color:#fff}
.btn-bn:hover{background:var(--orange-h)}
.toast{position:fixed;bottom:1.5rem;left:50%;transform:translateX(-50%) translateY(80px);background:#1e293b;color:#fff;padding:.65rem 1.25rem;border-radius:var(--radius);font-size:.875rem;font-weight:600;opacity:0;transition:transform .25s,opacity .25s;z-index:500;white-space:nowrap;pointer-events:none}
.toast.show{transform:translateX(-50%) translateY(0);opacity:1}
.pg-center{min-height:calc(100vh - 64px);display:flex;align-items:center;justify-content:center;padding:2rem}
.start-card{background:var(--white);border-radius:16px;padding:2.5rem 2rem;max-width:460px;width:100%;box-shadow:var(--sh-lg);text-align:center}
.start-card h1{font-size:1.6rem;font-weight:800;margin-bottom:.5rem}
.start-card p{font-size:.925rem;color:var(--muted);margin-bottom:1.5rem}
.sid-box{background:var(--bg);border:2px dashed var(--border);border-radius:var(--radius);padding:.875rem 1.25rem;margin-bottom:1.5rem}
.sid-label{font-size:.75rem;color:var(--muted);text-transform:uppercase;letter-spacing:.07em;margin-bottom:.3rem}
.sid-value{font-size:1.5rem;font-weight:800;color:var(--primary);letter-spacing:.1em;font-family:monospace}
.btn-primary{display:block;width:100%;background:var(--primary);color:#fff;border:none;border-radius:var(--radius);padding:.875rem 2rem;font-size:1rem;font-weight:700;cursor:pointer;text-decoration:none;transition:background .15s;margin-bottom:.75rem;text-align:center}
.btn-primary:hover{background:var(--primary-h)}
.btn-secondary{display:block;width:100%;background:var(--white);color:var(--primary);border:2px solid var(--primary);border-radius:var(--radius);padding:.75rem 2rem;font-size:1rem;font-weight:700;cursor:pointer;text-decoration:none;transition:all .15s;text-align:center}
.btn-secondary:hover{background:var(--primary);color:#fff}
.step-dots{display:flex;gap:.35rem;justify-content:center;margin-bottom:1.75rem}
.s-dot{width:8px;height:8px;border-radius:50%;background:var(--border)}
.s-dot.done{background:var(--primary)}
.s-dot.active{background:var(--primary);box-shadow:0 0 0 3px rgba(37,99,235,.25)}
.intr-card{background:var(--white);border-radius:16px;padding:2.5rem 2rem;max-width:400px;width:100%;box-shadow:var(--sh-lg);text-align:center}
.intr-card .ico{font-size:3rem;margin-bottom:.75rem}
.intr-card h2{font-size:1.25rem;font-weight:700;margin-bottom:.5rem}
.intr-card p{font-size:.9rem;color:var(--muted);margin-bottom:1.5rem}
.survey-wrap{max-width:680px;margin:2rem auto;padding:0 1.5rem}
.survey-card{background:var(--white);border-radius:var(--radius);box-shadow:var(--sh-md);padding:2rem}
.survey-card h2{font-size:1.2rem;font-weight:700;margin-bottom:.25rem}
.survey-card .sub{font-size:.875rem;color:var(--muted);margin-bottom:1.5rem}
.q-block{margin-bottom:1.5rem;padding-bottom:1.5rem;border-bottom:1px solid var(--border)}
.q-block:last-of-type{border-bottom:none;margin-bottom:0;padding-bottom:0}
.q-text{font-size:.9rem;font-weight:600;margin-bottom:.75rem;line-height:1.5}
.q-opts{display:flex;flex-wrap:wrap;gap:.5rem}
.q-opt label{display:flex;align-items:center;gap:.4rem;background:var(--bg);border:1px solid var(--border);border-radius:6px;padding:.45rem .75rem;font-size:.85rem;cursor:pointer;transition:all .15s}
.q-opt label:hover{border-color:var(--primary);background:#eff6ff}
.q-opt label:has(input:checked){border-color:var(--primary);background:#eff6ff;font-weight:600}
.q-opt input[type=radio]{accent-color:var(--primary)}
.btn-submit{background:var(--primary);color:#fff;border:none;border-radius:var(--radius);padding:.875rem 2rem;font-size:1rem;font-weight:700;cursor:pointer;width:100%;margin-top:1.5rem;transition:background .15s}
.btn-submit:hover{background:var(--primary-h)}
.lang-sw{display:flex;align-items:center;gap:.3rem;font-size:.8rem;flex-shrink:0}
.lang-sw a{color:var(--muted);text-decoration:none;font-weight:500;padding:.2rem .4rem;border-radius:4px;transition:color .15s}
.lang-sw a.active{color:var(--primary);font-weight:700}
.lang-sw a:hover:not(.active){color:var(--text)}
.lang-sw span{color:var(--border)}
.site-footer{background:var(--white);border-top:1px solid var(--border);margin-top:3rem;padding:1.5rem;text-align:center;font-size:.8rem;color:var(--muted)}
@media(max-width:768px){
    .pd-grid{grid-template-columns:1fr;gap:1.5rem}
    .hdr-inner{gap:.5rem;padding:0 .75rem}
    .logo{font-size:1.1rem}
    .hdr-nav{display:none}
    .lang-sw{display:none}
    .session-badge{display:none}
    .search-wrap{flex:1;max-width:none}
    .cart-text{display:none}
    .cart-btn{padding:.5rem .6rem}
    .product-grid{grid-template-columns:repeat(auto-fill,minmax(160px,1fr))}
    .pd-img{font-size:5rem}
    .start-card,.intr-card{padding:2rem 1.25rem}
}
CSS;

// --- LAYOUT HELPERS ---

function openPage(string $title): void {
    global $CSS;
    $lang = $_SESSION['lang'] ?? 'en';
    echo '<!DOCTYPE html>';
    echo '<html lang="' . $lang . '">';
    echo '<head>';
    echo '<meta charset="UTF-8">';
    echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
    echo '<title>' . esc($title) . ' - ShopLab</title>';
    echo '<style>' . $CSS . '</style>';
    echo '<script src="https://t.contentsquare.net/uxa/3ad88be923834.js"></script>';
    echo '</head>';
    echo '<body>';
}

function siteHeader(string $context = 'catalog'): void {
    $is_product = ($context === 'product');
    $lang = $_SESSION['lang'] ?? 'en';

    // Search bar is read-only on product pages so participants don't get distracted
    $search_readonly = $is_product ? ' readonly' : '';

    echo '<header class="site-header"><div class="hdr-inner">';
    echo '<a class="logo" href="#" onclick="goHome();return false;">Shop<em>Lab</em></a>';
    echo '<div class="search-wrap"><span class="search-ico">🔍</span>';
    echo '<input type="text" id="search-input" placeholder="' . esc(t('search_ph')) . '" tabindex="-1"' . $search_readonly . '></div>';
    echo '<ul class="hdr-nav">';
    echo '<li><a href="#" onclick="goHome();return false;">' . esc(t('home')) . '</a></li>';
    echo '</ul>';
    echo '<div class="lang-sw">';
    echo '<a href="index.php?lang=en"' . ($lang === 'en' ? ' class="active"' : '') . '>EN</a>';
    echo '<span>|</span>';
    echo '<a href="index.php?lang=nl"' . ($lang === 'nl' ? ' class="active"' : '') . '>NL</a>';
    echo '</div>';
    echo '<button class="cart-btn" tabindex="-1">🛒 <span class="cart-text">' . esc(t('cart')) . ' </span><strong id="cart-count">(0)</strong></button>';
    echo '</div></header>';

    echo '<nav class="cat-bar"><ul>';
    $categories = ['All', 'Electronics', 'Home & Kitchen', 'Sports', 'Office & Study', 'Travel & Daily Use'];
    foreach ($categories as $cat) {
        if ($is_product) {
            echo '<li><a href="#" class="cat-link" onclick="goHome();return false;">' . esc(tCat($cat)) . '</a></li>';
        } else {
            echo '<li><a href="#" class="cat-link" data-filter="' . esc($cat) . '">' . esc(tCat($cat)) . '</a></li>';
        }
    }
    echo '</ul></nav>';
}

function sessionBadge(string $sid): void {
    if ($sid !== '') {
        echo '<div class="session-badge">Session: <strong>' . esc($sid) . '</strong></div>';
    }
}

function taskBanner(string $msg, int $step): void {
    echo '<div class="task-banner">';
    echo '<div class="tag">' . esc(sprintf(t('task_label'), $step)) . '</div>';
    echo '<div class="msg">' . esc($msg) . '</div>';
    echo '</div>';
}

function stepDots(int $current): void {
    echo '<div class="step-dots">';
    for ($i = 1; $i <= 7; $i++) {
        if ($i < $current) {
            $class = 'done';
        } elseif ($i === $current) {
            $class = 'active';
        } else {
            $class = '';
        }
        echo '<div class="s-dot ' . $class . '"></div>';
    }
    echo '</div>';
}

function siteFooter(): void {
    echo '<footer class="site-footer">' . t('footer') . '</footer>';
}

function closePage(): void {
    echo '</body></html>';
}

// --- PAGE RENDERERS ---

function renderStart(string $sid): void {
    openPage(t('welcome_h'));
    $lang = $_SESSION['lang'] ?? 'en';

    echo '<div class="pg-center"><div class="start-card">';
    echo '<div style="font-size:2.5rem;margin-bottom:.75rem">🛍</div>';

    // Language selector at the top
    echo '<div class="lang-sw" style="justify-content:center;margin-bottom:1.25rem;font-size:.9rem;gap:.6rem">';
    echo '<a href="index.php?lang=en"' . ($lang === 'en' ? ' class="active"' : '') . '>🇬🇧 English</a>';
    echo '<span>|</span>';
    echo '<a href="index.php?lang=nl"' . ($lang === 'nl' ? ' class="active"' : '') . '>🇳🇱 Nederlands</a>';
    echo '</div>';

    echo '<h1>' . esc(t('welcome_h')) . '</h1>';
    echo '<p>' . esc(t('welcome_p')) . '</p>';

    // Show the session ID so the participant can note it down
    echo '<div class="sid-box">';
    echo '<div class="sid-label">' . esc(t('sid_label')) . '</div>';
    echo '<div class="sid-value">' . esc($sid) . '</div>';
    echo '</div>';

    echo '<form method="POST" action="index.php">';
    echo '<input type="hidden" name="action" value="start">';
    echo '<button class="btn-primary" type="submit">' . esc(t('start_btn')) . '</button>';
    echo '</form>';
    echo '<p style="font-size:.8rem;color:var(--muted);margin-top:.5rem">' . esc(t('sid_note')) . '</p>';
    echo '</div></div>';

    closePage();
}

function renderInterstitial(int $next_step): void {
    openPage(t('ready_h'));
    echo '<div class="pg-center"><div class="intr-card">';
    stepDots($next_step);
    echo '<div class="ico">⏸</div>';
    echo '<h2>' . esc(t('ready_h')) . '</h2>';
    echo '<p>' . esc(t('ready_p')) . '</p>';
    echo '<form method="POST" action="index.php">';
    echo '<input type="hidden" name="action" value="continue">';
    echo '<button class="btn-primary" type="submit">' . esc(t('continue_btn')) . '</button>';
    echo '</form>';
    echo '</div></div>';
    closePage();
}

// Show the product catalog for a task step
function renderTaskCatalog(int $step, string $sid): void {
    global $PRODUCTS;
    $cfg      = getTaskCfg($step);
    $target   = $PRODUCTS[$cfg['product_id']];
    $task_msg = sprintf(t('task_instr'), $target['name']);

    // Only log task_start once per step
    $already_started = !empty($_SESSION['task_started'][$step]);
    $_SESSION['task_started'][$step] = true;

    openPage(t('browse_title'));
    siteHeader('catalog');
    sessionBadge($sid);
    taskBanner($task_msg, $step);

    echo '<div class="section">';
    echo '<div class="section-header">';
    echo '<div class="section-title">' . esc(sprintf(t('all_products'), count($PRODUCTS))) . '</div>';
    echo '<div class="filter-bar">';
    echo '<span style="font-size:.8rem;color:var(--muted);align-self:center">' . esc(t('sort_label')) . '</span>';
    foreach ([
        ['default',    t('sort_default')],
        ['price_asc',  t('sort_price_asc')],
        ['price_desc', t('sort_price_desc')],
        ['reviews',    t('sort_reviews')],
        ['rating',     t('sort_rating')],
    ] as [$key, $label]) {
        $active = $key === 'default' ? ' active' : '';
        echo '<button class="filter-chip sort-chip' . $active . '" data-sort="' . esc($key) . '" type="button">' . esc($label) . '</button>';
    }
    echo '</div>';
    echo '</div>';

    echo '<div class="product-grid">';
    foreach (getOrderedProducts() as $p) {
        echo '<form method="POST" action="index.php" style="display:contents">';
        echo '<input type="hidden" name="action" value="view_product">';
        echo '<input type="hidden" name="product_id" value="' . (int)$p['id'] . '">';
        echo '<button type="submit" class="p-card" data-cat="' . esc($p['category']) . '" data-name="' . esc($p['name']) . '" data-price="' . $p['price'] . '" data-reviews="' . (int)$p['reviews'] . '" data-rating="' . $p['rating'] . '">';
        echo '<div class="p-card-img" style="background:' . esc($p['color']) . '" data-icon="' . esc($p['icon']) . '">';
        echo '<img src="images/' . (int)$p['id'] . '.png" alt="' . esc($p['name']) . '" loading="lazy" onerror="var d=this.parentElement;d.textContent=d.dataset.icon;">';
        echo '</div>';
        echo '<div class="p-card-body">';
        echo '<div class="p-card-cat">' . esc(tCat($p['category'])) . '</div>';
        echo '<div class="p-card-name">' . esc($p['name']) . '</div>';
        echo '<div class="p-card-stars">' . starsHtml($p['rating']) . '</div>';
        echo '<div class="p-card-row">';
        echo '<span class="p-card-price">€' . number_format($p['price'], 2) . '</span>';
        echo '<span class="p-card-rev">(' . number_format($p['reviews']) . ' ' . esc(t('reviews')) . ')</span>';
        echo '</div></div></button></form>';
    }
    echo '</div></div>';

    siteFooter();

    // Pass PHP values to JavaScript
    $js_step    = $step;
    $js_cond    = $cfg['condition'];
    $js_pid     = $cfg['product_id'];
    $js_sid     = htmlspecialchars($sid, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $js_started = $already_started ? 'true' : 'false';

    echo <<<JS
<script>
var t0   = performance.now();
var SID  = "{$js_sid}";
var STEP = {$js_step};
var COND = "{$js_cond}";
var TPID = {$js_pid};

function sendEvent(data) {
    data.session_id = SID;
    fetch('log.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    });
}

function sendBeaconEvent(data) {
    data.session_id = SID;
    navigator.sendBeacon('log.php', new Blob([JSON.stringify(data)], { type: 'application/json' }));
}

if (!{$js_started}) {
    sendEvent({ event_type: 'task_start', step: STEP, condition: COND, target_product_id: TPID });
}
sendEvent({ event_type: 'page_enter', step: STEP, condition: COND, target_product_id: TPID });

window.addEventListener('beforeunload', function() {
    sendBeaconEvent({
        event_type: 'page_exit',
        step: STEP,
        condition: COND,
        target_product_id: TPID,
        ms_page: Math.round(performance.now() - t0)
    });
});

// Category filter, sort and search
var activeFilter = sessionStorage.getItem('catalogFilter') || 'All';
var activeSort   = sessionStorage.getItem('catalogSort')   || 'default';
var originalOrder = null;

function applyFilters() {
    var query = (document.getElementById('search-input').value || '').toLowerCase().trim();
    document.querySelectorAll('.p-card').forEach(function(card) {
        var catMatch  = activeFilter === 'All' || card.dataset.cat === activeFilter;
        var nameMatch = !query || card.dataset.name.toLowerCase().indexOf(query) !== -1;
        var visible   = catMatch && nameMatch;
        var parent    = card.parentElement;
        if (parent && parent.tagName === 'FORM') {
            parent.style.display = visible ? 'contents' : 'none';
        } else {
            card.style.display = visible ? '' : 'none';
        }
    });
}

function applySortOrder() {
    var grid = document.querySelector('.product-grid');
    if (!originalOrder) {
        originalOrder = Array.from(grid.children);
    }
    var items = activeSort === 'default'
        ? originalOrder.slice()
        : Array.from(grid.children).sort(function(a, b) {
            var cA = a.classList.contains('p-card') ? a : a.querySelector('.p-card');
            var cB = b.classList.contains('p-card') ? b : b.querySelector('.p-card');
            if (!cA || !cB) return 0;
            if (activeSort === 'price_asc')  return parseFloat(cA.dataset.price)  - parseFloat(cB.dataset.price);
            if (activeSort === 'price_desc') return parseFloat(cB.dataset.price)  - parseFloat(cA.dataset.price);
            if (activeSort === 'reviews')    return parseInt(cB.dataset.reviews)   - parseInt(cA.dataset.reviews);
            if (activeSort === 'rating')     return parseFloat(cB.dataset.rating) - parseFloat(cA.dataset.rating);
            return 0;
        });
    items.forEach(function(item) { grid.appendChild(item); });
}

function setFilter(cat) {
    activeFilter = cat;
    sessionStorage.setItem('catalogFilter', cat);
    document.querySelectorAll('.filter-chip:not(.sort-chip)').forEach(function(chip) {
        chip.classList.toggle('active', chip.dataset.filter === cat);
    });
    document.querySelectorAll('.cat-link[data-filter]').forEach(function(link) {
        link.classList.toggle('active', link.dataset.filter === cat);
    });
    applyFilters();
}

function setSort(key) {
    activeSort = key;
    sessionStorage.setItem('catalogSort', key);
    document.querySelectorAll('.sort-chip').forEach(function(chip) {
        chip.classList.toggle('active', chip.dataset.sort === key);
    });
    applySortOrder();
    applyFilters();
}

document.querySelectorAll('.filter-chip[data-filter]').forEach(function(chip) {
    chip.addEventListener('click', function() { setFilter(this.dataset.filter); });
});
document.querySelectorAll('.cat-link[data-filter]').forEach(function(link) {
    link.addEventListener('click', function(e) {
        e.preventDefault();
        setFilter(this.dataset.filter);
    });
});
document.querySelectorAll('.sort-chip').forEach(function(chip) {
    chip.addEventListener('click', function() { setSort(this.dataset.sort); });
});
document.getElementById('search-input').addEventListener('input', applyFilters);

// Restore filter and sort state when coming back from a product page
applySortOrder();
setFilter(activeFilter);
document.querySelectorAll('.sort-chip').forEach(function(chip) {
    chip.classList.toggle('active', chip.dataset.sort === activeSort);
});

// Logo / Home link resets filter and scrolls to top
window.goHome = function() {
    document.getElementById('search-input').value = '';
    setFilter('All');
    window.scrollTo({ top: 0, behavior: 'smooth' });
};
</script>
JS;
    closePage();
}

// Show a single product detail page
function renderTaskProduct(int $step, int $product_id, string $sid): void {
    global $PRODUCTS;
    $cfg       = getTaskCfg($step);
    $is_target = ($product_id === $cfg['product_id']);
    $p         = $PRODUCTS[$product_id];
    $cond      = $cfg['condition'];

    openPage($p['name']);
    siteHeader('product');
    sessionBadge($sid);

    $task_msg = sprintf(t('task_instr'), $PRODUCTS[$cfg['product_id']]['name']);
    taskBanner($task_msg, $step);

    // Back button
    echo '<div class="back-wrap">';
    echo '<form id="back-form" method="POST" action="index.php" style="display:inline">';
    echo '<input type="hidden" name="action" value="back_to_catalog">';
    echo '<button type="submit" class="back-btn">' . esc(t('back_catalog')) . '</button>';
    echo '</form>';
    echo '</div>';

    $cat_key = $p['category'];
    echo '<div class="breadcrumb">';
    echo '<a href="#" onclick="goHome();return false;">' . esc(t('home')) . '</a>';
    echo '<span>›</span>';
    echo '<a href="#" onclick="goHomeFiltered(\'' . esc($cat_key) . '\');return false;">' . esc(tCat($cat_key)) . '</a>';
    echo '<span>›</span>';
    echo esc($p['name']);
    echo '</div>';

    echo '<div class="pd-wrap"><div class="pd-grid">';

    // Product image
    echo '<div class="pd-img" style="background:' . esc($p['color']) . '" data-icon="' . esc($p['icon']) . '">';
    echo '<img src="images/' . (int)$p['id'] . '.png" alt="' . esc($p['name']) . '" onerror="var d=this.parentElement;d.textContent=d.dataset.icon;">';
    echo '</div>';

    // Product info
    echo '<div class="pd-info">';
    echo '<div class="pd-cat">' . esc(tCat($p['category'])) . '</div>';
    echo '<h1>' . esc($p['name']) . '</h1>';
    echo '<div class="pd-stars">' . starsHtml($p['rating']) . ' <span class="rev-count">(' . number_format($p['reviews']) . ' ' . esc(t('reviews')) . ')</span></div>';
    echo '<div class="pd-price">€' . number_format($p['price'], 2) . '</div>';
    echo '<p class="pd-desc">' . esc($p['desc']) . '</p>';
    echo '<div class="pd-stock">' . esc(t('in_stock')) . '</div>';

    // Button order depends on experimental condition
    echo '<div id="btn-area">';
    if ($cond === 'standard') {
        // Standard: Add to cart (blue) first, Buy now (orange) second
        echo '<button class="pd-btn btn-atc" data-btn="add_to_cart">' . esc(t('add_to_cart')) . '</button>';
        echo '<button class="pd-btn btn-bn"  data-btn="buy_now">'    . esc(t('buy_now'))    . '</button>';
    } else {
        // Modified: Buy now comes first and gets the blue color, Add to cart gets orange
        echo '<button class="pd-btn btn-atc" data-btn="buy_now">'    . esc(t('buy_now'))    . '</button>';
        echo '<button class="pd-btn btn-bn"  data-btn="add_to_cart">' . esc(t('add_to_cart')) . '</button>';
    }
    echo '</div>';

    echo '</div></div></div>';

    echo '<div class="toast" id="toast">' . esc(t('toast_added')) . '</div>';

    siteFooter();

    $js_step      = $step;
    $js_cond      = $cond;
    $js_tpid      = $cfg['product_id'];
    $js_pid       = $product_id;
    $js_pname     = htmlspecialchars($p['name'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $js_is_target = $is_target ? 'true' : 'false';
    $js_sid       = htmlspecialchars($sid, ENT_QUOTES | ENT_HTML5, 'UTF-8');

    echo <<<JS
<script>
var t0        = performance.now();
var clicked   = false;
var SID       = "{$js_sid}";
var STEP      = {$js_step};
var COND      = "{$js_cond}";
var TPID      = {$js_tpid};
var PID       = {$js_pid};
var IS_TARGET = {$js_is_target};

function sendEvent(data, callback) {
    data.session_id = SID;
    fetch('log.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    }).then(function() {
        if (callback) callback();
    }).catch(function() {
        if (callback) callback();
    });
}

function sendBeaconEvent(data) {
    data.session_id = SID;
    navigator.sendBeacon('log.php', new Blob([JSON.stringify(data)], { type: 'application/json' }));
}

// Go back to catalog (logo, Home link, breadcrumb all use this)
window.goHome = function() {
    document.getElementById('back-form').submit();
};
window.goHomeFiltered = function(cat) {
    sessionStorage.setItem('catalogFilter', cat);
    document.getElementById('back-form').submit();
};

sendEvent({ event_type: 'product_view', step: STEP, condition: COND, target_product_id: TPID, clicked_element: "{$js_pname}" });
sendEvent({ event_type: 'page_enter',   step: STEP, condition: COND, target_product_id: TPID });

function onUnload() {
    sendBeaconEvent({
        event_type: 'page_exit',
        step: STEP,
        condition: COND,
        target_product_id: TPID,
        ms_page: Math.round(performance.now() - t0)
    });
}
window.addEventListener('beforeunload', onUnload);

document.querySelectorAll('#btn-area button').forEach(function(btn) {
    btn.addEventListener('click', function(e) {
        if (clicked) return;
        var time_to_click = Math.round(performance.now() - t0);
        var btn_name      = btn.dataset.btn;
        var click_x       = Math.round(e.clientX);
        var click_y       = Math.round(e.clientY);

        if (IS_TARGET) {
            // Target product – log the click and move to next step
            clicked = true;
            var is_unintended = (btn_name === 'buy_now') ? 1 : 0;
            window.removeEventListener('beforeunload', onUnload);

            sendEvent({
                event_type: 'button_click',
                step: STEP, condition: COND, target_product_id: TPID,
                clicked_element: btn_name,
                click_x: click_x, click_y: click_y,
                time_to_first_click_ms: time_to_click,
                is_unintended_interaction: is_unintended
            }, function() {
                sendEvent({
                    event_type: 'task_complete',
                    step: STEP, condition: COND, target_product_id: TPID,
                    clicked_element: btn_name,
                    click_x: click_x, click_y: click_y,
                    time_to_first_click_ms: time_to_click,
                    is_unintended_interaction: is_unintended,
                    completed: 1
                }, function() {
                    sendBeaconEvent({
                        event_type: 'page_exit',
                        step: STEP, condition: COND, target_product_id: TPID,
                        ms_page: Math.round(performance.now() - t0)
                    });
                    var form  = document.createElement('form');
                    form.method = 'POST';
                    form.action = 'index.php';
                    var input = document.createElement('input');
                    input.type  = 'hidden';
                    input.name  = 'action';
                    input.value = 'advance';
                    form.appendChild(input);
                    document.body.appendChild(form);
                    form.submit();
                });
            });
        } else {
            // Not the target product – log click and show a toast
            sendEvent({
                event_type: 'button_click',
                step: STEP, condition: COND, target_product_id: TPID,
                clicked_element: btn_name,
                click_x: click_x, click_y: click_y,
                time_to_first_click_ms: time_to_click
            });

            var toast = document.getElementById('toast');
            toast.classList.add('show');

            // Update cart counter in the header
            var cartCount = document.getElementById('cart-count');
            if (cartCount) {
                var current = parseInt(cartCount.textContent.replace(/\D/g, '')) || 0;
                cartCount.textContent = '(' + (current + 1) + ')';
            }

            setTimeout(function() { toast.classList.remove('show'); }, 2000);
            setTimeout(function() { clicked = false; }, 300);
        }
    });
});
</script>
JS;
    closePage();
}

// Decide whether to show the catalog or a product detail page
function renderTask(int $step, string $sid): void {
    $viewing = $_SESSION['task_product_view'] ?? null;
    if ($viewing !== null) {
        renderTaskProduct($step, (int)$viewing, $sid);
    } else {
        renderTaskCatalog($step, $sid);
    }
}

// Distraction tasks (steps 2 and 4)
function renderDistraction(int $step, string $sid): void {
    global $PRODUCTS;

    $msg = ($step === 2) ? t('dist_cheap') : t('dist_reviews');

    openPage(t('browse_title'));
    siteHeader('catalog');
    sessionBadge($sid);
    taskBanner($msg, $step);

    echo '<div class="section">';
    echo '<div class="section-header">';
    echo '<div class="section-title">' . esc(sprintf(t('all_products'), count($PRODUCTS))) . '</div>';
    echo '<div class="filter-bar">';
    echo '<span style="font-size:.8rem;color:var(--muted);align-self:center">' . esc(t('sort_label')) . '</span>';
    foreach ([
        ['default',    t('sort_default')],
        ['price_asc',  t('sort_price_asc')],
        ['price_desc', t('sort_price_desc')],
        ['reviews',    t('sort_reviews')],
        ['rating',     t('sort_rating')],
    ] as [$key, $label]) {
        $active = $key === 'default' ? ' active' : '';
        echo '<button class="filter-chip sort-chip' . $active . '" data-sort="' . esc($key) . '" type="button">' . esc($label) . '</button>';
    }
    echo '</div>';
    echo '</div>';
    echo '<div class="product-grid">';
    foreach (getOrderedProducts() as $p) {
        echo '<button class="p-card" data-pid="' . (int)$p['id'] . '" data-cat="' . esc($p['category']) . '" data-name="' . esc($p['name']) . '" data-price="' . $p['price'] . '" data-reviews="' . (int)$p['reviews'] . '" data-rating="' . $p['rating'] . '" onclick="distractionClick(this,event)">';
        echo '<div class="p-card-img" style="background:' . esc($p['color']) . '" data-icon="' . esc($p['icon']) . '">';
        echo '<img src="images/' . (int)$p['id'] . '.png" alt="' . esc($p['name']) . '" loading="lazy" onerror="var d=this.parentElement;d.textContent=d.dataset.icon;">';
        echo '</div>';
        echo '<div class="p-card-body">';
        echo '<div class="p-card-cat">' . esc(tCat($p['category'])) . '</div>';
        echo '<div class="p-card-name">' . esc($p['name']) . '</div>';
        echo '<div class="p-card-stars">' . starsHtml($p['rating']) . '</div>';
        echo '<div class="p-card-row">';
        echo '<span class="p-card-price">€' . number_format($p['price'], 2) . '</span>';
        echo '<span class="p-card-rev">(' . number_format($p['reviews']) . ' ' . esc(t('reviews')) . ')</span>';
        echo '</div></div></button>';
    }
    echo '</div></div>';

    siteFooter();

    $js_step = $step;
    $js_sid  = htmlspecialchars($sid, ENT_QUOTES | ENT_HTML5, 'UTF-8');

    echo <<<JS
<script>
var t0   = performance.now();
var SID  = "{$js_sid}";
var STEP = {$js_step};
var done = false;

function sendEvent(data) {
    data.session_id = SID;
    fetch('log.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    });
}

function sendBeaconEvent(data) {
    data.session_id = SID;
    navigator.sendBeacon('log.php', new Blob([JSON.stringify(data)], { type: 'application/json' }));
}

sendEvent({ event_type: 'task_start', step: STEP });
sendEvent({ event_type: 'page_enter', step: STEP });

function onUnload() {
    sendBeaconEvent({ event_type: 'page_exit', step: STEP, ms_page: Math.round(performance.now() - t0) });
}
window.addEventListener('beforeunload', onUnload);

window.distractionClick = function(btn, e) {
    if (done) return;
    done = true;
    var name    = btn.dataset.name;
    var click_x = Math.round(e.clientX);
    var click_y = Math.round(e.clientY);
    window.removeEventListener('beforeunload', onUnload);
    sendBeaconEvent({ event_type: 'distraction_click', step: STEP, clicked_element: name, click_x: click_x, click_y: click_y });
    sendBeaconEvent({ event_type: 'page_exit', step: STEP, ms_page: Math.round(performance.now() - t0) });

    var form  = document.createElement('form');
    form.method = 'POST';
    form.action = 'index.php';
    var input = document.createElement('input');
    input.type  = 'hidden';
    input.name  = 'action';
    input.value = 'advance';
    form.appendChild(input);
    document.body.appendChild(form);
    form.submit();
};

// Category filter, sort and search
var activeFilter = 'All';
var activeSort   = 'default';
var originalOrder = null;

function applyFilters() {
    var query = (document.getElementById('search-input').value || '').toLowerCase().trim();
    document.querySelectorAll('.p-card').forEach(function(card) {
        var catMatch  = activeFilter === 'All' || card.dataset.cat === activeFilter;
        var nameMatch = !query || card.dataset.name.toLowerCase().indexOf(query) !== -1;
        card.style.display = (catMatch && nameMatch) ? '' : 'none';
    });
}

function applySortOrder() {
    var grid = document.querySelector('.product-grid');
    if (!originalOrder) {
        originalOrder = Array.from(grid.children);
    }
    var items = activeSort === 'default'
        ? originalOrder.slice()
        : Array.from(grid.children).sort(function(a, b) {
            if (activeSort === 'price_asc')  return parseFloat(a.dataset.price)  - parseFloat(b.dataset.price);
            if (activeSort === 'price_desc') return parseFloat(b.dataset.price)  - parseFloat(a.dataset.price);
            if (activeSort === 'reviews')    return parseInt(b.dataset.reviews)   - parseInt(a.dataset.reviews);
            if (activeSort === 'rating')     return parseFloat(b.dataset.rating) - parseFloat(a.dataset.rating);
            return 0;
        });
    items.forEach(function(item) { grid.appendChild(item); });
}

function setFilter(cat) {
    activeFilter = cat;
    document.querySelectorAll('.filter-chip:not(.sort-chip)').forEach(function(chip) {
        chip.classList.toggle('active', chip.dataset.filter === cat);
    });
    document.querySelectorAll('.cat-link[data-filter]').forEach(function(link) {
        link.classList.toggle('active', link.dataset.filter === cat);
    });
    applyFilters();
}

function setSort(key) {
    activeSort = key;
    document.querySelectorAll('.sort-chip').forEach(function(chip) {
        chip.classList.toggle('active', chip.dataset.sort === key);
    });
    applySortOrder();
    applyFilters();
}

document.querySelectorAll('.filter-chip[data-filter]').forEach(function(chip) {
    chip.addEventListener('click', function() { setFilter(this.dataset.filter); });
});
document.querySelectorAll('.cat-link[data-filter]').forEach(function(link) {
    link.addEventListener('click', function(e) {
        e.preventDefault();
        setFilter(this.dataset.filter);
    });
});
document.querySelectorAll('.sort-chip').forEach(function(chip) {
    chip.addEventListener('click', function() { setSort(this.dataset.sort); });
});
document.getElementById('search-input').addEventListener('input', applyFilters);

setFilter('All');

window.goHome = function() {
    document.getElementById('search-input').value = '';
    setFilter('All');
    window.scrollTo({ top: 0, behavior: 'smooth' });
};
</script>
JS;
    closePage();
}

// Survey page (step 6)
function renderSurvey(string $sid): void {
    global $SURVEY_QUESTIONS;
    $lang = $_SESSION['lang'] ?? 'en';

    openPage(t('survey_h'));
    siteHeader('catalog');
    sessionBadge($sid);
    taskBanner(t('survey_banner'), 6);

    echo '<div class="survey-wrap"><div class="survey-card">';
    echo '<h2>' . esc(t('survey_h')) . '</h2>';
    echo '<p class="sub">' . esc(t('survey_sub')) . '</p>';
    echo '<form method="POST" action="index.php" id="sform">';
    echo '<input type="hidden" name="action" value="submit_survey">';

    foreach ($SURVEY_QUESTIONS as $i => $q) {
        $num  = $i + 1;
        $text = isset($q['text'][$lang]) ? $q['text'][$lang] : $q['text']['en'];
        $opts = isset($q['options'][$lang]) ? $q['options'][$lang] : $q['options']['en'];

        echo '<div class="q-block">';
        echo '<div class="q-text">' . $num . '. ' . esc($text) . '</div>';
        echo '<div class="q-opts">';
        foreach ($opts as $val => $label) {
            $field_name = 'q_' . $q['id'];
            $field_id   = $field_name . '_' . $val;
            echo '<div class="q-opt">';
            echo '<label for="' . esc($field_id) . '">';
            echo '<input type="radio" name="' . esc($field_name) . '" id="' . esc($field_id) . '" value="' . esc((string)$val) . '" required>';
            echo '<span>' . esc($label) . '</span>';
            echo '</label>';
            echo '</div>';
        }
        echo '</div>';
        echo '</div>';
    }

    echo '<button type="submit" class="btn-submit">' . esc(t('submit_btn')) . '</button>';
    echo '</form></div></div>';
    siteFooter();

    $js_sid   = htmlspecialchars($sid, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $js_alert = htmlspecialchars(t('survey_alert'), ENT_QUOTES | ENT_HTML5, 'UTF-8');

    echo <<<JS
<script>
var SID = "{$js_sid}";

fetch('log.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ session_id: SID, event_type: 'page_enter', step: 6 })
});

document.getElementById('sform').addEventListener('submit', function(e) {
    var questions = this.querySelectorAll('.q-block');
    for (var i = 0; i < questions.length; i++) {
        if (!questions[i].querySelector('input[type=radio]:checked')) {
            e.preventDefault();
            alert('{$js_alert}');
            return;
        }
    }
});
</script>
JS;
    closePage();
}

// End / thank-you page
function renderEnd(string $sid): void {
    openPage(t('done_h'));

    $safe_sid  = esc($sid);
    $js_sid    = htmlspecialchars($sid, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $q_url     = esc(QUALTRICS_URL);
    $js_copy   = htmlspecialchars(t('copy_btn'), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $js_copied = htmlspecialchars(t('copied'), ENT_QUOTES | ENT_HTML5, 'UTF-8');

    echo '<div class="pg-center"><div class="start-card">';
    echo '<div style="font-size:2.5rem;margin-bottom:.5rem">✅</div>';
    echo '<h1>' . esc(t('done_h')) . '</h1>';
    echo '<p>' . esc(t('done_p')) . '</p>';
    echo '<div class="sid-box">';
    echo '<div class="sid-label">' . esc(t('sid_label')) . '</div>';
    echo '<div class="sid-value" id="sid-val">' . $safe_sid . '</div>';
    echo '</div>';
    echo '<button class="btn-primary" onclick="copyId()" id="copy-btn">' . esc(t('copy_btn')) . '</button>';
    echo '<a class="btn-secondary" href="' . $q_url . '">' . esc(t('return_btn')) . '</a>';
    echo '</div></div>';

    echo <<<JS
<script>
var SID          = "{$js_sid}";
var COPY_LABEL   = "{$js_copy}";
var COPIED_LABEL = "{$js_copied}";

fetch('log.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ session_id: SID, event_type: 'page_enter', step: 8 })
});

function copyId() {
    var sid = document.getElementById('sid-val').innerText.trim();
    var btn = document.getElementById('copy-btn');

    function showCopied() {
        btn.textContent = COPIED_LABEL;
        btn.style.background = '#16a34a';
        setTimeout(function() {
            btn.textContent = COPY_LABEL;
            btn.style.background = '';
        }, 2500);
    }

    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(sid).then(showCopied).catch(function() {
            fallbackCopy(sid);
            showCopied();
        });
    } else {
        fallbackCopy(sid);
        showCopied();
    }
}

function fallbackCopy(text) {
    var textarea = document.createElement('textarea');
    textarea.value = text;
    textarea.style.cssText = 'position:fixed;opacity:0';
    document.body.appendChild(textarea);
    textarea.select();
    try { document.execCommand('copy'); } catch (e) {}
    document.body.removeChild(textarea);
}
</script>
JS;
    closePage();
}

// --- MAIN RENDER ---

if ($step === 0) {
    renderStart($sid);
} elseif ($intr) {
    renderInterstitial($step);
} elseif ($step === 1) {
    renderTask(1, $sid);
} elseif ($step === 2) {
    renderDistraction(2, $sid);
} elseif ($step === 3) {
    renderTask(3, $sid);
} elseif ($step === 4) {
    renderDistraction(4, $sid);
} elseif ($step === 5) {
    renderTask(5, $sid);
} elseif ($step === 6) {
    renderSurvey($sid);
} elseif ($step === 7) {
    renderTask(7, $sid);
} elseif ($step === 8) {
    renderEnd($sid);
} else {
    session_destroy();
    header('Location: index.php');
    exit;
}
