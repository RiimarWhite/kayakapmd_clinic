<?php

namespace Tests\Feature;

use App\Models\DoctorModel;
use App\Models\SecretaryModel;
use Database\Seeders\AdminSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Detailed Comment: Seed required admin, doctor, and secretary test records
     * prior to each test run in an isolated in-memory SQLite testing environment.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AdminSeeder::class);
        $this->seed(UserSeeder::class);
    }

    /**
     * Detailed Comment: Verify that an unauthenticated guest attempting to access
     * the doctor dashboard is cleanly intercepted and redirected to the login page.
     */
    public function test_unauthenticated_user_redirected_from_doctor_dashboard(): void
    {
        $response = $this->get('/doctor/dashboard');

        $response->assertStatus(302);
        $response->assertRedirect(route('login'));
    }

    /**
     * Detailed Comment: Verify that an unauthenticated guest attempting to access
     * the admin dashboard is cleanly intercepted and redirected to the login page.
     */
    public function test_unauthenticated_user_redirected_from_admin_dashboard(): void
    {
        $response = $this->get('/admin/dashboard');

        $response->assertStatus(302);
        $response->assertRedirect(route('login'));
    }

    /**
     * Detailed Comment: Verify that an unauthenticated guest attempting to access
     * the secretary queue page is cleanly intercepted and redirected to the login page.
     */
    public function test_unauthenticated_user_redirected_from_secretary_queue(): void
    {
        $response = $this->get('/secretary/queue');

        $response->assertStatus(302);
        $response->assertRedirect(route('login'));
    }

    /**
     * Detailed Comment: Verify doctor can authenticate via username and access doctor dashboard.
     */
    public function test_doctor_can_login_via_username(): void
    {
        $response = $this->post('/login', [
            'username' => 'doctor',
            'password' => '12345'
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('doctor'));

        // Follow redirect to dashboard
        $dashResponse = $this->get('/doctor/dashboard');
        $dashResponse->assertStatus(200);
    }

    /**
     * Detailed Comment: Verify doctor can authenticate via email address (eadd).
     */
    public function test_doctor_can_login_via_email(): void
    {
        $response = $this->post('/login', [
            'username' => 'doctor.dummy@gmail.com',
            'password' => '12345'
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('doctor'));
    }

    /**
     * Detailed Comment: Verify doctor can authenticate via reference number (docrefno).
     */
    public function test_doctor_can_login_via_docrefno(): void
    {
        $doctor = DoctorModel::where('username', 'doctor')->first();
        $this->assertNotNull($doctor);

        $response = $this->post('/login', [
            'username' => $doctor->docrefno,
            'password' => '12345'
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('doctor'));
    }

    /**
     * Detailed Comment: Verify secretary can authenticate via username.
     */
    public function test_secretary_can_login_via_username(): void
    {
        $response = $this->post('/login', [
            'username' => 'secretary',
            'password' => '12345'
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('secretary'));

        // Follow redirect to queue
        $queueResponse = $this->get('/secretary/queue');
        $queueResponse->assertStatus(200);
    }

    /**
     * Detailed Comment: Verify secretary can authenticate via last name (seclname).
     */
    public function test_secretary_can_login_via_lastname(): void
    {
        $response = $this->post('/login', [
            'username' => 'Doe',
            'password' => '12345'
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('secretary'));
    }

    /**
     * Detailed Comment: Verify secretary can authenticate via email address (secemail).
     */
    public function test_secretary_can_login_via_email(): void
    {
        $response = $this->post('/login', [
            'username' => 'secretary.dummy@gmail.com',
            'password' => '12345'
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('secretary'));
    }

    /**
     * Detailed Comment: Verify secretary can authenticate via ID number (secidno).
     */
    public function test_secretary_can_login_via_id_number(): void
    {
        $secretary = SecretaryModel::where('username', 'secretary')->first();
        $this->assertNotNull($secretary);

        $response = $this->post('/login', [
            'username' => $secretary->secidno,
            'password' => '12345'
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('secretary'));
    }

    /**
     * Detailed Comment: Verify admin can authenticate with username and password.
     */
    public function test_admin_can_login_via_username(): void
    {
        $response = $this->post('/login', [
            'username' => 'admin',
            'password' => 'admin123'
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('admin'));
    }

    /**
     * Detailed Comment: Verify that invalid credentials return back with validation errors.
     */
    public function test_invalid_credentials_rejected(): void
    {
        $response = $this->from('/login')->post('/login', [
            'username' => 'nonexistent_user',
            'password' => 'wrongpassword'
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('password');
    }

    /**
     * Detailed Comment: Verify that standard web form submission to logout
     * invalidates session data and redirects the client to the login route.
     */
    public function test_web_logout_invalidates_session_and_redirects_to_login(): void
    {
        $response = $this->post('/logout');

        $response->assertStatus(302);
        $response->assertRedirect(route('login'));
    }

    /**
     * Detailed Comment: Verify that an AJAX/Fetch request to logout returns
     * JSON payload with success indicator and the dynamic redirect URL.
     */
    public function test_ajax_logout_returns_json_with_redirect_url(): void
    {
        $response = $this->postJson('/logout');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'redirect' => route('login'),
        ]);
    }

    /**
     * Detailed Comment: Verify that the PreventBackHistory middleware attaches
     * anti-cache headers (no-store, must-revalidate) to web responses so that
     * browsers cannot display authenticated dashboards from Back-Forward cache.
     */
    public function test_prevent_back_history_middleware_sets_anticache_headers(): void
    {
        $response = $this->get('/login');

        $response->assertHeader('Cache-Control');
        $cacheControl = $response->headers->get('Cache-Control');

        $this->assertStringContainsString('no-store', $cacheControl);
        $this->assertStringContainsString('must-revalidate', $cacheControl);
        $this->assertStringContainsString('no-cache', $cacheControl);
        $this->assertEquals('no-cache', $response->headers->get('Pragma'));
    }

    /**
     * Detailed Comment: Verify that the login page cleanly renders with HTTP 200 OK
     * and contains all necessary form inputs and CSRF protection.
     */
    public function test_login_page_renders_successfully_with_http_200(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('Username');
        $response->assertSee('Password');
        $response->assertSee('Login');
        $response->assertSee('name="_token"', false);
    }

    /**
     * Detailed Comment: Verify that the logging stack channel has ignore_exceptions enabled
     * so that unexpected logging or stream errors never fail user-facing web requests.
     */
    public function test_logging_configuration_has_ignore_exceptions_enabled(): void
    {
        $this->assertTrue(config('logging.channels.stack.ignore_exceptions'));
    }

    /**
     * Detailed Comment: Verify that file logging channels enforce a 0666 permission mask
     * to prevent access conflicts between Docker CLI commands (root) and web server (www-data).
     */
    public function test_file_logging_channels_configure_permissive_mask(): void
    {
        $this->assertEquals(0666, config('logging.channels.single.permission'));
        $this->assertEquals(0666, config('logging.channels.daily.permission'));
    }
}
