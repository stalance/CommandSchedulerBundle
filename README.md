CommandSchedulerBundle
======================

This bundle will allow you to easily manage scheduling for Symfony's console commands (native or not) with cron expression.

## Versions & Dependencies

The following table shows the compatibilities of different versions of the bundle :

| Version                                                                                 | Symfony          | PHP        |
| --------------------------------------------------------------------------------------- |  --------------- | ---------- |
| [3.0 (master)](https://github.com/J-Mose/CommandSchedulerBundle/tree/master)            | ^3.4\|^4.3\|^5.0 | ^7.3|^8.0  |
| [2.2.x](https://github.com/J-Mose/CommandSchedulerBundle/tree/2.2)                      | ^3.4\|^4.3       | ^7.1       |

When using Symfony Flex there is an [installation recipe](https://github.com/symfony/recipes-contrib/tree/master/dukecity/command-scheduler-bundle/2.0).  
To use it, you have to enable contrib recipes on your project : `composer config extra.symfony.allow-contrib true`

## Features

- An admin interface to add, edit, enable/disable or delete scheduled commands.
- For each command, you define : 
  - name
  - symfony console command (choice based on native `list` command)
  - cron expression (see [Cron format](http://en.wikipedia.org/wiki/Cron#Format) for informations)
  - output file (for `$output->write`)
  - priority
- A new console command `scheduler:execute [--dump] [--no-output]` which will be the single entry point to all commands
- Management of queuing and prioritization between tasks 
- Locking system, to stop scheduling a command that has returned an error
- Monitoring with timeout or failed commands (Json URL and command with mailing)
- Translated in french, english, german and spanish
- An [EasyAdmin](https://github.com/EasyCorp/EasyAdminBundle) configuration template available [here](Resources/doc/index.md#6---easyadmin-integration)
- **Beta** - Handle commands with a deamon (unix only) if you don't want to use a cronjob

## Screenshots
![list](Resources/doc/images/scheduled-list.png)

![new](Resources/doc/images/new-schedule.png)

![new2](Resources/doc/images/command-list.png)

## Documentation

See the [documentation here](Resources/doc/index.md).

## License

This bundle is under the MIT license. See the [complete license](Resources/meta/LICENCE) for info.
