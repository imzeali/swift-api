<?php
/**
 * Created by PhpStorm.
 * User: zeali
 * Date: 2019-03-29
 * Time: 16:19
 */

namespace SwiftApi\Traits;


use Illuminate\Support\Facades\Cache;

trait WithSerialNumber
{
    protected static function bootWithSerialNumber()
    {
        static::creating(function ($model) {

            $model->number = self::generateNumber();
        });
    }

    public static function generateNumber($prefix)
    {
        $key = "ORDER_NUMBER_COUNT:{$prefix}:";
        return Cache::lock($key, 10)->block(10, function () use ($key) {
            if (is_null(Cache::get($key))) {
                $number_count = self::resetCount($key);
            } else {
                $number_count = Cache::increment($key);
            }
            return date('Ymd') . $number_count . str_pad($number_count, 4, '0', STR_PAD_LEFT);
        });


    }

    public static function resetCount($key)
    {
        $today_start_data = date("Y-m-d 00:00:00");
        $today_end_data = date("Y-m-d 23:59:59");
        $today_end_str = strtotime($today_end_data);
        $expire_time = $today_end_str - time();
        $max_count = (int)substr(self::whereBetween('created_at', [$today_start_data, $today_end_data])->lockForUpdate()->max('number'), -4);
        Cache::set($key, $max_count, $expire_time);
        return $max_count;
    }

    public function getNumberAttribute()
    {
        return $this->number_prefix ? $this->number_prefix.$this->number : "NO{$this->number}";
    }
}
