<?php

namespace SwiftApi\Controllers;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FsmController extends ResourceController
{

    public function transition(Request $request, $id)
    {
        $object = $this->getObject($id);
        $transition = $request->get('transition',$request->input('transition'));
        $data = $request->input('data', []);
        return DB::transaction(function () use ($object, $transition, $data) {
            $result = $object->$transition($data);
            $object->push();
            return $result;
        }, 5);
    }

    public function transitions($id)
    {
        return $this->getObject($id)->getTransitions();
    }


    public function operations_logs($id)
    {
        return $this->paginator($this->filter($this->collator($this->getObject($id)->fsm_logs())));
    }

    public function states()
    {
        return $this->getModelInstance()->getStates();
    }

    public function transitionNotice(Request $request)
    {
        $odd_number = $request->input('odd_numbers');
        $transition = $request->input('transition');
        $data = $request->input('other_data');
        if (!is_null($object = $this->getModelInstance()->where('remote_number', $odd_number)->first())){
            return DB::transaction(function () use ($object, $transition, $data) {
                $result = $object->$transition($data);
                $object->push();
                return ['receive'=>'success'];
            }, 5);
        }else{
            abort(404,'资源未找到');
        }
    }
}
