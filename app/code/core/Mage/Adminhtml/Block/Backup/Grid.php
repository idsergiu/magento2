<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Adminhtml backups grid block
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Block_Backup_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    protected function _construct()
    {
        $this->setSaveParametersInSession(true);
        $this->setId('backupsGrid');
        $this->setDefaultSort('time', 'desc');
    }

    /**
     * Init backups collection
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getSingleton('Mage_Backup_Model_Fs_Collection');
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Prepare mass action controls
     *
     * @return Mage_Adminhtml_Block_Backup_Grid
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('ids');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'=> Mage::helper('adminhtml')->__('Delete'),
             'url'  => $this->getUrl('*/*/massDelete'),
             'confirm' => Mage::helper('backup')->__('Are you sure you want to delete the selected backup(s)?')
        ));

        return $this;
    }

    /**
     * Configuration of grid
     *
     * @return Mage_Adminhtml_Block_Backup_Grid
     */
    protected function _prepareColumns()
    {
        $url7zip = Mage::helper('Mage_Adminhtml_Helper_Data')->__('The archive can be uncompressed with <a href="%s">%s</a> on Windows systems', 'http://www.7-zip.org/', '7-Zip');

        $this->addColumn('time', array(
            'header'    => Mage::helper('Mage_Backup_Helper_Data')->__('Time'),
            'index'     => 'date_object',
            'type'      => 'datetime',
        ));

        $this->addColumn('size', array(
            'header'    => Mage::helper('Mage_Backup_Helper_Data')->__('Size, Bytes'),
            'index'     => 'size',
            'type'      => 'number',
            'sortable'  => true,
            'filter'    => false
        ));

        $this->addColumn('type', array(
            'header'    => Mage::helper('Mage_Backup_Helper_Data')->__('Type'),
            'type'      => 'options',
            'options'   => Mage::helper('backup')->getBackupTypes(),
            'index'     =>'type'
        ));

        $this->addColumn('download', array(
            'header'    => Mage::helper('Mage_Backup_Helper_Data')->__('Download'),
            'format'    => '<a href="' . $this->getUrl('*/*/download', array('time' => '$time', 'type' => '$type'))
                . '">$extension</a> &nbsp; <small>('.$url7zip.')</small>',
            'index'     => 'type',
            'sortable'  => false,
            'filter'    => false
        ));

        if (Mage::helper('backup')->isRollbackAllowed()){
            $this->addColumn('action', array(
                    'header'   => Mage::helper('backup')->__('Action'),
                    'type'     => 'action',
                    'width'    => '80px',
                    'filter'   => false,
                    'sortable' => false,
                    'actions'  => array(array(
                        'url'     => '#',
                        'caption' => Mage::helper('backup')->__('Rollback'),
                        'onclick' => 'return backup.rollback(\'$type\', \'$time\');'
                    )),
                    'index'    => 'type',
                    'sortable' => false
            ));
        }

        return $this;
    }

}