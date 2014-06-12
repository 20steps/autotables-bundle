# 20steps/autotables-bundle (twentystepsAutoTablesBundle)

## About

The 20steps AutoTables Bundle provides an easy way for displaying entities in Symfony2 applications in auto-generated and editable tables. The tables are rendered by [DataTables](https://datatables.net/) and are made editable with a patched version of [jquery.datatables.editable](https://code.google.com/p/jquery-datatables-editable/).

The AutoTables Bundle is licensed under the LGPL license version 3.0 (http://www.gnu.org/licenses/lgpl-3.0.html).

## Features

* Visualization of custom entities in auto-generated tables
* Updating, removing and adding entities
* Integration of Doctrine repositories
* Integration of custom CRUD services
* Annotating entities with Doctrine annotations
* Annotating entities with AutoTablesBundle annotations
* Displaying columns of either properties or methods
* Modifying columns for either properties or methods
* Declaring columns as read-only
* Auto-initialization feature for newly added entities supporting constant values and ManyToOne-Mappings
* JQuery-UI and Bootstrap3 support
* Many customization options

## Installation

Require the bundle by adding the following entry to the respective section of your composer.json:

```
"20steps/autotables-bundle": "dev-master"
```

Get the bundle via packagist from GitHub by calling:

```
php composer.phar update 20steps/autotables-bundle
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
    prefix:   /autoTables
```

## Usage

The following sections describe how to integrate the auto-generated tables in your application. As a prerequisite the steps of the Installation section has to be done.

### Global configuration

The bundle is configured in your config.yml with a section named twentysteps_auto_tables:

```
twentysteps_auto_tables:
    ...
```

Possible global configuration options are:

* default_datatables_options: Default configuration of the [DataTables](https://datatables.net/) plugin.
It is given by a string containing a JSON representation of the options. Any settings given here will be extended by table specific options.

* trans_scope: Default scope for autotables related translations.

* frontend_framework: Frontend framework to be used for rendering. Currently the values "jquery-ui" (the default) and "bootstrap3" are supported.

```
twentysteps_auto_tables:
    default_datatables_options: >
    {
      "sDom": "TC<\\'clear'><'row table-header'<'col-md-3'f><'col-md-4'p>r>t<'row table-footer'<'col-md-9'i><'col-md-3'l>>"
    }
    trans_scope: 'autoTableMessages'
```

### Table specific configuration

Now the tables to be rendered by the bundle has to be configured. This happens as a list in a *tables* section.
Each table configuration has to have an *id* property. This is used to reference the configurations in later calls
to the bundle.

#### repository and service

Additionally each table configuration has to define either a *repository* or a *service*. The *repository* is the
name of a Doctrine repository for handling the entities to be printed. If you are not using Doctrine (or for some other reason)
you can define a *service* pointing to a service implementing the AutoTablesCrudService interface.

#### trans_scope

With the property *trans_scope* you can define a new scope for translating the messages used for the table. These are
the names of the columns and some additional messages like found in the messages.en.yml file of the bundle. By default the value
from the global configuration is used.

#### datatable_options

The property *datatables_options* may be used to give some table specific [DataTables](https://datatables.net/)  plugin
configuration, which will extend any configuration in the global section.

#### views

The *views* option may point to a directory with custom templates for overwriting the ones of the bundle.

#### columns

With *columns* you are able to overwrite any setting done by an autotables annotation resp. you are able to define settings
that hasn't been configured by an annotation. Any column setting in the config.yml overwrites any annotation.

Currently the following things may be configured for a column: *name*, *readOnly*, *type*, *order* and *ignore*.

The parameter *selector* has to be given to select the column to be overwritten. This may be a property like *description*, or
a method like *getDisplayName()*.

For columns that should be auto initialized while using the add entry form, you may configure an *initializer* section.
This section may contain the values *repository*, *value* and *id*.

The property *value* simply selects a constant value to be injected into each new entity with this column. By selecting a
repository one is able to inject an object found by a given id. The id may be passed by a constant value here in the
 config.xml or later in the Twig view while using the function *ts_auto_table_options*.

#### Example

Here is an example of a configuration given for a table *products*:

```
twentysteps_auto_tables:
  default_datatables_options: >
    {
      "sDom": "TC<\\'clear'><'row table-header'<'col-md-3'f><'col-md-4'p>r>t<'row table-footer'<'col-md-9'i><'col-md-3'l>>"
    }
  tables:
    -
      id: "products"
      repository: 'AcmeStoreBundle:Product'
      trans_scope: 'autoTables'
      datatables_options: >
        {
          "oLanguage": {
            "sLengthMenu": "_MENU_ entries per page",
            "sSearch": "Search",
            "oTableTools": {
                "aButtons": [
                  {"sExtends": "copy", "sButtonText": "Copy"},
                ]
            }
          }
        }
      columns:
              -
                selector: 'description'
                readOnly: true
                order: 2
              -
                selector: 'client'
                visible: false
                initializer:
                    repository: 'AcmeStoreBundle:Client'
              -
                selector: 'getDisplayName()'
                order: 10
                readOnly: false
```

### Annotation of entities

The information needed to render any column into a table is taken from annotations found in the entity.
The bundle searches for Doctrine annotations like *@Doctrine\ORM\Mapping\Column* and *@Doctrine\ORM\Mapping\Id*.
If you want to give a different name, set an order value or for some other reason you can use the bundle's
annotation *twentysteps\Bundle\AutoTablesBundle\Annotations\Column*. Even getter methods may be annotated
with this annotation, to create a column displaying the value returned by the getter. To be able to update the value in this case,
a setter of the same name has to be created (getFoo/setFoo). It's even possible to disable the editing of a column by using the
*readOnly* property of the annotation.

If you want to avoid rendering the column in the auto generated table, you should set the *ignore* flag of the annotation to true.
In this case the property resp. method is ignored by the bundle.

Like mentioned in the configuration section, any column may be initialized automatically while using the add entry form.
This may be configured with the *Initializer* annotation like described in the configuration section or shown in the following examples:

```
/**
 * @ORM\ManyToOne(targetEntity="Client", inversedBy="products")
 * @ORM\JoinColumn(name="client_id", referencedColumnName="id")
 * @AUT\Column(type="mixed", visible=false, initializer=@AUT\Initializer(repository="AcmeStoreBundle:Client"))
 */
private $client;

/**
 * @ORM\Column(type="string", length=100)
 * @AUT\Column(visible=false, initializer=@AUT\Initializer(value="bar"))
 */
private $foo;

```

The following code block shows a complete example of a properly annotated entity for AutoTablesBundle:

```
<?php

namespace Acme\StoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use twentysteps\Bundle\AutoTablesBundle\Annotations as AUT;

/**
 * Product
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Acme\StoreBundle\Entity\ProductRepository")
 */
class Product
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @AUT\Column(ignore=true)
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=100)
     * @AUT\Column(ignore=true)
     */
    protected $name;

    /**
     * @ORM\Column(type="decimal", scale=2)
     * @AUT\Column(name="col_prize", order = 2, readOnly = true)
     */
    protected $price;

    /**
     * @ORM\Column(type="text")
     * @AUT\Column(name="col_description", order = 3)
     */
    protected $description;

    /**
     * @AUT\Column(name="col_name", type="string", order=1)
     * @return string
     */
    public function getDisplayName()
    {
        return '#' . $this->name . '#';
    }

    public function setDisplayName($name)
    {
        $this->name = trim($name, '#');
    }
```

### View integration

Now your view has to be modified to render the table for your entities. We are assuming here that you are using
Twig templates.

#### ts_auto_table_stylesheets

The needed stylesheets of the bundle are included with the Twig function *ts_auto_table_stylesheets*. The stylesheets of
the selected frontend framework may be included here, if the corresponding option is set to true: *includeJqueryUi* resp.
*includeBootstrap3*.

#### ts_auto_table_options

You have to define at least the *entities* and the *tableId* for the data to be rendered. This is done by the function
*ts_auto_table_options* like done in the following example:

```
{{ ts_auto_table_options({
        'entities': products, 'tableId': 'products',
        'columns': [
            {
                'selector': 'getDisplayName()',
                'visible': true
            },
            {
                'selector': 'client',
                'initializer': {
                    'id': client.id
                }
            }
            {
                'selector': 'id',
                'visible': false
            }
    ]}) }}
```

As you can see you can also overwrite the configuration of any column here.

#### ts_auto_table_html

The HTML for the table has to be rendered with the Twig function *ts_auto_table_html*. To be able to render any table here, you
have to ensure that the function *ts_auto_table_options* is called in advance.

#### ts_auto_table_js

Finally the javascript code for the table has to be rendered. This is done by the Twig function *ts_auto_table_js*.
By default all needed javascript libraries are loaded excepting the jquery library. This behaviour can be configured by the
following options: *includeBootstrap3*, *includeJquery, includeJqueryUi, includeJqueryEditable, includeJqueryDataTables* and *includeJqueryValidate*

The [DataTables](https://datatables.net/) configuration may be extended here with the option *dtOptions*.

Furthermore the *transScope* may be overwritten here and the routes for the CRUD controller actions with the options
*updateRoute, deleteRoute* and *addRoute*.

The option *reloadAfterAdd* (default true) may be set to *true* to reload the page after an entity has been added. Currently this is needed
 to refresh any links rendered in custom view templates.

There is also an option *reloadAfterUpdate* (default false), with the same effect.

And like *ts_auto_table_html* this function also needs to be preceded by a call to *ts_auto_table_options*.

#### Example

In the following example you can see how to build a view displaying the entity *products*:

```
{% extends "TwigBundle::layout.html.twig" %}

{% block head %}
    <link rel="icon" sizes="16x16" href="{{ asset('favicon.ico') }}"/>
    <link rel="stylesheet" href="{{ asset('bundles/acmedemo/css/demo.css') }}"/>
    {{ ts_auto_table_stylesheets() }}
{% endblock %}

{% block title 'Product list' %}

{% block body %}
    <div class="block">
        List of products

        {{ ts_auto_table_options({
                'entities': products, 'tableId': 'products',
                'columns': [
                    {
                        'selector': 'getDisplayName()',
                        'ignore': false
                    },
                    {
                        'selector': 'id',
                        'ignore': false
                    }
                ]}) }}

        {{ ts_auto_table_html() }}
        {{ ts_auto_table_js({'includeJquery': true}) }}
    </div>
{% endblock %}

```

## Author

Marc Ewert (marc.ewert@20steps.de)

sponsored by: <a href="http://20steps.de">20steps - Digital Full Service Boutique</a>