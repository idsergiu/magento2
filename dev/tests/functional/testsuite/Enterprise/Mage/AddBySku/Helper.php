<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Mage_AddBySku
 * @subpackage  functional_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Helper class
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Enterprise_Mage_AddBySku_Helper extends Mage_Selenium_AbstractHelper
{
    /**
     * Add next product
     *
     * @param array $nextProduct
     * @param bool $pressButton
     */
    public function addProductShoppingCart(array $nextProduct, $pressButton = true)
    {
        if (!empty($nextProduct)) {
            $identificator = 0;
            foreach ($nextProduct as $value) {
                if ($identificator > 0) {
                    $this->clickButton('add_sku', false);
                    $this->waitForAjax();
                }
                $this->addParameter('itemId', $identificator);
                if (is_array($value)) {
                    $this->fillFieldset($value, 'add_to_shopping_cart');
                } else {
                    $this->fail('Got incorrect parameter');
                }
                $identificator++;
            }
            if ($pressButton) {
                $this->clickButton('add_selected_products_to_shopping_cart', false);
                $this->waitForAjax();
            }
        }
        $this->pleaseWait();
    }

    public function openShoppingCart()
    {
        $this->clickButton('manage_shopping_cart', false);
        $this->waitForPageToLoad();
        $this->addParameter('store', $this->defineParameterFromUrl('store'));
        $this->addParameter('customer', $this->defineParameterFromUrl('customer'));
        $this->validatePage('customer_shopping_cart');
    }

    public function getProductInfoInTable(
        $tableHeadName = 'product_table_head',
        $productTableLine = 'product_line',
        $skipFields = array('move_to_wishlist','remove')
    ) {
        $productValues = array();
        $tableRowNames = $this->shoppingCartHelper()->getColumnNamesAndNumbers($tableHeadName);
        $productLine = $this->_getControlXpath('pageelement', $productTableLine);

        $productCount = count($this->getElements($productLine));
        for ($index = 1; $index <= $productCount; $index++) {
            foreach ($tableRowNames as $key => $value) {
                if (in_array($key, $skipFields)) {
                    continue;
                }
                $xpathValue = $productLine . "[$index]//td[$value]";
                if ($key == 'qty') {
                    $productValues['product_' . $index][$key] = $this->getElement($xpathValue . '/input')->value();
                } else {
                    $productValues = $this->_defineProductValues($productValues, $xpathValue, $index, $key);
                }
            }
        }

        $productValues = $this->_defineSkuValues($productValues);

        return $productValues;
    }

    /**
     * Defines product values without qty
     *
     * @param $productValues
     * @param $xpathValue
     * @param $index
     * @param $key
     * @return mixed
     */
    protected function _defineProductValues($productValues, $xpathValue, $index, $key)
    {
        $text = $this->getElement($xpathValue)->attribute('text');
        if (preg_match('/Excl. Tax/', $text)) {
            $productValues = $this->_defineExclTax($productValues, $text, $index, $key);
        } elseif (preg_match('/Ordered/', $text)) {
            $values = explode(' ', $text);
            $values = array_map('trim', $values);
            foreach ($values as $k => $v) {
                if ($k % 2 != 0 && isset($values[$k - 1])) {
                    $productValues['product_' . $index][
                    $key . '_' . strtolower(preg_replace('#[^0-9a-z]+#i', '', $values[$k - 1]))] = $v;
                }
            }
        } else {
            $productValues['product_' . $index][$key] = trim($text);
        }

        return $productValues;
    }

    protected function _defineExclTax($productValues, $text, $index, $key)
    {
        $text = preg_replace("/ \\n/", ':', $text);
        $values = explode(':', $text);
        $values = array_map('trim', $values);
        foreach ($values as $k => $v) {
            if ($v == 'Excl. Tax' && isset($values[$k + 1])) {
                $productValues['product_' . $index][$key . '_excl_tax'] = $values[$k + 1];
            }
            if ($v == 'Incl. Tax' && isset($values[$k + 1])) {
                $productValues['product_' . $index][$key . '_incl_tax'] = $values[$k + 1];
            }
        }

        return $productValues;
    }

    /**
     * Returns sku values
     *
     * @param $productValues
     * @return mixed
     */
    protected function _defineSkuValues($productValues)
    {
        foreach ($productValues as &$productData) {
            $productData = array_diff($productData, array(''));
            foreach ($productData as &$fieldValue) {
                if (preg_match('/([\d]+\.[\d]+)|([\d]+)/', $fieldValue)) {
                    preg_match_all('/^([\D]+)?(([\d]+\.[\d]+)|([\d]+))(\%)?/', $fieldValue, $price);
                    $fieldValue = $price[0][0];
                }
                if (preg_match('/SKU:/', $fieldValue)) {
                    $skuArr = explode('SKU:', $fieldValue);
                    $sku = end($skuArr);
                    $productData['sku'] = $sku;
                    $fieldValue = substr($fieldValue, 0, strpos($fieldValue, ':') - 3);
                }
            }
        }

        return $productValues;
    }

    public function removeAllItemsFromErrorTable()
    {
        if ($this->controlIsVisible('button', 'remove_all')) {
            $this->clickButton('remove_all', false);
            $this->waitForAjax();
        }
    }

    public function removeSingleItemsFromErrorTable($itemIndex)
    {
        $this->addParameter('del_row', $itemIndex);
        $this->clickButton('remove_button', false);
        $this->waitForAjax();
    }

    public function getProductInfoInErrorTable()
    {
        $productValues = $this->getProductInfoInTable('product_table_head_error', 'product_line_error');
        for ($index = 1; $index <= count($productValues); $index++) {
            $count = 'product_' . $index;
            unset($productValues[$count]['product_name']);
            $productValues[$count]['sku'] = str_replace('SKU not found in catalog.', '', $productValues[$count]['sku']);
        }
        return $productValues;
    }

    /**
     * Verify presence of products in grid
     */
    public function verifyProductPresentInGrid($sku, $table)
    {
        if (is_string($sku)) {
            $this->addParameter('skuProduct', $sku);
            $this->assertTrue($this->controlIsPresent('pageelement', $table),
                "There is no product with: $sku in $table grid");
        }
        if (is_array($sku)) {
            foreach ($sku as $value) {
                $this->addParameter('skuProduct', $value);
                if ($this->controlIsPresent('pageelement', $table)) {
                } else {
                    $this->addVerificationMessage("There is no product with: $value in $table grid");
                }
            }
        }
        $this->assertEmptyVerificationErrors();
    }

    /**
     * Verify absence of product in grid
     */
    public function verifyProductAbsentInGrid($sku, array $tables)
    {
        foreach ($tables as $table) {
            $this->addParameter('skuProduct', $sku);
            $this->assertFalse($this->controlIsPresent('pageelement', $table),
                "Product with SKU: $sku is present in $table");
            if (is_array($sku)) {
                foreach ($sku as $value) {
                    $this->addParameter('skuProduct', $value);
                    if ($this->controlIsPresent('pageelement', $table)) {
                        $this->addVerificationMessage("Product with SKU: $value is present in $table");
                    }
                }
                $this->assertEmptyVerificationErrors();
            }
        }
    }

    public function getRowCount($type, $name, $paramName)
    {
        $gridRow = $this->_getControlXpath($type, $name);
        $rowCount = count($this->getElements($gridRow));
        $this->addParameter($paramName, $rowCount);
    }

    public function clearShoppingCartAndErrorTable()
    {
        //        $this->addBySkuHelper()->clearShoppingCart();
        $this->addBySkuHelper()->removeAllItemsFromErrorTable();
    }

    public function isShoppingCartEmpty()
    {
        if ($this->controlIsVisible('pageelement', 'product_table_head_error')) {
            $productValues = $this->getProductInfoInTable('product_table_head_error', 'product_line_error');
            if (empty($productValues)) {
                return true;
            }
        }
        return false;
    }

    public function verifyErrorTableIsEmpty()
    {
        $this->assertFalse($this->controlIsVisible('fieldset', 'shopping_cart_error'),
            'Products are not deleted from attention grid');
    }

    //---------------------------------------------------Frontend-------------------------------------------------------
    /**
     * Fulfill product SKU and qty fields to the according row
     *
     * @param array $data
     * @param array $rows, default value = 1
     */
    public function frontFulfillSkuQtyRows(array $data, $rows = array('1'))
    {
        foreach ($rows as $row) {
            $this->addParameter('number', $row);
            $this->fillFieldset($data, 'add_by_sku');
        }
    }

    /**
     * Delete item  is specified row
     *
     * @param array $rows, default value = 1
     */
    public function frontDeleteItems($rows = array('1'))
    {
        foreach ($rows as $row) {
            $this->addParameter('row_number', $row);
            $this->clickControl('link', 'remove_item');
            $this->assertMessagePresent('success', 'item_removed');
        }
    }

    /**
     * Verifying sku, price and qty fields for added product in Required attention grid
     *
     * @param array $product
     * @param bool $priceIsVisible
     * @param bool $qtyIsEnabled
     */
    public function frontCheckFields(array $product, $priceIsVisible, $qtyIsEnabled)
    {
        $this->addParameter('sku', $product['sku']);
        $this->assertTrue($this->controlIsVisible('pageelement', 'sku'),
            'Product is not added to required attention grid. ');
        if ($priceIsVisible) {
            $this->assertTrue($this->controlIsVisible('pageelement', 'price'),
                'Unit price is not available for added product. ');
        } else {
            $this->assertFalse($this->controlIsVisible('pageelement', 'price'),
                'Unit price is available for added product. ');
        }
        $this->assertEquals($this->getControlAttribute('field', 'qty', 'value'), $product['qty'],
            'Entered qty is not correspond to added qty');
        if ($qtyIsEnabled) {
            $this->assertTrue($this->getControlElement('field', 'qty')->enabled(), 'Qty field is disabled. ');
        } else {
            $this->assertFalse($this->getControlElement('field', 'qty')->enabled(),
                'Qty field available for editing. ');
        }
    }

    /**
     * Click the "Specify the product's options" link
     *
     * @param string $productName
     */
    public function clickSpecifyLink($productName)
    {
        $this->clickControl('link', 'specify_product', false);
        $this->waitForPageToLoad();
        $this->addParameter('id', $this->defineParameterFromUrl('id'));
        $this->addParameter('qty', $this->defineParameterFromUrl('qty'));
        $this->addParameter('sku', $this->defineParameterFromUrl('sku'));
        if (is_string($productName)) {
            $this->addParameter('productName', $productName);
        }
        $this->validatePage();
    }

    /**
     * Open Shopping Cart and clear all products in Required attention grid
     */
    public function frontClearRequiredAttentionGrid()
    {
        if ($this->getArea() == 'frontend') {
            $this->frontend('shopping_cart');
            if ($this->controlIsPresent('fieldset', 'products_requiring_attention')) {
                $this->clickButton('remove_all');
                $this->assertMessagePresent('success', 'items_removed');
            }
        }
    }

    /**
     * Configure product in Required Attention grid and add it to Shopping cart if possible
     *
     * @param array $product
     * @param string $productType
     * @param array $msgShoppingCart
     * @param array $msgAttentionGrid
     */
    public function frontConfigureProduct(array $product, $productType, array $msgShoppingCart, array $msgAttentionGrid)
    {
        if ($msgAttentionGrid['messageOne'] != 'null') {
            $this->assertMessagePresent($msgShoppingCart['type'], $msgAttentionGrid['messageOne']);
            switch ($msgAttentionGrid['messageOne']) {
                case 'qty_not_available':
                case 'requested_qty':
                    $this->frontCheckFields($product, true, true);
                    if ($productType == 'simpleMin') {
                        $qty = $product['qty'] + 1;
                    } else {
                        $qty = $product['qty'] - 1;
                    }
                    $this->addParameter('qty', $qty);
                    $this->assertMessagePresent($msgShoppingCart['type'], $msgAttentionGrid['messageTwo']);
                    $this->fillFieldset(array('qty' => $qty), 'products_requiring_attention');
                    $this->clickButton('add_to_cart');
                    break;
                case 'specify_option':
                    if (strlen(strstr($productType, 'grouped')) > 0) {
                        $this->frontCheckFields($product, false, false);
                    } else {
                        $this->frontCheckFields($product, true, true);
                    }
                    $this->clickSpecifyLink($product['product_name']);
                    $this->productHelper()->frontFillBuyInfo($product['Options']);
                    $this->clickButton('update_cart');
                    //Verifying
                    if (strlen(strstr($productType, 'grouped')) > 0) {
                        $this->addParameter('productName',
                            $product['Options']['option_1']['parameters']['subproductName']);
                    }
                    break;
                default:
                    $this->frontCheckFields($product, false, false);
                    break;
            }
        } else {
            if ($productType == 'simpleNotVisible') {
                $this->addParameter('productName', $product['product_name']);
            }
        }
    }
}
