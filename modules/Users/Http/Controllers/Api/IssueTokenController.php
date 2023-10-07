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

namespace Modules\Users\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Modules\Core\Http\Controllers\ApiController;
use Modules\Users\Models\User;

class IssueTokenController extends ApiController
{
    /**
     * Exchange Token
     *
     * Exchange new token for a given valid username and password
     *
     * The endpoint will return the plain-text token which may then be stored on the mobile device or other storage
     * and used to make additional API requests.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
            'device_name' => 'required|string|max:191',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => [__('auth.failed')],
            ]);
        }

        return $this->response([
            'accessToken' => $user->createToken($request->device_name)->plainTextToken,
            'userId' => $user->id,
            'email' => $user->email,
            'name' => $user->name,
        ]);
    }
}
