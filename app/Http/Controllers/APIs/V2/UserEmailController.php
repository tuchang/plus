<?php

declare(strict_types=1);

/*
 * +----------------------------------------------------------------------+
 * |                          ThinkSNS Plus                               |
 * +----------------------------------------------------------------------+
 * | Copyright (c) 2018 Chengdu ZhiYiChuangXiang Technology Co., Ltd.     |
 * +----------------------------------------------------------------------+
 * | This source file is subject to version 2.0 of the Apache license,    |
 * | that is bundled with this package in the file LICENSE, and is        |
 * | available through the world-wide-web at the following url:           |
 * | http://www.apache.org/licenses/LICENSE-2.0.html                      |
 * +----------------------------------------------------------------------+
 * | Author: Slim Kit Group <master@zhiyicx.com>                          |
 * | Homepage: www.thinksns.com                                           |
 * +----------------------------------------------------------------------+
 */

namespace Zhiyi\Plus\Http\Controllers\APIs\V2;

use Illuminate\Http\Request;
use Zhiyi\Plus\Models\VerificationCode as VerificationCodeModel;
use Illuminate\Contracts\Routing\ResponseFactory as ResponseFactoryContract;

class UserEmailController extends Controller
{
    /**
     * 解除用户 E-Mail 绑定.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Illuminate\Contracts\Routing\ResponseFactory $response
     * @return mixed
     * @author Seven Du <shiweidu@outlook.com>
     */
    public function delete(Request $request, ResponseFactoryContract $response)
    {
        $rules = [
            'verifiable_code' => 'required|string',
            'password' => 'required|string',
        ];
        $this->validate($request, $rules);

        $verifiable_code = $request->input('verifiable_code');
        $password = $request->input('password');
        $user = $request->user();
        $verify = VerificationCodeModel::where('channel', 'mail')
            ->where('account', $user->email)
            ->where('code', $verifiable_code)
            ->orderby('id', 'desc')
            ->first();

        if (! $user->verifyPassword($password)) {
            return $response->json(['message' => ['密码错误']], 422);
        } elseif (! $verify) {
            return $response->json(['message' => ['验证码错误或者已失效']], 422);
        }

        $user->getConnection()->transaction(function () use ($user, $verify) {
            $user->email = null;
            $user->save();
            $verify->delete();
        });

        return $response->make('', 204);
    }
}
