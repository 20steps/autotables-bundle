# 20steps/datatables-bundle (twentystepsAutoTablesBundle)

## About

The 20steps DataTables Bundle provides an easy way for displaying entities in Symfony2 applications in editable tables. The tables are rendered by [DataTables](https://datatables.net/) and are made editable with a patched version of [jquery.datatables.editable](https://code.google.com/p/jquery-datatables-editable/).

## Features

* Visualization of custom entities
* Updating, removing and adding entities
* Integration of Doctrine repositories
* Integration of custom CRUD services
* Annotating entities with Doctrine annotations
* Annotating entities with AutoTablesBundle annotations
* Displaying columns either for properties and methods
* Modifying columns by either properties and methods
* Declaring columns as read-only
* Many customization options

## Installation

Require the bundle by adding the following entry to the respective section of your composer.json:
```
"20steps/datatables-bundle": "dev-master"
```

Get the bundle via packagist from GitHub by calling:
```
php composer.phar update 20steps/datatables-bundle
```

Register the bundle in your application by adding the following line to the registerBundles() method of your AppKernel.php:  
```
new twentysteps\Bundle\AutoTablesBundle\twentystepsAutoTablesBundle()
```

Add the bundle to your assetic configuration in config.yml:  
```
assetic:
    bundles:        [ 'twentystepsAutoTablesBundle' ]
```

Add the bundle's routes to your routing.yml
```
twentysteps_auto_tables:
    resource: "@twentystepsAutoTablesBundle/Resources/config/routing.yml"
    prefix:   /
```

## Usage

* TBD Configure your tables in config.yml
* TBD Optionally annotate your domain objects
* TBD Optionally implement a CRUD service
* TBD Optionally translate the messages and column names
* TBD Add ts_dataTable_assets, ts_dataTable and ts_dataTable_js to your views

## Reference

## Author

Marc Ewert (marc.ewert@20steps.de)
