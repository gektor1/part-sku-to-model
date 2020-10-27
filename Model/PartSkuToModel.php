<?php

namespace Purei\PartSkuToModel\Model;

class PartSkuToModel extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface {
    /*
     * Constants defined for keys of array, makes typos less likely
     */

    const KEY_ID = 'id';
    const KEY_SKU = 'sku';
    const KEY_DESCRIPTION = 'description';
    const KEY_MODEL = 'model';
    const CACHE_TAG = 'purei_partskutomodel_partskutomodel';

    protected $_cacheTag = 'purei_partskutomodel_partskutomodel';
    protected $_eventPrefix = 'purei_partskutomodel_partskutomodel';

    protected function _construct() {
        $this->_init('Purei\PartSkuToModel\Model\ResourceModel\PartSkuToModel');
    }

    public function getIdentities() {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    public function getDefaultValues() {
        $values = [];

        return $values;
    }

    /**
     * {@inheritdoc}
     */
    public function getId() {
        return $this->getData(self::KEY_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function getSku() {
        return $this->getData(self::KEY_SKU);
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription() {
        return $this->getData(self::KEY_DESCRIPTION);
    }

    /**
     * {@inheritdoc}
     */
    public function getModel() {
        return $this->getData(self::KEY_MODEL);
    }

    public function loadByCriteria(array $filter) {

        //$filter should be array('columnname'=>'value','columname'=>'value')

        $collection = $this->getCollection();
        foreach ($filter as $column => $value) {
            $collection->addFieldToFilter($column, $value);
        }

        return $collection;
    }

}
