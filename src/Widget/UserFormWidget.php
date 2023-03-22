<?php

namespace WeDevelop\ElementalWidget\UserForm\Widget;

use gorriecoe\Link\Models\Link;
use gorriecoe\LinkField\LinkField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\ORM\FieldType\DBHTMLText;
use SilverStripe\UserForms\Form\GridFieldAddClassesButton;
use SilverStripe\UserForms\UserForm;
use WeDevelop\ElementalWidget\Model\Widget;
use WeDevelop\ElementalWidget\UserForm\Controller\UserFormWidgetController;

class UserFormWidget extends Widget
{
    use UserForm {
        getCMSFields as traitGetCMSFields;
    }

    /** @config */
    private static string $table_name = 'UserFormWidget';

    /** @config */
    private static string $singular_name = 'User form widget';

    /** @config */
    private static string $plural_name = 'User form widgets';

    /** @config */
    private static string $description = 'Displays a user form';

    /** @config */
    private static string $icon = 'font-icon-block-form';

    /** @config */
    private static array $has_one = [
        'SuccessPage' => Link::class,
    ];

    public function Controller(): UserFormWidgetController
    {
        return new UserFormWidgetController($this);
    }

    public function Form(): Form|DBHTMLText
    {
        return $this->Controller()->Form();
    }

    public function getCMSFields(): FieldList
    {
        $fields = $this->traitGetCMSFields();

        $fields->removeByName([
            'OnCompleteMessageLabel',
            'OnCompleteMessage',
            'SuccessPageID',
        ]);

        $fields->addFieldToTab('Root.Main', LinkField::create('SuccessPage', 'Success page', $this));

        /** @var GridField $fieldsField */
        $fieldsField = $fields->dataFieldByName('Fields');
        if ($fieldsField) {
            /** @var GridFieldAddClassesButton[] $components */
            $components = $fieldsField->getConfig()->getComponentsByType(GridFieldAddClassesButton::class);
            $nthOfComponent = 0;
            foreach ($components as $component) {
                if ($nthOfComponent > 1) {
                    $fieldsField->getConfig()->removeComponent($component);
                }
                $nthOfComponent++;
            }
        }

        return $fields;
    }
}
