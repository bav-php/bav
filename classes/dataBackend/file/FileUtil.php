<?php

namespace malkusch\bav;

/**
 * Helper for file system operations.
 *
 * @author Markus Malkusch <markus@malkusch.de>
 * @license GPL
 */
class FileUtil
{

    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @var string
     */
    private $cachedTempDirectory = null;

    /**
     * Inject the configuration.
     */
    public function __construct(Configuration $configuration = null)
    {
        if (is_null($configuration)) {
            $configuration = ConfigurationRegistry::getConfiguration();

        }
        $this->configuration = $configuration;
    }

    /**
     * Returns a writable directory for temporary files
     *
     * @return String
     * @see Configuration::setTempDirectory()
     * @throws NoTempDirectoryException
     */
    public function getTempDirectory()
    {
        if (! is_null($this->configuration->getTempDirectory())) {
            return $this->configuration->getTempDirectory();

        }

        if (is_null($this->cachedTempDirectory)) {
            $this->cachedTempDirectory = $this->findTempDirectory();

        }
        return $this->cachedTempDirectory;
    }

    /**
     * @return string
     * @throws NoTempDirectoryException
     */
    private function findTempDirectory()
    {
        $tmpDirs = array(
            function_exists('sys_get_temp_dir') ? sys_get_temp_dir() : false,
            empty($_ENV['TMP'])    ? false : $_ENV['TMP'],
            empty($_ENV['TMPDIR']) ? false : $_ENV['TMPDIR'],
            empty($_ENV['TEMP'])   ? false : $_ENV['TEMP'],
            ini_get('upload_tmp_dir'),
            '/tmp',
            __DIR__ . "/../../../data/"
        );

        foreach ($tmpDirs as $tmpDir) {
            if ($tmpDir && is_writable($tmpDir)) {
                return realpath($tmpDir);

            }
        }

        $tempfile = tempnam(uniqid(mt_rand(), true), '');
        if (file_exists($tempfile)) {
            unlink($tempfile);
            return realpath(dirname($tempfile));

        }

        throw new NoTempDirectoryException();
    }
}
