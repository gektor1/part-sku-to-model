<?php

namespace Purei\PartSkuToModel\Model\Export;

use Exception;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\ImportExport\Model\Export\Factory as ExportFactory;
use Magento\ImportExport\Model\Export\AbstractEntity;
use Magento\Store\Model\StoreManagerInterface;
use Magento\ImportExport\Model\ResourceModel\CollectionByPagesIteratorFactory;
use Purei\PartSkuToModel\Model\Export\AttributeCollection;
use Purei\PartSkuToModel\Model\PartSkuToModelFactory;

/**
 * @inheritdoc
 */
class PartSkuToModel extends AbstractEntity {

    /**
     * collection
     *
     * @var PartSkuToModelFactory
     */
    protected $_entityCollectionFactory;

    /**
     * collection
     *
     * @var PartSkuToModelCollaction
     */
    protected $_entityCollection;

    /**
     * collection
     *
     * @var AttributeCollection
     */
    protected $_entityAttributeCollection;

    public function __construct(ScopeConfigInterface $scopeConfig,
            StoreManagerInterface $storeManager,
            ExportFactory $collectionFactory,
            CollectionByPagesIteratorFactory $resourceColFactory,
            AttributeCollection $entityAttributeCollection,
            PartSkuToModelFactory $entityCollectionFactory,
            array $data = []
    ) {
        $this->_entityAttributeCollection = $entityAttributeCollection;
        $this->_entityCollectionFactory = $entityCollectionFactory;
        parent::__construct(
                $scopeConfig,
                $storeManager,
                $collectionFactory,
                $resourceColFactory,
                $data
        );
    }

    /**
     * @inheritdoc
     * @throws \Exception
     */
    public function getAttributeCollection() {
        return $this->_entityAttributeCollection->get();
    }

    /**
     * @inheritdoc
     * @throws Exception
     */
    public function export() {
        $writer = $this->getWriter();
        $writer->setHeaderCols($this->_getHeaderColumns());

        /** @var PartSkuToModelCollaction $collection */
        $collection = $this->_getEntityCollection()->getCollection();
        foreach ($collection->getData() as $data) {
            $writer->writeRow($data);
        }

        return $writer->getContents();
    }

    /**
     * @inheritdoc
     * @throws Exception
     */
    protected function _getHeaderColumns() {
        return [
            'sku',
            'description',
            'model'
        ];
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function exportItem($item) {
        // will not implement this method as it is legacy interface
    }

    /**
     * @inheritdoc
     */
    public function getEntityTypeCode() {
        return 'part_sku_to_model';
    }

    /**
     * Get entity collection
     *
     * @param bool $resetCollection
     * @return \Magento\Framework\Data\Collection\AbstractDb
     */
    protected function _getEntityCollection($resetCollection = false) {
        if ($resetCollection || empty($this->_entityCollection)) {
            $this->_entityCollection = $this->_entityCollectionFactory->create();
        }
        return $this->_entityCollection;
    }

}
