<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthService extends Service
{
    protected $userModel;



    function __construct(User $userModel)
    {
        $this->userModel = $userModel;
    }

    public function createNewUser(Request $request)
    {
        $request->only('username', 'email', 'password', 'name');
        $request->validate([
            'username' => 'string|required|min:4',
            'email' => 'string|email|required',
            'password' => 'string|required|min:8',
            'name' => 'string|required'
        ]);

        $userData = [
            'username' => $request->username,
            'password' => bcrypt($request->password),
            'email' => $request->email,
            'name' => $request->name
        ];


        $checkingIfUsernameOrEmailExists = $this->userModel->getUserByEmailOrEmail($userData);


        if ($checkingIfUsernameOrEmailExists) {
            return $this->jsonErrorResponse([
                'message' => "Username atau email telah digunakan"
            ], 400);
        }

        $userUuid = $this->userModel->insertNewUser($userData);

        if (!$userUuid) {
            return $this->jsonErrorResponse([
                'Message' => 'Gagal membuat user baru'
            ], 500);
        }

        return $this->jsonResponse([
            'message' => 'Berhasil membuat user baru',
            'Uuid_user' => $userUuid
        ], 201);
    }

    public function loginUser(Request $request)
    {
        $request->only('usernameOrEmail', 'password');
        $request->validate(
            [
                'usernameOrEmail' => 'string|required|min:4',
                'password' => 'string|min:8|required'
            ],
            [
                'usernameOrEmail.required' => 'Username Atau Email wajib di isi',
                'usernameOrEmail.min' => "Username atau Email minimal 4 huruf",
                'password.min' => "Password minimal 8 huruf",
                "password.required" => "Password wajib di isi"
            ]
        );

        $loginData = [
            'username' => $request->usernameOrEmail,
            'email' => $request->usernameOrEmail,
            'password' => $request->password
        ];

        $userData = $this->userModel->getUserByEmailOrEmail($loginData);

        if (!$userData) {
            return $this->jsonErrorResponse([
                'Message' => "Username atau Email tidak terdaftar"
            ]);
        }

        if (!Hash::check($loginData['password'], $userData->password)) {
            return $this->jsonErrorResponse([
                'Message' => "Password "
            ]);
        }
    }
}
