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

    /**
     * @var null|Logger
     */
    protected $logger = null;

    /**
     * @var \Magento\Framework\ObjectManagerInterface|null
     */
    protected $objectManager = null;

    /**
     * @var null|\TIG\Buckaroo\Model\ConfigProvider\Factory
     */
    protected $configProviderFactory = null;

    /**
     * @var string
     */
    protected $channelName = 'TIG_Buckaroo';

    /**
     * @var string
     */
    protected $defaultFilename = 'TIG_Buckaroo.log';

    /**
     * @var array
     */
    protected $message = [];

    /**
     * @var array
     */
    protected $mailTo = [];

    /**
     * @var string
     */
    protected $mailSubject = 'TIG_Buckaroo log mail';

    /**
     * @var string
     */
    protected $mailFrom = 'nobody@buckaroo.nl';

    /**
     * @var string
     */
    protected $mode = 'log';

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

        /**
         * Get some settings
         */
        /** @var \TIG\Buckaroo\Model\ConfigProvider\Account $config */
        $config = $this->configProviderFactory->get('account');

        /**
         * Get the mode currently set in config and set it
         */
        $this->setMode($config->getDebugMode());

        /**
         * If debug emails are set, add them to $this->mailTo
         */
        if ($config->getDebugEmail()) {
            $mailTo = $config->getDebugEmail();
            /**
             * If it's a comma-separated list, split
             */
            if (strpos($mailTo, ',') !== false) {
                $mailTo = explode(',', $mailTo);
            } else {
                $mailTo = [$mailTo];
            }
            $this->setMailTo($mailTo);
        }
    }

    /**
     * When the system exits before ->log() is explicitly called, attempt to log anyway
     */
    public function __destruct()
    {
        $this->log();
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
    public function log($message = null, $level = 100, $filename = null, $force = false)
    {
        /**
         * If message not given, see if $this->message is filled
         */
        if (!$message) {
            $message = $this->getMessageAsString();
        }
        /**
         * If message is still null, there's nothing to log at all
         */
        if (!$message) {
            return false;
        }

        /**
         * If filename isn't set, use the default filename that's currently set
         */
        if (!$filename) {
            $filename = $this->getDefaultFilename();
        }

        /**
         * If message is an array, print_r it to a string
         */
        if (is_array($message) || is_object($message)) {
            if (is_object($message) && method_exists($message, 'debug')) {
                $message = $message->debug();
            }
            $message = print_r($message, true);
        }

        /**
         * Set the name we're supposed to set, push the streamHandler & add the record
         */
        $this->logger->setName($this->getChannelName());

        /**
         * Monolog requires handlers, so we need to check which ones we need to push
         */
        if (strpos($this->getMode(), 'log') !== false) {
            /** Stream handler handles local file logging capabilities */
            $this->logger->pushHandler($this->createStreamHandler($filename));
            $this->logger->addRecord($level, $message, []);
        }
        /**
         * @todo Monolog's NativeMailHandler seems broken, so we use mail() in the meantime
         */
        if (strpos($this->mode, 'mail') !== false) {
            /** Mail handler handles sending logs to configured e-mail addresses */
            $headers =  'From: ' . $this->getMailFrom() . "\r\n" .
                        'Reply-To: ' . $this->getMailFrom() . "\r\n" .
                        'X-Mailer: PHP/' . phpversion();
            foreach ($this->getMailTo() as $mailTo) {
                mail($mailTo, $this->getMailSubject(), $message, $headers);
            }

        }

        /**
         * And reset the message, otherwise our __destruct will try it again
         */
        $this->resetMessage();

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

    /**
     * Reset the message array
     *
     * @return $this
     */
    public function resetMessage()
    {
        $this->message = [];
        return $this;
    }

    /**
     * Add $message to the message array, and cast to string if an array or object
     *
     * @param $message
     *
     * @return $this
     */
    public function addToMessage($message)
    {
        if (is_array($message) || is_object($message)) {
            $message = print_r($message, true);
        }
        $this->message[] = $message;
        return $this;
    }

    /**
     * Return the message array
     *
     * @return array
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Return the message array as imploded string
     *
     * @return string|null
     */
    public function getMessageAsString()
    {
        if (count($this->getMessage()) == 0) {
            return null;
        }
        return implode(PHP_EOL, $this->getMessage());
    }

    /**
     * Return mailTo array
     *
     * @return array
     */
    public function getMailTo()
    {
        return $this->mailTo;
    }

    /**
     * Set mailTo array
     *
     * @param array $mailTo
     *
     * @return $this
     */
    public function setMailTo($mailTo)
    {
        if (!is_array($mailTo)) {
            $mailTo = [$mailTo];
        }
        $this->mailTo = $mailTo;
        return $this;
    }

    /**
     * Add to mailTo array
     *
     * @param string $address
     *
     * @return $this
     */
    public function addMailTo($address)
    {
        if (!is_array($address)) {
            $this->mailTo[] = $address;
        }
        return $this;
    }

    /**
     * Return mail subject
     *
     * @return string
     */
    public function getMailSubject()
    {
        return $this->mailSubject;
    }

    /**
     * Set mail subject
     *
     * @param string $mailSubject
     *
     * @return $this
     */
    public function setMailSubject($mailSubject)
    {
        $this->mailSubject = $mailSubject;
        return $this;
    }

    /**
     * Return mail from address
     *
     * @return string
     */
    public function getMailFrom()
    {
        return $this->mailFrom;
    }

    /**
     * Set mail from address
     *
     * @param string $mailFrom
     *
     * @return $this
     */
    public function setMailFrom($mailFrom)
    {
        $this->mailFrom = $mailFrom;
        return $this;
    }

}