<?php

namespace DNADesign\CampaignSchedule\Extensions;

use Exception;
use SilverStripe\Control\Email\Email;
use SilverStripe\Core\Extension;
use SilverStripe\Forms\DatetimeField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\ListboxField;
use SilverStripe\Forms\ReadonlyField;
use SilverStripe\ORM\ManyManyList;
use SilverStripe\Security\Member;
use SilverStripe\Versioned\ChangeSet;

/**
 * @property ?string $ScheduledPublishDateTime
 * @property int $PublisherID
 * @method ManyManyList<Member> Watchers()
 * @extends Extension<ChangeSet&static>
 */
class ChangeSetSchedulingExtension extends Extension
{
    private static array $db = [
        'ScheduledPublishDateTime' => 'Datetime',
    ];

    private static array $many_many = [
        'Watchers' => Member::class,
    ];

    /**
     * Update Fields
     */
    protected function updateCMSFields(FieldList $fields): void
    {
        // Scheduled date time
        $scheduleDateTime = DatetimeField::create('ScheduledPublishDateTime', 'Publish campaign on');
        if ($this->getOwner()->IsInferred || $this->getOwner()->IsPublished()) {
            $scheduleDateTime->setReadonly(true);
        }

        $fields->addFieldToTab('Root.Schedule', $scheduleDateTime);

        // Add info under State
        if ($this->getOwner()->IsInDB() && $this->getOwner()->getIsScheduled() && !$this->getOwner()->IsPublished()) {
            $state = $fields->dataFieldByName('State');
            if ($state) {
                $state->setDescription('Scheduled to be automatically published on ' . $this->getScheduleDate());
            }
        }

        // Watchers
        if ($this->getOwner()->IsInDB()) {
            $watchers = ListboxField::create('Watchers', 'Watchers');
            $watchers->setSource(Member::get()->map());
            $watchers->setDescription('People who will receive an email once the campaign has been published on schedule');

            if ($this->getOwner()->IsInferred || $this->getOwner()->IsPublished()) {
                $watchers->setReadonly(true);
            }

            $fields->addFieldToTab('Root.Schedule', $watchers);
        }

        // When a campaign is published without a publisher (via cron, no-one logged in)
        // The publish date doesn't appear, so need to display it
        if ($this->getOwner()->IsPublished() && $this->getOwner()->PublisherID === 0) {
            $fields->addFieldsToTab('Root.Main', [
                ReadonlyField::create(
                    'PublishDate',
                    $this->getOwner()->fieldLabel('PublishDate')
                ),
                ReadonlyField::create(
                    'PublisherCustomName',
                    $this->getOwner()->fieldLabel('PublisherName'),
                    'Cron'
                ),
            ]);
        }
    }

    /**
     * Add schedule date to the summary fields
     */
    protected function updateSummaryFields(array &$fields): void
    {
        $fields['ScheduledPublishDateTime'] = 'Scheduled';
    }

    /**
     * Used to display the scheduled date in the grid field
     */
    private function getScheduleDate(): string
    {
        $date = $this->getOwner()->dbObject('ScheduledPublishDateTime');
        if ($date && $date->getTimestamp()) {
            return $date->Nice();
        }

        return '-';
    }

    /**
     * Return whether the campaign is schedule, ie Does it have a schedule date
     */
    public function getIsScheduled(): bool
    {
        $date = $this->getOwner()->dbObject('ScheduledPublishDateTime');
        return $date && $date->getTimestamp() > 0;
    }

    /**
     * Helper to figure out if a changeSet has been published
     */
    public function IsPublished(): bool
    {
        return $this->getOwner()->State == ChangeSet::STATE_PUBLISHED;
    }

    /**
     * Send an email notification to people watching this campaign
     * Note: can throws exception, because of Swift Mailer
     * TODO: make subject and content configurable and translatable
     *
     * @throws Exception
     */
    public function notifyWatchers(): bool
    {
        if ($this->getOwner()->Watchers()->count() > 0) {
            $addresses = $this->getOwner()->Watchers()->column('Email');
            array_filter($addresses, function ($address) {
                return filter_var($address, FILTER_VALIDATE_BOOL);
            });

            if (!empty($addresses)) {
                $email = new Email();
                $email->setSubject('A campaign you are watching has been published.');
                $email->setBody(sprintf('Campaign %s (%s) has been published automatically as per schedule.', $this->getOwner()->Name, $this->getOwner()->ID));
                $email->setTo(array_shift($addresses));
                foreach ($addresses as $address) {
                    $email->setCC($email);
                }

                return $email->send();
            }
        }

        return false;
    }
}
