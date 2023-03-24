<?php

declare(strict_types=1);

namespace WeDevelop\ElementalWidget\UserForm\Extension;

use SilverStripe\Forms\ReadonlyField;
use SilverStripe\ORM\DataExtension;
use SilverStripe\Forms\FieldList;
use WeDevelop\ElementalWidget\UserForm\Widget\UserFormWidget;

/**
 * @property bool $EnableSubmissionsLimit
 * @property int $SubmissionsLimitDay
 * @property int $SubmissionsLimitWeek
 * @property int $SubmissionsLimitMonth
 * @property int $SubmissionsCountDay
 * @property int $SubmissionsCountWeek
 * @property int $SubmissionsCountMonth
 * @property string $SubmissionsLimitText
 * @property SubmissionCountExtension|UserFormWidget $owner
 */
class SubmissionCountExtension extends DataExtension
{
    /**
     * @var array<string, string>
     * @config
     */
    private static array $db = [
        'SubmissionsCountDay' => 'Int',
        'SubmissionsCountWeek' => 'Int',
        'SubmissionsCountMonth' => 'Int',
        'SubmissionsCountTotal' => 'Int',
    ];

    public function updateCMSFields(FieldList $fields): void
    {
        $fields->removeByName([
            'SubmissionsCountDay',
            'SubmissionsCountWeek',
            'SubmissionsCountMonth',
            'SubmissionsCountTotal',
        ]);

        $fields->addFieldsToTab('Root.SubmissionCounts', [
            ReadonlyField::create('SubmissionsCountDay', 'Submissions today')->setDescription('Number of submissions that occurred today'),
            ReadonlyField::create('SubmissionsCountWeek', 'Submissions this week')->setDescription('Number of submissions that occurred this week'),
            ReadonlyField::create('SubmissionsCountMonth', 'Submissions this month')->setDescription('Number of submissions that occurred this month'),
            ReadonlyField::create('SubmissionsCountTotal', 'Total submissions')->setDescription('Total number of submissions'),
        ]);
    }

    /**
     * Returns true if the submission limit for this form is reached
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

        return $this->owner->EnableSubmissionsLimit && (
            $this->owner->SubmissionsCountDay >= $this->owner->SubmissionsLimitDay ||
                $this->owner->SubmissionsCountWeek >= $this->owner->SubmissionsLimitWeek ||
                $this->owner->SubmissionsCountMonth >= $this->owner->SubmissionsLimitMonth
        );
    }
}
