# Tabulator Bundle for Symfony

The Symfony Bundle, supporting the [Tabulator](https://tabulator.info) library.

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
            "table"   => $clientTable->getConfig(),
        ]);
    }
}
```

The next step is to prepare the JS file in which we will initialize the Tabulator.
In the example below we use the functionality from
the [AssetMapper](https://symfony.com/doc/current/frontend/asset_mapper.html) component, but it should work fine without
it e.g. importing Tabulator from CDN (ignore first import from the code below).

```javascript
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

## License

MIT
