<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAdminTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 190)->nullable();
            $table->string('username', 190)->unique()->nullable();
            $table->string('password', 60)->nullable();
            $table->string('email', 190)->unique()->nullable();
            $table->text('avatar')->nullable();
            $table->string('api_token', 80)->nullable();
            $table->smallInteger('user_type')->default(1);
            $table->string('remember_token', 100)->nullable();
            $table->timestamps();
        });

        Schema::create(config('api.database.roles_table'), function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 50)->unique();
            $table->string('slug', 50)->unique();
            $table->timestamps();
        });

        Schema::create(config('api.database.permissions_table'), function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 50)->unique();
            $table->string('slug', 50)->unique();
            $table->string('http_method')->nullable();
            $table->text('http_path')->nullable();
            $table->timestamps();
        });

        Schema::create(config('api.database.role_users_table'), function (Blueprint $table) {
            $table->integer('role_id');
            $table->integer('user_id');
            $table->index(['role_id', 'user_id']);
            $table->timestamps();
        });

        Schema::create(config('api.database.role_permissions_table'), function (Blueprint $table) {
            $table->integer('role_id');
            $table->integer('permission_id');
            $table->index(['role_id', 'permission_id']);
            $table->timestamps();
        });

        Schema::create(config('api.database.user_permissions_table'), function (Blueprint $table) {
            $table->integer('user_id');
            $table->integer('permission_id');
            $table->index(['user_id', 'permission_id']);
            $table->timestamps();
        });


        Schema::create(config('api.database.operation_log_table'), function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->string('path');
            $table->string('method', 10);
            $table->string('ip');
            $table->text('input');
            $table->index('user_id');
            $table->timestamps();
        });

        Schema::create(config('api.database.finite_state_machine_log_table'), function (Blueprint $table) {
            $table->unsignedBigInteger('id', true);
            $table->string('fsm_logs_able_type', 200);
            $table->unsignedBigInteger('fsm_logs_able_id');
            $table->string('from', 50);
            $table->string('to', 50);
            $table->string('transition', 50);
            $table->string('remark', 3000)->nullable();
            $table->unsignedBigInteger('creator_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(config('api.database.finite_state_machine_log_table'));
        Schema::dropIfExists(config('api.database.users_table'));
        Schema::dropIfExists(config('api.database.roles_table'));
        Schema::dropIfExists(config('api.database.permissions_table'));
        Schema::dropIfExists(config('api.database.user_permissions_table'));
        Schema::dropIfExists(config('api.database.role_users_table'));
        Schema::dropIfExists(config('api.database.role_permissions_table'));
        Schema::dropIfExists(config('api.database.operation_log_table'));
    }
}
