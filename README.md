# laravel-pending-change

A laravel package for saving changes to models that will be applied later

# Installation
Make sure you are running this in a laravel application

    composer require artificerkal/laravel-pending-change

Finally add this to your app.php config file as a package provider:

    Artificerkal\LaravelPendingChange\PendingChangeServiceProvider::class,