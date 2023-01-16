<?php
namespace ShasBgt\Sportsbook;

use Illuminate\Support\ServiceProvider;

class SportsbookServiceProvider extends ServiceProvider {

  public function boot()
  {
    $this->loadViewsWithFallbacks();

    $this->loadConfigs();

    $this->publishFiles();
  }

  public function loadConfigs()
  {

    $this->mergeConfigFrom(__DIR__.'/config/shasbgt/tmt.php', 'shasbgt.tmt');

  }


  public function publishFiles()
  {
    $this->publishes([
        __DIR__.'/config/shasbgt/tmt.php' => config_path('shasbgt/tmt.php'),
    ]);
  }


    public function loadViewsWithFallbacks()
    {
        $customBaseFolder = resource_path('views/vendor/shasbgt/sportsbook');

        // - first the published/overwritten views (in case they have any changes)
        if (file_exists($customBaseFolder)) {
            $this->loadViewsFrom($customBaseFolder, 'shasbgt_sportsbook');
        }
        // - then the stock views that come with the package, in case a published view might be missing
        $this->loadViewsFrom(realpath(__DIR__.'/resources/views'), 'shasbgt_sportsbook');
    }

  public function register()
  {

  }


  static public function checkLoading()
  {
    return 'Hii, it is loading';
  }
}
?>
