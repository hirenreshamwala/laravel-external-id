# Generate public usage id when saving Eloquent models
This package provides a trait that will generate a public usage id when saving any Eloquent model.

```php
$model = new EloquentModel();
$model->save();

echo $model->external_id; // ouputs "activerecord-is-awesome"
```

## Installation

You can install the package via composer:
``` bash
composer require xt/laravel-external-id
```

## Usage

Your Eloquent models should use the `XT\ExternalId\HasExternalId` trait and the `XT\ExternalId\ExternalIdOptions` class.

The trait contains an abstract method `getExternalIdOptions()` that you must implement yourself.

Your models' migrations should have a field to save the generated external id to.

Here's an example of how to implement the trait:

```php
namespace App;

use XT\ExternalId\HasExternalId;
use XT\ExternalId\ExternalIdOptions;
use Illuminate\Database\Eloquent\Model;

class YourEloquentModel extends Model
{
    use HasExternalId;

    /**
     * Get the options for generating the slug.
     */
    public function getExternalIdOptions() : ExternalIdOptions
    {
        return ExternalIdOptions::create()
            ->saveExternalIdTo('external_id');
    }
}
```

With its migration:

```php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateYourEloquentModelTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('your_eloquent_models', function (Blueprint $table) {
            $table->increments('id');
            $table->string('external_id'); // Field name same as your `saveExternalIdTo`
            $table->string('name');
            $table->timestamps();
        });
    }
}

```


You can also specify prefix, length, numeric id only, time based id or create your own custom id
```php
namespace App;

use XT\ExternalId\HasExternalId;
use XT\ExternalId\ExternalIdOptions;
use Illuminate\Database\Eloquent\Model;

class YourEloquentModel extends Model
{
    use HasExternalId;

    /**
     * Get the options for generating the slug.
     */
    public function getExternalIdOptions() : ExternalIdOptions
    {
        // generate alpha numeric id of particular length
        return ExternalIdOptions::create()
            ->saveExternalIdTo('external_id')
            ->setLength(16);
        
        // Generate id with prefix
        return ExternalIdOptions::create()
            ->saveExternalIdTo('external_id')    
            ->setPrefix('order_'); // Output: order_<generated_id>
            
        // Generate numeric id of particular length
        return ExternalIdOptions::create()
            ->saveExternalIdTo('external_id')
            ->setLength(16)
            ->setIsNumberOnly(true); //Optional

        // Generate incremental id
        return ExternalIdOptions::create()
            ->saveExternalIdTo('external_id')
            ->incremental(1); // increment start from 1. Default value is: 1
            
        // Generate unix timestamp based id
        return ExternalIdOptions::create()
            ->saveExternalIdTo('external_id')
            ->setIsTimeBase(true); // Output: 16628769732187896
    
        // You can create your own id generation logic
        return ExternalIdOptions::create()
            ->saveExternalIdTo('external_id')
            ->customIdScope(function () {
                return getRandomString(6, false).getRandomNumber(6);
            });
    }
}
```

To get the record using external id, you can use `findByExternalId` method

```php
YourEloquentModel::findByExternalId('<Your-ID>');

YourEloquentModel::findByExternalId('<Your-ID>', ['column1', 'column2']);

OR

YourEloquentModel::findByExternalIdOrFail('<Your-ID>');

YourEloquentModel::findByExternalIdOrFail('<Your-ID>', ['column1', 'column2']);
```


## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
