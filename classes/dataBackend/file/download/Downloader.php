<?php

namespace malkusch\bav;

/**
 * Downloads an URI
 * 
 * @author Markus Malkusch <markus@malkusch.de>
 * @link bitcoin:1335STSwu9hST4vcMRppEPgENMHD2r1REK Donations
 * @license GPL
 */
class Downloader
{

    /**
     * @var resource
     */
    private $handle;

    /**
     * Initializes the downloader.
     *
     * @throws DownloaderException
     */
    public function __construct()
    {
        $this->handle = curl_init();
        if (! is_resource($this->handle)) {
            throw new DownloaderException("Failed initializing curl");

        }
        curl_setopt($this->handle, CURLOPT_RETURNTRANSFER, true);
    }

    /**
     * Execute the curl call.
     *
     * @throws DownloaderException
     * @return mixed
     */
    private function download($uri)
    {
        curl_setopt($this->handle, CURLOPT_URL, $uri);
        $result = curl_exec($this->handle);

        $curl_info = curl_getinfo($this->handle);
        if ($curl_info['http_code'] >= 400) {
            throw new DownloaderException(
                sprintf(
                    "Failed to download '%s'. HTTP Code: %d",
                    $uri,
                    $curl_info['http_code']
                )
            );
        }
        return $result;
    }

    /**
     * Downloads the content of an URI
     * 
     * @param string $uri URI
     * @return string Content of the page
     * @throws DownloaderException
     */
    public function downloadContent($uri)
    {
        curl_setopt($this->handle, CURLOPT_RETURNTRANSFER, true);

        $content = $this->download($uri);
        if (! $content) {
            throw new DataBackendIOException("Failed to download '$uri'.");

        }
        return $content;
    }

    /**
     * Downloads a file.
     *
     * @param string $uri URI
     * @return string local path to downloaded file.
     * @throws DownloaderException
     */
    public function downloadFile($uri)
    {
        $fileUtil = new FileUtil();
        $file = tempnam($fileUtil->getTempDirectory(), "bavdownload");
        $fp   = fopen($file, 'w');
        if (! ($file && $fp)) {
            throw new DownloaderException("Failed opening a temporary file");

        }
        curl_setopt($this->handle, CURLOPT_FILE, $fp);

        if (! $this->download($uri)) {
            fclose($fp);
            unlink($file);
            throw new DownloaderException(curl_error($this->handle), curl_errno($this->handle));

        }
        return $file;
    }
}
