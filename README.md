> [!Important]
> 
> ### This project is no longer actively maintained.
> 
> The source code in this repository is no longer maintained. It has been superseded by [version 2](https://os2display.github.io/display-docs/), which offers improved features and better support.
> 
> Thank you to all who have contributed to this project. We recommend transitioning to [Os2Display version 2](https://os2display.github.io/display-docs/) for continued support and updates.
> 
> **Final Release**: The final stable release is version [2.1.0](https://github.com/os2display/campaign-bundle/releases/tag/2.1.0)
<br>


# campaign-bundle
Extends os2display with campaigns.

Campaigns supply a new functionality for controlling content on the screens.

With campaigns a group of screens can be "taken over" for a given period of time
with a given channel. After the period has expired the screen returns to the
regular content.

## Installation
Add the git repository to "repositories" in `composer.json`.

```
"repositories": {
    "os2display/campaign-bundle": {
      "type": "vcs",
      "url": "https://github.com/os2display/campaign-bundle"
    },
    ...
}
```

Require the bundle with composer.

```sh
composer require os2display/campaign-bundle
```

Enable the bundle in `AppKernel.php`, by adding Os2DisplayCampaignBundle to $bundles.

NB! This should be done after adding Os2Display/CoreBundle since,
MiddlewareCommunications.php from CoreBundle is overridden in CampaignBundle.

```sh
new Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle(),
new Os2Display\CampaignBundle\Os2DisplayCampaignBundle()
```

Enable `timestampable` and `blameable` in your configuration:

```yaml
stof_doctrine_extensions:
    orm:
        default:
            timestampable: true
            blameable: true
```
