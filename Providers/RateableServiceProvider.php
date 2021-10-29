<?php

namespace Modules\Rateable\Providers;

use Illuminate\Database\Eloquent\Factory as EloquentFactory;
use Illuminate\Support\ServiceProvider;
use Modules\Core\Traits\CanPublishConfiguration;
use Modules\Core\Events\BuildingSidebar;
use Modules\Core\Events\LoadingBackendTranslations;
use Modules\Rateable\Listeners\RegisterRateableSidebar;
use Illuminate\Support\Facades\Blade;

class RateableServiceProvider extends ServiceProvider
{
    use CanPublishConfiguration;
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerBindings();
        $this->app['events']->listen(BuildingSidebar::class, RegisterRateableSidebar::class);

        $this->app['events']->listen(LoadingBackendTranslations::class, function (LoadingBackendTranslations $event) {
            // append translations
        });


    }

    public function boot()
    {
       
        $this->publishConfig('rateable', 'config');
        $this->publishConfig('rateable', 'crud-fields');

        $this->mergeConfigFrom($this->getModuleConfigFilePath('rateable', 'settings'), "asgard.rateable.settings");
        $this->mergeConfigFrom($this->getModuleConfigFilePath('rateable', 'settings-fields'), "asgard.rateable.settings-fields");
        $this->mergeConfigFrom($this->getModuleConfigFilePath('rateable', 'permissions'), "asgard.rateable.permissions");

        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');

        $this->registerComponents();
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array();
    }


    /**
    * Register components
    */
    private function registerComponents()
    {
        Blade::componentNamespace("Modules\Rateable\View\Components", 'rateable');
    }

    /**
    * Register binding
    */
    private function registerBindings()
    {
        $this->app->bind(
            'Modules\Rateable\Repositories\RatingRepository',
            function () {
                $repository = new \Modules\Rateable\Repositories\Eloquent\EloquentRatingRepository(new \Modules\Rateable\Entities\Rating());

                if (! config('app.cache')) {
                    return $repository;
                }

                return new \Modules\Rateable\Repositories\Cache\CacheRatingDecorator($repository);
            }
        );
// add bindings

    }


}
