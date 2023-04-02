# SilverStripe Campaign Schedule [In development]

## Introduction

This module allows to schedule a campaign ([silverstripe/campaign-admin](https://github.com/silverstripe/silverstripe-campaign-admin)) to be published at a later date.

## Installation

```
composer require dnadesign/silverstripe-campaign-schedule ^1
```

### Alternative Installation
This module is not on Packagist yet, so to install, you need to require the modules via vcs.

```
"require": {}
    "dnadesign/silverstripe-campaign-schedule": "dev-master",
},
"repositories": [
    {
        "type": "vcs",
        "url": "git@github.com:dnadesign/silverstripe-campaign-schedule.git"
    }
]

```

## Requirements
- SilverStripe ^4
- [silverstripe/campaign-admin](https://github.com/silverstripe/silverstripe-campaign-admin)
- [silverstripe/cron-task](https://github.com/silverstripe/silverstripe-crontask)

## Set up
Make sure to add the [server configuration](https://github.com/silverstripe/silverstripe-crontask#server-configuration) for the cron task module.

## Usage

### Create a campaign

Follow the [guide](https://userhelp.silverstripe.org/en/4/creating_pages_and_content/campaigns/) to add pages and documents to a new or existing campaign.

### Edit the campaign

To edit a campaign, go to the `Campaigns` tab and click the cog icon of the campaign you wish to schedule.

![](docs/en/images/Edit%20campaign.png)

Then, open the `Schedule` tab and set a date and time. You can also associate watchers (members) to the campaign so they will receive an email once the campaign has been published via cron.

![](docs/en/images/Set%20schedule.png)

> **Note:** if a date is set in the past, the campaign will published as soon as the cron runs. Also a campaign can still be published manually at any time.

## Test

### Locally

To test this functionality locally, set up a schedule for a campaign then run `sake dev/cron`. This will run the cron task and if the campaign's schedule is in the past, it will get published.

**Note**: make sure your local environment has the right timezone set up.