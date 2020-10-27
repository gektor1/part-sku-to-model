<?php

namespace Purei\PartSkuToModel\Model\ResourceModel;

class PartSkuToModel extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb {

    public function __construct(
            \Magento\Framework\Model\ResourceModel\Db\Context $context
    ) {
        parent::__construct($context);
    }

    protected function _construct() {
        $this->_init('part_sku_to_model', 'id');
    }

}
