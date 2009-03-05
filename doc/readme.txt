License and authors
-------------------

    This project is free and under GPL (see gpl.txt). So do what ever you want.
    But it would be nice to leave a note about the authors.

    The author of the original project which gave the idea to this project is
    Björn Wilmsmann. Responsable for this project is Markus Malkusch <bav@malkusch.de>.


Requirements
------------

    You must have:

    * Reflection API
        The Autoloader needs the Reflection API to know which class has a class constructor.


    You may have:

    * CURL
        BAV_DataBackend_File->update() makes usage of the curl_* methods.
        BAV_DataBackend_File->update() is called to download the bank data from the
        Bundesbank. This is also needed by BAV_DataBackend_PDO->update(). If you provide
        data/banklist.txt without using BAV_DataBackend_File->update() you don't need CURL.

    * mbstring or iconv
        BAV works with unicode encoding. Your PHP must have support compiled
        in to either the mb_* or the iconv_* functions. If these functions are missing BAV
        works only with the ISO-8859-15 encoding.

    * PDO
        If you intend to use a DBS you need to use BAV_DataBackend_PDO. BAV_DataBackend_PDO
        needs a PDO support compiled in PHP.


Configuration
-------------

    There is nothing to configure at all. The configuration is done implicitly by
    the parameters you use to create a BAV_DataBackend object. You may specify another encoding 
    than UTF-8 with BAV::setEncoding().

    But before you can use any class of BAV you have to include the BAV_Autoloader
    class definition. You find it at classes/autoloader/BAV_Autoloader.php.
    BAV_Autoloader uses PHP's spl_autoload_* mechanism. That means that a __autoload()
    function after this inclusion will be ignored. Avoid to use __autoload(), use
    spl_autoload_register() instead. If you insist on using the IMO superfluous
    __autoload() it will work only if you've defined it before you include BAV_Autoloader.


Installation and Update
-----------------------

    Visit <http://bav.malkusch.de/> and download the latest version.

    You have to decide which datastructure you'll use. BAV comes with two structures:
    BAV_DataBackend_File and BAV_DataBackend_PDO. BAV_DataBackend_File uses the text
    file which is provided by the Bundesbank. BAV_DataBackend_PDO uses PHP's PDO-mechanism
    to connect with a DBS.

    If you use BAV the first time you have to create a BAV_DataBackend object (PDO or File)
    and call the method install(). In case of PDO it will create the tables. install() does
    also call update() to snychronize the first time with the Bundesbank.

    To keep your database synchronized you have to call update(). It will download the file
    from the Bundesbank and update your datastructure. The Bundesbank releases 4 times a year
    a new file: March, June, September, December.


Usage
-----

    Define one BAV_DataBackend object (which has an installed data structure). Use the
    BAV_DataBackend->getBank($bankID) to get a BAV_Bank object. This might raise a
    BAV_DataBackendException_BankNotFound if the bank was not found. If you only want to
    check if a bank exists you may use BAV_DataBackend->bankExists($bankID).

    You can use the BAV_Bank object to get information about the bank. Every bank has a
    main agency. You get this BAV_Agency object with BAV_Bank->getMainAgency(). A bank might
    also have some more agencies. These optional agencies can be fetched with
    BAV_Bank->getAgencies(). Note that the main agency is not included in this array. So the
    array BAV_Bank->getAgencies() might even be empty.

    A BAV_Agency object provides these informations:

        BAV_Agency->getPostcode()
        BAV_Agency->getCity()
        BAV_Agency->getName()
        BAV_Agency->getShortTerm()
        BAV_Agency->hasPAN()
        BAV_Agency->getPAN()
        BAV_Agency->hasBIC()
        BAV_Agency->getBIC()

    The boolean method BAV_Bank->isValid($accountID) will tell you if the account is valid (TRUE)
    or invalid (FALSE).

    If you use BAV_DataBackend_PDO you may use BAV_DataBackend_PDO->getAgencies($sql) to search for
    BAV_Agency objects with an arbitrary SQL statement. Your statement should at least return the id
    of the agencies. You perform better if your statement returns all attributes of the agency table.

    BAV uses UTF-8 as default enconding. So every string (especialy BAV_Agency->get*()) in BAV
    is UTF-8 encoded. If you intend to work with those strings you should use PHP's mb_* oder iconv_*
    methods.


Example
-------

    You find it in scripts/example.php


Helping BAV
-----------

    test/checkValidators.php can find broken classes. This is only usefull for debugging
    purposes if you implement new validators or data backends. There exists a file with account
    ids for testing the validators. It's located at data/verify.ini. You may add some account
    ids, if you want them to be checked by checkValidators.php. If you then find some
    validators which are implemented incorrectly, you should send a bug report. Don't forget
    to mention the validation alogrithm (BAV_Bank->getValidationType()), the account id and
    the expected result.

    There exists also the script scripts/verifyImport.php. You can use this to import
    your bank accounts to a data/verify.ini and check them with test/checkValidators.php.
    See the comments in verifyImport.php for more details on usage. If there are
    errors you can send your verify.ini to <bav@malkusch.de>. Even if there aren't
    errors you can send this file, so we have a larger database of testing
    accounts for future implementations. The verify.ini contains only bank
    accounts with the validation type. There is no information about the bank id,
    so the verify.ini can't be abused.

