<?php

namespace malkusch\bav;

/**
 * Configuration
 * 
 * @author Markus Malkusch <markus@malkusch.de>
 * @license GPL
 */
class Configuration
{

    /**
     * @var bool
     */
    private $automaticInstallation = null;

    /**
     * @var BAV_Encoding
     */
    private $encoding;

    /**
     * @var DataBackendContainer
     */
    private $backendContainer;

    /**
     * Turns automatic installation on or off.
     * 
     * If automatic installation is activated. The backend factory will check if it is
     * installed and if not so install the backend.
     * 
     * @see BAV_DataBackend::install()
     * @param bool $automaticInstallation Set true to turn installation on
     */
    public function setAutomaticInstallation($automaticInstallation)
    {
        $this->automaticInstallation = $automaticInstallation;
    }

    /**
     * Returns true if automatic installation is activated.
     *
     * @return bool
     */
    public function isAutomaticInstallation()
    {
        return $this->automaticInstallation;
    }

    /**
     * Sets the data backend container.
     */
    public function setDataBackendContainer(DataBackendContainer $backendContainer)
    {
        $this->backendContainer = $backendContainer;
    }

    /**
     * Returns the data backend factory.
     *
     * @return DataBackendContainer
     */
    public function getDataBackendContainer()
    {
        return $this->backendContainer;
    }
    
    /**
     * Sets the encoding.
     */
    public function setEncoding(\BAV_Encoding $encoding)
    {
        $this->encoding = $encoding;
    }

    /**
     * Returns the encoding.
     *
     * @return BAV_Encoding
     */
    public function getEncoding()
    {
        return $this->encoding;
    }
}
