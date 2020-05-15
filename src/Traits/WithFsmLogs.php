<?php


namespace SwiftApi\Traits;


trait WithFsmLogs
{

    public function fsm_logs()
    {
        return $this->morphMany(config('api.database.finite_state_machine_log_model'), 'fsm_logs_able')->orderBy('id', 'desc');
    }

    public function operation($transition)
    {
        return $this->fsm_logs()->where('transition', '=', $transition)->orderBy('id', 'desc')->first();
    }

    public function operations($transition)
    {
        if (is_array($transition)) {
            return $this->fsm_logs()->whereIn('transition', $transition);
        } else {
            return $this->fsm_logs()->where('transition', '=', $transition);
        }
    }

    public function operating_time($transition)
    {
        $logs = $this->fsm_logs()->where('transition', '=', $transition)->orderBy('id', 'desc')->first();

        if (is_null($logs)) {
            return null;
        }

        return $logs->created_at;

    }

}
