<?php

declare(strict_types=1);

namespace WeDevelop\ElementalWidget\UserForm\Extension;

use SilverStripe\Forms\HeaderField;
use UncleCheese\DisplayLogic\Forms\Wrapper;
use SilverStripe\ORM\DataExtension;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\HTMLEditor\HTMLEditorField;
use SilverStripe\Forms\FieldList;
use WeDevelop\ElementalWidget\UserForm\Widget\UserFormWidget;

/**
 * @property bool $EnableSubmissionsLimit
 * @property int $SubmissionsLimitDay
 * @property int $SubmissionsLimitWeek
 * @property int $SubmissionsLimitMonth
 * @property int $SubmissionsLimitTotal
 * @property string $SubmissionsLimitText
 * @property SubmissionLimitExtension|SubmissionCountExtension|UserFormWidget $owner
 */
class SubmissionLimitExtension extends DataExtension
{
    /** @config */
    private static array $db = [
        'EnableSubmissionsLimit' => 'Boolean',
        'SubmissionsLimitDay' => 'Int',
        'SubmissionsLimitWeek' => 'Int',
        'SubmissionsLimitMonth' => 'Int',
        'SubmissionsLimitTotal' => 'Int',
        'SubmissionsLimitModal' => 'Boolean(false)',
        'SubmissionsLimitTitle' => 'Varchar(255)',
        'SubmissionsLimitHideTitle' => 'Boolean(false)',
        'SubmissionsLimitContent' => 'HTMLText',
    ];

    public function updateCMSFields(FieldList $fields): FieldList
    {
        // Requires the counts of SubmissionCountExtension, this was split from the limit
        // since just tracking the submission counts could be interesting as-is.
        if (!$this->owner->hasExtension(SubmissionCountExtension::class)) {
            $fields->addFieldsToTab('Root.Main', [
                HeaderField::create('Notice', 'Notice: Missing extensions SubmissionCountExtension'),
            ]);

            return $fields;
        }

        $fields->removeByName([
            'SubmissionsLimitDay',
            'SubmissionsLimitWeek',
            'SubmissionsLimitMonth',
            'SubmissionsCountDay',
            'SubmissionsCountWeek',
            'SubmissionsCountMonth',
            'SubmissionsLimitText',
            'SubmissionsLimitModal',
            'SubmissionsLimitTitle',
            'SubmissionsLimitHideTitle',
            'SubmissionsLimitContent',
        ]);

        $fields->addFieldsToTab('Root.LimitSubmissions', [
            CheckboxField::create('EnableSubmissionsLimit'),
            Wrapper::create([
                TextField::create('SubmissionsLimitDay')
                    ->setDescription(sprintf('Current amount: %s', ($this->owner->SubmissionsLimitDay === 0 ? "No Limit" : $this->owner->SubmissionsLimitDay))),
                TextField::create('SubmissionsLimitWeek')
                    ->setDescription(sprintf('Current amount: %s', ($this->owner->SubmissionsLimitWeek === 0 ? "No Limit" : $this->owner->SubmissionsLimitWeek))),
                TextField::create('SubmissionsLimitMonth')
                    ->setDescription(sprintf('Current amount: %s', ($this->owner->SubmissionsLimitMonth === 0 ? "No Limit" : $this->owner->SubmissionsLimitMonth))),
                TextField::create('SubmissionsLimitTotal')
                    ->setDescription(sprintf('Current amount: %s', ($this->owner->SubmissionsLimitTotal === 0 ? "No Limit" : $this->owner->SubmissionsLimitTotal))),
                CheckboxField::create('SubmissionsLimitModal', 'Show modal over disabled form'),
                Wrapper::create([
                    TextField::create('SubmissionsLimitTitle', 'Title')
                        ->setDescription('Defaults to grid block title where the form is shown'),
                    CheckboxField::create('SubmissionsLimitHideTitle', 'Hide title'),
                    HTMLEditorField::create('SubmissionsLimitContent', 'Content')
                        ->setDescription('If not set the text "Excuses, het formulier is uitgeschakeld." will appear'),
                ])
                    ->displayIf('SubmissionsLimitModal')
                    ->isChecked()
                    ->end(),
            ])
                ->displayIf('EnableSubmissionsLimit')
                ->isChecked()
                ->end(),
        ]);

        return $fields;
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

    /**
     * Returns true if the submission limit for this form is reached
     *
     * @return boolean
     */
    public function LimitReached(): bool
    {
        if ($this->owner->SubmissionsLimitDay === 0) {
            $this->owner->SubmissionsLimitDay = INF;
        }

        if ($this->owner->SubmissionsLimitWeek === 0) {
            $this->owner->SubmissionsLimitWeek = INF;
        }

        if ($this->owner->SubmissionsLimitMonth === 0) {
            $this->owner->SubmissionsLimitMonth = INF;
        }

        if ($this->owner->SubmissionsCountTotal === 0) {
            $this->owner->SubmissionsLimitTotal = INF;
        }

        return $this->owner->EnableSubmissionsLimit && (
            $this->owner->SubmissionsCountDay >= $this->owner->SubmissionsLimitDay ||
                $this->owner->SubmissionsCountWeek >= $this->owner->SubmissionsLimitWeek ||
                $this->owner->SubmissionsCountMonth >= $this->owner->SubmissionsLimitMonth
        );
    }
}
