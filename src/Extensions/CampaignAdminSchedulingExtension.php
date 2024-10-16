<?php

namespace DNADesign\CampaignSchedule\Extensions;

use SilverStripe\CampaignAdmin\CampaignAdmin;
use SilverStripe\Core\Extension;
use SilverStripe\Versioned\ChangeSet;

/**
 * @extends Extension<CampaignAdmin&static>
 */
class CampaignAdminSchedulingExtension extends Extension
{
    /**
     * Add the Schedule date time to the GridField
     */
    protected function updateChangeSetResource(array &$resources, ChangeSet $changeSet): void
    {
        $resources['ScheduledPublishDateTime'] = $changeSet->getScheduleDate();
    }
}
