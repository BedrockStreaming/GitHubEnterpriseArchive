# GitHub Enterprise Archive [![Build Status](https://api.travis-ci.org/M6Web/GitHubEnterpriseArchive.png?branch=master)](http://travis-ci.org/M6Web/GitHubEnterpriseArchive)

Many companies all over the world uses [GitHub Enterprise](https://enterprise.github.com/) to work on lot of internal projects : writing code & documentation, fixing & submitting bugs, and so forth. GitHub Enterprise Archive (aka GHE Archive) is a project to **record** the internal public GitHub Enterprise timeline, **archive it**, and **make it easily accessible for** further analysis.

![Stats](http://www.stathat.com//graphs/46/9b/85986d698e40c33a8e60a3755fcc.png)

GitHub provides [18 event types](http://developer.github.com/v3/activity/events/types/), which range from new commits and fork events, to opening new tickets, commenting, and adding members to a project. The activity is aggregated in daily archives, which you can access with any HTTP client. Each archive contains a stream of JSON encoded GitHub events ([sample](https://gist.github.com/KuiKui/7583276)), which you can process in any language.

| Query | Command |
|--------|-------------|
| Your internal activity for November 20, 2013 | `wget http://your-ghe-archive/api/events/2013-11-20` |
| Your internal activity for November, 2013 | `wget http://your-ghe-archive/api/events/2013-11` |
| Your internal activity for 2013 | `wget http://your-ghe-archive/api/events/2013` |
| All your internal activity | `wget http://your-ghe-archive/api/events` |

*Note : use `page` and `per_page` GET parameters to paginate results.*

## Installation

#### Project

* Clone the project,
* Use [Composer](http://getcomposer.org/) to install dependencies with `composer install` in the project directory,
* Copy `app/config/parameters.yml.dist` to `app/config/parameters.yml` and edit the parameters,
* Setup your favorite web server (with PHP support).

*Note : GHE Archive does not use DB storage. To ease the setup, it stores data on file system.*

#### Cron

Add the command `app/console archive` to your crontab. Due to Github limitation, it can archive only the last 300 events. You must choose your cron frequency so that there are no more than 300 events between two cron jobs. The command automatically stops archiving events older than the last run. That means you should prefer to run it too frequently rather than risk losing some events.

#### Stathat (optional)

GHE Archive provides built-in graphs using [Stathat](http://www.stathat.com/) (it's free for 10 stats). If you registered to [Stathat](http://www.stathat.com/), enable the functionality and add your [API key](https://www.stathat.com/settings#ez-api) in `app/config/parameters.yml`. The global activity graph and the per-event graphs are automatically generated.

## Credits

Developped by the [Cytron Team](http://cytron.fr/) of [M6 Web](http://tech.m6web.fr/).
Respectfully inspired by [GitHub Archive](http://www.githubarchive.org/).

## License

[GHE Archive](https://github.com/M6Web/GitHubEnterpriseArchive) is licensed under the [MIT license](LICENSE).
