<?php

namespace Laravel\Telescope\Storage;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ApplicationModel extends Model
{

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'telescope_applications';

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

    protected $fillable = ["name", "uuid"];

    /**
     * Get the current connection name for the model.
     *
     * @return string
     */
    public function getConnectionName()
    {
        return config('telescope.storage.database.connection');
    }
}
