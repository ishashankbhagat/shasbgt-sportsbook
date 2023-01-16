<?php
namespace ShasBgt\Sportsbook;

use Illuminate\Support\ServiceProvider;

class SportsbookServiceProvider extends ServiceProvider {

  public function boot()
  {
    $this->loadViewsFrom(realpath(__DIR__.'/resources/views'), 'shasbgt_sportsbook');

    $this->mergeConfigFrom(__DIR__.'/config/shasbgt/tmt.php', 'shasbgt.tmt');

    $this->publishes([
        __DIR__.'/../config/shasbgt/tmt.php' => config_path('shasbgt/tmt.php'),
    ]);

  }

  public function register()
  {

  }
}
?>
