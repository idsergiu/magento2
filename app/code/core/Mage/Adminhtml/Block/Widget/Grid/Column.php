<?php
/**
 * Grid column block
 *
 * @package     Mage
 * @subpackage  Adminhtml
 * @copyright   Varien (c) 2007 (http://www.varien.com)
 * @license     http://www.opensource.org/licenses/osl-3.0.php
 * @author      Dmitriy Soroka <dmitriy@varien.com>
 */
class Mage_Adminhtml_Block_Widget_Grid_Column extends Mage_Adminhtml_Block_Widget
{
    protected $_grid;
    protected $_renderer;
    protected $_filter;
    protected $_type;
    
    public function __construct($data=array()) 
    {
        parent::__construct($data);
    }
    
    public function setGrid($grid)
    {
        $this->_grid = $grid;
        return $this;
    }
    
    public function getGrid()
    {
        return $this->_grid;
    }
    
    public function getHtmlProperty()
    {
        $out = ' ';
        if ($this->getWidth()) {
            $out='width="'.$this->getWidth().'%" ';
        }
        if ($this->getAlign()) {
            $out='align="'.$this->getAlign().'" ';
        }
        return $out;
    }
    
    public function getHeaderHtml()
    {
        $out = '';
        if ($this->getSortable()!==false) {

            $className = 'not-sort';
            $dir = (strtolower($this->getDir())=='asc') ? 'desc' : 'asc';
            if ($this->getDir()) {
                $className = 'sort-arrow-' . $dir;
            }
            $out = '<a href="" name="'.$this->getId().'" target="'.$dir
                   .'" class="' . $className . '">'.$this->getHeader().'</a>';
        }
        else {
            $out = $this->getHeader();
        }
        return $out;
    }
    
    public function setFilter($column, $args=array())
    {
        if (is_string($column)) {
            $column = $this->getColumn($column);
        }
        if (!$column instanceof Varien_Object) {
            throw Mage::exception('Mage_Adminhtml', 'Invalid column specified');
        }
        if (empty($args['model'])) {
            $args['model'] = 'Varien_Data_Form_Element_Text';
        }

        $filter = Mage::getModel($args['model'], $args);
        
        $filter->setForm($this->getForm())
            ->setHtmlId('grid_filter_'.$column->getId())
            ->setName('grid_filter['.$column->getId().']');
            
        $column->setFilter($filter);
        
        return $this;
    }
    
    public function getFilterHtml()
    {
        $out = '';
        
        $filter = $column->getFilter();
        if ($filter) {
            $out .= $filter->toHtml();
        }

        return $out;
    }
    
    /**
     * Retrieve row column field value for display
     *
     * @param   Varien_Object $row
     * @return  string
     */
    public function getRowField(Varien_Object $row)
    {
        return $this->getRenderer()->render($row);
    }
    
    public function setRenderer($renderer)
    {
        
    }
    
    protected function _getRendererByType()
    {
        switch (strtolower($this->getType())) {
            case 'date':
                $rendererClass = 'adminhtml/widget_grid_column_renderer_date';
                break;
            case 'currency':
                $rendererClass = 'adminhtml/widget_grid_column_renderer_currency';
                break;
            default:
                $rendererClass = 'adminhtml/widget_grid_column_renderer_text';
                break;
        }
        return $rendererClass;
    }
    
    public function getRenderer()
    {
        if (!$this->_renderer) {
            $rendererClass = $this->getData('renderer');
            if (!$rendererClass) {
                $rendererClass = $this->_getRendererByType();
            }
            $this->_renderer = $this->getLayout()->createBlock($rendererClass)
                ->setColumn($this);
        }
        return $this->_renderer;
    }
}
