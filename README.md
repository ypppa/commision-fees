# Commission Fees



Commission Fees is the special service to calculate transactions' commissions and fees.

## Installation

Use the package manager [composer](https://getcomposer.org/) to install Commission Fees project.

```bash
composer install
```

## Execution

CSV file format example:

```bash
php app.php operations.csv csv
```

JSON file format example:

```bash
php app.php operations.json json
```

## Code style

To run code style check type command:

```bash
composer check-cs
```

## Running tests

To run functional tests:

```bash
php bin/phpunit tests/Functional
```

To run unit tests:

```bash
php bin/phpunit tests/Unit
```
