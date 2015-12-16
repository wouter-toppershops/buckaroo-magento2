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
 * @copyright   Copyright (c) 2015 TIG B.V. (http://www.tig.nl)
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 */

namespace TIG\Buckaroo\Controller\Redirect;

class Test extends \Magento\Framework\App\Action\Action
{
    /**
     * Process action
     *
     * @throws \TIG\Buckaroo\Exception
     *
     * @return void
     */
    public function execute()
    {
        echo 'test';exit;
        /** @var \TIG\Buckaroo\Model\Certificate $certificate */
        $certificate = $this->_objectManager->create('TIG\Buckaroo\Model\Certificate');
        $certificate->setCertificate("-----BEGIN CERTIFICATE-----
MIICizCCAfSgAwIBAgIBADANBgkqhkiG9w0BAQUFADCBwjEUMBIGA1UEBhMLTmV0
aGVybGFuZHMxEDAOBgNVBAgTB1V0cmVjaHQxEDAOBgNVBAcTB1V0cmVjaHQxFjAU
BgNVBAoTDUJ1Y2thcm9vIEIuVi4xGjAYBgNVBAsTEVRlY2huaWNhbCBTdXBwb3J0
MS4wLAYDVQQDEyVCdWNrYXJvbyBPbmxpbmUgUGF5bWVudCBTZXJ2aWNlcyBCLlYu
MSIwIAYJKoZIhvcNAQkBFhNzdXBwb3J0QGJ1Y2thcm9vLm5sMB4XDTE1MTIxNDEx
MTc0N1oXDTI1MTIxNDExMTc0N1owVDEQMA4GA1UEBxMHREUgQklMVDEfMB0GA1UE
ChMWVElHIEJQRSAzIHRlc3QgYWNjb3VudDEfMB0GA1UEAxMWVElHIEJQRSAzIHRl
c3QgYWNjb3VudDCBnzANBgkqhkiG9w0BAQEFAAOBjQAwgYkCgYEAym3XovtS7nuy
b+708YWY3K7MRTqClr1l+uIEIvQo+QzrrcFbrcD410ZOtBwd7rPTHVXqfVo3jnib
pxwFIqkqNlqfC18J4DdIS+YHC2ntB/dNj/9aiodnvfWrEi5HuDnNRgOu9HoK+Gmy
SfakS5C21PjXuUa2dw2mvJ5Yz+NVTikCAwEAATANBgkqhkiG9w0BAQUFAAOBgQCW
xkCwG9TcvJq+AggGaX3XxibqNL0gDvrvdw/dc5i9ojx+fUf+dhEaSknQffEt5lPo
2TjpNzxeuWWdiNtpPNehooWpyFNP6aQdzkSfo1LNM/LM+r4UwRC7RHqYOtVzBCXx
swi0UrLJW15kGuDuRv4iVVaPq3vZ/N8OC1FhTPWOfQ==
-----END CERTIFICATE-----
-----BEGIN RSA PRIVATE KEY-----
MIICXgIBAAKBgQDKbdei+1Lue7Jv7vTxhZjcrsxFOoKWvWX64gQi9Cj5DOutwVut
wPjXRk60HB3us9MdVep9WjeOeJunHAUiqSo2Wp8LXwngN0hL5gcLae0H902P/1qK
h2e99asSLke4Oc1GA670egr4abJJ9qRLkLbU+Ne5RrZ3Daa8nljP41VOKQIEAAEA
AQKBgBBLFQ82QW+Wnz8pMaf7A9nHbAOqePZfGkU+SezyUBXzt0iOBq4OmTjinNUc
akBbUwPKdYxPZadfB9BEjhlDGnWO4CpjLHh8s7GnzTlek7fGK9LGVhRu/FGC28j5
cSbRCMXE+X9Ap7TvclKkxbkq/YIFc14whewbhRkzEqhBbN9tAkEA6I/O9/hzAe/M
NXHUzWSiSqJ5k102gRBnBc1iapYkzHzm3KMT8PqWaU0OJpFHWYEYMcWEicyhw/n5
8+17wTBeGwJBAN7UmRR5vFqPXqouwalwEPHw+X1e4uVzBs3U3LFkNIpOiXvRGGMQ
/C0tRfN75PnJH/oCc3HtiheUwFPxvje7+QsCQQDYmpi6fL0hYKdiX9NEOiauPQXf
K0JIk25hCRpRC+baTrr3ZSx9lefhy9MSON2rj4FpWf5IGj/QuFMFznslRFdhAkEA
sYt/dTsSAq4dZUff8ptiRQQWJfiGnP+rujESrx0CZ/jvvoH6BmUwKObbx4c+CHBi
VBfD2FDGKMfS/o+tWkchxQJATwQs3Lr03M90mmLfPrxuBnW9X4SkouwR4uLu39bc
CyMRVVkhQFkFOdhZV8NGANMOetaDLrIWUTOebVfVxNqcvw==
-----END RSA PRIVATE KEY-----");
        $certificate->setName('test');
        $certificate->save();
    }
}
