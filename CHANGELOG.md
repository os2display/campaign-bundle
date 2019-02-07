# os2display/campaign-bundle

## 1.3.0

* Renamed bundle to Os2DisplayCampaignBundle.

## 1.2.1

* Fixed issue where removing channel, screen or screenGroup messed up arrays.

## 1.2.0

* Refactored campaign bundle to react to events instead of overriding MiddlewareCommunications.
* Fixed issues that resulted in campaigns not applying correctly to screens.

## 1.1.0

* Added event subscriber to Os2Display\CoreBundle\Events\CleanupEvent::EVENT_CLEANUP_CHANNELS to protect channels that are part of a campaign from being deleted. 

## 1.0.1

* Made save button sticky.
* Fixed error messages.
* Added call to middleware to get which channels are in the middleware
  to make sure it matches the backend.
* Added watch parameter to datetime pickers.
* Changed blameable user field from string to reference.
* Removed os2display requirements.

## 1.0.0

* First release.
