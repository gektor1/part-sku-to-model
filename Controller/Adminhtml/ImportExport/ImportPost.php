<?php

namespace Purei\PartSkuToModel\Controller\Adminhtml\ImportExport;

use Magento\Framework\Controller\ResultFactory;

class ImportPost extends \Purei\PartSkuToModel\Controller\Adminhtml\ImportExport {

    /**
     * import action from import/export
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute() {
        $importFile = $this->getRequest()->getFiles('import_part_sku_to_model_file');
        $delimiter = $this->getRequest()->getParam('import_part_sku_to_model_delimiter');

        if ($this->getRequest()->isPost() && isset($importFile['tmp_name'])) {
            try {

                /** @var $importHandler \Purei\PartSkuToModel\Model\PartSkuToModel\CsvImportHandler */
                $importHandler = $this->_objectManager->create(
                        \Purei\PartSkuToModel\Model\PartSkuToModel\CsvImportHandler::class
                );
                
                $delimiters = array(
                    'comma'     => ',',
                    'semicolon' => ';',
                    'tab'         => "\t",
                    'pipe'         => '|',
                    'colon'     => ':'
                );
                
                $importHandler->importFromCsvFile($importFile, $delimiters[$delimiter]);

                $this->messageManager->addSuccess(__('The part_sku_to_model has been imported.'));
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addError(__('Invalid file upload attempt'));
            }
        } else {
            $this->messageManager->addError(__('Invalid file upload attempt'));
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->_redirect->getRedirectUrl());
        return $resultRedirect;
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
