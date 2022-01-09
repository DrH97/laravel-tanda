<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTandaRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'tanda_requests',
            function (Blueprint $table) {
                $table->bigIncrements('id');

                $table->string('request_id')->unique();
                $table->string('status');
                $table->string('message');
                $table->string('receipt_number')->nullable();
                $table->string('command_id');
                $table->string('provider');
                $table->string('destination');
                $table->integer('amount');

                $table->json('result')->nullable();

                $table->timestamp('last_modified');

                $table->unsignedBigInteger('relation_id')->nullable();
                $table->index('relation_id');

                $table->timestamps();
            }
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('kyanda_transactions');
    }
}
