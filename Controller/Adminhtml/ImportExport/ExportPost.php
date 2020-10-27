<?php

namespace Purei\PartSkuToModel\Controller\Adminhtml\ImportExport;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ResponseInterface;

class ExportPost extends \Purei\PartSkuToModel\Controller\Adminhtml\ImportExport {

    /**
     * Export action from import/export
     *
     * @return ResponseInterface
     */
    public function execute() {
        $template = '"{{sku}}","{{description}}","{{model}}"';
        $collection = $this->_objectManager->create(
                \Purei\PartSkuToModel\Model\ResourceModel\PartSkuToModel\Collection::class
        );

        $content = 'sku,description,model' . PHP_EOL;
        while ($partSkuToModel = $collection->fetchItem()) {
            $content .= $partSkuToModel->toString($template) . "\n";
        }
        return $this->fileFactory->create('part_sku_to_model_Export.csv', $content, DirectoryList::VAR_DIR);
    }

    /**
     * @return bool
     */
    protected function _isAllowed() {
        return $this->_authorization->isAllowed(
                        'Purei_PartSkuToModel::purei_importexport'
        );
    }

}
