<?php
/**
 * User: babybus zhili
 * Date: 2019-04-11 10:07
 * Email: <zealiemai@gmail.com>
 */

namespace SwiftApi\Traits;


use Finite\Loader\ArrayLoader;
use Finite\StateMachine\StateMachine;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/**
 * Trait WithStateMachine
 * 使用说明 http://md.baby-bus.com/web/#/16?page_id=1119
 * @package App\Model
 **/
trait WithStateMachine
{

    use WithFsmLogs;

    public $fsm;
    public $remark;
    protected $operator = null;
    protected $observables = [

    ];


    /**
     * 状态机引导初始化
     * 这里会被模型实例化的时候自动触发
     */
    protected static function bootWithStateMachine()
    {

        static::retrieved(function ($model) {
            /** @var $model WithStateMachine */
            $model->initStateMachine($model);
        });

        static::creating(function ($model) {
            /** @var $model WithStateMachine */
            $model->initStateMachine($model);
        });

        static::created(function ($model) {
            /** @var $model WithStateMachine */
            $model->insertFsmLog($model, 'INIT', 'init', $model->remark);
        });
    }

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->initStateMachine($this);
    }

    /**
     * 状态机事件拦截
     * @param $method
     * @param $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        if (isset($this->transitions[$method])) {
            $from = $this->getFiniteState();
            if (!is_null($parameters = array_key_exists(0, $parameters) ? $parameters[0] : $parameters)) {
                $this->remark = Arr::get($parameters, 'remark');
            }
            $res = $this->fsm->apply($method, (array)$parameters);
            $this->save();
            //insert log
            if ($this->log_enable !== false) {
                $this->insertFsmLog($this, $from, $method, $this->remark);
            }
            return $res;
        } else {
            return parent::__call($method, $parameters);
        }


    }

    private function insertFsmLog($model, $from, $transition, $remark = null)
    {
        /** @var WithStateMachine $model */
        $model->fsm_logs()->create([
            "from" => $from,
            "to" => $model->getFiniteState(),
            "transition" => $transition,
            "remark" => $remark,
            "creator_id" => $model->getOperator()->id
        ]);

    }

    private function initFsmObserverEvent($model)
    {
        foreach ($this->transitions as $transition => $item) {
            $before_do = Str::camel("before_{$transition}");
            $after_do = Str::camel("after_{$transition}");
            $model->observables[] = $before_do;
            $model->observables[] = $after_do;
            $model->fsm->getDispatcher()->addListener('finite.pre_transition.' . $model->fsm->getGraph() . '.' . $transition,
                function (\Finite\Event\TransitionEvent $e) use ($model, $before_do) {
                    $model->fireModelEvent($before_do, false);
                }
            );
            $model->fsm->getDispatcher()->addListener('finite.post_transition.' . $model->fsm->getGraph() . '.' . $transition,
                function (\Finite\Event\TransitionEvent $e) use ($model, $after_do) {
                    $model->save();
                    $model->fireModelEvent($after_do, false);
                }
            );
        }
    }

    private function initFsmCallbacks($model, &$configs)
    {
        foreach ($this->transitions as $transition => $item) {
            $before_do = Str::camel("before_{$transition}");
            $after_do = Str::camel("after_{$transition}");

            if (method_exists($model, $before_do)) {
                $before_do = [$model, $before_do];
            } else {
                $before_do = function ($m, $e) {
                };
            }

            if (method_exists($model, $after_do)) {
                $after_do = [$model, $after_do];
            } else {
                $after_do = function ($m, $e) {
                };
            }

            $configs['callbacks']['before'][] = ['on' => $transition, 'do' => $before_do];
            $configs['callbacks']['after'][] = ['on' => $transition, 'do' => $after_do];
        }
        return $configs;
    }

    /**
     * 初始化状态机过程
     */
    private function initStateMachine($model)
    {

        $model->fsm = new StateMachine($model);
        $configs = [
            "graph" => class_basename($model),
            "class" => get_class($model),
            "states" => $model->states,
            "transitions" => $model->transitions,
            "callbacks" => ['before' => [], 'after' => []]
        ];
        $loader = new ArrayLoader($this->initFsmCallbacks($model, $configs));
        $loader->load($model->fsm);
        $model->fsm->initialize();
        $this->initFsmObserverEvent($model);

    }

    /**
     * 获取模型成员方法
     * @param $method
     * @return \Closure
     */
    public function getMethod($method)
    {
        $object = $this;
        return function () use ($object, $method) {
            $args = func_get_args();
            return call_user_func_array(array($object, $method), $args);
        };
    }

    public function getStates()
    {
        $this->states['INIT'] = [
            'type' => '',
            'name' => '初始化',
            'color' => '',
            'properties' => []
        ];
        return $this->states;
    }

    public function getTransitionsByState($state)
    {
        $operations = [];

        foreach ($this->fsm->getTransitions() as $transition_name) {
            $transition = $this->transitions[$transition_name];
            $can = $transition['from'] == $state || (is_array($transition['from']) && in_array($state, $transition['from'])) || $transition['from'] == '*' ? true : false;
            $hide = (!array_key_exists('hide', $transition)) || ($transition['hide'] != true) ? false : true;

            if (!$hide) {
                $operations[] = [
                    'name' => $transition['name'],
                    'hide' => $hide,
                    'transition' => $transition_name,
                    'can' => $can,
                ];
            }
        }
        return $operations;
    }

    /**
     * @return array
     */
    public function getTransitions()
    {

        $operations = [];

        foreach ($this->fsm->getTransitions() as $transition_name) {
            $can = $this->fsm->can($transition_name);
            $transition = $this->transitions[$transition_name];
            $hide = (!array_key_exists('hide', $transition)) || ($transition['hide'] != true) ? false : true;

            if (!$hide) {
                $operations[] = [
                    'name' => $transition['name'],
                    'hide' => $hide,
                    'transition' => $transition_name,
                    'can' => $can,
                ];
            }
        }
        return $operations;
    }

    public function getTransitionLabel($transition_name)
    {
        if ($transition_name == 'init') {
            return '创建';
        } else {
            return Arr::get(Arr::get($this->transitions, $transition_name, []), 'name');
        }
    }

    public function getStateName($state)
    {
        return Arr::get(Arr::get($this->getStates(), $state, []), 'name');
    }

    public function getCurrentStateName()
    {
        return $this->getStateName($this->getFiniteState());
    }

    public function getOperator()
    {
        if (is_null($this->operator)) {
            $this->setOperator(Auth::user());
            return $this->operator;
        } else {
            return $this->operator;
        }
    }

    public function setOperator($user)
    {
        $this->operator = $user;
    }

    /**
     * Markdown FlowChart
     */
    public function showFlowChart()
    {
        $markdown = "```\ngraph LR\n";
        foreach ($this->states as $key => $item) {
            $markdown .= "{$key}[{$item['name']}]\n";
        }
        foreach ($this->transitions as $key => $transition) {
            if ($transition['from'] != '*' && $transition['to'] != '=') {
                $action = $transition['name'];
                if (is_array($transition['from'])) {
                    foreach ($transition['from'] as $from) {
                        $markdown .= "{$from}--{$action}-->{$transition['to']}\n";
                    }
                } else {
                    $markdown .= "{$transition['from']}--{$action}-->{$transition['to']}\n";
                }
            }
        }
        $markdown .= "```";
        echo $markdown;
    }

    public function showFlowChartSimple()
    {
        $markdown = "```\ngraph LR\n";
        foreach ($this->states as $key => $item) {
            $markdown .= "{$key}[{$item['name']}]\n";
        }
        foreach ($this->transitions as $key => $transition) {
            if ($transition['from'] != '*' && $transition['to'] != '=') {
                if (is_array($transition['from'])) {
                    foreach ($transition['from'] as $from) {
                        $markdown .= "{$from}-->{$transition['to']}\n";
                    }
                } else {
                    $markdown .= "{$transition['from']}-->{$transition['to']}\n";
                }
            }
        }

        $markdown .= "classDef nowState stroke:#42b983,stroke-width:4px,stroke-dasharray: 10;\n";
        $markdown .= "class {$this->state} nowState;\n";

        $markdown .= "```";
        echo $markdown;
    }

    public function showStateChart()
    {
        $markdown = "```\nstateDiagram\n";
        $markdown .= "[*]-->" . $this->getStateName($this->getInitState()) . "\n";

        foreach ($this->transitions as $key => $transition) {
            if ($transition['from'] != '*' && $transition['to'] != '=') {
                if (is_array($transition['from'])) {
                    foreach ($transition['from'] as $from) {
                        $markdown .= "{$this->getStateName($from)}-->{$this->getStateName($transition['to'])}\n";
                    }
                } else {
                    $markdown .= "{$this->getStateName($transition['from'])}-->{$this->getStateName($transition['to'])}\n";
                }
            }
        }
        $markdown .= $this->getStateName($this->getFinalState()) . "-->[*]\n";

        $markdown .= "```";
        echo $markdown;
    }

    /**
     * Gets the object state.
     *
     * @return string
     */
    public function getFiniteState()
    {
        return $this->state;
    }


    /**
     * Sets the object state.
     *
     * @param string $state
     */
    public function setFiniteState($state)
    {
        $this->state = $state;

    }

    public function getInitState()
    {
        foreach ((new static)->states as $state => $state_obj) {
            if ($state_obj['type'] == 'initial') {
                return $state;
            }
        }
    }

    public function getFinalState()
    {
        foreach ((new static)->states as $state => $state_obj) {
            if ($state_obj['type'] == 'final') {
                return $state;
            }
        }
    }
}

