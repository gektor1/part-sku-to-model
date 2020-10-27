<?php

namespace Purei\PartSkuToModel\Model\ResourceModel\PartSkuToModel;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection {

    protected $_idFieldName = 'id';
    protected $_eventPrefix = 'purei_partskutomodel_partskutomodel_collection';
    protected $_eventObject = 'partskutomodel_collection';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct() {
        $this->_init('Purei\PartSkuToModel\Model\PartSkuToModel', 'Purei\PartSkuToModel\Model\ResourceModel\PartSkuToModel');
    }

}
