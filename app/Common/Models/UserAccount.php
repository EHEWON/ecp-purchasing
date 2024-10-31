<?php

namespace App\Common\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Lumen\Auth\Authorizable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use App\Common\Models\RoleUsers;
use App\Common\Models\Roles;

class UserAccount extends Model implements AuthenticatableContract, AuthorizableContract, JWTSubject {

    use Authenticatable,
        Authorizable,
        HasFactory;
    protected $table = "user";
    protected $primaryKey = 'user_id';
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'username', 'email', 'password'
    ];
    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var string[]
     */
    protected $hidden = [
        'password',
    ];

    /**
     * JWT
     *
     * @author AdamTyn
     */
    public function getJWTIdentifier() {
        return $this->getKey();
    }

    /**
     * JWT
     *
     * @author AdamTyn
     */
    public function getJWTCustomClaims() {
        return [];
    }

    public function roles() {
        return $this->belongsToMany(RoleUsers::class, Roles::class, 'user_id', 'role_id')->withPivot(['user_id', 'role_id']);
    }

}
