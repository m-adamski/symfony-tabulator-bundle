# Tabulator Bundle for Symfony

The Symfony Bundle, supporting the [Tabulator](https://tabulator.info) library.

Please keep in mind that this library doesn't cover all the available functionality of the Tabulator library.
I tried to create a tool that will simplify the handling of tables and dynamic data loading using Ajax requests.

## Installation

Composer can install this bundle:

```shell
composer require m-adamski/symfony-tabulator-bundle
```

## Quickstart

Tabulator initialization is done using the ``create`` function of the provided ``TabulatorFactory``.
All remaining configuration is performed on the created ``Tabulator`` object.

```php
use Adamski\Symfony\TabulatorBundle\Adapter\Doctrine\RepositoryAdapter;
use Adamski\Symfony\TabulatorBundle\Column\DateTimeColumn;
use Adamski\Symfony\TabulatorBundle\Column\TextColumn;
use Adamski\Symfony\TabulatorBundle\Column\TwigColumn;
use Adamski\Symfony\TabulatorBundle\TabulatorFactory;
use App\Entity\Client;
use App\Repository\ClientRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class ClientController extends AbstractController {
    public function __construct(
        private readonly TabulatorFactory $tabulatorFactory,
    ) {}

    #[Route("/{_locale}/client", name: "client", methods: ["GET"])]
    public function index(Request $request): Response {
        $clientTable = $this->tabulatorFactory
            ->create("#table")
            ->setOptions([
                "paginationSize" => 5,
            ])
            ->addColumn("name", TextColumn::class, [
                "title" => "Name",
            ])
            ->addColumn("secretToken", TextColumn::class, [
                "title" => "Token",
                "extra" => [
                    "widthGrow" => 2
                ]
            ])
            ->addColumn("active", TwigColumn::class, [
                "title"    => "Status",
                "template" => "modules/Client/table/active.html.twig"
            ])
            ->addColumn("creationDate", DateTimeColumn::class, [
                "title"  => "Created",
                "format" => "Y-m-d H:i",
            ])
            ->createAdapter(RepositoryAdapter::class, [
                "entity"        => Client::class,
                "query_builder" => function (ClientRepository $clientRepository) {
                    return $clientRepository->createQueryBuilder("client");
                }
            ]);

        if (null !== ($tableResponse = $clientTable->handleRequest($request))) {
            return $tableResponse;
        }

        return $this->render("modules/Client/index.html.twig", [
            "table" => $clientTable
        ]);
    }
}
```

The next step is to prepare the JS file in which we will initialize the Tabulator.
In the example below we use the functionality from
the [AssetMapper](https://symfony.com/doc/current/frontend/asset_mapper.html) component, but it should work fine without
it e.g. importing Tabulator from CDN (ignore first two imports from the code below).

```javascript
import "tabulator-tables/dist/css/tabulator.min.css";
import { TabulatorFull as Tabulator } from "tabulator-tables";
import { readAttribute } from "./functions/read-attribute.js";
import { initTabulator } from "../../vendor/m-adamski/symfony-tabulator-bundle/src/Resources/public/js/tabulator.js";

let tableConfigAttr = readAttribute("table-config");

if (null !== tableConfigAttr) {
    let tableConfig = JSON.parse(tableConfigAttr);

    // Init Tabulator
    initTabulator(Tabulator, tableConfig).then((table) => {
        // Here you have access to the Tabulator instance
    });
}
```

As you may have noticed, in the above example we use a custom ``readAttribute`` function.
This is a function that reads the value from the data attribute.

```javascript
import _ from "lodash";

export function readAttribute(value) {
    const currentScript = document.querySelector("script[type='importmap']");

    if (currentScript !== null) {
        return currentScript.dataset[_.camelCase(value)];
    }

    return null;
}
```

Of course, it is up to you how you pass the configuration from the HTML template to JS.

Now we need to modify our template by adding the configuration that will be used during JS initialization.
To prepare the configuration we will use ``tabulator_config`` Twig Extension.

```html
{% extends 'base/container.html.twig' %}

{% block content %}
    <div id="table">Loading data..</div>
{% endblock %}
{% block importmap %}
    {{ importmap(['application', 'table'], {'data-table-config': tabulator_config(table)}) }}
{% endblock %}
```

## Available columns

Each available column has four basic options:

| Name       | Type    | Default Value | Description                                                                                                                                |
|------------|---------|---------------|--------------------------------------------------------------------------------------------------------------------------------------------|
| title      | string  | -             | Title of the column                                                                                                                        |
| visible    | boolean | true          | Decide whether the column will be visible or not                                                                                           |
| filterable | boolean | true          | Decides whether filtering by this column will be possible                                                                                  |
| extra      | array   | []            | [Tabulator Column Definition](https://tabulator.info/docs/6.3/columns#definition) (all options given here will be passed to the JS script) |

### TextColumn

The basic column that is used to display values.

No additional options available.

### CallableColumn

A custom function is called to obtain the value to be displayed.

Additional options:

| Name      | Type          | Default Value | Description                                                              |
|-----------|---------------|---------------|--------------------------------------------------------------------------|
| callable  | callable      | -             | The function that will be called - the row will be passed as a parameter |

### DateTimeColumn

Column that will display DateTime in a defined format.

Additional options:

| Name   | Type   | Default Value | Description                  |
|--------|--------|---------------|------------------------------|
| format | string | -             | Date format e.g. Y-m-d H:i:s |

### PropertyColumn

The value to display will be taken from the specified property of the row object.
This column uses the PropertyAccess Component.

Additional options:

| Name      | Type          | Default Value | Description                                                                                                                                                                   |
|-----------|---------------|---------------|-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| property  | string        | -             | Property of the object from which the value to be displayed will be obtained. [The PropertyAccess Component](https://symfony.com/doc/current/components/property_access.html) |
| nullValue | string / null | null          | The value that will be passed if the value of the property is null                                                                                                            |


### TickCrossColumn

A column that can display different content for different values of a boolean variable.
It will create column with [TickCross Formatter](https://tabulator.info/docs/6.3/format#formatter-tickcross).

Additional options:

| Name         | Type    | Default Value | Description                                                                                                      |
|--------------|---------|---------------|------------------------------------------------------------------------------------------------------------------|
| tickElement  | string  | -             | Custom HTML for the tick element, if set to false the tick element will not be shown (it will only show crosses) |
| crossElement | string  | -             | Custom HTML for the cross element, if set to false the cross element will not be shown (it will only show ticks) |
| allowEmpty   | boolean | false         | Set to true to allow any truthy value to show a tick                                                             |
| allowTruthy  | boolean | false         | Set to true to cause empty values (undefined, null, "") to display an empty cell instead of a cross              |

### TwigColumn

The column will be rendered based on the provided Twig template.
It will create column with [HTML Formatter](https://tabulator.info/docs/6.3/format#formatter-html).

Additional options:

| Name     | Type    | Default Value | Description                                                                                                        |
|----------|---------|---------------|--------------------------------------------------------------------------------------------------------------------|
| template | string  | -             | Path to the Twig template file                                                                                     |
| passRow  | boolean | false         | Decides whether to pass only the value from the defined column or the entire row/object to the given Twig template |

## License

MIT
