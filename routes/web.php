<?php


use App\Http\Controllers\Admin\SearchesController;
use App\Http\Controllers\Test\TwitterController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

Route::get('/', function () {
    return redirect()->route('admin::home');
});

Route::get('admin', [App\Http\Controllers\Auth\LoginController::class, 'CheckLogged']);
Route::get('admin/login', [App\Http\Controllers\Auth\LoginController::class, 'showLoginForm'])->name('login');
Route::post('admin/login', [App\Http\Controllers\Auth\LoginController::class, 'login']);
Route::get('admin/register', [App\Http\Controllers\Auth\RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('admin/register', [App\Http\Controllers\Auth\RegisterController::class, 'register']);
Route::post('admin/logout', [App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('logout');

Route::post('any_dummy', [App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('any');


// Rutas de Onboarding (autenticadas pero sin middleware de onboarding completo)
Route::middleware(['auth'])->prefix('admin/onboarding')->name('onboarding.')->group(function () {
    Route::get('step-2', [App\Http\Controllers\OnboardingController::class, 'step2'])->name('step2');
    Route::post('step-2', [App\Http\Controllers\OnboardingController::class, 'storeStep2']);
    Route::get('step-3', [App\Http\Controllers\OnboardingController::class, 'step3'])->name('step3');
    Route::post('step-3', [App\Http\Controllers\OnboardingController::class, 'storeStep3']);
    Route::post('skip/{step}', [App\Http\Controllers\OnboardingController::class, 'skip'])->name('skip');
});

Route::group(['as' => 'admin::', 'prefix' => 'admin', 'middleware' => ['auth', 'onboarding.check']], function () {

    Route::get('/home', [App\Http\Controllers\Admin\HomeController::class, 'index'])->name('home')->middleware('can:dashboard-admin');

    Route::get('/logout', [App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('logout');


    // Rutas para gestión de búsquedas de Twitter
    Route::get('/searches', [SearchesController::class, 'index'])->name('searches.index')->middleware('can:dashboard-admin');
    Route::get('/searches/create', [SearchesController::class, 'create'])->name('searches.create')->middleware('can:dashboard-admin');
    Route::post('/searches', [SearchesController::class, 'store'])->name('searches.store')->middleware('can:dashboard-admin');
    Route::get('/searches/{id}', [SearchesController::class, 'show'])->name('searches.show')->middleware('can:dashboard-admin');
    Route::get('/searches/{id}/edit', [SearchesController::class, 'edit'])->name('searches.edit')->middleware('can:dashboard-admin');
    Route::put('/searches/{id}', [SearchesController::class, 'update'])->name('searches.update')->middleware('can:dashboard-admin');
    Route::delete('/searches/{id}', [SearchesController::class, 'destroy'])->name('searches.destroy')->middleware('can:dashboard-admin');
    Route::put('/searches/{id}/toggle', [SearchesController::class, 'toggleActive'])->name('searches.toggle')->middleware('can:dashboard-admin');

});


Route::middleware([])->get('/install', function () {
    try {
        // Verificar la conexión a la base de datos
        DB::connection()->getPdo();
        echo 'Database connection is established.<br>';

        // Ejecutar las migraciones
        echo 'Running migrations...<br>';
        Artisan::call('migrate', ['--force' => true]);

        echo "Installation success!";
    } catch (\Exception $e) {
        // Capturar errores de conexión o de base de datos
        echo "Error: Could not connect to the database. Please check your configuration.<br>";
        echo "Exception message: " . $e->getMessage();
    }
});


Route::get('/forceinstall', function () {

    echo 'Forcing installation...<br>';

    // Ejecutar el comando para vaciar la base de datos y migrar desde cero
    Artisan::call('migrate:fresh', ['--force' => true]);

    echo "Database reset and installation success!";
});

Route::get('/clear-cache', function () {
    Artisan::call('cache:clear');
    //$exitCode = Artisan::call('route:cache');
    Artisan::call('route:clear');
    //dd("--");
    Artisan::call('view:clear');
    Artisan::call('config:cache');
    Artisan::call('vendor:publish --provider "L5Swagger\L5SwaggerServiceProvider"');
    dd("Cache borrada correctamente!!!");
    //$routeCollection = Route::getRoutes();
    //dd($routeCollection);
    // return what you want
});


Route::get('/test/twitter_search', [TwitterController::class, 'index']);
Route::get('/test/evaluate_tweet', [TwitterController::class, 'evaluateTweets']);
Route::get('/test/create_notification_tweet', [TwitterController::class, 'createFakeTweetForNotification']);
Route::get('/test/clean_whatsapp_logs', [TwitterController::class, 'cleanFutureWhatsappLogs']);



