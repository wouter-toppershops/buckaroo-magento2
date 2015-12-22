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

namespace TIG\Buckaroo\Debug;

class Debugger
{
    protected $logger = null;
    protected $objectManager = null;
    protected $configProviderFactory = null;

    protected $channelName = 'TIG_Buckaroo';
    protected $defaultFilename = 'TIG_Buckaroo.log';

    protected $mailTo = [
        'robin.de.graaf@tig.nl',
        'voh@hostvoh.net',
    ];
    protected $mailSubject = 'TIG_Buckaroo log mail';
    protected $mailFrom = 'info@buckaroo.nl';

    protected $mode = 'mail';

    /**
     * @param Logger                                        $logger
     * @param \Magento\Framework\ObjectManagerInterface     $objectManager
     * @param \TIG\Buckaroo\Model\ConfigProvider\Factory    $configProviderFactory
     */
    public function __construct(
        \TIG\Buckaroo\Debug\Logger $logger,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \TIG\Buckaroo\Model\ConfigProvider\Factory $configProviderFactory
    ) {
        $this->logger = $logger;
        $this->objectManager = $objectManager;
        $this->configProviderFactory = $configProviderFactory;

        // Get some settings
        /** @var \TIG\Buckaroo\Model\ConfigProvider\Account $config */
        $config = $this->configProviderFactory->get('account');
        if ($config->getDebugEmail()) {
            $mailTo = $config->getDebugEmail();
            if (strpos($mailTo, ',') !== false) {
                $mailTo = expode(',', $mailTo);
            } else {
                $mailTo = [$mailTo];
            }
            $this->mailTo = $mailTo;
        }
    }

    /**
     * Return the default filename
     *
     * @return string
     */
    public function getDefaultFilename()
    {
        return $this->defaultFilename;
    }

    /**
     * Set the default filename
     *
     * @param $defaultFilename
     *
     * @return $this
     */
    public function setDefaultFilename($defaultFilename)
    {
        $this->defaultFilename = $defaultFilename;
        return $this;
    }

    /**
     * Return the channel name
     *
     * @return string
     */
    public function getChannelName()
    {
        return $this->channelName;
    }

    /**
     * Set the channel name
     *
     * @param $channelName
     *
     * @return $this
     */
    public function setChannelName($channelName)
    {
        $this->channelName = $channelName;
        return $this;
    }

    /**
     * Return mode
     *
     * @return string
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * Set mode
     *
     * @param $mode
     */
    public function setMode($mode)
    {
        $this->mode = $mode;
    }

    /**
     * Log message
     *
     * @param      $message
     * @param int  $level
     * @param null $filename
     * @param bool $force
     *
     * @return $this
     */
    public function log($message, $level = 100, $filename = null, $force = false)
    {
        /**
         * If filename isn't set, use the default filename that's currently set
         */
        if (!$filename) {
            $filename = $this->getDefaultFilename();
        }
        /**
         * If message is an array, print_r it to a string
         */
        if (is_array($message)) {
            $message = print_r($message, true);
        }

        /**
         * Monolog requires handlers, so we need to check which ones we need to push
         */
        if (strpos($this->mode, 'log') !== false) {
            /** Stream handler handles local file logging capabilities */
            $this->logger->pushHandler($this->createStreamHandler($filename));
        }
        if (strpos($this->mode, 'mail') !== false) {
            /** Mail handler handles sending logs to configured e-mail addresses */
            $this->logger->pushHandler($this->createMailHandler());
        }

        /**
         * Set the name we're supposed to set, push the streamHandler & add the record
         */
        $this->logger->setName($this->getChannelName());
        $this->logger->addRecord($level, $message, []);

        return $this;
    }

    /**
     * Creates a new streamHandler
     *
     * @return mixed
     */
    public function createStreamHandler($filename = 'var/log/TIG_Buckaroo.log')
    {
        $streamHandler = $this->objectManager->create(
            '\Monolog\Handler\StreamHandler',
            [
                'stream' => 'var/log/' . $filename,
            ]
        );
        return $streamHandler;
    }

    /**
     * Creates a new mailHandler
     *
     * @return mixed
     */
    public function createMailHandler()
    {
        $mailHandler = $this->objectManager->create(
            '\Monolog\Handler\NativeMailerHandler',
            [
                'to'        => $this->mailTo,
                'subject'   => $this->mailSubject,
                'from'      => $this->mailFrom,
            ]
        );
        return $mailHandler;
    }

}