<?php

namespace SwiftApi\Model;


use Illuminate\Database\Eloquent\Model;
use SwiftApi\Traits\WithCreator;

class FsmLogs extends Model
{
    //
    use WithCreator;
    protected $with = ['creator'];

    protected $fillable = ['from', 'to', 'transition', 'parameters', 'remark'];

    protected $appends = ['model_label', 'transition_label'];

    public function getModelLabelAttribute()
    {
        return __($this->attributes['fsm_logs_able_type']);
    }

    public function getTransitionLabelAttribute()
    {
        return $this->fsm_logs_able->getTransitionLabel($this->attributes['transition']);
    }


    public function fsm_logs_able()
    {
        return $this->morphTo();
    }
}
