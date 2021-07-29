# SilverStripe Campaign Schedule

## Introduction

This module allows to schedule a campaign ([silverstripe/campaign-admin](https://github.com/silverstripe/silverstripe-campaign-admin)) to be published at a later date.

## Installation

```
composer require dnadesign/silverstripe-campaign-schedule ^1
```

### Alternative Installation
This module currently relies on a fork of silverstripe/campaign-admin which adds a hook in the CmapaignAdmin controller.
Also the module is not on Packagist yet.
So to install, you need to require the modules via vcs.

```
"require": {}
    "silverstripe/campaign-admin": "dev-hooks as 1.8.0",
    "dnadesign/silverstripe-campaign-schedule": "^1",
},
"repositories": [
    {
        "type": "vcs",
        "url": "git@github.com:dnadesign/silverstripe-campaign-admin.git"
    },
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
You can add a schedule date to any campaign. Note that if a date is set in the past, the campaign will published as soon as the cron runs.
You can also associate watchers (members) to the campaign so they will receive an email once the campaign has been published via cron.
Note that campaign can still be published manually at any time.

##