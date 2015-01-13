<?php

namespace malkusch\bav;

/**
 * PDO factory
 *
 * @license WTFPL
 * @author Markus Malkusch <markus@malkusch.de>
 * @see makePDO()
 */
class PDOFactory
{

    /**
     * Environment variable for a Data Source Name
     */
    const ENV_DSN = "PDO_DSN";

    /**
     * Environment variable for the database user
     */
    const ENV_USER = "PDO_USER";

    /**
     * Environment variable for the database password
     */
    const ENV_PASSWORD = "PDO_PASSWORD";

    /**
     * Builds a PDO.
     *
     * If the environment doesn't provide the dsn, user and password
     * it uses the default mysql test database.
     *
     * @return \PDO
     */
    public static function makePDO()
    {
        $dsn = getenv(self::ENV_DSN);
        if (! $dsn) {
            $dsn = "mysql:host=localhost;dbname=test";

        }

        $user = getenv(self::ENV_USER);
        if (! $user) {
            $user = "test";

        }

        $password = getenv(self::ENV_PASSWORD);
        if (! $password) {
            $password = null;

        }

        $pdo = new \PDO($dsn, $user, $password);
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        return $pdo;
    }
}
