<?php
/**
 * Concord CRM - https://www.concordcrm.com
 *
 * @version   1.3.1
 *
 * @link      Releases - https://www.concordcrm.com/releases
 * @link      Terms Of Service - https://www.concordcrm.com/terms
 *
 * @copyright Copyright (c) 2022-2023 KONKORD DIGITAL
 */

namespace Modules\Users\Tests\Feature\Controller\Api;

use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class IssueTokenControllerTest extends TestCase
{
    public function test_logged_in_user_cannot_access_issue_token_controller_endpoints()
    {
        $user = $this->withUserAttrs([
            'password' => Hash::make('password'),
            'access_api' => true,
        ])->createUser();

        $this->signIn($user);

        $this->postJson('/api/token')->assertRedirect(RouteServiceProvider::HOME);
    }

    public function test_token_can_be_exchanged_by_providing_valid_email_and_password()
    {
        $user = $this->withUserAttrs([
            'password' => Hash::make('password'),
            'access_api' => true,
        ])->createUser();

        $this->postJson('/api/token', [
            'email' => $user->email,
            'password' => 'password',
            'device_name' => 'Test Suite',
        ])->assertJsonStructure(['accessToken', 'userId', 'email', 'name']);
    }

    public function test_issue_token_requires_email()
    {
        $this->postJson('/api/token', [
            'email' => '',
        ])->assertJsonValidationErrors('email');
    }

    public function test_issue_token_requires_valid_email()
    {
        $this->postJson('/api/token', [
            'email' => 'dummy',
        ])->assertJsonValidationErrors('email');
    }

    public function test_issue_token_requires_password()
    {
        $this->postJson('/api/token', [
            'password' => '',
        ])->assertJsonValidationErrors('password');
    }

    public function test_issue_token_requires_device_name()
    {
        $this->postJson('/api/token', [
            'device_name' => '',
        ])->assertJsonValidationErrors('device_name');
    }

    public function test_issue_token_requires_valid_user()
    {
        $this->postJson('/api/token', [
            'email' => 'invalid-user@example.com',
            'password' => 'password',
            'device_name' => 'Test Suite',
        ])->assertJsonValidationErrors('email');
    }

    public function test_issue_token_requires_corret_authentication_details()
    {
        $user = $this->withUserAttrs([
            'access_api' => true,
        ])->createUser();

        $this->postJson('/api/token', [
            'email' => $user->email,
            'password' => 'invalid-password',
            'device_name' => 'Test Suite',
        ])->assertJsonValidationErrors('email');
    }
}
