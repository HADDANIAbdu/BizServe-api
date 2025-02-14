<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Client extends Authenticatable implements JWTSubject
{
    use HasFactory;
    use HasApiTokens, HasFactory, Notifiable;
    use SoftDeletes;

    protected $table = 'clients'; 


    protected $dates = ['deleted_at'];
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'firstname',
        'lastname',
        'email',
        'phone',
        'preference',
        'password'
    ];
    protected $hidden = [
        'password',
    ];
    protected $casts = [
        'password' => 'hashed',
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function enrollService($serviceName)
    {
        $service = Service::where('name', $serviceName)->firstOrFail();
        $this->services()->syncWithoutDetaching($service->id);
    }

    public function softDetachService($serviceId)
    {
        DB::table('client_service')
            ->where('client_id', $this->id)
            ->where('service_id', $serviceId)
            ->update(['deleted_at' => now()]);
    }
    public function restoreService($serviceId)
    {
        DB::table('client_service')
            ->where('client_id', $this->id)
            ->where('service_id', $serviceId)
            ->update(['deleted_at' => null]);
    }


    public function services()
    {
        return $this->belongsToMany(Service::class, 'client_service')->withPivot('enrollement_date');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function interactions()
    {
        return $this->hasMany(Interaction::class);
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }
}
