<?php
/**
 * Review Mysql4 resource model
 *
 * @package     Mage
 * @subpackage  Review
 * @copyright   Varien (c) 2007 (http://www.varien.com)
 * @license     http://www.opensource.org/licenses/osl-3.0.php
 * @author      Dmitriy Soroka <dmitriy@varien.com>
 * @author      Alexander Stadnitski <alexander@varien.com>
 */
class Mage_Review_Model_Mysql4_Review
{
    protected $_reviewTable;
    protected $_reviewDetailTable;
    protected $_reviewStatusTable;
    protected $_reviewEntityTable;

    /**
     * Read connection
     *
     * @var Zend_Db_Adapter_Abstract
     */
    protected $_read;

    /**
     * Write connection
     *
     * @var Zend_Db_Adapter_Abstract
     */
    protected $_write;

    public function __construct()
    {
        $resources = Mage::getSingleton('core/resource');

        $this->_reviewTable         = $resources->getTableName('review/review');
        $this->_reviewDetailTable   = $resources->getTableName('review/review_detail');
        $this->_reviewStatusTable   = $resources->getTableName('review/review_status');
        $this->_reviewEntityTable   = $resources->getTableName('review/review_entity');

        $this->_read    = $resources->getConnection('review_read');
        $this->_write   = $resources->getConnection('review_write');
    }

    public function load($reviewId)
    {
        $select = $this->_read->select();
        $select->from($this->_reviewTable)
            ->join($this->_reviewDetailTable, "{$this->_reviewTable}.review_id = {$this->_reviewDetailTable}.review_id")
            ->where("{$this->_reviewTable}.review_id = ?", $reviewId);

        return $this->_read->fetchRow($select);
    }

    public function save(Mage_Review_Model_Review $review)
    {
        $this->_write->beginTransaction();
        try {
            if ($review->getId()) {
                $data = $this->_prepareUpdateData($review);
                $condition = $this->_write->quoteInto('review_id = ?', $review->getId());

                $this->_write->update($this->_reviewTable, $data['base'], $condition);
                $this->_write->update($this->_reviewDetailTable, $data['detail'], $condition);
            }
            else {
                $data = $this->_prepareInsertData($review);

                $data['base']['created_at'] = now();
                $this->_write->insert($this->_reviewTable, $data['base']);

                $review->setId($this->_write->lastInsertId());
                $data['detail']['review_id'] = $review->getId();
                $this->_write->insert($this->_reviewDetailTable, $data['detail']);
            }
            $this->_write->commit();
        }
        catch (Exception $e){
            $this->_write->rollBack();
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Prepare data for review insert
     *
     * @todo    validate data
     * @param   Mage_Review_Model_Review $review
     * @return  array
     */
    protected function _prepareInsertData(Mage_Review_Model_Review $review)
    {
        $data = array(
            'base'  => array(
                'entity_id'         => $review->getEntityId(),
                'entity_pk_value'   => $review->getEntityPkValue(),
                'status_id'         => $review->getStatusId()
            ),
            'detail'=> array(
                'title'     => strip_tags($review->getTitle()),
                'detail'    => strip_tags($review->getDetail()),
                'store_id'=> $review->getStoreId(),
                'customer_id' => $review->getCustomerId(),
                'nickname'  => strip_tags($review->getNickname())
            )
        );

        return $data;
    }

    public function _prepareUpdateData(Mage_Review_Model_Review $review)
    {
        $data = array(
            'detail'=> array(
                'title'     => strip_tags($review->getTitle()),
                'detail'    => strip_tags($review->getDetail()),
                'nickname'  => strip_tags($review->getNickname())
            ),
            'base' => array(
                'status_id' => $review->getStatusId()
            )
        );

        return $data;
    }

    public function delete(Mage_Review_Model_Review $review)
    {
        if( $review->getId() ) {
            try {
                $this->_write->beginTransaction();
                $condition = $this->_write->quoteInto('review_id = ?', $review->getId());
                $this->_write->delete($this->_reviewTable, $condition);
                $this->_write->commit();
            } catch (Exception $e) {
                throw new Exception($e->getMessage());
            }
        }
    }

    public function getTotalReviews($entityPkValue)
    {
        $read = clone $this->_read;
        $select = $read->select();
        $select->from($this->_reviewTable, "COUNT(*)")
            ->where("{$this->_reviewTable}.entity_pk_value = ?", $entityPkValue);
        return $read->fetchOne($select);
    }
}