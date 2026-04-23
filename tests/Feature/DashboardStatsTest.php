<?php

namespace Tests\Feature;

use App\Livewire\DashboardStats;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DashboardStatsTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_component_can_render()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSeeLivewire('dashboard-stats');
    }

    public function test_it_calculates_today_stats_correctly()
    {
        $user = User::factory()->create();
        
        // Create some transactions
        Transaction::factory()->create([
            'status' => 'completed',
            'grand_total' => 1000,
            'transaction_date' => now(),
        ]);

        Transaction::factory()->create([
            'status' => 'completed',
            'grand_total' => 2000,
            'transaction_date' => now(),
        ]);

        Livewire::actingAs($user)
            ->test(DashboardStats::class)
            ->assertSet('dateRange', 'today')
            ->assertViewHas('stats', function ($stats) {
                return $stats['total_sales'] == 3000 && $stats['transaction_count'] == 2;
            });
    }

    public function test_it_updates_stats_when_date_range_changes()
    {
        $user = User::factory()->create();

        // Transaction for today
        Transaction::factory()->create([
            'status' => 'completed',
            'grand_total' => 1000,
            'transaction_date' => now(),
        ]);

        // Transaction for yesterday
        Transaction::factory()->create([
            'status' => 'completed',
            'grand_total' => 5000,
            'transaction_date' => now()->subDay(),
        ]);

        Livewire::actingAs($user)
            ->test(DashboardStats::class)
            ->set('dateRange', 'yesterday')
            ->assertViewHas('stats', function ($stats) {
                return $stats['total_sales'] == 5000;
            });
    }
}
