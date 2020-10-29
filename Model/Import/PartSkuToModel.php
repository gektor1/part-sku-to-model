<?php

namespace Purei\PartSkuToModel\Model\Import;

use Exception;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\ImportExport\Helper\Data as ImportHelper;
use Magento\ImportExport\Model\Import;
use Magento\ImportExport\Model\Import\Entity\AbstractEntity;
use Magento\ImportExport\Model\ResourceModel\Helper;
use Magento\ImportExport\Model\ResourceModel\Import\Data;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingError;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;
use Purei\PartSkuToModel\Model\Import\PartSkuToModel\RowValidatorInterface;

/**
 * Class PartSkuToModel
 */
class PartSkuToModel extends AbstractEntity {

    const ENTITY_CODE = 'part_sku_to_model';
    const TABLE = 'part_sku_to_model';

    /**
     * Column sku.
     */
    const COL_SKU = 'sku';

    /**
     * Column description.
     */
    const COL_DESCRIPTION = 'description';

    /**
     * Column model.
     */
    const COL_MODEL = 'model';

    /**
     * Column delete.
     */
    const COL_DELETE = 'delete';

    /**
     * Permanent entity columns.
     */
    protected $_permanentAttributes = [
        self::COL_SKU
    ];

    /**
     * Valid column names
     */
    protected $validColumnNames = [
        self::COL_SKU,
        self::COL_DESCRIPTION,
        self::COL_MODEL
    ];

    /**
     * Valid column names
     */
    protected $deleteColumnNames = [
        self::COL_SKU,
        self::COL_MODEL
    ];

    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * Courses constructor.
     *
     * @param JsonHelper $jsonHelper
     * @param ImportHelper $importExportData
     * @param Data $importData
     * @param ResourceConnection $resource
     * @param Helper $resourceHelper
     * @param ProcessingErrorAggregatorInterface $errorAggregator
     */
    public function __construct(
            JsonHelper $jsonHelper,
            ImportHelper $importExportData,
            Data $importData,
            ResourceConnection $resource,
            Helper $resourceHelper,
            ProcessingErrorAggregatorInterface $errorAggregator
    ) {
        $this->jsonHelper = $jsonHelper;
        $this->_importExportData = $importExportData;
        $this->_resourceHelper = $resourceHelper;
        $this->_dataSourceModel = $importData;
        $this->resource = $resource;
        $this->connection = $resource->getConnection(ResourceConnection::DEFAULT_CONNECTION);
        $this->errorAggregator = $errorAggregator;
    }

    /**
     * Entity type code getter.
     *
     * @return string
     */
    public function getEntityTypeCode() {
        return static::ENTITY_CODE;
    }

    /**
     * Get available columns
     *
     * @return array
     */
    public function getValidColumnNames(): array {
        return $this->validColumnNames;
    }

    /**
     * Row validation
     *
     * @param array $rowData
     * @param int $rowNum
     *
     * @return bool
     */
    public function validateRow(array $rowData, $rowNum): bool {
        if (isset($this->_validatedRows[$rowNum])) {
            return !$this->getErrorAggregator()->isRowInvalid($rowNum);
        }

        $this->_validatedRows[$rowNum] = true;

        $sku = $rowData[self::COL_SKU];
        if ('' === $sku) {
            $this->skipRow($rowNum, RowValidatorInterface::ERROR_SKU_IS_EMPTY, ProcessingError::ERROR_LEVEL_WARNING);
        }
        $model = $rowData[self::COL_MODEL];
        if ('' === $model) {
            $this->skipRow($rowNum, RowValidatorInterface::ERROR_MODEL_IS_EMPTY, ProcessingError::ERROR_LEVEL_NOTICE);
        }

        return !$this->getErrorAggregator()->isRowInvalid($rowNum);
    }

    /**
     * Import data
     *
     * @return bool
     *
     * @throws Exception
     */
    protected function _importData(): bool {
        switch ($this->getBehavior()) {
            case Import::BEHAVIOR_ADD_UPDATE:
                $this->addUpdateDeleteEntity();
                break;
        }
        return true;
    }

    /**
     * Delete entities
     *
     * @return bool
     */
    private function addUpdateDeleteEntity(): bool {
        $behavior = $this->getBehavior();
        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            $entityList = [];
            $entityDeleteList = [];

            foreach ($bunch as $rowNum => $row) {
                if (!$this->validateRow($row, $rowNum)) {
                    continue;
                }

                if ($this->getErrorAggregator()->hasToBeTerminated()) {
                    $this->getErrorAggregator()->addRowToSkip($rowNum);
                    continue;
                }

                $columnValues = [];
                if (!empty($row[self::COL_DELETE])) {
                    foreach ($this->getDeleteColumns() as $columnKey) {
                        $columnValues[$columnKey] = $row[$columnKey];
                    }
                    $entityDeleteList[] = $columnValues;
                } else {
                    foreach ($this->getAvailableColumns() as $columnKey) {
                        $columnValues[$columnKey] = $row[$columnKey];
                    }
                    $entityList[] = $columnValues;
                }
//                $this->countItemsCreated += (int) !isset($row[static::ENTITY_ID_COLUMN]);
//                $this->countItemsUpdated += (int) isset($row[static::ENTITY_ID_COLUMN]);
            }

            if ($entityList) {
                $this->saveEntityFinish($entityList);
            }
            if ($entityDeleteList) {
                foreach ($entityDeleteList as $entityDelete) {
                    $this->deleteEntityFinish($entityDelete);
                }
            }
        }

        return true;
    }

    /**
     * Save entities
     *
     * @param array $entityData
     *
     * @return bool
     */
    private function saveEntityFinish(array $entityData): bool {
        if ($entityData) {
            $tableName = $this->connection->getTableName(static::TABLE);
            $this->connection->insertOnDuplicate($tableName, $entityData, $this->getAvailableColumns());
            return true;
        }
        return false;
    }

    /**
     * Delete entities
     *
     * @param array $entityIds
     *
     * @return bool
     */
    private function deleteEntityFinish(array $entityData): bool {
        if ($entityData) {
            try {
                $this->countItemsDeleted += $this->connection->delete(
                        $this->connection->getTableName(static::TABLE),
                        [
                            $this->connection->quoteInto(static::COL_SKU . ' = ?', $entityData[self::COL_SKU]),
                            $this->connection->quoteInto(static::COL_MODEL . ' = ?', $entityData[self::COL_MODEL])
                        ]
                );
                return true;
            } catch (Exception $e) {
                return false;
            }
        }
        return false;
    }

    /**
     * Get available columns
     *
     * @return array
     */
    private function getAvailableColumns(): array {
        return $this->validColumnNames;
    }

    /**
     * Get available columns
     *
     * @return array
     */
    private function getDeleteColumns(): array {
        return $this->deleteColumnNames;
    }

    /**
     * Add row as skipped
     *
     * @param int $rowNum
     * @param string $errorCode Error code or simply column name
     * @param string $errorLevel error level
     * @param string|null $colName optional column name
     * @return $this
     */
    private function skipRow(
            $rowNum,
            string $errorCode,
            string $errorLevel = ProcessingError::ERROR_LEVEL_NOT_CRITICAL,
            $colName = null
    ): self {
        $this->addRowError($errorCode, $rowNum, $colName, null, $errorLevel);
        $this->getErrorAggregator()
                ->addRowToSkip($rowNum);
        return $this;
    }

}
