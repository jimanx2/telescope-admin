<?php

namespace Laravel\Telescope\Storage;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Telescope\Database\Factories\AdminModelFactory;
use Illuminate\Support\Str;

class AdminModel extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'telescope_admins';

    /**
     * The name of the "updated at" column.
     *
     * @var string
     */
    const UPDATED_AT = null;

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'uuid';

    /**
     * The "type" of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Prevent Eloquent from overriding uuid with `lastInsertId`.
     *
     * @var bool
     */
    public $incrementing = false;

    protected $fillable = ["email", "password"];
    protected $casts = [
        "allowed_applications" => "json"
    ];
    protected $appends = [
        "applications"
    ];

    /**
     * Get the current connection name for the model.
     *
     * @return string
     */
    public function getConnectionName()
    {
        return config('telescope.storage.database.connection');
    }

    /**
     * Create a new factory instance for the model.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public static function newFactory()
    {
        return AdminModelFactory::new();
    }

    public static function boot()
    {
        parent::boot();

        self::saving(function($model){
            
            if (is_null($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
            
            if (!empty($model->original) && $model->password != $model->original["password"]) {
                $model->password = password_hash($model->password, PASSWORD_DEFAULT);
            }
        });
    }

    public function getApplicationsAttribute()
    {
        return ApplicationModel::whereIn("uuid", $this->allowed_applications)
            ->pluck('name', 'uuid')
            ->toArray();
    }
}
