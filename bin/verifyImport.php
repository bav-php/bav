#!/bin/env php
<?php

namespace malkusch\bav;

/**
 * A sample script for importing many test accounts to a verify.ini
 * you need to modify this script. It won't work unmodified.
 *
 *
 * Copyright (C) 2006  Markus Malkusch <markus@malkusch.de>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @package scripts
 * @author Markus Malkusch <markus@malkusch.de>
 * @copyright Copyright (C) 2006 Markus Malkusch
 * @see VerifyImport
 */


/**
 * We need to require the autoloader
 */
require_once __DIR__ . "/../autoloader/autoloader.php";


/**
 * This works only if you have called FileDataBackend->install(). That
 * means there must exist a ../data/banklist.txt. If this does not apply to
 * you, you have to change these lines.
 */
$databack = new FileDataBackend();


$importer = new VerifyImport($databack);


/**
 * In this loop you should insert the account IDs with bank IDs.
 *
 * Of course you need to modify the while condition and the
 * sources for $bankID and $accountID.
 */
while ($youHaveMoreAccounts) {                // <- Please change

    $bankID    = $accounts[$i]['bankID'];     // <- Please change
    $accountID = $accounts[$i]['accountID'];  // <- Please change

    /**
     * As a third optional boolean parameter you may specify
     * if the account is valid (TRUE) or not (FALSE). Default
     * is a valid account.
     */
    $importer->import($bankID, $accountID);

}


/**
 * An optional parameter says where to save the file.
 * Default's to ../data/verify.ini
 */
$importer->save();