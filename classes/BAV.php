<?php 





/**
 * BAV is the super class of the Bank Account Validator project.
 * Every class will inherit this. The main purpose of this class is
 * an implementation of a namespace and set some configuration like
 * the project's encoding.
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
 * @author Markus Malkusch <markus@malkusch.de>
 * @copyright Copyright (C) 2006 Markus Malkusch
 */
abstract class BAV {


    static protected
    /**
     * @var BAV_Encoding
     */
    $encoding;


    static public function classConstructor() {
        try {
            self::setEncoding('UTF-8');
            
        } catch (BAV_EncodingException_Unsupported $e) {
            self::setEncoding('ISO-8859-15');
        
        }
    }
    /**
     * If you want to use another encoding
     *
     * @throws BAV_EncodingException_Unsupported
     * @param mixed $encoding
     * @see BAV_Encoding
     */
    static public function setEncoding($encoding) {
        self::$encoding = ($encoding instanceof BAV_Encoding)
                        ? $encoding
                        : BAV_Encoding::getInstance($encoding);
    }
    /**
     * @return BAV_Version version of BAV
     */
    static public function get_bav_version() {
        return new BAV_Version('0.28');
    }
    /**
     * Returns the version of the API. Note that different BAV versions
     * may have the same API version.
     *
     * @return BAV_Version version of BAV's API
     */
    static public function get_bav_api_version() {
        return new BAV_Version('2.4');
    }


}
