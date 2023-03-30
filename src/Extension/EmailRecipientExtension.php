<?php

declare(strict_types=1);

namespace WeDevelop\ElementalWidget\UserForm\Extension;

use SilverStripe\Forms\FieldList;
use SilverStripe\ORM\DataExtension;
use Symbiote\MultiValueField\Fields\MultiValueTextField;
use Symbiote\MultiValueField\ORM\FieldType\MultiValueField;

class EmailRecipientExtension extends DataExtension
{
    /**
     * @var array<string, string>
     * @config
     */
    private static array $db = [
        'PageRules' => MultiValueField::class,
    ];

    public function updateCMSFields(FieldList $fields): void
    {
        $fields->addFieldToTab(
            'Root.CustomRules',
            MultiValueTextField::create(
                'PageRules',
                'Page url rules'
            )->setDescription(
                '
                Emails will only be sent to these recipients if the URL of the page the form is sent from contains
                one of these specified values. If no values are specified then this field is ignored.'
            ),
            'CustomRulesCondition'
        );
    }
    public function updateFilteredEmailRecipients(&$recipients, $data): void
    {
        foreach ($recipients as $key => $recipient) {
            $pageRules = $recipient->PageRules->getValues();
            if (!count($pageRules)) {
                continue;
            }
            foreach ($pageRules as $rule) {
                if (str_contains($data['_PageUrl'], $rule)) {
                    continue 2;
                }
            }
            unset($recipients[$key]);
        }
    }
}
