<?php

namespace SwiftApi\Controllers;

use App\Http\Controllers\Controller;
use FilterEloquent\Filter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\morphToMany;
use Illuminate\Database\Eloquent\Relations\hasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Validator;

class ResourceController extends Controller
{
    /**
     * Resource model
     * @var
     */
    public $model;

    /**
     * Http method guarded
     * @var array store|update|destroy|index|show
     * @default store|update|destroy|
     */
    public $method_guarded = ['store', 'update', 'destroy'];

    /**
     * Http request validation rule mapping
     * @var array
     */
    public $rules = [
        'store' => null,
        'update' => null,
    ];
    /**
     * with relationship
     * @var array
     */
    public $with = [];
    public $show_with = [];

    public $paginator_enable = true;
    public $collator_enable = true;
    public $ordering = '-';

    /**
     * Custom resource routing
     * @param Router $router
     */
    public function registerRoute(Router $router)
    {

    }

    /**
     * Get current resource model instance
     * @return mixed
     */
    protected function getModelInstance()
    {
        return new $this->model();
    }

    /**
     * Get resource object
     * @param null $id
     * @return mixed
     */
    protected function getObject($id)
    {
        return $this->getModelInstance()->findOrFail($id);
    }

    /**
     * Instantiate a new resource model object
     * @param $data
     * @return mixed
     */
    protected function newObject($data = [])
    {
        return new $this->model($data);
    }


    /**
     * Get the current query
     * @return mixed
     */
    protected function getQuery($is_filter = true)
    {
        $query = $this->getModelInstance();
        if ($is_filter) {
            $query = $this->filter($query);
        }
        return $query->with($this->with);
    }

    /**
     * Universal filter
     * @return mixed
     */
    protected function filter($query)
    {

        $q = request()->get('q', null);
        if (!is_null($q)) {
            return (new Filter($query, $q))->filteredQuery();
        }
        return $query;

    }

    /**
     * Universal paginator
     * @return mixed
     */
    protected function paginator($query = null)
    {

        if (is_null($query)) {
            $query = $this->getQuery();
        }

        if ($this->paginator_enable) {
            return $query->paginate(request()->get('page_size', 10));
        } else {
            return $query;
        }

    }

    /**
     * Universal collator
     * @param null $query
     * @return mixed|null
     */
    protected function collator($query = null)
    {
        if (is_null($query)) {
            $query = $this->getQuery();
        }
        if ($this->collator_enable) {
            $sort_fields = request()->get('order_by', null);
            if (is_null($sort_fields)) {
                if ($query instanceof Builder ||
                    $query instanceof BelongsToMany ||
                    $query instanceof HasMany ||
                    $query instanceof morphToMany ||
                    $query instanceof hasManyThrough ||
                    $query instanceof MorphMany
                ) {
                    $key = $query->getModel()->getKeyName();
                } else {
                    $key = $query->getKeyName();
                }
                $sort_fields = [$this->ordering . $key];

            } else {
                $sort_fields = explode(',', $sort_fields);
            }
            foreach ($sort_fields as $key) {
                $order = 'asc';
                if ($key[0] == '-') {
                    $order = 'desc';
                }
                $field = str_replace('-', '', $key);
                $field = str_replace('+', '', $field);
                $query = $query->orderBy($field, $order);
            }
            return $query;
        } else {
            return $query;
        }
    }

    /**
     * List the specified resource
     * @return mixed
     */
    public function index()
    {
        return $this->paginator($this->collator());
    }


    /**
     * Store the specified resource
     * @param Request $request
     * @return mixed
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     * @throws \Throwable
     */
    public function store(Request $request)
    {
        return DB::transaction(function () use ($request) {

            if (!is_null($this->rules['store'])) {
                $request = app()->make($this->rules['store']);
                $data = $request->validated();
            }

            $new_object = $this->newObject();

            # Ignore array fields
            foreach ($data as $key => $value) {
                if (!is_array($value)) {
                    $new_object->$key = $value;
                }
            }

            $new_object = $this->beforeStore($new_object);

            if ($new_object->save()) {
                return $this->afterStore($new_object);
            } else {
                abort(400);
            }
        }, 5);
    }

    /**
     * @param Model $object
     * @param FormRequest $request
     * @return Model
     */
    public function beforeStore($object)
    {
        return $object;
    }

    /**
     * @param Model $object
     * @param FormRequest $request
     * @return Model
     */
    public function afterStore($object)
    {
        return $object;
    }

    /**
     * Display the specified resource.
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($pk)
    {
        return $this->getModelInstance()->with($this->show_with)->findOrFail($pk);
    }

    /**
     * Update the specified resource
     * @param Request $request
     * @param $id
     * @return mixed
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     * @throws \Throwable
     */
    public function update(Request $request, $id)
    {
        if (!is_null($this->rules['update'])) {
            $data = app()->make($this->rules['update'])->validated();
        }

        $object = $this->getObject($id);
        return DB::transaction(function () use ($object, $data) {
            if ($object->update($data)) {
                return $object;
            } else {
                abort(400);
            }
        }, 5);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if ($is_delete = $this->getObject($id)->delete()) {
            return abort(200, $id);
        }
    }

}
