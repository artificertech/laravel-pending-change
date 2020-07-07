# laravel-pending-change

A laravel package for saving changes to models that will be applied later

# Installation
Make sure you are running this in a laravel application

    composer require artificerkal/laravel-pending-change

Finally add this to your app.php config file as a package provider:

    Artificerkal\LaravelPendingChange\PendingChangeServiceProvider::class,

# Compatibility

- Illuminate\Database\Eloquent\Model::toArray:
  
  The HasPendingChange Trait overrides the toArray function to prevent the draft_data attribute from containing model relations. HasPendingChange should not be applied to a class with a toArray function defined in it but should be instead applied to a child class
