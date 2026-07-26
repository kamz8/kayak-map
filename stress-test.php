<?php

require_once __DIR__ . "/../vendor/autoload.php";

$app = require_once __DIR__ . "/../bootstrap/app.php";
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Link;
use App\Models\Trail;
use Illuminate\Support\Facades\DB;

echo "\n";
echo "════════════════════════════════════════════════════════════\n";
echo "🚀 STRESS TEST: Link API Performance\n";
echo "════════════════════════════════════════════════════════════\n\n";

// Test 1: Create trail with 100 links
echo "Test 1: Fetching 100 links...\n";
$trail = Trail::factory()->create();
$links = Link::factory()->count(100)->create();
$trail->links()->attach($links->pluck("id")->toArray());

DB::enableQueryLog();
$start = microtime(true);

$result = $trail->links()->get();

$time = (microtime(true) - $start) * 1000;
$queryCount = count(DB::getQueryLog());

echo "✓ Time: " . number_format($time, 2) . " ms\n";
echo "✓ Queries: {$queryCount}\n";
echo "✓ Links fetched: " . $result->count() . "\n\n";

DB::disableQueryLog();

// Cleanup
$trail->delete();
Link::query()->delete();

echo "════════════════════════════════════════════════════════════\n";
echo "✅ Stress test completed successfully!\n";
echo "════════════════════════════════════════════════════════════\n\n";

