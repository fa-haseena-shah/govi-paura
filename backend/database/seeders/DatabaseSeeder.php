<?php

namespace Database\Seeders;

use App\Domains\Auth\Models\Admin;
use App\Domains\Auth\Models\Buyer;
use App\Domains\Auth\Models\Farmer;
use App\Domains\Auth\Models\Rider;
use App\Domains\Auth\Models\RiderVehicle;
use App\Domains\Listing\Models\Listing;
use App\Domains\Notification\Models\SmsLog;
use App\Domains\Order\Models\Order;
use App\Domains\Order\Models\OrderItem;
use App\Domains\Payment\Models\Payment;
use App\Domains\Payment\Models\Receipt;
use App\Enums\BidStatus;
use App\Enums\BusinessType;
use App\Enums\DeliveryCategory;
use App\Enums\Language;
use App\Enums\ListingStatus;
use App\Enums\OrderSource;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\PaymentTransactionStatus;
use App\Enums\SellingMethod;
use App\Enums\SmsAlertType;
use App\Enums\UserStatus;
use App\Enums\UserType;
use App\Enums\VehicleType;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Seed in dependency order.
        $this->seedAdmin();
        $this->seedFarmers();
        $this->seedBuyers();
        $this->seedRiders();
        $this->seedListings();
        $this->seedOrders();
        $this->seedBids();
    }

    /*
    |--------------------------------------------------------------------------
    | Admin
    |--------------------------------------------------------------------------
    */

    private function seedAdmin(): void
    {
        $admin = User::create([
            'full_name' => 'System Admin',
            'email' => 'admin@govipaura.test',
            'phone' => '0770000000',
            'password' => 'password',
            'preferred_lang' => Language::English,
            'role' => UserType::Admin,
            'status' => UserStatus::Active,
        ]);

        Admin::create([
            'user_id' => $admin->id,
            'designation' => 'System Administrator',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Farmers
    |--------------------------------------------------------------------------
    */

    private function seedFarmers(): void
    {
        $farmers = [
            [
                'Kamal Perera',
                'kamal@farmer.test',
                '0712000001',
                '198812345671',
                'No. 12, Temple Road, Peradeniya',
                'Kandy',
                Language::Sinhala,
                UserStatus::Active,
            ],
            [
                'Nimal Silva',
                null,
                '0712000002',
                '198512345672',
                '5 Lake Road, Matale',
                'Matale',
                Language::Sinhala,
                UserStatus::Active,
            ],
            [
                'Sunethra Wickramasinghe',
                null,
                '0712000003',
                '199112345673',
                '28 Hill Street, Nuwara Eliya',
                'Nuwara Eliya',
                Language::English,
                UserStatus::Active,
            ],
            [
                'Ranjith Bandara',
                'ranjith@farmer.test',
                '0712000004',
                '198012345674',
                'Paddy Road, Anuradhapura',
                'Anuradhapura',
                Language::Sinhala,
                UserStatus::Active,
            ],
            [
                'Lakmini Jayasinghe',
                null,
                '0712000005',
                '199512345675',
                '9 Market Lane, Dambulla',
                'Dambulla',
                Language::Sinhala,
                UserStatus::PendingVerification,
            ],
        ];

        foreach (
            $farmers as [
                $name,
                $email,
                $phone,
                $nic,
                $address,
                $region,
                $lang,
                $status
            ]
        ) {
            $user = User::create([
                'full_name' => $name,
                'email' => $email,
                'phone' => $phone,
                'password' => 'password',
                'preferred_lang' => $lang,
                'role' => UserType::Farmer,
                'status' => $status,
            ]);

            Farmer::create([
                'user_id' => $user->id,
                'nic' => $nic,
                'address' => $address,
                'region' => $region,
            ]);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Buyers
    |--------------------------------------------------------------------------
    */

    private function seedBuyers(): void
    {
        $buyers = [
            [
                'Ishara Fernando',
                'greenmart@buyer.test',
                '0713000001',
                'Green Mart Colombo',
                BusinessType::Retailer,
                UserStatus::Active,
            ],
            [
                'Dinesh Kumar',
                'cinnamonkitchen@buyer.test',
                '0713000002',
                'Cinnamon Kitchen Restaurant',
                BusinessType::Restaurant,
                UserStatus::Active,
            ],
            [
                'Priyanka Herath',
                'cityfresh@buyer.test',
                '0713000003',
                'City Fresh Supermarket',
                BusinessType::Retailer,
                UserStatus::Active,
            ],
            [
                'Asanka Rathnayake',
                'ceylonmillers@buyer.test',
                '0713000004',
                'Ceylon Millers (Pvt) Ltd',
                BusinessType::Other,
                UserStatus::Active,
            ],
            [
                'Roshan De Silva',
                'lankatraders@buyer.test',
                '0713000005',
                'Lanka Traders',
                BusinessType::Other,
                UserStatus::Suspended,
            ],
        ];

        foreach (
            $buyers as [
                $name,
                $email,
                $phone,
                $business,
                $type,
                $status
            ]
        ) {
            $user = User::create([
                'full_name' => $name,
                'email' => $email,
                'phone' => $phone,
                'password' => 'password',
                'preferred_lang' => Language::English,
                'role' => UserType::Buyer,
                'status' => $status,
            ]);

            Buyer::create([
                'user_id' => $user->id,
                'business_name' => $business,
                'business_type' => $type,
            ]);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Riders and Vehicles
    |--------------------------------------------------------------------------
    */

    private function seedRiders(): void
    {
        $riders = [
            [
                'Sunil Fernando',
                'sunil@rider.test',
                '0714000001',
                DeliveryCategory::Paddy,
                'Anuradhapura',
                UserStatus::Active,
                [
                    [VehicleType::Lorry, 'NC-4521'],
                    [VehicleType::Tractor, 'NC-7788'],
                ],
            ],
            [
                'Dilan Rajapaksa',
                'dilan@rider.test',
                '0714000002',
                DeliveryCategory::Vegetables,
                'Kandy',
                UserStatus::Active,
                [
                    [VehicleType::ThreeWheeler, 'CBA-2345'],
                    [VehicleType::Bike, 'KY-3344'],
                ],
            ],
            [
                'Mohamed Rizwan',
                'rizwan@rider.test',
                '0714000003',
                DeliveryCategory::Vegetables,
                'Nuwara Eliya',
                UserStatus::Active,
                [
                    [VehicleType::Lorry, 'NW-6120'],
                ],
            ],
            [
                'Chaminda Gamage',
                'chaminda@rider.test',
                '0714000004',
                DeliveryCategory::Paddy,
                'Matale',
                UserStatus::PendingVerification,
                [
                    [VehicleType::Lorry, 'CP-2210'],
                ],
            ],
        ];

        foreach (
            $riders as [
                $name,
                $email,
                $phone,
                $category,
                $region,
                $status,
                $vehicles
            ]
        ) {
            $user = User::create([
                'full_name' => $name,
                'email' => $email,
                'phone' => $phone,
                'password' => 'password',
                'preferred_lang' => Language::English,
                'role' => UserType::Rider,
                'status' => $status,
            ]);

            Rider::create([
                'user_id' => $user->id,
                'category' => $category,
                'region' => $region,
            ]);

            foreach ($vehicles as [$type, $number]) {
                RiderVehicle::create([
                    'rider_id' => $user->id,
                    'vehicle_type' => $type,
                    'vehicle_no' => $number,
                ]);
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Listings
    |--------------------------------------------------------------------------
    */

    private function seedListings(): void
    {
        $fixed = SellingMethod::FixedPrice;
        $bidding = SellingMethod::Bidding;
        $active = ListingStatus::Active;

        // Farmer phone, crop, quantity, price, selling method,
        // starting price, closing time, status, location,
        // days since harvest, days since listing.
        $listings = [
            // Kamal Perera — Kandy
            ['0712000001', 'Tomato', 100, 120.00, $fixed, null, null, $active, 'Kandy', 3, 20],
            ['0712000001', 'Okra', 8, 200.00, $fixed, null, null, $active, 'Kandy', 2, 15],
            ['0712000001', 'Pumpkin', 60, 70.00, $fixed, null, null, ListingStatus::Closed, 'Kandy', 12, 25],
            ['0712000001', 'Snake Gourd', 0, 90.00, $fixed, null, null, ListingStatus::Unavailable, 'Kandy', 8, 22],

            // Nimal Silva — Matale
            ['0712000002', 'Beans', 80, 150.00, $fixed, null, null, $active, 'Matale', 4, 18],
            ['0712000002', 'Brinjal', 50, 110.00, $fixed, null, null, $active, 'Matale', 3, 16],
            ['0712000002', 'Green Chilli', 40, 300.00, $fixed, null, null, $active, 'Matale', 5, 24],

            // Sunethra Wickramasinghe — Nuwara Eliya
            ['0712000003', 'Carrot', 100, 80.00, $fixed, null, null, $active, 'Nuwara Eliya', 3, 19],
            ['0712000003', 'Cabbage', 60, 60.00, $fixed, null, null, $active, 'Nuwara Eliya', 4, 17],
            ['0712000003', 'Leeks', 40, 140.00, $fixed, null, null, $active, 'Nuwara Eliya', 2, 10],

            // Ranjith Bandara — Anuradhapura
            ['0712000004', 'Paddy (Samba)', 8000, 130.00, $fixed, null, null, $active, 'Anuradhapura', 10, 30],
            ['0712000004', 'Paddy (Nadu)', 5000, null, $bidding, 95.00, now()->addDays(14), $active, 'Anuradhapura', 7, 5],
            ['0712000004', 'Paddy (Keeri Samba)', 3000, null, $bidding, 120.00, now()->subDays(3), ListingStatus::Closed, 'Anuradhapura', 20, 28],
        ];

        foreach (
            $listings as [
                $phone,
                $crop,
                $qty,
                $price,
                $method,
                $startPrice,
                $closing,
                $status,
                $location,
                $harvestedAgo,
                $listedAgo
            ]
        ) {
            $farmer = User::where('phone', $phone)->firstOrFail();

            $listing = new Listing();

            $listing->forceFill([
                'farmer_id' => $farmer->id,
                'crop_type' => $crop,
                'qty' => $qty,
                'price_per_unit' => $price,
                'harvest_date' => now()->subDays($harvestedAgo)->toDateString(),
                'location' => $location,
                'photo_url' => null,
                'selling_method' => $method,
                'starting_price' => $startPrice,
                'closing_time' => $closing,
                'status' => $status,
                'created_at' => now()->subDays($listedAgo),
                'updated_at' => now()->subDays($listedAgo),
            ])->save();
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Orders, Order Items, Payments, Receipts and SMS Logs
    |--------------------------------------------------------------------------
    */

    private function seedOrders(): void
    {
        $card = PaymentMethod::Card;
        $cod = PaymentMethod::CashOnDelivery;
        $success = PaymentTransactionStatus::Success;
        $pending = PaymentTransactionStatus::Pending;

        // Buyer phone, farmer phone, days ago, order status,
        // [crop => quantity], [payment method, transaction status] or null.
        $orders = [
            ['0713000004', '0712000004', 15, OrderStatus::Completed, ['Paddy (Samba)' => 2000], [$card, $success]],
            ['0713000002', '0712000002', 12, OrderStatus::Completed, ['Green Chilli' => 40], [$cod, $success]],
            ['0713000001', '0712000001', 10, OrderStatus::Completed, ['Tomato' => 20], [$card, $success]],
            ['0713000002', '0712000003', 8, OrderStatus::Delivered, ['Carrot' => 15, 'Cabbage' => 10], [$cod, $pending]],
            ['0713000001', '0712000002', 6, OrderStatus::Confirmed, ['Beans' => 25, 'Brinjal' => 10], [$card, $success]],
            ['0713000003', '0712000001', 3, OrderStatus::Processing, ['Tomato' => 30], [$cod, $pending]],
            ['0713000003', '0712000003', 1, OrderStatus::Pending, ['Leeks' => 12], null],
        ];

        foreach ($orders as [$buyerPhone, $farmerPhone, $daysAgo, $status, $items, $payment]) {
            $buyer = User::where('phone', $buyerPhone)->firstOrFail();
            $farmer = User::where('phone', $farmerPhone)->firstOrFail();
            $placedAt = now()->subDays($daysAgo);

            // Resolve each order item's listing and price.
            $lines = [];

            foreach ($items as $crop => $qty) {
                $listing = Listing::where('farmer_id', $farmer->id)
                    ->where('crop_type', $crop)
                    ->firstOrFail();

                $unitPrice = (float) $listing->price_per_unit;

                $lines[] = [
                    'listing' => $listing,
                    'qty' => $qty,
                    'unit_price' => $unitPrice,
                    'sub_total' => round($unitPrice * $qty, 2),
                ];
            }

            $total = round(array_sum(array_column($lines, 'sub_total')), 2);
            $isPaid = $payment !== null && $payment[1] === $success;

            $order = new Order();

            $order->forceFill([
                'buyer_id' => $buyer->id,
                'farmer_id' => $farmer->id,
                'bid_id' => null,
                'total_amount' => $total,
                'status' => $status,
                'payment_status' => $isPaid ? PaymentStatus::Paid : PaymentStatus::Unpaid,
                'source' => OrderSource::FixedPricePurchase,
                'created_at' => $placedAt,
                'updated_at' => $placedAt,
            ])->save();

            foreach ($lines as $line) {
                $item = new OrderItem();

                $item->forceFill([
                    'order_id' => $order->id,
                    'listing_id' => $line['listing']->id,
                    'qty' => $line['qty'],
                    'unit_price' => $line['unit_price'],
                    'sub_total' => $line['sub_total'],
                    'created_at' => $placedAt,
                    'updated_at' => $placedAt,
                ])->save();

                // Reduce listing stock to reflect the seeded order.
                $line['listing']->decrement('qty', $line['qty']);
                $line['listing']->refresh();

                if ((int) $line['listing']->qty <= 0) {
                    $line['listing']->update([
                        'status' => ListingStatus::Sold,
                    ]);
                }
            }

            if ($payment !== null) {
                $this->createPayment(
                    $order,
                    $total,
                    $payment[0],
                    $payment[1],
                    $placedAt
                );
            }

            $this->createNewOrderSms($farmer, $order, $items, $placedAt);
        }

        $this->createLowStockSms();
    }

    private function createPayment(
        Order $order,
        float $amount,
        PaymentMethod $method,
        PaymentTransactionStatus $status,
        $at
    ): void {
        $payment = new Payment();

        $payment->forceFill([
            'reference' => 'ORDER-' . $order->id . '-SEED',
            'payable_type' => 'order',
            'payable_id' => $order->id,
            'amount' => $amount,
            'currency' => 'LKR',
            'status' => $status,
            'method' => $method,
            'created_at' => $at,
            'updated_at' => $at,
        ])->save();

        // Receipts are created only for successful payments.
        if ($status === PaymentTransactionStatus::Success) {
            $receipt = new Receipt();

            $receipt->forceFill([
                'payment_id' => $payment->id,
                'receipt_number' => 'RCPT-' . $at->format('Ymd') . '-'
                    . str_pad((string) $payment->id, 5, '0', STR_PAD_LEFT),
                'amount' => $amount,
                'issued_at' => $at,
                'created_at' => $at,
                'updated_at' => $at,
            ])->save();
        }
    }

    private function createNewOrderSms(
        User $farmer,
        Order $order,
        array $items,
        $at
    ): void {
        $summary = collect($items)
            ->map(fn ($qty, $crop) => "{$crop} x{$qty}")
            ->implode(', ');

        $this->createSms(
            $farmer,
            SmsAlertType::NewOrder,
            "Govi Paura: New order #{$order->id} - {$summary}. Check your dashboard.",
            $at
        );
    }

    private function createLowStockSms(): void
    {
        $okra = Listing::where('crop_type', 'Okra')->firstOrFail();

        $this->createSms(
            $okra->farmer,
            SmsAlertType::LowStock,
            "Govi Paura: Low stock alert — \"{$okra->crop_type}\" has only {$okra->qty} units left.",
            now()->subDays(2)
        );
    }

    private function createSms(
        User $farmer,
        SmsAlertType $type,
        string $message,
        $at
    ): void {
        $log = new SmsLog();

        $log->forceFill([
            'user_id' => $farmer->id,
            'phone' => $farmer->phone,
            'type' => $type,
            'message' => $message,
            'status' => 'sent',
            'created_at' => $at,
            'updated_at' => $at,
        ])->save();
    }

    /*
    |--------------------------------------------------------------------------
    | Bids
    |--------------------------------------------------------------------------
    */

    private function seedBids(): void
    {
        $listing = Listing::where('crop_type', 'Paddy (Keeri Samba)')
            ->firstOrFail();

        // Buyer phone, bid amount, quantity, days ago.
        $bids = [
            ['0713000004', 120.00, 1000, 6],
            ['0713000001', 125.00, 500, 5],
            ['0713000003', 130.00, 800, 4],
        ];

        foreach ($bids as [$phone, $amount, $qty, $daysAgo]) {
            $buyer = User::where('phone', $phone)->firstOrFail();
            $placedAt = now()->subDays($daysAgo);

            DB::table('bids')->insert([
                'listing_id' => $listing->id,
                'buyer_id' => $buyer->id,
                'bid_amount' => $amount,
                'bid_qty' => $qty,
                'bid_status' => BidStatus::Pending->value,
                'rejection_reason' => null,
                'decided_at' => null,
                'created_at' => $placedAt,
                'updated_at' => $placedAt,
            ]);
        }
    }
}