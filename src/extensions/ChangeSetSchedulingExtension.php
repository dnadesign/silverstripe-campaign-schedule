<?php

namespace DNADesign\CampaignSchedule\Extensions;

use SilverStripe\Control\Email\Email;
use SilverStripe\Forms\DatetimeField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\ListboxField;
use SilverStripe\Forms\ReadonlyField;
use SilverStripe\ORM\DataExtension;
use SilverStripe\ORM\FieldType\DBDatetime;
use SilverStripe\Security\Member;
use SilverStripe\Versioned\ChangeSet;

class ChangeSetSchedulingExtension extends DataExtension
{
    private static $db = [
        'ScheduledPublishDateTime' => 'Datetime'
    ];

    private static $many_many = [
        'Watchers' => Member::class
    ];

    /**
     * Update Fields
     * @return FieldList
     */
    public function updateCMSFields(FieldList $fields)
    {
        // Scheduled date time
        $scheduleDateTime = DatetimeField::create('ScheduledPublishDateTime', 'Publish campaign on');
        if ($this->owner->IsInferred || $this->owner->IsPublished()) {
            $scheduleDateTime->setReadonly(true);
        }

        $fields->addFieldToTab('Root.Schedule', $scheduleDateTime);

        // Add info under State
        if ($this->owner->IsInDB() && $this->owner->getIsScheduled() && !$this->owner->IsPublished()) {
            $state = $fields->dataFieldByName('State');
            if ($state) {
                $state->setDescription('Scheduled to be automatically published on '.$this->getScheduleDate());
            }
        }

        // Watchers
        if ($this->owner->IsInDB()) {
            $watchers = ListboxField::create('Watchers', 'Watchers');
            $watchers->setSource(Member::get()->map());
            $watchers->setDescription('People who will receive an email once the campaign has been published on schedule');

            if ($this->owner->IsInferred || $this->owner->IsPublished()) {
                $watchers->setReadonly(true);
            }

            $fields->addFieldToTab('Root.Schedule', $watchers);
        }

        // When a campaign is published without a publisher (via cron, no-one logged in)
        // The publish date doesn't appear, so need to display it
        if ($this->owner->IsPublished() && $this->owner->PublisherID === 0) {
            $fields->addFieldsToTab('Root.Main', [
                ReadonlyField::create(
                    'PublishDate',
                    $this->owner->fieldLabel('PublishDate')
                ),
                ReadonlyField::create(
                    'PublisherCustomName',
                    $this->owner->fieldLabel('PublisherName'),
                    'Cron'
                )
            ]);
        }
    }

    /**
     * Add schedule date to the summary fields
     *
     * @param array $fields
     * @return void
     */
    public function updateSummaryFields(&$fields)
    {
        $fields['ScheduledPublishDateTime'] = 'Scheduled';
    }

    /**
     * Used to display the scheduled date in the grid field
     *
     * @return string
     */
    public function getScheduleDate()
    {
        $date = $this->owner->dbObject('ScheduledPublishDateTime');
        if ($date) {
            return $this->owner->dbObject('ScheduledPublishDateTime')->Nice();
        }

        return '-';
    }

    /**
     * Return whether the campaign is schedule, ie Does it have a schedule date
     *
     * @return boolean
     */
    public function getIsScheduled()
    {
        $date = $this->owner->dbObject('ScheduledPublishDateTime');
        return $date && $date->getTimestamp() > 0;
    }

    /**
     * Helper to figure out if a changeSet has been published
     *
     * @return boolean
     */
    public function IsPublished()
    {
        return $this->owner->State == ChangeSet::STATE_PUBLISHED;
    }

    /**
     * Send an email notification to people watching this campaign
     * Note: can throws exception, because of Swift Mailer
     * TODO: make subject and content configurable and translatable
     *
     * @throws Exception
     * @return boolean
     */
    public function notifyWatchers()
    {
        if ($this->owner->Watchers()->count() > 0) {
            $addresses = $this->owner->Watchers()->column('Email');
            array_filter($addresses, function ($address) {
                return filter_var($address, FILTER_VALIDATE_BOOL);
            });

            if (!empty($addresses)) {
                $email = new Email();
                $email->setSubject('A campaign you are watching has been published.');
                $email->setBody(sprintf('Campaign %s (%s) has been published automatically as per schedule.', $this->owner->Name, $this->owner->ID));
                $email->setTo(array_shift($addresses));
                if (count($addresses) > 0) {
                    foreach ($addresses as $address) {
                        $email->setCC($email);
                    }
                }
                return $email->send();
            }
        }

        return false;
    }
}
