<?php
/**
 * JSON deserializer of REST request content.
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Webapi\Controller\Rest\Request\Deserializer;

use Magento\Framework\App\State;

class Json implements \Magento\Webapi\Controller\Rest\Request\DeserializerInterface
{
    /**
     * @var \Magento\Core\Helper\Data
     */
    protected $_helper;

    /**
     * @var State
     */
    protected $_appState;

    /**
     * @param \Magento\Core\Helper\Data $helper
     * @param \Magento\Framework\App\State $appState
     */
    public function __construct(\Magento\Core\Helper\Data $helper, State $appState)
    {
        $this->_helper = $helper;
        $this->_appState = $appState;
    }

    /**
     * Parse Request body into array of params.
     *
     * @param string $encodedBody Posted content from request.
     * @return array|null Return NULL if content is invalid.
     * @throws \InvalidArgumentException
     * @throws \Magento\Webapi\Exception If decoding error occurs or in case of empty argument type
     */
    public function deserialize($encodedBody)
    {
        if (!is_string($encodedBody)) {
            throw new \InvalidArgumentException(
                sprintf('"%s" data type is invalid. String is expected.', gettype($encodedBody))
            );
        }
        if (empty($encodedBody)) {
            throw new \Magento\Webapi\Exception(__('Request body should not be empty.'));
        }
        try {
            $decodedBody = $this->_helper->jsonDecode($encodedBody);
        } catch (\Zend_Json_Exception $e) {
            if ($this->_appState->getMode() !== State::MODE_DEVELOPER) {
                throw new \Magento\Webapi\Exception(__('Decoding error.'));
            } else {
                throw new \Magento\Webapi\Exception(
                    __('Decoding error: %1%2%3%4', PHP_EOL, $e->getMessage(), PHP_EOL, $e->getTraceAsString())
                );
            }
        }
        return $decodedBody;
    }
}
