<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AdminController;
use App\Http\Controllers\Admin\SocialLinksController;
use App\Http\Controllers\Admin\ShellController;
use App\Http\Controllers\Studio\SocialsController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Auth\SocialLoginController;
use App\Http\Controllers\Auth\LatchIdSessionController;
use App\Http\Controllers\LinkTypeViewController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\PagesController;
use App\Http\Controllers\InstallerController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\StripeWebhookController;
use App\Http\Controllers\DocumentationController;
use App\Http\Controllers\Studio\LatchDeckController;
use App\Http\Controllers\Studio\LatchIdController;
use App\Http\Controllers\Studio\NotificationController;
use App\Http\Controllers\Studio\ThemeController;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Prevents section below from being run by 'composer update'
if (file_exists(base_path('storage/app/ISINSTALLED'))) {
    if (EnvEditor::getKey('APP_KEY') == '') {
        try {
            Artisan::call('key:generate');
        } catch (exception $e) {
        }
    }

    if (!file_exists(base_path("config/advanced-config.php"))) {
        copy(base_path('storage/templates/advanced-config.php'), base_path('config/advanced-config.php'));
    }
}

// Installer
if (file_exists(base_path('INSTALLING')) or file_exists(base_path('INSTALLERLOCK'))) {

    Route::get('/', [InstallerController::class, 'showInstaller'])->name('showInstaller');
    Route::post('/create-admin', [InstallerController::class, 'createAdmin'])->name('createAdmin');
    Route::post('/db', [InstallerController::class, 'db'])->name('db');
    Route::post('/mysql', [InstallerController::class, 'mysql'])->name('mysql');
    Route::post('/options', [InstallerController::class, 'options'])->name('options');
    Route::get('/mysql-test', [InstallerController::class, 'mysqlTest'])->name('mysqlTest');
    Route::get('/skip', function () {
        Artisan::call('db:seed', ['--class' => 'AdminSeeder']);
        Auth::login(User::where('name', 'admin')->first());
        return redirect(url('dashboard'));
    });
    Route::post('/editConfigInstaller', [InstallerController::class, 'editConfigInstaller'])->name('editConfigInstaller');

    Route::get('{any}', function () {
        if (!DB::table('users')->get()->isEmpty()) {
            if (file_exists(base_path("INSTALLING")) and !file_exists(base_path('INSTALLERLOCK'))) {
                unlink(base_path("INSTALLING"));
                header("Refresh:0");
            }
        } else {
            return redirect(url(''));
        }
    })->where('any', '.*');

} else {

    if (env('MAINTENANCE_MODE') != 'true') {

        require __DIR__ . '/home.php';

        Route::get('/callback/{provider}', function (string $provider) {
            abort_unless(in_array($provider, ['google', 'discord', 'youtube'], true), 404);

            return view('auth.latchid-oauth-callback', [
                'provider' => $provider,
                'supabaseUrl' => config('services.supabase.url'),
                'supabaseAnonKey' => config('services.supabase.anon_key'),
            ]);
        })->name('latchid.oauth.callback');

        Route::post('/api/latchid/session', [LatchIdSessionController::class, 'store'])
            ->name('latchid.session');

        Route::get('/@', function () {
            return redirect('/studio/no_page_name');
        });

        Route::get('/panel/diagnose', function () {
            return view('panel/diagnose', []);
        });

        // Stripe webhook — unauthenticated + CSRF-exempt (see VerifyCsrfToken).
        // Authenticity is verified via the Stripe signature in the controller.
        // Keeps user_billing.plan_key (MySQL) and profiles.plan_key (Supabase)
        // in sync with subscription changes.
        Route::post('/stripe/webhook', [StripeWebhookController::class, 'handle'])
            ->name('stripe.webhook');

        $custom_prefix = config('advanced-config.custom_url_prefix');

        Route::get('/going/{id?}', [UserController::class, 'clickNumber'])->where('link', '.*')->name('clickNumber')->middleware('disableCookies');
        Route::get('/info/{id?}', [AdminController::class, 'redirectInfo'])->name('redirectInfo');

        if ($custom_prefix != "") {
            Route::get('/' . $custom_prefix . '{littlelink}', [UserController::class, 'littlelink']);
        }

        Route::get('/@{littlelink}', [UserController::class, 'littlelink'])->middleware('disableCookies');
        Route::get('/pages/' . strtolower(footer('Terms')), [AdminController::class, 'pagesTerms'])->name('pagesTerms')->middleware('disableCookies');
        Route::get('/pages/' . strtolower(footer('Privacy')), [AdminController::class, 'pagesPrivacy'])->name('pagesPrivacy')->middleware('disableCookies');
        Route::get('/pages/' . strtolower(footer('Contact')), [AdminController::class, 'pagesContact'])->name('pagesContact')->middleware('disableCookies');
        Route::get('/theme/@{littlelink}', [UserController::class, 'theme'])->name('theme');
        Route::get('/vcard/{id?}', [UserController::class, 'vcard'])->name('vcard');
        Route::get('/u/{id?}', [UserController::class, 'userRedirect'])->name('userRedirect');

        Route::get('/report', function () {
            return view('report');
        });
        Route::post('/report', [UserController::class, 'report'])->name('report');

        Route::get('/demo-page', [App\Http\Controllers\HomeController::class, 'demo'])->name('demo')->middleware('disableCookies');

        Route::get('/legal/privacy', function (\Illuminate\Http\Request $request) {
            $view = $request->header('HX-Request') ? 'public.legal-partial' : 'public.legal-page';

            return view($view, ['type' => 'privacy']);
        })->name('legal.privacy')->middleware('disableCookies');

        Route::get('/legal/terms', function (\Illuminate\Http\Request $request) {
            $view = $request->header('HX-Request') ? 'public.legal-partial' : 'public.legal-page';

            return view($view, ['type' => 'terms']);
        })->name('legal.terms')->middleware('disableCookies');

        Route::get('/block-asset/{type}', [LinkTypeViewController::class, 'blockAsset'])
            ->name('block.asset')->where(['type' => '[a-zA-Z0-9_-]+']);

        Route::get('/media/profile/{userId}', [MediaController::class, 'profile'])
            ->name('media.profile')
            ->where(['userId' => '[0-9]+']);

        Route::get('/media/opengraph/{userId}', [MediaController::class, 'openGraphProfile'])
            ->name('media.opengraph.profile')
            ->where(['userId' => '[0-9]+']);

        Route::get('/media/opengraph/{userId}.png', [MediaController::class, 'openGraphProfile'])
            ->name('media.opengraph.profile.png')
            ->where(['userId' => '[0-9]+']);
    }

    Route::middleware(['auth', 'blocked', 'impersonate'])->group(function () {

        Route::group([
            'middleware' => env('REGISTER_AUTH'),
        ], function () {

            if (env('FORCE_ROUTE_HTTPS') == 'true') {
                URL::forceScheme('https');
            }

            if (isset($_COOKIE['LinkCount'])) {
                if ($_COOKIE['LinkCount'] == '20') {
                    $LinkPage = 'showLinks20';
                } elseif ($_COOKIE['LinkCount'] == '30') {
                    $LinkPage = 'showLinks30';
                } elseif ($_COOKIE['LinkCount'] == 'all') {
                    $LinkPage = 'showLinksAll';
                } else {
                    $LinkPage = 'showLinks';
                }
            } else {
                $LinkPage = 'showLinks';
            }

            Route::get('/dashboard', [AdminController::class, 'index'])->name('panelIndex');
            Route::get('/studio/index', function () {
                return redirect(url('dashboard'));
            });

            Route::get('/studio/add-link', [UserController::class, 'AddUpdateLink'])->name('showButtons');
            Route::post('/studio/edit-link', [UserController::class, 'saveLink'])->name('addLink');
            Route::get('/studio/edit-link/{id}', [UserController::class, 'AddUpdateLink'])->name('showLink')->middleware('link-id');
            Route::post('/studio/sort-link', [UserController::class, 'sortLinks'])->name('sortLinks');
            Route::get('/studio/links', [UserController::class, $LinkPage])->name($LinkPage);
            Route::get('/studio/theme', [ThemeController::class, 'edit'])->name('showTheme');
            Route::post('/studio/theme', [ThemeController::class, 'update'])->name('editTheme');
            Route::get('/deleteLink/{id}', [UserController::class, 'deleteLink'])->name('deleteLink')->middleware('link-id');
            Route::get('/upLink/{up}/{id}', [UserController::class, 'upLink'])->name('upLink')->middleware('link-id');
            Route::post('/studio/edit-link/{id}', [UserController::class, 'editLink'])->name('editLink')->middleware('link-id');
            Route::get('/studio/button-editor/{id}', [UserController::class, 'showCSS'])->name('showCSS')->middleware('link-id');
            Route::post('/studio/button-editor/{id}', [UserController::class, 'editCSS'])->name('editCSS')->middleware('link-id');
            Route::get('/studio/page', [UserController::class, 'showPage'])->name('showPage');
            Route::get('/studio/no_page_name', [UserController::class, 'showPage'])->name('showPageNoPageName');
            Route::post('/studio/page', [UserController::class, 'editPage'])->name('editPage');
            Route::post('/studio/background', [UserController::class, 'themeBackground'])->name('themeBackground');
            Route::get('/studio/rem-background', [UserController::class, 'removeBackground'])->name('removeBackground');
            Route::get('/studio/profile', [UserController::class, 'showProfile'])->name('showProfile');
            Route::post('/studio/profile', [UserController::class, 'editProfile'])->name('editProfile');
            Route::post('/edit-icons', [UserController::class, 'editIcons'])->name('editIcons');
            Route::get('/clearIcon/{id}', [UserController::class, 'clearIcon'])->name('clearIcon');
            Route::get('/studio/page/delprofilepicture', [UserController::class, 'delProfilePicture'])->name('delProfilePicture');
            Route::get('/studio/delete-user/{id}', [UserController::class, 'deleteUser'])->name('deleteUser')->middleware('verified');
            Route::post('/auth-as', [AdminController::class, 'authAs'])->name('authAs');

            // LatchDeck (Encore-backed). The hub page is state-driven by the
            // creator's access status; all data lives behind the LatchDeck API.
            Route::view('/studio/latchapps', 'studio.latchapps')->name('studio.latchapps');
            Route::view('/studio/fax', 'studio.fax')->name('studio.fax');
            Route::get('/studio/latchdeck', [LatchDeckController::class, 'index'])->name('studio.latchdeck');
            Route::post('/studio/latchdeck/request-access', [LatchDeckController::class, 'requestAccess'])->name('studio.latchdeck.requestAccess');
            Route::post('/studio/latchdeck/cards', [LatchDeckController::class, 'storeCard'])->name('studio.latchdeck.cards.store');
            Route::post('/studio/latchdeck/cards/{id}', [LatchDeckController::class, 'updateCard'])->name('studio.latchdeck.cards.update');
            Route::post('/studio/latchdeck/cards/{id}/publish', [LatchDeckController::class, 'publishCard'])->name('studio.latchdeck.cards.publish');
            Route::post('/studio/latchdeck/cards/{id}/unpublish', [LatchDeckController::class, 'unpublishCard'])->name('studio.latchdeck.cards.unpublish');

            Route::get('/studio/subscription', [BillingController::class, 'index'])
                ->name('billing.index');

            Route::get('/billing/checkout/pro', [BillingController::class, 'checkoutPro'])
                ->name('billing.checkout.pro');

            Route::get('/billing/portal', [BillingController::class, 'portal'])
                ->name('billing.portal');

            Route::get('/studio/my-data', function () {
                $documents = collect([
                    'privacy' => base_path('docs/compliance/privacy.md'),
                    'tos' => base_path('docs/compliance/tos.md'),
                ])->mapWithKeys(function (string $path, string $key) {
                    $markdown = \Illuminate\Support\Facades\File::exists($path)
                        ? \Illuminate\Support\Facades\File::get($path)
                        : '# Missing document';

                    return [$key => [
                        'source_path' => $path,
                        'updated_at' => \Illuminate\Support\Facades\File::exists($path)
                            ? \Carbon\Carbon::createFromTimestamp(\Illuminate\Support\Facades\File::lastModified($path))
                            : null,
                        'html' => \Illuminate\Support\Str::markdown($markdown, [
                            'html_input' => 'strip',
                            'allow_unsafe_links' => false,
                        ]),
                    ]];
                });

                return view('studio.account.my-data', [
                    'privacyDocument' => $documents['privacy'],
                    'tosDocument' => $documents['tos'],
                ]);
            });
            Route::get('/studio/latchid', [LatchIdController::class, 'edit'])->name('studio.latchid');
            Route::post('/studio/latchid/email-preferences', [\App\Http\Controllers\Studio\EmailPreferenceController::class, 'update'])
                ->name('studio.latchid.email-preferences');

            // Notification center (Supabase-backed). The modal fetches these.
            Route::get('/studio/notifications', [NotificationController::class, 'index'])->name('studio.notifications');
            Route::post('/studio/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('studio.notifications.readAll');
            Route::post('/studio/notifications/{id}/read', [NotificationController::class, 'markRead'])->name('studio.notifications.read');
            Route::post('/studio/notifications/{id}/unread', [NotificationController::class, 'markUnread'])->name('studio.notifications.unread');
            Route::get('/studio/docs', [DocumentationController::class, 'index'])
                ->name('docs.index');
            Route::get('/studio/docs/article/{path}', [DocumentationController::class, 'article'])
                ->where('path', '.*')
                ->name('docs.article');
            Route::get('/studio/docs/{path}', [DocumentationController::class, 'show'])
                ->where('path', '.*')
                ->name('docs.show');
            Route::view('/studio/feedback', 'studio.growth.feedback');
            Route::get('/studio/socials', [SocialsController::class, 'index'])->name('studio.socials');
            Route::view('/studio/affiliate-program', 'studio.growth.affiliate-program');
            Route::view('/studio/creator-program', 'studio.growth.creator-program');

            Route::get('/admin/users/all', fn () => redirect(route('showUsers')));
            Route::get('/studio', fn () => redirect(url('dashboard')));
            Route::get('/studio/edit-link', fn () => redirect(url('dashboard')));

            if (env('ALLOW_USER_EXPORT') != false) {
                Route::get('/export-links', [UserController::class, 'exportLinks'])->name('exportLinks');
                Route::get('/export-all', [UserController::class, 'exportAll'])->name('exportAll');
            }

            if (env('ALLOW_USER_IMPORT') != false) {
                Route::post('/import-data', [UserController::class, 'importData'])->name('importData');
            }

            Route::get('/studio/linkparamform_part/{typeid}/{linkid}', [LinkTypeViewController::class, 'getParamForm'])->name('linkparamform.part');
        });
    });
}

// Social login route
Route::get('/social-auth/{provider}/callback', [SocialLoginController::class, 'providerCallback']);
Route::get('/social-auth/{provider}', [SocialLoginController::class, 'redirectToProvider'])->name('social.redirect');

Route::middleware(['auth', 'blocked', 'impersonate'])->group(function () {

    Route::group([
        'middleware' => 'admin',
    ], function () {

        if (env('FORCE_ROUTE_HTTPS') == 'true') {
            URL::forceScheme('https');
        }

        Route::get('/panel/index', function () {
            return redirect(url('dashboard'));
        });

        Route::get('/admin/users', [AdminController::class, 'users'])->name('showUsers');
        Route::get('/admin/links/{id}', [AdminController::class, 'showLinksUser'])->name('showLinksUser');
        Route::get('/admin/deleteLink/{id}', [AdminController::class, 'deleteLinkUser'])->name('deleteLinkUser');
        Route::get('/admin/users/block/{block}/{id}', [AdminController::class, 'blockUser'])->name('blockUser');
        Route::get('/admin/users/verify/{verify}/{id}', [AdminController::class, 'verifyCheckUser'])->name('verifyCheckUser');
        Route::get('/admin/users/verify-mail/{verify}/{id}', [AdminController::class, 'verifyUser'])->name('verifyUser');
        Route::get('/admin/edit-user/{id}', [AdminController::class, 'showUser'])->name('showUser');
        Route::post('/admin/edit-user/{id}', [AdminController::class, 'editUser'])->name('editUser');
        Route::get('/admin/new-user', [AdminController::class, 'createNewUser'])->name('createNewUser')->middleware('max.users');
        Route::get('/admin/delete-user/{id}', [AdminController::class, 'deleteUser'])->name('deleteUserAdmin');
        Route::post('/admin/delete-table-user/{id}', [AdminController::class, 'deleteTableUser'])->name('deleteTableUser');
        Route::get('/admin/pages', [AdminController::class, 'showSitePage'])->name('showSitePage');
        Route::post('/admin/pages', [AdminController::class, 'editSitePage'])->name('editSitePage');
        Route::get('/admin/advanced-config', [AdminController::class, 'showFileEditor'])->name('showFileEditor');
        Route::post('/admin/advanced-config', [AdminController::class, 'editAC'])->name('editAC');
        Route::get('/admin/env', [AdminController::class, 'showEnvEditor'])->name('showEnvEditor');
        Route::post('/admin/env', [AdminController::class, 'editENV'])->name('editENV');
        Route::get('/admin/site', [AdminController::class, 'showSite'])->name('showSite');
        Route::post('/admin/site', [AdminController::class, 'editSite'])->name('editSite');
        Route::view('/admin/dev-tools', 'studio.admin.dev-tools')->name('admin.dev-tools');
        Route::view('/admin/tour-builder', 'studio.admin.tour-builder')->name('admin.tour-builder');
        Route::get('/admin/socials', [SocialLinksController::class, 'edit'])->name('admin.socials');
        Route::post('/admin/socials', [SocialLinksController::class, 'update'])->name('admin.socials.update');
        Route::view('/admin/development-timeline', 'studio.admin.development-timeline')->name('admin.development-timeline');
        Route::get('/admin/shell', [ShellController::class, 'index'])->name('admin.shell');
        Route::post('/admin/shell/run', [ShellController::class, 'run'])->name('admin.shell.run');
        Route::get('/admin/site/delavatar', [AdminController::class, 'delAvatar'])->name('delAvatar');
        Route::get('/admin/site/delfavicon', [AdminController::class, 'delFavicon'])->name('delFavicon');
        Route::get('/admin/phpinfo', [AdminController::class, 'phpinfo'])->name('phpinfo');
        Route::get('/admin/backups', [AdminController::class, 'showBackups'])->name('showBackups');
        Route::post('/admin/theme', [AdminController::class, 'deleteTheme'])->name('deleteTheme');
        Route::get('/admin/theme', [AdminController::class, 'showThemes'])->name('showThemes');
        Route::get('/update/theme', [AdminController::class, 'updateThemes'])->name('updateThemes');
        Route::get('/admin/config', [AdminController::class, 'showConfig'])->name('showConfig');
        Route::post('/admin/config', [AdminController::class, 'editConfig'])->name('editConfig');
        Route::get('/send-test-email', [AdminController::class, 'SendTestMail'])->name('SendTestMail');
        Route::get('/auth-as/{id}', [AdminController::class, 'authAsID'])->name('authAsID');
        Route::get('/theme-updater', function () {
            return view('studio/theme-updater', []);
        });
        Route::get('/update', function () {
            return view('update', []);
        });
        Route::get('/backup', function () {
            return view('backup', []);
        });

        Route::group(['namespace' => 'App\Http\Controllers\Admin', 'prefix' => 'admin', 'as' => 'admin'], function () {
            Route::resources([
                'linktype' => LinkTypeController::class,
            ]);
        });

    });
});

if (env('MAINTENANCE_MODE') == 'true') {
    Route::get('/{any}', function () {
        return view('maintenance');
    })->where('any', '.*');
}

require __DIR__ . '/auth.php';

if (config('advanced-config.custom_url_prefix') == "") {
    Route::get('/{littlelink}', [UserController::class, 'littlelink']);
}
