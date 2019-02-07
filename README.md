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
    "itk-os2display/campaign-bundle": {
      "type": "vcs",
      "url": "https://github.com/itk-os2display/campaign-bundle"
    },
    ...
}
```

Require the bundle with composer.

```sh
composer require itk-os2display/campaign-bundle
```

Enable the bundle in `AppKernel.php`, by adding CampaignBundle to $bundles.

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
