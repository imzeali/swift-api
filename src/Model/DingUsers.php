<?php
/**
 * User: babybus zhili
 * Date: 2019-04-29 13:55
 * Email: <zealiemai@gmail.com>
 */

namespace SwiftApi\Model;

use Illuminate\Database\Eloquent\Model;


class DingUsers extends Model
{
    protected $guarded = [];
    protected $appends = ['work_day_total'];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->setTable(config('api.database.ding_users_table'));
    }

    public static function getDingUsersByUserid($ding_userid)
    {
        return self::where('userid', '=', $ding_userid)->first();
    }

    public function user()
    {
        return $this->belongsTo(config('api.database.users_model'), 'users_id', 'id');
    }

    public function getHiredDateAttribute()
    {
        return date("Y-m-d H:i:s", $this->attributes['hiredDate'] / 1000);;
    }

    public function getWorkDayTotalAttribute()
    {
        if ($this->attributes['hiredDate'] > 0) {
            return (int)round((time() - $this->attributes['hiredDate'] / 1000) / 60 / 60 / 24);
        }
    }
}
