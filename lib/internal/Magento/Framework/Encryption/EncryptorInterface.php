<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Framework\Encryption;

/**
 * Encryptor interface
 */
interface EncryptorInterface
{
    /**
     * Generate a [salted] hash.
     *
     * $salt can be:
     * false - salt is not used
     * true - random salt of the default length will be generated
     * integer - random salt of specified length will be generated
     * string - actual salt value to be used
     *
     * @param string $password
     * @param bool|int|string $salt
     * @return string
     */
    public function getHash($password, $salt = false);

    /**
     * Hash a string
     *
     * @param string $data
     * @return string
     */
    public function hash($data);

    /**
     * Validate hash against hashing method (with or without salt)
     *
     * @param string $password
     * @param string $hash
     * @return bool
     * @throws \Exception
     */
    public function validateHash($password, $hash);

    /**
     * Encrypt a string
     *
     * @param string $data
     * @return string
     */
    public function encrypt($data);

    /**
     * Decrypt a string
     *
     * @param string $data
     * @return string
     */
    public function decrypt($data);

    /**
     * Return crypt model, instantiate if it is empty
     *
     * @param string $key
     * @return \Magento\Framework\Encryption\Crypt
     */
    public function validateKey($key);
}
