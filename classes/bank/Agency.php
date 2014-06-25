<?php

namespace malkusch\bav;

/**
 * The agency belongs to one bank. Every bank has one main agency and may have
 * some more agencies in different cities. Don't create this object directly.
 * Use Bank->getMainAgency() or Bank->getAgencies().
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
 *
 * @package classes
 * @subpackage bank
 * @author Markus Malkusch <markus@malkusch.de>
 * @link bitcoin:1335STSwu9hST4vcMRppEPgENMHD2r1REK Donations
 * @copyright Copyright (C) 2006 Markus Malkusch
 */
class Agency
{

    /**
     * @var int
     */
    private $id = 0;

    /**
     * @var Bank
     */
    private $bank;

    /**
     * @var string
     */
    private $bic = '';

    /**
     * @var string
     */
    private $city = '';

    /**
     * @var string
     */
    private $pan = '';

    /**
     * @var string
     */
    private $postcode = '';

    /**
     * @var string
     */
    private $shortTerm = '';

    /**
     * @var string
     */
    private $name = '';

    /**
     * Don't create this object directly. Use Bank->getMainAgency()
     * or Bank->getAgencies().
     *
     * @param int $id
     * @param string $name
     * @param string $shortTerm
     * @param string $city
     * @param string $postcode
     * @param string $bic might be empty
     * @param string $pan might be empty
     */
    public function __construct($id, Bank $bank, $name, $shortTerm, $city, $postcode, $bic = '', $pan = '')
    {
        $this->id           = (int)$id;
        $this->bank         = $bank;
        $this->bic          = $bic;
        $this->postcode     = $postcode;
        $this->city         = $city;
        $this->name         = $name;
        $this->shortTerm    = $shortTerm;
        $this->pan          = $pan;
    }

    /**
     * @return bool
     */
    public function isMainAgency()
    {
        return $this->bank->getMainAgency() === $this;
    }

    /**
     * @return Bank
     */
    public function getBank()
    {
        return $this->bank;
    }

    /**
     * @return int
     */
    public function getID()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getPostcode()
    {
        return $this->postcode;
    }

    /**
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getShortTerm()
    {
        return $this->shortTerm;
    }

    /**
     * @return bool
     */
    public function hasPAN()
    {
        return ! empty($this->pan);
    }

    /**
     * @return bool
     */
    public function hasBIC()
    {
        return ! empty($this->bic);
    }

    /**
     * @throws UndefinedAttributeAgencyException
     * @return string
     */
    public function getPAN()
    {
        if (! $this->hasPAN()) {
            throw new UndefinedAttributeAgencyException($this, 'pan');

        }
        return $this->pan;
    }

    /**
     * @throws UndefinedAttributeAgencyException
     * @return string
     */
    public function getBIC()
    {
        if (! $this->hasBIC()) {
            throw new UndefinedAttributeAgencyException($this, 'bic');

        }
        return $this->bic;
    }
}
