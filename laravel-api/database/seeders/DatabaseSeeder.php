<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\PriceCategory;
use App\Models\PriceItem;
use App\Models\PriceHistory;
use App\Models\Complaint;
use App\Models\ComplaintStatusLog;
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AdminUserSeeder::class,
            PriceCategorySeeder::class,
            PriceItemSeeder::class,
        ]);

        $this->seedInitialStatusLogs();
    }

    private function seedInitialStatusLogs(): void
    {
        $complaints = Complaint::doesntHave('statusLogs')->get();
        foreach ($complaints as $c) {
            ComplaintStatusLog::create([
                'complaint_id' => $c->id,
                'old_status'   => null,
                'new_status'   => $c->status,
                'remarks'      => 'Initial complaint filed',
                'created_at'   => $c->created_at,
            ]);
        }
    }
}

// ════════════════════════════════════════════════════════
//  AdminUserSeeder
// ════════════════════════════════════════════════════════
class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['username' => 'admin'],
            [
                'name'     => 'Admin User',
                'cnic'     => '42201-0000000-1',
                'mobile'   => '03000000000',
                'email'    => 'admin@karachi.gov.pk',
                'password' => Hash::make('Admin@12345'),
                'role'     => 'admin',
            ]
        );

        // Demo citizen
        User::firstOrCreate(
            ['username' => 'demo_citizen'],
            [
                'name'     => 'Muhammad Ali',
                'cnic'     => '42201-1234567-1',
                'mobile'   => '03001234567',
                'email'    => 'demo@karachi.gov.pk',
                'password' => Hash::make('Demo@1234'),
                'role'     => 'citizen',
            ]
        );
    }
}

// ════════════════════════════════════════════════════════
//  PriceCategorySeeder
// ════════════════════════════════════════════════════════
class PriceCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Grains',        'name_urdu' => 'دالیں/اجناس',   'slug' => 'grains',       'icon' => '🌾', 'sort_order' => 1],
            ['name' => 'Pulses',        'name_urdu' => 'دالیں',          'slug' => 'pulses',       'icon' => '🫘', 'sort_order' => 2],
            ['name' => 'Cooking Oil',   'name_urdu' => 'کوکنگ آئل',      'slug' => 'cooking_oil',  'icon' => '🫙', 'sort_order' => 3],
            ['name' => 'Sugar & Salt',  'name_urdu' => 'چینی اور نمک',   'slug' => 'sugar_salt',   'icon' => '🧂', 'sort_order' => 4],
            ['name' => 'Vegetables',    'name_urdu' => 'سبزیاں',         'slug' => 'vegetables',   'icon' => '🥦', 'sort_order' => 5],
            ['name' => 'Fruits',        'name_urdu' => 'پھل',            'slug' => 'fruits',       'icon' => '🍎', 'sort_order' => 6],
            ['name' => 'Dairy',         'name_urdu' => 'ڈیری',           'slug' => 'dairy',        'icon' => '🥛', 'sort_order' => 7],
            ['name' => 'Meat & Poultry','name_urdu' => 'گوشت و مرغی',   'slug' => 'meat',         'icon' => '🍗', 'sort_order' => 8],
            ['name' => 'Spices',        'name_urdu' => 'مصالحہ جات',     'slug' => 'spices',       'icon' => '🌶️', 'sort_order' => 9],
        ];

        foreach ($categories as $cat) {
            PriceCategory::firstOrCreate(['slug' => $cat['slug']], $cat + ['is_active' => true]);
        }
    }
}

