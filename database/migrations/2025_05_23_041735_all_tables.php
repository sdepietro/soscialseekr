<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
//        /**
//         * Users (usuarios del sistema)
//         */
//        Schema::create('users', function (Blueprint $table) {
//            $table->id();
//            $table->string('name');
//            $table->string('lastname');
//            $table->string('role');
//            $table->string('email')->unique();
//            $table->timestamp('email_verified_at')->nullable();
//            $table->string('password');
//            $table->rememberToken();
//            $table->timestamps();
//            $table->softDeletes();
//        });



        /**
         * Permisos (estructura existente)
         */
        Schema::create('permissions_father', function (Blueprint $table) {
            $table->integer('id', true);
            $table->text('name');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('permissions', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('permission_father_id');
            $table->text('tag');
            $table->text('name');
            $table->text('description');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('permissions_users', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('user_id');
            $table->integer('permission_id');
            $table->timestamps();
            $table->softDeletes();
        });


        /**
         * Seeds mínimos
         */
        DB::table('permissions_father')->insert([
            ['id' => 1, 'name' => "Dashboard", 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'name' => "Usuarios",  'created_at' => now(), 'updated_at' => now()],
        ]);

        // Usuario admin
        $admin_mail = "admin@xfinder.com";
        DB::table('users')->insert([
            'name' => 'Admin',
            'lastname' => 'Principal',
            'role' => 'admin',
            'email' => $admin_mail,
            'password' => bcrypt('123456'),
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        /**
         * companies = empresas/organizaciones (para modelo SAAS)
         */
        Schema::create('companies', function (Blueprint $table) {
            $table->bigIncrements('id');

            // Información básica
            $table->string('name', 191);
            $table->string('legal_name', 191)->nullable();
            $table->string('tax_id', 50)->nullable()->comment('CUIT/NIT/RFC/etc');

            // Tipo y tamaño
            $table->string('industry', 100)->nullable()->comment('Sector: salud, fintech, retail, etc');
            $table->enum('company_size', ['1-10', '11-50', '51-200', '201-500', '500+'])->nullable();

            // Contacto
            $table->string('phone', 50)->nullable();
            $table->string('website', 255)->nullable();

            // Dirección
            $table->string('country', 5)->default('AR');
            $table->string('city', 100)->nullable();
            $table->text('address')->nullable();

            // Plan y límites
            $table->enum('plan', ['free', 'starter', 'professional', 'enterprise'])->default('free');
            $table->integer('max_searches')->default(3)->comment('Búsquedas simultáneas permitidas');
            $table->integer('max_frequency_minutes')->default(60)->comment('Frecuencia mínima en minutos');
            $table->boolean('can_use_ai')->default(false);

            // Suscripción
            $table->enum('subscription_status', ['trial', 'active', 'suspended', 'cancelled'])->default('trial');
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('subscription_started_at')->nullable();

            // Facturación
            $table->string('billing_email', 191)->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['plan', 'subscription_status']);
        });

        /**
         * user_onboarding = progreso del proceso de registro multi-paso
         */
        Schema::create('user_onboarding', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->unique();

            // Estado del proceso
            $table->integer('current_step')->default(1);
            $table->timestamp('completed_at')->nullable();

            // Pasos completados
            $table->boolean('step1_account')->default(false);
            $table->boolean('step2_company')->default(false);
            $table->boolean('step3_search')->default(false);

            // Metadata
            $table->json('skipped_steps')->nullable();
            $table->string('source', 50)->nullable()->comment('Origen: web, api, referral');

            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        /**
         * accounts = autores/perfiles de Twitter
         */
        Schema::create('accounts', function (Blueprint $table) {
            $table->bigIncrements('id');

            // Identificación básica
            $table->string('twitter_id', 64)->unique(); // "author.id" (string en API no oficial)
            $table->string('username', 50)->index();    // "author.userName"
            $table->string('name', 100)->nullable();
            $table->string('url', 512)->nullable();

            // Verificación / flags
            $table->boolean('is_blue_verified')->default(false);
            $table->string('verified_type', 50)->nullable();

            // Imágenes de perfil/portada
            $table->string('profile_picture', 512)->nullable();
            $table->string('cover_picture', 512)->nullable();

            // Bio y metadatos
            $table->text('description')->nullable();
            $table->string('location', 191)->nullable();
            $table->unsignedBigInteger('followers')->default(0);
            $table->unsignedBigInteger('following')->default(0);
            $table->boolean('can_dm')->default(false);
            $table->dateTime('created_at_twitter')->nullable();
            $table->unsignedBigInteger('favourites_count')->default(0);
            $table->boolean('has_custom_timelines')->default(false);
            $table->boolean('is_translator')->default(false);
            $table->unsignedBigInteger('media_count')->default(0);
            $table->unsignedBigInteger('statuses_count')->default(0);

            // Restricciones/regiones
            $table->json('withheld_in_countries')->nullable();
            $table->json('affiliates_highlighted_label')->nullable();
            $table->boolean('possibly_sensitive')->default(false);

            // Pinned / automatización
            $table->json('pinned_tweet_ids')->nullable();
            $table->boolean('is_automated')->default(false);
            $table->string('automated_by', 191)->nullable();

            // Estados de disponibilidad
            $table->boolean('unavailable')->default(false);
            $table->string('message', 512)->nullable();
            $table->string('unavailable_reason', 512)->nullable();

            // Bio extendida con entities (URLs dentro de la bio)
            $table->text('profile_bio_description')->nullable();
            $table->json('profile_bio_entities')->nullable();

            // Snapshot crudo por si hiciera falta (auditoría)
            $table->json('raw_payload')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['username', 'is_blue_verified']);
        });

        /**
         * searchs = consultas que disparamos cada X minutos
         * (se usa "searchs" tal cual lo pediste)
         */
        Schema::create('searchs', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->integer('user_id');          // Id del usuario que creó la búsqueda
            $table->string('name', 191)->nullable();          // etiqueta interna
            $table->text('query');                            // la query a enviar a la API
            $table->string('lang', 10)->nullable();           // ej: "es"
            $table->string('country', 5)->default('AR');      // acotamos por Argentina si aplica
            $table->boolean('active')->default(true);

            // Parámetros de control
            $table->unsignedInteger('run_every_minutes')->default(15);
            $table->unsignedInteger('min_like_count')->default(0)->comment('Si querés filtrar mínimo de likes');
            $table->unsignedInteger('min_retweet_count')->default(0)->comment('Si querés filtrar mínimo de retweets');
            $table->json('only_from_accounts')->nullable();   // lista de usernames/ids a incluir

            // Parámetros del endpoint avanzado
            $table->enum('query_type', ['Latest', 'Top'])->default('Latest'); // mapea al param queryType
            $table->string('timezone', 40)->nullable(); // opcional: si querés manejar fechas en zona específica

            // Paginación / estado
            $table->dateTime('last_run_at')->nullable();
            $table->string('next_cursor', 191)->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['active', 'country']);
        });

        /**
         * tweets = tweets encontrados
         */
        Schema::create('tweets', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('search_id'); // Id de la búsqueda que lo encontró

            // Relación con la cuenta autora
            $table->unsignedBigInteger('account_id')->nullable();
            $table->foreign('account_id')->references('id')->on('accounts')->onDelete('set null');

            // Identificación tweet
            $table->string('twitter_id', 64)->unique(); // "id" del tweet
            $table->string('url', 512)->nullable();

            // Contenido
            $table->longText('text')->nullable();
            $table->string('source', 191)->nullable();
            $table->string('lang', 10)->nullable();

            // Métricas
            $table->unsignedBigInteger('retweet_count')->default(0);
            $table->unsignedBigInteger('reply_count')->default(0);
            $table->unsignedBigInteger('like_count')->default(0);
            $table->unsignedBigInteger('quote_count')->default(0);
            $table->unsignedBigInteger('view_count')->default(0);
            $table->unsignedBigInteger('bookmark_count')->default(0);

            // Flags/relaciones dentro de Twitter
            $table->boolean('is_reply')->default(false);
            $table->string('in_reply_to_id', 64)->nullable();
            $table->string('conversation_id', 64)->nullable();
            $table->json('display_text_range')->nullable();
            $table->string('in_reply_to_user_id', 64)->nullable();
            $table->string('in_reply_to_username', 50)->nullable();

            // Entidades y embebidos
            $table->json('entities')->nullable();        // hashtags, urls, user_mentions
            $table->json('quoted_tweet')->nullable();    // snapshot si viene incluido
            $table->json('retweeted_tweet')->nullable(); // snapshot si viene incluido
            $table->boolean('is_limited_reply')->default(false);

            // Tiempos
            $table->dateTime('created_at_twitter')->nullable(); // "createdAt" del tweet

            //Analisis Chatgpt
            $table->boolean('ia_analyzed')->default(0)->comment('Si el tweet fue analizado por ChatGPT');
            $table->integer('ia_score')->nullable()->comment('Puntaje de 0 a 100 asignado por ChatGPT');
            $table->string('ia_reason')->nullable()->comment('Razón del puntaje asignado por ChatGPT');

            // Auditoría y trazabilidad de búsqueda
            $table->json('matched_search_ids')->nullable(); // lista de searchs (IDs) que lo "engancharon"
            $table->json('raw_payload')->nullable();        // snapshot crudo del tweet



            $table->timestamps();
            $table->softDeletes();

            $table->index(['account_id', 'created_at_twitter']);
            $table->index(['lang', 'is_reply']);
            $table->index(['retweet_count', 'like_count']);
        });

        /**
         * tweets_history = historial de cambios del tweet (métricas/snapshots)
         */
        Schema::create('tweets_history', function (Blueprint $table) {
            $table->bigIncrements('id');

            // Relación al tweet
            $table->unsignedBigInteger('tweet_id');
            $table->foreign('tweet_id')->references('id')->on('tweets')->onDelete('cascade');

            // Motivo del snapshot
            $table->enum('reason', ['metrics_update', 'content_update', 'deletion', 'other'])->default('metrics_update');

            // Copia de métricas (para consultas rápidas)
            $table->unsignedBigInteger('retweet_count')->default(0);
            $table->unsignedBigInteger('reply_count')->default(0);
            $table->unsignedBigInteger('like_count')->default(0);
            $table->unsignedBigInteger('quote_count')->default(0);
            $table->unsignedBigInteger('view_count')->default(0);
            $table->unsignedBigInteger('bookmark_count')->default(0);

            // Snapshots JSON para auditoría/diff
            $table->json('previous_snapshot')->nullable();
            $table->json('new_snapshot')->nullable();
            $table->json('diff')->nullable();

            // Cuándo se tomó este snapshot (además de timestamps)
            $table->dateTime('changed_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['tweet_id', 'reason']);
            $table->index(['changed_at']);
        });
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('tweets_history');
        Schema::dropIfExists('tweets');
        Schema::dropIfExists('searchs');
        Schema::dropIfExists('accounts');
        Schema::dropIfExists('user_onboarding');
        Schema::dropIfExists('companies');
        Schema::dropIfExists('permissions_users');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('permissions_father');
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
        Schema::enableForeignKeyConstraints();
    }
};
