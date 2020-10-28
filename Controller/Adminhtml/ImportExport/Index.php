<?php

namespace Purei\PartSkuToModel\Controller\Adminhtml\ImportExport;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class Index
 */
class Index extends \Purei\PartSkuToModel\Controller\Adminhtml\ImportExport implements HttpGetActionInterface {

    const MENU_ID = 'Purei_PartSkuToModel::purei_importexport';

    /**
     * @return Page
     */
    public function execute() {

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu(static::MENU_ID);
        $resultPage->addContent(
                $resultPage->getLayout()->createBlock(
                        \Purei\PartSkuToModel\Block\Adminhtml\ImportExport\Form::class)
        );
        $resultPage->getConfig()->getTitle()->prepend(__('Import and Export part_sku_to_model'));
        return $resultPage;
    }

}
