<?php

namespace App\Modules\V1\Authentication\Controllers;

use App\Modules\Controller;
use Symfony\Component\HttpFoundation\Response;
use App\Modules\V1\Authentication\Requests\LoginRequest;
use App\Modules\V1\Authentication\Requests\RegisterRequest;
use DB;
use Illuminate\Support\Facades\Auth;
use App\Modules\V1\Authentication\Services\AuthenticationService;

class AuthenticateController extends Controller
{
    public $authService;

    /**
     * AuthenticateController constructor.
     *
     * @param AuthenticationService $authService authService
     *
     * @return void
     */
    public function __construct(AuthenticationService $authService)
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
        $this->authService = $authService;
    }

    /**
     * Login
     *
     * @param LoginRequest $request Request
     *
     * @return App\Shared\Traits\ApiResponser;
     *
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function login(LoginRequest $request)
    {
        $authenticate = $this->authService->authenticate($request->all());

        $this->setMeta(__('messages.request_success'))
            ->setData($authenticate['data']);

        return $this->jsonOut();
    }

    /**
     * Logout
     *
     * @return mixed
     */
    public function logout()
    {
        $accessToken = Auth::user()->token();

        DB::table('oauth_refresh_tokens')
            ->where('access_token_id', $accessToken->id)
            ->update([
                'revoked' => true
            ]);
        $accessToken->revoke();

        return $this->setStatus(Response::HTTP_NO_CONTENT)->jsonOut();
    }

    /**
     * Get current user info
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public function getCurrentUser()
    {
        $user = $this->authService->currentUser();

        return $this->setStatus(Response::HTTP_OK)
            ->setMeta(__('messages.request_success'))
            ->setData($user)
            ->jsonOut();
    }

    /**
     * Register
     *
     * @param RegisterRequest $request Request
     *
     * @return App\Shared\Traits\ApiResponser;
     *
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function register(RegisterRequest $request)
    {
        $authenticate = $this->authService->register($request->all());

        $this->setMeta(__('messages.request_success'))
            ->setData($authenticate['data']);

        return $this->jsonOut();
    }
}
