# About BAV

BAV (bank account validator) is a validation library for German bank accounts.


# Installation

Use [Composer](https://getcomposer.org/):

```json
{
    "require": {
        "malkusch/bav": "^1"
    }
}
```

# Configuration

You can use BAV out of the box. BAV comes with a ready to play default
configuration ([`DefaultConfiguration`](http://bav-php.github.io/bav/api/class-malkusch.bav.DefaultConfiguration.html)):

* `UTF-8` encoding (if supported)

* [`FileDataBackendContainer`](http://bav-php.github.io/bav/api/class-malkusch.bav.FileDataBackendContainer.html).
I.e. it uses binary search on the file from the Bundesbank.
Note that this data backend uses the directory `bav/data` for install and update
operations. You have to make sure that this directory is writable.

* automatic installation. You don't have to call any installation
script. The container will download the Bundesbank file upon the first execution.

* update plan which triggers an E_USER_NOTICE if the Bundesbank file
is outdated.

You can define your own configuration by calling
[`ConfigurationRegistry::setConfiguration()`](http://bav-php.github.io/bav/api/class-malkusch.bav.ConfigurationRegistry.html#_setConfiguration)
or preferably creating the file `bav/configuration.php` which returns a
[`Configuration`](http://bav-php.github.io/bav/api/class-malkusch.bav.Configuration.html) object:

```php
namespace malkusch\bav;

$configuration = new DefaultConfiguration();

$pdo = new \PDO("mysql:host=localhost;dbname=test");
$configuration->setDataBackendContainer(new PDODataBackendContainer($pdo));

$configuration->setUpdatePlan(new AutomaticUpdatePlan());

return $configuration;
```


# Update

The Bundesbank releases new files for March, June, September and December.
BAV needs those new files. You have several possiblities to update bav:

## Script

Call `bin/bav-update.php`.

## Programmatically

```php
use malkusch\bav\BAV;

$bav = new BAV();
$bav->update();
```

## Automatic

Enable automatic updates with
[`AutomaticUpdatePlan`](http://bav-php.github.io/bav/api/class-malkusch.bav.AutomaticUpdatePlan.html)
in your `bav/configuration.php`:

```php
namespace malkusch\bav;

$configuration = new DefaultConfiguration();
$configuration->setUpdatePlan(new AutomaticUpdatePlan());

return $configuration;
```
This automatic update plan will perform long running update operations as a shutdown
hook. I.e. it won't bother users during normal operations.


# Usage

You can use BAV with the api facade
[`BAV`](http://bav-php.github.io/bav/api/class-malkusch.bav.BAV.html):

* [`BAV::isValidBank($bankID)`](http://bav-php.github.io/bav/api/class-malkusch.bav.BAV.html#_isValidBank):
Returns true for existing bank ids.

* [`BAV::isValidBankAccount($bankID, $account)`](http://bav-php.github.io/bav/api/class-malkusch.bav.BAV.html#_isValidBankAccount):
Returns true for existing accounts of an existing bank.

* [`BAV::isValidAccount($account)`](http://bav-php.github.io/bav/api/class-malkusch.bav.BAV.html#_isValidAccount):
This method validates an account against the bank of the last `isValidBank()` call.

* [`BAV::getValidBankFilterCallback()`](http://bav-php.github.io/bav/api/class-malkusch.bav.BAV.html#_getValidBankFilterCallback):
Returns a callback for filter bank validation.

* [`BAV::getValidAccountFilterCallback()`](http://bav-php.github.io/bav/api/class-malkusch.bav.BAV.html#_getValidAccountFilterCallback):
Returns a callback for filter account validation. The account filter needs
to be called after the bank filter.

* [`BAV::getMainAgency()`](http://bav-php.github.io/bav/api/class-malkusch.bav.BAV.html#_getMainAgency):
Returns the main agency of a bank.

* [`BAV::getAgencies()`](http://bav-php.github.io/bav/api/class-malkusch.bav.BAV.html#_getAgencies):
Returns further agencies. The main agency is not included in this list.
This list can be empty.

An [`Agency`](http://bav-php.github.io/bav/api/class-malkusch.bav.Agency.html)
object has the fields:

* [`Agency::getBIC()`](http://bav-php.github.io/bav/api/class-malkusch.bav.Agency.html#_getBIC)

* [`Agency::getPostcode()`](http://bav-php.github.io/bav/api/class-malkusch.bav.Agency.html#_getPostcode)

* [`Agency::getCity()`](http://bav-php.github.io/bav/api/class-malkusch.bav.Agency.html#_getCity)

* [`Agency::getName()`](http://bav-php.github.io/bav/api/class-malkusch.bav.Agency.html#_getName)

* [`Agency::getShortTerm()`](http://bav-php.github.io/bav/api/class-malkusch.bav.Agency.html#_getShortTerm)

* [`Agency::getPAN()`](http://bav-php.github.io/bav/api/class-malkusch.bav.Agency.html#_getPAN)

## Example

```php
use malkusch\bav\BAV;

$bav = new BAV();
$bankID  = "10000000";
$account = "1234567890"

// check for a bank
var_dump(
    $bav->isValidBank($bankID)
);

// check for a bank account
var_dump(
    $bav->isValidBankAccount($bankID, $account)
);

// filter validation
var_dump(
    filter_var($bankID, FILTER_CALLBACK, $bav->getValidBankFilterCallback()),
    filter_var($account, FILTER_CALLBACK, $bav->getValidAccountFilterCallback())
);

// Get informations about a bank
$agency = $bav->getMainAgency($bankID);
echo "{$agency->getName()} {$agency->getCity()}\n";
```
See also `bav/docs/example.php`.


# Optional Dependencies

You may have:

* **CURL**: If you provide `bav/data/banklist.txt` you don't need CURL.

* **mbstring**: BAV works with unicode encoding. Your PHP must have support compiled
in the `mb_*` functions. If these functions are missing BAV works only with the ISO-8859-15 encoding.

* **PDO**: If you intend to use a DBS you need to use
[`PDODataBackendContainer`](http://bav-php.github.io/bav/api/class-malkusch.bav.PDODataBackendContainer.html). 
`PDODataBackendContainer` needs a `PDO` support compiled in PHP.

* **doctrine/orm**: You can use
[`DoctrineBackendContainer`](http://bav-php.github.io/bav/api/class-malkusch.bav.DoctrineBackendContainer.html)
which uses doctrine
as data backend.


# License and authors

This project is free and under the WTFPL. So do what ever you want.
But it would be nice to leave a note about the authors.

The author of the original project which gave the idea to this project is
Björn Wilmsmann. Responsable for this project is Markus Malkusch <markus@malkusch.de>.

## Donations

If you like BAV and feel generous donate a few Bitcoins here:
[1335STSwu9hST4vcMRppEPgENMHD2r1REK](bitcoin:1335STSwu9hST4vcMRppEPgENMHD2r1REK)

[![Build Status](https://travis-ci.org/bav-php/bav.svg?branch=master)](https://travis-ci.org/bav-php/bav)
