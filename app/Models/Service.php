<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;


class Service extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $dates = ['deleted_at'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'price',
        'category',
        'duration',
    ];

    public function softDetachClient($clientId)
    {
        DB::table('client_service')
            ->where('service_id', $this->id)
            ->where('client_id', $clientId)
            ->update(['deleted_at' => now()]);
    }
    public function restoreClient($clientId)
    {
        DB::table('client_service')
            ->where('service_id', $this->id)
            ->where('client_id', $clientId)
            ->update(['deleted_at' => null]);
    }

    public function clients()
    {
        return $this->belongsToMany(Client::class, 'client_service')->withPivot('enrollement_date');
    }
    public function payments()
    {
        return $this->hasMany(Payment::class);
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
