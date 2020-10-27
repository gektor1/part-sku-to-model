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
     * Retrieve a list of fields required for CSV file (order is important!)
     *
     * @return array
     */
    public function getRequiredCsvFields() {
        // indexes are specified for clarity, they are used during import
        return [
            0 => 'sku',
            1 => 'description',
            2 => 'model',
            3 => 'delete'
        ];
    }

    /**
     * Import part_sku_to_model from CSV file
     *
     * @param array $file file info retrieved from $_FILES array
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function importFromCsvFile($file) {
        if (!isset($file['tmp_name'])) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Invalid file upload attempt.'));
        }
        $rawData = $this->csvProcessor->getData($file['tmp_name']);

        // first row of file represents headers
        $fileFields = $rawData[0];

        $validFields = $this->_filterFileFields($fileFields);
        $invalidFields = array_diff_key($fileFields, $validFields);
        $data = $this->_filterData($rawData, $invalidFields, $validFields);

        foreach ($data as $rowIndex => $dataRow) {
            // skip headers
            if ($rowIndex == 0) {
                continue;
            }
            $this->_import($dataRow);
        }
    }

    /**
     * Filter file fields (i.e. unset invalid fields)
     *
     * @param array $fileFields
     * @return string[] filtered fields
     */
    protected function _filterFileFields(array $fileFields) {
        $filteredFields = $this->getRequiredCsvFields();
        $requiredFieldsNum = count($this->getRequiredCsvFields());
        $fileFieldsNum = count($fileFields);

        // process title-related fields that are located right after required fields with store code as field name)
        for ($index = $requiredFieldsNum; $index < $fileFieldsNum; $index++) {
            $titleFieldName = $fileFields[$index];
            $filteredFields[$index] = $titleFieldName;
        }

        return $filteredFields;
    }

    /**
     * Filter data (i.e. unset all invalid fields and check consistency)
     *
     * @param array $rawData
     * @param array $invalidFields assoc array of invalid file fields
     * @param array $validFields assoc array of valid file fields
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    protected function _filterData(array $rawData, array $invalidFields, array $validFields) {
        $validFieldsNum = count($validFields);
        foreach ($rawData as $rowIndex => $dataRow) {
            // skip empty rows
            if (count($dataRow) <= 1) {
                unset($rawData[$rowIndex]);
                continue;
            }
            // unset invalid fields from data row
            foreach ($dataRow as $fieldIndex => $fieldValue) {
                if (isset($invalidFields[$fieldIndex])) {
                    unset($rawData[$rowIndex][$fieldIndex]);
                }
            }
            // check if number of fields in row match with number of valid fields
            if (count($rawData[$rowIndex]) != $validFieldsNum) {
                //throw new \Magento\Framework\Exception\LocalizedException(__('Invalid file format.'));
                continue;
            }
        }
        return $rawData;
    }

    /**
     * Import single row
     *
     * @param array $data
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _import(array $data) {
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
        
        if ($data[3] == 1) {
            $partSkuToModel->delete();
        } else {
            $partSkuToModel->addData($modelData);
            $partSkuToModel->save();
        }
    }

}
