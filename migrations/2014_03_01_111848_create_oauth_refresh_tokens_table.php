<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOauthRefreshTokensTable extends Migration
{

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('oauth_refresh_tokens', function (Blueprint $table) {
            $table->string('access_token', 40)->primary();
            $table->string('token', 40)->unique();
            $table->integer('expires');

            $table->timestamps();

            $table->foreign('access_token')
                  ->references('token')->on('oauth_access_tokens')
                  ->onDelete('cascade')
                  ->onUpdate('no action');
        });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('oauth_refresh_tokens', function ($table) {
            $table->dropForeign('oauth_refresh_tokens_access_token_foreign');
        });
        Schema::drop('oauth_refresh_tokens');
	}

}