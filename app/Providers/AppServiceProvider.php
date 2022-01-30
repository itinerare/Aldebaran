<?php

namespace App\Providers;

use App\Models\Commission\CommissionClass;
use App\Models\Gallery\Project;
use Auth;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
        //
        Schema::defaultStringLength(191);
        Paginator::useBootstrap();

        if (Schema::hasTable('projects')) {
            View::share('visibleProjects', Project::visible()->orderBy('sort', 'DESC')->get());
            view()->composer('*', function ($view) {
                $commissionClasses = CommissionClass::active(Auth::check() ? Auth::user() : null)->orderBy('sort', 'DESC')->get();

                $view->with('commissionClasses', $commissionClasses);
            });
        }

        /*
         * Paginate a standard Laravel Collection.
         *
         * @param int $perPage
         * @param int $total
         * @param int $page
         * @param string $pageName
         * @return array
         */
        Collection::macro('paginate', function ($perPage, $total = null, $page = null, $pageName = 'page') {
            $page = $page ?: LengthAwarePaginator::resolveCurrentPage($pageName);

            return new LengthAwarePaginator(
                $this->forPage($page, $perPage),
                $total ?: $this->count(),
                $perPage,
                $page,
                [
                    'path'     => LengthAwarePaginator::resolveCurrentPath(),
                    'pageName' => $pageName,
                ]
            );
        });
    }
}