// ════════════════════════════════════════════════════════
//  PriceItemSeeder
// ════════════════════════════════════════════════════════
class PriceItemSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            // ── GRAINS ──
            ['category' => 'grains', 'name' => 'Rice (Fine)',        'name_urdu' => 'چاول (باریک)',    'unit' => '1 Kg',  'unit_urdu' => '1 کلوگرام', 'price' => 150.00, 'prev' => 147.00],
            ['category' => 'grains', 'name' => 'Rice (Coarse)',      'name_urdu' => 'چاول (موٹا)',     'unit' => '1 Kg',  'unit_urdu' => '1 کلوگرام', 'price' => 120.00, 'prev' => 120.00],
            ['category' => 'grains', 'name' => 'Wheat Flour (Atta)', 'name_urdu' => 'گندم کا آٹا',    'unit' => '1 Kg',  'unit_urdu' => '1 کلوگرام', 'price' => 110.00, 'prev' => 112.00],
            ['category' => 'grains', 'name' => 'Maida',              'name_urdu' => 'میدہ',            'unit' => '1 Kg',  'unit_urdu' => '1 کلوگرام', 'price' => 130.00, 'prev' => 130.00],
            ['category' => 'grains', 'name' => 'Suji (Semolina)',    'name_urdu' => 'سوجی',           'unit' => '1 Kg',  'unit_urdu' => '1 کلوگرام', 'price' => 140.00, 'prev' => 138.00],

            // ── PULSES ──
            ['category' => 'pulses', 'name' => 'Gram Pulse (Chana Dal)', 'name_urdu' => 'چنا دال',   'unit' => '1 Kg',  'unit_urdu' => '1 کلوگرام', 'price' => 180.00, 'prev' => 176.00],
            ['category' => 'pulses', 'name' => 'Masoor Dal',             'name_urdu' => 'مسور دال',  'unit' => '1 Kg',  'unit_urdu' => '1 کلوگرام', 'price' => 160.00, 'prev' => 161.00],
            ['category' => 'pulses', 'name' => 'Moong Dal',              'name_urdu' => 'مونگ دال',  'unit' => '1 Kg',  'unit_urdu' => '1 کلوگرام', 'price' => 190.00, 'prev' => 188.00],
            ['category' => 'pulses', 'name' => 'Urad Dal',               'name_urdu' => 'اڑد دال',   'unit' => '1 Kg',  'unit_urdu' => '1 کلوگرام', 'price' => 200.00, 'prev' => 200.00],

            // ── COOKING OIL ──
            ['category' => 'cooking_oil', 'name' => 'Cooking Oil (Pack)',   'name_urdu' => 'کوکنگ آئل (پیک)',  'unit' => '1 Ltr', 'unit_urdu' => '1 لیٹر',    'price' => 320.00, 'prev' => 315.00],
            ['category' => 'cooking_oil', 'name' => 'Cooking Oil (Loose)',  'name_urdu' => 'کوکنگ آئل (کھلا)', 'unit' => '1 Ltr', 'unit_urdu' => '1 لیٹر',    'price' => 290.00, 'prev' => 290.00],
            ['category' => 'cooking_oil', 'name' => 'Mustard Oil',          'name_urdu' => 'سرسوں کا تیل',    'unit' => '1 Ltr', 'unit_urdu' => '1 لیٹر',    'price' => 350.00, 'prev' => 345.00],
            ['category' => 'cooking_oil', 'name' => 'Desi Ghee',            'name_urdu' => 'دیسی گھی',        'unit' => '1 Kg',  'unit_urdu' => '1 کلوگرام',  'price' => 950.00, 'prev' => 950.00],
            ['category' => 'cooking_oil', 'name' => 'Banaspati Ghee',       'name_urdu' => 'بناسپتی گھی',     'unit' => '1 Kg',  'unit_urdu' => '1 کلوگرام',  'price' => 380.00, 'prev' => 375.00],

            // ── SUGAR & SALT ──
            ['category' => 'sugar_salt', 'name' => 'Sugar (White)',     'name_urdu' => 'چینی (سفید)',    'unit' => '1 Kg',  'unit_urdu' => '1 کلوگرام', 'price' => 130.00, 'prev' => 132.00],
            ['category' => 'sugar_salt', 'name' => 'Sugar (Brown)',     'name_urdu' => 'چینی (براؤن)',   'unit' => '1 Kg',  'unit_urdu' => '1 کلوگرام', 'price' => 145.00, 'prev' => 145.00],
            ['category' => 'sugar_salt', 'name' => 'Salt (Iodized)',    'name_urdu' => 'نمک (آئوڈنائزڈ)', 'unit' => '1 Kg', 'unit_urdu' => '1 کلوگرام', 'price' => 20.00,  'prev' => 20.00],
            ['category' => 'sugar_salt', 'name' => 'Salt (Rock)',       'name_urdu' => 'پتھر نمک',       'unit' => '1 Kg',  'unit_urdu' => '1 کلوگرام', 'price' => 25.00,  'prev' => 25.00],

            // ── VEGETABLES ──
            ['category' => 'vegetables', 'name' => 'Onion (Pyaz)',     'name_urdu' => 'پیاز',        'unit' => '1 Kg', 'unit_urdu' => '1 کلوگرام', 'price' => 60.00,  'prev' => 55.00],
            ['category' => 'vegetables', 'name' => 'Tomato (Tamatar)', 'name_urdu' => 'ٹماٹر',       'unit' => '1 Kg', 'unit_urdu' => '1 کلوگرام', 'price' => 80.00,  'prev' => 75.00],
            ['category' => 'vegetables', 'name' => 'Potato (Aloo)',    'name_urdu' => 'آلو',         'unit' => '1 Kg', 'unit_urdu' => '1 کلوگرام', 'price' => 50.00,  'prev' => 50.00],
            ['category' => 'vegetables', 'name' => 'Garlic (Lehsan)',  'name_urdu' => 'لہسن',        'unit' => '1 Kg', 'unit_urdu' => '1 کلوگرام', 'price' => 200.00, 'prev' => 190.00],
            ['category' => 'vegetables', 'name' => 'Ginger (Adrak)',   'name_urdu' => 'ادرک',        'unit' => '1 Kg', 'unit_urdu' => '1 کلوگرام', 'price' => 180.00, 'prev' => 180.00],
            ['category' => 'vegetables', 'name' => 'Cabbage (Bandh Gobhi)', 'name_urdu' => 'بند گوبھی', 'unit' => '1 Kg', 'unit_urdu' => '1 کلوگرام', 'price' => 40.00, 'prev' => 40.00],
            ['category' => 'vegetables', 'name' => 'Cauliflower (Phool Gobhi)', 'name_urdu' => 'پھول گوبھی', 'unit' => '1 Kg', 'unit_urdu' => '1 کلوگرام', 'price' => 60.00, 'prev' => 55.00],
            ['category' => 'vegetables', 'name' => 'Spinach (Palak)',  'name_urdu' => 'پالک',        'unit' => '1 Kg', 'unit_urdu' => '1 کلوگرام', 'price' => 30.00,  'prev' => 30.00],
            ['category' => 'vegetables', 'name' => 'Brinjal (Baigan)', 'name_urdu' => 'بینگن',       'unit' => '1 Kg', 'unit_urdu' => '1 کلوگرام', 'price' => 50.00,  'prev' => 48.00],
            ['category' => 'vegetables', 'name' => 'Bitter Gourd (Karela)', 'name_urdu' => 'کریلا', 'unit' => '1 Kg', 'unit_urdu' => '1 کلوگرام', 'price' => 80.00,  'prev' => 80.00],

            // ── FRUITS ──
            ['category' => 'fruits', 'name' => 'Banana (Kela)',    'name_urdu' => 'کیلا',       'unit' => '1 Doz', 'unit_urdu' => '1 درجن',     'price' => 60.00,  'prev' => 60.00],
            ['category' => 'fruits', 'name' => 'Apple (Seb)',      'name_urdu' => 'سیب',        'unit' => '1 Kg',  'unit_urdu' => '1 کلوگرام',  'price' => 250.00, 'prev' => 240.00],
            ['category' => 'fruits', 'name' => 'Mango (Aam)',      'name_urdu' => 'آم',         'unit' => '1 Kg',  'unit_urdu' => '1 کلوگرام',  'price' => 200.00, 'prev' => 180.00],
            ['category' => 'fruits', 'name' => 'Orange (Narangi)', 'name_urdu' => 'نارنگی',     'unit' => '1 Kg',  'unit_urdu' => '1 کلوگرام',  'price' => 120.00, 'prev' => 120.00],
            ['category' => 'fruits', 'name' => 'Guava (Amrood)',   'name_urdu' => 'امرود',      'unit' => '1 Kg',  'unit_urdu' => '1 کلوگرام',  'price' => 80.00,  'prev' => 75.00],

            // ── DAIRY ──
            ['category' => 'dairy', 'name' => 'Milk (Fresh)',   'name_urdu' => 'تازہ دودھ',   'unit' => '1 Ltr', 'unit_urdu' => '1 لیٹر',    'price' => 130.00, 'prev' => 130.00],
            ['category' => 'dairy', 'name' => 'Yogurt (Dahi)', 'name_urdu' => 'دہی',         'unit' => '1 Kg',  'unit_urdu' => '1 کلوگرام',  'price' => 180.00, 'prev' => 175.00],
            ['category' => 'dairy', 'name' => 'Eggs (Anda)',   'name_urdu' => 'انڈہ',        'unit' => '12 Pcs','unit_urdu' => '12 عدد',      'price' => 280.00, 'prev' => 270.00],
            ['category' => 'dairy', 'name' => 'Butter',        'name_urdu' => 'مکھن',        'unit' => '200g',  'unit_urdu' => '200 گرام',   'price' => 250.00, 'prev' => 250.00],

            // ── MEAT ──
            ['category' => 'meat', 'name' => 'Chicken (Broiler)',  'name_urdu' => 'مرغی (بروائلر)', 'unit' => '1 Kg', 'unit_urdu' => '1 کلوگرام', 'price' => 350.00, 'prev' => 340.00],
            ['category' => 'meat', 'name' => 'Chicken (Desi)',     'name_urdu' => 'مرغی (دیسی)',    'unit' => '1 Kg', 'unit_urdu' => '1 کلوگرام', 'price' => 700.00, 'prev' => 700.00],
            ['category' => 'meat', 'name' => 'Beef (Boneless)',    'name_urdu' => 'گائے کا گوشت',  'unit' => '1 Kg', 'unit_urdu' => '1 کلوگرام', 'price' => 900.00, 'prev' => 880.00],
            ['category' => 'meat', 'name' => 'Mutton (Bakra)',     'name_urdu' => 'بکرے کا گوشت', 'unit' => '1 Kg', 'unit_urdu' => '1 کلوگرام', 'price' => 1500.00,'prev' => 1500.00],
            ['category' => 'meat', 'name' => 'Fish (Local)',       'name_urdu' => 'مچھلی (مقامی)', 'unit' => '1 Kg', 'unit_urdu' => '1 کلوگرام', 'price' => 400.00, 'prev' => 380.00],

            // ── SPICES ──
            ['category' => 'spices', 'name' => 'Red Chilli Powder',  'name_urdu' => 'لال مرچ پاؤڈر',  'unit' => '100g', 'unit_urdu' => '100 گرام', 'price' => 60.00, 'prev' => 58.00],
            ['category' => 'spices', 'name' => 'Turmeric (Haldi)',   'name_urdu' => 'ہلدی',           'unit' => '100g', 'unit_urdu' => '100 گرام', 'price' => 40.00, 'prev' => 40.00],
            ['category' => 'spices', 'name' => 'Coriander Powder',   'name_urdu' => 'دھنیا پاؤڈر',   'unit' => '100g', 'unit_urdu' => '100 گرام', 'price' => 50.00, 'prev' => 50.00],
            ['category' => 'spices', 'name' => 'Cumin (Zeera)',       'name_urdu' => 'زیرہ',          'unit' => '100g', 'unit_urdu' => '100 گرام', 'price' => 80.00, 'prev' => 75.00],
            ['category' => 'spices', 'name' => 'Garam Masala',        'name_urdu' => 'گرم مصالحہ',    'unit' => '100g', 'unit_urdu' => '100 گرام', 'price' => 120.00,'prev' => 120.00],
        ];

        foreach ($items as $i => $item) {
            $cat = PriceCategory::where('slug', $item['category'])->first();
            if (!$cat) continue;

            // Skip if this item was previously deleted (soft or force)
            $wasDeleted = PriceItem::onlyTrashed()
                ->where('name', $item['name'])
                ->where('price_category_id', $cat->id)
                ->exists();
            if ($wasDeleted) continue;

            $change  = $item['price'] - $item['prev'];
            $pct     = $item['prev'] > 0 ? round(($change / $item['prev']) * 100, 2) : 0;

            $priceItem = PriceItem::firstOrCreate(
                ['name' => $item['name'], 'price_category_id' => $cat->id],
                [
                    'name_urdu'         => $item['name_urdu'],
                    'unit'              => $item['unit'],
                    'unit_urdu'         => $item['unit_urdu'],
                    'price'             => $item['price'],
                    'previous_price'    => $item['prev'],
                    'price_change'      => $change,
                    'change_percent'    => $pct,
                    'sort_order'        => $i,
                    'is_active'         => true,
                ]
            );

            // Seed 7-day history if none exists (fix: existing items bhi seed ho)
            $hasHistory = PriceHistory::where('price_item_id', $priceItem->id)->exists();
            if (!$hasHistory) {
                $base = $item['price'] * 0.94;
                for ($d = 6; $d >= 0; $d--) {
                    $variance = rand(-300, 300) / 100;
                    PriceHistory::create([
                        'price_item_id' => $priceItem->id,
                        'price'         => round($base + ($d == 0 ? ($item['price'] - $base) : $variance + $base * ($d / 50)), 2),
                        'recorded_at'   => now()->subDays($d),
                    ]);
                }
            }
        }
    }
}


