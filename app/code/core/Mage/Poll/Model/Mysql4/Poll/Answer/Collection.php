<?php
class Mage_Poll_Model_Mysql4_Poll_Answer_Collection extends Varien_Data_Collection_Db
{
    protected $_pollAnswerTable;

    public function __construct()
    {
        parent::__construct(Mage::getSingleton('core/resource')->getConnection('poll_read'));

        $this->_pollAnswerTable = Mage::getSingleton('core/resource')->getTableName('poll/poll_answer');

        $this->_sqlSelect
            ->from($this->_pollAnswerTable);

        $this->setOrder("{$this->_pollAnswerTable}.answer_order");

        $this->setItemObjectClass(Mage::getConfig()->getModelClassName('poll/poll'));
    }

    public function loadData($printQuery = false, $logQuery = false)
    {
        parent::loadData($printQuery, $logQuery);
        return $this;
    }

    public function addPollFilter($arrPollId)
    {
        if( !$arrPollId ) {
            return $this;
        }

        $condition = 'poll_id';
        $condition.= $this->getConnection()->quoteInto(' IN(?) ', $arrPollId);
        $this->addFilter(null, $condition, 'string');
        return $this;
    }

    function getPollAnswers($pollData)
    {
        $arr = array();
        foreach( $this->_items as $key => $item ) {
            if( $item->getPollId() == $pollData->getPollId() ) {
                $item->setPercent( $item->getPercent($pollData->getVotesCount(), $item->getVotesCount()) );
                $arr[] = $item->getData();
            }
        }
        return $arr;
    }
}