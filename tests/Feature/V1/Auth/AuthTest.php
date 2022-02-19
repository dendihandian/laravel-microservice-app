<?php

namespace Tests\Feature\V1\Auth;

use App\Models\User;
use Database\Factories\UserFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Feature\V1\V1TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class AuthTest extends V1TestCase
{
    use DatabaseMigrations;

    /**
     * Register with valid data
     *
     * @return void
     */
    public function test_register_with_valid_data()
    {
        $requestBody = [
            'name' => 'dendihandian',
            'email' => 'dendihandian@spacex.com',
            'password' => 'thesecretpassword',
        ];

        $this
            ->withHeaders(['Accept' => 'application/json'])
            ->post("api/{$this->apiVersion}/auth/register", $requestBody)
            ->assertOk();
    }

    /**
     * Register with invalid data.
     *
     * @return void
     */
    public function test_register_with_invalid_data()
    {
        $requestBody = [
            'name' => '',
            'email' => '',
            'password' => '',
        ];

        $this
            ->withHeaders(['Accept' => 'application/json'])
            ->post("api/{$this->apiVersion}/auth/register", $requestBody)
            ->assertUnprocessable();
    }

    /**
     * Login with valid data
     *
     * @return void
     */
    public function test_login_with_valid_data()
    {
        $createdUser = UserFactory::new()->create();

        $requestBody = [
            'email' => $createdUser->email,
            'password' => 'password',
        ];

        $this
            ->withHeaders(['Accept' => 'application/json'])
            ->post("api/{$this->apiVersion}/auth/login", $requestBody)
            ->assertOk()
            ->assertJsonStructure([
                'token',
                'user',
            ])
            ->assertJsonFragment(['id' => $createdUser->id])
            ->assertJsonFragment(['name' => $createdUser->name])
            ->assertJsonFragment(['email' => $createdUser->email]);
    }

    /**
     * Login with invalid data.
     *
     * @return void
     */
    public function test_login_with_invalid_data()
    {
        $createdUser = UserFactory::new()->create();

        $requestBody = [
            'email' => $createdUser->email,
            'password' => 'wrongpassword',
        ];

        $this
            ->withHeaders(['Accept' => 'application/json'])
            ->post("api/{$this->apiVersion}/auth/login", $requestBody)
            ->assertStatus(400);
    }

    /**
     * Getting the authenticated user data with valid token.
     *
     * @return void
     */
    public function test_get_authenticated_user_data_with_valid_token()
    {
        $createdUser = UserFactory::new()->create();
        $token = $createdUser->createToken('mobile')->plainTextToken;

        $this
            ->withHeaders(['Accept' => 'application/json', 'Authorization' => 'Bearer ' . $token])
            ->get("api/{$this->apiVersion}/auth/user")
            ->assertOk()
            ->assertJsonFragment(['id' => $createdUser->id])
            ->assertJsonFragment(['name' => $createdUser->name])
            ->assertJsonFragment(['email' => $createdUser->email]);
    }

    /**
     * Getting the authenticated user data with invalid token.
     *
     * @return void
     */
    public function test_get_authenticated_user_data_with_invalid_token()
    {
        $createdUser = UserFactory::new()->create();
        $correctToken = $createdUser->createToken('mobile')->plainTextToken;

        $this
            ->withHeaders(['Accept' => 'application/json', 'Authorization' => 'Bearer ' . 'wrong token'])
            ->get("api/{$this->apiVersion}/auth/user")
            ->assertUnauthorized()
            ->assertDontSee($correctToken);
    }

    /**
     * Refresh token with valid token.
     *
     * @return void
     */
    public function test_refresh_token_with_valid_token()
    {
        $createdUser = UserFactory::new()->create();
        $token = $createdUser->createToken('mobile')->plainTextToken;

        $this
            ->withHeaders(['Accept' => 'application/json', 'Authorization' => 'Bearer ' . $token])
            ->post("api/{$this->apiVersion}/auth/token/refresh")
            ->assertOk()
            ->assertJsonStructure([
                'token',
                'user',
            ])
            ->assertJsonFragment(['id' => $createdUser->id])
            ->assertJsonFragment(['name' => $createdUser->name])
            ->assertJsonFragment(['email' => $createdUser->email]);
    }

    /**
     * Refresh token with invalid token.
     *
     * @return void
     */
    public function test_refresh_token_with_invalid_token()
    {
        $createdUser = UserFactory::new()->create();
        $correctToken = $createdUser->createToken('mobile')->plainTextToken;

        $this
            ->withHeaders(['Accept' => 'application/json', 'Authorization' => 'Bearer ' . 'wrong token'])
            ->post("api/{$this->apiVersion}/auth/token/refresh")
            ->assertDontSee($correctToken)
            ->assertUnauthorized();
    }

    /**
     * revoke token with valid token.
     *
     * @return void
     */
    public function test_revoke_token_with_valid_token()
    {
        $createdUser = UserFactory::new()->create();
        $token = $createdUser->createToken('mobile')->plainTextToken;

        $this
            ->withHeaders(['Accept' => 'application/json', 'Authorization' => 'Bearer ' . $token])
            ->post("api/{$this->apiVersion}/auth/token/revoke")
            ->assertOk();
    }

    /**
     * revoke token with invalid token.
     *
     * @return void
     */
    public function test_revoke_token_with_invalid_token()
    {
        $createdUser = UserFactory::new()->create();
        $correctToken = $createdUser->createToken('mobile')->plainTextToken;

        $this
            ->withHeaders(['Accept' => 'application/json', 'Authorization' => 'Bearer ' . 'wrong token'])
            ->post("api/{$this->apiVersion}/auth/token/revoke")
            ->assertDontSee($correctToken)
            ->assertUnauthorized();
    }
}
