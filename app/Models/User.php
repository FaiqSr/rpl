<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $table = 'users';
    protected $primaryKey = 'user_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = true;
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'role'
    ];

    public function addresses(): HasMany
    {
        return $this->hasMany(UserAddress::class, 'user_id', 'user_id');
    }

    public function article(): HasMany
    {
        return $this->hasMany(Article::class, 'user_id', 'user_id');
    }

    public function insertNewUser($data)
    {
        $newUuid = (string) Str::uuid();

        DB::table("$this->table")->insert([
            'user_id' => $newUuid,
            'username' => $data['username'],
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'created_at' => Date::now(),
            'updated_at' => Date::now()
        ]);

        return $newUuid;
    }

    public function getUserByEmailOrEmail($data)
    {
        $query = DB::table("$this->table as u")->where('username', $data['username'])->orWhere('email', $data['email'])->select('u.user_id')->first();
        return $query;
    }
}
