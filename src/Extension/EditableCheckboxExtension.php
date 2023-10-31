<?php

declare(strict_types=1);

namespace WeDevelop\ElementalWidget\UserForm\Extension;

use gorriecoe\Link\Models\Link;
use gorriecoe\LinkField\LinkField;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\FormField;
use SilverStripe\ORM\DataExtension;
use SilverStripe\ORM\FieldType\DBField;

/**
 * @property bool $TitleIsLink
 * @property bool $TitleIsPartiallyLink
 * @property string $ParsedTitle
 */
class EditableCheckboxExtension extends DataExtension
{
    /**
     * @var array<string, string>
     * @config
     */
    private static array $db = [
        'TitleIsLink' =>  'Boolean(false)',
        'TitleIsPartiallyLink' => 'Boolean(false)',
    ];
    /**
     * @var array<string, string>
     * @config
     */
    private static array $has_one = [
        'Link' => Link::class,
    ];

    public function updateCMSFields(FieldList $fields): void
    {
        $fields->addFieldsToTab('Root.Main', [
            CheckboxField::create('TitleIsLink'),
            LinkField::create('Link', 'Link', $this->owner),
            CheckboxField::create('TitleIsPartiallyLink')->displayIf('TitleIsLink')->isChecked()->end()
                ->setDescription('If enabled, you can create a partially linked title by enclosing the clickable part in single square brackets [ ], like: "I have read [the privacy statement]"'),
        ]);
    }

    public function beforeUpdateFormField(FormField &$field): void
    {
        $field->setTitle(DBField::create_field('HTMLText', $this->getParsedTitle()));
    }

    public function getParsedTitle(): string
    {
        if (!$this->owner->TitleIsLink) {
            return $this->owner->Title;
        }
        /** @var Link $linkObject */
        $linkObject =  $this->owner->Link;
        if (is_object($linkObject)) {
            $linkUrl = $linkObject->getLinkURL();
            $targetAttribute = $linkObject->OpenInNewWindow ? 'target="_blank"' : '';
        } else {
            $linkUrl = '';
            $targetAttribute = '';
        }

        if (!$this->owner->TitleIsPartiallyLink) {
            return "<a href='$linkUrl' $targetAttribute>{$this->owner->Title}</a>";
        }

        $title = $this->owner->Title;

        preg_match('/\[(.*?)\]/', $title, $matches);

        if (count($matches) === 2) {
            $textToWrap = trim($matches[1]);
            $replacement = "<a href='$linkUrl' $targetAttribute>$textToWrap</a>";
            $parsedTitle = str_replace("[$textToWrap]", $replacement, $title);

            return $parsedTitle;
        }

        return $title;
    }
}
