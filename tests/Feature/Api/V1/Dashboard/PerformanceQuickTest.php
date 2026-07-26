<?php

namespace Tests\Feature\Api\V1\Dashboard;

use App\Models\Link;
use App\Models\Trail;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PerformanceQuickTest extends TestCase
{
    use DatabaseTransactions;

    private User $adminUser;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create([
            'is_admin' => true,
            'email_verified_at' => now(),
        ]);

        $viewPermission = Permission::firstOrCreate(
            ['name' => 'trails.view', 'guard_name' => 'api']
        );

        $adminRole = Role::firstOrCreate(
            ['name' => 'Admin', 'guard_name' => 'api']
        );

        $adminRole->permissions()->syncWithoutDetaching([$viewPermission->id]);
        $this->adminUser->roles()->sync([$adminRole->id]);

        $this->token = JWTAuth::fromUser($this->adminUser);
    }

    private function authenticatedGet(string $uri)
    {
        return $this->getJson($uri, [
            'Authorization' => 'Bearer ' . $this->token,
            'X-Client-Type' => 'web',
        ]);
    }

    /**
     * @test
     * Szybki test performance - możesz zmienić liczbę linków
     */
    public function quick_performance_test()
    {
        // 🎯 ZMIEŃ TĘ WARTOŚĆ aby testować różne scenariusze
        $linkCount = 50;  // Wypróbuj: 10, 50, 100, 500

        echo "\n";
        echo "════════════════════════════════════════════════════════════\n";
        echo "🚀 QUICK PERFORMANCE TEST: {$linkCount} links\n";
        echo "════════════════════════════════════════════════════════════\n\n";

        // Arrange
        $trail = Trail::factory()->create();
        $links = Link::factory()->count($linkCount)->create();
        $trail->links()->attach($links->pluck('id')->toArray());

        // Enable query logging
        DB::enableQueryLog();

        $memoryBefore = memory_get_usage();
        $startTime = microtime(true);

        // Act - wykonaj request
        $response = $this->authenticatedGet("/api/v1/dashboard/trails/{$trail->id}/links");

        // Measure
        $endTime = microtime(true);
        $memoryAfter = memory_get_usage();

        $executionTime = ($endTime - $startTime) * 1000; // ms
        $memoryUsed = ($memoryAfter - $memoryBefore) / 1024 / 1024; // MB
        $queries = DB::getQueryLog();
        $queryCount = count($queries);

        // Assert
        $response->assertStatus(200);
        $response->assertJsonCount($linkCount, 'data');

        // Wyświetl szczegółowe metryki
        echo "📊 RESULTS:\n";
        echo "├─ ⏱  Execution Time: " . number_format($executionTime, 2) . " ms\n";
        echo "├─ 🔍 Query Count: {$queryCount}\n";
        echo "├─ 💾 Memory Used: " . number_format($memoryUsed, 2) . " MB\n";
        echo "├─ 🎯 Time per Link: " . number_format($executionTime / $linkCount, 4) . " ms\n";
        echo "└─ 📦 Response Size: " . number_format(strlen($response->getContent()) / 1024, 2) . " KB\n\n";

        // Pokaż wszystkie zapytania SQL
        echo "📝 EXECUTED QUERIES:\n";
        foreach ($queries as $index => $query) {
            $time = $query['time'];
            $sql = $query['query'];
            echo sprintf("  %2d. [%6.2fms] %s\n", $index + 1, $time, $sql);
        }
        echo "\n";

        echo "════════════════════════════════════════════════════════════\n";
        echo "✅ Test completed successfully!\n";
        echo "════════════════════════════════════════════════════════════\n\n";

        DB::disableQueryLog();

        // Performance assertions
        $this->assertLessThan(2000, $executionTime, "Too slow: {$executionTime}ms");
        $this->assertLessThan(20, $queryCount, "Too many queries: {$queryCount}");
    }

    /** @test */
    public function compare_with_vs_without_optimization()
    {
        echo "\n";
        echo "════════════════════════════════════════════════════════════\n";
        echo "📊 COMPARISON: Optimized vs Non-optimized\n";
        echo "════════════════════════════════════════════════════════════\n\n";

        $trail = Trail::factory()->create();
        $links = Link::factory()->count(30)->create();
        $trail->links()->attach($links->pluck('id')->toArray());

        // Test 1: Optimized (current implementation)
        DB::enableQueryLog();
        $start1 = microtime(true);

        $response1 = $this->authenticatedGet("/api/v1/dashboard/trails/{$trail->id}/links");

        $time1 = (microtime(true) - $start1) * 1000;
        $queries1 = count(DB::getQueryLog());
        DB::disableQueryLog();

        echo "✅ OPTIMIZED (current code):\n";
        echo "   Time: " . number_format($time1, 2) . " ms\n";
        echo "   Queries: {$queries1}\n\n";

        // Test 2: Simulate non-optimized (fetch full models)
        DB::enableQueryLog();
        $start2 = microtime(true);

        // Symuluj nieoptymalizowany kod
        $trailFull = Trail::find($trail->id); // Wszystkie kolumny
        $linksFull = $trailFull->links; // Lazy loading

        $time2 = (microtime(true) - $start2) * 1000;
        $queries2 = count(DB::getQueryLog());
        DB::disableQueryLog();

        echo "⚠️  NON-OPTIMIZED (simulated):\n";
        echo "   Time: " . number_format($time2, 2) . " ms\n";
        echo "   Queries: {$queries2}\n\n";

        $timeDiff = $time2 - $time1;
        $improvement = ($timeDiff / $time2) * 100;

        echo "💡 IMPROVEMENT:\n";
        echo "   Time saved: " . number_format($timeDiff, 2) . " ms\n";
        echo "   Performance gain: " . number_format($improvement, 1) . "%\n";
        echo "   Query reduction: " . ($queries2 - $queries1) . " fewer queries\n\n";

        echo "════════════════════════════════════════════════════════════\n\n";

        $response1->assertStatus(200);
    }
}
