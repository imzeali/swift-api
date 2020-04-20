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
            Cache::lock($model->getKeyLockName())->get(function () use ($model) {
                $model->number = $model->generateNumber($model->getSerialNumberKey());
            });
        });
    }

    public function getKeyLockName()
    {
        return "{$this->getSerialNumberKey()}:LOCK";
    }

    public function getSerialNumberKey()
    {
        return "SERIA_NUMBER_COUNT:{$this->getNumberPrefix()}:";

    }

    public function generateNumber($key)
    {
        if (is_null(Cache::get($key))) {
            $number_count = $this->resetCount($key);
        } else {
            $number_count = Cache::increment($key);
        }
        return date('Ymd') . $number_count . str_pad($number_count, 4, '0', STR_PAD_LEFT);
    }

    public function resetCount($key)
    {
        $max_count = (int)substr($this->whereBetween('created_at', [today(), today()->addHour('24')])->lockForUpdate()->max('number'), -4);
        Cache::put($key, $max_count, today()->addHour('24'));

        return $max_count;
    }

    public function getNumberPrefix()
    {
        return 'NO';
    }

    public function getNumberAttribute($value)
    {
        return $this->getNumberPrefix() . $value;
    }
}
