<?php


namespace SwiftApi\Traits;


use Illuminate\Support\Facades\Auth;

/**
 * Trait WithCreator
 * @package App\Models\Common
 * @property-read Users creator
 */
trait WithCreator
{

    protected static function bootWithCreator()
    {
        static::creating(function ($model) {
            if (!array_key_exists('creator_id',$model->attributes)
                ||is_null($model->attributes['creator_id'])) {
                $model->creator_id = Auth::id();
            }
        });
    }

    public function creator()
    {
        return $this->belongsTo(config('api.database.users_model'), 'creator_id');
    }

}
