<?php

namespace WeDevelop\ElementalWidget\UserForm\Extension;

use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\FormField;
use SilverStripe\ORM\DataExtension;

class FieldWidthExtension extends DataExtension
{
    /** @config */
    private static array $db = [
        'FieldWidth' => 'Varchar(255)',
    ];

    public function updateCMSFields(FieldList $fields): FieldList
    {
        $fields->dataFieldByName('Title')->setDescription('Use &lt;a href=&quot;page_url&quot;&gt;link_text&lt;/a&gt; to add a link to the label');

        $fields->addFieldsToTab('Root.Main', [
            DropdownField::create('FieldWidth', 'Field width', [
                '' => 'Full (default)',
                'is-half' => 'Half',
                'is-third' => 'One-third',
                'is-fourth' => 'One-fourth',
            ]),
        ]);
    }

    public function afterUpdateFormField(FormField $field): void
    {
        /** @var Object $owner */
        $owner = $this->owner;
        $field->addExtraClass($owner->FieldWidth);
    }
}
