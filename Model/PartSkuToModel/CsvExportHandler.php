<?php

namespace Purei\PartSkuToModel\Model\PartSkuToModel;

class CsvExportHandler {

    /**
     * @var \Purei\PartSkuToModel\Model\PartSkuToModelFactory
     */
    protected $_partSkuToModelFactory;

    /**
     * @param \Purei\PartSkuToModel\Model\PartSkuToModelFactory $partSkuToModelFactory
     * @param \Magento\Framework\File\Csv $csvProcessor
     */
    public function __construct(
            \Purei\PartSkuToModel\Model\PartSkuToModelFactory $partSkuToModelFactory,
            \Magento\Framework\File\Csv $csvProcessor
    ) {
        $this->_partSkuToModelFactory = $partSkuToModelFactory;
    }

    /**
     * Export
     */
    public function exportToCsvString() {

        $rows = [['sku', 'description', 'model']];

        $collection = $this->_partSkuToModelFactory->create()->getCollection();
        while ($partSkuToModel = $collection->fetchItem()) {
            $rows[] = [
                $partSkuToModel->getSku(),
                $partSkuToModel->getDescription(),
                $partSkuToModel->getModel()
            ];
        }

        $f = fopen('php://memory', 'r+');
        foreach ($rows as $row) {
            if (fputcsv($f, $row) === false) {
                return false;
            }
        }
        rewind($f);
        $csv_line = stream_get_contents($f);
        return rtrim($csv_line);
    }

}
