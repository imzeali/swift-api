<?php

namespace SwiftApi\Model;

use SwiftApi\Auth\Database\HasPermissions;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Class Administrator.
 *
 * @property Role[] $roles
 */
class Administrator extends Model implements AuthenticatableContract
{
    use Authenticatable, HasPermissions;

    protected $fillable = ['username', 'password', 'name', 'avatar', 'api_token'];
    protected $hidden = ['password', 'api_token', 'remember_token'];
    protected $with = ['ding_users'];
    /**
     * Create a new Eloquent model instance.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        $connection = config('api.database.connection') ?: config('database.default');

        $this->setConnection($connection);

        $this->setTable(config('api.database.users_table'));

        parent::__construct($attributes);
    }

    /**
     * A user has and belongs to many roles.
     *
     * @return BelongsToMany
     */
    public function roles(): BelongsToMany
    {
        $pivotTable = config('api.database.role_users_table');

        $relatedModel = config('api.database.roles_model');

        return $this->belongsToMany($relatedModel, $pivotTable, 'user_id', 'role_id');
    }

    /**
     * A User has and belongs to many permissions.
     *
     * @return BelongsToMany
     */
    public function permissions(): BelongsToMany
    {
        $pivotTable = config('api.database.user_permissions_table');

        $relatedModel = config('api.database.permissions_model');

        return $this->belongsToMany($relatedModel, $pivotTable, 'user_id', 'permission_id');
    }

    public function updateApiToken()
    {
        $token = Str::random(60);

        $this->forceFill([
            'api_token' => $token,
        ])->save();

        return $token;
    }
}
