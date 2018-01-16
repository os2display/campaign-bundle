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

Enable the bundle in `AppKernel.php`, by adding ItkCampaignBundle to $bundles.

```sh
new Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle(),
new Itk\CampaignBundle\ItkCampaignBundle()
```

Enable `timestampable` and `blameable` in your configuration:

```yaml
stof_doctrine_extensions:
    orm:
        default:
            timestampable: true
            blameable: true
```
