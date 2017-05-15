<?php
/**
 *
 *          ..::..
 *     ..::::::::::::..
 *   ::'''''':''::'''''::
 *   ::..  ..:  :  ....::
 *   ::::  :::  :  :   ::
 *   ::::  :::  :  ''' ::
 *   ::::..:::..::.....::
 *     ''::::::::::::''
 *          ''::''
 *
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Creative Commons License.
 * It is available through the world-wide-web at this URL:
 * http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 * If you are unable to obtain it through the world-wide-web, please send an email
 * to servicedesk@tig.nl so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future. If you wish to customize this module for your
 * needs please contact servicedesk@tig.nl for more information.
 *
 * @copyright   Copyright (c) Total Internet Group B.V. https://tig.nl/copyright
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 */
namespace TIG\Buckaroo\Service\Formatter\Address;

class PhoneFormatter
{
    private $validMobile = [
        'NL' => ['00316'],
        'BE' => ['003246', '003247', '003248', '003249'],
    ];

    private $invalidNotation = [
        'NL' => ['00310', '0310', '310', '31'],
        'BE' => ['00320', '0320', '320', '32'],
    ];

    /**
     * @param $phoneNumber
     * @param $country
     *
     * @return array
     */
    public function format($phoneNumber, $country)
    {
        $return = ["orginal" => $phoneNumber, "clean" => false, "mobile" => false, "valid" => false];

        $match = preg_replace('/[^0-9]/Uis', '', $phoneNumber);
        if ($match) {
            $phoneNumber = $match;
        }

        $return['clean'] = $this->formatPhoneNumber($phoneNumber, $country);
        $return['mobile'] = $this->isMobileNumber($return['clean'], $country);

        if (strlen((string)$return['clean']) == 13) {
            $return['valid'] = true;
        }

        return $return;
    }

    /**
     * @param $phoneNumber
     * @param $country
     *
     * @return string
     */
    private function formatPhoneNumber($phoneNumber, $country)
    {
        $phoneLength = strlen((string)$phoneNumber);

        if ($phoneLength > 10 && $phoneLength != 13) {
            $phoneNumber = $this->isValidNotation($phoneNumber, $country);
        }

        if ($phoneLength == 10) {
            $phoneNumber = '0031' . substr($phoneNumber, 1);
        }

        return $phoneNumber;
    }

    /**
     * @param $phoneNumber
     * @param $country
     *
     * @return bool
     */
    private function isMobileNumber($phoneNumber, $country)
    {
        $isMobile = false;

        array_walk(
            $this->validMobile[$country],
            function ($value) use (&$isMobile, $phoneNumber) {
                $phoneNumberPart = substr($phoneNumber, 0, strlen($value));
                $phoneNumberHasValue = strpos($phoneNumberPart, $value);

                if ($phoneNumberHasValue !== false) {
                    $isMobile = true;
                }
            }
        );

        return $isMobile;
    }

    /**
     * @param $phoneNumber
     * @param $country
     *
     * @return string
     */
    private function isValidNotation($phoneNumber, $country)
    {
        array_walk(
            $this->invalidNotation[$country],
            function ($invalid) use (&$phoneNumber) {
                $phoneNumberPart = substr($phoneNumber, 0, strlen($invalid));

                if (strpos($phoneNumberPart, $invalid) !== false) {
                    $phoneNumber = $this->formatNotation($phoneNumber, $invalid);
                }
            }
        );

        return $phoneNumber;
    }

    /**
     * @param $phoneNumber
     * @param $invalid
     *
     * @return string
     */
    private function formatNotation($phoneNumber, $invalid)
    {
        $valid = substr($invalid, 0, -1);

        if (substr($valid, 0, 2) == '31') {
            $valid = "00" . $valid;
        }

        if (substr($valid, 0, 2) == '03') {
            $valid = "0" . $valid;
        }

        if ($valid == '3') {
            $valid = "0" . $valid . "1";
        }

        $phoneNumber = substr_replace($phoneNumber, $valid, 0, strlen($invalid));

        return $phoneNumber;
    }
}
