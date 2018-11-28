# itk-os2display/campaign-bundle

## in develop

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
