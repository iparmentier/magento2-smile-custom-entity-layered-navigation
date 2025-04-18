<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/OSL-3.0
 *
 * @category  Amadeco
 * @package   Amadeco_SmileCustomEntityLayeredNavigation
 * @copyright Copyright (c) Amadeco (https://www.amadeco.fr) - Ilan Parmentier
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */
declare(strict_types=1);

namespace Amadeco\SmileCustomEntityLayeredNavigation\Block\Adminhtml\Attribute\Edit\Tab;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Magento\LayeredNavigation\Model\Attribute\Source\FilterableOptions;
use Smile\CustomEntity\Api\Data\CustomEntityAttributeInterface;

/**
 * Layered Navigation Configuration tab for Custom Entity attributes
 */
class Layered extends Generic
{
    /**
     * @var FilterableOptions
     */
    private FilterableOptions $filterableOptions;

    /**
     * Constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param FilterableOptions $filterableOptions
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        FilterableOptions $filterableOptions,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $data);
        $this->filterableOptions = $filterableOptions;
    }

    /**
     * Prepare form fields
     *
     * @return $this
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    protected function _prepareForm()
    {
        /** @var CustomEntityAttributeInterface $attributeObject */
        $attributeObject = $this->_coreRegistry->registry('entity_attribute');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create([
            'data' => [
                'id' => 'edit_form',
                'action' => $this->getData('action'),
                'method' => 'post'
            ]
        ]);

        $fieldset = $form->addFieldset(
            'layered_fieldset',
            ['legend' => __('Layered Navigation Configuration')]
        );

        $fieldset->addField('is_filterable', 'select', [
            'name' => 'is_filterable',
            'label' => __('Use in Layered Navigation'),
            'title' => __('Use in Layered Navigation'),
            'note' => __('Can be used only with custom entity input type Yes/No, Dropdown and Multiple Select.'),
            'values' => $this->filterableOptions->toOptionArray(),
            'data-role' => 'filterable-field'
        ]);

        $fieldset->addField('position', 'text', [
            'name' => 'position',
            'label' => __('Position'),
            'title' => __('Position'),
            'note' => __('Position of attribute in layered navigation block.'),
            'class' => 'validate-digits',
            'data-role' => 'position-field'
        ]);

        $this->setForm($form);

        if ($attributeObject) {
            $form->setValues($attributeObject->getData());
        }

        return parent::_prepareForm();
    }
}