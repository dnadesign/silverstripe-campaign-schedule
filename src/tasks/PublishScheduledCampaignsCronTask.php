<?php

namespace Hud\Tasks;

use Psr\Log\LoggerInterface;
use SilverStripe\CronTask\Interfaces\CronTask;
use SilverStripe\ORM\FieldType\DBDatetime;
use SilverStripe\Versioned\ChangeSet;

class PublishScheduledCampaignsCronTask implements CronTask
{
    private static $dependencies = [
        'Logger' => '%$' . LoggerInterface::class,
    ];
    
    /**
     * @param LoggerInterface $logger
     * @return $this
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
        return $this;
    }

    /**
     * run this task every 1 minutes
     *
     * @return string
     */
    public function getSchedule()
    {
        return "*/1 * * * *";
    }

    /**
     * Find the change sets that have been scheduled and publish them
     *
     * @return void
     */
    public function process()
    {
        $campaigns = ChangeSet::get()->filter([
            'State' => ChangeSet::STATE_OPEN,
            'ScheduledPublishDateTime:LessThanOrEqual' => DBDatetime::now()->format(DBDatetime::ISO_DATETIME)
        ]);

        if ($campaigns->count() == 0) {
            $this->logger->info('No campaign scheduled.');
            return;
        }

        foreach ($campaigns as $campaign) {
            try {
                $published =  $campaign->publish();
                if ($published) {
                    $this->logger->info(sprintf('Cron published campaign %s (%s)', $campaign->Name, $campaign->ID));
                    try {
                        $campaign->notifyWatchers();
                    } catch (\Exception $e) {
                        $error = new \Error(sprintf('Cron could not notify watchers after campaign %s (%s) was published [%s]', $campaign->Name, $campaign->ID, $e->getMessage()));
                        $this->logger->error($error->getMessage(), ['exception' => $error]);
                    }
                } else {
                    $error = new \Error(sprintf('Cron cannot published campaign %s (%s)', $campaign->Name, $campaign->ID));
                    $this->logger->error($error->getMessage(), ['exception' => $error]);
                }
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage(), ['exception' => $e]);
            }
        }
    }
}
