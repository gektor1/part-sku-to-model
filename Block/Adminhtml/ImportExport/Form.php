<?php

namespace Purei\PartSkuToModel\Block\Adminhtml\ImportExport;

class Form extends \Magento\Backend\Block\Widget {

    /**
     * @var string
     */
    protected $_template = 'Purei_PartSkuToModel::importExport.phtml';

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array $data
     */
    public function __construct(\Magento\Backend\Block\Template\Context $context, array $data = []) {
        parent::__construct($context, $data);
        $this->setUseContainer(true);
    }

}
