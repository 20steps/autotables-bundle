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

* frontend_framework: Frontend framework to be used for rendering. Currently the values "standard" and "bootstrap3" are supported.

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

Additionally each table configuration has to define either a *repository_id* or a *service_id*. The *repository_id* is the
name of a Doctrine repository for handling the entities to be printed. If you are not using Doctrine (or for some other reason)
you can define a *service_id* pointing to a service implementing the AutoTablesCrudService interface.

With the property *trans_scope* you can define a new scope for translating the messages used for the table. These are
the names of the columns and some additional messages like found in the messages.en.yml file of the bundle.

Finally the property *datatables_options* may be used to give some table specific [DataTables](https://datatables.net/)  plugin
configuration, which will extend any configuration in the global section.

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
      repository_id: 'AcmeStoreBundle:Product'
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
```

### Annotation of entities

The information needed to render any column into a table is taken from annotations found in the entity.
The bundle searches for Doctrine annotations like *@Doctrine\ORM\Mapping\Column* and *@Doctrine\ORM\Mapping\Id*.
If you want to give a different name, set an order value or for some other reason you can use the bundle's
annotation *twentysteps\Bundle\AutoTablesBundle\Annotations\ColumnMeta*. Even getter methods may be annotated
with this annotation, to create a column displaying the value returned by the getter. To be able to update the value in this case,
a setter of the same name has to be created (getFoo/setFoo). It's even possible to disable the editing of a column by using the
*readOnly* property of the annotation.

If you want to prevent that a Doctrine property is displayed in the table of the entity you can annotate the property
with the annotation  *twentysteps\Bundle\AutoTablesBundle\Annotations\ColumnIgnore*. In this case the property
is ignored by the bundle.

The following code block shows a complete example of a properly annotated entity for AutoTablesBundle:

```
<?php

namespace Acme\StoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use twentysteps\Bundle\AutoTablesBundle\Annotations as ATB;

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
     * @ATB\ColumnIgnore
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=100)
     * @ATB\ColumnIgnore
     */
    protected $name;

    /**
     * @ORM\Column(type="decimal", scale=2)
     * @ATB\ColumnMeta(name="col_prize", order = 2, readOnly = true)
     */
    protected $price;

    /**
     * @ORM\Column(type="text")
     * @ATB\ColumnMeta(name="col_description", order = 3)
     */
    protected $description;

    /**
     * @ATB\ColumnMeta(name="col_name", type="string", order=1)
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

The needed assets of the bundle are included with the Twig function *ts_autoTable_assets*. This will
include both the needed stylesheet and javascript files. Some people prefer to include the stylesheets in the head of the HTML
document and the javascript files just before the end of the body block. In this case you may use the *ts_autoTable_assets* function
two times. The first time you would exclude the javascript files by using the option ```{'javascript': false}```. The
second time you would exclude the stylesheet files by using the option ```{'stylesheet': false}```.

By default all needed javascript libraries are loaded excepting the jquery library. This behaviour can be configured by the
following options: *includeJquery, includeJqueryUi, includeJqueryEditable, includeJqueryDataTables* and *includeJqueryValidate*

Now it's time to render the JavaScript code for your entity's table. This is done by the Twig function *ts_autoTable_js*.
The call has to include the list of entities to be rendered in the option *entities* and the id of the table configuration
given by the option *tableId*. The [DataTables](https://datatables.net/) configuration may be extended here with the option *dtOptions*.

Furthermore the *transScope* may be overwritten here and the routes for the CRUD controller actions with the options
*updateRoute, deleteRoute* and *addRoute*.

Finally the HTML for the table has to be rendered with the Twig function *ts_autoTable*. Here the options
*entities* and *tableId* are needed again.

In the following example you can see how to build a view displaying the entity *products*:

```
{% extends "TwigBundle::layout.html.twig" %}

{% block head %}
    <link rel="icon" sizes="16x16" href="{{ asset('favicon.ico') }}"/>
    <link rel="stylesheet" href="{{ asset('bundles/acmedemo/css/demo.css') }}"/>
    {{ ts_autoTable_assets({'includeJquery': TRUE}) }}
{% endblock %}

{% block title 'Product list' %}

{% block body %}
    <div class="block">
        List of products

        {{ ts_autoTable({'entities': products, 'tableId': 'products' }) }}

        {{ ts_autoTable_js({
            'entities': products, 'tableId': 'products',
            'dtOptions': {
                'oLanguage': {
                    'sSearch': 'Find'
                }
            }
        }) }}
    </div>
{% endblock %}

```

## Author

Marc Ewert (marc.ewert@20steps.de)
