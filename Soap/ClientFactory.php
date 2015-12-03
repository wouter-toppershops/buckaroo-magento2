<?php
/**
 *                  ___________       __            __
 *                  \__    ___/____ _/  |_ _____   |  |
 *                    |    |  /  _ \\   __\\__  \  |  |
 *                    |    | |  |_| ||  |   / __ \_|  |__
 *                    |____|  \____/ |__|  (____  /|____/
 *                                              \/
 *          ___          __                                   __
 *         |   |  ____ _/  |_   ____ _______   ____    ____ _/  |_
 *         |   | /    \\   __\_/ __ \\_  __ \ /    \ _/ __ \\   __\
 *         |   ||   |  \|  |  \  ___/ |  | \/|   |  \\  ___/ |  |
 *         |___||___|  /|__|   \_____>|__|   |___|  / \_____>|__|
 *                  \/                           \/
 *                  ________
 *                 /  _____/_______   ____   __ __ ______
 *                /   \  ___\_  __ \ /  _ \ |  |  \\____ \
 *                \    \_\  \|  | \/|  |_| ||  |  /|  |_| |
 *                 \______  /|__|    \____/ |____/ |   __/
 *                        \/                       |__|
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
 * @copyright   Copyright (c) 2015 Total Internet Group B.V. (http://www.tig.nl)
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 */

namespace TIG\Buckaroo\Soap;

class ClientFactory extends \Magento\Framework\Webapi\Soap\ClientFactory
{
    /**
     * Factory method for \TIG\Buckaroo\Soap\Client\SoapClientWSSEC
     *
     * @param string $wsdl
     * @param array $options
     * @return \TIG\Buckaroo\Soap\Client\SoapClientWSSEC
     */
    public function create($wsdl, array $options = [])
    {
        $client = new \TIG\Buckaroo\Soap\Client\SoapClientWSSEC($wsdl, $options);

        $client->__setLocation('http://testcheckout.buckaroo.nl/soap/Soap.svc');
        $client->loadPem(
            "-----BEGIN CERTIFICATE-----
MIICizCCAfSgAwIBAgIBADANBgkqhkiG9w0BAQUFADCBwjEUMBIGA1UEBhMLTmV0
aGVybGFuZHMxEDAOBgNVBAgTB1V0cmVjaHQxEDAOBgNVBAcTB1V0cmVjaHQxFjAU
BgNVBAoTDUJ1Y2thcm9vIEIuVi4xGjAYBgNVBAsTEVRlY2huaWNhbCBTdXBwb3J0
MS4wLAYDVQQDEyVCdWNrYXJvbyBPbmxpbmUgUGF5bWVudCBTZXJ2aWNlcyBCLlYu
MSIwIAYJKoZIhvcNAQkBFhNzdXBwb3J0QGJ1Y2thcm9vLm5sMB4XDTE1MDgyNTA5
MjMzOFoXDTI1MDgyNTA5MjMzOFowVDEQMA4GA1UEBxMHREUgQklMVDEfMB0GA1UE
ChMWVElHIEJQRSAzIHRlc3QgYWNjb3VudDEfMB0GA1UEAxMWVElHIEJQRSAzIHRl
c3QgYWNjb3VudDCBnzANBgkqhkiG9w0BAQEFAAOBjQAwgYkCgYEAu1ky+LQiDhu5
5+NXvBx/Wj2YSIVD6K2UPn33PyVqDJtECCR535DrVoPlzW2OtH5o2ILLw7Ue91P3
tlDtiGQAmJ2tJBX5WJoDhLp9EW5HLkbbXFNLbZA04Pl9M/IKVz4TRsxIQfC+XVaW
UWFUafb0jy7gLa8McmPqL8WZXCbcko8CAwEAATANBgkqhkiG9w0BAQUFAAOBgQBi
wvzrkR5wMAQbbo+j3AVuJrAP/ijy0e9WquIEKAY+/gc74X2EGSF5lVIsVDyBdHTb
hfLGrJ7dz5TZZ9BZ5psKyqmyrjE4uYi/gw0EXksEuc9O/ySgfhdiV34XtBJU82U/
EWXx5tSIdHVgi5o+70QRKVwFkUK3fUyijSYuCw3kKQ==
-----END CERTIFICATE-----
-----BEGIN RSA PRIVATE KEY-----
MIICXQIBAAKBgQC7WTL4tCIOG7nn41e8HH9aPZhIhUPorZQ+ffc/JWoMm0QIJHnf
kOtWg+XNbY60fmjYgsvDtR73U/e2UO2IZACYna0kFflYmgOEun0RbkcuRttcU0tt
kDTg+X0z8gpXPhNGzEhB8L5dVpZRYVRp9vSPLuAtrwxyY+ovxZlcJtySjwIEAAEA
AQKBgBgaseQtyPPnvVOIfJFHWVtS8XTjMImPS7N+oYEOX0af83DYwJVzH9RRxA9a
OTIf0X2J1o2nkARiWUyrvOP/edPwlguLv3YiGCsLr5QPnt35L/KDLc2R0VaCD2T2
tV3Zs9oEjwsV7vxUrQ/6R3Hfz4Ulb0PNNUSh0YDT/ufgALWBAkEA4LzhRTRz+smp
kqrNyEkgPtFyGDnQ61UvD+nxj2jpjNcpvOf+4lMW5OXWEUBTdV88fqVN5dzbgYOJ
lRrS5uE7swJBANVo2Vlr8LgNQJBsvw7UWg1x//7S7M6u3YdFUrdTkS6C7DpVlfi9
TSI5U74fYJvFv9X8alZE6EdBKibJeEf6r7UCQCF/5jBzrioe71j+fugxBk522AQG
cj7yFq7Pl4NiBxZIaF9RUawY6Ju8KtmtdgNT6+eQ6niuIEZA/jwsoG1r+4MCQQCT
zfBrHHU2JAeJf6e7z5snIMOaa9+TLk1DuOGXEwvEWOzfYhNimUlo4Kd9UCILASTi
QdYsNcFiSGG3R2ZFA/zZAkBJi/1/v388rU592Hkf3jSPQiVhWkE8piqIdNUnHhA5
BdQ2gUgllhq2FyS60SV2rFSIekkBOiwo3g1cTBScHiUt
-----END RSA PRIVATE KEY-----"
        );

        return $client;
    }
}