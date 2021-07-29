<?php

namespace DNADesign\CampaignSchedule\Extensions;

use SilverStripe\Core\Extension;

class CampaignAdminSchedulingExtension extends Extension
{
    /**
     * Add the Schedule date time to the GridField
     *
     * @param array $resources
     * @param ChangeSet $changeSet
     * @return void
     */
    public function updateChangeSetResources(&$resources, $changeSet)
    {
        $resources['ScheduledPublishDateTime'] = $changeSet->getScheduleDate();
    }
}
