<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\SensorLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SensorIotTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test sensor data ingestion works.
     */
    public function test_sensor_ingestion_api(): void
    {
        $payload = [
            'gas' => 450,
            'flame' => false,
        ];

        $response = $this->postJson('/api/sensor', $payload);

        $response->assertStatus(200)
                 ->assertJson(['success' => true]);

        $this->assertDatabaseHas('sensor_logs', [
            'gas_value' => 450,
            'flame_detected' => false,
        ]);
    }

    /**
     * Test sensor data validation rules.
     */
    public function test_sensor_ingestion_validation_fails_on_empty_payload(): void
    {
        $response = $this->postJson('/api/sensor', []);

        $response->assertStatus(422)
                 ->assertJson([
                     'success' => false,
                 ])
                 ->assertJsonStructure(['errors' => ['gas', 'flame']]);
    }

    public function test_sensor_ingestion_validation_fails_on_invalid_data(): void
    {
        $response = $this->postJson('/api/sensor', [
            'gas' => 'not-an-integer',
            'flame' => 'not-a-boolean',
        ]);

        $response->assertStatus(422);
    }

    /**
     * Test web routes authentication protection.
     */
    public function test_unauthenticated_users_redirected_to_login(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
        $this->get('/dashboard/data')->assertRedirect('/login');
        $this->get('/logs')->assertRedirect('/login');
        $this->get('/logs/export')->assertRedirect('/login');
    }

    /**
     * Test dashboard load for authenticated users.
     */
    public function test_authenticated_user_can_access_dashboard_and_polling(): void
    {
        $user = User::factory()->create();
        
        // Seed a log to avoid empty database warnings
        SensorLog::create([
            'gas_value' => 200,
            'flame_detected' => false,
        ]);

        $response = $this->actingAs($user)->get('/dashboard');
        $response->assertStatus(200)
                 ->assertSee('Industrial IoT Monitoring Dashboard');

        $dataResponse = $this->actingAs($user)->get('/dashboard/data');
        $dataResponse->assertStatus(200)
                     ->assertJsonStructure([
                         'latest_log',
                         'system_state',
                         'device_info',
                         'stats',
                         'recent_logs',
                         'charts'
                     ]);
    }

    /**
     * Test logs listing view.
     */
    public function test_authenticated_user_can_view_logs(): void
    {
        $user = User::factory()->create();
        
        SensorLog::create([
            'gas_value' => 1200,
            'flame_detected' => false,
        ]);

        $response = $this->actingAs($user)->get('/logs');
        $response->assertStatus(200)
                 ->assertSee('1200')
                 ->assertSee('Sensor Communication Logs');
    }

    /**
     * Test CSV export downloads properly.
     */
    public function test_authenticated_user_can_export_csv(): void
    {
        $user = User::factory()->create();
        
        SensorLog::create([
            'gas_value' => 350,
            'flame_detected' => false,
        ]);

        $response = $this->actingAs($user)->get('/logs/export');
        $response->assertStatus(200)
                 ->assertHeader('Content-Type', 'text/csv; charset=UTF-8')
                 ->assertHeader('Content-Disposition', 'attachment; filename="smart_safety_sensor_logs_' . date('Ymd') . '_' . date('H') . date('i') . date('s') . '.csv"'); // approximate filename match

        // We can check if output contains headers by sending streamed content
        ob_start();
        $response->sendContent();
        $content = ob_get_clean();

        $this->assertStringContainsString('Gas Value (ppm)', $content);
        $this->assertStringContainsString('350', $content);
    }

    /**
     * Test GET /api/sensor/latest endpoint.
     */
    public function test_get_latest_sensor_data_api(): void
    {
        SensorLog::create([
            'gas_value' => 700,
            'flame_detected' => true,
        ]);

        $response = $this->getJson('/api/sensor/latest');

        $response->assertStatus(200)
                 ->assertJson([
                     'latest_reading' => [
                         'gas_value' => 700,
                         'flame_detected' => true,
                     ],
                     'system_status' => 'FIRE DETECTED',
                     'status_color' => 'red',
                 ]);
    }

    /**
     * Test GET /api/sensor/history endpoint.
     */
    public function test_get_sensor_history_api(): void
    {
        SensorLog::create([
            'gas_value' => 400,
            'flame_detected' => false,
        ]);
        SensorLog::create([
            'gas_value' => 500,
            'flame_detected' => false,
        ]);

        $response = $this->getJson('/api/sensor/history?limit=5');

        $response->assertStatus(200)
                 ->assertJsonCount(2)
                 ->assertJsonFragment([
                     'gas_value' => 400,
                     'flame_detected' => false,
                 ])
                 ->assertJsonFragment([
                     'gas_value' => 500,
                     'flame_detected' => false,
                 ]);
    }
}
