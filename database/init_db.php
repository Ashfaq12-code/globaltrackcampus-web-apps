<?php
/**
 * GlobeTrek Adventures - Database Initialization & Seed Runner
 * Can be run via CLI: php database/init_db.php
 * Or via web browser by navigating to /database/init_db.php
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';

$pdo = get_db_connection();

echo "Initializing GlobeTrek Adventures Database...\n";

// 1. Read and execute Schema
$schemaSql = file_get_contents(__DIR__ . '/schema.sql');
$pdo->exec($schemaSql);
echo "✓ Schema tables created successfully.\n";

// 2. Insert Seed Users with dynamically generated password hashes
$users = [
    [
        'full_name' => 'System Administrator',
        'email' => 'admin@globetrek.com',
        'password' => 'admin123',
        'role' => 'admin',
        'phone' => '+94 11 234 5678',
        'address' => 'HQ Colombo 03, Sri Lanka',
        'avatar' => 'admin-avatar.png'
    ],
    [
        'full_name' => 'Operational Staff (Dhammika)',
        'email' => 'staff@globetrek.com',
        'password' => 'staff123',
        'role' => 'staff',
        'phone' => '+94 77 123 4567',
        'address' => 'Operations Hub, Kandy, Sri Lanka',
        'avatar' => 'staff-avatar.png'
    ],
    [
        'full_name' => 'Kavinda Perera (Tour Coordinator)',
        'email' => 'kavinda@globetrek.com',
        'password' => 'staff123',
        'role' => 'staff',
        'phone' => '+94 71 987 6543',
        'address' => 'Southern Branch, Galle',
        'avatar' => 'staff2-avatar.png'
    ],
    [
        'full_name' => 'John Doe (Traveler)',
        'email' => 'customer@globetrek.com',
        'password' => 'customer123',
        'role' => 'customer',
        'phone' => '+1 415 555 2671',
        'address' => 'San Francisco, CA, USA',
        'avatar' => 'john-avatar.png'
    ],
    [
        'full_name' => 'Sarah Miller',
        'email' => 'sarah.miller@example.com',
        'password' => 'customer123',
        'role' => 'customer',
        'phone' => '+44 20 7946 0912',
        'address' => 'London, United Kingdom',
        'avatar' => 'sarah-avatar.png'
    ],
    [
        'full_name' => 'Kenji Sato',
        'email' => 'kenji.sato@example.com',
        'password' => 'customer123',
        'role' => 'customer',
        'phone' => '+81 3 5555 0143',
        'address' => 'Tokyo, Japan',
        'avatar' => 'kenji-avatar.png'
    ]
];

$stmtUser = $pdo->prepare("INSERT INTO users (full_name, email, password_hash, role, phone, address, avatar, is_active, created_at) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, 1, datetime('now'))");

foreach ($users as $u) {
    $hash = password_hash($u['password'], PASSWORD_DEFAULT);
    $stmtUser->execute([$u['full_name'], $u['email'], $hash, $u['role'], $u['phone'], $u['address'], $u['avatar']]);
}
echo "✓ Users seeded (Admin, Staff, Customer accounts).\n";

// 3. Destinations
$destinations = [
    [
        'name' => 'Ella & Central Highlands',
        'region' => 'Badulla District, Uva Province',
        'description' => 'A tranquil hillside haven famous for panoramic mountain gaps, lush emerald tea plantations, and the world-renowned Nine Arch Bridge.',
        'highlights' => 'Nine Arch Demodara Bridge, Little Adam\'s Peak, Ravana Falls, Flying Ravana Zipline, Ella Rock',
        'image_url' => 'assets/img/destinations/ella.jpg',
        'best_season' => 'December to March / July to September'
    ],
    [
        'name' => 'Sacred City of Kandy',
        'region' => 'Central Province',
        'description' => 'The cultural and spiritual heart of Sri Lanka, home to the revered Temple of the Sacred Tooth Relic, serene Kandy Lake, and royal botanical gardens.',
        'highlights' => 'Temple of the Tooth Relic, Royal Botanical Gardens Peradeniya, Kandy Cultural Dance Show, Bahirawakanda Buddha',
        'image_url' => 'assets/img/destinations/kandy.jpg',
        'best_season' => 'January to April / August (Esala Perahera)'
    ],
    [
        'name' => 'Sigiriya & Dambulla',
        'region' => 'Cultural Triangle, Matale District',
        'description' => 'The 8th Wonder of the World: King Kashyapa\'s towering 5th-century Lion Rock fortress surrounded by ancient water gardens and fresco paintings.',
        'highlights' => 'Sigiriya Lion Rock Fortress, Pidurangala Sunrise Hike, Dambulla Golden Cave Temple, Minneriya Elephant Gathering',
        'image_url' => 'assets/img/destinations/sigiriya.jpg',
        'best_season' => 'May to September (Dry Season)'
    ],
    [
        'name' => 'Galle Dutch Fort & Coast',
        'region' => 'Southern Province',
        'description' => 'A vibrant 16th-century UNESCO World Heritage fortress blending Portuguese, Dutch, and British colonial history with boutique cafes and ocean ramparts.',
        'highlights' => 'Galle Lighthouse, Historic Dutch Fort Ramparts, Maritime Museum, Unawatuna Beach, Stilt Fishermen of Weligama',
        'image_url' => 'assets/img/destinations/galle.jpg',
        'best_season' => 'November to April'
    ],
    [
        'name' => 'Nuwara Eliya (Little England)',
        'region' => 'Central Highlands',
        'description' => 'Cool misty hill country known for Victorian architecture, sprawling Pedro tea estates, Horton Plains National Park, and World\'s End drop.',
        'highlights' => 'Horton Plains & World\'s End, Pedro Tea Estate, Gregory Lake Water Sports, Victoria Park, Single Tree Hill',
        'image_url' => 'assets/img/destinations/nuwara-eliya.jpg',
        'best_season' => 'February to May'
    ],
    [
        'name' => 'Mirissa & Southern Beaches',
        'region' => 'Southern Coast',
        'description' => 'Tropical paradise famous for Blue Whale watching expeditions, surfing at Coconut Tree Hill, and sunset seafood dining along crescent bays.',
        'highlights' => 'Whale & Dolphin Watching, Coconut Tree Hill, Parrot Rock, Secret Beach, Sunset Surfing',
        'image_url' => 'assets/img/destinations/mirissa.jpg',
        'best_season' => 'November to April'
    ],
    [
        'name' => 'Yala National Park Wildlife',
        'region' => 'Southern & Uva Provinces',
        'description' => 'Sri Lanka\'s premier wildlife sanctuary boasting the world\'s highest density of wild leopards, Asian elephants, sloth bears, and exotic birds.',
        'highlights' => '4x4 Leopard Safari, Elephant Gathering at Waterholes, Sloth Bear Tracking, Bird Watching, Kumana Boundary',
        'image_url' => 'assets/img/destinations/yala.jpg',
        'best_season' => 'February to June'
    ]
];

$stmtDest = $pdo->prepare("INSERT INTO destinations (name, region, description, highlights, image_url, best_season) 
                           VALUES (?, ?, ?, ?, ?, ?)");
foreach ($destinations as $d) {
    $stmtDest->execute([$d['name'], $d['region'], $d['description'], $d['highlights'], $d['image_url'], $d['best_season']]);
}
echo "✓ Destinations seeded.\n";

// 4. Tour Packages
$packages = [
    [
        'title' => 'Ella Mountain Mist & Ridge Adventure',
        'destination_id' => 1,
        'duration_days' => 3,
        'duration_nights' => 2,
        'base_price' => 250.00,
        'difficulty' => 'Moderate',
        'category' => 'Adventure',
        'is_featured' => 1,
        'image_url' => 'assets/img/tours/ella-adventure.jpg',
        'short_desc' => '3 Days / 2 Nights traversing the iconic Nine Arch Bridge, Little Adam\'s Peak summit, and cascading Ravana Waterfalls with mountain guide.',
        'full_desc' => 'Experience the breathtaking beauty of Sri Lanka\'s central hill country. This 3-day adventure package takes you along misty ridges, across historic colonial railways, through verdant tea carpet estates, and down into lush jungle waterfalls. Ideal for nature enthusiasts, photographers, and active adventurers seeking authentic highland hospitality.',
        'status' => 'Active'
    ],
    [
        'title' => 'Sacred Kandy Cultural Heritage & Royal Gardens',
        'destination_id' => 2,
        'duration_days' => 2,
        'duration_nights' => 1,
        'base_price' => 190.00,
        'difficulty' => 'Easy',
        'category' => 'Cultural',
        'is_featured' => 1,
        'image_url' => 'assets/img/tours/kandy-cultural.jpg',
        'short_desc' => '2 Days / 1 Night exploring the Temple of the Sacred Tooth, Royal Botanical Gardens, and vibrant traditional Kandyan fire dances.',
        'full_desc' => 'Immerse yourself in Sri Lanka\'s last royal kingdom. Visit the sacred Dalada Maligawa (Temple of the Tooth Relic), stroll through 147 acres of tropical flora at Peradeniya Royal Botanic Gardens, witness dazzling Kandyan drumming and fire walking, and enjoy an evening stroll around the picturesque Kandy Lake.',
        'status' => 'Active'
    ],
    [
        'title' => 'Sigiriya Lion Rock Citadel & Minneriya Safari',
        'destination_id' => 3,
        'duration_days' => 3,
        'duration_nights' => 2,
        'base_price' => 310.00,
        'difficulty' => 'Moderate',
        'category' => 'Adventure',
        'is_featured' => 1,
        'image_url' => 'assets/img/tours/sigiriya-rock.jpg',
        'short_desc' => '3 Days / 2 Nights conquering the 5th-century fortress of Sigiriya, Dambulla cave frescoes, and a 4x4 open-top elephant safari in Minneriya.',
        'full_desc' => 'Scale the monumental granite monolith of Sigiriya, marvel at ancient fresco masterpieces, and climb Pidurangala Rock for an unforgettable sunrise panorama. Complete your journey with an exhilarating private jeep safari witnessing the famed Elephant Gathering at Minneriya National Park reservoir.',
        'status' => 'Active'
    ],
    [
        'title' => 'Galle Dutch Colonial Ramparts & Coastal Escape',
        'destination_id' => 4,
        'duration_days' => 2,
        'duration_nights' => 1,
        'base_price' => 220.00,
        'difficulty' => 'Easy',
        'category' => 'Coastal',
        'is_featured' => 1,
        'image_url' => 'assets/img/tours/galle-fort.jpg',
        'short_desc' => '2 Days / 1 Night discovering UNESCO Dutch Fort cobblestone alleys, lighthouse sunsets, stilt fishermen, and turtle conservation.',
        'full_desc' => 'Step back into maritime history within the fortified ramparts of Galle Fort. Discover artisanal spice and gem boutiques, dine in Dutch colonial courtyards, watch traditional stilt fishermen in Koggala, and visit a sea turtle rehabilitation sanctuary on the southern shoreline.',
        'status' => 'Active'
    ],
    [
        'title' => 'Nuwara Eliya Tea Trails & World\'s End Trek',
        'destination_id' => 5,
        'duration_days' => 4,
        'duration_nights' => 3,
        'base_price' => 380.00,
        'difficulty' => 'Challenging',
        'category' => 'Nature',
        'is_featured' => 0,
        'image_url' => 'assets/img/tours/nuwara-eliya-tea.jpg',
        'short_desc' => '4 Days / 3 Nights through misty tea estates, Horton Plains cloud forest trek, Baker\'s Falls, and colonial afternoon tea.',
        'full_desc' => 'Journey to Sri Lanka\'s highest elevated sanctuary. Hike across the mystical plateau of Horton Plains to gaze over the dramatic 880-meter vertical cliff at World\'s End. Pluck pure Ceylon tea alongside local tea masters and enjoy luxury high tea at historic colonial estates.',
        'status' => 'Active'
    ],
    [
        'title' => 'Mirissa Blue Whale Cruise & Tropical Beach Haven',
        'destination_id' => 6,
        'duration_days' => 3,
        'duration_nights' => 2,
        'base_price' => 290.00,
        'difficulty' => 'Easy',
        'category' => 'Coastal',
        'is_featured' => 0,
        'image_url' => 'assets/img/tours/mirissa-beach.jpg',
        'short_desc' => '3 Days / 2 Nights ocean safari for Blue Whales & spinner dolphins, surfing lessons, and vibrant beachfront sunset barbecues.',
        'full_desc' => 'Embark on a luxury catamaran cruise into the deep Indian Ocean to observe the magnificent Blue Whale—the largest mammal on Earth. Relax under palm canopies at Coconut Tree Hill and savor fresh grilled seafood as waves lap the sand.',
        'status' => 'Active'
    ],
    [
        'title' => 'Wild Yala Leopard & Sloth Bear Safari Expedition',
        'destination_id' => 7,
        'duration_days' => 2,
        'duration_nights' => 1,
        'base_price' => 340.00,
        'difficulty' => 'Moderate',
        'category' => 'Wildlife',
        'is_featured' => 0,
        'image_url' => 'assets/img/tours/yala-safari.jpg',
        'short_desc' => '2 Days / 1 Night with 2 private 4x4 guided game drives targeting the elusive Sri Lankan Leopard, sloth bears, and crocodiles.',
        'full_desc' => 'Venture into the heart of Yala\'s diverse ecosystem comprising scrub jungle, lagoons, and rocky outcroppings. Accompanied by certified naturalist trackers, track the endemic Sri Lankan Leopard (*Panthera pardus kotiya*) during golden hour safari drives.',
        'status' => 'Active'
    ],
    [
        'title' => 'Grand Ceylon Odyssey: Heritage, Highlands & Coast',
        'destination_id' => 3,
        'duration_days' => 7,
        'duration_nights' => 6,
        'base_price' => 850.00,
        'difficulty' => 'Moderate',
        'category' => 'Cultural',
        'is_featured' => 1,
        'image_url' => 'assets/img/tours/grand-ceylon.jpg',
        'short_desc' => '7 Days / 6 Nights ultimate all-inclusive voyage combining Sigiriya, Kandy, Nuwara Eliya tea trains, Ella, and Galle Fort.',
        'full_desc' => 'The definitive Sri Lankan exploration. Seamlessly connects ancient UNESCO kingdoms, scenic hill-country mountain train rides, tea plantation bungalows, wildlife safaris, and sun-kissed southern coastal ramparts in one masterfully curated week.',
        'status' => 'Active'
    ]
];

$stmtPkg = $pdo->prepare("INSERT INTO tour_packages (title, destination_id, duration_days, duration_nights, base_price, difficulty, category, is_featured, image_url, short_desc, full_desc, status) 
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
foreach ($packages as $p) {
    $stmtPkg->execute([
        $p['title'], $p['destination_id'], $p['duration_days'], $p['duration_nights'], $p['base_price'],
        $p['difficulty'], $p['category'], $p['is_featured'], $p['image_url'], $p['short_desc'], $p['full_desc'], $p['status']
    ]);
}
echo "✓ Tour packages seeded.\n";

// 5. Itineraries
$itineraries = [
    // Ella Package (ID 1)
    [1, 1, 'Arrival in Ella & Little Adam\'s Peak Sunset', 'Arrive in picturesque Ella, check into your scenic hotel, and embark on a mild afternoon trek up Little Adam\'s Peak for sweeping golden hour views across Ella Gap.'],
    [1, 2, 'Nine Arch Bridge & Tea Plantation Walk', 'Early morning photography session at the historic Nine Arch Viaduct train bridge. Followed by a walk through lush tea gardens and an afternoon swim at Ravana Falls.'],
    [1, 3, 'Flying Ravana Mega Zipline & Departure', 'Morning thrills at the Flying Ravana adventure park (zipline over tea canopy), traditional Sri Lankan rice & curry lunch, and departure transfer.'],
    
    // Kandy Package (ID 2)
    [2, 1, 'Sacred Tooth Relic Temple & Cultural Show', 'Afternoon arrival in Kandy. Visit the golden-roofed Temple of the Sacred Tooth Relic during the evening *Thevava* ceremony, followed by traditional Kandyan drums and fire dancing.'],
    [2, 2, 'Royal Botanical Gardens & Ceylon Spice Grove', 'Morning guided tour through Peradeniya Royal Botanic Gardens with its famous orchid house and palm avenue. Afternoon tea tasting before journey onward.'],
    
    // Sigiriya Package (ID 3)
    [3, 1, 'Dambulla Golden Cave Temples', 'Explore the 5 ancient rock-carved sanctuaries adorned with 150+ Buddha statues and centuries-old ceiling murals.'],
    [3, 2, 'Sigiriya Rock Fortress & Village Bullock Cart', 'Climb the 1,200 steps of the 5th-century Lion Rock citadel in early morning. Enjoy a traditional lake catamaran ride and authentic village lunch.'],
    [3, 3, 'Minneriya Wild Elephant Safari & Departure', 'Open-top 4x4 safari through Minneriya National Park to observe large wild elephant herds grazing along the reservoir shoreline.']
];

$stmtItin = $pdo->prepare("INSERT INTO package_itineraries (package_id, day_number, title, description) VALUES (?, ?, ?, ?)");
foreach ($itineraries as $it) {
    $stmtItin->execute([$it[0], $it[1], $it[2], $it[3]]);
}
echo "✓ Package itineraries seeded.\n";

// 6. Accommodations
$accommodations = [
    [
        'name' => 'Mountain View Eco Lodge (Standard)',
        'destination_id' => 1,
        'tier' => 'Standard',
        'price_per_night' => 0.00, // Included in base package
        'description' => 'Comfortable eco-friendly chalet with private balcony, mountain breeze, hot shower, and complimentary organic breakfast.',
        'amenities' => 'Free Wi-Fi, Mountain View, En-suite Bathroom, Breakfast Included',
        'image_url' => 'assets/img/hotels/standard-lodge.jpg'
    ],
    [
        'name' => 'Ella Heritage Boutique Villa (Deluxe)',
        'destination_id' => 1,
        'tier' => 'Deluxe',
        'price_per_night' => 45.00,
        'description' => 'Spacious deluxe room featuring teak colonial decor, infinity plunge pool overlooking Ella gap, and gourmet dining.',
        'amenities' => 'Infinity Pool, AC, Mini Bar, King Bed, Free Wi-Fi, Gourmet Breakfast',
        'image_url' => 'assets/img/hotels/deluxe-villa.jpg'
    ],
    [
        'name' => '98 Acres Luxury Resort & Spa (Luxury Resort)',
        'destination_id' => 1,
        'tier' => 'Luxury Resort',
        'price_per_night' => 95.00,
        'description' => 'Award-winning luxury hilltop resort surrounded by tea plantations. Features private chalets made of recycled railway timber, spa, and helipad.',
        'amenities' => 'Ayurvedic Spa, Private Jacuzzi, Heli-pad, Fine Dining Restaurant, 360° Panorama',
        'image_url' => 'assets/img/hotels/luxury-resort.jpg'
    ],
    [
        'name' => 'Kandy City Garden Inn (Standard)',
        'destination_id' => 2,
        'tier' => 'Standard',
        'price_per_night' => 0.00,
        'description' => 'Charming bed & breakfast within walking distance of Kandy Lake and city center.',
        'amenities' => 'Lake View Balcony, Wi-Fi, Hot Shower, Daily Breakfast',
        'image_url' => 'assets/img/hotels/kandy-inn.jpg'
    ],
    [
        'name' => 'Earl\'s Regency Cultural Palace (Luxury Resort)',
        'destination_id' => 2,
        'tier' => 'Luxury Resort',
        'price_per_night' => 85.00,
        'description' => '5-Star luxury sanctuary nestled in the hills of Tennekumbura with riverside dining and tennis courts.',
        'amenities' => '5-Star Spa, Swimming Pool, Multi-Cuisine Dining, Gym, Luxury Suites',
        'image_url' => 'assets/img/hotels/earls-regency.jpg'
    ]
];

$stmtAcc = $pdo->prepare("INSERT INTO accommodations (name, destination_id, tier, price_per_night, description, amenities, image_url) 
                          VALUES (?, ?, ?, ?, ?, ?, ?)");
foreach ($accommodations as $a) {
    $stmtAcc->execute([$a['name'], $a['destination_id'], $a['tier'], $a['price_per_night'], $a['description'], $a['amenities'], $a['image_url']]);
}
echo "✓ Accommodations seeded.\n";

// 7. Transport Options
$transports = [
    [
        'vehicle_name' => 'Comfort Air-Conditioned Sedan',
        'vehicle_type' => 'Private Sedan Car',
        'capacity' => 3,
        'price_per_day' => 25.00,
        'features' => 'Climate Control AC, English-speaking Chauffeur Guide, Fuel & Tolls Included, Bottled Water',
        'image_url' => 'assets/img/transport/sedan.jpg'
    ],
    [
        'vehicle_name' => 'Toyota KDH Luxury High-Roof Van',
        'vehicle_type' => 'AC Luxury Van',
        'capacity' => 7,
        'price_per_day' => 45.00,
        'features' => 'Reclining Captain Seats, Dual AC, Ample Luggage Space, USB Chargers, Experienced Tour Guide',
        'image_url' => 'assets/img/transport/van.jpg'
    ],
    [
        'vehicle_name' => 'Modified 4x4 Safari Land Cruiser',
        'vehicle_type' => '4x4 Safari SUV',
        'capacity' => 6,
        'price_per_day' => 60.00,
        'features' => 'Elevated Tiered Seating, Open Viewing Canopy, Heavy-duty Suspension, Professional Wildlife Tracker',
        'image_url' => 'assets/img/transport/safari-jeep.jpg'
    ],
    [
        'vehicle_name' => 'Luxury Tourist Mini Coach',
        'vehicle_type' => 'Mini Tourist Coach',
        'capacity' => 18,
        'price_per_day' => 110.00,
        'features' => 'PA System, Overhead Luggage Racks, Tinted Panoramic Windows, Dual AC, Group Chauffeur',
        'image_url' => 'assets/img/transport/coach.jpg'
    ]
];

$stmtTrans = $pdo->prepare("INSERT INTO transport_options (vehicle_name, vehicle_type, capacity, price_per_day, features, image_url) 
                            VALUES (?, ?, ?, ?, ?, ?)");
foreach ($transports as $t) {
    $stmtTrans->execute([$t['vehicle_name'], $t['vehicle_type'], $t['capacity'], $t['price_per_day'], $t['features'], $t['image_url']]);
}
echo "✓ Transport options seeded.\n";

// 8. Package Add-ons
$addons = [
    ['Scenic Mountain First-Class Train Ticket', 25.00, 'Reserved panoramic view seat along the famed Kandy to Ella mountain railway line.', 'train'],
    ['Guided Whale & Dolphin Catamaran Cruise', 45.00, '3-hour morning ocean expedition with marine biologist and onboard breakfast.', 'ship'],
    ['Authentic Clay Pot Cooking Masterclass', 30.00, 'Hands-on culinary session learning 5 Sri Lankan organic curries and coconut sambol.', 'utensils'],
    ['Ayurvedic Full-Body Herbal Spa Therapy', 50.00, '90-minute rejuvenating herbal oil massage and steam bath at licensed wellness center.', 'spa'],
    ['Yala Safari Park Jeep & Entry Pass', 55.00, 'Reserved 4x4 safari jeep with national park conservation permits and tracker.', 'paw']
];

$stmtAddon = $pdo->prepare("INSERT INTO package_addons (name, price, description, icon) VALUES (?, ?, ?, ?)");
foreach ($addons as $ad) {
    $stmtAddon->execute([$ad[0], $ad[1], $ad[2], $ad[3]]);
}
echo "✓ Package Add-ons seeded.\n";

// 9. Pre-populate Bookings & Payments
$bookings = [
    [
        'booking_code' => 'GT-2026-00124',
        'user_id' => 4, // John Doe
        'package_id' => 1, // Ella Adventure
        'travel_date' => date('Y-m-d', strtotime('+14 days')),
        'travelers_count' => 2,
        'accommodation_id' => 2, // Deluxe (45 * 2 nights = 90)
        'transport_id' => 2, // Van (45 * 3 days = 135)
        'add_ons_json' => json_encode(['Scenic Mountain First-Class Train Ticket' => 25.00]),
        'custom_notes' => 'Vegetarian meal preference for one guest.',
        'base_price' => 500.00, // 250 * 2
        'hotel_price' => 90.00,
        'transport_price' => 135.00,
        'addons_price' => 25.00,
        'total_amount' => 750.00,
        'payment_status' => 'Paid',
        'booking_status' => 'Confirmed',
        'created_at' => date('Y-m-d H:i:s', strtotime('-2 days'))
    ],
    [
        'booking_code' => 'GT-2026-00125',
        'user_id' => 5, // Sarah Miller
        'package_id' => 3, // Sigiriya
        'travel_date' => date('Y-m-d', strtotime('+30 days')),
        'travelers_count' => 1,
        'accommodation_id' => 1,
        'transport_id' => 1,
        'add_ons_json' => json_encode(['Yala Safari Park Jeep & Entry Pass' => 55.00]),
        'custom_notes' => 'Airport pickup requested from Bandaranaike Int Airport.',
        'base_price' => 310.00,
        'hotel_price' => 0.00,
        'transport_price' => 75.00,
        'addons_price' => 55.00,
        'total_amount' => 440.00,
        'payment_status' => 'Paid',
        'booking_status' => 'Confirmed',
        'created_at' => date('Y-m-d H:i:s', strtotime('-5 days'))
    ],
    [
        'booking_code' => 'GT-2026-00126',
        'user_id' => 6, // Kenji Sato
        'package_id' => 8, // Grand Ceylon Odyssey
        'travel_date' => date('Y-m-d', strtotime('+45 days')),
        'travelers_count' => 2,
        'accommodation_id' => 3,
        'transport_id' => 2,
        'add_ons_json' => json_encode(['Ayurvedic Full-Body Herbal Spa Therapy' => 50.00]),
        'custom_notes' => 'Honeymoon arrangement with flowers in room.',
        'base_price' => 1700.00,
        'hotel_price' => 570.00,
        'transport_price' => 315.00,
        'addons_price' => 50.00,
        'total_amount' => 2635.00,
        'payment_status' => 'Paid',
        'booking_status' => 'In-Progress',
        'created_at' => date('Y-m-d H:i:s', strtotime('-1 days'))
    ]
];

$stmtBk = $pdo->prepare("INSERT INTO bookings (booking_code, user_id, package_id, travel_date, travelers_count, accommodation_id, transport_id, add_ons_json, custom_notes, base_price, hotel_price, transport_price, addons_price, total_amount, payment_status, booking_status, created_at) 
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

$stmtPay = $pdo->prepare("INSERT INTO payments (booking_id, user_id, transaction_ref, payment_method, amount, payment_status, card_last4, paid_at) 
                          VALUES (?, ?, ?, ?, ?, 'Completed', ?, ?)");

foreach ($bookings as $bk) {
    $stmtBk->execute([
        $bk['booking_code'], $bk['user_id'], $bk['package_id'], $bk['travel_date'], $bk['travelers_count'],
        $bk['accommodation_id'], $bk['transport_id'], $bk['add_ons_json'], $bk['custom_notes'],
        $bk['base_price'], $bk['hotel_price'], $bk['transport_price'], $bk['addons_price'],
        $bk['total_amount'], $bk['payment_status'], $bk['booking_status'], $bk['created_at']
    ]);
    $bookingId = $pdo->lastInsertId();

    $stmtPay->execute([
        $bookingId, $bk['user_id'], 'TXN-' . strtoupper(substr(md5(uniqid()), 0, 10)),
        'Credit Card', $bk['total_amount'], '4242', $bk['created_at']
    ]);
}
echo "✓ Bookings and Payments seeded.\n";

// 10. Customer Queries
$queries = [
    [
        'user_id' => 4,
        'guest_name' => 'John Doe',
        'guest_email' => 'customer@globetrek.com',
        'subject' => 'Airport Pickup Arrangement for Ella Tour',
        'message' => 'Hello team, we will be landing at Colombo CMB Airport at 8:30 AM on the morning of our Ella tour. Can our private driver meet us at the arrivals hall with a name board?',
        'status' => 'Answered',
        'staff_reply' => 'Dear John, Absolutely! Our English-speaking chauffeur guide will be waiting in the arrival lobby holding a GlobeTrek Adventures name board with your name. Safe travels!',
        'replied_by' => 2
    ],
    [
        'user_id' => 5,
        'guest_name' => 'Sarah Miller',
        'guest_email' => 'sarah.miller@example.com',
        'subject' => 'Customization for 4-day Sigiriya package',
        'message' => 'Is it possible to add a sunrise hot air balloon flight over Dambulla to Day 2 of our Sigiriya booking?',
        'status' => 'Pending',
        'staff_reply' => null,
        'replied_by' => null
    ],
    [
        'user_id' => null,
        'guest_name' => 'Michael Chang',
        'guest_email' => 'mchang@example.com',
        'subject' => 'Family group travel discount inquiry',
        'message' => 'We are planning a trip for 8 adults and 3 children in December 2026. Do you offer corporate/family tier discounts for custom itineraries?',
        'status' => 'In Review',
        'staff_reply' => 'Hi Michael, yes we offer tailored group discounts for parties of 6 or more! Our staff will prepare a customized quote for you shortly.',
        'replied_by' => 3
    ]
];

$stmtQ = $pdo->prepare("INSERT INTO queries (user_id, guest_name, guest_email, subject, message, status, staff_reply, replied_by, created_at, updated_at) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, datetime('now'), datetime('now'))");
foreach ($queries as $q) {
    $stmtQ->execute([$q['user_id'], $q['guest_name'], $q['guest_email'], $q['subject'], $q['message'], $q['status'], $q['staff_reply'], $q['replied_by']]);
}
echo "✓ Customer queries seeded.\n";

// 11. Customer Reviews
$reviews = [
    [1, 4, 5, 'Our Ella mountain tour was extraordinary! The sunrise hike at Little Adam\'s Peak and the luxury tea lodge blew us away. Chauffeur guide was extremely knowledgeable.'],
    [2, 5, 5, 'Kandy cultural tour was majestic. Seeing the Temple of the Tooth and experiencing the royal botanical gardens was a highlight of my Asia trip.'],
    [3, 6, 4, 'Sigiriya fortress climb was breathtaking. Minneriya safari let us see over 80 wild elephants up close. Seamless booking experience!']
];

$stmtRev = $pdo->prepare("INSERT INTO reviews (package_id, user_id, rating, comment, is_approved) VALUES (?, ?, ?, ?, 1)");
foreach ($reviews as $rv) {
    $stmtRev->execute([$rv[0], $rv[1], $rv[2], $rv[3]]);
}
echo "✓ Reviews seeded.\n";

// 12. Travel Guides
$guides = [
    [
        'title' => 'Complete First-Timer Guide to Sri Lanka: Visas, Currency & Etiquette',
        'destination' => 'Island-Wide',
        'category' => 'Essential Tips',
        'read_time' => '6 min read',
        'snippet' => 'Everything you need to know before visiting the Pearl of the Indian Ocean, including ETA visa steps, local currency exchange, and cultural customs.',
        'content' => 'Sri Lanka welcomes international travelers with warm hospitality. When visiting religious sites like the Temple of the Tooth in Kandy or Dambulla Cave Temples, always cover shoulders and knees, and remove footwear. The local currency is Sri Lankan Rupee (LKR), though USD is widely accepted for tourism bookings.',
        'image_url' => 'assets/img/guides/guide-1.jpg'
    ],
    [
        'title' => 'The Scenic Kandy to Ella Mountain Train: Route, Tickets & Secrets',
        'destination' => 'Central Highlands',
        'category' => 'Transport Guide',
        'read_time' => '5 min read',
        'snippet' => 'Ranked among the world’s top 10 most scenic railway journeys. Here is how to book tickets, choose the right carriage, and catch the best views.',
        'content' => 'The iconic blue train traversing through misty cloud forests, eucalyptus groves, and roaring waterfalls is a bucket-list dream. For the most dramatic valley views, sit on the right side from Kandy to Nanu Oya, and switch to the left side as you approach Ella.',
        'image_url' => 'assets/img/guides/guide-2.jpg'
    ],
    [
        'title' => 'Best Time to Visit Sri Lanka: Understanding the Two Monsoon Seasons',
        'destination' => 'Climate & Weather',
        'category' => 'Weather Planning',
        'read_time' => '4 min read',
        'snippet' => 'Sri Lanka is a year-round destination thanks to its dual monsoon climate. Learn which coast is sunny when you travel.',
        'content' => 'When the south and west coasts experience the Yala monsoon (May to September), the east coast (Arugam Bay, Trincomalee) and Cultural Triangle enjoy sunny, dry skies. From December to April, the south coast (Galle, Mirissa) and hill country (Ella, Kandy) are bathed in golden sunshine.',
        'image_url' => 'assets/img/guides/guide-3.jpg'
    ]
];

$stmtG = $pdo->prepare("INSERT INTO travel_guides (title, destination, category, read_time, snippet, content, image_url) VALUES (?, ?, ?, ?, ?, ?, ?)");
foreach ($guides as $g) {
    $stmtG->execute([$g['title'], $g['destination'], $g['category'], $g['read_time'], $g['snippet'], $g['content'], $g['image_url']]);
}
echo "✓ Travel guides seeded.\n";

echo "==================================================\n";
echo "GlobeTrek Database Initialized and Seeded Successfully!\n";
echo "Demo Accounts:\n";
echo "  - Administrator : admin@globetrek.com     / admin123\n";
echo "  - Staff Member  : staff@globetrek.com     / staff123\n";
echo "  - Customer      : customer@globetrek.com  / customer123\n";
echo "==================================================\n";
