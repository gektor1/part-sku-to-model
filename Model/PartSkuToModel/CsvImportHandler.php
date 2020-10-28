<?php

namespace Purei\PartSkuToModel\Model\PartSkuToModel;

class CsvImportHandler {

    /**
     * @var \Purei\PartSkuToModel\Model\PartSkuToModelFactory
     */
    protected $_partSkuToModelFactory;

    /**
     * @var \Magento\Framework\File\Csv
     */
    protected $csvProcessor;

    /**
     * @param \Purei\PartSkuToModel\Model\PartSkuToModelFactory $partSkuToModelFactory
     * @param \Magento\Framework\File\Csv $csvProcessor
     */
    public function __construct(
            \Purei\PartSkuToModel\Model\PartSkuToModelFactory $partSkuToModelFactory,
            \Magento\Framework\File\Csv $csvProcessor
    ) {
        $this->_partSkuToModelFactory = $partSkuToModelFactory;
        $this->csvProcessor = $csvProcessor;
    }

    /**
     * Import part_sku_to_model from CSV file
     *
     * @param array $file file info retrieved from $_FILES array
     * @param string $delimiter delimiter
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function importFromCsvFile($file, $delimiter) {
        if (!isset($file['tmp_name'])) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Invalid file upload attempt.'));
        }
        $rawData = $this->csvProcessor->setDelimiter($delimiter)
                ->getData($file['tmp_name']);

        // first row of file represents headers
        array_shift($rawData);
        array_map(array($this, '_import'), $rawData);
    }

    /**
     * Import single row
     *
     * @param array $data
     */
    protected function _import(array $data) {
        if (empty($data[0]) || empty($data[2])) {
            return;
        }

        $modelData = [
            'sku' => $data[0],
            'description' => $data[1],
            'model' => $data[2],
        ];
        // try to load existing
        /** @var $partSkuToModel \Purei\PartSkuToModel\Model\ */
        $partSkuToModel = $this->_partSkuToModelFactory->create()
                        ->loadByCriteria([
                            'sku' => $data[0],
                            'model' => $data[2]
                        ])->getFirstItem();

        if (isset($data[3]) && $data[3] == 1) {
            $partSkuToModel->delete();
        } else {
            $partSkuToModel->addData($modelData);
            $partSkuToModel->save();
        }
    }

}
