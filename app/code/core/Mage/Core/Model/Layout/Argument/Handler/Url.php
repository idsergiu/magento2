<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_Core
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Layout argument. Type url
 *
 * @category    Mage
 * @package     Mage_Core
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Core_Model_Layout_Argument_Handler_Url extends Mage_Core_Model_Layout_Argument_HandlerAbstract
{
    /**
     * @var Mage_Core_Model_Url
     */
    protected $_urlModel;

    public function __construct(array $args = array())
    {
        parent::__construct($args);
        if (!isset($args['urlModel'])) {
            throw new InvalidArgumentException('Required url model is missing');
        }
        $this->_urlModel = $args['urlModel'];
        if (false === ($this->_urlModel instanceof Mage_Core_Model_Url)) {
            throw new InvalidArgumentException('Wrong url model passed');
        }
    }

    /**
     * Generate url
     * @param string $value
     * @throws InvalidArgumentException
     * @return Mage_Core_Model_Abstract|boolean
     */
    public function process($value)
    {
        if (true === is_string($value)) {
            $value = array($value => array());
        }

        if (false === is_array($value)) {
            throw new InvalidArgumentException('Passed value has incorrect format');
        }

        reset($value);
        $path = key($value);
        $params = $value[$path];

        $url = $this->_urlModel->getUrl($path, $params);

        return $url;
    }
}
